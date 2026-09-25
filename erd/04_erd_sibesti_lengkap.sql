-- =====================================================
-- SIBESTI - ERD 04 LENGKAP (Sesuai Database Saat Ini)
-- =====================================================
-- Struktur tabel dengan NOT NULL/NULL eksplisit dan relasi PK/FK
-- Semua PK: VARCHAR(36), format {nama_tabel}_id
-- CATATAN: Tabel locations telah dihapus. Update: Februari 2026
-- =====================================================

-- -----------------------------------------------------
-- 1. USER MANAGEMENT
-- -----------------------------------------------------

-- Tabel: users (tanpa location_id; tabel locations sudah dihapus)
CREATE TABLE users (
    user_id                 VARCHAR(36)  NOT NULL,
    name                    VARCHAR(50)  NULL,
    email                   VARCHAR(255) NOT NULL,
    email_verified_at       TIMESTAMP    NULL,
    password                VARCHAR(255) NOT NULL,
    remember_token          VARCHAR(100) NULL,
    role                    ENUM('admin', 'kepala_satuan_tugas', 'petugas_sertifikasi', 'petugas_gudang', 'petugas_bbi', 'penangkar') NULL DEFAULT 'petugas_bbi',
    location_placement      VARCHAR(50)  NULL,
    photo_path              VARCHAR(50)  NULL,
    full_name               VARCHAR(50)  NULL,
    status                  ENUM('active', 'inactive') NULL DEFAULT 'active',
    contact_type            ENUM('pegawai_uptd_bbi_tpph', 'pegawai_gudang', 'petugas_sertifikasi', 'petani', 'penyuluh', 'penangkar', 'lainnya') NULL,
    organization            VARCHAR(50)  NULL,
    position                VARCHAR(50)  NULL,
    nip                     VARCHAR(50)  NULL,
    primary_phone           VARCHAR(20)  NULL,
    primary_phone_is_whatsapp TINYINT(1)  NULL,
    secondary_phone         VARCHAR(20)  NULL,
    address                 TEXT         NULL,
    province                VARCHAR(50)  NULL,
    city                    VARCHAR(50)  NULL,
    district                VARCHAR(50)  NULL,
    village                 VARCHAR(50)  NULL,
    notes                   TEXT         NULL,
    created_at              TIMESTAMP    NULL,
    updated_at              TIMESTAMP    NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY uk_users_email (email)
);

-- -----------------------------------------------------
-- 2. PLANTING MODULE
-- -----------------------------------------------------

CREATE TABLE plant_types (
    plant_type_id  VARCHAR(36)  NOT NULL,
    name           VARCHAR(50)  NULL,
    category       VARCHAR(50)  NULL,
    variety        TEXT         NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (plant_type_id)
);

-- planting_locations: tanpa location_id (tabel locations dihapus)
CREATE TABLE planting_locations (
    planting_location_id   VARCHAR(36)   NOT NULL,
    name                   VARCHAR(50)   NOT NULL,
    location_type          ENUM('lapangan', 'sawah', 'greenhouse', 'grow_room', 'padang_rumput', 'petak_ternak', 'lainnya') NOT NULL DEFAULT 'lapangan',
    location_type_custom   VARCHAR(50)   NULL,
    planting_format       ENUM('petak', 'cover_crop', 'row', 'lainnya') NOT NULL DEFAULT 'petak',
    planting_format_custom VARCHAR(50)   NULL,
    num_beds               INT           NULL,
    bed_length_m           DECIMAL(8,2)  NULL,
    bed_width_m            DECIMAL(8,2)  NULL,
    map_size               VARCHAR(50)  NULL,
    light_condition        VARCHAR(50)  NULL,
    description            TEXT         NULL,
    location_summary        VARCHAR(50)  NULL,
    administrative_address TEXT         NULL,
    google_maps_link       VARCHAR(50)  NULL,
    primary_photo_path     VARCHAR(50)  NULL,
    land_status            VARCHAR(50)  NULL,
    ownership_status       VARCHAR(50)  NULL,
    water_source           VARCHAR(50)  NULL,
    soil_type              VARCHAR(50)  NULL,
    elevation_masl         INT          NULL,
    created_at             TIMESTAMP    NULL,
    updated_at             TIMESTAMP    NULL,
    PRIMARY KEY (planting_location_id)
);

