<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranMagang extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_MENUNGGU = 'Menunggu Review';
    public const STATUS_DITERIMA = 'Diterima';
    public const STATUS_DITOLAK = 'Ditolak';

    protected $table = 'pendaftaran_magang';

    protected $fillable = [
        'nomor_registrasi',
        'nama_lengkap',
        'institusi_asal',
        'jabatan',
        'no_whatsapp',
        'peserta',
        'tgl_mulai',
        'tgl_selesai',
        'status_magang',
        'alasan_ditolak',
        'processed_by',
        'approved_at',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'peserta' => 'array',
    ];

    public static function generateNomor(): string
    {
        $stamp = now()->format('Ym');
        $count = static::where('nomor_registrasi', 'like', 'MGN-'.$stamp.'-%')->count() + 1;
        $nomor = 'MGN-'.$stamp.'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        while (static::where('nomor_registrasi', $nomor)->exists()) {
            $count++;
            $nomor = 'MGN-'.$stamp.'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        }

        return $nomor;
    }

    public function pesertaList(): array
    {
        return is_array($this->peserta) ? $this->peserta : [];
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    public function statusColor(): string
    {
        return match ($this->status_magang) {
            self::STATUS_DITERIMA => 'success',
            self::STATUS_DITOLAK => 'danger',
            default => 'warning',
        };
    }

    public function isBerjalan(): bool
    {
        if ($this->status_magang !== self::STATUS_DITERIMA) {
            return false;
        }
        $today = now()->toDateString();

        return $this->tgl_mulai->toDateString() <= $today
            && $this->tgl_selesai->toDateString() >= $today;
    }
}
