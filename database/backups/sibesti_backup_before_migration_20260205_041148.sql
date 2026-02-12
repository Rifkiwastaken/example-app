-- Backup Database: sibit
-- Generated: 2026-02-05 04:11:48

SET FOREIGN_KEY_CHECKS=0;

-- Table: attachments
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attachment_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `attachment_date` date NOT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `new_created_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attachments_attachment_id_unq` (`attachment_id`),
  KEY `attachments_planting_location_id_foreign` (`planting_location_id`),
  KEY `attachments_created_by_foreign` (`created_by`),
  KEY `attachments_edited_by_foreign` (`edited_by`),
  CONSTRAINT `attachments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachments_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attachments_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bins
DROP TABLE IF EXISTS `bins`;
CREATE TABLE `bins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bin_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `new_warehouse_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_capacity` decimal(15,2) NOT NULL,
  `capacity_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bins_warehouse_id_internal_id_unique` (`warehouse_id`,`internal_id`),
  UNIQUE KEY `bins_bin_id_unq` (`bin_id`),
  CONSTRAINT `bins_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: bins
INSERT INTO `bins` (`id`, `bin_id`, `warehouse_id`, `new_warehouse_id`, `name`, `internal_id`, `max_capacity`, `capacity_unit`, `description`, `created_at`, `updated_at`) VALUES ('1', 'BIN-C6EE57DC', '1', 'WHS-330243E6', 'Rak Padi Anak Daro', 'GUD-SKR', '10000.00', 'kg', 'Rak khusus padi anak daro', '2026-01-18 19:12:48', '2026-01-18 19:12:48');

-- Table: certification_reports
DROP TABLE IF EXISTS `certification_reports`;
CREATE TABLE `certification_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_report_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_id` bigint unsigned NOT NULL,
  `new_certification_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  UNIQUE KEY `certification_report_certification_report_unq` (`certification_report_id`),
  KEY `certification_reports_certification_id_foreign` (`certification_id`),
  CONSTRAINT `certification_reports_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: certification_reports
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('1', 'CRP-76625703', '1', 'CRT-BA73435E', 'Laporan Pemeriksaan Pertanaman', '01', '2026-01-13', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14', '1000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-13 05:03:56', '2026-01-13 05:03:56', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('2', 'CRP-DA748FC6', '1', 'CRT-BA73435E', 'Laporan Sertifikasi Ulang', '02', '2026-01-13', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-31', '10000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-13 05:11:07', '2026-01-13 05:11:07', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('3', 'CRP-7ADD46A5', '3', 'CRT-8D98B9B2', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000003', '2026-01-13', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15', '1000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-13 17:45:42', '2026-01-13 17:45:42', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('4', 'CRP-9CD3B965', '5', 'CRT-FDDD225F', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000004', '2026-01-14', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-31', '123.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-14 17:01:32', '2026-01-14 17:01:32', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('5', 'CRP-9F1CDF03', '6', 'CRT-AB9F60A2', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000005', '2026-01-15', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-30', '15000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-15 05:18:06', '2026-01-15 05:18:06', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('6', 'CRP-D3097937', '7', 'CRT-652B97CF', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000006', '2026-01-18', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30', '150000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-18 15:43:30', '2026-01-18 15:43:30', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('7', 'CRP-0501BFC8', '8', 'CRT-55567EA8', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000007', '2026-01-18', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-29', '40000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-18 16:20:56', '2026-01-18 16:20:56', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('8', 'CRP-C78944B0', '9', 'CRT-AF7B874E', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000008', '2026-01-18', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-28', '25000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-18 16:23:46', '2026-01-18 16:23:46', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('9', 'CRP-CDD21AE7', '10', 'CRT-B84BD79B', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000009', '2026-01-18', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26', '10000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-18 16:28:07', '2026-01-18 16:28:07', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('10', 'CRP-14F2473A', '10', 'CRT-B84BD79B', 'Laporan Sertifikasi Ulang', 'BPSB-2026-000010', '2026-01-18', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-31', '10000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-18 19:11:30', '2026-01-18 19:11:30', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('11', 'CRP-7D20E5C2', '11', 'CRT-BEEF34E9', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000011', '2026-01-19', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30', '9000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-01-19 10:33:28', '2026-01-19 10:33:28', NULL, NULL);
INSERT INTO `certification_reports` (`id`, `certification_report_id`, `certification_id`, `new_certification_id`, `report_type`, `report_number_bpsb`, `report_date`, `growing_season`, `inspection_phase`, `inspector_name`, `reporter_name`, `seed_class_result`, `isolation_north`, `isolation_east`, `isolation_south`, `isolation_west`, `plant_characteristics_match`, `pest_disease_condition`, `weed_condition`, `population_per_sample`, `other_variety_mix_count`, `other_variety_mix_percentage`, `estimated_yield`, `expiry_date`, `certified_seed_quantity`, `certified_seed_unit`, `seed_unit`, `seed_unit_quantity`, `harvest_per_unit`, `harvest_per_unit_unit`, `estimated_sale_price_per_kg`, `conclusion`, `scan_file_path`, `created_at`, `updated_at`, `planting_batch_number`, `harvest_batch_number`) VALUES ('12', 'CRP-CC04A48D', '12', 'CRT-B3A0D436', 'Laporan Pemeriksaan Pertanaman', 'BPSB-2026-000012', '2026-02-03', NULL, 'Vegetatif', NULL, 'Admin SIBIT', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-31', '10000.00', 'kg', 'kg', NULL, NULL, NULL, '10000.00', 'LULUS', NULL, '2026-02-03 13:59:34', '2026-02-03 13:59:34', NULL, NULL);

-- Table: certifications
DROP TABLE IF EXISTS `certifications`;
CREATE TABLE `certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harvest_id` bigint unsigned NOT NULL,
  `new_harvest_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned DEFAULT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seed_class_requested` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certifications_certification_id_unq` (`certification_id`),
  KEY `certifications_harvest_id_foreign` (`harvest_id`),
  KEY `certifications_planting_location_id_foreign` (`planting_location_id`),
  KEY `certifications_plant_id_foreign` (`plant_id`),
  CONSTRAINT `certifications_harvest_id_foreign` FOREIGN KEY (`harvest_id`) REFERENCES `harvests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certifications_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certifications_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: certifications
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('1', 'CRT-BA73435E', '1', 'HRV-1F255F7C', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-13 05:03:56', '2026-01-13 05:03:56');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('2', 'CRT-D2522569', '2', 'HRV-D4F74E3A', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'dalam_proses', 'BP', '2026-01-13 16:46:58', '2026-01-13 16:46:58');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('3', 'CRT-8D98B9B2', '6', 'HRV-A2C3B5E4', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-13 17:45:42', '2026-01-13 17:45:42');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('4', 'CRT-AA3A7811', '3', 'HRV-82103A04', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'dalam_proses', 'BP', '2026-01-14 16:36:37', '2026-01-14 16:36:37');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('5', 'CRT-FDDD225F', '4', 'HRV-E0D00FA0', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-14 17:01:32', '2026-01-14 17:01:32');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('6', 'CRT-AB9F60A2', '5', 'HRV-BB05B6C6', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-15 05:18:06', '2026-01-15 05:18:06');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('7', 'CRT-652B97CF', '7', 'HRV-A0E524FD', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-18 15:43:30', '2026-01-18 15:43:30');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('8', 'CRT-55567EA8', '8', 'HRV-3F2BBC2F', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-18 16:20:56', '2026-01-18 16:20:56');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('9', 'CRT-AF7B874E', '9', 'HRV-09894B96', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-18 16:23:46', '2026-01-18 16:23:46');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('10', 'CRT-B84BD79B', '10', 'HRV-AB91732A', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-18 16:28:07', '2026-01-18 16:28:07');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('11', 'CRT-BEEF34E9', '11', 'HRV-C0009624', '1', 'LOC-C072EF9C', '1', 'PLT-BE0E8127', 'lulus', 'BP', '2026-01-19 10:33:28', '2026-01-19 10:33:28');
INSERT INTO `certifications` (`id`, `certification_id`, `harvest_id`, `new_harvest_id`, `planting_location_id`, `new_planting_location_id`, `plant_id`, `new_plant_id`, `certification_status`, `seed_class_requested`, `created_at`, `updated_at`) VALUES ('12', 'CRT-B3A0D436', '12', 'HRV-986AA1D5', '1', 'LOC-C072EF9C', '3', 'PLT-10D1FA62', 'lulus', 'BP', '2026-02-03 13:59:34', '2026-02-03 13:59:34');

-- Table: expenses
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_date` date NOT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `new_responsible_person_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `work_description` text COLLATE utf8mb4_unicode_ci,
  `worker_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `treatment_id` bigint unsigned DEFAULT NULL,
  `new_treatment_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nutrient_id` bigint unsigned DEFAULT NULL,
  `new_nutrient_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_id_unq` (`expense_id`),
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: expenses
INSERT INTO `expenses` (`id`, `expense_id`, `planting_location_id`, `new_planting_location_id`, `expense_name`, `work_name`, `amount`, `expense_type`, `expense_date`, `responsible_person_id`, `new_responsible_person_id`, `work_date`, `work_description`, `worker_name`, `planting_id`, `new_planting_id`, `description`, `treatment_id`, `new_treatment_id`, `nutrient_id`, `new_nutrient_id`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`) VALUES ('1', 'EXP-8C7D0960', '1', 'LOC-C072EF9C', 'Pupuk Cair', NULL, '10000.00', 'nutrisi', '2026-02-02', NULL, NULL, NULL, NULL, NULL, '5', 'PLN-17B28D05', NULL, NULL, NULL, '1', 'NTR-11E61269', '2026-02-02 00:45:48', '2026-02-02 00:45:48', NULL, NULL, NULL);

-- Table: failed_jobs
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

-- Table: harvests
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
  UNIQUE KEY `harvests_harvest_id_unq` (`harvest_id`),
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: harvests
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('1', 'HRV-1F255F7C', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-001', NULL, 'Lahan Sawah Lubuk Minturun', 'A', '1000.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 04:30:20', '2026-01-13 04:30:20');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('2', 'HRV-D4F74E3A', '1', 'PLT-BE0E8127', '2', 'PLN-4FB40340', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-002', NULL, 'pangan - Padi Inpari Anak Daro', 'A', '1000.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 04:41:08', '2026-01-13 04:41:08');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('3', 'HRV-82103A04', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-003', NULL, 'pangan - Padi Inpari Anak Daro', 'A', '100.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 04:50:05', '2026-01-13 04:50:05');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('4', 'HRV-E0D00FA0', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-003', NULL, 'Lahan Sawah Lubuk Minturun', 'A', '10.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 04:51:20', '2026-01-13 04:51:20');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('5', 'HRV-BB05B6C6', '1', 'PLT-BE0E8127', '2', 'PLN-4FB40340', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-005', NULL, 'pangan - Padi Inpari Anak Daro', 'A', '123.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 04:52:28', '2026-01-13 04:52:28');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('6', 'HRV-A2C3B5E4', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-13', 'PAN-2026-006', NULL, 'pangan - Padi Inpari Anak Daro', 'A', '145.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-13 05:01:30', '2026-01-13 05:01:30');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('7', 'HRV-A0E524FD', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-18', 'PAN-2026-007', NULL, 'Lahan Sawah Lubuk Minturun', 'A', '1000.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-18 15:41:15', '2026-01-18 15:41:15');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('8', 'HRV-3F2BBC2F', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-18', 'PAN-2026-008', NULL, 'Lahan Sawah Lubuk Minturun', NULL, '50000.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-18 16:20:28', '2026-01-18 16:20:28');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('9', 'HRV-09894B96', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-18', 'PAN-2026-009', NULL, 'Lahan Sawah Lubuk Minturun', NULL, '30000.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-18 16:23:17', '2026-01-18 16:23:17');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('10', 'HRV-AB91732A', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-18', 'PAN-2026-010', NULL, 'Lahan Sawah Lubuk Minturun', NULL, '1.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-18 16:27:41', '2026-01-18 16:27:41');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('11', 'HRV-C0009624', '1', 'PLT-BE0E8127', '1', 'PLN-9F792E7E', '1', 'LOC-C072EF9C', '2026-01-19', 'PAN-2026-011', NULL, 'pangan - Padi Inpari Anak Daro', 'A', '10000.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-01-19 10:31:55', '2026-01-19 10:31:55');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('12', 'HRV-986AA1D5', '3', 'PLT-10D1FA62', '5', 'PLN-17B28D05', '1', 'LOC-C072EF9C', '2026-02-02', 'PAN-2026-012', NULL, 'pangan - Padi Inpari 32', 'A', '10000.00', 'kg', 'kg', NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-02-02 00:52:19', '2026-02-02 00:52:19');
INSERT INTO `harvests` (`id`, `harvest_id`, `plant_id`, `new_plant_id`, `planting_id`, `new_planting_id`, `planting_location_id`, `new_planting_location_id`, `harvested_at`, `batch_no`, `note`, `source`, `quality`, `quantity`, `unit`, `harvest_unit`, `unit_quantity`, `quantity_per_unit`, `recorded_by`, `new_recorded_by`, `edited_at`, `edited_by`, `new_edited_by`, `loss_quantity`, `created_at`, `updated_at`) VALUES ('13', 'HRV-7E55BE1D', '3', 'PLT-10D1FA62', '5', 'PLN-17B28D05', '1', 'LOC-C072EF9C', '2026-02-03', 'PAN-2026-013', NULL, 'pangan - Padi Inpari 32', NULL, '1000.00', 'kg', NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, '2026-02-03 15:32:05', '2026-02-03 15:32:05');

-- Table: inventory_lots
DROP TABLE IF EXISTS `inventory_lots`;
CREATE TABLE `inventory_lots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_lot_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initial_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `current_stock` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `new_warehouse_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `new_bin_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_id` bigint unsigned DEFAULT NULL,
  `new_certification_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_lots_inventory_lot_id_unq` (`inventory_lot_id`),
  KEY `inventory_lots_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_lots_warehouse_id_foreign` (`warehouse_id`),
  KEY `inventory_lots_bin_id_foreign` (`bin_id`),
  KEY `inventory_lots_certification_id_foreign` (`certification_id`),
  CONSTRAINT `inventory_lots_bin_id_foreign` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_lots_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_lots_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_lots_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_lots
INSERT INTO `inventory_lots` (`id`, `inventory_lot_id`, `inventory_type_id`, `new_inventory_type_id`, `production_id`, `expiry_date`, `status`, `initial_stock`, `current_stock`, `stock_unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `certification_id`, `new_certification_id`, `created_at`, `updated_at`) VALUES ('1', 'LOT-EE6360AA', '1', 'INV-5A749C4E', 'BPSB-2026-000010', '2026-01-31', 'kadaluarsa', '10000.00', '8000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', NULL, NULL, '2026-01-18 19:26:27', '2026-02-03 13:58:37');
INSERT INTO `inventory_lots` (`id`, `inventory_lot_id`, `inventory_type_id`, `new_inventory_type_id`, `production_id`, `expiry_date`, `status`, `initial_stock`, `current_stock`, `stock_unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `certification_id`, `new_certification_id`, `created_at`, `updated_at`) VALUES ('2', 'LOT-A019C67D', '1', 'INV-5A749C4E', 'BPSB-2026-000009', '2026-01-26', 'kadaluarsa', '10000.00', '2000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', NULL, NULL, '2026-01-18 20:36:55', '2026-01-30 14:57:35');
INSERT INTO `inventory_lots` (`id`, `inventory_lot_id`, `inventory_type_id`, `new_inventory_type_id`, `production_id`, `expiry_date`, `status`, `initial_stock`, `current_stock`, `stock_unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `certification_id`, `new_certification_id`, `created_at`, `updated_at`) VALUES ('3', 'LOT-63AA3455', '1', 'INV-5A749C4E', 'BPSB-2026-000011', '2026-04-30', 'tersedia', '9000.00', '9000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', NULL, NULL, '2026-01-19 10:46:33', '2026-01-19 10:46:33');

