# SIBIT - Sistem Informasi Balai Benih Induk Tanaman

## 📋 Deskripsi Proyek
SIBIT adalah aplikasi website manajemen pertanian yang dikembangkan untuk Unit Pelaksana Teknis Daerah (UPTD) Balai Benih Induk (BBI) Tanaman Pangan, Palawija, dan Hortikultura (TPPH). Aplikasi ini dirancang untuk memanajemen dan monitoring penanaman pertanian dari fase persiapan lahan hingga panen menjadi bibit yang akan didistribusikan ke masyarakat.

## 🎯 Tujuan Aplikasi
- Monitoring penanaman dari persiapan lahan hingga panen
- Manajemen sertifikasi bibit
- Pengelolaan stok bibit
- Sistem penjualan dan distribusi bibit
- Tracking tugas dan aktivitas
- Monitoring cuaca untuk perencanaan pertanian

## 🚀 Fitur yang Telah Diimplementasikan

### 1. Authentication System
- ✅ Halaman login dengan desain modern dan responsif
- ✅ Email & password authentication
- ✅ Remember me functionality
- ✅ Session management
- ✅ Logout functionality

**Kredensial Default:**
- Email: `admin@sibit.com`
- Password: `password123`

### 2. Dashboard
- ✅ Informasi cuaca real-time untuk Lubuk Minturun, Padang
- ✅ Forecast cuaca per jam (Hourly)
- ✅ Forecast cuaca per hari (Daily) - placeholder
- ✅ Widget tugas dengan prioritas dan status
- ✅ Responsive layout untuk desktop dan mobile
- ✅ Integration dengan OpenWeatherMap API

**Data Cuaca yang Ditampilkan:**
- Suhu saat ini
- Kondisi cuaca
- Kelembaban
- Kecepatan angin
- Sky coverage
- Feels like temperature
- Forecast per jam untuk 6 jam ke depan

### 3. Manajemen Tugas (Task Management)
- ✅ Daftar tugas dengan tabel responsif
- ✅ Tambah tugas baru
- ✅ Edit tugas
- ✅ Hapus tugas
- ✅ Prioritas: Low, Medium, High, Highest
- ✅ Status: Pending, In Progress, Completed
- ✅ Tanggal tenggat (Due Date)
- ✅ Tag lokasi pada tugas
- ✅ Deskripsi tugas
- ✅ Visual indicators (badges dan colors)

### 4. Navigation & UI
- ✅ Sidebar navigation dengan dropdown menu
- ✅ Top header dengan search bar
- ✅ Action buttons (Plus, Help, Notification, Settings)
- ✅ Responsive sidebar (mobile-friendly)
- ✅ Active menu highlighting
- ✅ Breadcrumb navigation
- ✅ Modern card-based design

## 📁 Struktur File Penting

### Controllers
```
app/Http/Controllers/
├── AuthController.php          # Handle login/logout
├── DashboardController.php     # Dashboard & weather data
└── TaskController.php          # CRUD tugas
```

### Models
```
app/Models/
├── User.php                    # User model
└── Task.php                    # Task model
```

### Views
```
resources/views/
├── auth/
│   └── login.blade.php        # Halaman login
├── dashboard/
│   └── index.blade.php        # Dashboard utama
├── layouts/
│   └── app.blade.php          # Layout template
└── tasks/
    ├── index.blade.php        # Daftar tugas
    ├── create.blade.php       # Form tambah tugas
    └── edit.blade.php         # Form edit tugas
```

### Database
```
database/
├── migrations/
│   ├── create_users_table.php
│   └── create_tasks_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php
```

### Routes
```
routes/web.php                  # Semua routing aplikasi
```

## 🎨 Desain & UI/UX

### Color Scheme
- **Primary (Green)**: #28a745 - Untuk branding dan CTA
- **Secondary (Gray)**: #6c757d - Untuk elemen sekunder
- **Success**: #28a745 - Status sukses
- **Warning**: #ffc107 - Priority medium, status in-progress
- **Danger**: #dc3545 - Priority highest, delete actions
- **Info**: #007bff - Links dan informational

### Typography
- **Font Family**: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Font Sizes**: Responsive scaling untuk mobile dan desktop

### Responsiveness
- **Desktop**: Full sidebar, expanded layout
- **Tablet**: Collapsible sidebar
- **Mobile**: Off-canvas sidebar, stacked layout

## 🗄️ Database Schema

