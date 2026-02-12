-- =====================================================
-- SIBESTI - ERD 05 UPDATED
-- =====================================================
-- Struktur tabel dengan NOT NULL/NULL eksplisit dan relasi PK/FK
-- Versi detail (kolom tambahan sesuai ERD 05)
-- Update: Februari 2026
-- =====================================================

-- -----------------------------------------------------
-- 1. USER & LOCATION
-- -----------------------------------------------------

CREATE TABLE locations (
    location_id    VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    city           VARCHAR(255) NOT NULL,
    district       VARCHAR(255) NOT NULL,
    type           ENUM('lokasi_lahan', 'lokasi_sertifikasi', 'lokasi_gudang', 'lokasi_kantor_utama') NOT NULL,
    description    TEXT         NULL,
    google_maps_link VARCHAR(500) NULL,
    photo          VARCHAR(255) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (location_id)
);

CREATE TABLE users (
    user_id        VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    email          VARCHAR(255) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    role           ENUM('admin', 'kepala_satuan_tugas', 'petugas_sertifikasi', 'petugas_gudang', 'petugas_bbi') NOT NULL DEFAULT 'petugas_bbi',
    location_id    VARCHAR(36)  NULL,
    location_placement VARCHAR(255) NULL,
    photo_path     VARCHAR(255) NULL,
    full_name      VARCHAR(255) NULL,
    status         ENUM('active', 'inactive') NULL DEFAULT 'active',
    contact_type   ENUM('internal', 'external') NULL,
    organization   VARCHAR(255) NULL,
    position       VARCHAR(255) NULL,
    nip            VARCHAR(50)  NULL,
    primary_phone  VARCHAR(20)  NULL,
    secondary_phone VARCHAR(20) NULL,
    address        TEXT         NULL,
    province       VARCHAR(100) NULL,
    city           VARCHAR(100) NULL,
    district       VARCHAR(100) NULL,
    village        VARCHAR(100) NULL,
    notes          TEXT         NULL,
    remember_token VARCHAR(100) NULL,
    email_verified_at TIMESTAMP NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY uk_users_email (email),
    CONSTRAINT fk_users_location FOREIGN KEY (location_id) REFERENCES locations (location_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 2. PLANTING MODULE
-- -----------------------------------------------------

CREATE TABLE plant_types (
    plant_type_id  VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    category       VARCHAR(255) NULL,
    variety        TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (plant_type_id)
);

CREATE TABLE planting_locations (
    planting_location_id VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    location_id    VARCHAR(36)  NULL,
    location_type  VARCHAR(255) NULL,
    planting_format VARCHAR(255) NULL,
    map_size       DECIMAL(10,2) NULL,
    description    TEXT         NULL,
    google_maps_link VARCHAR(500) NULL,
    primary_photo_path VARCHAR(255) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (planting_location_id),
    CONSTRAINT fk_planting_locations_location FOREIGN KEY (location_id) REFERENCES locations (location_id) ON DELETE SET NULL
);

CREATE TABLE plants (
    plant_id       VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    plant_type_id  VARCHAR(36)  NOT NULL,
    variety        VARCHAR(255) NULL,
    status         ENUM('perencanaan', 'aktif', 'panen', 'selesai') NULL,
    progress       INT          NULL,
    planting_location_id VARCHAR(36) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (plant_id),
    CONSTRAINT fk_plants_plant_type FOREIGN KEY (plant_type_id) REFERENCES plant_types (plant_type_id),
    CONSTRAINT fk_plants_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE plantings (
    planting_id    VARCHAR(36)  NOT NULL,
    plant_id       VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36) NOT NULL,
    bed_label      VARCHAR(255) NULL,
    quantity_planted DECIMAL(15,2) NULL,
    planted_at     DATE         NULL,
    days_to_harvest INT          NULL,
    is_completed   TINYINT(1)   NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (planting_id),
    CONSTRAINT fk_plantings_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id),
    CONSTRAINT fk_plantings_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id)
);

CREATE TABLE harvests (
    harvest_id     VARCHAR(36)  NOT NULL,
    plant_id       VARCHAR(36)  NOT NULL,
    planting_id    VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36) NULL,
    harvested_at   DATE         NULL,
    batch_no       VARCHAR(255) NULL,
    quantity       DECIMAL(15,2) NULL,
    unit           VARCHAR(50)  NULL,
    quality        VARCHAR(255) NULL,
    loss_quantity  DECIMAL(15,2) NULL,
    note           TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (harvest_id),
    CONSTRAINT fk_harvests_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id),
    CONSTRAINT fk_harvests_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id),
    CONSTRAINT fk_harvests_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE planting_losses (
    planting_loss_id VARCHAR(36) NOT NULL,
    planting_id    VARCHAR(36)  NOT NULL,
    loss_date      DATE         NULL,
    loss_amount    DECIMAL(15,2) NULL,
    loss_reason    VARCHAR(255) NULL,
    description    TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (planting_loss_id),
    CONSTRAINT fk_planting_losses_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id)
);