-- Table: inventory_notes
DROP TABLE IF EXISTS `inventory_notes`;
CREATE TABLE `inventory_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_note_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_notes_inventory_note_id_unq` (`inventory_note_id`),
  KEY `inventory_notes_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_notes_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inventory_photos
DROP TABLE IF EXISTS `inventory_photos`;
CREATE TABLE `inventory_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_photo_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_photos_inventory_photo_id_unq` (`inventory_photo_id`),
  KEY `inventory_photos_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `inventory_photos_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_photos_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_photos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inventory_transactions
DROP TABLE IF EXISTS `inventory_transactions`;
CREATE TABLE `inventory_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_transaction_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_lot_id` bigint unsigned DEFAULT NULL,
  `new_inventory_lot_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `new_warehouse_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `new_bin_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_transactions_inventory_transaction_id_unique` (`inventory_transaction_id`),
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_transactions
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('1', 'TRX-C2EB6853', '1', 'INV-5A749C4E', '1', 'LOT-EE6360AA', 'stok_masuk', '10000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'Stok masuk ke bin', 'Stok ditambahkan ke bin Rak Padi Anak Daro dari data benih: pangan - Padi Inpari Anak Daro', '1', 'USR-1A85508A', '2026-01-18 19:26:27', '2026-01-18 19:26:27');
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('2', 'TRX-D9A914E0', '1', 'INV-5A749C4E', '2', 'LOT-A019C67D', 'stok_masuk', '10000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'Stok masuk ke bin', 'Stok ditambahkan ke bin Rak Padi Anak Daro dari data benih: pangan - Padi Inpari Anak Daro', '1', 'USR-1A85508A', '2026-01-18 20:36:55', '2026-01-18 20:36:55');
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('3', 'TRX-16C311C9', '1', 'INV-5A749C4E', '2', 'LOT-A019C67D', 'pengurangan', '-5000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'jlek', 'Stok dikurangi dari lot BPSB-2026-000009 di bin Rak Padi Anak Daro', '1', 'USR-1A85508A', '2026-01-18 20:37:13', '2026-01-18 20:37:13');
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('4', 'TRX-FE8E77F6', '1', 'INV-5A749C4E', '2', 'LOT-A019C67D', 'pengurangan', '-3000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'jlekk (Sinkronisasi dari pengurangan stok benih)', 'Stok dikurangi dari record data stok benih - Lot: BPSB-2026-000009', '1', 'USR-1A85508A', '2026-01-18 20:37:35', '2026-01-18 20:37:35');
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('5', 'TRX-6C5F9D2E', '1', 'INV-5A749C4E', '3', 'LOT-63AA3455', 'stok_masuk', '9000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'Stok masuk ke bin', 'Stok ditambahkan ke bin Rak Padi Anak Daro dari data benih: pangan - Padi Inpari Anak Daro', '1', 'USR-1A85508A', '2026-01-19 10:46:33', '2026-01-19 10:46:33');
INSERT INTO `inventory_transactions` (`id`, `inventory_transaction_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `transaction_type`, `quantity`, `unit`, `warehouse_id`, `new_warehouse_id`, `bin_id`, `new_bin_id`, `reason`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('6', 'TRX-7496C3BA', '1', 'INV-5A749C4E', '1', 'LOT-EE6360AA', 'distribusi', '2000.00', 'kg', '1', 'WHS-330243E6', '1', 'BIN-C6EE57DC', 'Penjualan', 'No. Struk: PJ-2026-001 - Pembeli: pak heru', '1', 'USR-1A85508A', '2026-01-30 20:47:58', '2026-01-30 20:47:58');

