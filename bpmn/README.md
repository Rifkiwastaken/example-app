# Diagram BPMN Aplikasi SIBIT

Folder ini berisi diagram Business Process Model Notation (BPMN) untuk aplikasi SIBIT (Sistem Informasi Benih Terintegrasi).

## Daftar File

1. **ANALISIS_ALUR_SISTEM.md** - Dokumentasi analisis alur sistem secara lengkap
2. **01_penanaman.bpmn** - Diagram BPMN untuk modul Penanaman
3. **02_sertifikasi.bpmn** - Diagram BPMN untuk modul Sertifikasi
4. **03_manajemen_stok_gudang.bpmn** - Diagram BPMN untuk modul Manajemen Stok dan Gudang
5. **04_penjualan.bpmn** - Diagram BPMN untuk modul Penjualan
6. **01_penanaman_clean.bpmn** - Versi ringkas & valid Camunda (ID unik)
7. **02_sertifikasi_clean.bpmn** - Versi ringkas & valid Camunda (ID unik)
8. **03_manajemen_stok_gudang_clean.bpmn** - Versi ringkas & valid Camunda (ID unik)
9. **04_penjualan_clean.bpmn** - Versi ringkas & valid Camunda (ID unik)
10. **01_penanaman_swimlane.bpmn** - Versi swimlane dengan pool/lane (Admin, Kepala Satgas/Penangkar) + artefak database
11. **02_sertifikasi_swimlane.bpmn** - Versi swimlane (Admin penuh) + artefak database
12. **03_manajemen_stok_gudang_swimlane.bpmn** - Versi swimlane (Admin, Petugas Gudang) + artefak database
13. **04_penjualan_swimlane.bpmn** - Versi swimlane (Petugas BBI) + artefak database

## Cara Membuka File BPMN

File BPMN dapat dibuka menggunakan berbagai tool, antara lain:

### 1. Camunda Modeler (Recommended)
- Download: https://camunda.com/download/modeler/
- Gratis dan open source
- Support penuh untuk BPMN 2.0
- Dapat mengedit dan memvalidasi diagram

### 2. Bizagi Modeler
- Download: https://www.bizagi.com/en/platform/modeler
- Gratis untuk penggunaan non-komersial
- User-friendly interface

### 3. Draw.io / diagrams.net
- Online: https://app.diagrams.net/
- Gratis dan berbasis web
- Dapat mengimpor file BPMN

### 4. Visual Paradigm
- Download: https://www.visual-paradigm.com/
- Berbayar (ada versi trial)
- Fitur lengkap untuk modeling

### 5. Eclipse BPMN2 Modeler
- Plugin untuk Eclipse IDE
- Open source
- Cocok untuk developer

## Deskripsi Modul

### 1. Modul Penanaman (01_penanaman.bpmn)
**Alur Utama:**
- Input data tanaman
- Pilih lokasi penanaman
- Input detail penanaman (spacing, depth, dll)
- Tracking perkembangan tanaman
- Update tahap germinasi
- Pencatatan hasil panen

**Key Activities:**
- Input Data Tanaman
- Pilih Lokasi Penanaman
- Input Detail Penanaman
- Tracking Perkembangan Tanaman
- Pencatatan Hasil Panen

### 2. Modul Sertifikasi (02_sertifikasi.bpmn)
**Alur Utama:**
- Pilih hasil panen
- Buat sertifikasi dengan status "dalam_proses"
- Pilih kelas benih (BS/BP/BR)
- Buat laporan pemeriksaan
- Input data inspeksi
- Upload dokumen scan
- Evaluasi hasil (LULUS/TIDAK LULUS)
- Jika lulus, tambahkan ke stok

**Key Activities:**
- Pilih Hasil Panen
- Buat Sertifikasi
- Buat Laporan Pemeriksaan
- Evaluasi Hasil
- Tambahkan ke Stok (jika lulus)

### 3. Modul Manajemen Stok dan Gudang (03_manajemen_stok_gudang.bpmn)
**Alur Utama:**
- Setup gudang dan bin
- Buat tipe inventaris
- Tambah benih ke stok (dari sertifikasi atau manual)
- Buat lot dengan production_id
- Operasi stok:
  - Stok keluar (untuk penjualan)
  - Penyesuaian stok
  - Pindah lokasi
- Monitoring stok

**Key Activities:**
- Buat Lokasi Gudang
- Buat Bin/Tempat Penyimpanan
- Buat Tipe Inventaris
- Tambah Benih ke Stok
- Proses FIFO
- Monitoring Stok

### 4. Modul Penjualan (04_penjualan.bpmn)
**Alur Utama:**
- Generate nomor struk
- Input data pembeli
- Pilih gudang dan bin
- Cek stok tersedia
- Pilih item dan quantity
- Input harga per unit
- Proses FIFO untuk pengurangan stok
- Buat transaksi penjualan
- Input metode dan status pembayaran
- Simpan penjualan

**Key Activities:**
- Generate Nomor Struk
- Input Data Pembeli
- Pilih Gudang dan Bin
- Proses FIFO
- Kurangi Stok dari Lot
- Buat Transaksi Penjualan
- Input Pembayaran

## Simbol BPMN yang Digunakan

- **Start Event** (Lingkaran hijau): Titik awal proses
- **End Event** (Lingkaran merah): Titik akhir proses
- **User Task** (Kotak dengan ikon orang): Tugas yang dilakukan oleh user
- **Service Task** (Kotak dengan ikon roda gigi): Tugas yang dilakukan oleh sistem
- **Exclusive Gateway** (Diamond): Decision point (pilihan)
- **Sequence Flow** (Panah): Alur eksekusi

## Hubungan Antar Modul

```
Penanaman → Panen → Sertifikasi → Stok → Penjualan
```

1. **Penanaman** menghasilkan **Panen**
2. **Panen** menjadi input untuk **Sertifikasi**
3. **Sertifikasi** yang lulus ditambahkan ke **Stok**
4. **Stok** digunakan untuk **Penjualan**
5. **Penjualan** mengurangi **Stok**

## Catatan Penting

1. **FIFO (First In First Out)**: Sistem menggunakan metode FIFO untuk pengurangan stok, artinya stok yang masuk lebih dulu akan keluar lebih dulu.

2. **Tracking Lot**: Setiap benih yang masuk ke gudang harus memiliki lot dengan production_id untuk tracking.

3. **Status Sertifikasi**: Hanya benih dengan status "lulus" yang dapat ditambahkan ke stok.

4. **Validasi Stok**: Sebelum melakukan penjualan, sistem harus memastikan stok tersedia.

5. **Tracking Expiry**: Sistem memantau tanggal kadaluarsa benih dan mengupdate status lot secara otomatis.

## Update Terbaru

Diagram BPMN telah diupdate dengan:
- ✅ Penjelasan dalam bahasa sistem (Request/Response, Controller, Model, SQL)
- ✅ Detail interaksi database untuk setiap activity
- ✅ Sequence flow yang terhubung dengan benar
- ✅ Dokumentasi lengkap operasi database (lihat DOKUMENTASI_DATABASE_OPERATIONS.md)

Lihat **README_UPDATED.md** untuk penjelasan lengkap perubahan.

## Versi

- **Versi BPMN**: 2.0
- **Tanggal Pembuatan**: Desember 2025
- **Aplikasi**: SIBIT (Sistem Informasi Benih Terintegrasi)

## Kontak

Untuk pertanyaan atau saran terkait diagram BPMN ini, silakan hubungi tim pengembang.

