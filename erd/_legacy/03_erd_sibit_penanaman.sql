-- =====================================================
-- ERD 03 - SIBESTI Penanaman (Planting) Tables
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tabel locations telah dihapus
-- Tanggal Update: Februari 2026
-- =====================================================

CREATE TABLE plant_types (
    plant_type_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(255),
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

CREATE TABLE plantings (
    planting_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    bed_label VARCHAR(255),
    days_to_emerge INT,
    spacing_between_plants VARCHAR(255),
    spacing_between_rows VARCHAR(255),
    sown_at DATE,
    quantity_planted INT,
    planted_at DATE,
    days_to_harvest INT,
    expected_yield_per_hectare DECIMAL(12,2),
    area_ha DECIMAL(10,2),
    perennial TINYINT(1),
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
    note TEXT,
    source VARCHAR(255),
    quality VARCHAR(255),
    quantity DECIMAL(12,2),
    unit VARCHAR(50),
    loss_quantity DECIMAL(12,2),
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
    responsible_person_id VARCHAR(36),
    edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

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
    updated_at TIMESTAMP
);

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
    updated_at TIMESTAMP
);

CREATE TABLE plant_notes (
    plant_note_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    description TEXT,
    note_date DATE,
    keywords VARCHAR(255),
    attachment_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

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
    updated_at TIMESTAMP
);

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
    updated_at TIMESTAMP
);

CREATE TABLE planting_location_photos (
    planting_location_photo_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(100),
    description TEXT,
    taken_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

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
    updated_at TIMESTAMP
);
