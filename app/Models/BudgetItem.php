<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'account_code',
        'description',
        'budgeted_amount',
        'realized_amount',
        'fund_source',
        'parent_id',
        'level',
        'order',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'realized_amount' => 'decimal:2',
        'level' => 'integer',
        'order' => 'integer',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BudgetItem::class, 'parent_id')->orderBy('order');
    }

    public function getPercentageAttribute(): float
    {
        if ($this->budgeted_amount == 0) {
            return 0;
        }
        return round(($this->realized_amount / $this->budgeted_amount) * 100, 2);
    }

    public function getSisaPaguAttribute(): float
    {
        return $this->budgeted_amount - $this->realized_amount;
    }
}
