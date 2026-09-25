<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebsiteContent extends Model
{
    use HasCustomId;

    protected $table = 'website_contents';
    protected $primaryKey = 'content_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jenis',
        'kategori',
        'judul',
        'slug',
        'excerpt',
        'body',
        'cover_path',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'author',
        'kip_label',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public const JENIS = [
        'profil' => 'Profil',
        'berita' => 'Berita Kegiatan',
        'artikel' => 'Artikel Edukasi Tani',
        'laporan' => 'Laporan',
        'dokumen' => 'Dokumen',
    ];

    public const PROFIL_KATEGORI = [
        'visi_misi' => 'Visi & Misi',
        'tupoksi' => 'Tugas Pokok & Fungsi',
        'struktur' => 'Struktur Organisasi',
        'peta' => 'Peta Wilayah Kerja Kebun Induk',
    ];

    public static function profilKategori(): array
    {
        $items = self::PROFIL_KATEGORI;
        $fromDb = static::query()
            ->where('jenis', 'profil')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->pluck('kategori');
        foreach ($fromDb as $key) {
            if (! isset($items[$key])) {
                $items[$key] = Str::headline(str_replace('_', ' ', $key));
            }
        }

        return $items;
    }

    public const KIP = [
        'Berkala' => 'Berkala',
        'Setiap Saat' => 'Setiap Saat',
        'Serta Merta' => 'Serta Merta',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? Str::headline($this->jenis);
    }

    public function kategoriLabel(): string
    {
        return self::PROFIL_KATEGORI[$this->kategori] ?? ($this->kategori ?: '-');
    }

    public function fileSizeLabel(): string
    {
        if (! $this->file_size) {
            return '-';
        }
        $mb = $this->file_size / 1048576;
        if ($mb >= 1) {
            return number_format($mb, 1).' MB';
        }

        return number_format($this->file_size / 1024, 0).' KB';
    }

    public function coverUrl(): ?string
    {
        if (! $this->cover_path || ! \Storage::disk('public')->exists($this->cover_path)) {
            return null;
        }

        return asset('storage/'.$this->cover_path);
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/'.$this->file_path) : null;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public static function makeSlug(string $judul, ?string $exceptId = null): string
    {
        $base = Str::slug($judul) ?: 'konten';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('content_id', '!=', $exceptId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public static function navJenis(): array
    {
        $fromDb = static::published()->select('jenis')->distinct()->pluck('jenis')->all();
        $items = [];
        foreach (array_unique(array_merge(array_keys(self::JENIS), $fromDb)) as $jenis) {
            $items[$jenis] = self::JENIS[$jenis] ?? Str::headline($jenis);
        }

        return $items;
    }
}
