<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'account_code',
        'account_name',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->whereNull('parent_id')->orderBy('order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->orderBy('order');
    }

    public function getTotalPaguAttribute(): float
    {
        return $this->allItems()->sum('budgeted_amount');
    }

    public function getTotalRealisasiAttribute(): float
    {
        return $this->allItems()->sum('realized_amount');
    }

    public function getSisaPaguAttribute(): float
    {
        return $this->total_pagu - $this->total_realisasi;
    }
}
