-- =====================================================
-- ERD 02c - SIBESTI Inventory & Sales Module
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tanggal Update: Februari 2026
-- =====================================================

-- =====================================================
-- 1. Tabel warehouses
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

-- =====================================================
-- 2. Tabel bins
-- =====================================================
CREATE TABLE bins (
    bin_id VARCHAR(36) PRIMARY KEY,
    warehouse_id VARCHAR(36),
    name VARCHAR(255),
    internal_id VARCHAR(50),
    max_capacity DECIMAL(15,2),
    capacity_unit VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id)
);

-- =====================================================
-- 3. Tabel inventory_types
-- =====================================================
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
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id) ON DELETE SET NULL,
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- =====================================================
-- 4. Tabel inventory_lots
-- =====================================================
CREATE TABLE inventory_lots (
    inventory_lot_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36) NOT NULL,
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
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id) ON DELETE SET NULL,
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id) ON DELETE SET NULL,
    FOREIGN KEY (certification_id) REFERENCES certifications(certification_id) ON DELETE SET NULL
);

-- =====================================================
-- 5. Tabel inventory_transactions
-- =====================================================
CREATE TABLE inventory_transactions (
    inventory_transaction_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36) NOT NULL,
    inventory_lot_id VARCHAR(36),
    transaction_type ENUM('stok_masuk','stok_keluar','penyesuaian_tambah','penyesuaian_kurang','distribusi','pindah_lokasi'),
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    reason VARCHAR(255),
    notes TEXT,
    user_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots(inventory_lot_id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id) ON DELETE SET NULL,
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================================
-- 6. Tabel inventory_type_warehouses
-- =====================================================
CREATE TABLE inventory_type_warehouses (
    inventory_type_warehouse_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    warehouse_only TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id)
);

-- =====================================================
-- 7. Tabel inventory_notes
-- =====================================================
CREATE TABLE inventory_notes (
    inventory_note_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    content TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- 8. Tabel inventory_photos
-- =====================================================
CREATE TABLE inventory_photos (
    inventory_photo_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    photo_path VARCHAR(255),
    caption TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- 9. Tabel inventory_type_seeds
-- =====================================================
CREATE TABLE inventory_type_seeds (
    inventory_type_seed_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36) NOT NULL,
    plant_id VARCHAR(36) NOT NULL,
    planting_location_id VARCHAR(36) NOT NULL,
    certification_report_id VARCHAR(36),
    quantity DECIMAL(12,2),
    seed_unit VARCHAR(50),
    seed_unit_quantity DECIMAL(12,2),
    seed_per_unit DECIMAL(12,2),
    seed_per_unit_unit VARCHAR(50),
    total_seed_quantity DECIMAL(12,2),
    total_seed_unit VARCHAR(50),
    estimated_sale_price_per_kg DECIMAL(12,2),
    expiry_date DATE,
    filled_by_user_id VARCHAR(36),
    edited_at TIMESTAMP,
    edited_by VARCHAR(36),
    storage_number VARCHAR(50),
    report_type VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id) ON DELETE CASCADE,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id) ON DELETE CASCADE,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id) ON DELETE CASCADE,
    FOREIGN KEY (certification_report_id) REFERENCES certification_reports(certification_report_id) ON DELETE SET NULL,
    FOREIGN KEY (filled_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (edited_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- =====================================================
-- 10. Tabel inventory_type_certification_reports
-- =====================================================
CREATE TABLE inventory_type_certification_reports (
    inventory_type_certification_report_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36) NOT NULL,
    certification_report_id VARCHAR(36) NOT NULL,
    quantity DECIMAL(12,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id) ON DELETE CASCADE,
    FOREIGN KEY (certification_report_id) REFERENCES certification_reports(certification_report_id) ON DELETE CASCADE,
    UNIQUE (inventory_type_id, certification_report_id)
);

-- =====================================================
-- 11. Tabel seed_histories
-- =====================================================
CREATE TABLE seed_histories (
    seed_history_id VARCHAR(36) PRIMARY KEY,
    inventory_type_seed_id VARCHAR(36),
    inventory_type_id VARCHAR(36),
    action VARCHAR(50),
    description TEXT,
    old_data JSON,
    new_data JSON,
    user_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_seed_id) REFERENCES inventory_type_seeds(inventory_type_seed_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================================
-- 12. Tabel sales
-- =====================================================
CREATE TABLE sales (
    sale_id VARCHAR(36) PRIMARY KEY,
    receipt_number VARCHAR(50) UNIQUE,
    sale_date DATE,
    buyer_name VARCHAR(255),
    buyer_contact VARCHAR(255),
    total_amount DECIMAL(15,2),
    payment_method ENUM('cash','transfer_bank'),
    payment_status ENUM('lunas','belum_lunas'),
    planting_location_id VARCHAR(36),
    notes TEXT,
    user_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================================
-- 13. Tabel sale_items
-- =====================================================
CREATE TABLE sale_items (
    sale_item_id VARCHAR(36) PRIMARY KEY,
    sale_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    inventory_lot_id VARCHAR(36),
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    unit_price DECIMAL(15,2),
    subtotal DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots(inventory_lot_id)
);
