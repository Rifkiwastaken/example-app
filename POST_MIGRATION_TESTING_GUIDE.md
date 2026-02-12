# 📋 Post-Migration Testing Guide

## ✅ What Has Been Completed

### Database Migration
- ✅ Phase 1: Added custom ID columns
- ✅ Phase 2: Migrated 153 records with custom IDs
- ✅ Phase 3: Finalized structure (29/30 tables)

### Code Updates
- ✅ 30 Models updated with HasCustomId trait
- ✅ 255 Controller references fixed
- ✅ 290 View references fixed
- ✅ Cache cleared

### Total Fixes
- **545+ automatic code fixes applied**
- **All major components updated**

---

## 🚀 How to Start Testing

### 1. Start the Development Server

```bash
# Make sure you're in the project directory
cd d:/cklipsi/9 des/sibesti

# Start Laravel development server
php artisan serve
```

The server should start at: `http://localhost:8000`

### 2. Open Your Browser

Navigate to: `http://localhost:8000`

---

## 🧪 Testing Checklist

### Critical Pages (Test First)

#### ✅ Landing Page
- [ ] Open `http://localhost:8000`
- [ ] Should load without errors
- [ ] Check if inventory types display correctly
- [ ] Check if certifications display correctly

**Expected Result**: Page loads successfully, no SQL errors

**If Error**: Check `app/Http/Controllers/LandingPageController.php` - already fixed

---

#### ✅ Dashboard
- [ ] Login to the application
- [ ] Navigate to `/dashboard`
- [ ] Check all widgets load
- [ ] Check charts display correctly
- [ ] Check recent activities

**Expected Result**: Dashboard loads with all data

**If Error**: Check `app/Http/Controllers/DashboardController.php` - already fixed

---

#### ✅ Plants Management
- [ ] Go to `/plants`
- [ ] View list of plants
- [ ] Click on a plant to view details
- [ ] Try to create a new plant
- [ ] Try to edit an existing plant

**Expected Result**: All CRUD operations work

**Common Issues**:
- If error mentions `plant_id`, check the specific controller method
- New plants should get custom IDs like `PLT-XXXXXXXX`

---

#### ✅ Plantings
- [ ] Go to `/plantings`
- [ ] View list of plantings
- [ ] Create new planting
- [ ] View planting details
- [ ] Edit planting

**Expected Result**: All operations work, relationships intact

**Check**: Planting → Plant relationship works

---

#### ✅ Harvests
- [ ] Go to `/harvests`
- [ ] View harvest list
- [ ] Create new harvest
- [ ] Link harvest to planting

**Expected Result**: Harvest creation works, FK relationships maintained

---

#### ✅ Certifications
- [ ] Go to `/certifications`
- [ ] View certification list
- [ ] Create new certification
- [ ] Add certification report
- [ ] Link to inventory types

**Expected Result**: All certification operations work

**Check**: Certification → Plant → InventoryType relationships

---

#### ✅ Inventory/Warehouse
- [ ] Go to `/seed-stock`
- [ ] View inventory types
- [ ] Create new inventory type
- [ ] Add seeds
- [ ] Check stock levels

**Expected Result**: Inventory management works

**Check**: InventoryType → Seeds → Lots relationships

---

#### ✅ Sales
- [ ] Go to `/sales`
- [ ] View sales list
- [ ] Create new sale
- [ ] Add sale items
- [ ] Check inventory deduction

**Expected Result**: Sales process works, inventory updates

---

### Secondary Pages

#### Reports
- [ ] Production reports
- [ ] Stock reports
- [ ] Sales reports
- [ ] Certification reports

#### User Management
- [ ] View users
- [ ] Edit user (not yourself)
- [ ] Check permissions

---

## 🔍 What to Look For

### ✅ Success Indicators
- Pages load without errors
- Data displays correctly
- Forms submit successfully
- Relationships work (e.g., Plant → Plantings)
- New records get custom IDs (e.g., `PLT-8X92MKA1`)

### ❌ Error Indicators
- SQL errors mentioning "Column not found"
- Errors mentioning `id` column
- Blank pages
- 500 Internal Server Error
- Relationship errors

---

## 🐛 If You Find Errors

### Step 1: Identify the Error

Take note of:
1. **Page/URL** where error occurred
2. **Action** you were performing (view, create, edit, delete)
3. **Error message** (especially SQL errors)

### Step 2: Check Error Details

Common error patterns:

