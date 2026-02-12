-- =====================================================
-- SIBESTI DATABASE ERD - STRUKTUR TABEL LENGKAP
-- =====================================================
-- Format: nama_kolom tipe_data(ukuran)
-- Semua Primary Key menggunakan format: nama_tabel_id VARCHAR(36)
-- Tanggal Update: Februari 2026
-- =====================================================


-- =====================================================
-- 1. TABEL SISTEM & AUTENTIKASI
-- =====================================================

-- Tabel: users
-- Deskripsi: Data pengguna sistem
CREATE TABLE users (
    user_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255),
    role ENUM('admin', 'pimpinan', 'petugas_lapangan', 'penangkar'),
    location_id VARCHAR(36),
    location_placement VARCHAR(255),
    photo_path VARCHAR(255),
    full_name VARCHAR(255),
    status ENUM('active', 'inactive'),
    contact_type ENUM('internal', 'external'),
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
    updated_at TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

-- Tabel: locations
-- Deskripsi: Data lokasi fisik/kantor
CREATE TABLE locations (
    location_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    city VARCHAR(255),
    district VARCHAR(255),
    type ENUM('kantor', 'lapangan', 'gudang', 'lainnya'),
    description TEXT,
    google_maps_link VARCHAR(500),
    photo VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tabel: password_reset_tokens
-- Deskripsi: Token reset password
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP
);

-- Tabel: personal_access_tokens
-- Deskripsi: Token akses API (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255),
    tokenable_id BIGINT,
    name VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);


-- =====================================================
-- 2. TABEL MANAJEMEN TUGAS
-- =====================================================

