# Diagram UML - Aplikasi SIBIT

Folder ini berisi diagram UML untuk aplikasi SIBIT (Sistem Informasi Benih Terintegrasi), terdiri dari:
- **Use Case Diagram** - Menunjukkan aktor dan use case sistem
- **Sequence Diagram** - Menunjukkan interaksi antar komponen sistem
- **Activity Diagram** - Menunjukkan alur aktivitas pengguna dan sistem

## Daftar File

### Use Case Diagram
1. **00_use_case_overview.puml** - Use case diagram overview seluruh sistem dengan semua aktor (Admin, Kepala Satuan Tugas, Penangkar, Petugas Gudang, Petugas BBI) dan semua modul (Penanaman, Sertifikasi, Gudang/Stok, Penjualan, Laporan, Manajemen User)
2. **01_use_case_penanaman.puml** - Use case diagram detail untuk modul Penanaman
3. **02_use_case_sertifikasi.puml** - Use case diagram detail untuk modul Sertifikasi
4. **03_use_case_manajemen_stok.puml** - Use case diagram detail untuk modul Manajemen Stok dan Gudang
5. **04_use_case_penjualan.puml** - Use case diagram detail untuk modul Penjualan

### Sequence Diagram
Sequence diagram ini menggambarkan interaksi antar objek untuk setiap use case yang ada di use case diagram:

1. **00_sequence_overview.puml** - Sequence diagram overview alur keseluruhan sistem (dari penanaman hingga penjualan)
2. **01_sequence_penanaman.puml** - Sequence diagram untuk modul Penanaman
   - Lihat Daftar Penanaman
   - Input Data Penanaman
   - Edit Data Penanaman
   - Hapus Data Penanaman
   - Trading Perkembangan Tanaman
   - Input Data Panen
3. **02_sequence_sertifikasi.puml** - Sequence diagram untuk modul Sertifikasi
   - Lihat Daftar Sertifikasi
   - Buat Laporan Pemeriksaan
   - Upload Dokumen Scan
   - Edit Laporan Pemeriksaan
   - Evaluasi Hasil Pemeriksaan
   - Pilih hasil Panen
4. **03_sequence_manajemen_stok.puml** - Sequence diagram untuk modul Manajemen Stok dan Gudang
   - Lihat Daftar Stok
   - Tambah Stok Baru
   - Kelola Gudang
   - Kelola Bin
   - Adjustment Stok
   - Lihat History Transaksi
5. **04_sequence_penjualan.puml** - Sequence diagram untuk modul Penjualan
   - Lihat Daftar Penjualan
   - Input Data Penjualan
   - Pilih List untuk dijual
   - Proses Penjualan (dengan metode FIFO)
   - Batalkan Penjualan
6. **05_sequence_manajemen_user.puml** - Sequence diagram untuk modul Manajemen User
   - Kelola User (CRUD)
   - Kelola Role

### Activity Diagram
Activity diagram ini menggambarkan alur aktivitas untuk setiap use case yang ada di use case diagram:

1. **01_activity_penanaman.puml** - Activity diagram untuk modul Penanaman
   - Lihat Daftar Penanaman
   - Input Data Penanaman
   - Edit Data Penanaman
   - Hapus Data Penanaman
   - Trading Perkembangan Tanaman
   - Input Data Panen

2. **02_activity_sertifikasi.puml** - Activity diagram untuk modul Sertifikasi
   - Lihat Daftar Sertifikasi
   - Buat Laporan Pemeriksaan
   - Upload Dokumen Scan
   - Edit Laporan Pemeriksaan
   - Evaluasi Hasil Pemeriksaan
   - Pilih hasil Panen

3. **03_activity_manajemen_stok.puml** - Activity diagram untuk modul Manajemen Stok dan Gudang
   - Lihat Daftar Stok
   - Tambah Stok Baru
   - Kelola Gudang
   - Kelola Bin
   - Adjustment Stok
   - Lihat History Transaksi