CREATE TABLE plants (
    plant_id               VARCHAR(36)  NOT NULL,
    name                   VARCHAR(50)  NULL,
    plant_type_id          VARCHAR(36)  NOT NULL,
    variety                VARCHAR(50)  NULL,
    status                 ENUM('perencanaan', 'aktif', 'panen', 'selesai') NULL,
    progress               INT          NULL,
    planting_location_id   VARCHAR(36)  NULL,
    created_at             TIMESTAMP    NULL,
    updated_at             TIMESTAMP    NULL,
    PRIMARY KEY (plant_id),
    CONSTRAINT fk_plants_plant_type FOREIGN KEY (plant_type_id) REFERENCES plant_types (plant_type_id),
    CONSTRAINT fk_plants_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE plantings (
    planting_id            VARCHAR(36)  NOT NULL,
    plant_id               VARCHAR(36)  NOT NULL,
    planting_location_id   VARCHAR(36)  NULL,
    bed_label              VARCHAR(50)  NULL,
    planting_batch_number  VARCHAR(50)  NULL,
    days_to_emerge         INT          NULL,
    spacing_between_plants VARCHAR(50)  NULL,
    spacing_between_rows   VARCHAR(50)  NULL,
    sowing_depth           VARCHAR(50)  NULL,
    avg_height             VARCHAR(50)  NULL,
    start_method           ENUM('tanam_langsung', 'baki_semai', 'pindahkan_ke_tanah', 'transplant', 'container', 'ditanam_di_baki_semai', 'batang_bawah', 'umbi', 'sambung_okulasi', 'lainnya') NULL,
    germination_stage     ENUM('benih_ditanam', 'perkecambahan', 'bibit', 'sudah_ditanam', 'vegetatif', 'berbunga', 'pematangan_buah', 'selesai') NULL,
    seeds_per_hole         INT          NULL,
    light_profile          VARCHAR(50)  NULL,
    soil_condition         VARCHAR(50)  NULL,
    planting_detail        TEXT         NULL,
    pruning_detail         TEXT         NULL,
    perennial              TINYINT(1)   NOT NULL DEFAULT 0,
    days_to_flower         INT          NULL,
    days_to_harvest        INT          NULL,
    harvest_window_days    INT          NULL,
    expected_loss_rate     VARCHAR(50)  NULL,
    harvest_unit           VARCHAR(50)  NULL,
    planting_format        VARCHAR(50)  NULL,
    planting_format_custom VARCHAR(50)  NULL,
    expected_yield_per_hectare DECIMAL(12,2) NULL,
    quantity_planted       INT          NULL,
    planted_at             DATE         NULL,
    area_ha                DECIMAL(10,2) NULL,
    estimated_harvest_date DATE         NULL,
    is_completed           TINYINT(1)   NULL,
    created_at             TIMESTAMP    NULL,
    updated_at             TIMESTAMP    NULL,
    PRIMARY KEY (planting_id),
    CONSTRAINT fk_plantings_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id) ON DELETE CASCADE,
    CONSTRAINT fk_plantings_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE harvests (
    harvest_id             VARCHAR(36)  NOT NULL,
    plant_id               VARCHAR(36)  NULL,
    planting_id            VARCHAR(36)  NULL,
    planting_location_id   VARCHAR(36)  NULL,
    harvested_at           DATE         NULL,
    batch_no               VARCHAR(50)  NULL,
    quantity               DECIMAL(15,2) NULL,
    unit                   VARCHAR(50)  NULL,
    quality                VARCHAR(50)  NULL,
    created_at             TIMESTAMP    NULL,
    updated_at             TIMESTAMP    NULL,
    PRIMARY KEY (harvest_id),
    CONSTRAINT fk_harvests_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id) ON DELETE SET NULL,
    CONSTRAINT fk_harvests_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_harvests_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE planting_losses (
    planting_loss_id VARCHAR(36) NOT NULL,
    planting_id      VARCHAR(36) NOT NULL,
    loss_date        DATE        NULL,
    loss_amount      DECIMAL(15,2) NULL,
    loss_reason      VARCHAR(50) NULL,
    description      TEXT        NULL,
    created_at       TIMESTAMP   NULL,
    updated_at       TIMESTAMP   NULL,
    PRIMARY KEY (planting_loss_id),
    CONSTRAINT fk_planting_losses_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id)
);