### Table: users
```sql
- id (bigint, PK)
- name (varchar)
- email (varchar, unique)
- email_verified_at (timestamp, nullable)
- password (varchar)
- remember_token (varchar, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Table: tasks
```sql
- id (bigint, PK)
- title (varchar)
- description (text, nullable)
- priority (enum: low, medium, high, highest)
- status (enum: pending, in_progress, completed)
- due_date (date)
- location (varchar, nullable)
- location_tagged (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

## 🔌 API Integration

### OpenWeatherMap API
- **Endpoint**: https://api.openweathermap.org/data/2.5/weather
- **Location**: Lubuk Minturun, Padang (Lat: -0.9478, Lon: 100.4172)
- **Units**: Metric (Celsius)
- **Language**: Indonesian (id)
- **Fallback**: Dummy data jika API tidak tersedia

## 🛣️ Routing Structure

```
GET  /                          → Redirect ke login
GET  /login                     → Halaman login
POST /login                     → Process login
POST /logout                    → Logout user

Protected Routes (auth middleware):
GET  /dashboard                 → Dashboard utama
GET  /tasks                     → Daftar tugas
GET  /tasks/create             → Form tambah tugas
POST /tasks                    → Simpan tugas baru
GET  /tasks/{id}/edit          → Form edit tugas
PUT  /tasks/{id}               → Update tugas
DELETE /tasks/{id}             → Hapus tugas
```

## 📱 Responsive Breakpoints

- **Mobile**: < 768px
  - Sidebar hidden by default
  - Stacked cards
  - Hidden search bar
  - Touch-optimized buttons

- **Tablet**: 768px - 991px
  - Collapsible sidebar
  - 2-column layout

- **Desktop**: >= 992px
  - Full sidebar
  - Multi-column layout
  - All features visible

## 🔐 Security Features

- ✅ CSRF Protection
- ✅ Password hashing (bcrypt)
- ✅ Authentication middleware
- ✅ Session management
- ✅ XSS protection
- ✅ SQL injection prevention (Eloquent ORM)

## 🎭 User Roles & Permissions
**Fase Saat Ini**: Single admin user
**Pengembangan Mendatang**: Multiple roles dengan permissions

## 📊 Status Pengembangan

### ✅ Completed (Phase 1)
- Authentication system
- Dashboard dengan weather widget
- Task management system
- Responsive UI/UX
- Database structure

### 🔄 In Progress (Phase 2)
- Manajemen Penanaman
- Manajemen Sertifikasi
- Manajemen Stok
- Penjualan

### 📝 Planned (Phase 3)
- Perencanaan
- Laporan & Analytics
- Peta Lahan
- Multi-user management
- Advanced permissions
- Notifications system
- Export/Import data

## 🛠️ Technology Stack

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Blade Templates, Bootstrap 5
- **Database**: MySQL/MariaDB
- **Icons**: Font Awesome 6
- **Authentication**: Laravel built-in auth
- **API**: Guzzle HTTP Client
- **Asset Building**: Vite

## 📦 Dependencies

### PHP (Composer)
- laravel/framework: ^10.10
- guzzlehttp/guzzle: ^7.2
- laravel/sanctum: ^3.3

### JavaScript (NPM)
- vite: ^5.0.0
- axios: ^1.6.4
- laravel-vite-plugin: ^1.0.0

## 🚦 Testing

### Manual Testing Checklist
- [x] Login functionality
- [x] Logout functionality
- [x] Dashboard loading
- [x] Weather data display
- [x] Task list display
- [x] Create new task
- [x] Edit task
- [x] Delete task
- [x] Responsive layout (mobile)
- [x] Responsive layout (tablet)
- [x] Responsive layout (desktop)

## 📞 Support & Maintenance

### Log Files
- Storage location: `storage/logs/laravel.log`
- Log level: Configurable via `.env` (LOG_LEVEL)

### Cache Management
```bash
php artisan cache:clear      # Clear application cache
php artisan config:clear     # Clear config cache
php artisan route:clear      # Clear route cache
php artisan view:clear       # Clear compiled views
```

### Database Backup
Recommended: Regular database backups using mysqldump or Laravel backup package

## 🎓 Credits & References
- **Design Inspiration**: Farmbrite agricultural management system
- **Framework**: Laravel Documentation
- **Weather API**: OpenWeatherMap
- **Icons**: Font Awesome
- **UI Components**: Bootstrap 5

---

**Versi Aplikasi**: 1.0.0  
**Tanggal Rilis**: 2024  
**Developer**: Tim Pengembangan SIBIT  
**License**: Proprietary - UPTD BBI TPPH





















