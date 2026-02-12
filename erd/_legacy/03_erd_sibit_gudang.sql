-- =====================================================
-- ERD 03 - SIBESTI Gudang (Warehouse) Tables
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tanggal Update: Februari 2026
-- =====================================================

CREATE TABLE warehouses (
    warehouse_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    internal_id VARCHAR(50) UNIQUE,
    tracking_type ENUM('bin_separated','warehouse_only'),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE bins (
    bin_id VARCHAR(36) PRIMARY KEY,
    warehouse_id VARCHAR(36),
    name VARCHAR(255),
    internal_id VARCHAR(50),
    max_capacity DECIMAL(15,2),
    capacity_unit VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_types (
    inventory_type_id VARCHAR(36) PRIMARY KEY,
    category VARCHAR(255),
    name VARCHAR(255),
    sku VARCHAR(100) UNIQUE,
    electronic_id VARCHAR(255),
    unit VARCHAR(50),
    estimated_value_per_unit DECIMAL(15,2),
    estimated_kg_per_unit DECIMAL(10,2),
    track_individual_lots TINYINT(1),
    low_stock_threshold DECIMAL(10,2),
    low_stock_unit VARCHAR(50),
    low_stock_email VARCHAR(255),
    description TEXT,
    plant_id VARCHAR(36),
    responsible_person_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_lots (
    inventory_lot_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    production_id VARCHAR(255),
    expiry_date DATE,
    status ENUM('tersedia','segera_kadaluarsa','kadaluarsa','habis') DEFAULT 'tersedia',
    initial_stock DECIMAL(15,2) DEFAULT 0,
    current_stock DECIMAL(15,2) DEFAULT 0,
    stock_unit VARCHAR(50) DEFAULT 'kg',
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    certification_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_transactions (
    inventory_transaction_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    inventory_lot_id VARCHAR(36),
    transaction_type ENUM('stok_masuk','stok_keluar','penyesuaian_tambah','penyesuaian_kurang','distribusi','pindah_lokasi'),
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    reason VARCHAR(255),
    notes TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_type_warehouses (
    inventory_type_warehouse_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    warehouse_only TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_notes (
    inventory_note_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    content TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_photos (
    inventory_photo_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    photo_path VARCHAR(255),
    caption TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_type_seeds (
    inventory_type_seed_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    certification_report_id VARCHAR(36),
    quantity DECIMAL(12,2),
    seed_unit VARCHAR(50),
    total_seed_quantity DECIMAL(12,2),
    total_seed_unit VARCHAR(50),
    estimated_sale_price_per_kg DECIMAL(12,2),
    expiry_date DATE,
    filled_by_user_id VARCHAR(36),
    storage_number VARCHAR(50),
    report_type VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE inventory_type_certification_reports (
    inventory_type_certification_report_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    certification_report_id VARCHAR(36),
    quantity DECIMAL(12,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE seed_histories (
    seed_history_id VARCHAR(36) PRIMARY KEY,
    inventory_type_seed_id VARCHAR(36),
    inventory_type_id VARCHAR(36),
    action VARCHAR(50),
    description TEXT,
    old_data JSON,
    new_data JSON,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
