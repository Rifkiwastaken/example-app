-- Backup Database SIBESTI
-- Generated: 2026-02-05 05:34:48
-- Database: sibit

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for `attachments`
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `attachment_date` date NOT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_planting_location_id_foreign` (`planting_location_id`),
  KEY `attachments_created_by_foreign` (`created_by`),
  KEY `attachments_edited_by_foreign` (`edited_by`),
  CONSTRAINT `attachments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachments_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attachments_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `bins`
DROP TABLE IF EXISTS `bins`;
CREATE TABLE `bins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` bigint unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_capacity` decimal(15,2) NOT NULL,
  `capacity_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bins_warehouse_id_internal_id_unique` (`warehouse_id`,`internal_id`),
  CONSTRAINT `bins_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `certification_reports`
DROP TABLE IF EXISTS `certification_reports`;
CREATE TABLE `certification_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `report_number_bpsb` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `report_date` date NOT NULL,
  `growing_season` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspection_phase` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspector_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reporter_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_class_result` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isolation_north` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isolation_east` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isolation_south` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isolation_west` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_characteristics_match` tinyint(1) DEFAULT NULL,
  `pest_disease_condition` text COLLATE utf8mb4_unicode_ci,
  `weed_condition` enum('Bersih','Cukup Bersih','Kotor') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `population_per_sample` int DEFAULT NULL,
  `other_variety_mix_count` int DEFAULT NULL,
  `other_variety_mix_percentage` decimal(5,2) DEFAULT NULL,
  `estimated_yield` decimal(12,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `certified_seed_quantity` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah benih yang lulus sertifikasi dalam kg',
  `certified_seed_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_unit_quantity` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah satuan benih',
  `harvest_per_unit` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah panen per satuan benih',
  `harvest_per_unit_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_sale_price_per_kg` decimal(12,2) DEFAULT NULL COMMENT 'Estimasi penjualan per kg',
  `conclusion` enum('LULUS','TIDAK LULUS') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scan_file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `planting_batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certification_reports_report_number_bpsb_unique` (`report_number_bpsb`),
  KEY `certification_reports_certification_id_foreign` (`certification_id`),
  CONSTRAINT `certification_reports_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `certifications`
DROP TABLE IF EXISTS `certifications`;
CREATE TABLE `certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` bigint unsigned NOT NULL,
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `plant_id` bigint unsigned DEFAULT NULL,
  `certification_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_class_requested` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certifications_harvest_id_foreign` (`harvest_id`),
  KEY `certifications_planting_location_id_foreign` (`planting_location_id`),
  KEY `certifications_plant_id_foreign` (`plant_id`),
  CONSTRAINT `certifications_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certifications_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certifications_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `expenses`
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `expense_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_date` date NOT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `work_description` text COLLATE utf8mb4_unicode_ci,
  `worker_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `treatment_id` bigint unsigned DEFAULT NULL,
  `nutrient_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_planting_location_id_foreign` (`planting_location_id`),
  KEY `expenses_treatment_id_foreign` (`treatment_id`),
  KEY `expenses_nutrient_id_foreign` (`nutrient_id`),
  KEY `expenses_planting_id_foreign` (`planting_id`),
  KEY `expenses_edited_by_foreign` (`edited_by`),
  KEY `expenses_responsible_person_id_foreign` (`responsible_person_id`),
  CONSTRAINT `expenses_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_nutrient_id_foreign` FOREIGN KEY (`nutrient_id`) REFERENCES `nutrients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_treatment_id_foreign` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `failed_jobs`
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `harvests`
DROP TABLE IF EXISTS `harvests`;
CREATE TABLE `harvests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvested_at` date NOT NULL,
  `batch_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quality` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_quantity` decimal(12,2) DEFAULT NULL,
  `quantity_per_unit` decimal(12,2) DEFAULT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `new_recorded_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loss_quantity` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `harvests_harvest_id_unique` (`harvest_id`),
  KEY `harvests_plant_id_foreign` (`plant_id`),
  KEY `harvests_planting_id_foreign` (`planting_id`),
  KEY `harvests_planting_location_id_foreign` (`planting_location_id`),
  KEY `harvests_recorded_by_foreign` (`recorded_by`),
  KEY `harvests_edited_by_foreign` (`edited_by`),
  CONSTRAINT `harvests_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `harvests_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvests_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_lots`
DROP TABLE IF EXISTS `inventory_lots`;
CREATE TABLE `inventory_lots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `production_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `current_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `certification_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_lots_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_lots_warehouse_id_foreign` (`warehouse_id`),
  KEY `inventory_lots_bin_id_foreign` (`bin_id`),
  KEY `inventory_lots_certification_id_foreign` (`certification_id`),
  CONSTRAINT `inventory_lots_bin_id_foreign` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_lots_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_lots_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_lots_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_notes`
DROP TABLE IF EXISTS `inventory_notes`;
CREATE TABLE `inventory_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_notes_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_notes_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_photos`
DROP TABLE IF EXISTS `inventory_photos`;
CREATE TABLE `inventory_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `photo_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_photos_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_photos_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_photos_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_photos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_transactions`
DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE `inventory_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `inventory_lot_id` bigint unsigned DEFAULT NULL,
  `transaction_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `reason` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_transactions_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_transactions_inventory_lot_id_foreign` (`inventory_lot_id`),
  KEY `inventory_transactions_warehouse_id_foreign` (`warehouse_id`),
  KEY `inventory_transactions_bin_id_foreign` (`bin_id`),
  KEY `inventory_transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_transactions_bin_id_foreign` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_transactions_inventory_lot_id_foreign` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_transactions_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_transactions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_type_certification_reports`
DROP TABLE IF EXISTS `inventory_type_certification_reports`;
CREATE TABLE `inventory_type_certification_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `certification_report_id` bigint unsigned NOT NULL,
  `quantity` decimal(12,2) NOT NULL COMMENT 'Jumlah benih yang ditambahkan ke stok bibit',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_type_cert_report_unique` (`inventory_type_id`,`certification_report_id`),
  KEY `inv_type_cert_reports_cert_fk` (`certification_report_id`),
  CONSTRAINT `inv_type_cert_reports_cert_fk` FOREIGN KEY (`certification_report_id`) REFERENCES `certification_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_cert_reports_inv_type_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_type_seeds`
DROP TABLE IF EXISTS `inventory_type_seeds`;
CREATE TABLE `inventory_type_seeds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `quantity` decimal(12,2) NOT NULL COMMENT 'Jumlah benih yang ditambahkan',
  `seed_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_unit_quantity` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah satuan benih',
  `seed_per_unit` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah benih per satuan benih',
  `seed_per_unit_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_seed_quantity` decimal(12,2) DEFAULT NULL COMMENT 'Jumlah benih total',
  `total_seed_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_sale_price_per_kg` decimal(12,2) DEFAULT NULL COMMENT 'Estimasi penjualan per kg',
  `expiry_date` date DEFAULT NULL COMMENT 'Tanggal kadaluarsa',
  `filled_by_user_id` bigint unsigned DEFAULT NULL COMMENT 'User yang mengisi data',
  `storage_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor penyimpanan (dapat diedit oleh user)',
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jenis laporan BPSB',
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inv_type_seeds_plant_fk` (`plant_id`),
  KEY `inv_type_seeds_location_fk` (`planting_location_id`),
  KEY `inv_type_seeds_user_fk` (`filled_by_user_id`),
  KEY `inventory_type_seeds_inventory_type_id_plant_id_index` (`inventory_type_id`,`plant_id`),
  KEY `inventory_type_seeds_edited_by_foreign` (`edited_by`),
  CONSTRAINT `inv_type_seeds_inv_type_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_location_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_plant_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_user_fk` FOREIGN KEY (`filled_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_type_seeds_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_type_warehouses`
DROP TABLE IF EXISTS `inventory_type_warehouses`;
CREATE TABLE `inventory_type_warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `warehouse_only` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_type_wh_bin_unique` (`inventory_type_id`,`warehouse_id`,`bin_id`),
  KEY `inventory_type_warehouses_warehouse_id_foreign` (`warehouse_id`),
  KEY `inventory_type_warehouses_bin_id_foreign` (`bin_id`),
  CONSTRAINT `inventory_type_warehouses_bin_id_foreign` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_type_warehouses_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_type_warehouses_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `inventory_types`
DROP TABLE IF EXISTS `inventory_types`;
CREATE TABLE `inventory_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` bigint unsigned DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `electronic_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_value_per_unit` decimal(15,2) DEFAULT NULL,
  `estimated_kg_per_unit` decimal(10,2) DEFAULT NULL,
  `track_individual_lots` tinyint(1) NOT NULL DEFAULT '0',
  `low_stock_threshold` decimal(10,2) DEFAULT NULL,
  `low_stock_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `low_stock_email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_types_sku_unique` (`sku`),
  KEY `inventory_types_responsible_person_id_foreign` (`responsible_person_id`),
  KEY `inventory_types_plant_id_foreign` (`plant_id`),
  CONSTRAINT `inventory_types_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_types_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `landing_page_settings`
DROP TABLE IF EXISTS `landing_page_settings`;
CREATE TABLE `landing_page_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landing_page_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `landing_page_settings`
LOCK TABLES `landing_page_settings` WRITE;
INSERT INTO `landing_page_settings` (`id`,`key`,`value`,`created_at`,`updated_at`) VALUES
('1','hero_title','Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('2','hero_subtitle','Pantau ketersediaan stok benih padi bersertifikat secara real-time dari seluruh unit UPTD BBI TPPH.','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('3','hero_image','https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('4','office_address','UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura<br>Jl. Raya Padang - Bukittinggi KM 15<br>Lubuk Minturun, Padang, Sumatera Barat<br>Kode Pos: 25163','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('5','office_phone','(0751) 123456','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('6','office_whatsapp','+62 812-3456-7890','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('7','office_email','info@bbitpph.sumbar.go.id','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('8','facebook_url','#','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('9','instagram_url','#','2026-02-05 05:05:37','2026-02-05 05:05:37'),
('10','youtube_url','#','2026-02-05 05:05:37','2026-02-05 05:05:37');
UNLOCK TABLES;

-- Table structure for `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `migrations`
LOCK TABLES `migrations` WRITE;
INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES
('1','2014_10_12_000000_create_users_table','1'),
('2','2014_10_12_100000_create_password_reset_tokens_table','1'),
('3','2019_08_19_000000_create_failed_jobs_table','1'),
('4','2019_12_14_000001_create_personal_access_tokens_table','1'),
('5','2024_01_01_000000_create_tasks_table','1'),
('6','2024_01_02_000000_create_locations_table','1'),
('7','2024_01_02_000001_add_role_and_location_to_users_table','1'),
('8','2024_01_03_000001_create_task_templates_table','1'),
('9','2024_01_03_000002_create_task_series_table','1'),
('10','2024_01_03_000003_safe_update_tasks_table','1'),
('11','2025_01_01_000100_create_plant_types_table','1'),
('12','2025_01_01_000110_create_plants_table','1'),
('13','2025_01_01_000120_create_planting_locations_table','1'),
('14','2025_01_01_000130_create_plantings_table','1'),
('15','2025_01_01_000140_create_harvests_table','1'),
('16','2025_01_01_000150_add_fk_to_plants_after_locations_exist','1'),
('17','2025_01_01_000160_create_plant_notes_table','1'),
('18','2025_01_01_000170_create_plant_photos_table','1'),
('19','2025_01_01_000180_create_treatments_table','1'),
('20','2025_01_01_000190_create_nutrients_table','1'),
('21','2025_01_01_000200_create_user_planting_location_tables','1'),
('22','2025_01_02_000100_create_certifications_table','1'),
('23','2025_01_02_000110_create_certification_reports_table','1'),
('24','2025_01_03_000100_create_warehouses_table','1'),
('25','2025_01_03_000110_create_bins_table','1'),
('26','2025_01_03_000120_create_inventory_types_table','1'),
('27','2025_01_03_000130_create_inventory_lots_table','1'),
('28','2025_01_03_000140_create_inventory_transactions_table','1'),
('29','2025_01_03_000150_create_inventory_type_warehouses_table','1'),
('30','2025_01_03_000160_create_inventory_notes_table','1'),
('31','2025_01_03_000170_create_inventory_photos_table','1'),
('32','2025_01_04_000100_create_sales_table','1'),
('33','2025_01_04_000110_create_sale_items_table','1'),
('34','2025_01_04_000200_update_planting_format_enum','1'),
('35','2025_01_05_000100_create_planting_location_notes_table','1'),
('36','2025_01_05_000110_create_planting_location_photos_table','1'),
('37','2025_01_05_000120_add_planting_location_to_tasks_table','1'),
('38','2025_01_05_000130_add_fields_to_treatments_table','1'),
('39','2025_01_05_000140_add_unit_to_nutrients_table','1'),
('40','2025_01_05_000150_add_fields_to_certifications_table','1'),
('41','2025_01_05_000160_add_expiry_date_to_certification_reports_table','1'),
('42','2025_01_06_000000_create_planting_losses_table','1'),
('43','2025_01_20_000000_add_penangkar_role_and_user_details_to_users_table','1'),
('44','2025_01_21_000000_update_nutrients_table_remove_nutrient_fields_add_new_fields','1'),
('45','2025_01_22_000000_add_new_fields_to_treatments_table','1'),
('46','2025_01_22_000001_add_responsible_person_to_expenses_table','1'),
('47','2025_01_30_000000_add_last_edited_to_tasks_table','1'),
('48','2025_01_31_000000_create_expenses_table','1'),
('49','2025_01_31_100000_add_assigned_to_and_read_by_to_planting_location_notes','1'),
('50','2025_01_31_200000_add_certified_seed_fields_to_certification_reports_table','1'),
('51','2025_02_01_000000_create_inventory_type_certification_reports_table','1'),
('52','2025_02_01_100000_create_inventory_type_seeds_table','1'),
('53','2025_02_02_000000_create_attachments_table','1'),
('54','2025_02_03_000000_add_institution_source_to_nutrients_table','1'),
('55','2025_02_03_100000_add_nutrient_name_to_nutrients_table','1'),
('56','2025_02_03_200000_add_edited_fields_to_nutrients_table','1'),
('57','2025_02_04_000000_add_fields_to_expenses_table','1'),
('58','2025_02_04_100000_add_responsible_person_and_attachment_to_nutrients_table','1'),
('59','2025_02_04_200000_add_edited_fields_to_treatments_table','1'),
('60','2025_02_05_000000_add_responsible_person_to_warehouses_table','1'),
('61','2025_11_13_010000_update_planting_locations_with_management_fields','1'),
('62','2025_11_29_045256_add_created_by_to_tasks_table','1'),
('63','2025_12_06_000000_add_harvest_fields_to_harvests_table','1'),
('64','2025_12_06_100000_add_edited_fields_to_harvests_table','1'),
('65','2025_12_06_200000_add_is_completed_to_plantings_table','1'),
('66','2025_12_07_204116_add_seed_unit_fields_to_certification_reports_table','1'),
('67','2025_12_07_212600_add_responsible_person_to_inventory_types_table','1'),
('68','2025_12_07_213000_add_unit_fields_to_certification_reports_table','1'),
('69','2025_12_07_220000_add_fields_to_inventory_type_seeds_table','1'),
('70','2025_12_07_221000_create_seed_histories_table','1'),
('71','2025_12_08_000000_add_estimated_harvest_date_to_plantings_table','1'),
('72','2025_12_09_000000_add_planting_format_to_plantings_table','1'),
('73','2025_12_09_100000_add_location_type_custom_to_planting_locations_table','1'),
('74','2025_12_09_200000_update_location_type_enum_to_include_sawah','1'),
('75','2025_12_11_000300_add_plant_id_to_inventory_types_table','1'),
('76','2025_12_11_124653_add_planting_location_id_to_sales_table','1'),
('77','2025_12_11_190123_add_area_ha_to_plantings_table','1'),
('78','2025_12_12_000726_add_report_type_to_certification_reports_table','1'),
('79','2025_12_12_000757_make_report_number_bpsb_unique_in_certification_reports','1'),
('80','2025_12_16_140042_drop_contacts_and_related_tables','1'),
('81','2025_12_16_140913_drop_planning_tables_production_targets_budgets','1'),
('82','2025_12_16_155019_drop_locations_table','1'),
('83','2026_01_07_135555_add_buyer_and_distribution_fields_to_sales_table','1'),
('84','2026_01_07_142115_limit_all_varchar_fields_to_50_characters','1'),
('85','2026_01_11_210643_create_landing_page_settings_table','1'),
('86','2026_01_11_215355_add_password_plain_to_users_table','1'),
('87','2026_01_11_225846_add_renew_from_report_id_to_certification_reports_table','1'),
('88','2026_01_12_054533_add_certification_report_id_to_inventory_type_seeds_table','1'),
('89','2026_01_12_084541_add_stock_number_to_inventory_type_seeds_table','1'),
('90','2026_01_13_045054_add_planting_batch_number_to_plantings_table','1'),
('91','2026_01_14_164037_add_certification_report_id_to_inventory_type_seeds_table','1'),
('92','2026_01_18_155836_add_storage_number_and_report_type_to_inventory_type_seeds_table','1'),
('93','2026_01_28_204318_add_variety_to_plant_types_table','1'),
('94','2026_02_02_004607_add_missing_columns_to_expenses_table','1'),
('95','2026_02_03_134748_add_batch_numbers_to_certification_reports_table','1');
UNLOCK TABLES;

-- Table structure for `nutrients`
DROP TABLE IF EXISTS `nutrients`;
CREATE TABLE `nutrients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nutrient_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `product_applied` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_applied` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `technician` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `attachment` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nutrients_planting_location_id_foreign` (`planting_location_id`),
  KEY `nutrients_planting_id_foreign` (`planting_id`),
  KEY `nutrients_edited_by_foreign` (`edited_by`),
  KEY `nutrients_responsible_person_id_foreign` (`responsible_person_id`),
  CONSTRAINT `nutrients_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nutrients_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nutrients_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nutrients_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `personal_access_tokens`
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `plant_notes`
DROP TABLE IF EXISTS `plant_notes`;
CREATE TABLE `plant_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `keywords` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plant_notes_plant_id_foreign` (`plant_id`),
  CONSTRAINT `plant_notes_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `plant_photos`
DROP TABLE IF EXISTS `plant_photos`;
CREATE TABLE `plant_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` bigint unsigned NOT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `taken_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plant_photos_plant_id_foreign` (`plant_id`),
  CONSTRAINT `plant_photos_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `plant_types`
DROP TABLE IF EXISTS `plant_types`;
CREATE TABLE `plant_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variety` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plant_types_plant_type_id_unique` (`plant_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `plant_types`
LOCK TABLES `plant_types` WRITE;
INSERT INTO `plant_types` (`id`,`plant_type_id`,`name`,`category`,`variety`,`created_at`,`updated_at`) VALUES
('1',NULL,'Padi','pangan','Anak daro','2026-02-05 05:26:47','2026-02-05 05:26:47');
UNLOCK TABLES;

-- Table structure for `planting_location_notes`
DROP TABLE IF EXISTS `planting_location_notes`;
CREATE TABLE `planting_location_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `keywords` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `assigned_to` json DEFAULT NULL,
  `read_by` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planting_location_notes_planting_location_id_foreign` (`planting_location_id`),
  KEY `planting_location_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `planting_location_notes_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planting_location_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `planting_location_photos`
DROP TABLE IF EXISTS `planting_location_photos`;
CREATE TABLE `planting_location_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `taken_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planting_location_photos_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `planting_location_photos_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `planting_locations`
DROP TABLE IF EXISTS `planting_locations`;
CREATE TABLE `planting_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_summary` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `administrative_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_maps_link` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_photo_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_type` enum('lapangan','sawah','greenhouse','grow_room','padang_rumput','petak_ternak','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT 'lapangan',
  `location_type_custom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_format` enum('ditanam_dalam_petak','cover_crop','row_crop','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT 'ditanam_dalam_petak',
  `planting_format_custom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_beds` int unsigned DEFAULT NULL,
  `bed_length_m` decimal(8,2) DEFAULT NULL,
  `bed_width_m` decimal(8,2) DEFAULT NULL,
  `map_size` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `light_condition` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `land_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownership_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `water_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soil_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `elevation_masl` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_locations_planting_location_id_unique` (`planting_location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `planting_losses`
DROP TABLE IF EXISTS `planting_losses`;
CREATE TABLE `planting_losses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_id` bigint unsigned NOT NULL,
  `loss_date` date NOT NULL,
  `loss_amount` decimal(12,2) NOT NULL,
  `loss_reason` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planting_losses_planting_id_foreign` (`planting_id`),
  CONSTRAINT `planting_losses_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `plantings`
DROP TABLE IF EXISTS `plantings`;
CREATE TABLE `plantings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bed_label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_to_emerge` int unsigned DEFAULT NULL,
  `spacing_between_plants` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spacing_between_rows` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sowing_depth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_height` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `germination_stage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seeds_per_hole` int unsigned DEFAULT NULL,
  `light_profile` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `soil_condition` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_detail` text COLLATE utf8mb4_unicode_ci,
  `pruning_detail` text COLLATE utf8mb4_unicode_ci,
  `perennial` tinyint(1) NOT NULL DEFAULT '0',
  `days_to_flower` int unsigned DEFAULT NULL,
  `days_to_harvest` int unsigned DEFAULT NULL,
  `harvest_window_days` int unsigned DEFAULT NULL,
  `expected_loss_rate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_yield_per_hectare` decimal(12,2) DEFAULT NULL,
  `quantity_planted` int unsigned DEFAULT NULL,
  `planted_at` date DEFAULT NULL,
  `estimated_harvest_date` date DEFAULT NULL,
  `area_ha` decimal(10,2) DEFAULT NULL COMMENT 'Luas lahan dalam hektar',
  `planting_format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_format_custom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plantings_planting_batch_number_unique` (`planting_batch_number`),
  UNIQUE KEY `plantings_planting_id_unique` (`planting_id`),
  KEY `plantings_plant_id_foreign` (`plant_id`),
  KEY `plantings_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `plantings_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plantings_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `plants`
DROP TABLE IF EXISTS `plants`;
CREATE TABLE `plants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_type_id` bigint unsigned DEFAULT NULL,
  `new_plant_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variety` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('perencanaan','ditanam','dipanen','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'perencanaan',
  `progress` tinyint unsigned NOT NULL DEFAULT '0',
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plants_plant_id_unique` (`plant_id`),
  KEY `plants_plant_type_id_foreign` (`plant_type_id`),
  KEY `plants_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `plants_plant_type_id_foreign` FOREIGN KEY (`plant_type_id`) REFERENCES `plant_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plants_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `sale_items`
DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `inventory_lot_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `sale_items_inventory_lot_id_foreign` (`inventory_lot_id`),
  CONSTRAINT `sale_items_inventory_lot_id_foreign` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `sales`
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_date` date NOT NULL,
  `buyer_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_nik` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_category` enum('petani_perorangan','kelompok_tani','instansi_pemerintah','swasta','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_category_custom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_province` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_district` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_village` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planned_location_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_planting_area` decimal(10,2) DEFAULT NULL,
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_receipt_number_unique` (`receipt_number`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `sales_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `seed_histories`
DROP TABLE IF EXISTS `seed_histories`;
CREATE TABLE `seed_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_seed_id` bigint unsigned NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi aksi',
  `old_data` json DEFAULT NULL COMMENT 'Data sebelum perubahan',
  `new_data` json DEFAULT NULL COMMENT 'Data setelah perubahan',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seed_histories_user_id_foreign` (`user_id`),
  KEY `seed_histories_inventory_type_seed_id_action_index` (`inventory_type_seed_id`,`action`),
  CONSTRAINT `seed_histories_inventory_type_seed_id_foreign` FOREIGN KEY (`inventory_type_seed_id`) REFERENCES `inventory_type_seeds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seed_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `task_series`
DROP TABLE IF EXISTS `task_series`;
CREATE TABLE `task_series` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` bigint unsigned NOT NULL,
  `series_tasks` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_series_template_id_foreign` (`template_id`),
  CONSTRAINT `task_series_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `task_templates`
DROP TABLE IF EXISTS `task_templates`;
CREATE TABLE `task_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tasks_list` json DEFAULT NULL,
  `association` enum('penanaman','sertifikasi','gudang','penjualan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `tasks`
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `task_report` text COLLATE utf8mb4_unicode_ci,
  `checklist` json DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `association` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('low','medium','high','highest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date NOT NULL,
  `due_time` time DEFAULT NULL,
  `repeats` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hours_spent` decimal(8,2) DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `series_id` bigint unsigned DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_tagged` tinyint(1) NOT NULL DEFAULT '0',
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `task_color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_edited_at` timestamp NULL DEFAULT NULL,
  `last_edited_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `collaborators` json DEFAULT NULL,
  `new_priority` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_template_id_foreign` (`template_id`),
  KEY `tasks_series_id_foreign` (`series_id`),
  KEY `tasks_planting_location_id_foreign` (`planting_location_id`),
  KEY `tasks_planting_id_foreign` (`planting_id`),
  KEY `tasks_last_edited_by_foreign` (`last_edited_by`),
  KEY `tasks_created_by_foreign` (`created_by`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_last_edited_by_foreign` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_series_id_foreign` FOREIGN KEY (`series_id`) REFERENCES `task_series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `treatments`
DROP TABLE IF EXISTS `treatments`;
CREATE TABLE `treatments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `treatment_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `treatment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_detail` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opt_institution` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `withholding_period_days` int DEFAULT NULL,
  `technician` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `batch_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `treatment_date` date NOT NULL,
  `retreat_date` date DEFAULT NULL,
  `treatment_location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_applied` decimal(10,2) DEFAULT NULL,
  `unit_measurement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `keywords` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `treatments_planting_location_id_foreign` (`planting_location_id`),
  KEY `treatments_planting_id_foreign` (`planting_id`),
  KEY `treatments_responsible_person_id_foreign` (`responsible_person_id`),
  KEY `treatments_edited_by_foreign` (`edited_by`),
  CONSTRAINT `treatments_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `treatments_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `treatments_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `treatments_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `user_planting_location_land_manager`
DROP TABLE IF EXISTS `user_planting_location_land_manager`;
CREATE TABLE `user_planting_location_land_manager` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_land_manager_user_unique` (`planting_location_id`,`user_id`),
  KEY `user_planting_location_land_manager_user_id_foreign` (`user_id`),
  CONSTRAINT `user_planting_location_land_manager_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_planting_location_land_manager_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `user_planting_location_land_worker`
DROP TABLE IF EXISTS `user_planting_location_land_worker`;
CREATE TABLE `user_planting_location_land_worker` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_land_worker_user_unique` (`planting_location_id`,`user_id`),
  KEY `user_planting_location_land_worker_user_id_foreign` (`user_id`),
  CONSTRAINT `user_planting_location_land_worker_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_planting_location_land_worker_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `contact_type` enum('pegawai_uptd_bbi_tpph','pegawai_gudang','petugas_sertifikasi','petani','penyuluh','penangkar','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_phone_is_whatsapp` tinyint(1) NOT NULL DEFAULT '1',
  `secondary_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `province` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','kepala_satuan_tugas','petugas_sertifikasi','petugas_gudang','petugas_bbi','penangkar') COLLATE utf8mb4_unicode_ci DEFAULT 'petugas_bbi',
  `location_placement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_user_id_unique` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `users`
LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`,`user_id`,`name`,`full_name`,`status`,`contact_type`,`organization`,`position`,`nip`,`primary_phone`,`primary_phone_is_whatsapp`,`secondary_phone`,`address`,`province`,`city`,`district`,`village`,`notes`,`email`,`photo_path`,`email_verified_at`,`password`,`remember_token`,`created_at`,`updated_at`,`role`,`location_placement`) VALUES
('1',NULL,'Admin SIBIT',NULL,'active',NULL,NULL,NULL,NULL,NULL,'1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin@sibit.com',NULL,'2026-02-05 05:17:17','$2y$12$tZh5xQHO4vnH6MHBT2oWWuk8PZ7ANk.GQFzQCKfA11RvAQ5vS7HBu',NULL,'2026-02-05 05:17:17','2026-02-05 05:17:17','admin',NULL);
UNLOCK TABLES;

-- Table structure for `warehouses`
DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_internal_id_unique` (`internal_id`),
  KEY `warehouses_responsible_person_id_foreign` (`responsible_person_id`),
  CONSTRAINT `warehouses_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