4. **04_activity_penjualan.puml** - Activity diagram untuk modul Penjualan
   - Lihat Daftar Penjualan
   - Input Data Penjualan
   - Pilih List untuk dijual
   - Proses Penjualan
   - Batalkan Penjualan

5. **05_activity_manajemen_user.puml** - Activity diagram untuk modul Manajemen User
   - Kelola User
   - Kelola Role

### Class Diagram
1. **05_class_diagram.puml** - Class diagram domain model SIBESTI dengan:
   - **Model dikelompokkan per modul**: User & Lokasi, Modul Penanaman, Modul Sertifikasi, Modul Gudang & Inventori, Modul Penjualan, Modul Tugas, Pendukung.
   - **Relasi antar objek**: dalam bentuk panah saja (tanpa label cardinality). Arah panah: kelas yang memiliki FK mengarah ke kelas yang direferensi.
   - Update: Februari 2026.

## Format File

File menggunakan format **PlantUML** (.puml), yang merupakan format text-based untuk membuat diagram UML. Format ini mudah di-edit dan dapat di-render ke berbagai format output.

## Cara Menggunakan

### 1. Online (Paling Mudah)
- Buka http://www.plantuml.com/plantuml/uml/
- Copy-paste isi file .puml
- Diagram akan langsung ter-render
- Bisa di-download sebagai PNG, SVG, atau format lain

### 2. VS Code Extension
- Install extension "PlantUML" di VS Code
- File .puml akan otomatis ter-render sebagai preview
- Bisa export ke PNG/SVG langsung dari VS Code

### 3. Command Line (PlantUML)
- Install PlantUML: http://plantuml.com/download
- Jalankan: `java -jar plantuml.jar file.puml`
- Akan menghasilkan file PNG/SVG

### 4. Online Tools Lain
- **PlantText**: https://www.planttext.com/
- **PlantUML Server**: http://www.plantuml.com/plantuml/
- **Draw.io**: Import PlantUML (File > Import from > PlantUML)

## Deskripsi Diagram

### 00_sequence_overview.puml
**Overview Alur Keseluruhan Sistem**

Menunjukkan alur lengkap dari penanaman hingga penjualan:
1. Penanaman → Data penanaman disimpan
2. Panen → Hasil panen dicatat
3. Sertifikasi → Benih disertifikasi (LULUS/TIDAK LULUS)
4. Stok → Benih lulus ditambahkan ke gudang
5. Penjualan → Stok dikurangi menggunakan FIFO

**Aktor**: User
**Participant**: PlantingController, HarvestController, CertificationController, InventoryTypeController, SaleController, Database

### 01_sequence_penanaman.puml
**Modul Penanaman**

Menunjukkan interaksi untuk:
- Input data penanaman
- Melihat daftar penanaman
- Update data penanaman
- Hapus data penanaman

**Aktor**: User
**Participant**: PlantingController, Plant Model, PlantingLocation Model, Planting Model, Database

**Key Interactions**:
- Validasi data penanaman
- Relasi dengan Plant dan PlantingLocation
- Pagination untuk daftar penanaman

### 02_sequence_sertifikasi.puml
**Modul Sertifikasi**

Menunjukkan interaksi untuk:
- Buat sertifikasi dari panen
- Buat laporan pemeriksaan
- Evaluasi hasil (LULUS/TIDAK LULUS)
- Tambahkan benih lulus ke stok

**Aktor**: User
**Participant**: CertificationController, Harvest Model, Certification Model, CertificationReport Model, Plant Model, PlantingLocation Model, InventoryType Model, Database, Storage

**Key Interactions**:
- FirstOrCreate untuk Certification
- Upload file scan dokumen
- Update status berdasarkan conclusion
- Link ke InventoryType untuk stok

### 03_sequence_manajemen_stok.puml
**Modul Manajemen Stok dan Gudang**

Menunjukkan interaksi untuk:
- Setup gudang dan bin
- Buat tipe inventaris
- Tambah benih ke stok (dari sertifikasi/manual)
- Tambah lot ke bin
- Kurangi stok
- Penyesuaian stok
- Monitoring stok

