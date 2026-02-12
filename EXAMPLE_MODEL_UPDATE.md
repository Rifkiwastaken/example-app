# Contoh Update Model untuk Menggunakan Custom ID

Setelah migrasi selesai, Anda perlu mengupdate semua Model Laravel untuk menggunakan Trait `HasCustomId`.

## Contoh 1: Model Plant (Sebelum)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plant_type_id',
        'variety',
        'status',
        'progress',
        'planting_location_id',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PlantType::class, 'plant_type_id');
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id');
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }
}
```

## Contoh 1: Model Plant (Sesudah)

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory, HasCustomId;

    // Tentukan primary key yang baru
    protected $primaryKey = 'plant_id';

    // Tentukan tipe key
    protected $keyType = 'string';

    // Non-incrementing
    public $incrementing = false;

    protected $fillable = [
        'name',
        'plant_type_id',
        'variety',
        'status',
        'progress',
        'planting_location_id',
    ];

    // Optional: Override prefix jika ingin custom (default sudah ada di Trait)
    // public function getCustomIdPrefix(): string
    // {
    //     return 'PLT';
    // }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PlantType::class, 'plant_type_id', 'plant_type_id');
    }

    public function plantingLocation(): BelongsTo
    {
        return $this->belongsTo(PlantingLocation::class, 'planting_location_id', 'planting_location_id');
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class, 'plant_id', 'plant_id');
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'plant_id', 'plant_id');
    }
}
```

## Contoh 2: Model Sale (Sebelum)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
```

## Contoh 2: Model Sale (Sesudah)

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasCustomId;

    protected $primaryKey = 'sale_id';
    protected $keyType = 'string';
    public $incrementing = false;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }
}
```

## Contoh 3: Model User (Khusus)

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasCustomId;

    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'user_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'user_id');
    }
}
```

## Poin Penting saat Update Model:

1. **Tambahkan Trait**: `use HasCustomId;`

2. **Set Primary Key**: 
   ```php
   protected $primaryKey = 'nama_tabel_singular_id';
   ```

3. **Set Key Type**: 
   ```php
   protected $keyType = 'string';
   ```

4. **Disable Incrementing**: 
   ```php
   public $incrementing = false;
   ```

5. **Update Relationships**: Tambahkan parameter foreign key dan owner key
   ```php
   // BelongsTo
   return $this->belongsTo(Model::class, 'foreign_key', 'owner_key');
   
   // HasMany
   return $this->hasMany(Model::class, 'foreign_key', 'local_key');
   
   // BelongsToMany
   return $this->belongsToMany(Model::class, 'pivot_table', 'foreign_pivot_key', 'related_pivot_key', 'parent_key', 'related_key');
   ```

## Daftar Model yang Perlu Diupdate:

- [ ] User
- [ ] PlantType
- [ ] Plant
- [ ] PlantingLocation
- [ ] Planting
- [ ] Harvest
- [ ] PlantNote
- [ ] PlantPhoto
- [ ] PlantingLocationNote
- [ ] PlantingLocationPhoto
- [ ] PlantingLoss
- [ ] Location
- [ ] Nutrient
- [ ] Treatment
- [ ] Certification
- [ ] CertificationReport
- [ ] Warehouse
- [ ] Bin
- [ ] InventoryType
- [ ] InventoryLot
- [ ] InventoryTransaction
- [ ] InventoryTypeWarehouse (pivot)
- [ ] InventoryTypeSeed
- [ ] InventoryTypeCertificationReport (pivot)
- [ ] InventoryNote
- [ ] InventoryPhoto
- [ ] SeedHistory
- [ ] Sale
- [ ] SaleItem
- [ ] Task
- [ ] TaskSeries
- [ ] TaskTemplate
- [ ] Expense
- [ ] Attachment

## Testing Setelah Update:

```php
// Test create dengan auto-generate ID
$plant = Plant::create([
    'name' => 'Tomat Cherry',
    'plant_type_id' => 'PTY-ABC12345',
    'variety' => 'Cherry',
    'status' => 'perencanaan',
]);

echo $plant->plant_id; // Output: PLT-XYZ98765 (auto-generated)

// Test relationships
$plantType = $plant->type; // Harus berfungsi normal
$plantings = $plant->plantings; // Harus berfungsi normal
