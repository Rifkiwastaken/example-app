# TODO: Database Migration to Custom String IDs

## 📋 Status Migrasi

**Status Saat Ini:** ⏸️ Belum Dimulai

**Tanggal Rencana Eksekusi:** _[Isi tanggal]_

**PIC:** _[Isi nama]_

---

## ✅ Checklist Persiapan

### Dokumentasi & Pemahaman
- [ ] Baca dan pahami `MIGRATION_STRATEGY_CUSTOM_IDS.md`
- [ ] Baca dan pahami `EXECUTION_GUIDE.md`
- [ ] Baca dan pahami `EXAMPLE_MODEL_UPDATE.md`
- [ ] Review semua file migrasi di `database/migrations/phase_1/`
- [ ] Review semua file migrasi di `database/migrations/phase_2/`
- [ ] Review semua file migrasi di `database/migrations/phase_3/`
- [ ] Review `app/Traits/HasCustomId.php`

### Backup & Safety
- [ ] Backup database production (simpan di lokasi aman)
- [ ] Backup semua file aplikasi (git commit)
- [ ] Verifikasi backup bisa di-restore
- [ ] Siapkan environment development untuk testing
- [ ] Clone database production ke development

### Testing di Development
- [ ] Jalankan Fase 1 di development
- [ ] Verifikasi Fase 1 berhasil
- [ ] Jalankan Fase 2 di development
- [ ] Verifikasi Fase 2 berhasil
- [ ] Jalankan Fase 3 di development
- [ ] Verifikasi Fase 3 berhasil
- [ ] Update minimal 3 model untuk testing
- [ ] Test CRUD operations
- [ ] Test relationships
- [ ] Catat semua issue yang ditemukan
- [ ] Perbaiki semua issue

### Koordinasi
- [ ] Informasikan ke semua stakeholder tentang rencana maintenance
- [ ] Tentukan waktu maintenance (pilih waktu dengan traffic rendah)
- [ ] Siapkan announcement untuk user
- [ ] Siapkan rollback plan jika gagal

---

## 🚀 Eksekusi di Production

### Pre-Migration
- [ ] Backup database production (final backup)
- [ ] Aktifkan maintenance mode: `php artisan down`
- [ ] Verifikasi tidak ada user yang aktif
- [ ] Verifikasi tidak ada background job yang berjalan
- [ ] Catat waktu mulai: _[Isi waktu]_

### Fase 1: Tambah Kolom Baru
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_1/2026_02_10_000001_phase1_add_custom_id_columns_core.php`
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_1/2026_02_10_000002_phase1_add_custom_id_columns_certification.php`
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_1/2026_02_10_000003_phase1_add_custom_id_columns_inventory.php`
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_1/2026_02_10_000004_phase1_add_custom_id_columns_sales.php`
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_1/2026_02_10_000005_phase1_add_custom_id_columns_support.php`
- [ ] Verifikasi kolom baru sudah ditambahkan (cek dengan SQL)
- [ ] Catat waktu selesai Fase 1: _[Isi waktu]_

### Fase 2: Migrasi Data
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_2/2026_02_10_100001_phase2_migrate_data_core.php`
- [ ] Monitor progress (lihat output di console)
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_2/2026_02_10_100002_phase2_migrate_data_remaining.php`
- [ ] Monitor progress (lihat output di console)
- [ ] Verifikasi custom ID sudah terisi semua
- [ ] Verifikasi FK baru sudah terupdate dengan benar
- [ ] Verifikasi tidak ada data NULL
- [ ] Verifikasi tidak ada FK yang tidak match
- [ ] Catat waktu selesai Fase 2: _[Isi waktu]_

### Fase 3: Finalisasi
- [ ] **CHECKPOINT:** Pastikan Fase 1 & 2 100% berhasil sebelum lanjut!
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_3/2026_02_10_200001_phase3_finalize_core.php`
- [ ] Monitor progress (lihat output di console)
- [ ] Jalankan: `php artisan migrate --path=database/migrations/phase_3/2026_02_10_200002_phase3_finalize_remaining.php`
- [ ] Monitor progress (lihat output di console)
- [ ] Verifikasi kolom 'id' lama sudah dihapus
- [ ] Verifikasi kolom custom ID sudah jadi Primary Key
- [ ] Verifikasi FK sudah menggunakan custom ID
- [ ] Catat waktu selesai Fase 3: _[Isi waktu]_

---

## 🔧 Update Aplikasi

### Update Models (Core)
- [ ] Update `app/Models/User.php`
- [ ] Update `app/Models/PlantType.php`
- [ ] Update `app/Models/Plant.php`
- [ ] Update `app/Models/PlantingLocation.php`
- [ ] Update `app/Models/Planting.php`
- [ ] Update `app/Models/Harvest.php`
- [ ] Update `app/Models/PlantNote.php`
- [ ] Update `app/Models/PlantPhoto.php`
- [ ] Update `app/Models/PlantingLocationNote.php`
- [ ] Update `app/Models/PlantingLocationPhoto.php`
- [ ] Update `app/Models/PlantingLoss.php`
- [ ] Update `app/Models/Location.php`
- [ ] Update `app/Models/Nutrient.php`
- [ ] Update `app/Models/Treatment.php`

