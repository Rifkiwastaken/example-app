# Troubleshooting Post-Migration Errors

## Common Error: "Column not found: 1054 Unknown column 'table.id'"

### Cause
Code masih menggunakan kolom `id` lama yang sudah dihapus saat migrasi Phase 3.

### Solution Steps

#### 1. Identify the Error Location
Error message akan menunjukkan:
- File path (e.g., `app/Http/Controllers/LandingPageController.php`)
- Line number
- SQL query yang error

#### 2. Common Patterns to Fix

**Pattern 1: Model Property Access**
```php
// ❌ Wrong
$model->id

// ✅ Correct
$model->model_id  // Sesuaikan dengan nama tabel
```

**Pattern 2: Where Clauses**
```php
// ❌ Wrong
where('id', $value)
where('certification_id', $certification->id)
whereIn('id', $ids)

// ✅ Correct
where('model_id', $value)
where('certification_id', $certification->certification_id)
whereIn('model_id', $ids)
```

**Pattern 3: Pluck/Select**
```php
// ❌ Wrong
pluck('id')
select('id', 'name')

// ✅ Correct
pluck('model_id')
select('model_id', 'name')
```

**Pattern 4: Foreign Key References**
```php
// ❌ Wrong
InventoryLot::where('inventory_type_id', $type->id)
Warehouse::whereIn('id', $warehouseIds)

// ✅ Correct
InventoryLot::where('inventory_type_id', $type->inventory_type_id)
Warehouse::whereIn('warehouse_id', $warehouseIds)
```

#### 3. Quick Reference: Table → Custom ID Column

| Table | Old PK | New PK |
|-------|--------|--------|
| users | id | user_id |
| plants | id | plant_id |
| plant_types | id | plant_type_id |
| plantings | id | planting_id |
| planting_locations | id | planting_location_id |
| harvests | id | harvest_id |
| certifications | id | certification_id |
| certification_reports | id | certification_report_id |
| warehouses | id | warehouse_id |
| bins | id | bin_id |
| inventory_types | id | inventory_type_id |
| inventory_lots | id | inventory_lot_id |
| inventory_transactions | id | inventory_transaction_id |
| inventory_type_seeds | id | inventory_type_seed_id |
| sales | id | sale_id |
| sale_items | id | sale_item_id |
| tasks | id | task_id |
| expenses | id | expense_id |
| nutrients | id | nutrient_id |
| attachments | id | attachment_id |

### Automated Fix Script

Run this script to find all old `id` references:

```bash
php find_old_id_references.php
```

This will generate `old_id_references.json` with all findings.

### Manual Fix Process

1. **Open the file** mentioned in error
2. **Go to the line number** shown in error
3. **Identify the model** being queried
4. **Replace `id`** with appropriate custom ID column
5. **Test the page** again

### Example Fix: LandingPageController

**Error:**
```
SQLSTATE[42522]: Column not found: 1054 Unknown column 'inventory_types.id'
```

**Location:**
```php
// Line 81
$warehouseIds = InventoryLot::where('inventory_type_id', $type->id)
```

**Fix:**
```php
// Replace $type->id with $type->inventory_type_id
$warehouseIds = InventoryLot::where('inventory_type_id', $type->inventory_type_id)
```

### Testing After Fix

1. Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

2. Refresh the page

3. Check for new errors

4. Repeat until all errors fixed

### Prevention

To prevent these errors in new code:

1. **Always use custom ID columns** in queries
2. **Use model relationships** instead of manual queries when possible
3. **Test thoroughly** after any database changes
4. **Use IDE autocomplete** to ensure correct column names

### Common Locations to Check

- Controllers (especially index/show methods)
- API Resources
- Blade views (especially forms and links)
- Seeders
- Tests

### If Stuck

1. Check `QUICK_START_CUSTOM_IDS.md` for examples
2. Review `MIGRATION_SUCCESS_REPORT.md` for complete mapping
3. Run `php find_old_id_references.php` to find all issues
4. Ask for help with specific error message

---

**Last Updated**: February 2026
