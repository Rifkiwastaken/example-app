# Panduan Testing Aplikasi SIBIT

## 🧪 Cara Testing Aplikasi

### 1. Setup Awal
Pastikan aplikasi sudah terinstall dengan benar:
```bash
# Check Laravel version
php artisan --version

# Check database connection
php artisan migrate:status
```

### 2. Testing Authentication

#### Test Login
1. Buka browser: `http://localhost:8000`
2. Akan redirect otomatis ke halaman login
3. Masukkan kredensial:
   - Email: `admin@sibit.com`
   - Password: `password123`
4. Klik "Sign In"
5. Harus redirect ke dashboard

#### Test Remember Me
1. Di halaman login, centang "Keep me signed in"
2. Login seperti biasa
3. Tutup browser
4. Buka kembali `http://localhost:8000`
5. Harus tetap login (tidak diminta login lagi)

#### Test Logout
1. Klik icon Settings (gear) di header
2. Atau tambahkan button logout di sidebar
3. Harus redirect ke halaman login

#### Test Invalid Credentials
1. Coba login dengan email/password salah
2. Harus muncul error message: "Email atau password salah"

### 3. Testing Dashboard

#### Test Weather Widget
1. Setelah login, perhatikan section "CUACA KOTA PADANG"
2. Harus menampilkan:
   - Suhu saat ini (dalam Celsius)
   - Deskripsi cuaca
   - Humidity
   - Wind speed
   - Sky coverage
3. Test tab switching:
   - Klik tab "Hourly" → Lihat forecast per jam
   - Klik tab "Daily" → Placeholder message

#### Test Tasks Widget
1. Di dashboard, perhatikan section "TUGAS"
2. Jika belum ada tugas, harus muncul "Tidak ada Tugas"
3. Klik "Lihat Tugas" → Harus redirect ke halaman tasks

### 4. Testing Task Management

#### Test View Tasks
1. Dari sidebar, klik "Tugas"
2. Harus menampilkan halaman daftar tugas
3. Jika belum ada data, harus ada message dan button "Tambah Tugas Pertama"

#### Test Create Task
1. Klik button "Tambah Tugas"
2. Isi form:
   - Judul: "Pemupukan Lahan A"
   - Deskripsi: "Pemupukan dengan NPK pada lahan A blok 1"
   - Prioritas: "High"
   - Status: "Pending"
   - Tanggal Tenggat: Pilih tanggal besok
   - Lokasi: "Lahan A - Blok 1"
   - Centang "Tag Lokasi"
3. Klik "Simpan Tugas"
4. Harus redirect ke daftar tugas dengan success message
5. Tugas baru harus muncul di tabel

#### Test Edit Task
1. Di daftar tugas, klik button Edit (icon pensil)
2. Form harus terisi dengan data tugas yang dipilih
3. Ubah beberapa field, misalnya:
   - Ubah prioritas ke "Highest"
   - Ubah status ke "In Progress"
4. Klik "Update Tugas"
5. Harus redirect dengan success message
6. Perubahan harus terlihat di tabel

#### Test Delete Task
1. Di daftar tugas, klik button Delete (icon trash)
2. Harus muncul confirmation dialog
3. Klik OK/Yes
4. Tugas harus terhapus dari daftar
5. Muncul success message

#### Test Task Validation
1. Coba submit form task kosong
2. Harus muncul validation error untuk field required
3. Coba pilih tanggal masa lalu untuk due date
4. Form harus mencegah submission jika ada error

### 5. Testing Responsive Design

#### Test Mobile View (< 768px)
1. Buka browser dev tools (F12)
2. Toggle device toolbar
3. Pilih device mobile (iPhone, Samsung)
4. Cek:
   - Sidebar harus hidden
   - Button hamburger harus muncul
   - Klik hamburger → sidebar slide in
   - Klik diluar sidebar → sidebar hide
   - Cards harus stack vertical
   - Table harus scrollable horizontal
   - Form fields harus full width

#### Test Tablet View (768px - 991px)
1. Resize browser ke ukuran tablet
2. Cek:
   - Sidebar bisa collapse/expand
   - Layout 2 kolom
   - Weather dan tasks masih side by side (jika muat)

#### Test Desktop View (>= 992px)
1. Resize browser ke full screen
2. Cek:
   - Sidebar always visible
   - Multi-column layout optimal
   - Search bar visible di header
   - Semua fitur accessible

### 6. Testing Navigation

#### Test Sidebar Navigation
1. Klik "Dashboard" → Harus ke dashboard
2. Klik "Tugas" → Harus ke task list
3. Active menu harus ter-highlight dengan warna hijau
4. Menu lain (Penanaman, Sertifikasi, dll) → Placeholder (belum ada halaman)