CREATE TABLE treatments (
    treatment_id   VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36) NULL,
    planting_id    VARCHAR(36)  NULL,
    treatment_type VARCHAR(255) NULL,
    treatment_name VARCHAR(255) NULL,
    treatment_date DATE         NULL,
    amount_applied DECIMAL(15,2) NULL,
    total_cost     DECIMAL(15,2) NULL,
    responsible_person_id VARCHAR(36) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (treatment_id),
    CONSTRAINT fk_treatments_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_treatments_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_treatments_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE nutrients (
    nutrient_id    VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36) NULL,
    planting_id    VARCHAR(36)  NULL,
    product_applied VARCHAR(255) NULL,
    amount_applied DECIMAL(15,2) NULL,
    application_date DATE       NULL,
    total_cost     DECIMAL(15,2) NULL,
    responsible_person_id VARCHAR(36) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (nutrient_id),
    CONSTRAINT fk_nutrients_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_nutrients_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_nutrients_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 3. CERTIFICATION MODULE
-- -----------------------------------------------------

CREATE TABLE certifications (
    certification_id VARCHAR(36) NOT NULL,
    harvest_id     VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36) NULL,
    plant_id       VARCHAR(36)  NULL,
    certification_status VARCHAR(255) NULL,
    seed_class_requested VARCHAR(255) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (certification_id),
    CONSTRAINT fk_certifications_harvest FOREIGN KEY (harvest_id) REFERENCES harvests (harvest_id),
    CONSTRAINT fk_certifications_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_certifications_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id) ON DELETE SET NULL
);

CREATE TABLE certification_reports (
    certification_report_id VARCHAR(36) NOT NULL,
    certification_id VARCHAR(36) NOT NULL,
    report_number_bpsb VARCHAR(255) NULL,
    report_date   DATE         NULL,
    inspection_phase VARCHAR(255) NULL,
    inspector_name VARCHAR(255) NULL,
    seed_class_result VARCHAR(255) NULL,
    conclusion    ENUM('lulus', 'tidak_lulus', 'perlu_pemeriksaan_ulang') NULL,
    scan_file_path VARCHAR(255) NULL,
    expiry_date   DATE         NULL,
    certified_seed_quantity DECIMAL(15,2) NULL,
    created_at    TIMESTAMP    NULL,
    updated_at    TIMESTAMP    NULL,
    PRIMARY KEY (certification_report_id),
    CONSTRAINT fk_certification_reports_certification FOREIGN KEY (certification_id) REFERENCES certifications (certification_id)
);

-- -----------------------------------------------------
-- 4. INVENTORY MODULE
-- -----------------------------------------------------

CREATE TABLE warehouses (
    warehouse_id   VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NOT NULL,
    internal_id    VARCHAR(50)  NULL,
    tracking_type  ENUM('per_lot', 'aggregate') NULL,
    description    TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (warehouse_id),
    UNIQUE KEY uk_warehouses_internal_id (internal_id)
);

CREATE TABLE bins (
    bin_id         VARCHAR(36)  NOT NULL,
    warehouse_id   VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NULL,
    internal_id    VARCHAR(50)  NULL,
    max_capacity   DECIMAL(15,2) NULL,
    capacity_unit  VARCHAR(50)  NULL,
    description    TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (bin_id),
    CONSTRAINT fk_bins_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id)
);

CREATE TABLE inventory_types (
    inventory_type_id VARCHAR(36) NOT NULL,
    category       VARCHAR(255) NULL,
    name           VARCHAR(255) NOT NULL,
    sku            VARCHAR(100) NULL,
    unit           VARCHAR(50)  NULL,
    estimated_value_per_unit DECIMAL(15,2) NULL,
    track_individual_lots TINYINT(1) NULL,
    low_stock_threshold DECIMAL(15,2) NULL,
    responsible_person_id VARCHAR(36) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_type_id),
    UNIQUE KEY uk_inventory_types_sku (sku),
    CONSTRAINT fk_inventory_types_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_lots (
    inventory_lot_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    production_id  VARCHAR(255) NULL,
    expiry_date    DATE         NULL,
    status        ENUM('available', 'reserved', 'sold', 'expired', 'damaged') NULL,
    initial_stock  DECIMAL(15,2) NULL,
    current_stock  DECIMAL(15,2) NULL,
    warehouse_id   VARCHAR(36)  NULL,
    bin_id         VARCHAR(36)  NULL,
    certification_id VARCHAR(36) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_lot_id),
    CONSTRAINT fk_inventory_lots_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_lots_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_lots_bin FOREIGN KEY (bin_id) REFERENCES bins (bin_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_lots_certification FOREIGN KEY (certification_id) REFERENCES certifications (certification_id) ON DELETE SET NULL
);