CREATE TABLE treatments (
    treatment_id          VARCHAR(36)  NOT NULL,
    planting_location_id  VARCHAR(36)  NULL,
    planting_id           VARCHAR(36)  NULL,
    treatment_type        VARCHAR(50)  NULL,
    treatment_name        VARCHAR(50)  NULL,
    treatment_date        DATE         NULL,
    responsible_person_id  VARCHAR(36)  NULL,
    created_at            TIMESTAMP    NULL,
    updated_at            TIMESTAMP    NULL,
    PRIMARY KEY (treatment_id),
    CONSTRAINT fk_treatments_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_treatments_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_treatments_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE nutrients (
    nutrient_id           VARCHAR(36)  NOT NULL,
    planting_location_id  VARCHAR(36)  NULL,
    planting_id           VARCHAR(36)  NULL,
    product_applied       VARCHAR(50)  NULL,
    application_date      DATE         NULL,
    responsible_person_id VARCHAR(36)  NULL,
    created_at            TIMESTAMP    NULL,
    updated_at            TIMESTAMP    NULL,
    PRIMARY KEY (nutrient_id),
    CONSTRAINT fk_nutrients_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_nutrients_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_nutrients_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE plant_notes (
    plant_note_id  VARCHAR(36) NOT NULL,
    plant_id       VARCHAR(36) NOT NULL,
    description    TEXT       NULL,
    note_date      DATE       NULL,
    created_at     TIMESTAMP  NULL,
    updated_at     TIMESTAMP  NULL,
    PRIMARY KEY (plant_note_id),
    CONSTRAINT fk_plant_notes_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id)
);

CREATE TABLE plant_photos (
    plant_photo_id VARCHAR(36)  NOT NULL,
    plant_id       VARCHAR(36)  NOT NULL,
    file_path      VARCHAR(255) NULL,
    description    TEXT        NULL,
    created_at     TIMESTAMP   NULL,
    updated_at     TIMESTAMP   NULL,
    PRIMARY KEY (plant_photo_id),
    CONSTRAINT fk_plant_photos_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id)
);

CREATE TABLE planting_location_notes (
    planting_location_note_id VARCHAR(36) NOT NULL,
    planting_location_id     VARCHAR(36) NOT NULL,
    planting_id              VARCHAR(36) NULL,
    title                    VARCHAR(255) NULL,
    description              TEXT        NULL,
    user_id                  VARCHAR(36) NULL,
    created_at               TIMESTAMP   NULL,
    updated_at               TIMESTAMP   NULL,
    PRIMARY KEY (planting_location_note_id),
    CONSTRAINT fk_planting_location_notes_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id),
    CONSTRAINT fk_planting_location_notes_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_planting_location_notes_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE planting_location_photos (
    planting_location_photo_id VARCHAR(36) NOT NULL,
    planting_location_id       VARCHAR(36) NOT NULL,
    planting_id                VARCHAR(36) NULL,
    file_path                  VARCHAR(255) NULL,
    description                TEXT        NULL,
    created_at                 TIMESTAMP   NULL,
    updated_at                 TIMESTAMP   NULL,
    PRIMARY KEY (planting_location_photo_id),
    CONSTRAINT fk_planting_location_photos_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id),
    CONSTRAINT fk_planting_location_photos_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL
);

