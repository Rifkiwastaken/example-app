-- =====================================================
-- ERD 02a - SIBESTI Core: User, Tanaman, Penanaman
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tabel locations telah dihapus (drop_locations_table)
-- Tanggal Update: Februari 2026
-- =====================================================

-- =====================================================
-- 1. Tabel users
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
    primary_phone_is_whatsapp TINYINT(1),
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
-- 2. Tabel plant_types
-- =====================================================
CREATE TABLE plant_types (
    plant_type_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- =====================================================
-- 3. Tabel planting_locations (harus dibuat sebelum plants)
-- =====================================================
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

-- =====================================================
-- 4. Tabel plants
-- =====================================================
CREATE TABLE plants (
    plant_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    plant_type_id VARCHAR(36),
    variety VARCHAR(255),
    status ENUM('perencanaan','ditanam','dipanen','selesai'),
    progress TINYINT,
    planting_location_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_type_id) REFERENCES plant_types(plant_type_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id) ON DELETE SET NULL
);

-- =====================================================
-- 5. Tabel plantings
-- =====================================================
CREATE TABLE plantings (
    planting_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    bed_label VARCHAR(255),
    days_to_emerge INT,
    spacing_between_plants VARCHAR(255),
    spacing_between_rows VARCHAR(255),
    sown_at DATE,
    start_method VARCHAR(255),
    germination_stage VARCHAR(255),
    quantity_planted INT,
    planted_at DATE,
    days_to_harvest INT,
    expected_yield_per_hectare DECIMAL(12,2),
    area_ha DECIMAL(10,2),
    perennial TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- =====================================================
-- 6. Tabel harvests
-- =====================================================
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
    loss_quantity DECIMAL(12,2),
    note TEXT,
    source VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- =====================================================
-- 7. Tabel planting_losses
-- =====================================================
CREATE TABLE planting_losses (
    planting_loss_id VARCHAR(36) PRIMARY KEY,
    planting_id VARCHAR(36),
    loss_date DATE,
    loss_amount DECIMAL(15,2),
    loss_reason VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id)
);

-- =====================================================
-- 8. Tabel treatments
-- =====================================================
CREATE TABLE treatments (
    treatment_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    treatment_type VARCHAR(255),
    treatment_name VARCHAR(255),
    product_detail VARCHAR(255),
    application_method VARCHAR(255),
    treatment_date DATE,
    amount_applied DECIMAL(10,2),
    unit_measurement VARCHAR(255),
    total_cost DECIMAL(10,2),
    record_expense TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- =====================================================
-- 9. Tabel nutrients
-- =====================================================
CREATE TABLE nutrients (
    nutrient_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    planting_id VARCHAR(36),
    nutrient_name VARCHAR(255),
    product_applied VARCHAR(255),
    amount_applied DECIMAL(10,2),
    unit VARCHAR(50),
    application_method VARCHAR(255),
    application_date DATE,
    total_cost DECIMAL(15,2),
    technician VARCHAR(255),
    description TEXT,
    responsible_person_id VARCHAR(36),
    edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (edited_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- =====================================================
-- 10. Tabel expenses
-- =====================================================
CREATE TABLE expenses (
    expense_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    expense_name VARCHAR(255),
    amount DECIMAL(15,2),
    expense_type ENUM('treatment','nutrient','labor','equipment','other'),
    expense_date DATE,
    treatment_id VARCHAR(36),
    nutrient_id VARCHAR(36),
    responsible_person_id VARCHAR(36),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (treatment_id) REFERENCES treatments(treatment_id),
    FOREIGN KEY (nutrient_id) REFERENCES nutrients(nutrient_id),
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id)
);

-- =====================================================
-- 11. Tabel attachments
-- =====================================================
CREATE TABLE attachments (
    attachment_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    title VARCHAR(255),
    description TEXT,
    attachment_date DATE,
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    created_by VARCHAR(36),
    edited_at TIMESTAMP,
    edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (edited_by) REFERENCES users(user_id)
);

-- =====================================================
-- 12. Tabel task_templates
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

-- =====================================================
-- 13. Tabel task_series
-- =====================================================
CREATE TABLE task_series (
    task_series_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    template_id VARCHAR(36),
    series_tasks JSON,
    is_active TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES task_templates(task_template_id)
);

-- =====================================================
-- 14. Tabel tasks
-- =====================================================
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
    updated_at TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (template_id) REFERENCES task_templates(task_template_id) ON DELETE SET NULL,
    FOREIGN KEY (series_id) REFERENCES task_series(task_series_id) ON DELETE SET NULL,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id) ON DELETE SET NULL,
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- =====================================================
-- 15. Tabel user_planting_location_land_manager
-- =====================================================
CREATE TABLE user_planting_location_land_manager (
    user_planting_location_land_manager_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE (planting_location_id, user_id)
);

-- =====================================================
-- 16. Tabel user_planting_location_land_worker
-- =====================================================
CREATE TABLE user_planting_location_land_worker (
    user_planting_location_land_worker_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE (planting_location_id, user_id)
);

-- =====================================================
-- 17. Tabel plant_notes
-- =====================================================
CREATE TABLE plant_notes (
    plant_note_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    description TEXT,
    note_date DATE,
    keywords VARCHAR(255),
    attachment_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id)
);

-- =====================================================
-- 18. Tabel plant_photos
-- =====================================================
CREATE TABLE plant_photos (
    plant_photo_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(255),
    description TEXT,
    taken_at DATETIME,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id)
);

-- =====================================================
-- 19. Tabel planting_location_notes
-- =====================================================
CREATE TABLE planting_location_notes (
    planting_location_note_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    title VARCHAR(255),
    description TEXT,
    note_date DATE,
    keywords VARCHAR(255),
    attachment_path VARCHAR(255),
    user_id VARCHAR(36),
    assigned_to JSON,
    read_by JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- 20. Tabel planting_location_photos
-- =====================================================
CREATE TABLE planting_location_photos (
    planting_location_photo_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    description TEXT,
    taken_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);