-- Tabel: task_templates
-- Deskripsi: Template tugas
CREATE TABLE task_templates (
    task_template_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    tasks_list JSON,
    association ENUM('penanaman', 'sertifikasi', 'gudang', 'penjualan', 'umum'),
    is_active TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tabel: task_series
-- Deskripsi: Seri tugas berulang
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

-- Tabel: tasks
-- Deskripsi: Data tugas/laporan
CREATE TABLE tasks (
    task_id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    priority ENUM('rendah', 'sedang', 'tinggi'),
    status ENUM('pending', 'in_progress', 'completed', 'cancelled'),
    due_date DATE,
    location VARCHAR(255),
    location_tagged VARCHAR(255),
    task_report TEXT,
    checklist JSON,
    attachments JSON,
    association ENUM('penanaman', 'sertifikasi', 'gudang', 'penjualan', 'umum'),
    new_status ENUM('belum_dikerjakan', 'sedang_dikerjakan', 'selesai', 'dibatalkan'),
    assigned_to VARCHAR(36),
    new_priority ENUM('rendah', 'sedang', 'tinggi', 'mendesak'),
    start_date DATE,
    start_time TIME,
    due_time TIME,
    template_id VARCHAR(36),
    series_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    planting_id VARCHAR(36),
    task_color VARCHAR(7),
    collaborators JSON,
    repeats VARCHAR(50),
    hours_spent DECIMAL(8,2),
    created_by VARCHAR(36),
    last_edited_at TIMESTAMP,
    last_edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id),
    FOREIGN KEY (template_id) REFERENCES task_templates(task_template_id),
    FOREIGN KEY (series_id) REFERENCES task_series(task_series_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    FOREIGN KEY (last_edited_by) REFERENCES users(user_id)
);


-- =====================================================
-- 3. TABEL MODUL PENANAMAN
-- =====================================================

-- Tabel: plant_types
-- Deskripsi: Jenis/tipe tanaman
CREATE TABLE plant_types (
    plant_type_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(255),
    variety TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tabel: plants
-- Deskripsi: Data tanaman individual
CREATE TABLE plants (
    plant_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    plant_type_id VARCHAR(36),
    variety VARCHAR(255),
    status ENUM('perencanaan', 'aktif', 'panen', 'selesai'),
    progress INT,
    planting_location_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_type_id) REFERENCES plant_types(plant_type_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- Tabel: planting_locations
-- Deskripsi: Lokasi penanaman/lahan
CREATE TABLE planting_locations (
    planting_location_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    location_id VARCHAR(36),
    location_type ENUM('sawah', 'kebun', 'greenhouse', 'polybag', 'lainnya'),
    planting_format ENUM('bedengan', 'baris', 'kotak', 'acak', 'lainnya'),
    planting_format_custom VARCHAR(255),
    num_beds INT,
    bed_length_m DECIMAL(10,2),
    bed_width_m DECIMAL(10,2),
    map_size DECIMAL(10,2),
    light_condition VARCHAR(255),
    description TEXT,
    location_summary TEXT,
    administrative_address TEXT,
    google_maps_link VARCHAR(500),
    land_status VARCHAR(255),
    ownership_status VARCHAR(255),
    water_source VARCHAR(255),
    soil_type VARCHAR(255),
    elevation_masl INT,
    primary_photo_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

-- Tabel: plantings
-- Deskripsi: Data penanaman
CREATE TABLE plantings (
    planting_id VARCHAR(36) PRIMARY KEY,
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    bed_label VARCHAR(255),
    days_to_emerge INT,
    spacing_between_plants DECIMAL(10,2),
    spacing_between_rows DECIMAL(10,2),
    sowing_depth DECIMAL(10,2),
    avg_height DECIMAL(10,2),
    start_method ENUM('tanam_langsung', 'baki_semai', 'pindahkan_ke_tanah', 'transplant', 'container', 'ditanam_di_baki_semai', 'batang_bawah', 'umbi', 'sambung_okulasi', 'lainnya'),
    germination_stage ENUM('benih_ditanam', 'perkecambahan', 'bibit', 'sudah_ditanam', 'vegetatif', 'berbunga', 'pematangan_buah', 'selesai'),
    seeds_per_hole INT,
    light_profile ENUM('matahari_penuh', 'matahari_penuh_sebagian', 'matahari_sebagian', 'matahari_setengah_teduh', 'setengah_teduh', 'teduh_sepenuhnya'),
    soil_condition ENUM('berkapur', 'liat', 'lempung', 'gambut', 'berpasir', 'lanau'),
    planting_detail TEXT,
    pruning_detail TEXT,
    perennial TINYINT(1),
    days_to_flower INT,
    days_to_harvest INT,
    harvest_window_days INT,
    expected_loss_rate DECIMAL(5,2),
    harvest_unit ENUM('ikat', 'barel', 'tandan', 'gantang', 'lusin', 'gram', 'batang', 'kilogram', 'kiloliter', 'liter', 'mililiter', 'satuan', 'ton'),
    expected_yield_per_hectare DECIMAL(15,2),
    quantity_planted DECIMAL(15,2),
    planted_at DATE,
    is_completed TINYINT(1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- Tabel: harvests
-- Deskripsi: Data panen
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
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    loss_quantity DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id)
);

-- Tabel: planting_losses
-- Deskripsi: Data kehilangan/kegagalan tanam
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

-- Tabel: plant_notes
-- Deskripsi: Catatan tanaman
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

-- Tabel: plant_photos
-- Deskripsi: Foto tanaman
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

-- Tabel: planting_location_notes
-- Deskripsi: Catatan lokasi penanaman
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

-- Tabel: planting_location_photos
-- Deskripsi: Foto lokasi penanaman
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
-- 4. TABEL TREATMENT & NUTRIENT
-- =====================================================

-- Tabel: treatments
-- Deskripsi: Data perlakuan/penanganan tanaman
CREATE TABLE treatments (
    treatment_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    planting_id VARCHAR(36),
    treatment_type VARCHAR(255),
    treatment_name VARCHAR(255),
    product_detail TEXT,
    opt_institution VARCHAR(255),
    application_method VARCHAR(255),
    withholding_period_days INT,
    technician VARCHAR(255),
    description TEXT,
    treatment_date DATE,
    treatment_location VARCHAR(255),
    amount_applied DECIMAL(15,2),
    unit_measurement VARCHAR(50),
    total_cost DECIMAL(15,2),
    keywords VARCHAR(255),
    responsible_person_id VARCHAR(36),
    institution_source VARCHAR(255),
    attachment VARCHAR(255),
    batch_number VARCHAR(255),
    retreat_date DATE,
    edited_at TIMESTAMP,
    edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id),
    FOREIGN KEY (edited_by) REFERENCES users(user_id)
);

-- Tabel: nutrients
-- Deskripsi: Data pemupukan/nutrisi
CREATE TABLE nutrients (
    nutrient_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    planting_id VARCHAR(36),
    product_applied VARCHAR(255),
    amount_applied DECIMAL(15,2),
    unit VARCHAR(50),
    application_method VARCHAR(255),
    application_date DATE,
    total_cost DECIMAL(15,2),
    technician VARCHAR(255),
    description TEXT,
    institution_source VARCHAR(255),
    responsible_person_id VARCHAR(36),
    attachment_id VARCHAR(36),
    edited_at TIMESTAMP,
    edited_by VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id),
    FOREIGN KEY (attachment_id) REFERENCES attachments(attachment_id),
    FOREIGN KEY (edited_by) REFERENCES users(user_id)
);

-- Tabel: expenses
-- Deskripsi: Data pengeluaran/biaya
CREATE TABLE expenses (
    expense_id VARCHAR(36) PRIMARY KEY,
    planting_location_id VARCHAR(36),
    expense_name VARCHAR(255),
    amount DECIMAL(15,2),
    expense_type ENUM('treatment', 'nutrient', 'labor', 'equipment', 'other'),
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

-- Tabel: attachments
-- Deskripsi: Lampiran dokumen
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
-- 5. TABEL SERTIFIKASI
-- =====================================================

-- Tabel: certifications
-- Deskripsi: Data sertifikasi benih
CREATE TABLE certifications (
    certification_id VARCHAR(36) PRIMARY KEY,
    harvest_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    plant_id VARCHAR(36),
    certification_status VARCHAR(255),
    seed_class_requested VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (harvest_id) REFERENCES harvests(harvest_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id)
);

-- Tabel: certification_reports
-- Deskripsi: Laporan pemeriksaan sertifikasi
CREATE TABLE certification_reports (
    certification_report_id VARCHAR(36) PRIMARY KEY,
    certification_id VARCHAR(36),
    report_number_bpsb VARCHAR(255),
    report_date DATE,
    growing_season VARCHAR(255),
    inspection_phase VARCHAR(255),
    inspector_name VARCHAR(255),
    seed_class_result VARCHAR(255),
    isolation_north DECIMAL(10,2),
    isolation_east DECIMAL(10,2),
    isolation_south DECIMAL(10,2),
    isolation_west DECIMAL(10,2),
    plant_characteristics_match TINYINT(1),
    pest_disease_condition TEXT,
    weed_condition ENUM('bersih', 'sedikit', 'sedang', 'banyak'),
    population_per_sample INT,
    other_variety_mix_count INT,
    other_variety_mix_percentage DECIMAL(5,2),
    estimated_yield DECIMAL(15,2),
    conclusion ENUM('lulus', 'tidak_lulus', 'perlu_pemeriksaan_ulang'),
    scan_file_path VARCHAR(255),
    expiry_date DATE,
    certified_seed_quantity DECIMAL(15,2),
    estimated_sale_price_per_kg DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (certification_id) REFERENCES certifications(certification_id)
);


-- =====================================================
-- 6. TABEL GUDANG & INVENTORI
-- =====================================================

-- Tabel: warehouses
-- Deskripsi: Data gudang
CREATE TABLE warehouses (
    warehouse_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    internal_id VARCHAR(50) UNIQUE,
    tracking_type ENUM('per_lot', 'aggregate'),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tabel: bins
-- Deskripsi: Data rak/bin dalam gudang
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
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
    UNIQUE (warehouse_id, internal_id)
);

-- Tabel: inventory_types
-- Deskripsi: Jenis inventori/stok
CREATE TABLE inventory_types (
    inventory_type_id VARCHAR(36) PRIMARY KEY,
    category VARCHAR(255),
    name VARCHAR(255),
    sku VARCHAR(100) UNIQUE,
    electronic_id VARCHAR(255),
    unit VARCHAR(50),
    estimated_value_per_unit DECIMAL(15,2),
    estimated_kg_per_unit DECIMAL(15,2),
    track_individual_lots TINYINT(1),
    low_stock_threshold DECIMAL(15,2),
    low_stock_unit VARCHAR(50),
    low_stock_email VARCHAR(255),
    description TEXT,
    responsible_person_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (responsible_person_id) REFERENCES users(user_id)
);

-- Tabel: inventory_lots
-- Deskripsi: Lot inventori
CREATE TABLE inventory_lots (
    inventory_lot_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    production_id VARCHAR(255),
    expiry_date DATE,
    status ENUM('available', 'reserved', 'sold', 'expired', 'damaged'),
    initial_stock DECIMAL(15,2),
    current_stock DECIMAL(15,2),
    stock_unit VARCHAR(50),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    certification_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id),
    FOREIGN KEY (certification_id) REFERENCES certifications(certification_id)
);

-- Tabel: inventory_transactions
-- Deskripsi: Transaksi inventori
CREATE TABLE inventory_transactions (
    inventory_transaction_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    inventory_lot_id VARCHAR(36),
    transaction_type ENUM('masuk', 'keluar', 'adjustment', 'transfer'),
    quantity DECIMAL(15,2),
    unit VARCHAR(50),
    warehouse_id VARCHAR(36),
    bin_id VARCHAR(36),
    reason VARCHAR(255),
    notes TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots(inventory_lot_id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Tabel: inventory_type_warehouses
-- Deskripsi: Relasi inventori-gudang
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
    FOREIGN KEY (bin_id) REFERENCES bins(bin_id),
    UNIQUE (inventory_type_id, warehouse_id, bin_id)
);

-- Tabel: inventory_notes
-- Deskripsi: Catatan inventori
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

-- Tabel: inventory_photos
-- Deskripsi: Foto inventori
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

-- Tabel: inventory_type_seeds
-- Deskripsi: Relasi inventori-benih
CREATE TABLE inventory_type_seeds (
    inventory_type_seed_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    plant_id VARCHAR(36),
    planting_location_id VARCHAR(36),
    quantity DECIMAL(15,2),
    estimated_sale_price_per_kg DECIMAL(15,2),
    expiry_date DATE,
    filled_by_user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (plant_id) REFERENCES plants(plant_id),
    FOREIGN KEY (planting_location_id) REFERENCES planting_locations(planting_location_id),
    FOREIGN KEY (filled_by_user_id) REFERENCES users(user_id)
);

-- Tabel: inventory_type_certification_reports
-- Deskripsi: Relasi inventori-laporan sertifikasi
CREATE TABLE inventory_type_certification_reports (
    inventory_type_certification_report_id VARCHAR(36) PRIMARY KEY,
    inventory_type_id VARCHAR(36),
    certification_report_id VARCHAR(36),
    quantity DECIMAL(15,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (certification_report_id) REFERENCES certification_reports(certification_report_id),
    UNIQUE (inventory_type_id, certification_report_id)
);


-- =====================================================
-- 7. TABEL PENJUALAN
-- =====================================================

-- Tabel: sales
-- Deskripsi: Data penjualan
CREATE TABLE sales (
    sale_id VARCHAR(36) PRIMARY KEY,
    receipt_number VARCHAR(50) UNIQUE,
    sale_date DATE,
    buyer_name VARCHAR(255),
    buyer_contact VARCHAR(255),
    total_amount DECIMAL(15,2),
    payment_method ENUM('tunai', 'transfer', 'kredit'),
    payment_status ENUM('pending', 'paid', 'partial', 'cancelled'),
    notes TEXT,
    user_id VARCHAR(36),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Tabel: sale_items
-- Deskripsi: Item penjualan
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
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    FOREIGN KEY (inventory_type_id) REFERENCES inventory_types(inventory_type_id),
    FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots(inventory_lot_id)
);


-- =====================================================
-- 8. TABEL RELASI USER-LOKASI PENANAMAN
-- =====================================================

-- Tabel: user_planting_location_land_manager
-- Deskripsi: Relasi pengelola lahan
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

-- Tabel: user_planting_location_land_worker
-- Deskripsi: Relasi pekerja lahan
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
-- 9. TABEL LANDING PAGE
-- =====================================================

-- Tabel: landing_page_settings
-- Deskripsi: Pengaturan halaman landing
CREATE TABLE landing_page_settings (
    landing_page_setting_id VARCHAR(36) PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE,
    value TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);


-- =====================================================
-- RINGKASAN TABEL
-- =====================================================
-- Total Tabel: 35
-- 
-- 1. Sistem & Autentikasi (4):
--    - users, locations, password_reset_tokens, personal_access_tokens
--
-- 2. Manajemen Tugas (3):
--    - task_templates, task_series, tasks
--
-- 3. Modul Penanaman (9):
--    - plant_types, plants, planting_locations, plantings, harvests,
--    - planting_losses, plant_notes, plant_photos, 
--    - planting_location_notes, planting_location_photos
--
-- 4. Treatment & Nutrient (4):
--    - treatments, nutrients, expenses, attachments
--
-- 5. Sertifikasi (2):
--    - certifications, certification_reports
--
-- 6. Gudang & Inventori (8):
--    - warehouses, bins, inventory_types, inventory_lots,
--    - inventory_transactions, inventory_type_warehouses,
--    - inventory_notes, inventory_photos, inventory_type_seeds,
--    - inventory_type_certification_reports
--
-- 7. Penjualan (2):
--    - sales, sale_items
--
-- 8. Relasi User-Lokasi (2):
--    - user_planting_location_land_manager, user_planting_location_land_worker
--
-- 9. Landing Page (1):
--    - landing_page_settings
-- =====================================================