CREATE TABLE expenses (
    expense_id             VARCHAR(36)  NOT NULL,
    planting_location_id   VARCHAR(36)  NULL,
    expense_name           VARCHAR(50)  NULL,
    amount                 DECIMAL(15,2) NULL,
    expense_type           VARCHAR(50)  NULL,
    expense_date           DATE         NULL,
    responsible_person_id  VARCHAR(36)  NULL,
    created_at             TIMESTAMP    NULL,
    updated_at             TIMESTAMP    NULL,
    PRIMARY KEY (expense_id),
    CONSTRAINT fk_expenses_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 3. CERTIFICATION MODULE
-- -----------------------------------------------------

CREATE TABLE certifications (
    certification_id       VARCHAR(36) NOT NULL,
    harvest_id             VARCHAR(36) NOT NULL,
    planting_location_id   VARCHAR(36) NULL,
    plant_id               VARCHAR(36) NULL,
    certification_status   VARCHAR(50) NULL,
    seed_class_requested   VARCHAR(50) NULL,
    created_at             TIMESTAMP   NULL,
    updated_at             TIMESTAMP   NULL,
    PRIMARY KEY (certification_id),
    CONSTRAINT fk_certifications_harvest FOREIGN KEY (harvest_id) REFERENCES harvests (harvest_id),
    CONSTRAINT fk_certifications_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_certifications_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id) ON DELETE SET NULL
);

CREATE TABLE certification_reports (
    certification_report_id VARCHAR(36) NOT NULL,
    certification_id       VARCHAR(36) NOT NULL,
    report_number_bpsb    VARCHAR(50) NULL,
    report_date           DATE        NULL,
    conclusion            VARCHAR(255) NULL,
    expiry_date           DATE        NULL,
    certified_seed_quantity DECIMAL(15,2) NULL,
    created_at            TIMESTAMP   NULL,
    updated_at            TIMESTAMP   NULL,
    PRIMARY KEY (certification_report_id),
    CONSTRAINT fk_certification_reports_certification FOREIGN KEY (certification_id) REFERENCES certifications (certification_id)
);

-- -----------------------------------------------------
-- 4. INVENTORY MODULE
-- -----------------------------------------------------

CREATE TABLE warehouses (
    warehouse_id          VARCHAR(36)  NOT NULL,
    name                  VARCHAR(50)  NULL,
    internal_id           VARCHAR(50)  NULL,
    tracking_type         VARCHAR(50)  NULL,
    description           TEXT         NULL,
    responsible_person_id  VARCHAR(36)  NULL,
    created_at            TIMESTAMP    NULL,
    updated_at            TIMESTAMP    NULL,
    PRIMARY KEY (warehouse_id),
    UNIQUE KEY uk_warehouses_internal_id (internal_id),
    CONSTRAINT fk_warehouses_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE bins (
    bin_id        VARCHAR(36)  NOT NULL,
    warehouse_id  VARCHAR(36)  NOT NULL,
    name          VARCHAR(50)  NULL,
    internal_id   VARCHAR(50)  NULL,
    max_capacity  DECIMAL(15,2) NULL,
    capacity_unit VARCHAR(50)  NULL,
    created_at    TIMESTAMP    NULL,
    updated_at    TIMESTAMP    NULL,
    PRIMARY KEY (bin_id),
    CONSTRAINT fk_bins_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id)
);

