<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'sale_date',
        'buyer_name',
        'buyer_contact',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user who recorded this sale
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this sale
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
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
            ->orderBy('id', 'desc')
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

