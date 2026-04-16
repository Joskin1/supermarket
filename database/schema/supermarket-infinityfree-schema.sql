/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_actor_id_created_at_index` (`actor_id`,`created_at`),
  KEY `activity_logs_event_index` (`event`),
  CONSTRAINT `activity_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backup_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `backup_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `file_size_bytes` bigint unsigned DEFAULT NULL,
  `checksum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `backup_runs_backup_code_unique` (`backup_code`),
  UNIQUE KEY `backup_runs_file_path_unique` (`file_path`),
  KEY `backup_runs_created_by_foreign` (`created_by`),
  KEY `backup_runs_status_index` (`status`),
  CONSTRAINT `backup_runs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_category_sales_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_category_sales_summaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_date` date NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `category_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_quantity_sold` int unsigned NOT NULL DEFAULT '0',
  `total_sales_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `transactions_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dcss_sales_date_category_snapshot_unique` (`sales_date`,`category_snapshot`),
  KEY `daily_category_sales_summaries_category_id_foreign` (`category_id`),
  KEY `daily_category_sales_summaries_sales_date_index` (`sales_date`),
  CONSTRAINT `daily_category_sales_summaries_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_product_sales_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_product_sales_summaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_date` date NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_code_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `category_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_quantity_sold` int unsigned NOT NULL DEFAULT '0',
  `total_sales_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `transactions_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_product_sales_summaries_sales_date_product_id_unique` (`sales_date`,`product_id`),
  KEY `daily_product_sales_summaries_product_id_foreign` (`product_id`),
  KEY `daily_product_sales_summaries_sales_date_index` (`sales_date`),
  KEY `daily_product_sales_summaries_category_id_index` (`category_id`),
  CONSTRAINT `daily_product_sales_summaries_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `daily_product_sales_summaries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `daily_sales_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_sales_summaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_date` date NOT NULL,
  `total_transactions_count` int unsigned NOT NULL DEFAULT '0',
  `total_quantity_sold` int unsigned NOT NULL DEFAULT '0',
  `total_sales_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `batches_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_sales_summaries_sales_date_unique` (`sales_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `product_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `purchase_price` decimal(12,2) NOT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `current_stock` int unsigned NOT NULL DEFAULT '0',
  `reorder_level` int unsigned NOT NULL DEFAULT '0',
  `unit_of_measure` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  KEY `products_current_stock_reorder_level_index` (`current_stock`,`reorder_level`),
  KEY `products_product_group_index` (`product_group`),
  KEY `products_name_index` (`name`),
  KEY `products_slug_index` (`slug`),
  KEY `products_brand_index` (`brand`),
  KEY `products_is_active_index` (`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_import_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_import_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded',
  `sales_date_from` date DEFAULT NULL,
  `sales_date_to` date DEFAULT NULL,
  `total_rows` int unsigned NOT NULL DEFAULT '0',
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `failed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_quantity_sold` int unsigned NOT NULL DEFAULT '0',
  `total_sales_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_import_batches_batch_code_unique` (`batch_code`),
  KEY `sales_import_batches_uploaded_by_foreign` (`uploaded_by`),
  KEY `sales_import_batches_status_created_at_index` (`status`,`created_at`),
  KEY `sales_import_batches_file_hash_index` (`file_hash`),
  KEY `sales_import_batches_status_index` (`status`),
  KEY `sales_import_batches_sales_date_from_index` (`sales_date_from`),
  KEY `sales_import_batches_sales_date_to_index` (`sales_date_to`),
  KEY `sales_import_batches_processed_at_index` (`processed_at`),
  CONSTRAINT `sales_import_batches_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_import_failures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_import_failures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `row_number` int unsigned NOT NULL,
  `raw_row` json NOT NULL,
  `error_messages` json NOT NULL,
  `product_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_import_failures_batch_id_row_number_index` (`batch_id`,`row_number`),
  KEY `sales_import_failures_product_code_index` (`product_code`),
  KEY `sales_import_failures_sales_date_index` (`sales_date`),
  CONSTRAINT `sales_import_failures_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `sales_import_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sales_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_code_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity_sold` int unsigned NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `sales_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sales_time` time DEFAULT NULL,
  `source_row_number` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_records_created_by_foreign` (`created_by`),
  KEY `sales_records_batch_id_sales_date_index` (`batch_id`,`sales_date`),
  KEY `sales_records_product_id_sales_date_index` (`product_id`,`sales_date`),
  KEY `sales_records_product_code_snapshot_index` (`product_code_snapshot`),
  KEY `sales_records_category_snapshot_index` (`category_snapshot`),
  KEY `sales_records_sales_date_index` (`sales_date`),
  KEY `sales_records_batch_id_source_row_number_index` (`batch_id`,`source_row_number`),
  KEY `sales_records_sales_time_index` (`sales_time`),
  CONSTRAINT `sales_records_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `sales_import_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_records_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_records_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `quantity_change` int NOT NULL,
  `previous_stock` int unsigned NOT NULL,
  `new_stock` int unsigned NOT NULL,
  `counted_stock` int unsigned DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `adjustment_date` date NOT NULL,
  `adjusted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_adjustments_adjusted_by_foreign` (`adjusted_by`),
  KEY `stock_adjustments_product_id_adjustment_date_index` (`product_id`,`adjustment_date`),
  KEY `stock_adjustments_reference_index` (`reference`),
  KEY `stock_adjustments_adjustment_date_index` (`adjustment_date`),
  CONSTRAINT `stock_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `quantity_added` int unsigned NOT NULL,
  `unit_cost_price` decimal(12,2) NOT NULL,
  `unit_selling_price` decimal(12,2) NOT NULL,
  `stock_date` date NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_entries_created_by_foreign` (`created_by`),
  KEY `stock_entries_product_id_stock_date_index` (`product_id`,`stock_date`),
  KEY `stock_entries_stock_date_index` (`stock_date`),
  KEY `stock_entries_reference_index` (`reference`),
  CONSTRAINT `stock_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_entries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NGN',
  `low_stock_contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_footer` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_08_14_170933_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_04_09_232426_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_04_10_000001_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_04_10_000002_create_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_04_10_000003_create_stock_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_04_10_005807_create_sales_import_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_04_10_005810_create_sales_records_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_04_10_005813_create_sales_import_failures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_04_10_100001_create_daily_sales_summaries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_04_10_100002_create_daily_product_sales_summaries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_04_10_100003_create_daily_category_sales_summaries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_04_10_100004_update_daily_category_sales_summaries_uniqueness',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_04_11_000001_add_sales_time_and_source_row_number_to_sales_records_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_04_13_000001_create_stock_adjustments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_04_13_000002_create_activity_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_04_13_000003_create_system_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_04_13_000004_create_backup_runs_table',1);