### Update Models (Certification)
- [ ] Update `app/Models/Certification.php`
- [ ] Update `app/Models/CertificationReport.php`

### Update Models (Inventory)
- [ ] Update `app/Models/Warehouse.php` (jika ada)
- [ ] Update `app/Models/Bin.php`
- [ ] Update `app/Models/InventoryType.php`
- [ ] Update `app/Models/InventoryLot.php`
- [ ] Update `app/Models/InventoryTransaction.php`
- [ ] Update `app/Models/InventoryTypeSeed.php`
- [ ] Update `app/Models/InventoryNote.php`
- [ ] Update `app/Models/InventoryPhoto.php`
- [ ] Update `app/Models/SeedHistory.php`

### Update Models (Sales)
- [ ] Update `app/Models/Sale.php`
- [ ] Update `app/Models/SaleItem.php`

### Update Models (Support)
- [ ] Update `app/Models/Task.php`
- [ ] Update `app/Models/TaskSeries.php`
- [ ] Update `app/Models/TaskTemplate.php`
- [ ] Update `app/Models/Expense.php`
- [ ] Update `app/Models/Attachment.php`

### Update Controllers (Jika Perlu)
- [ ] Review semua Controller yang menggunakan `->id`
- [ ] Update ke `->plant_id`, `->user_id`, dll sesuai model
- [ ] Review semua route parameter binding
- [ ] Update jika ada hardcoded reference ke 'id'

### Update Views (Jika Perlu)
- [ ] Review semua view yang menampilkan ID
- [ ] Update dari `$model->id` ke `$model->plant_id` (sesuai model)
- [ ] Review semua form yang submit ID
- [ ] Update hidden input dari 'id' ke custom ID

---

## 🧪 Testing Post-Migration

### Basic CRUD Testing
- [ ] Test Create Plant (auto-generate ID)
- [ ] Test Read Plant (by custom ID)
- [ ] Test Update Plant
- [ ] Test Delete Plant
- [ ] Test Create User
- [ ] Test Create Sale
- [ ] Test Create Inventory

### Relationship Testing
- [ ] Test Plant → PlantType relationship
- [ ] Test Plant → Plantings relationship
- [ ] Test Plant → Harvests relationship
- [ ] Test Sale → SaleItems relationship
- [ ] Test User → Tasks relationship
- [ ] Test InventoryLot → InventoryType relationship

### Advanced Testing
- [ ] Test pagination
- [ ] Test search/filter
- [ ] Test export (jika ada)
- [ ] Test import (jika ada)
- [ ] Test API endpoints (jika ada)
- [ ] Test authentication
- [ ] Test authorization

### Performance Testing
- [ ] Cek query performance (EXPLAIN)
- [ ] Cek page load time
- [ ] Cek memory usage
- [ ] Cek database size

---

## 🎯 Post-Migration

### Cleanup
- [ ] Hapus file migration lama (optional, backup dulu)
- [ ] Update dokumentasi database
- [ ] Update ERD jika ada
- [ ] Commit semua perubahan ke git

### Monitoring
- [ ] Monitor error log selama 24 jam pertama
- [ ] Monitor performance selama 1 minggu
- [ ] Kumpulkan feedback dari user
- [ ] Catat semua issue yang muncul

### Documentation
- [ ] Update README.md
- [ ] Update API documentation (jika ada)
- [ ] Update user manual (jika ada)
- [ ] Buat catatan lessons learned

---

## 📊 Metrics & Results

### Migration Time
- Fase 1: _[Isi durasi]_
- Fase 2: _[Isi durasi]_
- Fase 3: _[Isi durasi]_
- Update Models: _[Isi durasi]_
- Testing: _[Isi durasi]_
- **Total Downtime:** _[Isi durasi]_

### Database Stats
- Jumlah tabel yang dimigrasi: _[Isi jumlah]_
- Jumlah record yang dimigrasi: _[Isi jumlah]_
- Database size sebelum: _[Isi size]_
- Database size sesudah: _[Isi size]_

### Issues Found
- [ ] _[List semua issue yang ditemukan]_
- [ ] _[Status penyelesaian]_

---

## 🔄 Rollback Plan (Jika Diperlukan)

### Jika Gagal di Fase 1 atau 2
- [ ] Rollback migration: `php artisan migrate:rollback`
- [ ] Restore dari backup jika perlu
- [ ] Analisis error
- [ ] Perbaiki issue
- [ ] Retry

### Jika Gagal di Fase 3 atau Setelahnya
- [ ] **STOP semua operasi**
- [ ] Restore database dari backup
- [ ] Revert semua perubahan code (git)
- [ ] Restart aplikasi
- [ ] Analisis root cause
- [ ] Buat plan perbaikan
- [ ] Reschedule migration

---

## 📝 Notes & Observations

_[Gunakan section ini untuk mencatat apapun selama proses migrasi]_

### Issues Encountered
- 

### Solutions Applied
- 

### Lessons Learned
- 

### Recommendations for Future
- 

---

**Last Updated:** _[Tanggal]_

**Status:** _[Belum Dimulai / Dalam Progress / Selesai / Gagal]_