-- Table: inventory_type_certification_reports
DROP TABLE IF EXISTS `inventory_type_certification_reports`;
CREATE TABLE `inventory_type_certification_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_certification_report_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_report_id` bigint unsigned NOT NULL,
  `new_certification_report_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL COMMENT 'Jumlah benih yang ditambahkan ke stok bibit',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_type_cert_report_unique` (`inventory_type_id`,`certification_report_id`),
  KEY `inv_type_cert_reports_cert_fk` (`certification_report_id`),
  CONSTRAINT `inv_type_cert_reports_cert_fk` FOREIGN KEY (`certification_report_id`) REFERENCES `certification_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_cert_reports_inv_type_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_type_certification_reports
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('1', 'ICR-DAE3C9FD', '1', 'INV-5A749C4E', '1', 'CRP-76625703', '15000.00', '2026-01-13 05:06:18', '2026-01-15 05:55:20');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('2', 'ICR-3A366BE9', '1', 'INV-5A749C4E', '2', 'CRP-DA748FC6', '10000.00', '2026-01-14 16:12:16', '2026-01-14 16:12:16');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('3', 'ICR-43387CF9', '1', 'INV-5A749C4E', '3', 'CRP-7ADD46A5', '1000.00', '2026-01-14 16:34:20', '2026-01-14 16:34:20');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('4', 'ICR-9DBF7463', '1', 'INV-5A749C4E', '4', 'CRP-9CD3B965', '150000.00', '2026-01-14 17:02:36', '2026-01-18 15:43:44');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('5', 'ICR-9786F301', '1', 'INV-5A749C4E', '6', 'CRP-D3097937', '150000.00', '2026-01-18 15:51:37', '2026-01-18 15:51:37');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('6', 'ICR-8876BD14', '1', 'INV-5A749C4E', '5', 'CRP-9F1CDF03', '15000.00', '2026-01-18 16:05:43', '2026-01-18 16:05:43');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('7', 'ICR-18E2D285', '1', 'INV-5A749C4E', '7', 'CRP-0501BFC8', '40000.00', '2026-01-18 16:21:38', '2026-01-18 16:21:38');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('8', 'ICR-F1644908', '1', 'INV-5A749C4E', '8', 'CRP-C78944B0', '25000.00', '2026-01-18 16:27:00', '2026-01-18 16:27:00');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('9', 'ICR-E38E2399', '1', 'INV-5A749C4E', '9', 'CRP-CDD21AE7', '10000.00', '2026-01-18 19:10:48', '2026-01-18 19:10:48');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('10', 'ICR-0F6A78CA', '1', 'INV-5A749C4E', '10', 'CRP-14F2473A', '10000.00', '2026-01-18 19:11:40', '2026-01-18 19:11:40');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('11', 'ICR-896E045A', '1', 'INV-5A749C4E', '11', 'CRP-7D20E5C2', '9000.00', '2026-01-19 10:45:19', '2026-01-19 10:45:19');
INSERT INTO `inventory_type_certification_reports` (`id`, `inventory_type_certification_report_id`, `inventory_type_id`, `new_inventory_type_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `created_at`, `updated_at`) VALUES ('12', 'ICR-48568575', '3', 'INV-0C8D96CB', '12', 'CRP-CC04A48D', '10000.00', '2026-02-03 16:04:55', '2026-02-03 16:04:55');

