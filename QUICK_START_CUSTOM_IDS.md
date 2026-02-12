# Quick Start Guide - Custom IDs

## 🚀 Untuk Developer

### Membuat Record Baru

Custom ID akan **otomatis ter-generate** saat membuat record baru:

```php
// Contoh 1: Create Plant
$plant = Plant::create([
    'name' => 'Tomat Cherry',
    'plant_type_id' => 'PTY-ABC12345',
    'status' => 'perencanaan'
]);

// plant_id otomatis: PLT-K9L2M3N4
echo $plant->plant_id;
```

```php
// Contoh 2: Create Sale
$sale = Sale::create([
    'receipt_number' => 'PJ-2026-001',
    'sale_date' => now(),
    'buyer_name' => 'John Doe',
    'total_amount' => 150000,
    'user_id' => 'USR-X5Y6Z7W8'
]);

// sale_id otomatis: SAL-P7Q8R9S0
```

### Mencari Record

```php
// Find by custom ID
$plant = Plant::find('PLT-8X92MKA1');

// Where clause
$plants = Plant::where('plant_type_id', 'PTY-ABC12345')->get();

// With relationships
$plant = Plant::with('type', 'plantings')->find('PLT-8X92MKA1');
```

### Update Record

```php
$plant = Plant::find('PLT-8X92MKA1');
$plant->update([
    'status' => 'ditanam',
    'progress' => 50
]);
```

### Delete Record

```php
$plant = Plant::find('PLT-8X92MKA1');
$plant->delete(); // Cascade delete akan bekerja normal
```

---

## 📋 Format ID Reference

| Model | Prefix | Contoh ID | Primary Key Column |
|-------|--------|-----------|-------------------|
| User | USR | USR-K3L9M2N4 | user_id |
| PlantType | PTY | PTY-A1B2C3D4 | plant_type_id |
| Plant | PLT | PLT-8X92MKA1 | plant_id |
| PlantingLocation | LOC | LOC-X5Y6Z7W8 | planting_location_id |
| Planting | PLN | PLN-M9N0P1Q2 | planting_id |
| Harvest | HRV | HRV-R3S4T5U6 | harvest_id |
| Certification | CRT | CRT-V7W8X9Y0 | certification_id |
| CertificationReport | CRP | CRP-Z1A2B3C4 | certification_report_id |
| Warehouse | WHS | WHS-D5E6F7G8 | warehouse_id |
| Bin | BIN | BIN-H9I0J1K2 | bin_id |
| InventoryType | INV | INV-L3M4N5O6 | inventory_type_id |
| InventoryLot | LOT | LOT-P7Q8R9S0 | inventory_lot_id |
| InventoryTransaction | TRX | TRX-T1U2V3W4 | inventory_transaction_id |
| Sale | SAL | SAL-B9C0D1E2 | sale_id |
| SaleItem | SIT | SIT-F3G4H5I6 | sale_item_id |
| Task | TSK | TSK-J7K8L9M0 | task_id |

---

## 🔧 Troubleshooting

### Problem: "Column 'id' not found"

**Cause**: Code masih menggunakan `id` lama

**Solution**: Update ke custom ID column
```php
// ❌ Wrong
$plant = Plant::find($request->id);

// ✅ Correct
$plant = Plant::find($request->plant_id);
```

### Problem: "Foreign key constraint fails"

**Cause**: Mencoba insert FK dengan ID yang tidak ada

**Solution**: Pastikan referenced record exists
```php
// Cek dulu apakah plant_type exists
$plantType = PlantType::find('PTY-ABC12345');
if (!$plantType) {
    return response()->json(['error' => 'Plant type not found'], 404);
}

$plant = Plant::create([
    'plant_type_id' => 'PTY-ABC12345',
    // ...
]);
```

### Problem: Custom ID tidak ter-generate

**Cause**: Model belum menggunakan trait

**Solution**: Tambahkan trait ke model
```php
use App\Traits\HasCustomId;

class YourModel extends Model
{
    use HasFactory;
    use HasCustomId; // ← Tambahkan ini
}
```

---

## 🎯 Best Practices

### 1. Selalu Gunakan Custom ID di Route

```php
// ✅ Good
Route::get('/plants/{plant_id}', [PlantController::class, 'show']);

// ❌ Avoid
Route::get('/plants/{id}', [PlantController::class, 'show']);
```

### 2. Validasi Format ID

```php
// Di FormRequest
public function rules()
{
    return [
        'plant_id' => ['required', 'regex:/^PLT-[A-Z0-9]{8}$/'],
        'plant_type_id' => ['required', 'regex:/^PTY-[A-Z0-9]{8}$/'],
    ];
}
```

### 3. Gunakan Route Model Binding

```php
// Di RouteServiceProvider atau routes
Route::bind('plant', function ($value) {
    return Plant::where('plant_id', $value)->firstOrFail();
});

// Di Controller
public function show(Plant $plant)
{
    return view('plants.show', compact('plant'));
}
```

### 4. API Response

```json
{
    "data": {
        "plant_id": "PLT-8X92MKA1",
        "name": "Tomat Cherry",
        "plant_type_id": "PTY-ABC12345",
        "plant_type": {
            "plant_type_id": "PTY-ABC12345",
            "name": "Tomat"
        }
    }
}
```

---

## 📝 Migration Checklist untuk Tabel Baru

Jika menambah tabel baru di masa depan:

- [ ] Tambahkan prefix di `HasCustomId::getCustomIdPrefix()`
- [ ] Gunakan VARCHAR(36) untuk PK di migration
- [ ] Set custom ID sebagai PRIMARY KEY
- [ ] Gunakan trait `HasCustomId` di model
- [ ] Set `protected $primaryKey = 'table_name_id'` di model
- [ ] Update foreign keys di tabel lain jika perlu

**Contoh Migration:**
```php
Schema::create('new_table', function (Blueprint $table) {
    $table->string('new_table_id', 36)->primary();
    $table->string('related_table_id', 36)->nullable();
    $table->foreign('related_table_id')
          ->references('related_table_id')
          ->on('related_table')
          ->onDelete('cascade');
    $table->timestamps();
});
```

**Contoh Model:**
```php
use App\Traits\HasCustomId;

class NewTable extends Model
{
    use HasFactory;
    use HasCustomId;
    
    protected $primaryKey = 'new_table_id';
    
    protected $fillable = [
        'related_table_id',
        // ...
    ];
}
```

---

## 🧪 Testing

### Unit Test Example

```php
public function test_plant_creates_with_custom_id()
{
    $plant = Plant::create([
        'name' => 'Test Plant',
        'status' => 'perencanaan'
    ]);
    
    $this->assertNotNull($plant->plant_id);
    $this->assertMatchesRegularExpression(
        '/^PLT-[A-Z0-9]{8}$/', 
        $plant->plant_id
    );
}

public function test_plant_relationships_work()
{
    $plantType = PlantType::factory()->create();
    $plant = Plant::factory()->create([
        'plant_type_id' => $plantType->plant_type_id
    ]);
    
    $this->assertEquals(
        $plantType->plant_type_id, 
        $plant->type->plant_type_id
    );
}
```

---

## 📞 Support

Jika ada masalah:
1. Cek dokumentasi lengkap di `MIGRATION_SUCCESS_REPORT.md`
2. Review trait di `app/Traits/HasCustomId.php`
3. Lihat contoh di models yang sudah ada

---

**Last Updated**: February 2026
**Status**: ✅ Production Ready
