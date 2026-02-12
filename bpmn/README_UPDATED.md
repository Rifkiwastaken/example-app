# Update Diagram BPMN - Penjelasan Bahasa Sistem

## Perubahan yang Dilakukan

### 1. Penjelasan dalam Bahasa Sistem
Semua activity dan task sekarang menggunakan terminologi sistem:
- **Request/Response**: Menunjukkan HTTP request dan response
- **Controller::method()**: Menunjukkan method controller yang dipanggil
- **Model::method()**: Menunjukkan method model yang dipanggil
- **SQL Operations**: Menunjukkan operasi database yang dilakukan

### 2. Detail Interaksi Database
Setiap activity yang berinteraksi dengan database memiliki:
- **Documentation tag**: Menjelaskan operasi database yang dilakukan
- **SQL Query**: Menunjukkan query SQL yang dieksekusi
- **Tujuan**: Menjelaskan tujuan operasi database

### 3. Sequence Flow yang Jelas
Semua sequence flow:
- Terhubung dengan benar dari start event hingga end event
- Memiliki label yang jelas (Ya/Tidak, Valid/Invalid, dll)
- Menunjukkan alur eksekusi yang benar

## Format Activity

### User Task
Format: `User: [Action]`
Contoh: `User: Input Data Penanaman`

### Service Task (Database Operations)
Format: `Database: [Operation] [Table]`
Contoh: `Database: INSERT INTO plantings`

### Service Task (System Logic)
Format: `Logic: [Description]`
Contoh: `Logic: Check is_completed OR days_to_harvest reached`

### Service Task (Controller)
Format: `Request: [HTTP Method] [Route]`
Contoh: `Request: POST /plantings`

## Detail Database Operations

Untuk detail lengkap operasi database, lihat file:
- **DOKUMENTASI_DATABASE_OPERATIONS.md** - Dokumentasi lengkap semua operasi database

## Cara Membaca Diagram

1. **Start Event**: Menunjukkan entry point (HTTP request)
2. **User Task**: Aktivitas yang memerlukan input dari user
3. **Service Task**: Aktivitas sistem (database, logic, dll)
4. **Gateway**: Decision point (conditional flow)
5. **End Event**: Exit point (response ke user)

## Contoh Alur

```
Start Event (Request)
    ↓
Service Task (Query Database)
    ↓
User Task (Render Form)
    ↓
User Task (User Input)
    ↓
Service Task (Validasi)
    ↓
Gateway (Valid?)
    ├─ Ya → Service Task (INSERT Database) → End Event (Success)
    └─ Tidak → End Event (Error)
```

## File yang Diupdate

1. **01_penanaman.bpmn** - ✅ Updated dengan detail database
2. **02_sertifikasi.bpmn** - ⏳ Akan diupdate
3. **03_manajemen_stok_gudang.bpmn** - ⏳ Akan diupdate
4. **04_penjualan.bpmn** - ⏳ Akan diupdate

## Catatan

- Semua diagram menggunakan format BPMN 2.0 standar
- Diagram dapat dibuka dengan Camunda Modeler, Bizagi, atau tool BPMN lainnya
- Sequence flow sudah terhubung dengan benar dan akan muncul saat dibuka di tool BPMN
















