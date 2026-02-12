# 🚀 SIBIT - Quick Start Guide

Panduan cepat untuk menjalankan aplikasi SIBIT dalam 5 menit!

## ⚡ Quick Setup (Untuk Laragon)

Jika menggunakan **Laragon**, aplikasi sudah berada di folder yang tepat (`c:\laragon\www\example-app`).

### 1. Start Services
- Buka Laragon
- Klik "Start All" untuk menjalankan Apache dan MySQL

### 2. Setup Database
Buka terminal/cmd di folder project dan jalankan:
```bash
# Buat database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sibit"

# Jalankan migration
php artisan migrate

# Jalankan seeder untuk user default
php artisan db:seed
```

### 3. Generate App Key (jika belum)
```bash
php artisan key:generate
```

### 4. Akses Aplikasi
Buka browser dan akses:
```
http://localhost/example-app/public
```

Atau jika sudah setup virtual host:
```
http://example-app.test
```

## 🔑 Login

**Email**: admin@sibit.com  
**Password**: password123

## 📱 Fitur yang Bisa Dicoba

### 1. Dashboard
- Lihat informasi cuaca Padang
- Lihat ringkasan tugas

### 2. Manajemen Tugas
- Klik "Tugas" di sidebar
- Tambah tugas baru
- Edit tugas
- Hapus tugas

## 🛠️ Commands yang Berguna

### Clear Cache (jika ada masalah)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Reset Database (mulai dari awal)
```bash
php artisan migrate:fresh --seed
```

### Check Migration Status
```bash
php artisan migrate:status
```

## 🌐 Setup Virtual Host (Optional)

### Untuk Laragon:
1. Klik kanan icon Laragon di system tray
2. Pilih "Quick app" > "laravel"
3. Atau rename folder `example-app` ke nama yang diinginkan
4. Laragon akan otomatis membuat virtual host: `http://nama-folder.test`

### Manual Virtual Host:
Edit file hosts (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 sibit.test
```

Buat virtual host di Apache/Nginx yang mengarah ke folder `public`.

## 🎯 Quick Test Checklist

- [ ] Buka halaman login
- [ ] Login dengan kredensial default
- [ ] Lihat dashboard dengan weather widget
- [ ] Buka halaman Tugas
- [ ] Tambah 1 tugas baru
- [ ] Edit tugas yang baru dibuat
- [ ] Kembali ke dashboard, tugas harus muncul
- [ ] Logout

Jika semua checklist di atas berhasil, aplikasi sudah berjalan dengan baik! ✅

## ❓ Troubleshooting Cepat

### Masalah: "No application encryption key"
**Solusi**: 
```bash
php artisan key:generate
```

### Masalah: Database connection error
**Solusi**: 
1. Pastikan MySQL running di Laragon
2. Check file `.env` bagian DB_*
3. Default Laragon: username=root, password=(kosong)

### Masalah: 404 Not Found
**Solusi**: 
Pastikan akses ke folder `/public`, bukan root folder

### Masalah: Weather data tidak muncul
**Solusi**: 
Normal, akan muncul fallback data. Untuk real data, perlu API key OpenWeatherMap (lihat INSTALASI.md)

## 📚 Next Steps

Setelah aplikasi berjalan:
1. Baca **RINGKASAN_APLIKASI.md** untuk overview lengkap
2. Baca **INSTALASI.md** untuk setup advanced
3. Baca **TESTING_GUIDE.md** untuk testing komprehensif

## 💡 Tips

- Gunakan Chrome DevTools untuk testing responsive
- Bookmark halaman login untuk akses cepat
- Buat sample data (tasks) untuk testing yang lebih realistis
- Check `storage/logs/laravel.log` jika ada error

---

**Selamat menggunakan SIBIT! 🌾**

Jika ada pertanyaan atau masalah, refer ke dokumentasi lengkap atau hubungi tim developer.





















