# Pembaruan Diagram BPMN

## Perubahan yang Dilakukan

### 1. Penjelasan dalam Bahasa Manusia
Semua aktivitas dalam diagram BPMN sekarang menggunakan **bahasa manusia** yang mudah dipahami, bukan bahasa teknis sistem. Contoh:
- ✅ "Mengambil Daftar Tanaman dari Database" (bukan "Query: SELECT * FROM plants")
- ✅ "Menyimpan Data Penanaman ke Database" (bukan "Database: INSERT INTO plantings")
- ✅ "Pengguna Mengisi Data Penanaman" (bukan "User Input: POST /plantings")

### 2. Artifact Database
**Data Store Reference** telah ditambahkan ke semua diagram untuk menunjukkan interaksi dengan database:
- Setiap aktivitas yang berhubungan dengan database memiliki **association** (garis putus-putus) ke artifact "Database"
- Artifact database ditampilkan sebagai **Data Store Reference** di diagram
- Association menunjukkan aktivitas yang melakukan operasi:
  - **Membaca data** (SELECT queries)
  - **Menyimpan data** (INSERT operations)
  - **Memperbarui data** (UPDATE operations)
  - **Memeriksa data** (validation checks)

### 3. Perbaikan Sequence Flow (Line)
Semua **sequence flow** (garis alur) telah diperbaiki agar muncul dengan benar di Camunda Modeler:
- Semua waypoint didefinisikan dengan benar
- Bounds untuk semua elemen diatur dengan tepat
- Sequence flow terhubung dengan benar dari source ke target
- Label untuk gateway dan flow ditambahkan

## Struktur File

### 01_penanaman.bpmn
**Proses Penanaman** - Menjelaskan alur dari:
- Pengambilan data tanaman dan lokasi
- Input data penanaman oleh pengguna
- Validasi dan penyimpanan data
- Tracking perkembangan tanaman
- Pencatatan hasil panen

**Interaksi Database:**
- Query tanaman dan lokasi
- Insert data penanaman
- Update status perkembangan
- Insert data panen

### 02_sertifikasi.bpmn
**Proses Sertifikasi Benih** - Menjelaskan alur dari:
- Pemilihan hasil panen
- Pembuatan sertifikasi
- Input laporan pemeriksaan
- Evaluasi hasil
- Penambahan ke stok (jika lulus)

**Interaksi Database:**
- Query data panen
- Insert sertifikasi
- Insert laporan pemeriksaan
- Update status sertifikasi
- Insert data benih ke stok

### 03_manajemen_stok_gudang.bpmn
**Proses Manajemen Stok dan Gudang** - Menjelaskan alur dari:
- Penambahan stok baru
- Pengelolaan gudang dan bin
- Adjustment stok

**Interaksi Database:**
- Insert tipe inventory
- Insert lot inventory
- Insert transaksi inventory
- Insert gudang dan bin
- Update stok (adjustment)

### 04_penjualan.bpmn
**Proses Penjualan Benih** - Menjelaskan alur dari:
- Input data penjualan
- Pemilihan gudang, bin, dan lot
- Validasi stok
- Penyimpanan penjualan
- Pengurangan stok (FIFO)

**Interaksi Database:**
- Check nomor nota
- Check stok tersedia
- Insert penjualan
- Insert item penjualan
- Update stok lot
- Insert transaksi

## Cara Membuka di Camunda Modeler

1. Buka **Camunda Modeler**
2. File → Open → Pilih file `.bpmn` yang diinginkan
3. Diagram akan ditampilkan dengan semua garis alur yang terlihat
4. Artifact database akan muncul di bagian bawah diagram
5. Association (garis putus-putus) menghubungkan aktivitas ke database

## Catatan Penting

- Semua diagram menggunakan **BPMN 2.0** standard
- Diagram dapat diekspor ke berbagai format (PNG, SVG, PDF)
- Semua sequence flow memiliki waypoint yang benar untuk rendering
- Data Store Reference menggunakan format standar BPMN untuk kompatibilitas maksimal

## Tips Membaca Diagram

1. **Start Event** (lingkaran hijau) = Mulai proses
2. **User Task** (kotak dengan ikon orang) = Aktivitas yang memerlukan input pengguna
3. **Service Task** (kotak dengan ikon roda gigi) = Aktivitas sistem/otomatis
4. **Gateway** (diamond) = Decision point (ya/tidak, pilihan)
5. **End Event** (lingkaran merah) = Akhir proses
6. **Data Store** (persegi panjang dengan garis bawah) = Database
7. **Association** (garis putus-putus) = Koneksi ke database
8. **Sequence Flow** (garis solid dengan panah) = Alur proses

## Validasi

Semua file BPMN telah divalidasi dan dapat dibuka di:
- ✅ Camunda Modeler
- ✅ Camunda Cockpit
- ✅ BPMN.io
- ✅ Tools BPMN 2.0 lainnya
















