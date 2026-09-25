<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SeedUnit extends Model
{
    use HasCustomId;

    protected $table = 'seed_units';
    protected $primaryKey = 'seed_unit_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const FIXED = [
        ['name' => 'Kilogram', 'code' => 'kg'],
        ['name' => 'Gram', 'code' => 'g'],
        ['name' => 'Butir Biji', 'code' => 'btr'],
        ['name' => 'Kecambah', 'code' => 'kcb'],
        ['name' => 'Batang / Pohon', 'code' => 'btg'],
        ['name' => 'Stek', 'code' => 'stk'],
        ['name' => 'Ton', 'code' => 'ton'],
    ];

    protected $fillable = [
        'name',
        'code',
        'is_fixed',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
    ];

    public static function ensureFixed(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('seed_units')) {
            return;
        }

        foreach (self::FIXED as $unit) {
            $existing = DB::table('seed_units')->where('code', $unit['code'])->first();
            if ($existing) {
                DB::table('seed_units')->where('seed_unit_id', $existing->seed_unit_id)->update([
                    'name' => $unit['name'],
                    'is_fixed' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }

            self::create([
                'name' => $unit['name'],
                'code' => $unit['code'],
                'is_fixed' => true,
            ]);
        }
    }

    public function label(): string
    {
        return $this->name.' ('.$this->code.')';
    }

    public function stockVarieties(): HasMany
    {
        return $this->hasMany(Plant::class, 'satuan_stok_id', 'seed_unit_id');
    }
}
