# 🔧 CRITICAL FIX: Model Relationships

## ⚠️ Root Cause of Errors

Setelah migrasi ke custom string IDs, Laravel Eloquent relationships masih menggunakan default `id` column untuk joins. Ini menyebabkan error "Column not found: inventory_types.id".

## ✅ Solution Applied

### Fixed: InventoryType Model

File: `app/Models/InventoryType.php`

**All relationships updated to use explicit foreign keys:**

```php
// ✅ FIXED - hasMany relationships
public function lots(): HasMany
{
    return $this->hasMany(InventoryLot::class, 'inventory_type_id', 'inventory_type_id');
}

public function transactions(): HasMany
{
    return $this->hasMany(InventoryTransaction::class, 'inventory_type_id', 'inventory_type_id');
}

public function seeds(): HasMany
{
    return $this->hasMany(InventoryTypeSeed::class, 'inventory_type_id', 'inventory_type_id');
}

public function notes(): HasMany
{
    return $this->hasMany(InventoryNote::class, 'inventory_type_id', 'inventory_type_id');
}

public function photos(): HasMany
{
    return $this->hasMany(InventoryPhoto::class, 'inventory_type_id', 'inventory_type_id');
}

public function saleItems(): HasMany
{
    return $this->hasMany(SaleItem::class, 'inventory_type_id', 'inventory_type_id');
}

// ✅ FIXED - belongsTo relationships
public function responsiblePerson(): BelongsTo
{
    return $this->belongsTo(User::class, 'responsible_person_id', 'user_id');
}

public function plant(): BelongsTo
{
    return $this->belongsTo(Plant::class, 'plant_id', 'plant_id');
}

// ✅ FIXED - belongsToMany relationships
public function warehouses(): BelongsToMany
{
    return $this->belongsToMany(
        Warehouse::class, 
        'inventory_type_warehouses', 
        'inventory_type_id',      // foreign pivot key
        'warehouse_id',           // related pivot key
        'inventory_type_id',      // parent key
        'warehouse_id'            // related key
    )
    ->withPivot('bin_id', 'warehouse_only')
    ->withTimestamps();
}

public function certificationReports(): BelongsToMany
{
    return $this->belongsToMany(
        CertificationReport::class, 
        'inventory_type_certification_reports', 
        'inventory_type_id', 
        'certification_report_id', 
        'inventory_type_id', 
        'certification_report_id'
    )
    ->withPivot('quantity')
    ->withTimestamps();
}
```

## 📝 Relationship Syntax Reference

### hasMany()
```php
return $this->hasMany(
    RelatedModel::class,
    'foreign_key',  // FK column in related table
    'local_key'     // PK column in this table
);
```

### belongsTo()
```php
return $this->belongsTo(
    ParentModel::class,
    'foreign_key',  // FK column in this table
    'owner_key'     // PK column in parent table
);
```

### belongsToMany()
```php
return $this->belongsToMany(
    RelatedModel::class,
    'pivot_table',
    'foreign_pivot_key',  // FK to this model in pivot
    'related_pivot_key',  // FK to related model in pivot
    'parent_key',         // PK in this model
    'related_key'         // PK in related model
);
```

## 🔍 Why This Was Needed

**Before (Default Behavior):**
```php
// Laravel assumes parent table has 'id' column
public function seeds(): HasMany
{
    return $this->hasMany(InventoryTypeSeed::class);
}

// Generated SQL:
// SELECT * FROM inventory_type_seeds 
// WHERE inventory_type_id IN (
//     SELECT id FROM inventory_types  ← ERROR! 'id' doesn't exist
// )
```

**After (Explicit Keys):**
```php
// Explicitly tell Laravel to use custom ID
public function seeds(): HasMany
{
    return $this->hasMany(InventoryTypeSeed::class, 'inventory_type_id', 'inventory_type_id');
}

// Generated SQL:
// SELECT * FROM inventory_type_seeds 
// WHERE inventory_type_id IN (
//     SELECT inventory_type_id FROM inventory_types  ← CORRECT!
// )
```

## ⚠️ Other Models May Need Similar Fixes

If you encounter similar errors with other models, apply the same pattern:

### Models That May Need Fixing:
1. **Plant** - if errors with plantings, harvests, certifications
2. **Certification** - if errors with reports
3. **Warehouse** - if errors with bins, lots
4. **Sale** - if errors with sale items
5. **User** - if errors with created records
6. **PlantingLocation** - if errors with plantings, notes
7. **Planting** - if errors with harvests, nutrients, treatments

### How to Fix:
1. Open the model file (e.g., `app/Models/Plant.php`)
2. Find all relationship methods
3. Add explicit foreign keys using the patterns above
4. Clear cache: `php artisan cache:clear`
5. Test again

## 🎯 Testing After Fix

1. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

2. **Refresh the page** that was showing error

3. **Expected Result:** Page loads without SQL errors

4. **If still error:** Check which model/relationship is failing and fix that model

## 📊 Impact

- **Fixed**: InventoryType model (all 11 relationships)
- **Status**: Landing page should now work
- **Next**: Test other pages, fix models as needed

## 🔗 Related Files

- `app/Models/InventoryType.php` - FIXED ✅
- `app/Http/Controllers/LandingPageController.php` - Already fixed
- `fix_all_model_relationships.php` - Guide for fixing other models

---

**Last Updated**: February 2026  
**Status**: Critical fix applied, ready for testing