**Aktor**: User
**Participant**: WarehouseController, InventoryTypeController, Warehouse Model, Bin Model, InventoryType Model, InventoryLot Model, InventoryTransaction Model, InventoryTypeSeed Model, Plant Model, Database

**Key Interactions**:
- Multi-step form untuk create inventory type
- Session management untuk step-by-step process
- FIFO untuk pengurangan stok
- Auto-update status lot berdasarkan expiry date
- Transaction tracking untuk semua operasi stok

### 04_sequence_penjualan.puml
**Modul Penjualan**

Menunjukkan interaksi untuk:
- Buat transaksi penjualan
- Proses FIFO untuk pengurangan stok
- Validasi stok tersedia
- Restore stok jika penjualan dibatalkan
- AJAX untuk get bins dan lots

**Aktor**: User
**Participant**: SaleController, Sale Model, SaleItem Model, Warehouse Model, Bin Model, InventoryLot Model, InventoryType Model, InventoryTransaction Model, Database

**Key Interactions**:
- Generate nomor struk otomatis
- FIFO (First In First Out) untuk pengurangan stok
- Transaction management (BEGIN/COMMIT/ROLLBACK)
- Validasi stok sebelum mengurangi
- Restore stok saat pembatalan penjualan

## Simbol dan Notasi

- **Actor** (User): Pengguna sistem
- **Participant**: Komponen sistem (Controller, Model, Database)
- **Activate/Deactivate**: Menunjukkan periode aktif komponen
- **Alt/Else**: Conditional flow (jika/maka)
- **Loop**: Iterasi/perulangan
- **Note**: Catatan penjelasan
- **Arrow**: 
  - `->` : Synchronous call
  - `-->` : Return/Response

## Alur Utama Sistem

```
User → Controller → Model → Database
         ↓
      Validation
         ↓
      Business Logic
         ↓
      Database Operation
         ↓
      Response to User
```

## Catatan Penting

1. **Transaction Management**: Operasi yang melibatkan multiple database operations menggunakan transaction (BEGIN/COMMIT/ROLLBACK) untuk menjaga konsistensi data.

2. **FIFO Implementation**: Sistem menggunakan metode FIFO (First In First Out) untuk pengurangan stok, diimplementasikan dengan `ORDER BY created_at ASC`.

3. **Validation**: Setiap input dari user divalidasi sebelum diproses untuk memastikan data integrity.

4. **Status Updates**: Status lot otomatis di-update berdasarkan kondisi (tersedia, habis, kadaluarsa).

5. **Relations**: Model menggunakan Eloquent relationships untuk efisiensi query (eager loading dengan `with()`).

## Versi

- **Format**: PlantUML
- **UML Version**: 2.0
- **Tanggal Pembuatan**: Desember 2025
- **Aplikasi**: SIBIT (Sistem Informasi Benih Terintegrasi)

## Deskripsi Use Case Diagram

### 00_use_case_overview.puml
**Overview Use Case Keseluruhan Sistem**

Menunjukkan semua aktor dan use case utama dalam sistem:
- **Aktor**: Admin, Kepala Satuan Tugas, Petugas Sertifikasi, Petugas Gudang, Petugas BBI, Penangkar
- **Modul**: Penanaman, Sertifikasi, Manajemen Stok dan Gudang, Penjualan, Manajemen User
- **Relasi**: Include dan Extend relationships

### 01_use_case_penanaman.puml
**Modul Penanaman**

**Aktor**:
- Kepala Satuan Tugas Manajemen Penanaman
- Penangkar
- Sistem

**Use Case Utama**:
- Input Data Penanaman
- Tracking Perkembangan Tanaman
- Input Data Panen
- Lihat, Edit, Hapus Data Penanaman

**Relasi**:
- Include: Input Penanaman → Query Data → Validasi → Simpan
- Extend: List → Detail, Edit, Delete

### 02_use_case_sertifikasi.puml
**Modul Sertifikasi**

**Aktor**:
- Petugas Sertifikasi
- Penangkar
- Sistem

