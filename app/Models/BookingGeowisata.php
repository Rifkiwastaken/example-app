<?php

namespace App\Models;

use App\Support\IndonesiaHoliday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGeowisata extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_BOOKED = 'Booked';
    public const STATUS_HADIR = 'Hadir';
    public const STATUS_BATAL = 'Batal';

    public const PENGAJUAN_MENUNGGU = 'menunggu_review';
    public const PENGAJUAN_DISETUJUI = 'disetujui';
    public const PENGAJUAN_DITOLAK = 'ditolak';

    public const MAX_PESERTA_PER_HARI = 150;

    protected $table = 'booking_geowisata';

    protected $fillable = [
        'kode_booking',
        'nama_lembaga',
        'jumlah_peserta',
        'tgl_kunjungan',
        'nama_pj',
        'no_whatsapp_pj',
        'status_kedatangan',
        'status_pengajuan',
        'alasan_ditolak',
        'processed_by',
        'approved_at',
    ];

    protected $casts = [
        'tgl_kunjungan' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public static function generateKode(?Carbon $date = null): string
    {
        $date = $date ?: now();
        $year = $date->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        $kode = 'GEO-'.$year.'-'.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        while (static::where('kode_booking', $kode)->exists()) {
            $count++;
            $kode = 'GEO-'.$year.'-'.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        }

        return $kode;
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    public function isActiveSlot(): bool
    {
        return $this->status_kedatangan !== self::STATUS_BATAL
            && $this->status_pengajuan !== self::PENGAJUAN_DITOLAK;
    }

    public function pengajuanLabel(): string
    {
        return match ($this->status_pengajuan) {
            self::PENGAJUAN_DISETUJUI => 'Disetujui',
            self::PENGAJUAN_DITOLAK => 'Ditolak',
            default => 'Menunggu review',
        };
    }

    public function pengajuanColor(): string
    {
        return match ($this->status_pengajuan) {
            self::PENGAJUAN_DISETUJUI => 'success',
            self::PENGAJUAN_DITOLAK => 'danger',
            default => 'warning',
        };
    }

    public static function bookedOn(string $date): int
    {
        return (int) static::query()
            ->whereDate('tgl_kunjungan', $date)
            ->where('status_kedatangan', '!=', self::STATUS_BATAL)
            ->where('status_pengajuan', '!=', self::PENGAJUAN_DITOLAK)
            ->sum('jumlah_peserta');
    }

    public static function isDateAvailable(string $date, int $peserta = 0, ?int $ignoreId = null): bool
    {
        $carbon = Carbon::parse($date);
        if ($carbon->lt(now()->startOfDay()) || IndonesiaHoliday::isClosedDay($carbon)) {
            return false;
        }

        $query = static::query()
            ->whereDate('tgl_kunjungan', $date)
            ->where('status_kedatangan', '!=', self::STATUS_BATAL)
            ->where('status_pengajuan', '!=', self::PENGAJUAN_DITOLAK);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return ((int) $query->sum('jumlah_peserta') + $peserta) <= self::MAX_PESERTA_PER_HARI;
    }
}
