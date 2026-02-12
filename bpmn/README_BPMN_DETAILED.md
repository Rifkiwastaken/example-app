# BPMN Diagram dengan Swimlanes - Aplikasi SIBIT

Folder ini berisi diagram BPMN yang lebih detail dengan menggunakan **swimlanes** (lanes) untuk menunjukkan interaksi antar aktor dalam proses bisnis.

## File BPMN dengan Swimlanes

1. **01_penanaman_detailed.bpmn** - BPMN untuk modul Penanaman dengan swimlanes Pengguna dan Sistem
2. **02_sertifikasi_detailed.bpmn** - BPMN untuk modul Sertifikasi dengan swimlanes Petugas Sertifikasi, Sistem, dan Penangkar

## Format BPMN dengan Swimlanes

### Struktur Swimlanes

Setiap diagram BPMN menggunakan struktur **collaboration** dengan **participant** dan **lanes**:

- **Participant**: Pool utama yang berisi proses bisnis
- **Lanes**: Swimlanes yang membagi aktivitas berdasarkan aktor/role

### Aktor dalam Swimlanes

#### Modul Penanaman
- **Lane Pengguna**: Aktivitas yang dilakukan oleh pengguna (User)
- **Lane Sistem**: Aktivitas yang dilakukan oleh sistem

#### Modul Sertifikasi
- **Lane Petugas Sertifikasi**: Aktivitas yang dilakukan oleh petugas sertifikasi
- **Lane Sistem**: Aktivitas yang dilakukan oleh sistem
- **Lane Penangkar**: Aktivitas yang dilakukan oleh penangkar (untuk menambahkan ke stok)

## Elemen BPMN yang Digunakan

### 1. Events
- **Start Event**: Mulai proses (lingkaran hijau)
- **End Event**: Akhir proses (lingkaran merah)
- **Intermediate Catch Event**: Event menunggu (untuk loop)

### 2. Activities
- **User Task**: Aktivitas yang memerlukan input dari pengguna
- **Service Task**: Aktivitas sistem/otomatis

### 3. Gateways
- **Exclusive Gateway**: Decision point (diamond dengan X)
  - Label: "Ya" / "Tidak"
  - Label: "LULUS" / "TIDAK LULUS"
  - Label: "Siap Panen?" / "Belum"

### 4. Data Store
- **Data Store Reference**: Database artifact
- **Association**: Garis putus-putus menghubungkan aktivitas ke database

### 5. Sequence Flow
- **Sequence Flow**: Alur proses (garis solid dengan panah)
- Semua flow memiliki waypoint yang benar untuk rendering di Camunda

## Alur Proses

### Modul Penanaman (01_penanaman_detailed.bpmn)

**Swimlanes**: Pengguna | Sistem

1. **Sistem**: Validasi role → Query data → Display form
2. **Gateway**: Role valid?
   - **Ya**: Pengguna mengisi data penanaman
   - **Tidak**: Error
3. **Pengguna**: Isi data penanaman → Isi data perkembangan
4. **Sistem**: Validasi data → Check database → Simpan penanaman
5. **Pengguna**: Tracking perkembangan
6. **Sistem**: Update perkembangan → Check siap panen
7. **Gateway**: Siap panen?
   - **Ya**: Pengguna isi data panen → Sistem simpan panen → Selesai
   - **Belum**: Menunggu perkembangan → Kembali ke tracking

**Interaksi Database**:
- Query data tanaman dan lokasi
- Check tanaman dan lokasi terdaftar
- Simpan data penanaman
- Update status perkembangan
- Simpan data panen

### Modul Sertifikasi (02_sertifikasi_detailed.bpmn)

**Swimlanes**: Petugas Sertifikasi | Sistem | Penangkar

1. **Sistem**: Validasi role → Query data panen → Create sertifikasi
2. **Gateway**: Role valid?
   - **Ya**: Petugas memilih panen
   - **Tidak**: Error
3. **Petugas Sertifikasi**: Pilih panen → Buat laporan → Isi data inspeksi
4. **Sistem**: Upload dokumen → Validasi laporan → Check nomor laporan → Simpan laporan
5. **Petugas Sertifikasi**: Evaluasi hasil
6. **Gateway**: Kesimpulan?
   - **LULUS**: Update status LULUS → Penangkar tambahkan ke stok → Simpan stok → Selesai
   - **TIDAK LULUS**: Update status TIDAK LULUS → Selesai

**Interaksi Database**:
- Query data panen
- Create sertifikasi baru
- Check nomor laporan unik
- Simpan laporan pemeriksaan
- Update status sertifikasi
- Simpan data benih ke stok

## Cara Membuka di Camunda Modeler

1. Buka **Camunda Modeler**
2. File → Open → Pilih file `.bpmn` yang diinginkan
3. Diagram akan ditampilkan dengan:
   - Swimlanes yang jelas terlihat
   - Semua garis alur yang terhubung dengan benar
   - Data store dengan association ke database
   - Gateway dengan label yang jelas

## Perbedaan dengan BPMN Tanpa Swimlanes

### BPMN Tanpa Swimlanes (01_penanaman.bpmn)
- Semua aktivitas dalam satu pool
- Tidak menunjukkan siapa yang melakukan aktivitas
- Lebih sederhana untuk proses yang hanya melibatkan satu aktor

### BPMN dengan Swimlanes (01_penanaman_detailed.bpmn)
- Aktivitas dibagi berdasarkan aktor/role
- Jelas menunjukkan interaksi antar aktor
- Lebih detail dan mudah dipahami untuk proses kompleks
- Sesuai dengan format referensi yang diberikan

## Catatan Penting

1. **Swimlanes**: Setiap lane mewakili aktor/role yang berbeda
2. **Cross-lane Flow**: Flow dapat berpindah antar lanes menunjukkan handoff antar aktor
3. **Data Store**: Database artifact berada di luar lanes untuk menunjukkan bahwa semua aktor mengakses database yang sama
4. **Association**: Garis putus-putus menunjukkan aktivitas yang berinteraksi dengan database

## Versi

- **Format**: BPMN 2.0 XML
- **Tool**: Camunda Modeler
- **Tanggal Pembuatan**: Desember 2025
- **Aplikasi**: SIBIT (Sistem Informasi Benih Terintegrasi)

## Referensi

- BPMN 2.0 Specification: https://www.omg.org/spec/BPMN/2.0/
- Camunda Modeler: https://camunda.com/products/camunda-platform/modeler/
- BPMN Tutorial: https://camunda.com/bpmn/reference/
