CREATE TABLE inventory_transactions (
    inventory_transaction_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    inventory_lot_id VARCHAR(36) NULL,
    transaction_type ENUM('masuk', 'keluar', 'adjustment', 'transfer') NULL,
    quantity       DECIMAL(15,2) NULL,
    warehouse_id   VARCHAR(36)  NULL,
    bin_id         VARCHAR(36)  NULL,
    reason         VARCHAR(255) NULL,
    notes          TEXT         NULL,
    user_id        VARCHAR(36)  NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_transaction_id),
    CONSTRAINT fk_inventory_transactions_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_transactions_lot FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots (inventory_lot_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_transactions_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_transactions_bin FOREIGN KEY (bin_id) REFERENCES bins (bin_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_transactions_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 5. SALES MODULE
-- -----------------------------------------------------

CREATE TABLE sales (
    sale_id        VARCHAR(36)  NOT NULL,
    receipt_number VARCHAR(50)  NULL,
    sale_date      DATE         NULL,
    buyer_name     VARCHAR(255) NULL,
    buyer_contact  VARCHAR(255) NULL,
    total_amount   DECIMAL(15,2) NULL,
    payment_method ENUM('tunai', 'transfer', 'kredit') NULL,
    payment_status ENUM('pending', 'paid', 'partial', 'cancelled') NULL,
    notes          TEXT         NULL,
    user_id        VARCHAR(36)  NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (sale_id),
    UNIQUE KEY uk_sales_receipt_number (receipt_number),
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE sale_items (
    sale_item_id   VARCHAR(36)  NOT NULL,
    sale_id        VARCHAR(36)  NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    inventory_lot_id VARCHAR(36) NULL,
    quantity       DECIMAL(15,2) NULL,
    unit_price     DECIMAL(15,2) NULL,
    subtotal       DECIMAL(15,2) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (sale_item_id),
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales (sale_id),
    CONSTRAINT fk_sale_items_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_sale_items_lot FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots (inventory_lot_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 6. TASK MANAGEMENT
-- -----------------------------------------------------

CREATE TABLE task_templates (
    task_template_id VARCHAR(36) NOT NULL,
    name           VARCHAR(255) NULL,
    description    TEXT         NULL,
    tasks_list     JSON         NULL,
    association    ENUM('penanaman', 'sertifikasi', 'gudang', 'penjualan') NULL,
    is_active      TINYINT(1)   NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (task_template_id)
);

CREATE TABLE task_series (
    task_series_id VARCHAR(36)  NOT NULL,
    name           VARCHAR(255) NULL,
    description    TEXT         NULL,
    template_id    VARCHAR(36)  NULL,
    series_tasks   JSON         NULL,
    is_active      TINYINT(1)   NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (task_series_id),
    CONSTRAINT fk_task_series_template FOREIGN KEY (template_id) REFERENCES task_templates (task_template_id) ON DELETE SET NULL
);

CREATE TABLE tasks (
    task_id        VARCHAR(36)  NOT NULL,
    title          VARCHAR(255) NULL,
    description    TEXT         NULL,
    new_status     VARCHAR(255) NULL,
    new_priority   VARCHAR(255) NULL,
    due_date       DATE         NULL,
    assigned_to    VARCHAR(36)  NULL,
    planting_location_id VARCHAR(36) NULL,
    template_id    VARCHAR(36)  NULL,
    series_id      VARCHAR(36)  NULL,
    created_by     VARCHAR(36)  NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (task_id),
    CONSTRAINT fk_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_template FOREIGN KEY (template_id) REFERENCES task_templates (task_template_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_series FOREIGN KEY (series_id) REFERENCES task_series (task_series_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users (user_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 7. PLANT NOTES & PHOTOS
-- -----------------------------------------------------

CREATE TABLE plant_notes (
    plant_note_id  VARCHAR(36)  NOT NULL,
    plant_id       VARCHAR(36)  NOT NULL,
    description    TEXT         NULL,
    note_date      DATE         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (plant_note_id),
    CONSTRAINT fk_plant_notes_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id)
);

CREATE TABLE plant_photos (
    plant_photo_id VARCHAR(36)  NOT NULL,
    plant_id       VARCHAR(36)  NOT NULL,
    file_path      VARCHAR(255) NULL,
    description    TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (plant_photo_id),
    CONSTRAINT fk_plant_photos_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id)
);

-- =====================================================
-- RINGKASAN ERD 05: NOT NULL/NULL dan PK/FK sudah didefinisikan
-- =====================================================