**Use Case Utama**:
- Pilih Hasil Panen
- Buat Laporan Pemeriksaan
- Upload Dokumen Scan
- Evaluasi Hasil Pemeriksaan
- Tambahkan Benih ke Stok (jika LULUS)

**Relasi**:
- Include: Buat Laporan → Upload → Validasi → Simpan
- Extend: Evaluasi → Tambah ke Stok (jika LULUS)

### 03_use_case_manajemen_stok.puml
**Modul Manajemen Stok dan Gudang**

**Aktor**:
- Petugas Gudang
- Penangkar
- Sistem

**Use Case Utama**:
- Tambah Stok Baru
- Kelola Gudang dan Bin
- Adjustment Stok
- Lihat History Transaksi

**Relasi**:
- Include: Tambah Stok → Validasi → Simpan Inventory Type → Simpan Lot
- Extend: List Gudang → Detail, Edit, Delete, Kelola Bin

### 04_use_case_penjualan.puml
**Modul Penjualan**

**Aktor**:
- Petugas BBI
- Sistem

**Use Case Utama**:
- Input Data Penjualan
- Pilih Lot untuk Dijual
- Proses Penjualan dengan FIFO
- Batalkan Penjualan

**Relasi**:
- Include: Input Penjualan → Pilih Lot → Validasi → Proses → Kurangi Stok
- Extend: List → Detail, Batal

## Deskripsi Activity Diagram

### 01_activity_penanaman.puml
**Modul Penanaman**

Menunjukkan alur aktivitas:
- Pengguna mengakses halaman create penanaman
- Sistem memvalidasi role dan menampilkan form
- Pengguna mengisi data penanaman
- Sistem memvalidasi dan menyimpan data
- Pengguna melakukan tracking perkembangan
- Sistem memeriksa kesiapan panen
- Pengguna mencatat hasil panen

**Swimlanes**: Pengguna | Sistem

### 02_activity_sertifikasi.puml
**Modul Sertifikasi**

Menunjukkan alur aktivitas:
- Pengguna memilih hasil panen
- Sistem membuat sertifikasi baru
- Pengguna membuat laporan pemeriksaan
- Sistem memvalidasi dan menyimpan laporan
- Pengguna mengevaluasi hasil (LULUS/TIDAK LULUS)
- Jika LULUS: Pengguna menambahkan benih ke stok

**Swimlanes**: Pengguna | Sistem

### 03_activity_manajemen_stok.puml
**Modul Manajemen Stok dan Gudang**

Menunjukkan alur aktivitas dengan 3 jalur:
1. **Tambah Stok**: Pengguna menambah stok baru dengan lot
2. **Kelola Gudang**: Pengguna membuat gudang dan bin
3. **Adjustment Stok**: Pengguna melakukan penyesuaian stok

**Swimlanes**: Pengguna | Sistem

### 04_activity_penjualan.puml
**Modul Penjualan**

Menunjukkan alur aktivitas:
- Pengguna mengakses halaman create penjualan
- Sistem menampilkan form dengan data gudang/bin
- Pengguna mengisi data penjualan dan memilih lot
- Sistem memvalidasi dan memeriksa stok
- Sistem mengurangi stok menggunakan FIFO
- Sistem mencatat transaksi penjualan

**Swimlanes**: Pengguna | Sistem

## Format Activity Diagram

Activity diagram menggunakan format **PlantUML** dengan:
- **Swimlanes**: Membagi aktivitas antara "Pengguna" dan "Sistem"
- **Decision Nodes**: Menggunakan format `if (kondisi?) then (Ya) else (Tidak)`
- **Activities**: Menggunakan format `:Nama aktivitas;`
- **Start/Stop**: Menggunakan `start` dan `stop`

## Referensi

- PlantUML Documentation: 
  - Sequence Diagram: http://plantuml.com/sequence-diagram
  - Activity Diagram: http://plantuml.com/activity-diagram-beta
- UML Diagrams: https://www.uml-diagrams.org/

## Kontak

Untuk pertanyaan atau saran terkait diagram UML ini, silakan hubungi tim pengembang.

