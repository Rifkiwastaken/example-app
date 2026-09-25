<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeedRequest extends Model
{
    use HasCustomId;

    public const STATUS_PENDING = 'menunggu_verifikasi';
    public const STATUS_APPROVED = 'disetujui';
    public const STATUS_REJECTED = 'ditolak';
    public const STATUS_READY = 'siap_diambil';
    public const STATUS_TAKEN = 'telah_diambil';

    protected $table = 'seed_requests';
    protected $primaryKey = 'seed_request_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'request_number',
        'request_date',
        'buyer_name',
        'buyer_contact',
        'buyer_nik',
        'buyer_category',
        'buyer_category_custom',
        'organization',
        'destination_province',
        'destination_city',
        'destination_district',
        'destination_village',
        'planned_location_name',
        'planned_gps',
        'estimated_planting_area',
        'status',
        'rejection_reason',
        'created_by',
        'processed_by',
        'approved_at',
        'ready_at',
        'taken_at',
        'receipt_number',
        'notes',
        'payment_method',
        'payment_proof',
        'total_amount',
    ];

    protected $casts = [
        'request_date' => 'date',
        'estimated_planting_area' => 'decimal:2',
        'approved_at' => 'datetime',
        'ready_at' => 'datetime',
        'taken_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('request_date', $year)->count() + 1;
        $number = 'PB-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        while (static::where('request_number', $number)->exists()) {
            $count++;
            $number = 'PB-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(SeedRequestItem::class, 'seed_request_id', 'seed_request_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by', 'user_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'seed_request_id', 'seed_request_id');
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(StockPackaging::class, 'seed_request_id', 'seed_request_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu verifikasi',
            self::STATUS_APPROVED => 'Permintaan telah dikonfirmasi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_READY => 'Benih siap untuk diambil',
            self::STATUS_TAKEN => 'Benih telah diambil',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_READY => 'primary',
            self::STATUS_TAKEN => 'success',
            default => 'secondary',
        };
    }

    public static function releaseExpiredHolds(): int
    {
        $expired = StockPackaging::where('hold_for_request', true)
            ->whereNotNull('hold_until')
            ->where('hold_until', '<', Carbon::now())
            ->get();

        foreach ($expired as $pkg) {
            $request = $pkg->seed_request_id ? static::find($pkg->seed_request_id) : null;
            if ($request && in_array($request->status, [self::STATUS_TAKEN, self::STATUS_REJECTED], true)) {
                continue;
            }
            $pkg->update([
                'hold_for_request' => false,
                'hold_until' => null,
                'seed_request_id' => null,
            ]);
            if ($request && ! in_array($request->status, [self::STATUS_TAKEN, self::STATUS_REJECTED], true)) {
                $request->update([
                    'status' => self::STATUS_REJECTED,
                    'rejection_reason' => 'Otomatis ditolak karena tidak ada kelanjutan selama 30 hari.',
                ]);
            }
        }

        return $expired->count();
    }
}
