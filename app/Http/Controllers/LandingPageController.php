<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use App\Models\PlantType;
use App\Models\Stock;
use App\Models\WebsiteContent;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = trim((string) $request->get('search', ''));
        $categoryFilter = $request->get('category', 'all');
        $plantNameFilter = $request->get('plant_name', 'all');
        $varietyIdFilter = $request->get('variety_id', 'all');
        $commodityFilter = $request->get('commodity_id', 'all');
        $infoCommodityFilter = $request->get('info_commodity_id', 'all');
        $infoVarietyFilter = $request->get('info_variety_id', 'all');
        $infoSearch = trim((string) $request->get('info_search', ''));
        $today = now()->toDateString();

        $commodities = PlantType::orderBy('category')->orderBy('name')->get();
        $categories = $commodities->pluck('category')->filter()
            ->map(fn ($c) => trim((string) $c))
            ->unique(fn ($c) => mb_strtolower($c))
            ->sort()
            ->values();
        $plantNameOptions = $commodities
            ->when($categoryFilter !== 'all', fn ($rows) => $rows->filter(
                fn ($row) => strcasecmp((string) $row->category, $categoryFilter) === 0
            ))
            ->unique('name')
            ->sortBy('name')
            ->values();

        $plantsQuery = Plant::with(['type', 'satuanStok']);
        if ($categoryFilter !== 'all') {
            $plantsQuery->whereHas('type', fn ($q) => $q->whereRaw('LOWER(category) = ?', [mb_strtolower($categoryFilter)]));
        } elseif ($commodityFilter !== 'all') {
            $plantsQuery->where('seed_commodity_id', $commodityFilter);
        }
        if ($plantNameFilter !== 'all') {
            $plantsQuery->whereHas('type', fn ($q) => $q->where('name', $plantNameFilter));
        }
        if ($varietyIdFilter !== 'all') {
            $plantsQuery->where('seed_varieties_id', $varietyIdFilter);
        }
        $plants = $plantsQuery->orderBy('name')->get();
        if ($searchQuery !== '') {
            $needle = mb_strtolower($searchQuery);
            $plants = $plants->filter(function ($plant) use ($needle) {
                return str_contains(mb_strtolower((string) $plant->name), $needle)
                    || str_contains(mb_strtolower((string) $plant->variety), $needle)
                    || str_contains(mb_strtolower((string) ($plant->type->name ?? '')), $needle);
            })->values();
        }

        $varietyOptions = Plant::with('type')
            ->when($categoryFilter !== 'all', fn ($q) => $q->whereHas('type', fn ($t) => $t->whereRaw('LOWER(category) = ?', [mb_strtolower($categoryFilter)])))
            ->when($plantNameFilter !== 'all', fn ($q) => $q->whereHas('type', fn ($t) => $t->where('name', $plantNameFilter)))
            ->orderBy('variety')
            ->get();

        $stocksByPlant = Stock::where('status_stok', Stock::STATUS_SIAP)
            ->where('stok_saat_ini', '>', 0)
            ->whereDate('tgl_kedaluwarsa', '>=', $today)
            ->with(['certificationReport', 'packagings'])
            ->get()
            ->filter(fn (Stock $stock) => $stock->hasLabel())
            ->groupBy('seed_varieties_id');

        $stockData = $plants->map(function ($plant) use ($stocksByPlant) {
            $stocks = $stocksByPlant->get($plant->seed_varieties_id, collect());
            $totalStock = (float) $stocks->sum('stok_saat_ini');
            if ($totalStock <= 0) {
                return null;
            }

            return [
                'plant_id' => $plant->getKey(),
                'category' => $plant->type->category ?? '-',
                'plant_name' => $plant->type->name ?? $plant->name,
                'variety_name' => $plant->name,
                'variety_detail' => $plant->variety,
                'stock_available' => $totalStock,
                'stock_unit' => $plant->satuanStok?->code ?? '',
                'unit_price' => $plant->harga_jual !== null ? (float) $plant->harga_jual : null,
            ];
        })->filter()->values();

        $varietyRecords = Plant::query()
            ->when($commodityFilter !== 'all', function ($q) use ($commodityFilter) {
                $q->where(function ($w) use ($commodityFilter) {
                    $w->where('seed_commodity_id', $commodityFilter);
                });
            })
            ->orderBy('variety')
            ->get();

        $infoVarietyRecords = Plant::query()
            ->when($infoCommodityFilter !== 'all', fn ($q) => $q->where('seed_commodity_id', $infoCommodityFilter))
            ->orderBy('variety')
            ->get();

        $publicQuery = Plant::with('type')->where(function ($q) {
            $q->whereNotNull('public_description')->orWhereNotNull('public_photo_path');
        });
        if ($infoCommodityFilter !== 'all') {
            $publicQuery->where('seed_commodity_id', $infoCommodityFilter);
        }
        if ($infoVarietyFilter !== 'all') {
            $publicQuery->whereKey($infoVarietyFilter);
        }
        if ($infoSearch !== '') {
            $publicQuery->where(function ($q) use ($infoSearch) {
                $q->where('name', 'like', '%'.$infoSearch.'%')
                    ->orWhere('variety', 'like', '%'.$infoSearch.'%')
                    ->orWhere('public_description', 'like', '%'.$infoSearch.'%');
            });
        }
        $publicVarieties = $publicQuery->orderBy('name')->get()->map(function ($plant) {
            return [
                'name' => $plant->variety ?: $plant->name,
                'category' => trim(($plant->type->category ?? '').' - '.($plant->type->name ?? ''), ' -'),
                'description' => $plant->public_description ?: $plant->description,
                'photo' => $plant->public_photo_path
                    ? asset('storage/'.$plant->public_photo_path)
                    : 'https://images.unsplash.com/photo-1593113598332-cd288d649433?w=400',
            ];
        });

        $latestPosts = WebsiteContent::published()
            ->whereIn('jenis', ['berita', 'artikel'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $situs = WebsiteSetting::current();

        $stockGroups = $stockData->groupBy(fn ($row) => $row['category'] ?: 'Lainnya');

        return view('landing.home', compact(
            'stockData',
            'stockGroups',
            'searchQuery',
            'commodities',
            'commodityFilter',
            'categoryFilter',
            'plantNameFilter',
            'varietyIdFilter',
            'categories',
            'plantNameOptions',
            'varietyOptions',
            'latestPosts',
            'situs'
        ));
    }

    public function varietiesJson(Request $request)
    {
        $commodityId = $request->get('commodity_id');
        $query = Plant::query()->orderBy('variety');
        if ($commodityId && $commodityId !== 'all') {
            $query->where('seed_commodity_id', $commodityId);
        }

        return response()->json($query->get(['seed_varieties_id', 'name', 'variety', 'seed_commodity_id']));
    }

    public function edit()
    {
        return redirect()->route('contents.settings');
    }

    public function update(Request $request)
    {
        return redirect()->route('contents.settings');
    }
}
