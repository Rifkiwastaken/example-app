-- =====================================================
-- ERD 02d - SIBESTI Support Module
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tanggal Update: Februari 2026
-- =====================================================

-- =====================================================
-- 1. Tabel plant_notes
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
-- 2. Tabel plant_photos
-- =====================================================
CREATE TABLE plant_photos (
    plant_photo_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    file_path VARCHAR(255),
    file_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    description TEXT,
    taken_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id)
);

-- =====================================================
-- 3. Tabel planting_location_notes
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
-- 4. Tabel planting_location_photos
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

-- =====================================================
-- 5. Tabel attachments
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
-- 6. Tabel landing_page_settings
-- =====================================================
CREATE TABLE landing_page_settings (
    landing_page_setting_id VARCHAR(36) PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE,
    value TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