CREATE TABLE inventory_types (
    inventory_type_id     VARCHAR(36)  NOT NULL,
    category             VARCHAR(50)  NULL,
    name                 VARCHAR(50)  NULL,
    sku                  VARCHAR(100) NULL,
    unit                 VARCHAR(50)  NULL,
    estimated_value_per_unit DECIMAL(15,2) NULL,
    responsible_person_id VARCHAR(36) NULL,
    created_at           TIMESTAMP   NULL,
    updated_at           TIMESTAMP   NULL,
    PRIMARY KEY (inventory_type_id),
    UNIQUE KEY uk_inventory_types_sku (sku),
    CONSTRAINT fk_inventory_types_responsible FOREIGN KEY (responsible_person_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_lots (
    inventory_lot_id    VARCHAR(36)  NOT NULL,
    inventory_type_id   VARCHAR(36)  NOT NULL,
    production_id       VARCHAR(50)  NULL,
    status              VARCHAR(50)  NULL,
    initial_stock       DECIMAL(15,2) NULL,
    current_stock       DECIMAL(15,2) NULL,
    stock_unit          VARCHAR(50)  NULL,
    warehouse_id        VARCHAR(36)  NULL,
    bin_id              VARCHAR(36)  NULL,
    certification_id    VARCHAR(36)  NULL,
    created_at          TIMESTAMP    NULL,
    updated_at          TIMESTAMP    NULL,
    PRIMARY KEY (inventory_lot_id),
    CONSTRAINT fk_inventory_lots_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_lots_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_lots_bin FOREIGN KEY (bin_id) REFERENCES bins (bin_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_lots_certification FOREIGN KEY (certification_id) REFERENCES certifications (certification_id) ON DELETE SET NULL
);

CREATE TABLE inventory_transactions (
    inventory_transaction_id VARCHAR(36) NOT NULL,
    inventory_type_id   VARCHAR(36) NOT NULL,
    inventory_lot_id    VARCHAR(36) NULL,
    transaction_type   VARCHAR(50) NULL,
    quantity           DECIMAL(15,2) NULL,
    unit               VARCHAR(50) NULL,
    user_id            VARCHAR(36) NULL,
    created_at         TIMESTAMP   NULL,
    updated_at         TIMESTAMP   NULL,
    PRIMARY KEY (inventory_transaction_id),
    CONSTRAINT fk_inventory_transactions_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_transactions_lot FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots (inventory_lot_id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_transactions_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_type_warehouses (
    inventory_type_warehouse_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    warehouse_id   VARCHAR(36)  NOT NULL,
    bin_id         VARCHAR(36)  NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_type_warehouse_id),
    CONSTRAINT fk_inv_type_wh_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inv_type_wh_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses (warehouse_id),
    CONSTRAINT fk_inv_type_wh_bin FOREIGN KEY (bin_id) REFERENCES bins (bin_id) ON DELETE SET NULL
);

CREATE TABLE inventory_notes (
    inventory_note_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    content        TEXT         NULL,
    user_id        VARCHAR(36)  NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_note_id),
    CONSTRAINT fk_inventory_notes_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_notes_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_photos (
    inventory_photo_id VARCHAR(36) NOT NULL,
    inventory_type_id  VARCHAR(36) NOT NULL,
    photo_path        VARCHAR(255) NULL,
    user_id           VARCHAR(36) NULL,
    created_at        TIMESTAMP   NULL,
    updated_at        TIMESTAMP   NULL,
    PRIMARY KEY (inventory_photo_id),
    CONSTRAINT fk_inventory_photos_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inventory_photos_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_type_seeds (
    inventory_type_seed_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    plant_id         VARCHAR(36) NULL,
    planting_location_id VARCHAR(36) NULL,
    certification_report_id VARCHAR(36) NULL,
    quantity         DECIMAL(12,2) NULL,
    seed_unit        VARCHAR(50)  NULL,
    seed_unit_quantity DECIMAL(12,2) NULL,
    seed_per_unit    DECIMAL(12,2) NULL,
    seed_per_unit_unit VARCHAR(50) NULL,
    total_seed_quantity DECIMAL(12,2) NULL,
    total_seed_unit  VARCHAR(50) NULL,
    estimated_sale_price_per_kg DECIMAL(12,2) NULL,
    expiry_date      DATE        NULL,
    filled_by_user_id VARCHAR(36) NULL,
    edited_at        TIMESTAMP   NULL,
    edited_by        VARCHAR(36) NULL,
    created_at       TIMESTAMP   NULL,
    updated_at       TIMESTAMP   NULL,
    PRIMARY KEY (inventory_type_seed_id),
    CONSTRAINT fk_inv_type_seeds_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inv_type_seeds_plant FOREIGN KEY (plant_id) REFERENCES plants (plant_id) ON DELETE SET NULL,
    CONSTRAINT fk_inv_type_seeds_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_inv_type_seeds_cert_report FOREIGN KEY (certification_report_id) REFERENCES certification_reports (certification_report_id) ON DELETE SET NULL,
    CONSTRAINT fk_inv_type_seeds_user FOREIGN KEY (filled_by_user_id) REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_inv_type_seeds_edited_by FOREIGN KEY (edited_by) REFERENCES users (user_id) ON DELETE SET NULL
);

-- Riwayat perubahan stok benih (seed histories)
CREATE TABLE seed_histories (
    seed_history_id        VARCHAR(36) NOT NULL,
    inventory_type_id      VARCHAR(36) NULL,
    inventory_type_seed_id VARCHAR(36) NULL,
    action                VARCHAR(50) NULL,
    description           TEXT        NULL,
    old_data              JSON        NULL,
    new_data              JSON        NULL,
    user_id               VARCHAR(36) NULL,
    created_at            TIMESTAMP   NULL,
    updated_at            TIMESTAMP   NULL,
    PRIMARY KEY (seed_history_id),
    KEY idx_seed_histories_seed_action (inventory_type_seed_id, action),
    CONSTRAINT fk_seed_histories_inventory_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id) ON DELETE SET NULL,
    CONSTRAINT fk_seed_histories_seed FOREIGN KEY (inventory_type_seed_id) REFERENCES inventory_type_seeds (inventory_type_seed_id) ON DELETE SET NULL,
    CONSTRAINT fk_seed_histories_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_type_certification_reports (
    inventory_type_certification_report_id VARCHAR(36) NOT NULL,
    inventory_type_id VARCHAR(36) NOT NULL,
    certification_report_id VARCHAR(36) NOT NULL,
    quantity       DECIMAL(15,2) NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (inventory_type_certification_report_id),
    CONSTRAINT fk_inv_type_cert_rep_type FOREIGN KEY (inventory_type_id) REFERENCES inventory_types (inventory_type_id),
    CONSTRAINT fk_inv_type_cert_rep_report FOREIGN KEY (certification_report_id) REFERENCES certification_reports (certification_report_id)
);

-- -----------------------------------------------------
-- 5. SALES MODULE
-- -----------------------------------------------------

CREATE TABLE sales (
    sale_id         VARCHAR(36)  NOT NULL,
    receipt_number  VARCHAR(50)  NULL,
    sale_date       DATE         NULL,
    buyer_name      VARCHAR(50)  NULL,
    buyer_contact   VARCHAR(50)  NULL,
    total_amount    DECIMAL(15,2) NULL,
    payment_method  VARCHAR(50)  NULL,
    payment_status  VARCHAR(50)  NULL,
    planting_location_id VARCHAR(36) NULL,
    user_id         VARCHAR(36)  NULL,
    created_at      TIMESTAMP    NULL,
    updated_at      TIMESTAMP    NULL,
    PRIMARY KEY (sale_id),
    UNIQUE KEY uk_sales_receipt_number (receipt_number),
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL
);

CREATE TABLE sale_items (
    sale_item_id      VARCHAR(36)  NOT NULL,
    sale_id           VARCHAR(36)  NOT NULL,
    inventory_type_id VARCHAR(36)  NOT NULL,
    inventory_lot_id  VARCHAR(36)  NULL,
    quantity          DECIMAL(15,2) NULL,
    unit              VARCHAR(50)  NULL,
    unit_price        DECIMAL(15,2) NULL,
    subtotal          DECIMAL(15,2) NULL,
    created_at        TIMESTAMP    NULL,
    updated_at        TIMESTAMP    NULL,
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
    name            VARCHAR(50)  NULL,
    description     TEXT         NULL,
    tasks_list      JSON         NULL,
    association     VARCHAR(50)  NULL,
    is_active       TINYINT(1)   NULL,
    created_at      TIMESTAMP    NULL,
    updated_at      TIMESTAMP    NULL,
    PRIMARY KEY (task_template_id)
);

CREATE TABLE task_series (
    task_series_id VARCHAR(36)  NOT NULL,
    name           VARCHAR(50)  NULL,
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
    task_id               VARCHAR(36)  NOT NULL,
    title                 VARCHAR(50)  NULL,
    description           TEXT         NULL,
    new_status            VARCHAR(50)  NULL,
    new_priority          VARCHAR(50)  NULL,
    due_date              DATE         NULL,
    assigned_to           VARCHAR(36)  NULL,
    planting_location_id  VARCHAR(36)  NULL,
    planting_id           VARCHAR(36)  NULL,
    template_id           VARCHAR(36)  NULL,
    series_id             VARCHAR(36)  NULL,
    created_by            VARCHAR(36)  NULL,
    created_at            TIMESTAMP    NULL,
    updated_at            TIMESTAMP    NULL,
    PRIMARY KEY (task_id),
    CONSTRAINT fk_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_template FOREIGN KEY (template_id) REFERENCES task_templates (task_template_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_series FOREIGN KEY (series_id) REFERENCES task_series (task_series_id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users (user_id) ON DELETE SET NULL
);

-- -----------------------------------------------------
-- 7. SUPPORT TABLES
-- -----------------------------------------------------

CREATE TABLE attachments (
    attachment_id        VARCHAR(36)  NOT NULL,
    planting_location_id VARCHAR(36)  NULL,
    planting_id         VARCHAR(36)  NULL,
    title               VARCHAR(50)  NULL,
    file_path           VARCHAR(50)  NULL,
    created_by          VARCHAR(36)  NULL,
    created_at          TIMESTAMP    NULL,
    updated_at          TIMESTAMP    NULL,
    PRIMARY KEY (attachment_id),
    CONSTRAINT fk_attachments_planting_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id) ON DELETE SET NULL,
    CONSTRAINT fk_attachments_planting FOREIGN KEY (planting_id) REFERENCES plantings (planting_id) ON DELETE SET NULL,
    CONSTRAINT fk_attachments_created_by FOREIGN KEY (created_by) REFERENCES users (user_id) ON DELETE SET NULL
);

CREATE TABLE landing_page_settings (
    landing_page_setting_id VARCHAR(36) NOT NULL,
    key_name    VARCHAR(100) NOT NULL,
    value       TEXT         NULL,
    created_at  TIMESTAMP    NULL,
    updated_at  TIMESTAMP    NULL,
    PRIMARY KEY (landing_page_setting_id),
    UNIQUE KEY uk_landing_page_settings_key (key_name)
);

CREATE TABLE user_planting_location_land_manager (
    user_planting_location_land_manager_id VARCHAR(36) NOT NULL,
    planting_location_id VARCHAR(36) NOT NULL,
    user_id        VARCHAR(36)  NOT NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (user_planting_location_land_manager_id),
    UNIQUE KEY uk_user_pl_manager (planting_location_id, user_id),
    CONSTRAINT fk_user_pl_manager_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id),
    CONSTRAINT fk_user_pl_manager_user FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE user_planting_location_land_worker (
    user_planting_location_land_worker_id VARCHAR(36) NOT NULL,
    planting_location_id VARCHAR(36) NOT NULL,
    user_id        VARCHAR(36)  NOT NULL,
    created_at     TIMESTAMP    NULL,
    updated_at     TIMESTAMP    NULL,
    PRIMARY KEY (user_planting_location_land_worker_id),
    UNIQUE KEY uk_user_pl_worker (planting_location_id, user_id),
    CONSTRAINT fk_user_pl_worker_location FOREIGN KEY (planting_location_id) REFERENCES planting_locations (planting_location_id),
    CONSTRAINT fk_user_pl_worker_user FOREIGN KEY (user_id) REFERENCES users (user_id)
);

-- =====================================================
-- RINGKASAN ERD 04 (Sesuai database saat ini)
-- Total: 35 tabel. Tabel locations telah dihapus (ditambah seed_histories).
-- =====================================================