-- Table: inventory_type_seeds
DROP TABLE IF EXISTS `inventory_type_seeds`;
CREATE TABLE `inventory_type_seeds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_seed_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_report_id` bigint unsigned DEFAULT NULL,
  `new_certification_report_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `new_filled_by_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor penyimpanan (dapat diedit oleh user)',
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Jenis laporan BPSB',
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_type_seeds_inventory_type_seed__unq` (`inventory_type_seed_id`),
  KEY `inv_type_seeds_plant_fk` (`plant_id`),
  KEY `inv_type_seeds_location_fk` (`planting_location_id`),
  KEY `inv_type_seeds_user_fk` (`filled_by_user_id`),
  KEY `inventory_type_seeds_inventory_type_id_plant_id_index` (`inventory_type_id`,`plant_id`),
  KEY `inventory_type_seeds_edited_by_foreign` (`edited_by`),
  KEY `inv_type_seeds_cert_report_fk` (`certification_report_id`),
  CONSTRAINT `inv_type_seeds_cert_report_fk` FOREIGN KEY (`certification_report_id`) REFERENCES `certification_reports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inv_type_seeds_inv_type_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_location_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_plant_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_type_seeds_user_fk` FOREIGN KEY (`filled_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_type_seeds_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_type_seeds
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('1', 'ITS-027108F3', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '1000.00', 'kg', '1000.00', '1.00', 'kg', '1000.00', 'kg', '10000.00', '2026-01-14', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-13 05:06:18', '2026-01-13 05:06:18');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('2', 'ITS-14D58F96', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '10000.00', 'kg', '10000.00', '1.00', 'kg', '10000.00', 'kg', '10000.00', '2026-05-31', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-13 05:11:17', '2026-01-13 05:11:17');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('3', 'ITS-D160C606', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '10000.00', 'kg', '10000.00', '1.00', 'kg', '10000.00', 'kg', '10000.00', '2026-05-31', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:44:59', '2026-01-13 17:44:59');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('4', 'ITS-139F597E', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '1000.00', 'kg', '1000.00', '1.00', 'kg', '1000.00', 'kg', '10000.00', '2026-01-15', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:47:09', '2026-01-13 17:47:09');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('5', 'ITS-7923A4A1', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '10000.00', 'kg', '10000.00', '1.00', 'kg', '10000.00', 'kg', '10000.00', '2026-05-31', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-14 16:12:16', '2026-01-14 16:12:16');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('6', 'ITS-5BF93918', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '1000.00', 'kg', '1000.00', '1.00', 'kg', '1000.00', 'kg', '10000.00', '2026-01-15', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-14 16:34:20', '2026-01-14 16:34:20');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('7', 'ITS-CB8F5EF7', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', '4', 'CRP-9CD3B965', '123.00', 'kg', '123.00', '1.00', 'kg', '123.00', 'kg', '10000.00', '2026-03-31', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-14 17:02:36', '2026-01-14 17:02:36');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('8', 'ITS-6EAA93EA', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-15 05:55:20', '2026-01-15 05:55:20');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('9', 'ITS-A3ABE05B', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 14:49:54', '2026-01-18 14:49:54');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('10', 'ITS-3E810156', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:15:58', '2026-01-18 15:15:58');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('11', 'ITS-F7A3DD5B', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:20:34', '2026-01-18 15:20:34');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('12', 'ITS-4F0BD044', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:25:22', '2026-01-18 15:25:22');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('13', 'ITS-BDE7C069', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:32:32', '2026-01-18 15:32:32');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('14', 'ITS-EB6B7842', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:40:00', '2026-01-18 15:40:00');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('15', 'ITS-F193F0AD', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '150000.00', 'kg', '150000.00', '1.00', 'kg', '150000.00', 'kg', '10000.00', '2026-01-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:43:44', '2026-01-18 15:43:44');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('16', 'ITS-8D87A607', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '150000.00', 'kg', '150000.00', '1.00', 'kg', '150000.00', 'kg', '10000.00', '2026-01-30', '1', 'USR-1A85508A', NULL, NULL, NULL, NULL, NULL, '2026-01-18 15:51:37', '2026-01-18 15:51:37');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('17', 'ITS-E93F7579', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '15000.00', 'kg', '15000.00', '1.00', 'kg', '15000.00', 'kg', '10000.00', '2026-06-30', '1', 'USR-1A85508A', 'BPSB-2026-000005', 'Laporan Pemeriksaan Pertanaman', NULL, NULL, NULL, '2026-01-18 16:05:43', '2026-01-18 16:05:43');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('18', 'ITS-8F728C50', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '40000.00', 'kg', '40000.00', '1.00', 'kg', '40000.00', 'kg', '10000.00', '2026-01-29', '1', 'USR-1A85508A', 'BPSB-2026-000007', 'Laporan Pemeriksaan Pertanaman', NULL, NULL, NULL, '2026-01-18 16:21:38', '2026-01-18 16:21:38');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('19', 'ITS-D35B41E9', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, '25000.00', 'kg', '25000.00', '1.00', 'kg', '25000.00', 'kg', '10000.00', '2026-01-28', '1', 'USR-1A85508A', 'BPSB-2026-000008', 'Laporan Pemeriksaan Pertanaman', NULL, NULL, NULL, '2026-01-18 16:27:00', '2026-01-18 16:27:00');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('20', 'ITS-3D18A6D8', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', '9', 'CRP-CDD21AE7', '2000.00', 'kg', '10000.00', '1.00', 'kg', '2000.00', 'kg', '10000.00', '2026-01-26', '1', 'USR-1A85508A', 'BPSB-2026-000009', 'Laporan Pemeriksaan Pertanaman', '2026-01-18 20:37:35', '1', 'USR-1A85508A', '2026-01-18 19:10:48', '2026-01-18 20:37:35');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('21', 'ITS-1D79F9E0', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', '10', 'CRP-14F2473A', '5000.00', 'kg', '10000.00', '1.00', 'kg', '5000.00', 'kg', '10000.00', '2026-01-31', '1', 'USR-1A85508A', 'BPSB-2026-000010', 'Laporan Sertifikasi Ulang', '2026-01-18 20:23:00', '1', 'USR-1A85508A', '2026-01-18 19:11:40', '2026-01-18 20:23:00');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('22', 'ITS-02EFF579', '1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', '11', 'CRP-7D20E5C2', '9000.00', 'kg', '9000.00', '1.00', 'kg', '9000.00', 'kg', '10000.00', '2026-04-30', '1', 'USR-1A85508A', 'BPSB-2026-000011', 'Laporan Pemeriksaan Pertanaman', NULL, NULL, NULL, '2026-01-19 10:45:19', '2026-01-19 10:45:19');
INSERT INTO `inventory_type_seeds` (`id`, `inventory_type_seed_id`, `inventory_type_id`, `new_inventory_type_id`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `certification_report_id`, `new_certification_report_id`, `quantity`, `seed_unit`, `seed_unit_quantity`, `seed_per_unit`, `seed_per_unit_unit`, `total_seed_quantity`, `total_seed_unit`, `estimated_sale_price_per_kg`, `expiry_date`, `filled_by_user_id`, `new_filled_by_user_id`, `storage_number`, `report_type`, `edited_at`, `edited_by`, `new_edited_by`, `created_at`, `updated_at`) VALUES ('23', 'ITS-F4AFCF8D', '3', 'INV-0C8D96CB', '3', 'PLT-10D1FA62', '1', 'LOC-C072EF9C', '12', 'CRP-CC04A48D', '10000.00', 'kg', '10000.00', '1.00', 'kg', '10000.00', 'kg', '15000.00', '2026-05-31', '1', 'USR-1A85508A', 'BPSB-2026-000012', 'Laporan Pemeriksaan Pertanaman', NULL, NULL, NULL, '2026-02-03 16:04:55', '2026-02-03 16:04:55');

-- Table: inventory_type_warehouses
DROP TABLE IF EXISTS `inventory_type_warehouses`;
CREATE TABLE `inventory_type_warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_warehous_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `new_warehouse_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bin_id` bigint unsigned DEFAULT NULL,
  `new_bin_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_only` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_type_wh_bin_unique` (`inventory_type_id`,`warehouse_id`,`bin_id`),
  UNIQUE KEY `inventory_type_warehouses_inventory_type_warehous_id_unique` (`inventory_type_warehous_id`),
  KEY `inventory_type_warehouses_warehouse_id_foreign` (`warehouse_id`),
  KEY `inventory_type_warehouses_bin_id_foreign` (`bin_id`),
  CONSTRAINT `inventory_type_warehouses_bin_id_foreign` FOREIGN KEY (`bin_id`) REFERENCES `bins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_type_warehouses_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_type_warehouses_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inventory_types
DROP TABLE IF EXISTS `inventory_types`;
CREATE TABLE `inventory_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned DEFAULT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `new_responsible_person_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_types_sku_unique` (`sku`),
  UNIQUE KEY `inventory_types_inventory_type_id_unq` (`inventory_type_id`),
  KEY `inventory_types_responsible_person_id_foreign` (`responsible_person_id`),
  KEY `inventory_types_plant_id_foreign` (`plant_id`),
  CONSTRAINT `inventory_types_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_types_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_types
INSERT INTO `inventory_types` (`id`, `inventory_type_id`, `plant_id`, `new_plant_id`, `category`, `name`, `sku`, `electronic_id`, `unit`, `estimated_value_per_unit`, `estimated_kg_per_unit`, `track_individual_lots`, `low_stock_threshold`, `low_stock_unit`, `low_stock_email`, `description`, `responsible_person_id`, `new_responsible_person_id`, `created_at`, `updated_at`) VALUES ('1', 'INV-5A749C4E', '1', 'PLT-BE0E8127', 'Padi Inpari', 'pangan - Padi Inpari Anak Daro - Anak Daro', '01', NULL, 'kg', '10000.00', NULL, '0', '100.00', 'kg', NULL, NULL, NULL, NULL, '2026-01-13 05:05:59', '2026-01-13 05:05:59');
INSERT INTO `inventory_types` (`id`, `inventory_type_id`, `plant_id`, `new_plant_id`, `category`, `name`, `sku`, `electronic_id`, `unit`, `estimated_value_per_unit`, `estimated_kg_per_unit`, `track_individual_lots`, `low_stock_threshold`, `low_stock_unit`, `low_stock_email`, `description`, `responsible_person_id`, `new_responsible_person_id`, `created_at`, `updated_at`) VALUES ('2', 'INV-27F293B4', '2', 'PLT-9836C356', 'Padi Inpari', 'pangan - Padi Inpari Cisokan - Cisokan', 'ASD-01', NULL, 'kg', '10000.00', NULL, '0', '100000.00', 'kg', NULL, NULL, NULL, NULL, '2026-01-19 10:34:35', '2026-01-19 10:34:35');
INSERT INTO `inventory_types` (`id`, `inventory_type_id`, `plant_id`, `new_plant_id`, `category`, `name`, `sku`, `electronic_id`, `unit`, `estimated_value_per_unit`, `estimated_kg_per_unit`, `track_individual_lots`, `low_stock_threshold`, `low_stock_unit`, `low_stock_email`, `description`, `responsible_person_id`, `new_responsible_person_id`, `created_at`, `updated_at`) VALUES ('3', 'INV-0C8D96CB', '3', 'PLT-10D1FA62', 'Padi', 'pangan - Padi Inpari 32 - Inpari 32', 'SKU-2026-0003', NULL, 'kg', '15000.00', NULL, '0', '150000.00', 'kg', NULL, NULL, NULL, NULL, '2026-02-03 15:58:09', '2026-02-03 15:58:51');

-- Table: landing_page_settings
DROP TABLE IF EXISTS `landing_page_settings`;
CREATE TABLE `landing_page_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `landing_page_setting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landing_page_settings_key_unique` (`key`),
  UNIQUE KEY `landing_page_setting_landing_page_setting_unq` (`landing_page_setting_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: landing_page_settings
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('1', 'LPS-50ACEA9B', 'hero_title', 'Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('2', 'LPS-421F6098', 'hero_subtitle', 'Pantau ketersediaan stok benih padi bersertifikat secara real-time dari seluruh unit UPTD BBI TPPH.', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('3', 'LPS-B93C9C26', 'hero_image', 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('4', 'LPS-B96781F1', 'office_address', 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura<br>Jl. Raya Padang - Bukittinggi KM 15<br>Lubuk Minturun, Padang, Sumatera Barat<br>Kode Pos: 25163', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('5', 'LPS-8EEA4147', 'office_phone', '(0751) 123456', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('6', 'LPS-9298C0D4', 'office_whatsapp', '+62 812-3456-7890', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('7', 'LPS-2A58D039', 'office_email', 'info@bbitpph.sumbar.go.id', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('8', 'LPS-76F77EA2', 'facebook_url', '#', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('9', 'LPS-43DBDBA3', 'instagram_url', '#', '2026-01-12 09:42:36', '2026-01-12 09:42:36');
INSERT INTO `landing_page_settings` (`id`, `landing_page_setting_id`, `key`, `value`, `created_at`, `updated_at`) VALUES ('10', 'LPS-B16B705F', 'youtube_url', '#', '2026-01-12 09:42:36', '2026-01-12 09:42:36');

-- Table: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: migrations
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '2014_10_12_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '2014_10_12_100000_create_password_reset_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '2019_08_19_000000_create_failed_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2019_12_14_000001_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2024_01_01_000000_create_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2024_01_02_000000_create_locations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2024_01_02_000001_add_role_and_location_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2024_01_03_000001_create_task_templates_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2024_01_03_000002_create_task_series_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2024_01_03_000003_safe_update_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2025_01_01_000100_create_plant_types_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2025_01_01_000110_create_plants_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2025_01_01_000120_create_planting_locations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2025_01_01_000130_create_plantings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2025_01_01_000140_create_harvests_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2025_01_01_000150_add_fk_to_plants_after_locations_exist', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2025_01_01_000160_create_plant_notes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2025_01_01_000170_create_plant_photos_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2025_01_01_000180_create_treatments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2025_01_01_000190_create_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2025_01_01_000200_create_user_planting_location_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2025_01_02_000100_create_certifications_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2025_01_02_000110_create_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2025_01_03_000100_create_warehouses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2025_01_03_000110_create_bins_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2025_01_03_000120_create_inventory_types_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2025_01_03_000130_create_inventory_lots_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('28', '2025_01_03_000140_create_inventory_transactions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('29', '2025_01_03_000150_create_inventory_type_warehouses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('30', '2025_01_03_000160_create_inventory_notes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('31', '2025_01_03_000170_create_inventory_photos_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('32', '2025_01_04_000100_create_sales_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('33', '2025_01_04_000110_create_sale_items_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('34', '2025_01_04_000200_update_planting_format_enum', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('35', '2025_01_05_000100_create_planting_location_notes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('36', '2025_01_05_000110_create_planting_location_photos_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('37', '2025_01_05_000120_add_planting_location_to_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('38', '2025_01_05_000130_add_fields_to_treatments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('39', '2025_01_05_000140_add_unit_to_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('40', '2025_01_05_000150_add_fields_to_certifications_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('41', '2025_01_05_000160_add_expiry_date_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('42', '2025_01_06_000000_create_planting_losses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('43', '2025_01_20_000000_add_penangkar_role_and_user_details_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('44', '2025_01_21_000000_update_nutrients_table_remove_nutrient_fields_add_new_fields', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('45', '2025_01_22_000000_add_new_fields_to_treatments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('46', '2025_01_22_000001_add_responsible_person_to_expenses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('47', '2025_01_30_000000_add_last_edited_to_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('48', '2025_01_31_000000_create_expenses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('49', '2025_01_31_100000_add_assigned_to_and_read_by_to_planting_location_notes', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('50', '2025_01_31_200000_add_certified_seed_fields_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('51', '2025_02_01_000000_create_inventory_type_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('52', '2025_02_01_100000_create_inventory_type_seeds_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('53', '2025_02_02_000000_create_attachments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('54', '2025_02_03_000000_add_institution_source_to_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('55', '2025_02_03_100000_add_nutrient_name_to_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('56', '2025_02_03_200000_add_edited_fields_to_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('57', '2025_02_04_000000_add_fields_to_expenses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('58', '2025_02_04_100000_add_responsible_person_and_attachment_to_nutrients_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('59', '2025_02_04_200000_add_edited_fields_to_treatments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('60', '2025_02_05_000000_add_responsible_person_to_warehouses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('61', '2025_11_13_010000_update_planting_locations_with_management_fields', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('62', '2025_11_29_045256_add_created_by_to_tasks_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('63', '2025_12_06_000000_add_harvest_fields_to_harvests_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('64', '2025_12_06_100000_add_edited_fields_to_harvests_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('65', '2025_12_06_200000_add_is_completed_to_plantings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('66', '2025_12_07_204116_add_seed_unit_fields_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('67', '2025_12_07_212600_add_responsible_person_to_inventory_types_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('68', '2025_12_07_213000_add_unit_fields_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('69', '2025_12_07_220000_add_fields_to_inventory_type_seeds_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('70', '2025_12_07_221000_create_seed_histories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('71', '2025_12_08_000000_add_estimated_harvest_date_to_plantings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('72', '2025_12_09_000000_add_planting_format_to_plantings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('73', '2025_12_09_100000_add_location_type_custom_to_planting_locations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('74', '2025_12_09_200000_update_location_type_enum_to_include_sawah', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('75', '2025_12_11_000300_add_plant_id_to_inventory_types_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('76', '2025_12_11_124653_add_planting_location_id_to_sales_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('77', '2025_12_11_190123_add_area_ha_to_plantings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('78', '2025_12_12_000726_add_report_type_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('79', '2025_12_12_000757_make_report_number_bpsb_unique_in_certification_reports', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('80', '2025_12_16_140042_drop_contacts_and_related_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('81', '2025_12_16_140913_drop_planning_tables_production_targets_budgets', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('82', '2025_12_16_155019_drop_locations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('83', '2026_01_07_135555_add_buyer_and_distribution_fields_to_sales_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('84', '2026_01_07_142115_limit_all_varchar_fields_to_50_characters', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('85', '2026_01_11_210643_create_landing_page_settings_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('86', '2026_01_11_215355_add_password_plain_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('87', '2026_01_11_225846_add_renew_from_report_id_to_certification_reports_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('88', '2026_01_12_054533_add_certification_report_id_to_inventory_type_seeds_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('89', '2026_01_12_084541_add_stock_number_to_inventory_type_seeds_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('90', '2026_01_13_045054_add_planting_batch_number_to_plantings_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('91', '2026_01_14_164037_add_certification_report_id_to_inventory_type_seeds_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('92', '2026_01_18_155836_add_storage_number_and_report_type_to_inventory_type_seeds_table', '4');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('93', '2026_01_28_204318_add_variety_to_plant_types_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('94', '2026_02_02_004607_add_missing_columns_to_expenses_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('95', '2026_02_03_134748_add_batch_numbers_to_certification_reports_table', '7');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('100', '2026_02_10_001_phase1_add_custom_id_level_0', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('101', '2026_02_10_002_phase1_add_custom_id_level_1', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('102', '2026_02_10_003_phase1_add_custom_id_level_2', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('103', '2026_02_10_004_phase1_add_custom_id_level_3', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('104', '2026_02_10_005_phase1_add_custom_id_level_4', '8');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('105', '2026_02_10_101_phase2_migrate_data_level_0', '9');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('106', '2026_02_10_102_phase2_migrate_data_level_1', '10');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('107', '2026_02_10_103_phase2_migrate_data_level_2', '11');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('108', '2026_02_10_104_phase2_migrate_data_level_3', '12');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('109', '2026_02_10_105_phase2_migrate_data_level_4', '13');

-- Table: nutrients
DROP TABLE IF EXISTS `nutrients`;
CREATE TABLE `nutrients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nutrient_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nutrient_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_applied` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_applied` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `technician` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `new_responsible_person_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edited_by` bigint unsigned DEFAULT NULL,
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nutrients_nutrient_id_unq` (`nutrient_id`),
  KEY `nutrients_planting_location_id_foreign` (`planting_location_id`),
  KEY `nutrients_planting_id_foreign` (`planting_id`),
  KEY `nutrients_edited_by_foreign` (`edited_by`),
  KEY `nutrients_responsible_person_id_foreign` (`responsible_person_id`),
  CONSTRAINT `nutrients_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nutrients_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nutrients_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nutrients_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: nutrients
INSERT INTO `nutrients` (`id`, `nutrient_id`, `nutrient_name`, `planting_location_id`, `new_planting_location_id`, `product_applied`, `amount_applied`, `unit`, `application_method`, `total_cost`, `technician`, `institution_source`, `responsible_person_id`, `new_responsible_person_id`, `attachment`, `application_date`, `description`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`, `planting_id`, `new_planting_id`) VALUES ('1', 'NTR-11E61269', 'Pupuk', '1', 'LOC-C072EF9C', 'Pupuk Cair', '2.00', 'kg', 'Siar', '10000.00', NULL, NULL, '1', 'USR-1A85508A', NULL, '2026-02-02', NULL, '2026-02-02 00:45:48', '2026-02-02 00:45:48', NULL, NULL, NULL, '5', 'PLN-17B28D05');

-- Table: password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: personal_access_tokens
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

-- Table: plant_notes
DROP TABLE IF EXISTS `plant_notes`;
CREATE TABLE `plant_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_note_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `keywords` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plant_notes_plant_note_id_unq` (`plant_note_id`),
  KEY `plant_notes_plant_id_foreign` (`plant_id`),
  CONSTRAINT `plant_notes_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: plant_photos
DROP TABLE IF EXISTS `plant_photos`;
CREATE TABLE `plant_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plant_photo_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plant_id` bigint unsigned NOT NULL,
  `new_plant_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint NOT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `taken_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plant_photos_plant_photo_id_unq` (`plant_photo_id`),
  KEY `plant_photos_plant_id_foreign` (`plant_id`),
  CONSTRAINT `plant_photos_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: plant_types
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
  UNIQUE KEY `plant_types_plant_type_id_unq` (`plant_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: plant_types
INSERT INTO `plant_types` (`id`, `plant_type_id`, `name`, `category`, `variety`, `created_at`, `updated_at`) VALUES ('1', 'PTY-3C6DCBEE', 'Padi Inpari', 'pangan', NULL, '2026-01-13 04:09:38', '2026-01-13 04:09:38');
INSERT INTO `plant_types` (`id`, `plant_type_id`, `name`, `category`, `variety`, `created_at`, `updated_at`) VALUES ('2', 'PTY-9397C6D3', 'Padi Bujang Marantau', 'pangan', NULL, '2026-01-19 10:03:18', '2026-01-19 10:03:18');
INSERT INTO `plant_types` (`id`, `plant_type_id`, `name`, `category`, `variety`, `created_at`, `updated_at`) VALUES ('3', 'PTY-0C74FBC5', 'Padi', 'pangan', 'Anak Daro, Inpari 32', '2026-01-28 20:58:46', '2026-01-28 20:58:46');
INSERT INTO `plant_types` (`id`, `plant_type_id`, `name`, `category`, `variety`, `created_at`, `updated_at`) VALUES ('4', 'PTY-284CBE8F', 'Padi', 'pangan', 'Inpari 32', '2026-02-01 23:42:57', '2026-02-01 23:42:57');

-- Table: planting_location_notes
DROP TABLE IF EXISTS `planting_location_notes`;
CREATE TABLE `planting_location_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_note_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `keywords` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` json DEFAULT NULL,
  `read_by` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_no_planting_location_no_unq` (`planting_location_note_id`),
  KEY `planting_location_notes_planting_location_id_foreign` (`planting_location_id`),
  KEY `planting_location_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `planting_location_notes_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planting_location_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: planting_location_notes
INSERT INTO `planting_location_notes` (`id`, `planting_location_note_id`, `planting_location_id`, `new_planting_location_id`, `title`, `description`, `note_date`, `keywords`, `attachment_path`, `user_id`, `new_user_id`, `assigned_to`, `read_by`, `created_at`, `updated_at`) VALUES ('1', 'LCN-0BF6855A', '1', 'LOC-C072EF9C', 'baca', 'baca', '2026-02-02', NULL, NULL, '1', 'USR-1A85508A', '[1]', NULL, '2026-02-02 00:47:05', '2026-02-02 00:47:05');

-- Table: planting_location_photos
DROP TABLE IF EXISTS `planting_location_photos`;
CREATE TABLE `planting_location_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_photo_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `taken_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_ph_planting_location_ph_unq` (`planting_location_photo_id`),
  KEY `planting_location_photos_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `planting_location_photos_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: planting_locations
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
  UNIQUE KEY `planting_locations_planting_location_id_unq` (`planting_location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: planting_locations
INSERT INTO `planting_locations` (`id`, `planting_location_id`, `name`, `location_summary`, `administrative_address`, `google_maps_link`, `primary_photo_path`, `location_type`, `location_type_custom`, `planting_format`, `planting_format_custom`, `num_beds`, `bed_length_m`, `bed_width_m`, `map_size`, `light_condition`, `description`, `land_status`, `ownership_status`, `water_source`, `soil_type`, `elevation_masl`, `created_at`, `updated_at`) VALUES ('1', 'LOC-C072EF9C', 'Lahan Sawah Lubuk Minturun', 'Lubuk Minturun, Padang', NULL, NULL, NULL, 'sawah', NULL, 'ditanam_dalam_petak', NULL, '5', '100.00', '3.00', '12', 'sinar_matahari_penuh', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 04:24:55', '2026-01-13 04:24:55');

-- Table: planting_losses
DROP TABLE IF EXISTS `planting_losses`;
CREATE TABLE `planting_losses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planting_loss_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned NOT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loss_date` date NOT NULL,
  `loss_amount` decimal(12,2) NOT NULL,
  `loss_reason` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_losses_planting_loss_id_unq` (`planting_loss_id`),
  KEY `planting_losses_planting_id_foreign` (`planting_id`),
  CONSTRAINT `planting_losses_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: planting_losses
INSERT INTO `planting_losses` (`id`, `planting_loss_id`, `planting_id`, `new_planting_id`, `loss_date`, `loss_amount`, `loss_reason`, `description`, `created_at`, `updated_at`) VALUES ('1', 'PLS-7330F95C', '1', 'PLN-9F792E7E', '2026-01-13', '10.00', NULL, NULL, '2026-01-13 04:33:20', '2026-01-13 04:33:20');
INSERT INTO `planting_losses` (`id`, `planting_loss_id`, `planting_id`, `new_planting_id`, `loss_date`, `loss_amount`, `loss_reason`, `description`, `created_at`, `updated_at`) VALUES ('2', 'PLS-45DE150E', '1', 'PLN-9F792E7E', '2026-01-13', '10.00', 'penyakit', NULL, '2026-01-13 04:37:22', '2026-01-13 04:37:22');
INSERT INTO `planting_losses` (`id`, `planting_loss_id`, `planting_id`, `new_planting_id`, `loss_date`, `loss_amount`, `loss_reason`, `description`, `created_at`, `updated_at`) VALUES ('3', 'PLS-2FE237F9', '1', 'PLN-9F792E7E', '2026-01-13', '10.00', 'hama', NULL, '2026-01-13 04:37:48', '2026-01-13 04:37:48');

-- Table: plantings
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
  UNIQUE KEY `plantings_planting_id_unq` (`planting_id`),
  KEY `plantings_plant_id_foreign` (`plant_id`),
  KEY `plantings_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `plantings_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plantings_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: plantings
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('1', 'PLN-9F792E7E', NULL, '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 'kilogram', NULL, '1000', '2026-01-13', NULL, NULL, NULL, NULL, '0', '2026-01-13 04:26:32', '2026-01-13 04:26:32');
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('2', 'PLN-4FB40340', NULL, '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 'kilogram', NULL, '10000', '2026-01-13', '2026-04-30', '12.00', NULL, NULL, '0', '2026-01-13 04:40:44', '2026-01-13 04:40:44');
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('3', 'PLN-6072E617', NULL, '1', 'PLT-BE0E8127', '1', 'LOC-C072EF9C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 'kilogram', NULL, '1000', '2026-01-13', '2026-01-30', '1000.00', NULL, NULL, '0', '2026-01-13 04:49:54', '2026-01-13 04:49:54');
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('4', 'PLN-28287CF7', 'TANAM-2026-004', '2', 'PLT-9836C356', '1', 'LOC-C072EF9C', 'Blok 1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 'kilogram', NULL, '1000', '2026-01-19', '2026-07-19', '1.00', NULL, NULL, '0', '2026-01-19 10:07:17', '2026-01-19 10:07:17');
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('5', 'PLN-17B28D05', 'TANAM-2026-005', '3', 'PLT-10D1FA62', '1', 'LOC-C072EF9C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, 'kilogram', NULL, '30000', '2026-02-01', NULL, '1.00', NULL, NULL, '0', '2026-02-01 23:43:55', '2026-02-01 23:43:55');
INSERT INTO `plantings` (`id`, `planting_id`, `planting_batch_number`, `plant_id`, `new_plant_id`, `planting_location_id`, `new_planting_location_id`, `bed_label`, `days_to_emerge`, `spacing_between_plants`, `spacing_between_rows`, `sowing_depth`, `avg_height`, `start_method`, `germination_stage`, `seeds_per_hole`, `light_profile`, `soil_condition`, `planting_detail`, `pruning_detail`, `perennial`, `days_to_flower`, `days_to_harvest`, `harvest_window_days`, `expected_loss_rate`, `harvest_unit`, `expected_yield_per_hectare`, `quantity_planted`, `planted_at`, `estimated_harvest_date`, `area_ha`, `planting_format`, `planting_format_custom`, `is_completed`, `created_at`, `updated_at`) VALUES ('6', 'PLN-EF83A784', 'TANAM-2026-006', '3', 'PLT-10D1FA62', '1', 'LOC-C072EF9C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, '30000', '2026-02-03', NULL, '1.00', NULL, NULL, '0', '2026-02-03 15:32:05', '2026-02-03 15:32:05');

-- Table: plants
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
  UNIQUE KEY `plants_plant_id_unq` (`plant_id`),
  KEY `plants_plant_type_id_foreign` (`plant_type_id`),
  KEY `plants_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `plants_plant_type_id_foreign` FOREIGN KEY (`plant_type_id`) REFERENCES `plant_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plants_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: plants
INSERT INTO `plants` (`id`, `plant_id`, `name`, `plant_type_id`, `new_plant_type_id`, `variety`, `status`, `progress`, `planting_location_id`, `new_planting_location_id`, `created_at`, `updated_at`) VALUES ('1', 'PLT-BE0E8127', 'pangan - Padi Inpari Anak Daro', '1', 'PTY-3C6DCBEE', 'Anak Daro', 'perencanaan', '0', NULL, NULL, '2026-01-13 04:18:00', '2026-01-13 04:18:00');
INSERT INTO `plants` (`id`, `plant_id`, `name`, `plant_type_id`, `new_plant_type_id`, `variety`, `status`, `progress`, `planting_location_id`, `new_planting_location_id`, `created_at`, `updated_at`) VALUES ('2', 'PLT-9836C356', 'pangan - Padi Inpari Cisokan', '1', 'PTY-3C6DCBEE', 'Cisokan', 'perencanaan', '0', NULL, NULL, '2026-01-19 10:04:26', '2026-01-19 10:04:26');
INSERT INTO `plants` (`id`, `plant_id`, `name`, `plant_type_id`, `new_plant_type_id`, `variety`, `status`, `progress`, `planting_location_id`, `new_planting_location_id`, `created_at`, `updated_at`) VALUES ('3', 'PLT-10D1FA62', 'pangan - Padi Inpari 32', '4', 'PTY-284CBE8F', 'Inpari 32', 'perencanaan', '0', NULL, NULL, '2026-02-01 23:43:13', '2026-02-01 23:43:13');

-- Table: sale_items
DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_item_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_id` bigint unsigned NOT NULL,
  `new_sale_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_id` bigint unsigned NOT NULL,
  `new_inventory_type_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_lot_id` bigint unsigned DEFAULT NULL,
  `new_inventory_lot_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_items_sale_item_id_unq` (`sale_item_id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_inventory_type_id_foreign` (`inventory_type_id`),
  KEY `sale_items_inventory_lot_id_foreign` (`inventory_lot_id`),
  CONSTRAINT `sale_items_inventory_lot_id_foreign` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_inventory_type_id_foreign` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: sale_items
INSERT INTO `sale_items` (`id`, `sale_item_id`, `sale_id`, `new_sale_id`, `inventory_type_id`, `new_inventory_type_id`, `inventory_lot_id`, `new_inventory_lot_id`, `quantity`, `unit`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES ('1', 'SIT-B70486CF', '1', 'SAL-5B96B89C', '1', 'INV-5A749C4E', '1', 'LOT-EE6360AA', '2000.00', 'kg', '10000.00', '20000000.00', '2026-01-30 20:47:58', '2026-01-30 20:47:58');

-- Table: sales
DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_receipt_number_unique` (`receipt_number`),
  UNIQUE KEY `sales_sale_id_unq` (`sale_id`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_planting_location_id_foreign` (`planting_location_id`),
  CONSTRAINT `sales_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: sales
INSERT INTO `sales` (`id`, `sale_id`, `receipt_number`, `sale_date`, `buyer_name`, `buyer_contact`, `buyer_nik`, `buyer_category`, `buyer_category_custom`, `destination_province`, `destination_city`, `destination_district`, `destination_village`, `planned_location_name`, `estimated_planting_area`, `planting_location_id`, `new_planting_location_id`, `total_amount`, `payment_method`, `payment_status`, `notes`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('1', 'SAL-5B96B89C', 'PJ-2026-001', '2026-01-30', 'pak heru', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '20000000.00', 'cash', 'lunas', NULL, '1', 'USR-1A85508A', '2026-01-30 20:47:58', '2026-01-30 20:47:58');

-- Table: seed_histories
DROP TABLE IF EXISTS `seed_histories`;
CREATE TABLE `seed_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seed_history_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_type_seed_id` bigint unsigned NOT NULL,
  `new_inventory_type_seed_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Deskripsi aksi',
  `old_data` json DEFAULT NULL COMMENT 'Data sebelum perubahan',
  `new_data` json DEFAULT NULL COMMENT 'Data setelah perubahan',
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seed_histories_seed_history_id_unq` (`seed_history_id`),
  KEY `seed_histories_user_id_foreign` (`user_id`),
  KEY `seed_histories_inventory_type_seed_id_action_index` (`inventory_type_seed_id`,`action`),
  CONSTRAINT `seed_histories_inventory_type_seed_id_foreign` FOREIGN KEY (`inventory_type_seed_id`) REFERENCES `inventory_type_seeds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seed_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: seed_histories
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('1', 'SDH-A3E96A91', '1', 'ITS-027108F3', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 1, \"plant_id\": \"1\", \"quantity\": \"1000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-13T05:06:18.000000Z\", \"updated_at\": \"2026-01-13T05:06:18.000000Z\", \"expiry_date\": \"2026-01-14T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"1000.00\", \"total_seed_quantity\": \"1000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-13 05:06:18', '2026-01-13 05:06:18');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('2', 'SDH-82B1DC55', '2', 'ITS-14D58F96', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 2, \"plant_id\": \"1\", \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-13T05:11:17.000000Z\", \"updated_at\": \"2026-01-13T05:11:17.000000Z\", \"expiry_date\": \"2026-05-31T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-13 05:11:17', '2026-01-13 05:11:17');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('3', 'SDH-9CEA6074', '3', 'ITS-D160C606', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 3, \"plant_id\": \"1\", \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-13T17:44:59.000000Z\", \"updated_at\": \"2026-01-13T17:44:59.000000Z\", \"expiry_date\": \"2026-05-31T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-13 17:44:59', '2026-01-13 17:44:59');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('4', 'SDH-63E44174', '4', 'ITS-139F597E', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 4, \"plant_id\": \"1\", \"quantity\": \"1000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-13T17:47:09.000000Z\", \"updated_at\": \"2026-01-13T17:47:09.000000Z\", \"expiry_date\": \"2026-01-15T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"1000.00\", \"total_seed_quantity\": \"1000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-13 17:47:09', '2026-01-13 17:47:09');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('5', 'SDH-8F91B07F', '5', 'ITS-7923A4A1', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 5, \"plant_id\": \"1\", \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-14T16:12:16.000000Z\", \"updated_at\": \"2026-01-14T16:12:16.000000Z\", \"expiry_date\": \"2026-05-31T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-14 16:12:16', '2026-01-14 16:12:16');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('6', 'SDH-CA26FC4D', '6', 'ITS-5BF93918', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 6, \"plant_id\": \"1\", \"quantity\": \"1000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-14T16:34:20.000000Z\", \"updated_at\": \"2026-01-14T16:34:20.000000Z\", \"expiry_date\": \"2026-01-15T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"1000.00\", \"total_seed_quantity\": \"1000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-14 16:34:20', '2026-01-14 16:34:20');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('7', 'SDH-E39419EF', '7', 'ITS-CB8F5EF7', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 7, \"plant_id\": \"1\", \"quantity\": \"123.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-14T17:02:36.000000Z\", \"updated_at\": \"2026-01-14T17:02:36.000000Z\", \"expiry_date\": \"2026-03-31T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"123.00\", \"total_seed_quantity\": \"123.00\", \"planting_location_id\": \"1\", \"certification_report_id\": \"4\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-14 17:02:36', '2026-01-14 17:02:36');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('8', 'SDH-BAFEABB1', '8', 'ITS-6EAA93EA', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 8, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-15T05:55:20.000000Z\", \"updated_at\": \"2026-01-15T05:55:20.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-15 05:55:20', '2026-01-15 05:55:20');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('9', 'SDH-C4EFA4C3', '9', 'ITS-A3ABE05B', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 9, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T14:49:54.000000Z\", \"updated_at\": \"2026-01-18T14:49:54.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 14:49:55', '2026-01-18 14:49:55');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('10', 'SDH-9A3E4976', '10', 'ITS-3E810156', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 10, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:15:58.000000Z\", \"updated_at\": \"2026-01-18T15:15:58.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:15:58', '2026-01-18 15:15:58');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('11', 'SDH-A9D98798', '11', 'ITS-F7A3DD5B', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 11, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:20:34.000000Z\", \"updated_at\": \"2026-01-18T15:20:34.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:20:34', '2026-01-18 15:20:34');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('12', 'SDH-5CE86B87', '12', 'ITS-4F0BD044', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 12, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:25:22.000000Z\", \"updated_at\": \"2026-01-18T15:25:22.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:25:22', '2026-01-18 15:25:22');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('13', 'SDH-ED12ADE8', '13', 'ITS-BDE7C069', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 13, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:32:32.000000Z\", \"updated_at\": \"2026-01-18T15:32:32.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:32:32', '2026-01-18 15:32:32');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('14', 'SDH-F826953B', '14', 'ITS-EB6B7842', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 14, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:40:00.000000Z\", \"updated_at\": \"2026-01-18T15:40:00.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:40:00', '2026-01-18 15:40:00');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('15', 'SDH-7EE0B598', '15', 'ITS-F193F0AD', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 15, \"plant_id\": \"1\", \"quantity\": \"150000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:43:44.000000Z\", \"updated_at\": \"2026-01-18T15:43:44.000000Z\", \"expiry_date\": \"2026-01-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"150000.00\", \"total_seed_quantity\": \"150000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:43:44', '2026-01-18 15:43:44');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('16', 'SDH-38D70EF8', '16', 'ITS-8D87A607', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 16, \"plant_id\": \"1\", \"quantity\": \"150000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T15:51:37.000000Z\", \"updated_at\": \"2026-01-18T15:51:37.000000Z\", \"expiry_date\": \"2026-01-30T00:00:00.000000Z\", \"seed_per_unit\": \"1.00\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"150000.00\", \"total_seed_quantity\": \"150000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 15:51:37', '2026-01-18 15:51:37');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('17', 'SDH-E2A28148', '17', 'ITS-E93F7579', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 17, \"plant_id\": \"1\", \"quantity\": \"15000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T16:05:43.000000Z\", \"updated_at\": \"2026-01-18T16:05:43.000000Z\", \"expiry_date\": \"2026-06-30T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000005\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"15000.00\", \"total_seed_quantity\": \"15000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 16:05:43', '2026-01-18 16:05:43');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('18', 'SDH-BB863F2A', '18', 'ITS-8F728C50', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 18, \"plant_id\": \"1\", \"quantity\": \"40000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T16:21:38.000000Z\", \"updated_at\": \"2026-01-18T16:21:38.000000Z\", \"expiry_date\": \"2026-01-29T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000007\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"40000.00\", \"total_seed_quantity\": \"40000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 16:21:38', '2026-01-18 16:21:38');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('19', 'SDH-AB2B13AD', '19', 'ITS-D35B41E9', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 19, \"plant_id\": \"1\", \"quantity\": \"25000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T16:27:00.000000Z\", \"updated_at\": \"2026-01-18T16:27:00.000000Z\", \"expiry_date\": \"2026-01-28T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000008\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"25000.00\", \"total_seed_quantity\": \"25000.00\", \"planting_location_id\": \"1\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 16:27:00', '2026-01-18 16:27:00');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('20', 'SDH-7146DD7E', '20', 'ITS-3D18A6D8', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 20, \"plant_id\": 1, \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:10:48.000000Z\", \"updated_at\": \"2026-01-18T19:10:48.000000Z\", \"expiry_date\": \"2026-01-26T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000009\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": 1, \"certification_report_id\": \"9\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 19:10:48', '2026-01-18 19:10:48');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('21', 'SDH-2FA05DE8', '21', 'ITS-1D79F9E0', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 21, \"plant_id\": 1, \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:11:40.000000Z\", \"updated_at\": \"2026-01-18T19:11:40.000000Z\", \"expiry_date\": \"2026-01-31T00:00:00.000000Z\", \"report_type\": \"Laporan Sertifikasi Ulang\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000010\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": 1, \"certification_report_id\": \"10\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 19:11:40', '2026-01-18 19:11:40');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('22', 'SDH-64287D36', '21', 'ITS-1D79F9E0', 'reduce_stock', 'Stok dikurangi: 5000 kg - Alasan: jelek', '{\"id\": 21, \"plant_id\": 1, \"quantity\": \"10000.00\", \"edited_at\": null, \"edited_by\": null, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:11:40.000000Z\", \"updated_at\": \"2026-01-18T19:11:40.000000Z\", \"expiry_date\": \"2026-01-31T00:00:00.000000Z\", \"report_type\": \"Laporan Sertifikasi Ulang\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000010\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": 1, \"certification_report_id\": 10, \"estimated_sale_price_per_kg\": \"10000.00\"}', '{\"id\": 21, \"plant_id\": 1, \"quantity\": \"5000.00\", \"edited_at\": \"2026-01-18T20:23:00.000000Z\", \"edited_by\": 1, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:11:40.000000Z\", \"updated_at\": \"2026-01-18T20:23:00.000000Z\", \"expiry_date\": \"2026-01-31T00:00:00.000000Z\", \"report_type\": \"Laporan Sertifikasi Ulang\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000010\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"5000.00\", \"planting_location_id\": 1, \"certification_report_id\": 10, \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 20:23:00', '2026-01-18 20:23:00');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('23', 'SDH-F267DD6D', '20', 'ITS-3D18A6D8', 'reduce_stock', 'Stok dikurangi: 5,000.00 kg - Alasan: jlek (Sinkronisasi dari pengurangan stok di bin)', '{\"id\": 20, \"plant_id\": 1, \"quantity\": \"10000.00\", \"edited_at\": null, \"edited_by\": null, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:10:48.000000Z\", \"updated_at\": \"2026-01-18T19:10:48.000000Z\", \"expiry_date\": \"2026-01-26T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000009\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": 1, \"certification_report_id\": 9, \"estimated_sale_price_per_kg\": \"10000.00\"}', '{\"id\": 20, \"plant_id\": 1, \"quantity\": \"5000.00\", \"edited_at\": \"2026-01-18T20:37:13.000000Z\", \"edited_by\": 1, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:10:48.000000Z\", \"updated_at\": \"2026-01-18T20:37:13.000000Z\", \"expiry_date\": \"2026-01-26T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000009\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"5000.00\", \"planting_location_id\": 1, \"certification_report_id\": 9, \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 20:37:13', '2026-01-18 20:37:13');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('24', 'SDH-2D073D9D', '20', 'ITS-3D18A6D8', 'reduce_stock', 'Stok dikurangi: 3000 kg - Alasan: jlekk', '{\"id\": 20, \"plant_id\": 1, \"quantity\": \"5000.00\", \"edited_at\": \"2026-01-18T20:37:13.000000Z\", \"edited_by\": 1, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:10:48.000000Z\", \"updated_at\": \"2026-01-18T20:37:13.000000Z\", \"expiry_date\": \"2026-01-26T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000009\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"5000.00\", \"planting_location_id\": 1, \"certification_report_id\": 9, \"estimated_sale_price_per_kg\": \"10000.00\"}', '{\"id\": 20, \"plant_id\": 1, \"quantity\": \"2000.00\", \"edited_at\": \"2026-01-18T20:37:35.000000Z\", \"edited_by\": 1, \"seed_unit\": \"kg\", \"created_at\": \"2026-01-18T19:10:48.000000Z\", \"updated_at\": \"2026-01-18T20:37:35.000000Z\", \"expiry_date\": \"2026-01-26T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000009\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": 1, \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"2000.00\", \"planting_location_id\": 1, \"certification_report_id\": 9, \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-18 20:37:35', '2026-01-18 20:37:35');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('25', 'SDH-72E8D0A6', '22', 'ITS-02EFF579', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 22, \"plant_id\": 1, \"quantity\": \"9000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-01-19T10:45:19.000000Z\", \"updated_at\": \"2026-01-19T10:45:19.000000Z\", \"expiry_date\": \"2026-04-30T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000011\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 1, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"9000.00\", \"total_seed_quantity\": \"9000.00\", \"planting_location_id\": 1, \"certification_report_id\": \"11\", \"estimated_sale_price_per_kg\": \"10000.00\"}', '1', 'USR-1A85508A', '2026-01-19 10:45:19', '2026-01-19 10:45:19');
INSERT INTO `seed_histories` (`id`, `seed_history_id`, `inventory_type_seed_id`, `new_inventory_type_seed_id`, `action`, `description`, `old_data`, `new_data`, `user_id`, `new_user_id`, `created_at`, `updated_at`) VALUES ('26', 'SDH-F2611A74', '23', 'ITS-F4AFCF8D', 'create', 'Benih baru ditambahkan', NULL, '{\"id\": 23, \"plant_id\": 3, \"quantity\": \"10000.00\", \"seed_unit\": \"kg\", \"created_at\": \"2026-02-03T16:04:55.000000Z\", \"updated_at\": \"2026-02-03T16:04:55.000000Z\", \"expiry_date\": \"2026-05-31T00:00:00.000000Z\", \"report_type\": \"Laporan Pemeriksaan Pertanaman\", \"seed_per_unit\": \"1.00\", \"storage_number\": \"BPSB-2026-000012\", \"total_seed_unit\": \"kg\", \"filled_by_user_id\": \"1\", \"inventory_type_id\": 3, \"seed_per_unit_unit\": \"kg\", \"seed_unit_quantity\": \"10000.00\", \"total_seed_quantity\": \"10000.00\", \"planting_location_id\": 1, \"certification_report_id\": \"12\", \"estimated_sale_price_per_kg\": \"15000.00\"}', '1', 'USR-1A85508A', '2026-02-03 16:04:55', '2026-02-03 16:04:55');

-- Table: task_series
DROP TABLE IF EXISTS `task_series`;
CREATE TABLE `task_series` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_series_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` bigint unsigned NOT NULL,
  `new_template_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `series_tasks` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_series_task_series_id_unq` (`task_series_id`),
  KEY `task_series_template_id_foreign` (`template_id`),
  CONSTRAINT `task_series_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `task_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: task_templates
DROP TABLE IF EXISTS `task_templates`;
CREATE TABLE `task_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_template_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tasks_list` json DEFAULT NULL,
  `association` enum('penanaman','sertifikasi','gudang','penjualan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_templates_task_template_id_unq` (`task_template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: task_templates
INSERT INTO `task_templates` (`id`, `task_template_id`, `name`, `description`, `tasks_list`, `association`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'TTP-6807E297', 'Pembersihan Lahan', 'PEmbersihan Lahan', '[{\"title\": \"Pembersihan Lahan\", \"repeats\": \"\", \"checklist\": [], \"new_status\": \"dalam_progress\", \"task_color\": \"#28a745\", \"association\": \"penanaman\", \"description\": \"PEmbersihan Lahan\", \"hours_spent\": null, \"task_report\": \"\", \"new_priority\": \"medium\"}]', 'penanaman', '1', '2026-02-02 00:06:36', '2026-02-02 00:06:36');

-- Table: tasks
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `new_template_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `series_id` bigint unsigned DEFAULT NULL,
  `new_series_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_tagged` tinyint(1) NOT NULL DEFAULT '0',
  `planting_location_id` bigint unsigned DEFAULT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_edited_at` timestamp NULL DEFAULT NULL,
  `last_edited_by` bigint unsigned DEFAULT NULL,
  `new_last_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `new_assigned_to` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `new_created_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collaborators` json DEFAULT NULL,
  `new_priority` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tasks_task_id_unq` (`task_id`),
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: tasks
INSERT INTO `tasks` (`id`, `task_id`, `title`, `description`, `task_report`, `checklist`, `attachments`, `association`, `new_status`, `priority`, `status`, `due_date`, `due_time`, `repeats`, `hours_spent`, `template_id`, `new_template_id`, `series_id`, `new_series_id`, `location`, `location_tagged`, `planting_location_id`, `new_planting_location_id`, `planting_id`, `new_planting_id`, `task_color`, `created_at`, `updated_at`, `last_edited_at`, `last_edited_by`, `new_last_edited_by`, `assigned_to`, `new_assigned_to`, `created_by`, `new_created_by`, `collaborators`, `new_priority`, `start_date`, `start_time`) VALUES ('1', 'TSK-8CEADEDF', 'Pembersihan Lahan', 'PEmbersihan Lahan', NULL, '[]', NULL, 'penanaman', 'dalam_progress', 'medium', 'pending', '2026-02-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '1', 'LOC-C072EF9C', '5', 'PLN-17B28D05', NULL, '2026-02-02 00:06:54', '2026-02-02 00:06:54', NULL, NULL, NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, 'medium', NULL, NULL);
INSERT INTO `tasks` (`id`, `task_id`, `title`, `description`, `task_report`, `checklist`, `attachments`, `association`, `new_status`, `priority`, `status`, `due_date`, `due_time`, `repeats`, `hours_spent`, `template_id`, `new_template_id`, `series_id`, `new_series_id`, `location`, `location_tagged`, `planting_location_id`, `new_planting_location_id`, `planting_id`, `new_planting_id`, `task_color`, `created_at`, `updated_at`, `last_edited_at`, `last_edited_by`, `new_last_edited_by`, `assigned_to`, `new_assigned_to`, `created_by`, `new_created_by`, `collaborators`, `new_priority`, `start_date`, `start_time`) VALUES ('2', 'TSK-96E817B2', 'yaya', 'yaya', NULL, '[]', NULL, 'penanaman', 'dalam_progress', 'medium', 'pending', '2026-02-02', '08:27:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '1', 'LOC-C072EF9C', '5', 'PLN-17B28D05', NULL, '2026-02-02 00:26:19', '2026-02-02 00:26:19', NULL, NULL, NULL, NULL, NULL, '1', 'USR-1A85508A', NULL, 'medium', NULL, NULL);

-- Table: treatments
DROP TABLE IF EXISTS `treatments`;
CREATE TABLE `treatments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `treatment_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `treatment_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `new_responsible_person_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_id` bigint unsigned DEFAULT NULL,
  `new_planting_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `new_edited_by` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `treatments_treatment_id_unq` (`treatment_id`),
  KEY `treatments_planting_location_id_foreign` (`planting_location_id`),
  KEY `treatments_planting_id_foreign` (`planting_id`),
  KEY `treatments_responsible_person_id_foreign` (`responsible_person_id`),
  KEY `treatments_edited_by_foreign` (`edited_by`),
  CONSTRAINT `treatments_edited_by_foreign` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `treatments_planting_id_foreign` FOREIGN KEY (`planting_id`) REFERENCES `plantings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `treatments_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `treatments_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: treatments
INSERT INTO `treatments` (`id`, `treatment_id`, `treatment_name`, `planting_location_id`, `new_planting_location_id`, `responsible_person_id`, `new_responsible_person_id`, `planting_id`, `new_planting_id`, `treatment_type`, `product_detail`, `opt_institution`, `application_method`, `withholding_period_days`, `technician`, `institution_source`, `attachment`, `batch_number`, `description`, `treatment_date`, `retreat_date`, `treatment_location`, `amount_applied`, `unit_measurement`, `total_cost`, `keywords`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`) VALUES ('1', 'TRT-5640E5A6', 'Pengendalian Hama', '1', 'LOC-C072EF9C', '1', 'USR-1A85508A', '5', 'PLN-17B28D05', 'Pemupukan', NULL, NULL, 'Semprot', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-02', NULL, NULL, NULL, NULL, '10000.00', NULL, '2026-02-02 00:44:31', '2026-02-02 00:44:31', NULL, NULL, NULL);
INSERT INTO `treatments` (`id`, `treatment_id`, `treatment_name`, `planting_location_id`, `new_planting_location_id`, `responsible_person_id`, `new_responsible_person_id`, `planting_id`, `new_planting_id`, `treatment_type`, `product_detail`, `opt_institution`, `application_method`, `withholding_period_days`, `technician`, `institution_source`, `attachment`, `batch_number`, `description`, `treatment_date`, `retreat_date`, `treatment_location`, `amount_applied`, `unit_measurement`, `total_cost`, `keywords`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`) VALUES ('2', 'TRT-899307F3', 'Pengendalian Hama', '1', 'LOC-C072EF9C', '1', 'USR-1A85508A', '5', 'PLN-17B28D05', 'Pemupukan', NULL, NULL, 'Semprot', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-02', NULL, NULL, NULL, NULL, '10000.00', NULL, '2026-02-02 00:44:35', '2026-02-02 00:44:35', NULL, NULL, NULL);
INSERT INTO `treatments` (`id`, `treatment_id`, `treatment_name`, `planting_location_id`, `new_planting_location_id`, `responsible_person_id`, `new_responsible_person_id`, `planting_id`, `new_planting_id`, `treatment_type`, `product_detail`, `opt_institution`, `application_method`, `withholding_period_days`, `technician`, `institution_source`, `attachment`, `batch_number`, `description`, `treatment_date`, `retreat_date`, `treatment_location`, `amount_applied`, `unit_measurement`, `total_cost`, `keywords`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`) VALUES ('3', 'TRT-D7557C3B', 'Pengendalian Hama', '1', 'LOC-C072EF9C', '1', 'USR-1A85508A', '5', 'PLN-17B28D05', 'Pemupukan', NULL, NULL, 'Semprot', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-02', NULL, NULL, NULL, NULL, '10000.00', NULL, '2026-02-02 00:44:42', '2026-02-02 00:44:42', NULL, NULL, NULL);
INSERT INTO `treatments` (`id`, `treatment_id`, `treatment_name`, `planting_location_id`, `new_planting_location_id`, `responsible_person_id`, `new_responsible_person_id`, `planting_id`, `new_planting_id`, `treatment_type`, `product_detail`, `opt_institution`, `application_method`, `withholding_period_days`, `technician`, `institution_source`, `attachment`, `batch_number`, `description`, `treatment_date`, `retreat_date`, `treatment_location`, `amount_applied`, `unit_measurement`, `total_cost`, `keywords`, `created_at`, `updated_at`, `edited_at`, `edited_by`, `new_edited_by`) VALUES ('4', 'TRT-DB91270C', 'Pengendalian Hama', '1', 'LOC-C072EF9C', '1', 'USR-1A85508A', '5', 'PLN-17B28D05', 'Pemupukan', NULL, NULL, 'Semprot', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-02', NULL, NULL, NULL, NULL, '10000.00', NULL, '2026-02-02 00:45:27', '2026-02-02 00:45:27', NULL, NULL, NULL);

-- Table: user_planting_location_land_manager
DROP TABLE IF EXISTS `user_planting_location_land_manager`;
CREATE TABLE `user_planting_location_land_manager` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_planting_location_land_manager_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_land_manager_user_unique` (`planting_location_id`,`user_id`),
  UNIQUE KEY `user_planting_locati_user_planting_locati_unq` (`user_planting_location_land_manager_id`),
  KEY `user_planting_location_land_manager_user_id_foreign` (`user_id`),
  CONSTRAINT `user_planting_location_land_manager_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_planting_location_land_manager_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_planting_location_land_worker
DROP TABLE IF EXISTS `user_planting_location_land_worker`;
CREATE TABLE `user_planting_location_land_worker` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_planting_location_land_worker_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planting_location_id` bigint unsigned NOT NULL,
  `new_planting_location_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `new_user_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planting_location_land_worker_user_unique` (`planting_location_id`,`user_id`),
  UNIQUE KEY `user_planting_locati_user_planting_locati_unq` (`user_planting_location_land_worker_id`),
  KEY `user_planting_location_land_worker_user_id_foreign` (`user_id`),
  CONSTRAINT `user_planting_location_land_worker_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_planting_location_land_worker_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
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
  UNIQUE KEY `users_user_id_unq` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: users
INSERT INTO `users` (`id`, `user_id`, `name`, `full_name`, `status`, `contact_type`, `organization`, `position`, `nip`, `primary_phone`, `primary_phone_is_whatsapp`, `secondary_phone`, `address`, `province`, `city`, `district`, `village`, `notes`, `email`, `photo_path`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `location_placement`) VALUES ('1', 'USR-1A85508A', 'Admin SIBIT', NULL, 'active', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin@sibit.com', NULL, '2026-01-12 09:42:37', '$2y$12$q4r.t4a0B1SzMn00xZr0h.yKo9pjftySDhZ3dmq.ZwNY4O98EZqwq', NULL, '2026-01-12 09:42:37', '2026-01-12 09:42:37', 'admin', NULL);
INSERT INTO `users` (`id`, `user_id`, `name`, `full_name`, `status`, `contact_type`, `organization`, `position`, `nip`, `primary_phone`, `primary_phone_is_whatsapp`, `secondary_phone`, `address`, `province`, `city`, `district`, `village`, `notes`, `email`, `photo_path`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `location_placement`) VALUES ('2', 'USR-27ADECD1', 'karin', NULL, 'active', NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'karin@sibit.com', NULL, NULL, '$2y$12$LOhZTz9phe8XZeZWbkB7KOpZlePL4.KakrD9GDo/Pi9fTp9zTYSje', NULL, '2026-01-13 04:25:45', '2026-01-13 04:25:45', 'penangkar', 'Lubuk Minturun');

-- Table: warehouses
DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `responsible_person_id` bigint unsigned DEFAULT NULL,
  `new_responsible_person_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_internal_id_unique` (`internal_id`),
  UNIQUE KEY `warehouses_warehouse_id_unq` (`warehouse_id`),
  KEY `warehouses_responsible_person_id_foreign` (`responsible_person_id`),
  CONSTRAINT `warehouses_responsible_person_id_foreign` FOREIGN KEY (`responsible_person_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: warehouses
INSERT INTO `warehouses` (`id`, `warehouse_id`, `name`, `internal_id`, `tracking_type`, `description`, `responsible_person_id`, `new_responsible_person_id`, `created_at`, `updated_at`) VALUES ('1', 'WHS-330243E6', 'Gudang Lubuk Minturun', '01', 'bin_separated', NULL, '1', 'USR-1A85508A', '2026-01-13 05:04:37', '2026-01-13 05:04:37');

SET FOREIGN_KEY_CHECKS=1;
