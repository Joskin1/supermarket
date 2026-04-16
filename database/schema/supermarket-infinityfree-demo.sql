
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Demo Sudo','akinjoseph221@gmail.com','2026-04-13 18:36:08','$2y$12$xUcmqsHf9WWYvf7rtZtJDui.0HeOn1AudSwV1Pz6g5ow2Z3Nsh/du',NULL,NULL,NULL,'rQVkYVoTBUqVaPOClqHVb7nu25XOsUH4D96xuNOqTbTd4cRL4H0ELLA733m2','2026-04-13 18:36:08','2026-04-13 18:36:08'),(2,'Adaeze Manager','store-manager@supermarket.test','2026-04-13 18:36:08','$2y$12$UG1ym.Mf6aFNwlLPILn2G./Hb3sIw2c9o/bVTBO732v6EaeA1b07S',NULL,NULL,NULL,NULL,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(3,'Kunle Inventory','inventory-admin@supermarket.test','2026-04-13 18:36:08','$2y$12$/AGmjRmAFwPZmLsa8K9QM.4G4WFppkyRRpJl4gbX32JE2F7ie5IhK',NULL,NULL,NULL,NULL,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(4,'Bola Sales','sales-admin@supermarket.test','2026-04-13 18:36:08','$2y$12$g3DakiUW4p5t1gEA4TdL6.T5NOMSgWVxYfRs./.ogEr.EPgnGcD9m',NULL,NULL,NULL,NULL,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(5,'Destiny Christensen','ajewole@example.com','2026-04-13 21:19:51','$2y$12$VRNUJhdxQEU5LmsoVNuPCOE7sDGU5aXoKryeKM3QwukmmhrZE7/WG',NULL,NULL,NULL,'X5ykFOjPmJ1KPuN1z9eE3fDpD7shc8k5kj6RHoOcH2RMJZYD6YEJ6ckTLRCu','2026-04-13 21:18:32','2026-04-13 21:19:51'),(6,'Ajewole','aje@example.com','2026-04-13 21:22:20','$2y$12$I4dLTq5VW8Y0awNFWuIzzuc0hJAGHSVPBkC70w0OryiH/Si0mxR7K',NULL,NULL,NULL,'cvSdQ09S95WbWvMJvTjECShS0zXkuHjAdn62od1ZMpZo9SKI5dbPTy9ONpXk','2026-04-13 21:20:48','2026-04-13 21:22:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'sudo','web','2026-04-13 18:36:07','2026-04-13 18:36:07'),(2,'admin','web','2026-04-13 18:36:07','2026-04-13 18:36:07');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3),(2,'App\\Models\\User',4),(2,'App\\Models\\User',5),(2,'App\\Models\\User',6);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Beverages','beverages','Soft drinks, bottled water, juices, and quick-serve refreshment items.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(2,'Groceries','groceries','Pantry staples such as grains, sugar, oil, and meal-prep essentials.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(3,'Toiletries','toiletries','Daily personal care items that move steadily every week.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(4,'Household Items','household-items','Cleaning and upkeep products for home restocking trips.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(5,'Snacks & Confectionery','snacks-confectionery','Impulse snacks and shelf-stable treats near the counter and aisles.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(6,'Dairy & Breakfast','dairy-breakfast','Breakfast staples, cereals, and powdered milk products.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08'),(7,'Baby Care','baby-care','Trusted baby essentials for repeat-family purchases.',1,'2026-04-13 18:36:08','2026-04-13 18:36:08');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Soft Drink','Coca-Cola Classic Soft Drink','coca-cola-classic-soft-drink-50cl','BEV-COKE-50CL','Coca-Cola','50cl','Fast-moving chilled soft drink for daily walk-in traffic.',520.00,650.00,20,18,'bottle',1,'2026-04-13 18:36:08','2026-04-13 21:06:12'),(2,1,'Soft Drink','Fanta Orange Soft Drink','fanta-orange-soft-drink-50cl','BEV-FANTA-50CL','Fanta','50cl','Popular orange soda sold from the drinks aisle and chiller.',520.00,650.00,26,18,'bottle',1,'2026-04-13 18:36:08','2026-04-13 21:06:12'),(3,1,'Soft Drink','Pepsi Cola Soft Drink','pepsi-cola-soft-drink-50cl','BEV-PEPSI-50CL','Pepsi','50cl','Alternative cola option with steady cooler sales.',520.00,650.00,17,18,'bottle',1,'2026-04-13 18:36:08','2026-04-13 21:06:14'),(4,1,'Juice','Five Alive Pulpy Orange Juice','five-alive-pulpy-orange-juice-1l','BEV-FIVEALIVE-1L','Five Alive','1L','Family-sized juice line often bought with breakfast items.',1180.00,1500.00,15,8,'carton',1,'2026-04-13 18:36:08','2026-04-13 21:06:12'),(5,1,'Water','Nestle Pure Life Water','nestle-pure-life-water-75cl','BEV-PURELIFE-75CL','Nestle','75cl','Bottled water line with fast daily turnover.',250.00,350.00,0,6,'bottle',1,'2026-04-13 18:36:08','2026-04-13 18:36:10'),(6,2,'Rice','Mama Gold Parboiled Rice','mama-gold-parboiled-rice-5kg','GRO-RICE-MG-5KG','Mama Gold','5kg','Reliable rice staple for weekly family shopping.',7700.00,8900.00,11,5,'bag',1,'2026-04-13 18:36:08','2026-04-13 21:06:13'),(7,2,'Pasta','Golden Penny Spaghetti','golden-penny-spaghetti-500g','GRO-SPAG-GP-500G','Golden Penny','500g','Fast pantry essential often purchased in multiples.',650.00,900.00,17,10,'pack',1,'2026-04-13 18:36:08','2026-04-13 21:06:12'),(8,2,'Cooking Oil','Devon King\'s Vegetable Oil','devon-kings-vegetable-oil-1l','GRO-OIL-DK-1L','Devon King\'s','1L','Mid-sized cooking oil line for everyday household restocks.',2050.00,2400.00,10,6,'bottle',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(9,2,'Sugar','Dangote Sugar','dangote-sugar-1kg','GRO-SUGAR-DAN-1KG','Dangote','1kg','Household sugar staple with steady repeat demand.',1450.00,1800.00,15,6,'bag',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(10,2,'Swallow','Semovita','semovita-1kg','GRO-SEMOVITA-1KG','Honeywell','1kg','Semolina staple that moves strongly at month-end and weekends.',1320.00,1700.00,4,5,'pack',1,'2026-04-13 18:36:09','2026-04-13 21:06:14'),(11,3,'Toothpaste','Colgate MaxFresh Toothpaste','colgate-maxfresh-toothpaste-120g','TOI-TP-COL-120G','Colgate','120g','Everyday toothpaste with dependable basket frequency.',1300.00,1650.00,11,8,'tube',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(12,3,'Roll-On','Nivea Men Roll-On','nivea-men-roll-on-50ml','TOI-ROLL-NIV-50ML','Nivea','50ml','Fast recognizable deodorant line for quick top-up purchases.',2600.00,3200.00,6,8,'bottle',1,'2026-04-13 18:36:09','2026-04-13 21:06:13'),(13,3,'Bathing Soap','Dettol Original Soap','dettol-original-soap-175g','TOI-SOAP-DETTOL-175G','Dettol','175g','Trusted antiseptic bathing soap with constant repeat sales.',650.00,850.00,13,10,'bar',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(14,3,'Toothbrush','Oral-B Medium Toothbrush','oral-b-medium-toothbrush-medium','TOI-TB-ORALB-MED','Oral-B','Medium','Single toothbrush line used to test low-stock behavior.',480.00,750.00,1,6,'pcs',1,'2026-04-13 18:36:09','2026-04-13 21:06:13'),(15,4,'Detergent','Ariel Ultra Clean Detergent','ariel-ultra-clean-detergent-850g','HOU-DET-ARIEL-850G','Ariel','850g','Laundry detergent line with higher-ticket household margins.',3400.00,4200.00,10,7,'pack',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(16,4,'Bleach','Hypo Original Bleach','hypo-original-bleach-1l','HOU-BLEACH-HYPO-1L','Hypo','1L','Fast-moving bleach used to demonstrate out-of-stock scenarios.',950.00,1300.00,0,6,'bottle',1,'2026-04-13 18:36:09','2026-04-13 18:36:10'),(17,4,'Toilet Cleaner','Harpic Power Plus Toilet Cleaner','harpic-power-plus-toilet-cleaner-500ml','HOU-HARPIC-500ML','Harpic','500ml','Bathroom cleaner frequently bought with bleach and detergents.',1450.00,1850.00,5,4,'bottle',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(18,5,'Pastry Snack','Gala Sausage Roll','gala-sausage-roll-80g','SNK-GALA-80G','Gala','80g','Impulse counter snack with high turnover and quick restocking needs.',250.00,400.00,3,12,'pcs',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(19,5,'Crisps','Pringles Original','pringles-original-165g','SNK-PRINGLES-165G','Pringles','165g','Premium snack tube with slower but higher-value movement.',2900.00,3500.00,6,4,'can',1,'2026-04-13 18:36:09','2026-04-13 21:06:14'),(20,5,'Confectionery','Cadbury TomTom Rolls','cadbury-tomtom-rolls-40s','SNK-TOMTOM-40S','Cadbury','40s','Counter candy line with steady add-on purchases.',520.00,800.00,11,5,'roll',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(21,6,'Milk','Peak Full Cream Milk Powder','peak-full-cream-milk-powder-400g','DBR-PEAK-400G','Peak','400g','High-demand powdered milk for breakfast shoppers.',3200.00,3800.00,0,6,'tin',1,'2026-04-13 18:36:09','2026-04-13 21:06:14'),(22,6,'Chocolate Drink','Milo Refill','milo-refill-400g','DBR-MILO-400G','Milo','400g','Breakfast chocolate drink refill with stable repeat sales.',2650.00,3200.00,7,6,'pack',1,'2026-04-13 18:36:09','2026-04-13 21:06:13'),(23,6,'Cereal','Kellogg\'s Corn Flakes','kelloggs-corn-flakes-500g','DBR-CORNFLAKES-500G','Kellogg\'s','500g','Breakfast cereal line seeded to sit right on the reorder threshold.',3550.00,4200.00,4,5,'box',1,'2026-04-13 18:36:09','2026-04-13 21:06:13'),(24,7,'Lotion','Cussons Baby Lotion','cussons-baby-lotion-200ml','BABY-CUSSONS-200ML','Cussons','200ml','Trusted baby lotion purchased by returning family customers.',2250.00,2800.00,4,3,'bottle',1,'2026-04-13 18:36:09','2026-04-13 21:06:12'),(25,7,'Diaper','Molfix Baby Diapers Midi','molfix-baby-diapers-midi-28s','BABY-MOLFIX-MIDI-28S','Molfix','28s','Core diaper pack used to show higher-ticket baby care sales.',5200.00,6200.00,6,4,'pack',1,'2026-04-13 18:36:09','2026-04-13 21:06:13');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_entries` WRITE;
/*!40000 ALTER TABLE `stock_entries` DISABLE KEYS */;
INSERT INTO `stock_entries` VALUES (1,1,36,500.00,620.00,'2026-03-30','DEMO-STOCK-BEV-COKE-50CL-01','Opening cooler stock before the week began.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(2,1,24,520.00,650.00,'2026-04-08','DEMO-STOCK-BEV-COKE-50CL-02','Weekend replenishment for beverage demand.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(3,2,30,500.00,620.00,'2026-03-31','DEMO-STOCK-BEV-FANTA-50CL-01','Initial shelf fill for the orange soda bay.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(4,2,18,520.00,650.00,'2026-04-08','DEMO-STOCK-BEV-FANTA-50CL-02','Replenished after strong weekend movement.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(5,3,24,500.00,620.00,'2026-04-01','DEMO-STOCK-BEV-PEPSI-50CL-01','Base stock for the cola shelf.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(6,3,12,520.00,650.00,'2026-04-08','DEMO-STOCK-BEV-PEPSI-50CL-02','Short top-up to balance cola options.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(7,4,12,1150.00,1450.00,'2026-04-01','DEMO-STOCK-BEV-FIVEALIVE-1L-01','Initial juice shelf arrangement.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(8,4,8,1180.00,1500.00,'2026-04-07','DEMO-STOCK-BEV-FIVEALIVE-1L-02','Refill before the new sales week.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(9,5,12,240.00,330.00,'2026-04-02','DEMO-STOCK-BEV-PURELIFE-75CL-01','Loaded into the water section for weekday trade.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(10,5,6,250.00,350.00,'2026-04-09','DEMO-STOCK-BEV-PURELIFE-75CL-02','Small refill before the sales upload period.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(11,6,8,7600.00,8800.00,'2026-04-03','DEMO-STOCK-GRO-RICE-MG-5KG-01','Base rice stock from the last supply run.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(12,6,6,7700.00,8900.00,'2026-04-07','DEMO-STOCK-GRO-RICE-MG-5KG-02','Supplementary rice stock for the dry-goods lane.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(13,7,18,620.00,860.00,'2026-04-03','DEMO-STOCK-GRO-SPAG-GP-500G-01','Pasta shelf opening balance.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(14,7,12,650.00,900.00,'2026-04-08','DEMO-STOCK-GRO-SPAG-GP-500G-02','Quick restock before the weekend rush.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(15,8,10,2000.00,2350.00,'2026-04-04','DEMO-STOCK-GRO-OIL-DK-1L-01','Cooking oil base stock from regular supplier.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(16,8,6,2050.00,2400.00,'2026-04-09','DEMO-STOCK-GRO-OIL-DK-1L-02','Top-up after midweek family shopping.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(17,9,12,1400.00,1750.00,'2026-04-04','DEMO-STOCK-GRO-SUGAR-DAN-1KG-01','Sugar restock for the dry pantry section.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(18,9,8,1450.00,1800.00,'2026-04-09','DEMO-STOCK-GRO-SUGAR-DAN-1KG-02','Refill after early-week sales.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(19,10,8,1280.00,1650.00,'2026-04-04','DEMO-STOCK-GRO-SEMOVITA-1KG-01','Dry-goods opening stock for semolina items.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(20,10,6,1320.00,1700.00,'2026-04-09','DEMO-STOCK-GRO-SEMOVITA-1KG-02','Late refill before daily sales reconciliation.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(21,11,16,1250.00,1600.00,'2026-04-02','DEMO-STOCK-TOI-TP-COL-120G-01','Shelf reset for oral care products.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(22,11,8,1300.00,1650.00,'2026-04-08','DEMO-STOCK-TOI-TP-COL-120G-02','Oral care replenishment after steady movement.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(23,12,8,2550.00,3150.00,'2026-04-03','DEMO-STOCK-TOI-ROLL-NIV-50ML-01','Base stock for the male grooming shelf.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(24,12,4,2600.00,3200.00,'2026-04-09','DEMO-STOCK-TOI-ROLL-NIV-50ML-02','Short replenishment to maintain availability.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(25,13,20,620.00,820.00,'2026-04-01','DEMO-STOCK-TOI-SOAP-DETTOL-175G-01','Bulk soap restock for toiletries aisle.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(26,13,10,650.00,850.00,'2026-04-08','DEMO-STOCK-TOI-SOAP-DETTOL-175G-02','Secondary shelf fill ahead of weekend traffic.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(27,14,6,460.00,700.00,'2026-04-02','DEMO-STOCK-TOI-TB-ORALB-MED-01','Oral care accessory shelf launch stock.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(28,14,4,480.00,750.00,'2026-04-08','DEMO-STOCK-TOI-TB-ORALB-MED-02','Minor refill before sales upload review.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(29,15,10,3300.00,4100.00,'2026-04-03','DEMO-STOCK-HOU-DET-ARIEL-850G-01','Household aisle detergent reset.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(30,15,6,3400.00,4200.00,'2026-04-09','DEMO-STOCK-HOU-DET-ARIEL-850G-02','Quick household top-up after midweek restocking.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(31,16,8,920.00,1250.00,'2026-04-03','DEMO-STOCK-HOU-BLEACH-HYPO-1L-01','Cleaning aisle bleach allocation.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(32,16,4,950.00,1300.00,'2026-04-09','DEMO-STOCK-HOU-BLEACH-HYPO-1L-02','Top-up stock before the final daily imports.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(33,17,6,1400.00,1800.00,'2026-04-04','DEMO-STOCK-HOU-HARPIC-500ML-01','Bathroom cleaner shelf opening stock.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(34,17,4,1450.00,1850.00,'2026-04-09','DEMO-STOCK-HOU-HARPIC-500ML-02','Replenished with the weekly household order.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(35,18,16,240.00,380.00,'2026-04-03','DEMO-STOCK-SNK-GALA-80G-01','Counter snack tray opening stock.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(36,18,8,250.00,400.00,'2026-04-09','DEMO-STOCK-SNK-GALA-80G-02','Refilled after lunch-hour snack demand.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(37,19,8,2850.00,3400.00,'2026-04-04','DEMO-STOCK-SNK-PRINGLES-165G-01','Snack aisle feature stock.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(38,19,4,2900.00,3500.00,'2026-04-09','DEMO-STOCK-SNK-PRINGLES-165G-02','Premium snack top-up before weekend demand.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(39,20,12,500.00,760.00,'2026-04-04','DEMO-STOCK-SNK-TOMTOM-40S-01','Counter confectionery opening count.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(40,20,8,520.00,800.00,'2026-04-09','DEMO-STOCK-SNK-TOMTOM-40S-02','Refilled for checkout impulse purchases.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(41,21,6,3150.00,3700.00,'2026-04-03','DEMO-STOCK-DBR-PEAK-400G-01','Breakfast shelf anchor stock.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(42,21,4,3200.00,3800.00,'2026-04-09','DEMO-STOCK-DBR-PEAK-400G-02','Small milk refill before the current day trade.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(43,22,8,2600.00,3100.00,'2026-04-03','DEMO-STOCK-DBR-MILO-400G-01','Breakfast drinks opening allocation.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(44,22,6,2650.00,3200.00,'2026-04-09','DEMO-STOCK-DBR-MILO-400G-02','Replenished alongside milk products.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(45,23,5,3500.00,4100.00,'2026-04-04','DEMO-STOCK-DBR-CORNFLAKES-500G-01','Cereal shelf opening balance.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(46,23,4,3550.00,4200.00,'2026-04-09','DEMO-STOCK-DBR-CORNFLAKES-500G-02','Small top-up to keep the cereal section full.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(47,24,5,2200.00,2700.00,'2026-04-04','DEMO-STOCK-BABY-CUSSONS-200ML-01','Baby care shelf opening setup.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(48,24,3,2250.00,2800.00,'2026-04-09','DEMO-STOCK-BABY-CUSSONS-200ML-02','Minor refill after repeat family visits.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(49,25,6,5100.00,6100.00,'2026-04-04','DEMO-STOCK-BABY-MOLFIX-MIDI-28S-01','Diaper shelf opening stock.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09'),(50,25,4,5200.00,6200.00,'2026-04-09','DEMO-STOCK-BABY-MOLFIX-MIDI-28S-02','Small replenishment before current day trading.',3,'2026-04-13 18:36:09','2026-04-13 18:36:09');
/*!40000 ALTER TABLE `stock_entries` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_adjustments` WRITE;
/*!40000 ALTER TABLE `stock_adjustments` DISABLE KEYS */;
INSERT INTO `stock_adjustments` VALUES (1,1,-2,26,24,NULL,'Damaged units removed after shelf inspection.','ADJ-DEMO-DAMAGE-001','Local demo adjustment for loss control review.','2026-04-12',3,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,2,-1,28,27,27,'Physical stock count reconciliation.','ADJ-DEMO-COUNT-001','Local demo adjustment after manual shelf count.','2026-04-13',3,'2026-04-13 18:36:10','2026-04-13 18:36:10');
/*!40000 ALTER TABLE `stock_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sales_import_batches` WRITE;
/*!40000 ALTER TABLE `sales_import_batches` DISABLE KEYS */;
INSERT INTO `sales_import_batches` VALUES (1,'SIB-DEMO-20260410-A','SIB-DEMO-20260410-A.csv','sales-imports/demo/2026/04/SIB-DEMO-20260410-A.csv','daily-sales-2026-04-10-morning.csv','42e4f5196308b64d24932d414ae3e61f44e360231f0bef484f3367d9ae87affe',4,'processed','2026-04-10','2026-04-10',9,9,0,36,55850.00,'Morning and afternoon paper sales were reconciled without issues.','2026-04-10 18:18:00','2026-04-10 18:10:00','2026-04-10 18:18:00'),(2,'SIB-DEMO-20260412-B','SIB-DEMO-20260412-B.csv','sales-imports/demo/2026/04/SIB-DEMO-20260412-B.csv','daily-sales-2026-04-12-closing.csv','20a4ca49d6597fa3fef66daaad4ffebb91ac33c0d895e6f2e2b428883af09df3',2,'processed_with_failures','2026-04-12','2026-04-12',9,7,2,39,39900.00,'Closing sheet uploaded with two cashier entry issues flagged for review.','2026-04-12 20:14:00','2026-04-12 20:06:00','2026-04-12 20:14:00'),(3,'SIB-DEMO-20260413-C','SIB-DEMO-20260413-C.csv','sales-imports/demo/2026/04/SIB-DEMO-20260413-C.csv','daily-sales-2026-04-13-morning.csv','7fb62c9cf3f75fcee51aa2d4482753f0addb77fbcd7f0265bc735895d70d3cda',4,'processed','2026-04-13','2026-04-13',12,12,0,108,158400.00,'Morning shift uploaded cleanly after product-by-product cross-check.','2026-04-13 13:12:00','2026-04-13 13:02:00','2026-04-13 13:12:00'),(4,'SIB-DEMO-20260413-D','SIB-DEMO-20260413-D.csv','sales-imports/demo/2026/04/SIB-DEMO-20260413-D.csv','daily-sales-2026-04-13-afternoon.csv','4597261908ec1533cca8b0f4a52d1bf2c9b87f74579ef0e1622d4e59cff871d9',3,'processed_with_failures','2026-04-13','2026-04-13',10,8,2,51,67100.00,'Afternoon upload succeeded, but two rows were held back for correction.','2026-04-13 17:55:00','2026-04-13 17:45:00','2026-04-13 17:55:00'),(5,'SIB-DEMO-20260413-E','SIB-DEMO-20260413-E.csv','sales-imports/demo/2026/04/SIB-DEMO-20260413-E.csv','daily-sales-2026-04-13-correction-attempt.csv','b9c5a60ba6dcd23081ac056024870ed05661780b668e194a9bced211522ccb21',2,'failed','2026-04-13','2026-04-13',3,0,3,0,0.00,'Correction upload failed completely and should be replaced with a cleaned file.','2026-04-13 19:16:00','2026-04-13 19:10:00','2026-04-13 19:16:00'),(6,'SIB-DEMO-20260413-F','SIB-DEMO-20260413-F.csv','sales-imports/demo/2026/04/SIB-DEMO-20260413-F.csv','daily-sales-2026-04-13-night-pending.csv','dc512283a67abefe1505ced744dede9e1e07675ddeb77429406a64efead078ad',4,'uploaded',NULL,NULL,0,0,0,0,0.00,'Night shift template has been uploaded and is waiting for review before processing.',NULL,'2026-04-13 21:05:00','2026-04-13 21:05:00'),(7,'SIB-20260413-210611-C5NF','01KP4AN2DZWAPCAZARWV90ZRZA.xlsx','sales-imports/2026/04/01KP4AN2DZWAPCAZARWV90ZRZA.xlsx','daily-sales-template-2026-04-13.xlsx','c575568b42b079f21597398afa981bccc8cd84efe147c9f775abd699db6111b3',1,'processed_with_failures','2026-04-13','2026-04-13',25,23,2,38,87950.00,'Amet assumenda veritatis officiis libero qui dolore voluptas aute itaque in suscipit qui nihil molestiae lorem vel eaque ullamco\n\nSystem: Some rows were imported successfully, but one or more rows failed validation.','2026-04-13 21:06:17','2026-04-13 21:06:11','2026-04-13 21:06:17');
/*!40000 ALTER TABLE `sales_import_batches` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sales_import_failures` WRITE;
/*!40000 ALTER TABLE `sales_import_failures` DISABLE KEYS */;
INSERT INTO `sales_import_failures` VALUES (1,2,9,'{\"date\": \"2026-04-12\", \"note\": \"Product code was written from memory on the offline sheet.\", \"category\": \"Beverages\", \"unit_price\": 650, \"product_code\": \"BEV-SPRITE-50CL\", \"product_name\": \"Sprite Lemon-Lime Soft Drink\", \"total_amount\": 1950, \"quantity_sold\": 3}','[\"The product code does not match any existing product.\"]','BEV-SPRITE-50CL','Sprite Lemon-Lime Soft Drink','2026-04-12','2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,2,10,'{\"date\": \"2026-04-12\", \"note\": \"Cashier count exceeded available stock.\", \"category\": \"Toiletries\", \"unit_price\": 750, \"product_code\": \"TOI-TB-ORALB-MED\", \"product_name\": \"Oral-B Medium Toothbrush\", \"total_amount\": 10500, \"quantity_sold\": 14}','[\"The quantity sold exceeds the current stock for this product.\"]','TOI-TB-ORALB-MED','Oral-B Medium Toothbrush','2026-04-12','2026-04-13 18:36:10','2026-04-13 18:36:10'),(3,4,10,'{\"date\": \"2026-04-13\", \"note\": \"Total was written incorrectly on the paper sheet.\", \"category\": \"Beverages\", \"unit_price\": 650, \"product_code\": \"BEV-PEPSI-50CL\", \"product_name\": \"Pepsi Cola Soft Drink\", \"total_amount\": 1000, \"quantity_sold\": 2}','[\"The total amount must match unit price multiplied by quantity sold.\"]','BEV-PEPSI-50CL','Pepsi Cola Soft Drink','2026-04-13','2026-04-13 18:36:10','2026-04-13 18:36:10'),(4,4,11,'{\"date\": \"2026-04-13\", \"note\": \"Product has not been added to the master catalog yet.\", \"category\": \"Snacks & Confectionery\", \"unit_price\": 700, \"product_code\": \"SNK-LOCALCHIPS-100G\", \"product_name\": \"Local Plantain Chips\", \"total_amount\": 3500, \"quantity_sold\": 5}','[\"The product code does not match any existing product.\"]','SNK-LOCALCHIPS-100G','Local Plantain Chips','2026-04-13','2026-04-13 18:36:10','2026-04-13 18:36:10'),(5,5,2,'{\"date\": \"2026-04-13\", \"note\": \"Attempted to upload all pending rice sales in one row.\", \"category\": \"Groceries\", \"unit_price\": 8900, \"product_code\": \"GRO-RICE-MG-5KG\", \"product_name\": \"Mama Gold Parboiled Rice\", \"total_amount\": 356000, \"quantity_sold\": 40}','[\"The quantity sold exceeds the current stock for this product.\"]','GRO-RICE-MG-5KG','Mama Gold Parboiled Rice','2026-04-13','2026-04-13 18:36:10','2026-04-13 18:36:10'),(6,5,3,'{\"date\": \"2026-04-13\", \"note\": \"Quantity was left blank on the offline sheet.\", \"category\": \"Beverages\", \"unit_price\": 1500, \"product_code\": \"BEV-FIVEALIVE-1L\", \"product_name\": \"Five Alive Pulpy Orange Juice\", \"total_amount\": null, \"quantity_sold\": null}','[\"The quantity sold field is required.\"]','BEV-FIVEALIVE-1L','Five Alive Pulpy Orange Juice','2026-04-13','2026-04-13 18:36:10','2026-04-13 18:36:10'),(7,5,4,'{\"date\": \"2026-04-13\", \"note\": \"Cashier entered a rounded total instead of the exact amount.\", \"category\": \"Toiletries\", \"unit_price\": 1650, \"product_code\": \"TOI-TP-COL-120G\", \"product_name\": \"Colgate MaxFresh Toothpaste\", \"total_amount\": 2000, \"quantity_sold\": 2}','[\"The total amount must match unit price multiplied by quantity sold.\"]','TOI-TP-COL-120G','Colgate MaxFresh Toothpaste','2026-04-13','2026-04-13 18:36:10','2026-04-13 18:36:10'),(8,7,15,'{\"date\": \"2026-04-13\", \"note\": null, \"time\": null, \"unit_price\": 1300, \"product_code\": \"HOU-BLEACH-HYPO-1L\", \"product_name\": \"Hypo Original Bleach\", \"total_amount\": 1300, \"quantity_sold\": \"1\"}','[\"The quantity sold exceeds the remaining stock for this product at this row. Available stock: 0.\"]','HOU-BLEACH-HYPO-1L','Hypo Original Bleach','2026-04-13','2026-04-13 21:06:12','2026-04-13 21:06:12'),(9,7,20,'{\"date\": \"2026-04-13\", \"note\": null, \"time\": null, \"unit_price\": 350, \"product_code\": \"BEV-PURELIFE-75CL\", \"product_name\": \"Nestle Pure Life Water\", \"total_amount\": 350, \"quantity_sold\": \"1\"}','[\"The quantity sold exceeds the remaining stock for this product at this row. Available stock: 0.\"]','BEV-PURELIFE-75CL','Nestle Pure Life Water','2026-04-13','2026-04-13 21:06:13','2026-04-13 21:06:13');
/*!40000 ALTER TABLE `sales_import_failures` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sales_records` WRITE;
/*!40000 ALTER TABLE `sales_records` DISABLE KEYS */;
INSERT INTO `sales_records` VALUES (1,1,1,'BEV-COKE-50CL','Beverages','Coca-Cola Classic Soft Drink',650.00,8,5200.00,'2026-04-10','Packed from the front chiller display.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(2,1,2,'BEV-FANTA-50CL','Beverages','Fanta Orange Soft Drink',650.00,4,2600.00,'2026-04-10','Included in lunch-time cooler sales.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(3,1,4,'BEV-FIVEALIVE-1L','Beverages','Five Alive Pulpy Orange Juice',1500.00,4,6000.00,'2026-04-10','Breakfast basket sales from family shoppers.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(4,1,6,'GRO-RICE-MG-5KG','Groceries','Mama Gold Parboiled Rice',8900.00,2,17800.00,'2026-04-10','Two family restock purchases.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(5,1,11,'TOI-TP-COL-120G','Toiletries','Colgate MaxFresh Toothpaste',1650.00,3,4950.00,'2026-04-10','Toiletries shelf movement from repeat buyers.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(6,1,13,'TOI-SOAP-DETTOL-175G','Toiletries','Dettol Original Soap',850.00,6,5100.00,'2026-04-10','Soap sold strongly during evening rush.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(7,1,15,'HOU-DET-ARIEL-850G','Household Items','Ariel Ultra Clean Detergent',4200.00,1,4200.00,'2026-04-10','Single detergent basket add-on.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(8,1,18,'SNK-GALA-80G','Snacks & Confectionery','Gala Sausage Roll',400.00,6,2400.00,'2026-04-10','Counter snack movement during school pickup hours.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(9,1,21,'DBR-PEAK-400G','Dairy & Breakfast','Peak Full Cream Milk Powder',3800.00,2,7600.00,'2026-04-10','Breakfast essentials sale at checkout.',4,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(10,2,1,'BEV-COKE-50CL','Beverages','Coca-Cola Classic Soft Drink',650.00,10,6500.00,'2026-04-12','Evening cooler sales after office closing traffic.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(11,2,3,'BEV-PEPSI-50CL','Beverages','Pepsi Cola Soft Drink',650.00,6,3900.00,'2026-04-12','Alternative cola sales from walk-in customers.',2,'2026-04-13 18:36:09','2026-04-13 18:36:09',NULL,NULL),(12,2,5,'BEV-PURELIFE-75CL','Beverages','Nestle Pure Life Water',350.00,8,2800.00,'2026-04-12','Water sold steadily throughout the day.',2,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(13,2,9,'GRO-SUGAR-DAN-1KG','Groceries','Dangote Sugar',1800.00,4,7200.00,'2026-04-12','Sugar restocks for home shoppers.',2,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(14,2,11,'TOI-TP-COL-120G','Toiletries','Colgate MaxFresh Toothpaste',1650.00,4,6600.00,'2026-04-12','Additional toothpaste sales captured from the back shelf.',2,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(15,2,12,'TOI-ROLL-NIV-50ML','Toiletries','Nivea Men Roll-On',3200.00,2,6400.00,'2026-04-12','Personal care purchases recorded at closing.',2,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(16,2,16,'HOU-BLEACH-HYPO-1L','Household Items','Hypo Original Bleach',1300.00,5,6500.00,'2026-04-12','Cleaning products moved strongly before weekend prep.',2,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(17,3,1,'BEV-COKE-50CL','Beverages','Coca-Cola Classic Soft Drink',650.00,16,10400.00,'2026-04-13','Strong cooler movement before lunch.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(18,3,2,'BEV-FANTA-50CL','Beverages','Fanta Orange Soft Drink',650.00,16,10400.00,'2026-04-13','Orange soda moved with school-run traffic.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(19,3,5,'BEV-PURELIFE-75CL','Beverages','Nestle Pure Life Water',350.00,10,3500.00,'2026-04-13','Water stock sold out during hot afternoon demand.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(20,3,10,'GRO-SEMOVITA-1KG','Groceries','Semovita',1700.00,9,15300.00,'2026-04-13','Weekend meal prep shopping.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(21,3,13,'TOI-SOAP-DETTOL-175G','Toiletries','Dettol Original Soap',850.00,10,8500.00,'2026-04-13','Soap sales recorded from the front toiletries rack.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(22,3,14,'TOI-TB-ORALB-MED','Toiletries','Oral-B Medium Toothbrush',750.00,8,6000.00,'2026-04-13','Remaining toothbrush stock sold down quickly.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(23,3,18,'SNK-GALA-80G','Snacks & Confectionery','Gala Sausage Roll',400.00,14,5600.00,'2026-04-13','Counter snacks sold heavily during lunch period.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(24,3,21,'DBR-PEAK-400G','Dairy & Breakfast','Peak Full Cream Milk Powder',3800.00,7,26600.00,'2026-04-13','Milk tins moved with breakfast restocks.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(25,3,22,'DBR-MILO-400G','Dairy & Breakfast','Milo Refill',3200.00,6,19200.00,'2026-04-13','Milo refill purchases from repeat shoppers.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(26,3,23,'DBR-CORNFLAKES-500G','Dairy & Breakfast','Kellogg\'s Corn Flakes',4200.00,4,16800.00,'2026-04-13','Cereal sold in family breakfast baskets.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(27,3,25,'BABY-MOLFIX-MIDI-28S','Baby Care','Molfix Baby Diapers Midi',6200.00,3,18600.00,'2026-04-13','Diaper packs sold to regular family customers.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(28,3,19,'SNK-PRINGLES-165G','Snacks & Confectionery','Pringles Original',3500.00,5,17500.00,'2026-04-13','Premium snack tins sold during evening top-up shopping.',4,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(29,4,7,'GRO-SPAG-GP-500G','Groceries','Golden Penny Spaghetti',900.00,12,10800.00,'2026-04-13','Pasta sales from family dinner restocks.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(30,4,8,'GRO-OIL-DK-1L','Groceries','Devon King\'s Vegetable Oil',2400.00,5,12000.00,'2026-04-13','Cooking oil sold in weekday top-up baskets.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(31,4,3,'BEV-PEPSI-50CL','Beverages','Pepsi Cola Soft Drink',650.00,12,7800.00,'2026-04-13','Pepsi line sold down near closing time.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(32,4,12,'TOI-ROLL-NIV-50ML','Toiletries','Nivea Men Roll-On',3200.00,3,9600.00,'2026-04-13','Personal care purchases captured after shift change.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(33,4,17,'HOU-HARPIC-500ML','Household Items','Harpic Power Plus Toilet Cleaner',1850.00,4,7400.00,'2026-04-13','Bathroom cleaner sales from household restock baskets.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(34,4,16,'HOU-BLEACH-HYPO-1L','Household Items','Hypo Original Bleach',1300.00,7,9100.00,'2026-04-13','Bleach stock cleared out before end of day.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(35,4,20,'SNK-TOMTOM-40S','Snacks & Confectionery','Cadbury TomTom Rolls',800.00,6,4800.00,'2026-04-13','Checkout confectionery sales were strong.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(36,4,24,'BABY-CUSSONS-200ML','Baby Care','Cussons Baby Lotion',2800.00,2,5600.00,'2026-04-13','Baby lotion sold to repeat customers.',3,'2026-04-13 18:36:10','2026-04-13 18:36:10',NULL,NULL),(37,7,15,'HOU-DET-ARIEL-850G','Household Items','Ariel Ultra Clean Detergent',4200.00,5,21000.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,2),(38,7,20,'SNK-TOMTOM-40S','Snacks & Confectionery','Cadbury TomTom Rolls',800.00,3,2400.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,3),(39,7,1,'BEV-COKE-50CL','Beverages','Coca-Cola Classic Soft Drink',650.00,4,2600.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,4),(40,7,11,'TOI-TP-COL-120G','Toiletries','Colgate MaxFresh Toothpaste',1650.00,6,9900.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,5),(41,7,24,'BABY-CUSSONS-200ML','Baby Care','Cussons Baby Lotion',2800.00,2,5600.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,6),(42,7,9,'GRO-SUGAR-DAN-1KG','Groceries','Dangote Sugar',1800.00,1,1800.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,7),(43,7,13,'TOI-SOAP-DETTOL-175G','Toiletries','Dettol Original Soap',850.00,1,850.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,8),(44,7,8,'GRO-OIL-DK-1L','Groceries','Devon King\'s Vegetable Oil',2400.00,1,2400.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,9),(45,7,2,'BEV-FANTA-50CL','Beverages','Fanta Orange Soft Drink',650.00,1,650.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,10),(46,7,4,'BEV-FIVEALIVE-1L','Beverages','Five Alive Pulpy Orange Juice',1500.00,1,1500.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,11),(47,7,18,'SNK-GALA-80G','Snacks & Confectionery','Gala Sausage Roll',400.00,1,400.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,12),(48,7,7,'GRO-SPAG-GP-500G','Groceries','Golden Penny Spaghetti',900.00,1,900.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,13),(49,7,17,'HOU-HARPIC-500ML','Household Items','Harpic Power Plus Toilet Cleaner',1850.00,1,1850.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,14),(50,7,23,'DBR-CORNFLAKES-500G','Dairy & Breakfast','Kellogg\'s Corn Flakes',4200.00,1,4200.00,'2026-04-13',NULL,1,'2026-04-13 21:06:12','2026-04-13 21:06:12',NULL,16),(51,7,6,'GRO-RICE-MG-5KG','Groceries','Mama Gold Parboiled Rice',8900.00,1,8900.00,'2026-04-13',NULL,1,'2026-04-13 21:06:13','2026-04-13 21:06:13',NULL,17),(52,7,22,'DBR-MILO-400G','Dairy & Breakfast','Milo Refill',3200.00,1,3200.00,'2026-04-13',NULL,1,'2026-04-13 21:06:13','2026-04-13 21:06:13',NULL,18),(53,7,25,'BABY-MOLFIX-MIDI-28S','Baby Care','Molfix Baby Diapers Midi',6200.00,1,6200.00,'2026-04-13',NULL,1,'2026-04-13 21:06:13','2026-04-13 21:06:13',NULL,19),(54,7,12,'TOI-ROLL-NIV-50ML','Toiletries','Nivea Men Roll-On',3200.00,1,3200.00,'2026-04-13',NULL,1,'2026-04-13 21:06:13','2026-04-13 21:06:13',NULL,21),(55,7,14,'TOI-TB-ORALB-MED','Toiletries','Oral-B Medium Toothbrush',750.00,1,750.00,'2026-04-13',NULL,1,'2026-04-13 21:06:13','2026-04-13 21:06:13',NULL,22),(56,7,21,'DBR-PEAK-400G','Dairy & Breakfast','Peak Full Cream Milk Powder',3800.00,1,3800.00,'2026-04-13',NULL,1,'2026-04-13 21:06:14','2026-04-13 21:06:14',NULL,23),(57,7,3,'BEV-PEPSI-50CL','Beverages','Pepsi Cola Soft Drink',650.00,1,650.00,'2026-04-13',NULL,1,'2026-04-13 21:06:14','2026-04-13 21:06:14',NULL,24),(58,7,19,'SNK-PRINGLES-165G','Snacks & Confectionery','Pringles Original',3500.00,1,3500.00,'2026-04-13',NULL,1,'2026-04-13 21:06:14','2026-04-13 21:06:14',NULL,25),(59,7,10,'GRO-SEMOVITA-1KG','Groceries','Semovita',1700.00,1,1700.00,'2026-04-13',NULL,1,'2026-04-13 21:06:14','2026-04-13 21:06:14',NULL,26);
/*!40000 ALTER TABLE `sales_records` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `daily_sales_summaries` WRITE;
/*!40000 ALTER TABLE `daily_sales_summaries` DISABLE KEYS */;
INSERT INTO `daily_sales_summaries` VALUES (1,'2026-04-10',9,36,55850.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,'2026-04-12',7,39,39900.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(4,'2026-04-13',43,197,313450.00,3,'2026-04-13 21:06:17','2026-04-13 21:06:17');
/*!40000 ALTER TABLE `daily_sales_summaries` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `daily_product_sales_summaries` WRITE;
/*!40000 ALTER TABLE `daily_product_sales_summaries` DISABLE KEYS */;
INSERT INTO `daily_product_sales_summaries` VALUES (1,'2026-04-10',1,'BEV-COKE-50CL','Coca-Cola Classic Soft Drink',1,'Beverages',8,5200.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,'2026-04-10',2,'BEV-FANTA-50CL','Fanta Orange Soft Drink',1,'Beverages',4,2600.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(3,'2026-04-10',4,'BEV-FIVEALIVE-1L','Five Alive Pulpy Orange Juice',1,'Beverages',4,6000.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(4,'2026-04-10',6,'GRO-RICE-MG-5KG','Mama Gold Parboiled Rice',2,'Groceries',2,17800.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(5,'2026-04-10',11,'TOI-TP-COL-120G','Colgate MaxFresh Toothpaste',3,'Toiletries',3,4950.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(6,'2026-04-10',13,'TOI-SOAP-DETTOL-175G','Dettol Original Soap',3,'Toiletries',6,5100.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(7,'2026-04-10',15,'HOU-DET-ARIEL-850G','Ariel Ultra Clean Detergent',4,'Household Items',1,4200.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(8,'2026-04-10',18,'SNK-GALA-80G','Gala Sausage Roll',5,'Snacks & Confectionery',6,2400.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(9,'2026-04-10',21,'DBR-PEAK-400G','Peak Full Cream Milk Powder',6,'Dairy & Breakfast',2,7600.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(10,'2026-04-12',1,'BEV-COKE-50CL','Coca-Cola Classic Soft Drink',1,'Beverages',10,6500.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(11,'2026-04-12',3,'BEV-PEPSI-50CL','Pepsi Cola Soft Drink',1,'Beverages',6,3900.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(12,'2026-04-12',5,'BEV-PURELIFE-75CL','Nestle Pure Life Water',1,'Beverages',8,2800.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(13,'2026-04-12',9,'GRO-SUGAR-DAN-1KG','Dangote Sugar',2,'Groceries',4,7200.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(14,'2026-04-12',11,'TOI-TP-COL-120G','Colgate MaxFresh Toothpaste',3,'Toiletries',4,6600.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(15,'2026-04-12',12,'TOI-ROLL-NIV-50ML','Nivea Men Roll-On',3,'Toiletries',2,6400.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(16,'2026-04-12',16,'HOU-BLEACH-HYPO-1L','Hypo Original Bleach',4,'Household Items',5,6500.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(37,'2026-04-13',1,'BEV-COKE-50CL','Coca-Cola Classic Soft Drink',1,'Beverages',20,13000.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(38,'2026-04-13',2,'BEV-FANTA-50CL','Fanta Orange Soft Drink',1,'Beverages',17,11050.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(39,'2026-04-13',3,'BEV-PEPSI-50CL','Pepsi Cola Soft Drink',1,'Beverages',13,8450.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(40,'2026-04-13',4,'BEV-FIVEALIVE-1L','Five Alive Pulpy Orange Juice',1,'Beverages',1,1500.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(41,'2026-04-13',5,'BEV-PURELIFE-75CL','Nestle Pure Life Water',1,'Beverages',10,3500.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(42,'2026-04-13',6,'GRO-RICE-MG-5KG','Mama Gold Parboiled Rice',2,'Groceries',1,8900.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(43,'2026-04-13',7,'GRO-SPAG-GP-500G','Golden Penny Spaghetti',2,'Groceries',13,11700.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(44,'2026-04-13',8,'GRO-OIL-DK-1L','Devon King\'s Vegetable Oil',2,'Groceries',6,14400.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(45,'2026-04-13',9,'GRO-SUGAR-DAN-1KG','Dangote Sugar',2,'Groceries',1,1800.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(46,'2026-04-13',10,'GRO-SEMOVITA-1KG','Semovita',2,'Groceries',10,17000.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(47,'2026-04-13',11,'TOI-TP-COL-120G','Colgate MaxFresh Toothpaste',3,'Toiletries',6,9900.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(48,'2026-04-13',12,'TOI-ROLL-NIV-50ML','Nivea Men Roll-On',3,'Toiletries',4,12800.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(49,'2026-04-13',13,'TOI-SOAP-DETTOL-175G','Dettol Original Soap',3,'Toiletries',11,9350.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(50,'2026-04-13',14,'TOI-TB-ORALB-MED','Oral-B Medium Toothbrush',3,'Toiletries',9,6750.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(51,'2026-04-13',15,'HOU-DET-ARIEL-850G','Ariel Ultra Clean Detergent',4,'Household Items',5,21000.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(52,'2026-04-13',16,'HOU-BLEACH-HYPO-1L','Hypo Original Bleach',4,'Household Items',7,9100.00,1,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(53,'2026-04-13',17,'HOU-HARPIC-500ML','Harpic Power Plus Toilet Cleaner',4,'Household Items',5,9250.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(54,'2026-04-13',18,'SNK-GALA-80G','Gala Sausage Roll',5,'Snacks & Confectionery',15,6000.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(55,'2026-04-13',19,'SNK-PRINGLES-165G','Pringles Original',5,'Snacks & Confectionery',6,21000.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(56,'2026-04-13',20,'SNK-TOMTOM-40S','Cadbury TomTom Rolls',5,'Snacks & Confectionery',9,7200.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(57,'2026-04-13',21,'DBR-PEAK-400G','Peak Full Cream Milk Powder',6,'Dairy & Breakfast',8,30400.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(58,'2026-04-13',22,'DBR-MILO-400G','Milo Refill',6,'Dairy & Breakfast',7,22400.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(59,'2026-04-13',23,'DBR-CORNFLAKES-500G','Kellogg\'s Corn Flakes',6,'Dairy & Breakfast',5,21000.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(60,'2026-04-13',24,'BABY-CUSSONS-200ML','Cussons Baby Lotion',7,'Baby Care',4,11200.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(61,'2026-04-13',25,'BABY-MOLFIX-MIDI-28S','Molfix Baby Diapers Midi',7,'Baby Care',4,24800.00,2,'2026-04-13 21:06:18','2026-04-13 21:06:18');
/*!40000 ALTER TABLE `daily_product_sales_summaries` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `daily_category_sales_summaries` WRITE;
/*!40000 ALTER TABLE `daily_category_sales_summaries` DISABLE KEYS */;
INSERT INTO `daily_category_sales_summaries` VALUES (1,'2026-04-10',1,'Beverages',16,13800.00,3,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,'2026-04-10',6,'Dairy & Breakfast',2,7600.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(3,'2026-04-10',2,'Groceries',2,17800.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(4,'2026-04-10',4,'Household Items',1,4200.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(5,'2026-04-10',5,'Snacks & Confectionery',6,2400.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(6,'2026-04-10',3,'Toiletries',9,10050.00,2,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(7,'2026-04-12',1,'Beverages',24,13200.00,3,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(8,'2026-04-12',2,'Groceries',4,7200.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(9,'2026-04-12',4,'Household Items',5,6500.00,1,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(10,'2026-04-12',3,'Toiletries',6,13000.00,2,'2026-04-13 18:36:10','2026-04-13 18:36:10'),(18,'2026-04-13',7,'Baby Care',8,36000.00,4,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(19,'2026-04-13',1,'Beverages',61,37500.00,8,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(20,'2026-04-13',6,'Dairy & Breakfast',20,73800.00,6,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(21,'2026-04-13',2,'Groceries',31,53800.00,8,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(22,'2026-04-13',4,'Household Items',17,39350.00,4,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(23,'2026-04-13',5,'Snacks & Confectionery',30,34200.00,6,'2026-04-13 21:06:18','2026-04-13 21:06:18'),(24,'2026-04-13',3,'Toiletries',30,38800.00,7,'2026-04-13 21:06:18','2026-04-13 21:06:18');
/*!40000 ALTER TABLE `daily_category_sales_summaries` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'Akin Joseph Supermarket','Africa/Lagos','NGN','akinjoseph221@gmail.com','Thank you for shopping with Akin Joseph Supermarket.','2026-04-13 18:36:08','2026-04-13 18:36:08');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'system_settings.seeded','Local demo system settings were prepared.','App\\Models\\SystemSetting',1,'{\"business_name\": \"Akin Joseph Supermarket\", \"currency_code\": \"NGN\", \"business_timezone\": \"Africa/Lagos\"}','2026-04-13 18:36:08'),(2,4,'stock_entry.created','Stock was added to Coca-Cola Classic Soft Drink.','App\\Models\\StockEntry',1,'{\"new_stock\": 36, \"reference\": \"DEMO-STOCK-BEV-COKE-50CL-01\", \"product_id\": 1, \"product_name\": \"Coca-Cola Classic Soft Drink\", \"previous_stock\": 0, \"quantity_added\": 36}','2026-04-13 18:36:09'),(3,3,'stock_entry.created','Stock was added to Coca-Cola Classic Soft Drink.','App\\Models\\StockEntry',2,'{\"new_stock\": 60, \"reference\": \"DEMO-STOCK-BEV-COKE-50CL-02\", \"product_id\": 1, \"product_name\": \"Coca-Cola Classic Soft Drink\", \"previous_stock\": 36, \"quantity_added\": 24}','2026-04-13 18:36:09'),(4,4,'stock_entry.created','Stock was added to Fanta Orange Soft Drink.','App\\Models\\StockEntry',3,'{\"new_stock\": 30, \"reference\": \"DEMO-STOCK-BEV-FANTA-50CL-01\", \"product_id\": 2, \"product_name\": \"Fanta Orange Soft Drink\", \"previous_stock\": 0, \"quantity_added\": 30}','2026-04-13 18:36:09'),(5,3,'stock_entry.created','Stock was added to Fanta Orange Soft Drink.','App\\Models\\StockEntry',4,'{\"new_stock\": 48, \"reference\": \"DEMO-STOCK-BEV-FANTA-50CL-02\", \"product_id\": 2, \"product_name\": \"Fanta Orange Soft Drink\", \"previous_stock\": 30, \"quantity_added\": 18}','2026-04-13 18:36:09'),(6,4,'stock_entry.created','Stock was added to Pepsi Cola Soft Drink.','App\\Models\\StockEntry',5,'{\"new_stock\": 24, \"reference\": \"DEMO-STOCK-BEV-PEPSI-50CL-01\", \"product_id\": 3, \"product_name\": \"Pepsi Cola Soft Drink\", \"previous_stock\": 0, \"quantity_added\": 24}','2026-04-13 18:36:09'),(7,3,'stock_entry.created','Stock was added to Pepsi Cola Soft Drink.','App\\Models\\StockEntry',6,'{\"new_stock\": 36, \"reference\": \"DEMO-STOCK-BEV-PEPSI-50CL-02\", \"product_id\": 3, \"product_name\": \"Pepsi Cola Soft Drink\", \"previous_stock\": 24, \"quantity_added\": 12}','2026-04-13 18:36:09'),(8,2,'stock_entry.created','Stock was added to Five Alive Pulpy Orange Juice.','App\\Models\\StockEntry',7,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-BEV-FIVEALIVE-1L-01\", \"product_id\": 4, \"product_name\": \"Five Alive Pulpy Orange Juice\", \"previous_stock\": 0, \"quantity_added\": 12}','2026-04-13 18:36:09'),(9,3,'stock_entry.created','Stock was added to Five Alive Pulpy Orange Juice.','App\\Models\\StockEntry',8,'{\"new_stock\": 20, \"reference\": \"DEMO-STOCK-BEV-FIVEALIVE-1L-02\", \"product_id\": 4, \"product_name\": \"Five Alive Pulpy Orange Juice\", \"previous_stock\": 12, \"quantity_added\": 8}','2026-04-13 18:36:09'),(10,4,'stock_entry.created','Stock was added to Nestle Pure Life Water.','App\\Models\\StockEntry',9,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-BEV-PURELIFE-75CL-01\", \"product_id\": 5, \"product_name\": \"Nestle Pure Life Water\", \"previous_stock\": 0, \"quantity_added\": 12}','2026-04-13 18:36:09'),(11,3,'stock_entry.created','Stock was added to Nestle Pure Life Water.','App\\Models\\StockEntry',10,'{\"new_stock\": 18, \"reference\": \"DEMO-STOCK-BEV-PURELIFE-75CL-02\", \"product_id\": 5, \"product_name\": \"Nestle Pure Life Water\", \"previous_stock\": 12, \"quantity_added\": 6}','2026-04-13 18:36:09'),(12,2,'stock_entry.created','Stock was added to Mama Gold Parboiled Rice.','App\\Models\\StockEntry',11,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-GRO-RICE-MG-5KG-01\", \"product_id\": 6, \"product_name\": \"Mama Gold Parboiled Rice\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(13,3,'stock_entry.created','Stock was added to Mama Gold Parboiled Rice.','App\\Models\\StockEntry',12,'{\"new_stock\": 14, \"reference\": \"DEMO-STOCK-GRO-RICE-MG-5KG-02\", \"product_id\": 6, \"product_name\": \"Mama Gold Parboiled Rice\", \"previous_stock\": 8, \"quantity_added\": 6}','2026-04-13 18:36:09'),(14,2,'stock_entry.created','Stock was added to Golden Penny Spaghetti.','App\\Models\\StockEntry',13,'{\"new_stock\": 18, \"reference\": \"DEMO-STOCK-GRO-SPAG-GP-500G-01\", \"product_id\": 7, \"product_name\": \"Golden Penny Spaghetti\", \"previous_stock\": 0, \"quantity_added\": 18}','2026-04-13 18:36:09'),(15,3,'stock_entry.created','Stock was added to Golden Penny Spaghetti.','App\\Models\\StockEntry',14,'{\"new_stock\": 30, \"reference\": \"DEMO-STOCK-GRO-SPAG-GP-500G-02\", \"product_id\": 7, \"product_name\": \"Golden Penny Spaghetti\", \"previous_stock\": 18, \"quantity_added\": 12}','2026-04-13 18:36:09'),(16,2,'stock_entry.created','Stock was added to Devon King\'s Vegetable Oil.','App\\Models\\StockEntry',15,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-GRO-OIL-DK-1L-01\", \"product_id\": 8, \"product_name\": \"Devon King\'s Vegetable Oil\", \"previous_stock\": 0, \"quantity_added\": 10}','2026-04-13 18:36:09'),(17,3,'stock_entry.created','Stock was added to Devon King\'s Vegetable Oil.','App\\Models\\StockEntry',16,'{\"new_stock\": 16, \"reference\": \"DEMO-STOCK-GRO-OIL-DK-1L-02\", \"product_id\": 8, \"product_name\": \"Devon King\'s Vegetable Oil\", \"previous_stock\": 10, \"quantity_added\": 6}','2026-04-13 18:36:09'),(18,2,'stock_entry.created','Stock was added to Dangote Sugar.','App\\Models\\StockEntry',17,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-GRO-SUGAR-DAN-1KG-01\", \"product_id\": 9, \"product_name\": \"Dangote Sugar\", \"previous_stock\": 0, \"quantity_added\": 12}','2026-04-13 18:36:09'),(19,3,'stock_entry.created','Stock was added to Dangote Sugar.','App\\Models\\StockEntry',18,'{\"new_stock\": 20, \"reference\": \"DEMO-STOCK-GRO-SUGAR-DAN-1KG-02\", \"product_id\": 9, \"product_name\": \"Dangote Sugar\", \"previous_stock\": 12, \"quantity_added\": 8}','2026-04-13 18:36:09'),(20,2,'stock_entry.created','Stock was added to Semovita.','App\\Models\\StockEntry',19,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-GRO-SEMOVITA-1KG-01\", \"product_id\": 10, \"product_name\": \"Semovita\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(21,3,'stock_entry.created','Stock was added to Semovita.','App\\Models\\StockEntry',20,'{\"new_stock\": 14, \"reference\": \"DEMO-STOCK-GRO-SEMOVITA-1KG-02\", \"product_id\": 10, \"product_name\": \"Semovita\", \"previous_stock\": 8, \"quantity_added\": 6}','2026-04-13 18:36:09'),(22,4,'stock_entry.created','Stock was added to Colgate MaxFresh Toothpaste.','App\\Models\\StockEntry',21,'{\"new_stock\": 16, \"reference\": \"DEMO-STOCK-TOI-TP-COL-120G-01\", \"product_id\": 11, \"product_name\": \"Colgate MaxFresh Toothpaste\", \"previous_stock\": 0, \"quantity_added\": 16}','2026-04-13 18:36:09'),(23,3,'stock_entry.created','Stock was added to Colgate MaxFresh Toothpaste.','App\\Models\\StockEntry',22,'{\"new_stock\": 24, \"reference\": \"DEMO-STOCK-TOI-TP-COL-120G-02\", \"product_id\": 11, \"product_name\": \"Colgate MaxFresh Toothpaste\", \"previous_stock\": 16, \"quantity_added\": 8}','2026-04-13 18:36:09'),(24,4,'stock_entry.created','Stock was added to Nivea Men Roll-On.','App\\Models\\StockEntry',23,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-TOI-ROLL-NIV-50ML-01\", \"product_id\": 12, \"product_name\": \"Nivea Men Roll-On\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(25,3,'stock_entry.created','Stock was added to Nivea Men Roll-On.','App\\Models\\StockEntry',24,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-TOI-ROLL-NIV-50ML-02\", \"product_id\": 12, \"product_name\": \"Nivea Men Roll-On\", \"previous_stock\": 8, \"quantity_added\": 4}','2026-04-13 18:36:09'),(26,4,'stock_entry.created','Stock was added to Dettol Original Soap.','App\\Models\\StockEntry',25,'{\"new_stock\": 20, \"reference\": \"DEMO-STOCK-TOI-SOAP-DETTOL-175G-01\", \"product_id\": 13, \"product_name\": \"Dettol Original Soap\", \"previous_stock\": 0, \"quantity_added\": 20}','2026-04-13 18:36:09'),(27,3,'stock_entry.created','Stock was added to Dettol Original Soap.','App\\Models\\StockEntry',26,'{\"new_stock\": 30, \"reference\": \"DEMO-STOCK-TOI-SOAP-DETTOL-175G-02\", \"product_id\": 13, \"product_name\": \"Dettol Original Soap\", \"previous_stock\": 20, \"quantity_added\": 10}','2026-04-13 18:36:09'),(28,4,'stock_entry.created','Stock was added to Oral-B Medium Toothbrush.','App\\Models\\StockEntry',27,'{\"new_stock\": 6, \"reference\": \"DEMO-STOCK-TOI-TB-ORALB-MED-01\", \"product_id\": 14, \"product_name\": \"Oral-B Medium Toothbrush\", \"previous_stock\": 0, \"quantity_added\": 6}','2026-04-13 18:36:09'),(29,3,'stock_entry.created','Stock was added to Oral-B Medium Toothbrush.','App\\Models\\StockEntry',28,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-TOI-TB-ORALB-MED-02\", \"product_id\": 14, \"product_name\": \"Oral-B Medium Toothbrush\", \"previous_stock\": 6, \"quantity_added\": 4}','2026-04-13 18:36:09'),(30,2,'stock_entry.created','Stock was added to Ariel Ultra Clean Detergent.','App\\Models\\StockEntry',29,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-HOU-DET-ARIEL-850G-01\", \"product_id\": 15, \"product_name\": \"Ariel Ultra Clean Detergent\", \"previous_stock\": 0, \"quantity_added\": 10}','2026-04-13 18:36:09'),(31,3,'stock_entry.created','Stock was added to Ariel Ultra Clean Detergent.','App\\Models\\StockEntry',30,'{\"new_stock\": 16, \"reference\": \"DEMO-STOCK-HOU-DET-ARIEL-850G-02\", \"product_id\": 15, \"product_name\": \"Ariel Ultra Clean Detergent\", \"previous_stock\": 10, \"quantity_added\": 6}','2026-04-13 18:36:09'),(32,2,'stock_entry.created','Stock was added to Hypo Original Bleach.','App\\Models\\StockEntry',31,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-HOU-BLEACH-HYPO-1L-01\", \"product_id\": 16, \"product_name\": \"Hypo Original Bleach\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(33,3,'stock_entry.created','Stock was added to Hypo Original Bleach.','App\\Models\\StockEntry',32,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-HOU-BLEACH-HYPO-1L-02\", \"product_id\": 16, \"product_name\": \"Hypo Original Bleach\", \"previous_stock\": 8, \"quantity_added\": 4}','2026-04-13 18:36:09'),(34,2,'stock_entry.created','Stock was added to Harpic Power Plus Toilet Cleaner.','App\\Models\\StockEntry',33,'{\"new_stock\": 6, \"reference\": \"DEMO-STOCK-HOU-HARPIC-500ML-01\", \"product_id\": 17, \"product_name\": \"Harpic Power Plus Toilet Cleaner\", \"previous_stock\": 0, \"quantity_added\": 6}','2026-04-13 18:36:09'),(35,3,'stock_entry.created','Stock was added to Harpic Power Plus Toilet Cleaner.','App\\Models\\StockEntry',34,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-HOU-HARPIC-500ML-02\", \"product_id\": 17, \"product_name\": \"Harpic Power Plus Toilet Cleaner\", \"previous_stock\": 6, \"quantity_added\": 4}','2026-04-13 18:36:09'),(36,4,'stock_entry.created','Stock was added to Gala Sausage Roll.','App\\Models\\StockEntry',35,'{\"new_stock\": 16, \"reference\": \"DEMO-STOCK-SNK-GALA-80G-01\", \"product_id\": 18, \"product_name\": \"Gala Sausage Roll\", \"previous_stock\": 0, \"quantity_added\": 16}','2026-04-13 18:36:09'),(37,3,'stock_entry.created','Stock was added to Gala Sausage Roll.','App\\Models\\StockEntry',36,'{\"new_stock\": 24, \"reference\": \"DEMO-STOCK-SNK-GALA-80G-02\", \"product_id\": 18, \"product_name\": \"Gala Sausage Roll\", \"previous_stock\": 16, \"quantity_added\": 8}','2026-04-13 18:36:09'),(38,2,'stock_entry.created','Stock was added to Pringles Original.','App\\Models\\StockEntry',37,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-SNK-PRINGLES-165G-01\", \"product_id\": 19, \"product_name\": \"Pringles Original\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(39,3,'stock_entry.created','Stock was added to Pringles Original.','App\\Models\\StockEntry',38,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-SNK-PRINGLES-165G-02\", \"product_id\": 19, \"product_name\": \"Pringles Original\", \"previous_stock\": 8, \"quantity_added\": 4}','2026-04-13 18:36:09'),(40,4,'stock_entry.created','Stock was added to Cadbury TomTom Rolls.','App\\Models\\StockEntry',39,'{\"new_stock\": 12, \"reference\": \"DEMO-STOCK-SNK-TOMTOM-40S-01\", \"product_id\": 20, \"product_name\": \"Cadbury TomTom Rolls\", \"previous_stock\": 0, \"quantity_added\": 12}','2026-04-13 18:36:09'),(41,3,'stock_entry.created','Stock was added to Cadbury TomTom Rolls.','App\\Models\\StockEntry',40,'{\"new_stock\": 20, \"reference\": \"DEMO-STOCK-SNK-TOMTOM-40S-02\", \"product_id\": 20, \"product_name\": \"Cadbury TomTom Rolls\", \"previous_stock\": 12, \"quantity_added\": 8}','2026-04-13 18:36:09'),(42,2,'stock_entry.created','Stock was added to Peak Full Cream Milk Powder.','App\\Models\\StockEntry',41,'{\"new_stock\": 6, \"reference\": \"DEMO-STOCK-DBR-PEAK-400G-01\", \"product_id\": 21, \"product_name\": \"Peak Full Cream Milk Powder\", \"previous_stock\": 0, \"quantity_added\": 6}','2026-04-13 18:36:09'),(43,3,'stock_entry.created','Stock was added to Peak Full Cream Milk Powder.','App\\Models\\StockEntry',42,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-DBR-PEAK-400G-02\", \"product_id\": 21, \"product_name\": \"Peak Full Cream Milk Powder\", \"previous_stock\": 6, \"quantity_added\": 4}','2026-04-13 18:36:09'),(44,2,'stock_entry.created','Stock was added to Milo Refill.','App\\Models\\StockEntry',43,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-DBR-MILO-400G-01\", \"product_id\": 22, \"product_name\": \"Milo Refill\", \"previous_stock\": 0, \"quantity_added\": 8}','2026-04-13 18:36:09'),(45,3,'stock_entry.created','Stock was added to Milo Refill.','App\\Models\\StockEntry',44,'{\"new_stock\": 14, \"reference\": \"DEMO-STOCK-DBR-MILO-400G-02\", \"product_id\": 22, \"product_name\": \"Milo Refill\", \"previous_stock\": 8, \"quantity_added\": 6}','2026-04-13 18:36:09'),(46,2,'stock_entry.created','Stock was added to Kellogg\'s Corn Flakes.','App\\Models\\StockEntry',45,'{\"new_stock\": 5, \"reference\": \"DEMO-STOCK-DBR-CORNFLAKES-500G-01\", \"product_id\": 23, \"product_name\": \"Kellogg\'s Corn Flakes\", \"previous_stock\": 0, \"quantity_added\": 5}','2026-04-13 18:36:09'),(47,3,'stock_entry.created','Stock was added to Kellogg\'s Corn Flakes.','App\\Models\\StockEntry',46,'{\"new_stock\": 9, \"reference\": \"DEMO-STOCK-DBR-CORNFLAKES-500G-02\", \"product_id\": 23, \"product_name\": \"Kellogg\'s Corn Flakes\", \"previous_stock\": 5, \"quantity_added\": 4}','2026-04-13 18:36:09'),(48,2,'stock_entry.created','Stock was added to Cussons Baby Lotion.','App\\Models\\StockEntry',47,'{\"new_stock\": 5, \"reference\": \"DEMO-STOCK-BABY-CUSSONS-200ML-01\", \"product_id\": 24, \"product_name\": \"Cussons Baby Lotion\", \"previous_stock\": 0, \"quantity_added\": 5}','2026-04-13 18:36:09'),(49,3,'stock_entry.created','Stock was added to Cussons Baby Lotion.','App\\Models\\StockEntry',48,'{\"new_stock\": 8, \"reference\": \"DEMO-STOCK-BABY-CUSSONS-200ML-02\", \"product_id\": 24, \"product_name\": \"Cussons Baby Lotion\", \"previous_stock\": 5, \"quantity_added\": 3}','2026-04-13 18:36:09'),(50,2,'stock_entry.created','Stock was added to Molfix Baby Diapers Midi.','App\\Models\\StockEntry',49,'{\"new_stock\": 6, \"reference\": \"DEMO-STOCK-BABY-MOLFIX-MIDI-28S-01\", \"product_id\": 25, \"product_name\": \"Molfix Baby Diapers Midi\", \"previous_stock\": 0, \"quantity_added\": 6}','2026-04-13 18:36:09'),(51,3,'stock_entry.created','Stock was added to Molfix Baby Diapers Midi.','App\\Models\\StockEntry',50,'{\"new_stock\": 10, \"reference\": \"DEMO-STOCK-BABY-MOLFIX-MIDI-28S-02\", \"product_id\": 25, \"product_name\": \"Molfix Baby Diapers Midi\", \"previous_stock\": 6, \"quantity_added\": 4}','2026-04-13 18:36:09'),(52,3,'stock_adjustment.created','Stock was adjusted for Coca-Cola Classic Soft Drink.','App\\Models\\StockAdjustment',1,'{\"reason\": \"Damaged units removed after shelf inspection.\", \"new_stock\": 24, \"reference\": \"ADJ-DEMO-DAMAGE-001\", \"product_id\": 1, \"product_name\": \"Coca-Cola Classic Soft Drink\", \"counted_stock\": null, \"previous_stock\": 26, \"quantity_change\": -2}','2026-04-13 18:36:10'),(53,3,'stock_adjustment.created','Stock was adjusted for Fanta Orange Soft Drink.','App\\Models\\StockAdjustment',2,'{\"reason\": \"Physical stock count reconciliation.\", \"new_stock\": 27, \"reference\": \"ADJ-DEMO-COUNT-001\", \"product_id\": 2, \"product_name\": \"Fanta Orange Soft Drink\", \"counted_stock\": 27, \"previous_stock\": 28, \"quantity_change\": -1}','2026-04-13 18:36:10'),(54,1,'backup.created','A recovery backup snapshot was created.','App\\Models\\BackupRun',1,'{\"file_path\": \"backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-183610.json\", \"backup_code\": \"BKP-20260413-183610-OFSH\", \"file_size_bytes\": 150407}','2026-04-13 18:36:10'),(55,1,'backup.created','A recovery backup snapshot was created.','App\\Models\\BackupRun',2,'{\"file_path\": \"backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-184129.json\", \"backup_code\": \"BKP-20260413-184129-2JWM\", \"file_size_bytes\": 150953}','2026-04-13 18:41:29'),(56,1,'backup.created','A recovery backup snapshot was created.','App\\Models\\BackupRun',3,'{\"file_path\": \"backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-205301.json\", \"backup_code\": \"BKP-20260413-205301-UZBH\", \"file_size_bytes\": 151499}','2026-04-13 20:53:02'),(57,1,'sales_import_batch.uploaded','A sales workbook was uploaded for processing.','App\\Models\\SalesImportBatch',7,'{\"file_hash\": \"c575568b42b079f21597398afa981bccc8cd84efe147c9f775abd699db6111b3\", \"batch_code\": \"SIB-20260413-210611-C5NF\", \"original_file_name\": \"daily-sales-template-2026-04-13.xlsx\"}','2026-04-13 21:06:11'),(58,1,'sales_import_batch.processed','Sales import batch SIB-20260413-210611-C5NF finished with status processed with failures.','App\\Models\\SalesImportBatch',7,'{\"status\": \"processed_with_failures\", \"batch_code\": \"SIB-20260413-210611-C5NF\", \"total_rows\": 25, \"failed_rows\": 2, \"successful_rows\": 23, \"total_sales_amount\": 87950, \"total_quantity_sold\": 38}','2026-04-13 21:06:17'),(59,1,'user.created','A new user account was created.','App\\Models\\User',5,'{\"role\": \"admin\", \"email\": \"ajewole@example.com\", \"user_id\": 5}','2026-04-13 21:18:36'),(60,1,'user.created','A new user account was created.','App\\Models\\User',6,'{\"role\": \"admin\", \"email\": \"aje@example.com\", \"user_id\": 6}','2026-04-13 21:20:48');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `backup_runs` WRITE;
/*!40000 ALTER TABLE `backup_runs` DISABLE KEYS */;
INSERT INTO `backup_runs` VALUES (1,'BKP-20260413-183610-OFSH','local','akin-joseph-supermarket-backup-2026-04-13-183610.json','backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-183610.json','completed',150407,'e8eaf15a517fd67740dbbba2fc2f00261e7d227d872bbc2c953008447c39113b','Local demo seed recovery snapshot',1,'2026-04-13 18:36:10','2026-04-13 18:36:10','2026-04-13 18:36:10','2026-04-13 18:36:10'),(2,'BKP-20260413-184129-2JWM','local','akin-joseph-supermarket-backup-2026-04-13-184129.json','backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-184129.json','completed',150953,'d0d0b995d43d68aac27a5640c88f3fae59b806d26f5692e8b078d5fbc5f1a54d',NULL,1,'2026-04-13 18:41:29','2026-04-13 18:41:29','2026-04-13 18:41:29','2026-04-13 18:41:29'),(3,'BKP-20260413-205301-UZBH','local','akin-joseph-supermarket-backup-2026-04-13-205301.json','backups/2026/04/akin-joseph-supermarket-backup-2026-04-13-205301.json','completed',151499,'80223e30a2b7f1f30168d18a1b935d413bb8afdc4a87498ab4fe4de9208a9963',NULL,1,'2026-04-13 20:53:01','2026-04-13 20:53:02','2026-04-13 20:53:01','2026-04-13 20:53:02');
/*!40000 ALTER TABLE `backup_runs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_08_14_170933_add_two_factor_columns_to_users_table',1),(5,'2026_04_09_232426_create_permission_tables',1),(6,'2026_04_10_000001_create_categories_table',1),(7,'2026_04_10_000002_create_products_table',1),(8,'2026_04_10_000003_create_stock_entries_table',1),(9,'2026_04_10_005807_create_sales_import_batches_table',1),(10,'2026_04_10_005810_create_sales_records_table',1),(11,'2026_04_10_005813_create_sales_import_failures_table',1),(12,'2026_04_10_100001_create_daily_sales_summaries_table',1),(13,'2026_04_10_100002_create_daily_product_sales_summaries_table',1),(14,'2026_04_10_100003_create_daily_category_sales_summaries_table',1),(15,'2026_04_10_100004_update_daily_category_sales_summaries_uniqueness',1),(16,'2026_04_11_000001_add_sales_time_and_source_row_number_to_sales_records_table',1),(17,'2026_04_13_000001_create_stock_adjustments_table',1),(18,'2026_04_13_000002_create_activity_logs_table',1),(19,'2026_04_13_000003_create_system_settings_table',1),(20,'2026_04_13_000004_create_backup_runs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

