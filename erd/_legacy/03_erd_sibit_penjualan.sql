-- =====================================================
-- ERD 03 - SIBESTI Penjualan (Sales) Tables
-- Sistem Informasi Benih Bersertifikat
-- Semua ID menggunakan VARCHAR(36) dengan format {nama_tabel}_id
-- Tanggal Update: Februari 2026
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
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE sale_items (
    sale_item_id VARCHAR(36) PRIMARY KEY,
    sale_id VARCHAR(36),
    inventory_type_id VARCHAR(36),
    inventory_lot_id VARCHAR(36),
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    unit_price DECIMAL(15,2),
    subtotal DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
