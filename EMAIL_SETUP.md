# Setup Email Notifications dengan Gmail SMTP

## Konfigurasi Email di .env

Tambahkan konfigurasi berikut ke file `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Setup Gmail App Password

1. Buka Google Account Settings: https://myaccount.google.com/
2. Pilih **Security** (Keamanan)
3. Aktifkan **2-Step Verification** jika belum aktif
4. Klik **App passwords** (Kata sandi aplikasi)
5. Pilih aplikasi: **Mail**
6. Pilih perangkat: **Other (Custom name)**
7. Masukkan nama: **SIBESTI**
8. Klik **Generate**
9. Salin password yang dihasilkan (16 karakter tanpa spasi)
10. Gunakan password ini sebagai `MAIL_PASSWORD` di `.env`

## Setup Queue

Email notifications dikirim secara asynchronous menggunakan queue. Pastikan queue worker berjalan:

### Development (Local)
```bash
php artisan queue:work
```

### Production
Setup supervisor atau systemd untuk menjalankan queue worker secara otomatis.

## Setup Scheduler

Scheduler berjalan otomatis untuk:
- **Stok Rendah**: Setiap hari pukul 08:00 WIB
- **Benih Kadaluarsa di Bin**: Setiap hari pukul 08:00 WIB
- **Benih Mendekati Kadaluarsa**: Setiap hari pukul 08:00 WIB

Pastikan cron job sudah di-setup di server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

### Test Email Configuration
```bash
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('your-email@gmail.com')->subject('Test');
});
```

### Test Commands
```bash
# Test low stock check
php artisan sibesti:check-low-stock

# Test expired bin stock check
php artisan sibesti:check-expired-bin-stock

# Test expiring seeds check
php artisan sibesti:check-expiring-seeds
```

## Jenis Notifikasi

### 1. Task Notification (Penangkar & Kepala Satgas)
- **Trigger**: Saat Task baru dibuat
- **Penerima**: User yang ditugaskan (penangkar/kepala_satuan_tugas)
- **Konten**: Judul tugas, lokasi, tenggat waktu, link detail

### 2. Note Notification (Penangkar & Kepala Satgas)
- **Trigger**: Saat Note baru dibuat
- **Penerima**: User yang ditugaskan (penangkar/kepala_satuan_tugas)
- **Konten**: Judul catatan, lokasi, konten, link detail

### 3. Low Stock Notification (Petugas Gudang & Admin)
- **Trigger**: Scheduler harian (08:00 WIB)
- **Penerima**: Admin dan Petugas Gudang
- **Konten**: Daftar benih dengan stok di bawah threshold

### 4. Expired Bin Stock Notification (Petugas Gudang & Admin)
- **Trigger**: Scheduler harian (08:00 WIB)
- **Penerima**: Admin dan Petugas Gudang
- **Konten**: Daftar bin dengan benih kadaluarsa

### 5. Expiring Seeds Notification (Admin)
- **Trigger**: Scheduler harian (08:00 WIB)
- **Penerima**: Admin (ahmadfarid0410@gmail.com)
- **Konten**: Daftar benih yang mendekati/melahwati kadaluarsa (H-14)

## Troubleshooting

### Email tidak terkirim
1. Pastikan `MAIL_PASSWORD` menggunakan App Password, bukan password Gmail biasa
2. Pastikan 2-Step Verification sudah aktif
3. Cek log: `storage/logs/laravel.log`
4. Test koneksi SMTP dengan command di atas

### Queue tidak berjalan
1. Pastikan queue worker berjalan: `php artisan queue:work`
2. Cek failed jobs: `php artisan queue:failed`
3. Retry failed jobs: `php artisan queue:retry all`

### Scheduler tidak berjalan
1. Pastikan cron job sudah di-setup
2. Test scheduler: `php artisan schedule:run`
3. List scheduled tasks: `php artisan schedule:list`



