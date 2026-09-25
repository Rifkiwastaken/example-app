<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\Stock;
use App\Models\StockPackaging;
use App\Models\WebsiteContent;
use App\Models\WebsiteSetting;
use App\Support\SeedCertificate;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function profil()
    {
        $contents = WebsiteContent::published()->jenis('profil')->orderBy('kategori')->orderBy('judul')->get()->groupBy('kategori');
        $categories = WebsiteContent::profilKategori();
        $situs = WebsiteSetting::current();

        return view('site.profil', compact('contents', 'categories', 'situs'));
    }

    public function posts(Request $request)
    {
        $tab = $request->get('jenis', 'semua');
        $q = trim((string) $request->get('q', ''));
        $query = WebsiteContent::published()->whereIn('jenis', ['berita', 'artikel']);
        if ($tab === 'berita') {
            $query->where('jenis', 'berita');
        } elseif ($tab === 'artikel') {
            $query->where('jenis', 'artikel');
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('judul', 'like', '%'.$q.'%')
                    ->orWhere('excerpt', 'like', '%'.$q.'%')
                    ->orWhere('body', 'like', '%'.$q.'%')
                    ->orWhere('author', 'like', '%'.$q.'%');
            });
        }
        $posts = $query->orderByDesc('published_at')->orderByDesc('created_at')->paginate(12)->withQueryString();

        return view('site.posts', compact('posts', 'tab', 'q'));
    }

    public function showPost(string $slug)
    {
        $post = WebsiteContent::published()->where('slug', $slug)->firstOrFail();

        return view('site.show', compact('post'));
    }

    public function documents(Request $request)
    {
        $jenis = $request->get('jenis');
        $query = WebsiteContent::published()->whereIn('jenis', ['laporan', 'dokumen'])->whereNotNull('file_path');
        if ($jenis && in_array($jenis, ['laporan', 'dokumen'], true)) {
            $query->where('jenis', $jenis);
        }
        $documents = $query->orderByDesc('published_at')->orderBy('judul')->paginate(20)->withQueryString();

        return view('site.documents', compact('documents', 'jenis'));
    }

    public function showDocument(WebsiteContent $content)
    {
        abort_unless($content->is_published && in_array($content->jenis, ['laporan', 'dokumen'], true), 404);

        $situs = WebsiteSetting::current();

        return view('site.document-show', compact('content', 'situs'));
    }

    public function download(WebsiteContent $content)
    {
        return $this->fileResponse($content, true);
    }

    public function viewFile(WebsiteContent $content)
    {
        return $this->fileResponse($content, false);
    }

    private function fileResponse(WebsiteContent $content, bool $asDownload)
    {
        if (! $content->is_published || ! $content->file_path) {
            abort(404);
        }
        $path = storage_path('app/public/'.$content->file_path);
        if (! is_file($path)) {
            abort(404);
        }

        $name = $content->file_name ?: basename($content->file_path);
        if ($asDownload) {
            return response()->download($path, $name);
        }

        return response()->file($path, [
            'Content-Type' => $content->mime_type ?: (mime_content_type($path) ?: 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }

    public function prices()
    {
        $situs = WebsiteSetting::current();
        $today = now()->toDateString();
        $plants = Plant::with(['type', 'satuanStok'])->orderBy('name')->get();
        $stocks = Stock::with(['packagings.rack.warehouse', 'certificationReport'])
            ->where('status_stok', Stock::STATUS_SIAP)
            ->where('stok_saat_ini', '>', 0)
            ->whereDate('tgl_kedaluwarsa', '>=', $today)
            ->get()
            ->groupBy('seed_varieties_id');

        $rows = $plants->map(function (Plant $plant) use ($stocks) {
            return $this->catalogRow($plant, $stocks->get($plant->getKey(), collect()));
        });

        $groups = $rows
            ->groupBy(fn (array $row) => $row['category'])
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);

        return view('site.prices', compact('situs', 'groups'));
    }

    public function priceShow(Plant $plant)
    {
        $situs = WebsiteSetting::current();
        $today = now()->toDateString();
        $plant->load(['type', 'satuanStok']);

        $stocks = Stock::with([
            'packagings.rack.warehouse',
            'certificationReport',
            'postHarvest.planting.field.plantingLocation',
        ])
            ->where('seed_varieties_id', $plant->getKey())
            ->where('status_stok', Stock::STATUS_SIAP)
            ->where('stok_saat_ini', '>', 0)
            ->orderBy('nomor_induk')
            ->get();

        $activeStocks = $stocks->filter(function (Stock $stock) use ($today) {
            return ! $stock->tgl_kedaluwarsa || $stock->tgl_kedaluwarsa->toDateString() >= $today;
        });

        $row = $this->catalogRow($plant, $activeStocks);

        $stockRows = $stocks
            ->groupBy(fn (Stock $stock) => $stock->nomor_induk ?: 'id-'.$stock->id)
            ->map(function ($group) {
                /** @var \Illuminate\Support\Collection<int, \App\Models\Stock> $group */
                $primary = $group->sortBy(fn (Stock $stock) => optional($stock->tgl_kedaluwarsa)->timestamp ?? PHP_INT_MAX)->first();
                $canCertificate = $primary && ($primary->hasLabel() || $primary->postHarvest);

                return [
                    'stock' => $primary,
                    'nomor_induk' => $primary?->nomor_induk ?: '-',
                    'qty' => (float) $group->sum('stok_saat_ini'),
                    'kelas' => $group->map(fn (Stock $stock) => $stock->certificationReport?->warnaLabel() ?: $stock->certificationReport?->warna_label)->filter()->unique()->implode(', ') ?: '-',
                    'expired_at' => $primary?->tgl_kedaluwarsa,
                    'lokasi' => $this->warehouseLocations($group),
                    'can_certificate' => (bool) $canCertificate,
                ];
            })
            ->values();

        $locations = $plant->plantings()
            ->with('field.plantingLocation')
            ->get()
            ->map(fn ($planting) => $planting->field?->plantingLocation)
            ->filter()
            ->unique('planting_location_id')
            ->values();

        return view('site.price-show', compact('situs', 'plant', 'row', 'stockRows', 'locations'));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Stock>  $plantStocks
     */
    private function catalogRow(Plant $plant, $plantStocks): array
    {
        $qty = (float) $plantStocks->sum('stok_saat_ini');
        $kelas = $plantStocks
            ->map(fn (Stock $stock) => $stock->certificationReport?->warnaLabel() ?: $stock->certificationReport?->warna_label)
            ->filter()
            ->unique()
            ->implode(', ');

        return [
            'plant' => $plant,
            'category' => $plant->type?->category ?: 'Lainnya',
            'qty' => $qty,
            'lokasi' => $this->warehouseLocations($plantStocks),
            'kelas' => $kelas ?: '-',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Stock>  $stocks
     */
    private function warehouseLocations($stocks): ?string
    {
        $packagings = $stocks->flatMap(fn (Stock $stock) => $stock->packagings ?? collect());
        $sellable = $packagings->filter(fn ($pkg) => method_exists($pkg, 'isSellable') && $pkg->isSellable());
        $source = $sellable->isNotEmpty() ? $sellable : $packagings;
        $names = $source
            ->map(function ($pkg) {
                $warehouse = $pkg->rack?->warehouse?->name;
                if (! $warehouse) {
                    return null;
                }

                return trim($warehouse.($pkg->rack?->name ? ' - '.$pkg->rack->name : ''));
            })
            ->filter()
            ->unique()
            ->values();

        return $names->isNotEmpty() ? $names->implode(', ') : null;
    }

    public function contact()
    {
        return view('site.contact', ['situs' => WebsiteSetting::current()]);
    }

    public function contentsByJenis(Request $request, string $jenis)
    {
        if (in_array($jenis, ['profil', 'berita', 'artikel', 'laporan', 'dokumen'], true)) {
            return redirect()->route(match ($jenis) {
                'profil' => 'site.profil',
                'berita', 'artikel' => 'site.posts',
                default => 'site.documents',
            });
        }

        $items = WebsiteContent::published()->jenis($jenis)
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('site.jenis', compact('items', 'jenis'));
    }

    public function labelsJson(Plant $plant)
    {
        return $this->lotsJson($plant);
    }

    public function lotsJson(Plant $plant)
    {
        $lots = SeedCertificate::activeLotsForPlant($plant)->map(fn (Stock $stock) => SeedCertificate::lotRow($stock));

        return response()->json([
            'plant' => $plant->displayName(),
            'unit' => $plant->satuanStok?->code ?: '',
            'lots' => $lots,
            'labels' => $lots,
        ]);
    }

    public function seedCertificate(Stock $stock)
    {
        $stock->load(SeedCertificate::stockRelations());
        abort_unless($stock->hasLabel() || $stock->postHarvest, 404);

        return view('public.seed-certificate', [
            'stock' => $stock,
            'cert' => SeedCertificate::certificatePayload($stock),
        ]);
    }

    public function seedLabel(StockPackaging $packaging)
    {
        $packaging->load(SeedCertificate::packagingRelations());

        return view('public.seed-label', [
            'packaging' => $packaging,
            'label' => SeedCertificate::labelPayload($packaging),
        ]);
    }
}
