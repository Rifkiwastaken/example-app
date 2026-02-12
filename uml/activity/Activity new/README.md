# Activity Diagrams - Sesuai Use Case Overview

Folder ini berisi 18 diagram aktivitas yang menyesuaikan dengan **18 use case** pada file **00_use_case_overview.puml** (Use Case Overview - Sistem Informasi Manajemen Perbenihan).

## Pemetaan Use Case → File Activity

| No | Use Case (Overview) | File Activity | Aktor |
|----|----------------------|---------------|-------|
| 01 | Mengelola data tanaman | 01_kelola_data_tanaman.puml | Admin/Kepala Sek, Penangkar/Kepala Satgas |
| 02 | Melihat data tanaman | 02_lihat_data_tanaman.puml | Penangkar/Kepala Satgas |
| 03 | Mengelola data lokasi penanaman | 03_kelola_data_lokasi_penanaman.puml | Admin/Kepala Sek, Penangkar/Kepala Satgas |
| 04 | Melihat data lokasi penanaman | 04_lihat_data_lokasi_penanaman.puml | Penangkar/Kepala Satgas |
| 05 | Mengelola data Penanaman | 05_kelola_data_penanaman.puml | Admin/Kepala Sek, Penangkar/Kepala Satgas |
| 06 | Monitoring Perkembangan Tanaman | 06_monitoring_perkembangan_tanaman.puml | Admin/Kepala Sek, Penangkar/Kepala Satgas |
| 07 | Mengelola data panen | 07_kelola_data_panen.puml | Admin/Kepala Sek, Penangkar/Kepala Satgas |
| 08 | Mengelola data sertifikasi | 08_kelola_data_sertifikasi.puml | Admin/Kepala Sek, Petugas BBI |
| 09 | Login | 09_login.puml | Semua aktor |
| 10 | Logout | 10_logout.puml | Semua aktor |
| 11 | Mengelola Profile | 11_kelola_profile.puml | Semua aktor |
| 12 | Menambahkan User | 12_tambah_user.puml | Admin/Kepala Sek |
| 13 | Mengakses Dashboard | 13_akses_dashboard.puml | Semua aktor |
| 14 | Menambahkan data stok benih | 14_tambah_data_stok_benih.puml | Admin/Kepala Sek, Petugas BBI, Petugas Gudang |
| 15 | Mengelola data stok benih | 15_kelola_data_stok_benih.puml | Petugas Gudang |
| 16 | Mengelola data lokasi gudang dan bin | 16_kelola_data_lokasi_gudang_bin.puml | Admin/Kepala Sek, Petugas BBI, Petugas Gudang |
| 17 | Mengelola data penjualan | 17_kelola_data_penjualan.puml | Admin/Kepala Sek, Petugas BBI |
| 18 | Mengelola data laporan | 18_kelola_data_laporan.puml | Admin/Kepala Sek, Petugas BBI |

## Relasi Include (Use Case Overview)

- **Mengelola data tanaman** `<<include>>` **Melihat data tanaman**
- **Mengelola data lokasi penanaman** `<<include>>` **Melihat data lokasi penanaman**
- **Menambahkan data stok benih** `<<include>>` **Mengelola data stok benih**

## Referensi

- Use Case Overview: `uml/00_use_case_overview.puml`
