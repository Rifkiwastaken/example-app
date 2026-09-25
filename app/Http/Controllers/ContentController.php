<?php

namespace App\Http\Controllers;

use App\Models\WebsiteContent;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $jenis = $request->get('jenis');
        $query = WebsiteContent::query()->orderByDesc('published_at')->orderByDesc('created_at');
        if ($jenis) {
            $query->where('jenis', $jenis);
        }
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('judul', 'like', '%'.$q.'%')->orWhere('slug', 'like', '%'.$q.'%');
            });
        }
        $contents = $query->paginate(20)->withQueryString();
        $jenisList = WebsiteContent::navJenis();
        $counts = WebsiteContent::query()->selectRaw('jenis, count(*) as total')->groupBy('jenis')->pluck('total', 'jenis');

        return view('contents.index', compact('contents', 'jenis', 'jenisList', 'counts'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('contents.form', [
            'content' => new WebsiteContent(['is_published' => true, 'author' => 'Humas UPTD BBI TPH']),
            'jenisList' => WebsiteContent::navJenis(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $this->validated($request);
        $data['slug'] = WebsiteContent::makeSlug($data['judul']);
        $data['created_by'] = Auth::user()->user_id ?? null;
        $data['published_at'] = $data['is_published'] ? ($data['published_at'] ?? now()) : null;
        $this->storeFiles($request, $data);
        WebsiteContent::create($data);

        return redirect()->route('contents.index', ['jenis' => $data['jenis']])
            ->with('success', 'Konten berhasil ditambahkan.');
    }

    public function edit(WebsiteContent $content)
    {
        $this->authorizeAdmin();

        return view('contents.form', [
            'content' => $content,
            'jenisList' => WebsiteContent::navJenis(),
        ]);
    }

    public function update(Request $request, WebsiteContent $content)
    {
        $this->authorizeAdmin();
        $data = $this->validated($request, $content);
        $data['slug'] = WebsiteContent::makeSlug($data['judul'], $content->getKey());
        $data['published_at'] = $data['is_published'] ? ($data['published_at'] ?? $content->published_at ?? now()) : null;
        $this->storeFiles($request, $data, $content);
        $content->update($data);

        return redirect()->route('contents.index', ['jenis' => $content->jenis])
            ->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(WebsiteContent $content)
    {
        $this->authorizeAdmin();
        $content->delete();

        return back()->with('success', 'Konten dihapus.');
    }

    public function settings()
    {
        $this->authorizeAdmin();

        return view('contents.settings', ['situs' => WebsiteSetting::current()]);
    }

    public function updateSettings(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'office_name' => 'required|string|max:200',
            'tagline' => 'nullable|string|max:255',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'hero_image' => 'nullable|url|max:255',
            'retribusi_note' => 'nullable|string',
        ]);
        WebsiteSetting::current()->update($data);

        return back()->with('success', 'Informasi umum situs disimpan.');
    }

    public function storeProfilKategori(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'nama' => 'required|string|max:80',
        ]);
        $key = Str::slug($data['nama'], '_');
        WebsiteContent::firstOrCreate([
            'jenis' => 'profil',
            'kategori' => $key,
            'judul' => $data['nama'],
        ], [
            'slug' => WebsiteContent::makeSlug($data['nama']),
            'body' => '',
            'is_published' => false,
            'created_by' => Auth::user()->user_id ?? null,
        ]);

        return back()->with('success', 'Kategori profil ditambahkan dan muncul di daftar kiri halaman profil.');
    }

    protected function validated(Request $request, ?WebsiteContent $content = null): array
    {
        $data = $request->validate([
            'jenis' => 'required|string|max:50',
            'jenis_custom' => 'nullable|string|max:50',
            'kategori' => 'nullable|string|max:80',
            'kategori_custom' => 'nullable|string|max:80',
            'judul' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'nullable|string',
            'author' => 'nullable|string|max:150',
            'kip_label' => 'nullable|string|max:50',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'cover' => 'nullable|image|max:4096',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);
        if (($data['jenis'] ?? '') === 'lainnya') {
            $custom = trim((string) ($data['jenis_custom'] ?? ''));
            if ($custom === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jenis_custom' => 'Isi nama jenis konten baru.',
                ]);
            }
            $data['jenis'] = Str::slug($custom);
        }
        if (($data['kategori'] ?? '') === 'lainnya') {
            $customKat = trim((string) ($data['kategori_custom'] ?? ''));
            if ($customKat === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'kategori_custom' => 'Isi nama kategori profil baru.',
                ]);
            }
            $data['kategori'] = Str::slug($customKat, '_');
        }
        unset($data['jenis_custom'], $data['kategori_custom'], $data['cover'], $data['file']);
        $data['is_published'] = $request->boolean('is_published');
        if (in_array($data['jenis'], ['laporan', 'dokumen'], true) && ! $request->hasFile('file') && ! $content?->file_path) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'Unggah berkas PDF/dokumen untuk jenis laporan atau dokumen.',
            ]);
        }

        return $data;
    }

    protected function storeFiles(Request $request, array &$data, ?WebsiteContent $content = null): void
    {
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('website-covers', 'public');
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('website-docs', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }
    }

    protected function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola konten situs.');
        }
    }
}
