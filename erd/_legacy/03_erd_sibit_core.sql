-- =====================================================
-- ERD 03 - SIBESTI Core Tables
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tabel locations telah dihapus (drop_locations_table)
-- Tanggal Update: Februari 2026
-- =====================================================

-- =====================================================
-- TABEL SISTEM & AUTENTIKASI
-- =====================================================

CREATE TABLE users (
    user_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255),
    role ENUM('admin','kepala_satuan_tugas','petugas_sertifikasi','petugas_gudang','petugas_bbi','penangkar'),
    location_placement VARCHAR(255),
    photo_path VARCHAR(255),
    full_name VARCHAR(255),
    status ENUM('active','inactive'),
    contact_type ENUM('pegawai_uptd_bbi_tpph','pegawai_gudang','petugas_sertifikasi','petani','penyuluh','penangkar','lainnya'),
    organization VARCHAR(255),
    position VARCHAR(255),
    nip VARCHAR(50),
    primary_phone VARCHAR(20),
    secondary_phone VARCHAR(20),
    address TEXT,
    province VARCHAR(100),
    city VARCHAR(100),
    district VARCHAR(100),
    village VARCHAR(100),
    notes TEXT,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- =====================================================
-- TABEL TANAMAN
-- =====================================================

CREATE TABLE plant_types (
    plant_type_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE planting_locations (
    planting_location_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    location_type ENUM('lapangan','greenhouse','grow_room','padang_rumput','petak_ternak','lainnya'),
    planting_format ENUM('petak','cover_crop','row','lainnya'),
    num_beds INT,
    bed_length_m DECIMAL(8,2),
    bed_width_m DECIMAL(8,2),
    map_size VARCHAR(255),
    light_condition VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE plants (
    plant_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    plant_type_id VARCHAR(36),
    variety VARCHAR(255),
    status ENUM('perencanaan','ditanam','dipanen','selesai'),
    progress TINYINT,
    planting_location_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE plantings (
    planting_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    bed_label VARCHAR(255),
    quantity_planted INT,
    planted_at DATE,
    days_to_harvest INT,
    area_ha DECIMAL(10,2),
    is_completed TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE harvests (
    harvest_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    planting_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    harvested_at DATE,
    batch_no VARCHAR(255),
    quantity DECIMAL(12,2),
    unit VARCHAR(50),
    quality VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE planting_losses (
    planting_loss_id VARCHAR(36) PRIMARY KEY,
    planting_id VARCHAR(36),
    loss_date DATE,
    loss_amount DECIMAL(12,2),
    loss_reason VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- =====================================================
-- TABEL TUGAS
-- =====================================================

CREATE TABLE task_templates (
    task_template_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    tasks_list JSON,
    association ENUM('penanaman','sertifikasi','gudang','penjualan','umum'),
    is_active TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE task_series (
    task_series_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    template_id VARCHAR(36),
    series_tasks JSON,
    is_active TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE tasks (
    task_id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    priority ENUM('low','medium','high','highest'),
    status ENUM('pending','in_progress','completed'),
    due_date DATE,
    location VARCHAR(255),
    planting_location_id VARCHAR(36),
    planting_id VARCHAR(36),
    assigned_to VARCHAR(36),
    template_id VARCHAR(36),
    series_id VARCHAR(36),
    created_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- =====================================================
-- TABEL RELASI USER-LOKASI
-- =====================================================

CREATE TABLE user_planting_location_land_manager (
    user_planting_location_land_manager_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE user_planting_location_land_worker (
    user_planting_location_land_worker_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