#### Test Breadcrumb
1. Buka halaman Task Create/Edit
2. Harus ada breadcrumb: Dashboard > Tugas > [Action]
3. Klik link di breadcrumb → Harus navigate ke halaman tersebut

#### Test Header Icons
1. Plus icon → Untuk future: Quick add menu
2. Help icon → Untuk future: Help documentation
3. Notification icon → Untuk future: Notifications
4. Settings icon → Untuk future: Settings atau current logout

### 7. Testing Data Persistence

#### Test Task Data
1. Tambah beberapa tugas (minimal 5)
2. Logout
3. Login kembali
4. Cek dashboard → Tugas harus muncul di widget
5. Cek halaman Tugas → Semua tugas harus ada

#### Test Session
1. Login dan tetap di dashboard
2. Tunggu 2 jam (atau sesuai SESSION_LIFETIME di .env)
3. Coba navigate ke halaman lain
4. Jika session expired, harus redirect ke login

### 8. Testing Error Handling

#### Test 404 Page
1. Akses URL yang tidak ada: `http://localhost:8000/halaman-tidak-ada`
2. Harus muncul Laravel default 404 page

#### Test Unauthenticated Access
1. Logout dari aplikasi
2. Coba akses langsung: `http://localhost:8000/dashboard`
3. Harus redirect ke login page

#### Test Database Error
1. Stop MySQL service
2. Coba akses aplikasi
3. Harus muncul error connection (di development mode)
4. Di production harus muncul error page yang user-friendly

### 9. Testing Performance

#### Test Page Load Time
1. Buka Network tab di browser dev tools
2. Refresh halaman dashboard
3. Cek total load time (harus < 3 detik)
4. Cek ukuran resource (CSS, JS, images)

#### Test Weather API
1. Monitor Network tab saat load dashboard
2. Cek API call ke OpenWeatherMap
3. Jika API key valid → Response 200
4. Jika API key invalid → Fallback data harus muncul

### 10. Testing Security

#### Test CSRF Protection
1. Buka form task di browser 1
2. Copy form HTML
3. Buka browser 2 (different session)
4. Paste form dan submit
5. Harus error CSRF token mismatch

#### Test SQL Injection
1. Di form login, coba input:
   - Email: `admin' OR '1'='1`
   - Password: `anything`
2. Harus login failed (tidak bisa bypass)

#### Test XSS
1. Tambah task dengan title: `<script>alert('XSS')</script>`
2. Simpan task
3. View di list → Script harus di-escape, tidak execute

## ✅ Checklist Testing

### Authentication
- [ ] Login dengan kredensial valid
- [ ] Login dengan kredensial invalid
- [ ] Remember me functionality
- [ ] Logout functionality
- [ ] Redirect after login
- [ ] Session expiration

### Dashboard
- [ ] Weather widget tampil
- [ ] Weather data sesuai lokasi
- [ ] Task widget tampil
- [ ] Task count akurat
- [ ] Link "Lihat Tugas" berfungsi

### Task Management
- [ ] View task list
- [ ] Create new task
- [ ] Edit existing task
- [ ] Delete task
- [ ] Validation works
- [ ] Priority badges tampil
- [ ] Status badges tampil
- [ ] Location tag works

### Responsive Design
- [ ] Mobile view (< 768px)
- [ ] Tablet view (768-991px)
- [ ] Desktop view (>= 992px)
- [ ] Sidebar toggle mobile
- [ ] Table scroll horizontal

### Navigation
- [ ] Sidebar links work
- [ ] Active menu highlight
- [ ] Breadcrumb navigation
- [ ] Header icons present

### Data & Security
- [ ] Data persistence
- [ ] CSRF protection
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] Auth middleware

## 🐛 Known Issues & Limitations

### Current Limitations
1. Weather API memerlukan API key valid untuk data real-time
2. Menu Penanaman, Sertifikasi, Stok, dll masih placeholder
3. Belum ada multi-user role management
4. Belum ada notification system
5. Belum ada export/import data

### Future Improvements
1. Implementasi semua modul (Penanaman, Sertifikasi, dll)
2. Advanced filtering & search
3. Data analytics & reporting
4. Multi-language support
5. Mobile app version

## 📝 Reporting Bugs

Jika menemukan bug saat testing:
1. Screenshot halaman error
2. Copy error message dari log (storage/logs/laravel.log)
3. Catat steps to reproduce
4. Report ke tim developer

---

**Happy Testing! 🎉**


















