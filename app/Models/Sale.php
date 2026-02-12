<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'sale_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'receipt_number',
        'sale_date',
        'buyer_name',
        'buyer_contact',
        'buyer_nik',
        'buyer_category',
        'buyer_category_custom',
        'destination_province',
        'destination_city',
        'destination_district',
        'destination_village',
        'planned_location_name',
        'estimated_planting_area',
        'planting_location_id', // Tetap ada untuk backward compatibility
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'estimated_planting_area' => 'decimal:2',
    ];

    /**
     * Get the user who recorded this sale
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the planting location where seeds were planted
     */
    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    /**
     * Get all items in this sale
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'transfer_bank' => 'Transfer Bank',
            default => $this->payment_method,
        };
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'lunas' => 'LUNAS',
            'belum_lunas' => 'BELUM LUNAS',
            default => $this->payment_status,
        };
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'lunas' => 'success',
            'belum_lunas' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Generate next receipt number
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastSale = self::whereYear('sale_date', $year)
            ->orderBy('sale_date', 'desc')
            ->orderBy('sale_id', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->receipt_number, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'PJ-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}

