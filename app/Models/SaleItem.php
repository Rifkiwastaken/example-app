<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $primaryKey = 'sale_item_id';
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
        'planned_gps',
        'estimated_planting_area',
        'stock_packaging_id',
        'seed_request_id',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'payment_proof',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'date',
        'estimated_planting_area' => 'decimal:2',
    ];

    /**
     * Get the user who recorded this sale
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(StockPackaging::class, 'stock_packaging_id');
    }

    public function getInventoryTypeAttribute()
    {
        return $this->packaging?->stock?->plant;
    }

    public function seedRequest(): BelongsTo
    {
        return $this->belongsTo(SeedRequest::class, 'seed_request_id', 'seed_request_id');
    }

    public function displayOrganization(): ?string
    {
        $organization = $this->seedRequest?->organization
            ?: ($this->getAttribute('organization') ?: null);

        if (filled($organization)) {
            return trim((string) $organization);
        }

        return null;
    }

    public static function hydrateFromStockHistories($items)
    {
        $items = collect($items);
        if ($items->isEmpty()) {
            return $items;
        }

        $receipt = (string) $items->first()->receipt_number;
        $histories = StockHistory::with(['plant.type', 'packaging.stock.plant.type', 'packaging.rack.warehouse'])
            ->where('reason', 'like', '%'.$receipt.'%')
            ->orderBy('created_at')
            ->orderBy('stock_history_id')
            ->get();

        if ($histories->isEmpty()) {
            return $items;
        }

        $used = [];
        foreach ($items as $item) {
            $match = $histories->first(function (StockHistory $history) use ($item, $used) {
                if (isset($used[$history->getKey()])) {
                    return false;
                }

                return (float) $history->quantity === (float) $item->quantity
                    && (! $item->unit || ! $history->unit || $history->unit === $item->unit);
            }) ?? $histories->first(fn (StockHistory $history) => ! isset($used[$history->getKey()]));

            if (! $match) {
                continue;
            }

            $used[$match->getKey()] = true;
            $item->setRelation('saleHistory', $match);

            if ((! $item->relationLoaded('packaging') || ! $item->packaging) && $match->packaging) {
                $item->setRelation('packaging', $match->packaging);
            }

            $label = self::labelFromHistoryNotes($match->notes);
            if ((! $item->relationLoaded('packaging') || ! $item->packaging) && $label) {
                $packaging = StockPackaging::with(['stock.plant.type', 'rack.warehouse'])
                    ->where('no_label_seri', $label)
                    ->first();
                if ($packaging) {
                    $item->setRelation('packaging', $packaging);
                }
            }
        }

        return $items;
    }

    public static function labelFromHistoryNotes(?string $notes): ?string
    {
        if (! filled($notes)) {
            return null;
        }

        if (preg_match('/Kemasan\s+(.+?)\s+terjual/u', $notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function resolvedPackaging(): ?StockPackaging
    {
        if ($this->packaging) {
            return $this->packaging;
        }

        $packagings = $this->seedRequest?->relationLoaded('packagings')
            ? $this->seedRequest->packagings
            : $this->seedRequest?->packagings()->with(['stock.plant.type', 'rack.warehouse'])->get();

        if (! $packagings || $packagings->isEmpty()) {
            return $this->relationLoaded('saleHistory') ? $this->saleHistory?->packaging : null;
        }

        $sold = $packagings->first(function (StockPackaging $pkg) {
            return $pkg->status_kemasan === StockPackaging::STATUS_DISALURKAN
                && (float) $pkg->kapasitas_per_kemasan === (float) $this->quantity;
        });

        return $sold
            ?: $packagings->first(fn (StockPackaging $pkg) => $pkg->status_kemasan === StockPackaging::STATUS_DISALURKAN)
            ?: $packagings->first();
    }

    public function matchingRequestItem(): ?SeedRequestItem
    {
        $items = $this->seedRequest?->relationLoaded('items')
            ? $this->seedRequest->items
            : $this->seedRequest?->items()->with('plant.type')->get();

        if (! $items || $items->isEmpty()) {
            return null;
        }

        return $items->first(function (SeedRequestItem $item) {
            return (float) $item->quantity === (float) $this->quantity
                && (! $this->unit || ! $item->unit || $item->unit === $this->unit);
        }) ?: $items->first();
    }

    public function resolvedPlant(): ?Plant
    {
        return $this->resolvedPackaging()?->stock?->plant
            ?: $this->matchingRequestItem()?->plant
            ?: ($this->relationLoaded('saleHistory') ? $this->saleHistory?->plant : null);
    }

    public function displayPlantName(): string
    {
        return $this->resolvedPlant()?->displayName() ?: '-';
    }

    public function displayNomorInduk(): string
    {
        $fromPackaging = $this->resolvedPackaging()?->stock?->nomor_induk;
        if (filled($fromPackaging)) {
            return $fromPackaging;
        }

        $plantId = $this->resolvedPlant()?->getKey()
            ?: ($this->relationLoaded('saleHistory') ? $this->saleHistory?->seed_varieties_id : null);
        if (! $plantId) {
            return '-';
        }

        $fromLot = Stock::query()->where('seed_varieties_id', $plantId)->orderByDesc('id')->value('nomor_induk');
        if (filled($fromLot)) {
            return $fromLot;
        }

        $note = StockHistory::query()
            ->where('seed_varieties_id', $plantId)
            ->where('notes', 'like', 'Lot induk%')
            ->orderBy('created_at')
            ->value('notes');

        if (preg_match('/Lot induk\s+(\S+)/u', (string) $note, $matches)) {
            return trim($matches[1], " .");
        }

        return '-';
    }

    public function displayLabelNumber(): string
    {
        $fromPackaging = $this->resolvedPackaging()?->no_label_seri;
        if (filled($fromPackaging)) {
            return $fromPackaging;
        }

        $history = $this->relationLoaded('saleHistory') ? $this->saleHistory : null;

        return self::labelFromHistoryNotes($history?->notes) ?: '-';
    }

    public static function parseGps(?string $value): ?array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (! preg_match('/(-?\d+(?:\.\d+)?)\s*[,;\s]\s*(-?\d+(?:\.\d+)?)/', $value, $matches)) {
            return null;
        }

        $lat = (float) $matches[1];
        $lng = (float) $matches[2];
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    /**
     * Get the inventory lot (sale_items.warehouse_lot_id -> warehouse_lots.warehouse_lot_id)
     */
    public function inventoryLot(): BelongsTo
    {
        return $this->packaging();
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method ?? '') {
            'cash' => 'Cash',
            'transfer_bank' => 'Transfer Bank',
            default => (string) ($this->payment_method ?? ''),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status ?? '') {
            'lunas' => 'LUNAS',
            'belum_lunas' => 'BELUM LUNAS',
            default => (string) ($this->payment_status ?? ''),
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status ?? '') {
            'lunas' => 'success',
            'belum_lunas' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Generate next receipt number (data now in sale_items)
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = 'PJ-'.$year.'-';
        $max = self::query()
            ->where('receipt_number', 'like', $prefix.'%')
            ->pluck('receipt_number')
            ->map(function ($number) use ($prefix) {
                $suffix = substr((string) $number, strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max();

        $nextNumber = ((int) $max) + 1;
        do {
            $receipt = $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (self::where('receipt_number', $receipt)->exists());

        return $receipt;
    }
}