**Pattern 1: Column not found**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'table.id'
```

**Solution**: The query is still using old `id` column
- Find the controller method mentioned in error
- Replace `id` with appropriate custom ID column

**Pattern 2: Relationship error**
```
Call to undefined relationship
```

**Solution**: Check model relationships use correct FK columns

### Step 3: Use Troubleshooting Tools

We have created several tools to help:

1. **Find remaining issues**:
   ```bash
   php find_old_id_references.php
   ```

2. **Check specific file**:
   Open the file mentioned in error and search for `->id` or `where('id'`

3. **Reference guide**:
   Check `TROUBLESHOOTING_POST_MIGRATION.md` for common fixes

### Step 4: Fix the Issue

**Quick Reference - Common Fixes**:

```php
// ❌ Wrong
$plant->id
where('id', $value)
whereIn('id', $ids)
pluck('id')

// ✅ Correct
$plant->plant_id
where('plant_id', $value)
whereIn('plant_id', $ids)
pluck('plant_id')
```

**Table → Custom ID Mapping**:
- plants → plant_id
- users → user_id
- plantings → planting_id
- harvests → harvest_id
- certifications → certification_id
- inventory_types → inventory_type_id
- warehouses → warehouse_id
- sales → sale_id

### Step 5: Clear Cache & Retry

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

Then refresh the page.

---

## 📊 Testing Progress Tracker

Use this to track your testing:

```
Landing Page:        [ ] Tested  [ ] Working  [ ] Issues Found: _______
Dashboard:           [ ] Tested  [ ] Working  [ ] Issues Found: _______
Plants List:         [ ] Tested  [ ] Working  [ ] Issues Found: _______
Plants Create:       [ ] Tested  [ ] Working  [ ] Issues Found: _______
Plants Edit:         [ ] Tested  [ ] Working  [ ] Issues Found: _______
Plantings List:      [ ] Tested  [ ] Working  [ ] Issues Found: _______
Plantings Create:    [ ] Tested  [ ] Working  [ ] Issues Found: _______
Harvests List:       [ ] Tested  [ ] Working  [ ] Issues Found: _______
Harvests Create:     [ ] Tested  [ ] Working  [ ] Issues Found: _______
Certifications:      [ ] Tested  [ ] Working  [ ] Issues Found: _______
Inventory Types:     [ ] Tested  [ ] Working  [ ] Issues Found: _______
Warehouses:          [ ] Tested  [ ] Working  [ ] Issues Found: _______
Sales:               [ ] Tested  [ ] Working  [ ] Issues Found: _______
Reports:             [ ] Tested  [ ] Working  [ ] Issues Found: _______
User Management:     [ ] Tested  [ ] Working  [ ] Issues Found: _______
```

---

## 🎯 Success Criteria

Migration is considered successful when:

- [ ] All critical pages load without errors
- [ ] All CRUD operations work (Create, Read, Update, Delete)
- [ ] All relationships work correctly
- [ ] New records get custom IDs automatically
- [ ] No SQL "Column not found" errors
- [ ] Data integrity maintained

---

## 📞 Need Help?

### Documentation Available
1. **FINAL_MIGRATION_SUMMARY.md** - Complete overview
2. **TROUBLESHOOTING_POST_MIGRATION.md** - Error fixing guide
3. **QUICK_START_CUSTOM_IDS.md** - Developer reference
4. **MIGRATION_SUCCESS_REPORT.md** - Technical details

### Tools Available
- `find_old_id_references.php` - Find remaining old ID references
- `verify_migration_complete.php` - Verify database structure
- `test_endpoints.php` - Automated endpoint testing (requires server running)

### Common Issues & Solutions

**Issue**: "Column 'id' not found"
**Solution**: Replace `->id` with `->model_id` in the mentioned file

**Issue**: "Relationship not found"
**Solution**: Check model relationships use correct FK columns

**Issue**: "Unique constraint violation"
**Solution**: Custom IDs should be unique, check HasCustomId trait

**Issue**: "New records not getting custom IDs"
**Solution**: Ensure model uses HasCustomId trait

---

## ✅ Final Checklist

Before considering migration complete:

- [ ] All critical pages tested
- [ ] All CRUD operations tested
- [ ] All relationships verified
- [ ] New record creation tested
- [ ] No SQL errors found
- [ ] Performance acceptable
- [ ] Data integrity verified

---

## 🎉 When Testing is Complete

If all tests pass:

1. **Document any issues found and fixed**
2. **Update this guide with any new findings**
3. **Consider migration SUCCESSFUL**
4. **Deploy to production** (after backup!)

---

**Good luck with testing!** 🚀

The migration infrastructure is solid. Most issues (if any) will be minor reference fixes that can be quickly resolved using the tools and guides provided.
