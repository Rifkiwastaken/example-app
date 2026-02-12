-- =====================================================
-- ERD 03 - SIBESTI Sertifikasi (Certification) Tables
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tanggal Update: Februari 2026
-- =====================================================

CREATE TABLE certifications (
    certification_id VARCHAR(36) PRIMARY KEY,
    harvest_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    plant_id VARCHAR(36),
    certification_status VARCHAR(255),
    seed_class_requested VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE certification_reports (
    certification_report_id VARCHAR(36) PRIMARY KEY,
    certification_id VARCHAR(36),
    report_number_bpsb VARCHAR(255) UNIQUE,
    report_date DATE,
    growing_season VARCHAR(255),
    inspection_phase VARCHAR(255),
    inspector_name VARCHAR(255),
    reporter_name VARCHAR(255),
    seed_class_result VARCHAR(255),
    isolation_north VARCHAR(255),
    isolation_east VARCHAR(255),
    isolation_south VARCHAR(255),
    isolation_west VARCHAR(255),
    plant_characteristics_match TINYINT(1),
    pest_disease_condition TEXT,
    weed_condition ENUM('Bersih','Cukup Bersih','Kotor'),
    population_per_sample INT,
    other_variety_mix_count INT,
    other_variety_mix_percentage DECIMAL(5,2),
    estimated_yield DECIMAL(12,2),
    conclusion ENUM('LULUS','TIDAK LULUS'),
    scan_file_path VARCHAR(255),
    expiry_date DATE,
    certified_seed_quantity DECIMAL(12,2),
    certified_seed_unit VARCHAR(50),
    seed_unit VARCHAR(50),
    seed_unit_quantity DECIMAL(12,2),
    harvest_per_unit DECIMAL(12,2),
    harvest_per_unit_unit VARCHAR(50),
    estimated_sale_price_per_kg DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
