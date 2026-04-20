
CREATE DATABASE IF NOT EXISTS `hotel_dashboard` ;
USE `hotel_dashboard`;


CREATE TABLE IF NOT EXISTS `action_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actionable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actionable_id` bigint(20) unsigned NOT NULL,
  `target_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `fields` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `exception` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `original` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  KEY `action_events_actionable_type_actionable_id_index` (`actionable_type`,`actionable_id`),
  KEY `action_events_batch_id_model_type_model_id_index` (`batch_id`,`model_type`,`model_id`),
  KEY `action_events_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9527 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `action_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `team_id` bigint(20) unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `action_types_team_id_foreign` (`team_id`),
  CONSTRAINT `action_types_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) NOT NULL,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `activity_log_team_id_index` (`team_id`),
  KEY `activity_log_subject_id_index` (`subject_id`),
  KEY `activity_log_subject_type_index` (`subject_type`),
  KEY `activity_log_causer_id_index` (`causer_id`),
  KEY `activity_log_causer_type_index` (`causer_type`),
  KEY `activity_log_created_at_index` (`created_at`),
  KEY `activity_log_updated_at_index` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `announcements` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `transient` tinyint(4) NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_tokens_token_unique` (`token`),
  KEY `api_tokens_user_id_expires_at_index` (`user_id`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `auto_numbers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `auto_renew_reservations_job_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `original_team_day_end` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `exception_message` text COLLATE utf8mb4_unicode_ci,
  `exception_trace` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auto_renew_reservations_job_logs_team_id_index` (`team_id`),
  KEY `auto_renew_reservations_job_logs_reservation_id_index` (`reservation_id`),
  KEY `auto_renew_reservations_job_logs_original_team_day_end_index` (`original_team_day_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `banks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('individual','organization') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'organization',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag_ar` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banks_team_id_foreign` (`team_id`),
  KEY `banks_created_by_foreign` (`created_by`),
  CONSTRAINT `banks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banks_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `blocked_guests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `blockable_id` bigint(20) unsigned NOT NULL,
  `blockable_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blocked_guests_team_id_foreign` (`team_id`),
  KEY `blocked_guests_created_by_foreign` (`created_by`),
  CONSTRAINT `blocked_guests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blocked_guests_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `breakfast_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `breakfast_prices_team_id_index` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `business_date_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `business_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bdt_transaction` (`transaction_id`),
  KEY `idx_bdt_team_date` (`team_id`,`business_date`),
  KEY `business_date_transactions_team_id_index` (`team_id`),
  KEY `business_date_transactions_transaction_id_index` (`transaction_id`),
  KEY `business_date_transactions_business_date_index` (`business_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `cache_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `cashier_shifts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'Cashier ID',
  `shift_date` date NOT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `closing_balance` decimal(12,2) DEFAULT NULL,
  `system_balance` decimal(12,2) DEFAULT NULL COMMENT 'Calculated from transactions',
  `variance` decimal(12,2) DEFAULT NULL COMMENT 'Closing - System balance',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('open','closed','approved') COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashier_shifts_team_id_foreign` (`team_id`),
  CONSTRAINT `cashier_shifts_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `checkout_balance_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `transfer_type` enum('to_promissory','to_credit_note','refunded','waived') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `promissory_id` bigint(20) unsigned DEFAULT NULL,
  `refund_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transferred_by` bigint(20) unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `checkout_balance_transfers_reservation_id_foreign` (`reservation_id`),
  KEY `checkout_balance_transfers_team_id_foreign` (`team_id`),
  CONSTRAINT `checkout_balance_transfers_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `checkout_balance_transfers_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `country_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `commenter_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commenter_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentable_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `child_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_commenter_id_commenter_type_index` (`commenter_id`,`commenter_type`),
  KEY `comments_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`),
  KEY `comments_child_id_foreign` (`child_id`),
  KEY `comments_team_id_foreign` (`team_id`),
  CONSTRAINT `comments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25736 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `commission_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `source_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `period_from` date NOT NULL,
  `period_to` date NOT NULL,
  `room_revenue_base` decimal(12,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commission_payments_source_id_foreign` (`source_id`),
  KEY `commission_payments_reservation_id_foreign` (`reservation_id`),
  KEY `commission_payments_team_id_foreign` (`team_id`),
  CONSTRAINT `commission_payments_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commission_payments_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_payments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `entity_type` enum('company','individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'company',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_incharge_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_incharge_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `company_group_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to company_groups.id. Links hotel-level record to master corporate account.',
  `payment_terms_days` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'Options: 0,30,45,60,90,180. Default 0 = payment due immediately.',
  `credit_limit` decimal(12,2) DEFAULT NULL COMMENT 'Hotel-level credit limit. NULL = no limit set.',
  PRIMARY KEY (`id`),
  KEY `companies_team_id_index` (`team_id`),
  KEY `companies_user_id_index` (`user_id`),
  KEY `companies_name_index` (`name`),
  KEY `companies_phone_index` (`phone`),
  KEY `companies_email_index` (`email`),
  KEY `companies_city_index` (`city`),
  KEY `companies_person_incharge_name_index` (`person_incharge_name`),
  KEY `companies_tax_number_index` (`tax_number`),
  KEY `companies_created_at_index` (`created_at`),
  KEY `companies_updated_at_index` (`updated_at`),
  KEY `companies_deleted_at_index` (`deleted_at`),
  KEY `companies_entity_type_index` (`entity_type`),
  KEY `companies_country_id_foreign` (`country_id`),
  CONSTRAINT `companies_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1952 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `company_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Corporate account name EN',
  `name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Corporate account name AR',
  `tax_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VAT number for auto-matching',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_limit` decimal(14,2) DEFAULT NULL COMMENT 'Group-level limit across all hotels',
  `payment_terms_days` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '0,30,45,60,90,180 days',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `company_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_notes_company_id_index` (`company_id`),
  KEY `company_notes_team_id_index` (`team_id`),
  KEY `company_notes_created_by_index` (`created_by`),
  KEY `company_notes_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `countries` (
  `code` bigint(20) unsigned NOT NULL,
  `is_gcc` tinyint(1) NOT NULL DEFAULT '0',
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `iso3` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `countries_code_unique` (`code`),
  KEY `countries_iso3_index` (`iso3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_self_registered` tinyint(1) NOT NULL DEFAULT '0',
  `token` text COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_expire_date` date DEFAULT NULL,
  `birthday_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` bigint(20) unsigned DEFAULT NULL,
  `work` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_id` bigint(20) unsigned DEFAULT NULL,
  `customer_type` bigint(20) unsigned DEFAULT NULL,
  `highlight_id` bigint(20) unsigned DEFAULT NULL,
  `coming_away` bigint(20) unsigned DEFAULT NULL,
  `id_serial_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_category_type` enum('Normal','VIP','Member','Corporate') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `id_number` (`id_number`),
  KEY `country_id` (`country_id`),
  KEY `type_id` (`type_id`,`id_type`),
  KEY `customer_type` (`customer_type`,`phone`),
  KEY `country_id_2` (`country_id`),
  KEY `highlight_id` (`highlight_id`),
  KEY `phone` (`phone`),
  KEY `customer_is_self_registered_index` (`is_self_registered`)
) ENGINE=InnoDB AUTO_INCREMENT=790383 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


CREATE TABLE IF NOT EXISTS `customer_guest_reservation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `guest_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `customer_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('customer','pos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `qoyod_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_qoyods_team_id_index` (`team_id`),
  KEY `customer_qoyods_customer_id_index` (`customer_id`),
  KEY `customer_qoyods_qoyod_id_index` (`qoyod_id`),
  KEY `customer_qoyods_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `daily_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `rented_units` bigint(20) unsigned NOT NULL DEFAULT '0',
  `vacant_units` bigint(20) unsigned NOT NULL DEFAULT '0',
  `occupancy_ratio` double NOT NULL DEFAULT '0',
  `new_reservations` bigint(20) unsigned NOT NULL DEFAULT '0',
  `still_reservations` bigint(20) unsigned NOT NULL DEFAULT '0',
  `checked_out_reservations` bigint(20) unsigned NOT NULL DEFAULT '0',
  `general_receipts` double NOT NULL DEFAULT '0',
  `security_deposits` double NOT NULL DEFAULT '0',
  `total_payment` double NOT NULL DEFAULT '0',
  `cash_only` double NOT NULL DEFAULT '0',
  `mada_only` double NOT NULL DEFAULT '0',
  `bank_transfer` double NOT NULL DEFAULT '0',
  `credit_card` double NOT NULL DEFAULT '0',
  `credit_payment` double NOT NULL DEFAULT '0',
  `refund_except_insurance` double NOT NULL DEFAULT '0',
  `insurance_retrieval` double NOT NULL DEFAULT '0',
  `cash_refund` double NOT NULL DEFAULT '0',
  `mada_refund` double NOT NULL DEFAULT '0',
  `bank_transfer_refund` double NOT NULL DEFAULT '0',
  `credit_refund` double NOT NULL DEFAULT '0',
  `total_refund_payment` double NOT NULL DEFAULT '0',
  `promissory_notes` bigint(20) unsigned NOT NULL DEFAULT '0',
  `rent_revenue` double NOT NULL DEFAULT '0',
  `pos_reservation_revenue` double NOT NULL DEFAULT '0',
  `total_revenue` double NOT NULL DEFAULT '0',
  `walk_in_pos` double NOT NULL DEFAULT '0',
  `res_pos` double NOT NULL DEFAULT '0',
  `total_pos_revenue` double NOT NULL DEFAULT '0',
  `reservations` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_reports_team_id_index` (`team_id`),
  CONSTRAINT `daily_reports_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `device_logins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_initiator_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_os` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_impersonated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_logins_device_initiator_id_unique` (`device_initiator_id`),
  KEY `device_logins_user_id_foreign` (`user_id`),
  CONSTRAINT `device_logins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `digital_signatures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `ref_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reservation',
  `signature_base64` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `resource_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_url_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digital_signatures_team_id_index` (`team_id`),
  KEY `digital_signatures_ref_id_index` (`ref_id`),
  KEY `digital_signatures_type_index` (`type`),
  KEY `digital_signatures_user_id_foreign` (`user_id`),
  CONSTRAINT `digital_signatures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `early_late_charge_configs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `charge_type` enum('early_checkin','late_checkout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tier_from_hour` tinyint(4) NOT NULL COMMENT 'Hour 0-23',
  `tier_to_hour` tinyint(4) NOT NULL COMMENT 'Hour 0-23',
  `rate_type` enum('fixed','percentage_first_night','percentage_nightly_rate') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate_amount` decimal(8,2) NOT NULL,
  `applies_to` enum('all','daily','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `early_late_charge_configs_team_id_foreign` (`team_id`),
  CONSTRAINT `early_late_charge_configs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `ewa_mappers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `ewa_mapper_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ewa_mapper_id` bigint(20) unsigned NOT NULL,
  `qoyod_id` bigint(20) unsigned NOT NULL,
  `qoyod_product_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `fandaqah_staah_reservations` (
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `staah_booking_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staah_room_reservation_id` bigint(20) unsigned DEFAULT NULL,
  `staah_booking_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `staah_created_at` timestamp NULL DEFAULT NULL,
  `staah_modified_at` timestamp NULL DEFAULT NULL,
  `staah_processed_at` timestamp NULL DEFAULT NULL,
  KEY `fandaqah_staah_reservations_team_id_index` (`team_id`),
  KEY `fandaqah_staah_reservations_reservation_id_index` (`reservation_id`),
  KEY `fandaqah_staah_reservations_staah_booking_id_index` (`staah_booking_id`),
  KEY `fandaqah_staah_reservations_staah_room_reservation_id_index` (`staah_room_reservation_id`),
  KEY `fandaqah_staah_reservations_staah_booking_status_index` (`staah_booking_status`),
  KEY `fandaqah_staah_reservations_created_at_index` (`created_at`),
  KEY `fandaqah_staah_reservations_updated_at_index` (`updated_at`),
  KEY `fandaqah_staah_reservations_staah_created_at_index` (`staah_created_at`),
  KEY `fandaqah_staah_reservations_staah_modified_at_index` (`staah_modified_at`),
  KEY `fandaqah_staah_reservations_staah_processed_at_index` (`staah_processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `feature_announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_en` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_ar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  `is_spreaded` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `form_integrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `integration_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `reject_reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_integrations_team_id_index` (`team_id`),
  KEY `form_integrations_integration_name_index` (`integration_name`),
  KEY `form_integrations_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;








CREATE TABLE IF NOT EXISTS `group_reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `balance` double DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_reservations_company_id_index` (`company_id`),
  KEY `group_reservations_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `group_reservation_balance_mappers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `balance` double(8,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_reservation_balance_mappers_reservation_id_index` (`reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26368 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `guests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `shomoos_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` tinyint(4) DEFAULT NULL,
  `relation_type` tinyint(4) DEFAULT NULL,
  `id_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` tinyint(4) DEFAULT NULL,
  `customer_type` tinyint(4) DEFAULT NULL,
  `country_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_serial_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `shomoos_escort_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday_date` date DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65919 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `highlightables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `highlight_id` bigint(20) unsigned NOT NULL,
  `highlightable_id` bigint(20) unsigned NOT NULL,
  `highlightable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `highlightables_highlight_id_foreign` (`highlight_id`),
  KEY `highlightables_team_id_foreign` (`team_id`),
  CONSTRAINT `highlightables_highlight_id_foreign` FOREIGN KEY (`highlight_id`) REFERENCES `highlights` (`id`) ON DELETE CASCADE,
  CONSTRAINT `highlightables_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `highlights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `deleteable` bigint(20) unsigned NOT NULL DEFAULT '1',
  `order` bigint(20) unsigned DEFAULT NULL,
  `status` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#FFFFFF',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `hotel_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `image` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `id_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` bigint(20) unsigned DEFAULT NULL,
  `type_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `integrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `team_id` bigint(20) unsigned unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `integration_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` bigint(20) unsigned DEFAULT NULL,
  `action` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `integration_logs_subject_id_index` (`subject_id`),
  KEY `integration_logs_subject_type_index` (`subject_type`),
  KEY `integration_logs_team_id_index` (`team_id`) USING BTREE,
  KEY `integration_logs_created_at_index` (`created_at`) USING BTREE,
  KEY `integration_logs_status_index` (`status`) USING BTREE,
  KEY `integration_logs_action_index` (`action`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `integration_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `order` bigint(20) unsigned NOT NULL,
  `status` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `default_price` double NOT NULL DEFAULT '0',
  `default_additional_key_price` double NOT NULL DEFAULT '0',
  `show_on_purchase` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `invitations` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitations_token_unique` (`token`),
  KEY `invitations_team_id_index` (`team_id`),
  KEY `invitations_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `provider_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(8,2) DEFAULT NULL,
  `tax` decimal(8,2) DEFAULT NULL,
  `card_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_reported_to_zatca` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `invoices_created_at_index` (`created_at`),
  KEY `invoices_user_id_index` (`user_id`),
  KEY `invoices_team_id_index` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `invoice_credit_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_invoice_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_reported_to_zatca` longtext COLLATE utf8mb4_unicode_ci,
  `payload` json DEFAULT NULL,
  `service_log_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_credit_notes_number_index` (`number`),
  KEY `invoice_credit_notes_reservation_invoice_id_index` (`reservation_invoice_id`),
  KEY `invoice_credit_notes_team_id_index` (`team_id`),
  KEY `invoice_credit_notes_created_by_index` (`created_by`),
  KEY `invoice_credit_notes_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6427 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `invoice_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL COMMENT 'Source reservation',
  `company_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Target corporate account',
  `reservation_invoice_id` bigint(20) unsigned DEFAULT NULL COMMENT 'The formal invoice document being transferred',
  `promissory_id` bigint(20) unsigned NOT NULL COMMENT 'FK to promissories.id â€” the AR record created',
  `transfer_amount` decimal(12,4) NOT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transferred_by` bigint(20) unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `invoice_transfers_reservation_id_foreign` (`reservation_id`),
  KEY `invoice_transfers_company_id_foreign` (`company_id`),
  KEY `invoice_transfers_promissory_id_foreign` (`promissory_id`),
  KEY `invoice_transfers_team_id_foreign` (`team_id`),
  CONSTRAINT `invoice_transfers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `invoice_transfers_promissory_id_foreign` FOREIGN KEY (`promissory_id`) REFERENCES `promissories` (`id`),
  CONSTRAINT `invoice_transfers_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `invoice_transfers_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `iptv_guest_needs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `treated_by` bigint(20) unsigned DEFAULT NULL,
  `room_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_treated` tinyint(4) NOT NULL DEFAULT '0',
  `request_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wakeup_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `iptv_guest_needs_team_id_index` (`team_id`),
  KEY `iptv_guest_needs_reservation_id_index` (`reservation_id`),
  KEY `iptv_guest_needs_treated_by_index` (`treated_by`),
  KEY `iptv_guest_needs_is_treated_index` (`is_treated`),
  KEY `iptv_guest_needs_request_type_index` (`request_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `jawaly_sms_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numbers` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gate_message` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` bigint(20) unsigned DEFAULT NULL,
  `available_at` bigint(20) unsigned NOT NULL,
  `created_at` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=36508 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `leasing_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `leasing_category_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `leasing_category_id` bigint(20) unsigned NOT NULL,
  `qoyod_id` bigint(20) unsigned NOT NULL,
  `leasing_product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `let_link_devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `background_video_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_en` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_ar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_uuid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_tag` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `enable_voice_accessibility` tinyint(1) NOT NULL DEFAULT '0',
  `health_status` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `auth_code` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `let_link_devices_team_id_foreign` (`team_id`),
  CONSTRAINT `let_link_devices_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  `collection_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `manipulations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `custom_properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `responsive_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `order_column` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2336 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_en` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_ar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=596 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `mt_metas` (
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facility_type_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_type_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_classification_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_classification_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facility_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_status_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_status_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facilityPhone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facilityMobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facilityWebSite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facilityLocation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `mt_metas_license_number_unique` (`license_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `night_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `business_date` date NOT NULL,
  `run_number` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=first run, 2+=rerun',
  `status` enum('running','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `triggered_by` enum('auto','manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `triggered_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `steps_completed` json DEFAULT NULL,
  `steps_failed` json DEFAULT NULL,
  `noshows_flagged` bigint(20) unsigned NOT NULL DEFAULT '0',
  `noshow_charges_posted` bigint(20) unsigned NOT NULL DEFAULT '0',
  `transactions_frozen` bigint(20) unsigned NOT NULL DEFAULT '0',
  `occupancy_snapshot_id` bigint(20) unsigned DEFAULT NULL,
  `rerun_of_log_id` bigint(20) unsigned DEFAULT NULL,
  `dw_synced_at` timestamp NULL DEFAULT NULL COMMENT 'Set by Airflow after ETL',
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_night_audit_run` (`team_id`,`business_date`,`run_number`),
  KEY `night_audit_log_occupancy_snapshot_id_foreign` (`occupancy_snapshot_id`),
  CONSTRAINT `night_audit_log_occupancy_snapshot_id_foreign` FOREIGN KEY (`occupancy_snapshot_id`) REFERENCES `night_audit_occupancy_snapshot` (`id`),
  CONSTRAINT `night_audit_log_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `night_audit_noshow_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `night_audit_log_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `business_date` date DEFAULT NULL,
  `original_date_in` date DEFAULT NULL,
  `charge_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `charge_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `rule_id` bigint(20) unsigned DEFAULT NULL,
  `action_taken` enum('flagged_only','charged_and_cancelled','cancelled_only') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `night_audit_noshow_log_night_audit_log_id_foreign` (`night_audit_log_id`),
  KEY `night_audit_noshow_log_reservation_id_foreign` (`reservation_id`),
  KEY `night_audit_noshow_log_charge_transaction_id_foreign` (`charge_transaction_id`),
  KEY `night_audit_noshow_log_rule_id_foreign` (`rule_id`),
  CONSTRAINT `night_audit_noshow_log_charge_transaction_id_foreign` FOREIGN KEY (`charge_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `night_audit_noshow_log_night_audit_log_id_foreign` FOREIGN KEY (`night_audit_log_id`) REFERENCES `night_audit_log` (`id`) ON DELETE CASCADE,
  CONSTRAINT `night_audit_noshow_log_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `night_audit_noshow_log_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `no_show_charge_rules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `night_audit_occupancy_snapshot` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `business_date` date NOT NULL,
  `run_number` tinyint(4) NOT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT '1',
  `total_rooms` bigint(20) unsigned NOT NULL,
  `rooms_available` bigint(20) unsigned NOT NULL,
  `rooms_occupied` bigint(20) unsigned NOT NULL,
  `rooms_cleaning` bigint(20) unsigned NOT NULL,
  `rooms_maintenance` bigint(20) unsigned NOT NULL,
  `rooms_complimentary` bigint(20) unsigned NOT NULL,
  `rooms_house_use` bigint(20) unsigned NOT NULL,
  `rooms_day_use` bigint(20) unsigned NOT NULL,
  `is_backfill` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag for historical reconstructions',
  `occupancy_pct` decimal(5,2) NOT NULL,
  `adr` decimal(10,2) NOT NULL,
  `revpar` decimal(10,2) NOT NULL,
  `arrivals_count` bigint(20) unsigned NOT NULL,
  `departures_count` bigint(20) unsigned NOT NULL,
  `stayovers_count` bigint(20) unsigned NOT NULL,
  `noshows_count` bigint(20) unsigned NOT NULL,
  `cancellations_count` bigint(20) unsigned NOT NULL,
  `new_bookings_count` bigint(20) unsigned NOT NULL,
  `room_revenue` decimal(14,2) NOT NULL,
  `room_revenue_complimentary` decimal(14,2) NOT NULL,
  `service_revenue` decimal(14,2) NOT NULL,
  `noshow_revenue` decimal(14,2) NOT NULL,
  `adjustment_revenue` decimal(14,2) NOT NULL,
  `rebate_amount` decimal(14,2) NOT NULL,
  `total_revenue` decimal(14,2) NOT NULL,
  `vat_total` decimal(14,2) NOT NULL,
  `ewa_total` decimal(14,2) NOT NULL,
  `total_deposits_collected` decimal(14,2) NOT NULL,
  `total_promissory_created` decimal(14,2) NOT NULL,
  `total_promissory_collected` decimal(14,2) NOT NULL,
  `outstanding_promissory_balance` decimal(14,2) NOT NULL,
  `adults_count` bigint(20) unsigned NOT NULL,
  `children_count` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `night_audit_occupancy_snapshot_team_id_foreign` (`team_id`),
  CONSTRAINT `night_audit_occupancy_snapshot_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `night_audit_snapshot_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint(20) unsigned NOT NULL COMMENT 'FK to night_audit_occupancy_snapshot',
  `team_id` bigint(20) unsigned NOT NULL,
  `business_date` date NOT NULL,
  `status` enum('pending','inprogress','done','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `queued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `picked_up_at` timestamp NULL DEFAULT NULL COMMENT 'When Airflow started processing',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'When warehouse load finished',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `night_audit_snapshot_queue_snapshot_id_foreign` (`snapshot_id`),
  KEY `night_audit_snapshot_queue_team_id_foreign` (`team_id`),
  CONSTRAINT `night_audit_snapshot_queue_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `night_audit_occupancy_snapshot` (`id`) ON DELETE CASCADE,
  CONSTRAINT `night_audit_snapshot_queue_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` bigint(20) unsigned NOT NULL DEFAULT '1',
  `team_id` bigint(20) NOT NULL,
  `created_by_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `day_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notes_created_by_id_foreign` (`created_by_id`),
  KEY `notes_team_id_index` (`team_id`),
  KEY `notes_day_id_foreign` (`day_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1495 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `note_days` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL,
  `team_id` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `note_days_team_id_index` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_url` text COLLATE utf8mb4_unicode_ci,
  `read` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_created_at_index` (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `notification_controls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `type` enum('management','customer') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `notification_controls_team_id_key_type_index` (`team_id`,`key`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=21152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `no_show_charge_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL COMMENT 'FK to teams (Hotel)',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Rule name (e.g. Ramadan Peak)',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `charge_type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'fixed=SAR, percentage=% of first night',
  `charge_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `applies_to` enum('all','daily','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `no_show_charge_rules_team_id_foreign` (`team_id`),
  CONSTRAINT `no_show_charge_rules_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_access_token_providers` (
  `oauth_access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`oauth_access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_personal_access_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `occupieds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `units_count` bigint(20) unsigned NOT NULL,
  `available` bigint(20) unsigned NOT NULL DEFAULT '0',
  `maintenance` bigint(20) unsigned NOT NULL DEFAULT '0',
  `booked` bigint(20) unsigned NOT NULL DEFAULT '0',
  `occupied` bigint(20) unsigned NOT NULL DEFAULT '0',
  `percentage` bigint(20) unsigned NOT NULL DEFAULT '0',
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cleaning` bigint(20) unsigned NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `units_count` (`units_count`),
  KEY `available` (`available`),
  KEY `team_id` (`team_id`),
  KEY `created_at` (`created_at`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2166067 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `occupied_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'the name of offer',
  `discount_type` enum('percentage','price') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'percentage % or SAR',
  `discount_amount` decimal(8,2) DEFAULT NULL,
  `categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'unit categories ids one or multiple',
  `categories_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'the days to apply the offer',
  `enabled` tinyint(4) NOT NULL DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offers_team_id_foreign` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `online_reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_in` date NOT NULL,
  `date_out` date NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('cancelled','waiting','confirmed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `confirmed_by` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `provider` enum('website','public_api') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'website',
  `public_api_consumer_id` bigint(20) unsigned DEFAULT NULL,
  `nights` bigint(20) unsigned DEFAULT NULL,
  `price` double(8,2) DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `online_reservations_unit_id_foreign` (`unit_id`),
  KEY `online_reservations_team_id_foreign` (`team_id`),
  KEY `online_reservations_cancelled_by_foreign` (`cancelled_by`),
  KEY `online_reservations_confirmed_by_foreign` (`confirmed_by`),
  KEY `online_reservations_customer_id_foreign` (`customer_id`),
  KEY `online_reservations_reservation_id_foreign` (`reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `ota_reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hotel_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cm_booking_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booked_on` timestamp NULL DEFAULT NULL,
  `checkin` date DEFAULT NULL,
  `checkout` date DEFAULT NULL,
  `segment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `pah` tinyint(1) NOT NULL DEFAULT '0',
  `is_posted` tinyint(1) NOT NULL DEFAULT '0',
  `is_open` tinyint(1) NOT NULL DEFAULT '0',
  `unit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` text COLLATE utf8mb4_unicode_ci,
  `guest` text COLLATE utf8mb4_unicode_ci,
  `rooms` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ota_reservations_team_id_foreign` (`team_id`),
  CONSTRAINT `ota_reservations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `gateway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payable_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payable_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('success','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_logs_payable_type_payable_id_index` (`payable_type`,`payable_id`),
  KEY `payment_logs_payable_id_index` (`payable_id`),
  KEY `payment_logs_payable_type_index` (`payable_type`),
  KEY `payment_logs_team_id_index` (`team_id`),
  KEY `payment_logs_user_id_index` (`user_id`),
  KEY `payment_logs_gateway_index` (`gateway`),
  KEY `payment_logs_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `performance_indicators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `monthly_recurring_revenue` decimal(8,2) NOT NULL,
  `yearly_recurring_revenue` decimal(8,2) NOT NULL,
  `daily_volume` decimal(8,2) NOT NULL,
  `new_users` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_indicators_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `plan_type` enum('yearly','monthly','quarterly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yearly',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_keys` bigint(20) unsigned NOT NULL DEFAULT '0',
  `additional_key_price` double NOT NULL DEFAULT '0',
  `allowed_number_of_users` bigint(20) unsigned NOT NULL DEFAULT '0',
  `show_on_purchase` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plan_integrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `integration_setting_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `plan_id` bigint(20) unsigned NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `additional_key_price` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plan_integrations_integration_setting_id_foreign` (`integration_setting_id`),
  KEY `plan_integrations_plan_id_foreign` (`plan_id`),
  CONSTRAINT `plan_integrations_integration_setting_id_foreign` FOREIGN KEY (`integration_setting_id`) REFERENCES `integration_settings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plan_integrations_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pms_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtype` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guest_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_user_action` tinyint(1) NOT NULL DEFAULT '0',
  `is_treated` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pms_notifications_team_id_index` (`team_id`),
  KEY `pms_notifications_type_index` (`type`),
  KEY `pms_notifications_subtype_index` (`subtype`),
  KEY `pms_notifications_requires_user_action_index` (`requires_user_action`),
  KEY `pms_notifications_is_treated_index` (`is_treated`),
  KEY `pms_notifications_read_at_index` (`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `pos_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kernal_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_holder_verification_method_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_holder_verification_method_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `cvr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_address_one_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tvr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_address_two_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_address_one_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_address_two_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_category_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `res_desc_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tc` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `fpan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_expiry_date` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_request_time` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_message_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` bigint(20) unsigned DEFAULT NULL,
  `transaction_response_time` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `function_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merchant_name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terminal_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `res_desc` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formatted_amount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_scheme` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_scheme_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_mode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `par` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pos_service_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rrn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acq_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_message_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tsi` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resp_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desc` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `promissories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `serial` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_amount` double DEFAULT NULL,
  `collected_amount` double DEFAULT '0',
  `status` enum('fulfilled','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `due_location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_owner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_for` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Direct FK to companies.id. Avoids join through reservation.',
  `fulfilled_at` timestamp NULL DEFAULT NULL COMMENT 'When fully settled. Required for DSO calculation.',
  `signature_status` enum('signed','unsigned','waived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'signed' COMMENT 'signed = guest signed the note. unsigned = guest absent or refused. waived = management/corporate decision.',
  `unsigned_reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promissories_serial_index` (`serial`),
  KEY `promissories_reservation_id_index` (`reservation_id`),
  KEY `promissories_team_id_index` (`team_id`),
  KEY `promissories_user_id_index` (`user_id`),
  KEY `promissories_status_index` (`status`),
  KEY `promissories_due_date_index` (`due_date`),
  KEY `promissories_due_location_index` (`due_location`),
  KEY `promissories_created_at_index` (`created_at`),
  KEY `promissories_updated_at_index` (`updated_at`),
  KEY `promissories_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=949 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `promissory_payment_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `promissory_id` bigint(20) unsigned NOT NULL,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `amount_applied` decimal(12,2) NOT NULL,
  `payment_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint(20) unsigned DEFAULT NULL,
  `is_reversed` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promissory_payment_log_promissory_id_foreign` (`promissory_id`),
  KEY `promissory_payment_log_transaction_id_foreign` (`transaction_id`),
  KEY `promissory_payment_log_team_id_foreign` (`team_id`),
  CONSTRAINT `promissory_payment_log_promissory_id_foreign` FOREIGN KEY (`promissory_id`) REFERENCES `promissories` (`id`),
  CONSTRAINT `promissory_payment_log_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promissory_payment_log_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.promo_codes
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `discount_value` bigint(20) unsigned NOT NULL,
  `discount_type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_repeatedly` tinyint(1) NOT NULL,
  `usage_limit` bigint(20) unsigned NOT NULL DEFAULT '0',
  `end_date` date DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promo_codes_created_by_foreign` (`created_by`),
  KEY `promo_codes_updated_by_foreign` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `promo_code_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `promo_code_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promo_code_logs_promo_code_id_foreign` (`promo_code_id`),
  KEY `promo_code_logs_team_id_foreign` (`team_id`),
  KEY `promo_code_logs_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `public_api_consumers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `web_hook_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `web_hook_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 for not approved - 1 for approved',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `quick_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `guest_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `payment_status` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_description` text COLLATE utf8mb4_unicode_ci,
  `payment_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quick_payments_payment_id_unique` (`payment_id`),
  KEY `quick_payments_team_id_index` (`team_id`),
  KEY `quick_payments_reservation_id_index` (`reference_id`),
  KEY `quick_payments_payment_code_index` (`payment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `q_one` bigint(20) unsigned NOT NULL,
  `q_two` bigint(20) unsigned NOT NULL,
  `q_three` bigint(20) unsigned NOT NULL,
  `q_four` bigint(20) unsigned NOT NULL,
  `q_five` bigint(20) unsigned NOT NULL,
  `q_six` bigint(20) unsigned NOT NULL,
  `overall_rating` double(8,2) DEFAULT NULL,
  `q_seven` text COLLATE utf8mb4_unicode_ci,
  `q_eight` text COLLATE utf8mb4_unicode_ci,
  `team_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `redeemers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint(20) unsigned NOT NULL,
  `redeemer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redeemer_id` bigint(20) unsigned NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `redeemers_redeemer_type_redeemer_id_index` (`redeemer_type`,`redeemer_id`),
  KEY `redeemers_voucher_id_foreign` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `related_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `related_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `related_teams_team_id_foreign` (`team_id`),
  KEY `related_teams_related_id_foreign` (`related_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staah_booking_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_show` tinyint(1) NOT NULL DEFAULT '0',
  `breakfast_price` decimal(10,2) DEFAULT NULL,
  `invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('confirmed','canceled','awaiting-confirmation','awaiting-payment','timeout','hold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_date` timestamp NULL DEFAULT NULL,
  `is_online` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `bill_ref_id` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canceled_reason` text COLLATE utf8mb4_unicode_ci,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `change_rate` double(15,8) DEFAULT NULL,
  `prices` json DEFAULT NULL,
  `total_price` double DEFAULT '0',
  `sub_total` double DEFAULT '0',
  `ewa_total` double DEFAULT '0',
  `vat_total` double DEFAULT '0',
  `ttx_total` double DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `date_in` date DEFAULT NULL,
  `date_in_time` datetime DEFAULT NULL,
  `checked_in` datetime DEFAULT NULL,
  `checking_in` tinyint(4) NOT NULL DEFAULT '0',
  `date_out` date DEFAULT NULL,
  `date_out_time` datetime DEFAULT NULL,
  `checked_out` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `purpose_of_visit` bigint(20) unsigned DEFAULT NULL,
  `scth_reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `source_num` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rent_type` bigint(20) unsigned NOT NULL DEFAULT '1',
  `reservation_type` enum('single','group') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `group_reservation_id` bigint(20) unsigned DEFAULT NULL,
  `attachable_id` bigint(20) unsigned DEFAULT NULL,
  `rating_id` bigint(20) unsigned DEFAULT NULL,
  `old_prices` json DEFAULT NULL,
  `shomoos_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shomos_status` tinyint(1) DEFAULT '0',
  `occ` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `action_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_vc` bigint(20) GENERATED ALWAYS AS (reverse((reverse(`number`) << 0))) VIRTUAL,
  `bookingId` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cmBookingId` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_prices` json DEFAULT NULL,
  `reservation_notif_id` bigint(20) unsigned DEFAULT NULL,
  `channel_booking_id` bigint(20) unsigned DEFAULT NULL,
  `remove_vat` tinyint(1) NOT NULL DEFAULT '0',
  `reservation_category_type` enum('Normal','Complimentary','HouseUse','DayUse') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `adults` bigint(20) unsigned NOT NULL DEFAULT '1',
  `children` bigint(20) unsigned NOT NULL DEFAULT '0',
  `primary_payment_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cash | Mada | Visa | BankTransfer | Promissory. Set at checkout.',
  PRIMARY KEY (`id`),
  KEY `reservations_source_id_foreign` (`source_id`),
  KEY `reservations_team_id_index` (`team_id`),
  KEY `reservations_unit_id_index` (`unit_id`),
  KEY `reservations_customer_id_index` (`customer_id`),
  KEY `reservations_status_index` (`status`),
  KEY `reservations_date_in_index` (`date_in`),
  KEY `reservations_checked_in_index` (`checked_in`),
  KEY `reservations_date_out_index` (`date_out`),
  KEY `reservations_checked_out_index` (`checked_out`),
  KEY `reservations_rent_type_index` (`rent_type`),
  KEY `reservations_date_in_date_out_index` (`date_in`,`date_out`),
  KEY `invoice_number` (`invoice_number`),
  KEY `deleted_at` (`deleted_at`),
  KEY `created_at` (`created_at`),
  KEY `shomoos_id` (`shomoos_id`),
  KEY `number` (`number`),
  KEY `date_in_time` (`date_in_time`),
  KEY `date_out_time` (`date_out_time`),
  KEY `reservations_is_online_index` (`is_online`),
  KEY `reservations_occ_index` (`occ`),
  KEY `reservations_source_num_index` (`source_num`),
  KEY `reservations_reservation_type_index` (`reservation_type`),
  KEY `reservations_company_id_index` (`company_id`),
  KEY `reservations_group_reservation_id_index` (`group_reservation_id`),
  KEY `reservations_attachable_id_index` (`attachable_id`),
  KEY `reservations_staah_booking_id_index` (`staah_booking_id`),
  KEY `reservations_checking_in_index` (`checking_in`),
  KEY `number_vc` (`number_vc`),
  CONSTRAINT `reservations_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1062270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_audit_locks` (
  `reservation_id` bigint(20) unsigned NOT NULL,
  `locked_from_date` date NOT NULL COMMENT 'First closed night for this res',
  `locked_by_audit` bigint(20) unsigned NOT NULL COMMENT 'Audit log ID',
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  KEY `reservation_audit_locks_locked_by_audit_foreign` (`locked_by_audit`),
  KEY `reservation_audit_locks_team_id_foreign` (`team_id`),
  CONSTRAINT `reservation_audit_locks_locked_by_audit_foreign` FOREIGN KEY (`locked_by_audit`) REFERENCES `night_audit_log` (`id`),
  CONSTRAINT `reservation_audit_locks_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`),
  CONSTRAINT `reservation_audit_locks_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `html_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','signed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `signed_at` timestamp NULL DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` tinyint(4) NOT NULL,
  `shorten_url_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'i need this column to map the code with contract saved in aws to avoid guest trying to open same link multiple times',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_contracts_uuid_unique` (`uuid`),
  KEY `reservation_contracts_status_index` (`status`),
  KEY `reservation_contracts_signed_at_index` (`signed_at`),
  KEY `reservation_contracts_shorten_url_code_index` (`shorten_url_code`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_extensions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `unit_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `old_date_out` date NOT NULL,
  `new_date_out` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_extensions_reservation_id_foreign` (`reservation_id`),
  KEY `reservation_extensions_team_id_foreign` (`team_id`),
  KEY `reservation_extensions_unit_id_foreign` (`unit_id`),
  KEY `reservation_extensions_created_by_foreign` (`created_by`),
  CONSTRAINT `reservation_extensions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `reservation_extensions_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_extensions_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_extensions_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_guests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `id_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` bigint(20) unsigned DEFAULT NULL,
  `relation_type` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_type` bigint(20) unsigned DEFAULT NULL,
  `customer_type` bigint(20) unsigned DEFAULT NULL,
  `country_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_serial_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shomoos_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visa_number` varchar(11) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `shomoos_id` (`shomoos_id`),
  KEY `reservation_guests_customer_id_index` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34935 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `number` bigint(20) unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_group_reservation` tinyint(4) DEFAULT '0',
  `is_reported_to_zatca` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `team_id` (`team_id`),
  KEY `number` (`number`),
  KEY `created_by` (`created_by`),
  KEY `is_group_reservation` (`is_group_reservation`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=931566 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_message_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=500 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `qoyod_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_services_team_id_index` (`team_id`),
  KEY `reservation_services_user_id_index` (`user_id`),
  KEY `reservation_services_name_ar_index` (`name_ar`),
  KEY `reservation_services_name_en_index` (`name_en`),
  KEY `reservation_services_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_service_mappers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_service_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `presistent_service_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presistent_service_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presistent_price` double DEFAULT NULL,
  `presistent_old_price` double DEFAULT NULL,
  `presistent_vat_percentage` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_service_mappers_reservation_id_index` (`reservation_id`),
  KEY `reservation_service_mappers_reservation_service_id_index` (`reservation_service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reservation_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `old_unit_id` bigint(20) unsigned NOT NULL,
  `new_unit_id` bigint(20) unsigned NOT NULL,
  `old_date_in` date DEFAULT NULL,
  `old_date_out` date DEFAULT NULL,
  `new_date_in` date DEFAULT NULL,
  `new_date_out` date DEFAULT NULL,
  `old_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_transfers_old_unit_id_foreign` (`old_unit_id`),
  KEY `reservation_transfers_new_unit_id_foreign` (`new_unit_id`),
  KEY `reservation_transfers_created_by_foreign` (`created_by`),
  KEY `reservation_transfers_reservation_id_foreign` (`reservation_id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56762 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `reserved_rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deletable` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roles_team_id_foreign` (`team_id`),
  CONSTRAINT `roles_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8962 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permission` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_slug`),
  CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.role_user
CREATE TABLE IF NOT EXISTS `role_user` (
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.room_status_log
CREATE TABLE IF NOT EXISTS `room_status_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `from_status` tinyint(4) NOT NULL COMMENT '1=available, 2=cleaning, 3=maint',
  `to_status` tinyint(4) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `change_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model class causing change',
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `room_status_log_unit_id_foreign` (`unit_id`),
  KEY `room_status_log_team_id_foreign` (`team_id`),
  CONSTRAINT `room_status_log_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_status_log_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.scheduled_notifications
CREATE TABLE IF NOT EXISTS `scheduled_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `target_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `rescheduled_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.senders
CREATE TABLE IF NOT EXISTS `senders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `senders_team_id_foreign` (`team_id`),
  KEY `senders_created_by_foreign` (`created_by`),
  CONSTRAINT `senders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `senders_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.services
CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `services_category_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` double(8,5) NOT NULL DEFAULT '0.00000',
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `order` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_services_category_id_index` (`services_category_id`),
  KEY `services_team_id_index` (`team_id`),
  KEY `services_status_index` (`status`),
  KEY `services_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2483 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.service_categories
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `show_in_reservation` tinyint(4) NOT NULL DEFAULT '1',
  `show_in_pos` tinyint(4) NOT NULL DEFAULT '1',
  `order` bigint(20) unsigned NOT NULL DEFAULT '0',
  `users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `rev_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Values: POS | room | F&B | other. Confirm current deployment status.',
  PRIMARY KEY (`id`),
  KEY `service_categories_team_id_index` (`team_id`),
  KEY `service_categories_status_index` (`status`),
  KEY `service_categories_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=795 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.service_category_qoyods
CREATE TABLE IF NOT EXISTS `service_category_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `qoyod_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_category_qoyods_category_id_index` (`category_id`),
  KEY `service_category_qoyods_qoyod_id_index` (`qoyod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.service_logs
CREATE TABLE IF NOT EXISTS `service_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` bigint(20) unsigned NOT NULL,
  `amount` bigint(20) NOT NULL,
  `decimals` bigint(20) unsigned DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_subtraction` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `active_note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zatca_invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_freezed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Set to 1 by night audit. Blocks edits.',
  `business_date` date DEFAULT NULL COMMENT 'Assigned during night audit Step 1.',
  PRIMARY KEY (`id`),
  KEY `service_logs_team_id_index` (`team_id`),
  KEY `service_logs_user_id_index` (`user_id`),
  KEY `service_logs_transaction_id_index` (`transaction_id`),
  KEY `service_logs_type_index` (`type`),
  KEY `service_logs_number_index` (`number`),
  KEY `service_logs_decimals_index` (`decimals`),
  KEY `service_logs_is_subtraction_index` (`is_subtraction`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.service_logs_notes
CREATE TABLE IF NOT EXISTS `service_logs_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_log_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `service_logs_notes_service_log_id_foreign` (`service_log_id`),
  CONSTRAINT `service_logs_notes_service_log_id_foreign` FOREIGN KEY (`service_log_id`) REFERENCES `service_logs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.service_qoyods
CREATE TABLE IF NOT EXISTS `service_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `qoyod_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_qoyods_service_id_index` (`service_id`),
  KEY `service_qoyods_qoyod_id_index` (`qoyod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `sessions_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `key` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26302 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.short_urls
CREATE TABLE IF NOT EXISTS `short_urls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_urls_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.sources
CREATE TABLE IF NOT EXISTS `sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `deleteable` bigint(20) unsigned NOT NULL DEFAULT '1',
  `order` bigint(20) unsigned DEFAULT NULL,
  `status` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_travel_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = this source is a travel agent or OTA with commission.',
  `iata_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IATA accreditation number. Used in commission reports.',
  `commission_rate` decimal(5,2) DEFAULT NULL COMMENT 'Commission percentage or fixed SAR amount.',
  `commission_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'How commission is calculated.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21961 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.special_prices
CREATE TABLE IF NOT EXISTS `special_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'the name of special price',
  `unit_category_id` bigint(20) unsigned DEFAULT NULL,
  `days_prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'the days prices object',
  `enabled` tinyint(4) NOT NULL DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `special_prices_unit_category_id_foreign` (`unit_category_id`),
  KEY `special_prices_team_id_foreign` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.staah_daily_reservations
CREATE TABLE IF NOT EXISTS `staah_daily_reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staah_daily_reservations_team_id_index` (`team_id`),
  KEY `staah_daily_reservations_created_at_index` (`created_at`),
  KEY `staah_daily_reservations_updated_at_index` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_plan` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint(20) unsigned NOT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.subscription_invoices
CREATE TABLE IF NOT EXISTS `subscription_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `base` double NOT NULL DEFAULT '0',
  `vat` double NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `asset_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_reported` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('PAID','UNPAID','PENDING') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `type` enum('INVOICE','CREDIT_NOTE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `initiator` enum('USER','SYSTEM') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  `meta` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_mean` enum('BANK_TRANSFER','ONLINE_PAYMENT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ONLINE_PAYMENT',
  `tran_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_reason_flag` enum('CUSTOMER_REQUEST','CHANGED_MIND','ORDER_CANCELED','DUPLICATE_ORDER','WRONG_ITEM_ORDERED','NO_LONGER_NEEDED') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `subscription_invoices_subscription_id_foreign` (`subscription_id`),
  KEY `subscription_invoices_invoice_id_foreign` (`invoice_id`),
  KEY `subscription_invoices_user_id_foreign` (`user_id`),
  CONSTRAINT `subscription_invoices_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `subscription_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `team_subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2305 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.subscription_items
CREATE TABLE IF NOT EXISTS `subscription_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_subscription_id` bigint(20) unsigned NOT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_plan` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_items_stripe_id_unique` (`stripe_id`),
  KEY `subscription_items_team_subscription_id_stripe_plan_index` (`team_subscription_id`,`stripe_plan`),
  CONSTRAINT `subscription_items_team_subscription_id_foreign` FOREIGN KEY (`team_subscription_id`) REFERENCES `team_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.subscription_payments
CREATE TABLE IF NOT EXISTS `subscription_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tran_ref` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.tasks
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `taskable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskable_id` bigint(20) unsigned NOT NULL,
  `when` datetime NOT NULL,
  `done` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mytravel_hotel_id` bigint(20) unsigned DEFAULT NULL,
  `en_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel_manager_status` enum('connected','disconnected') COLLATE utf8mb4_unicode_ci DEFAULT 'disconnected',
  `staah_max_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_url` text COLLATE utf8mb4_unicode_ci,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_billing_plan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_price` bigint(20) unsigned DEFAULT NULL,
  `contract_note` text COLLATE utf8mb4_unicode_ci,
  `card_brand` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last_four` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address_line_2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SAR',
  `vat_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_billing_information` text COLLATE utf8mb4_unicode_ci,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `enable_website` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `country_code` bigint(20) unsigned NOT NULL DEFAULT '113',
  `city_id` bigint(20) unsigned DEFAULT NULL,
  `type_id` bigint(20) unsigned DEFAULT NULL,
  `booking_provider` tinyint(1) DEFAULT '0',
  `booking_provider_note` text COLLATE utf8mb4_unicode_ci,
  `have_website` tinyint(1) DEFAULT '0',
  `have_website_note` text COLLATE utf8mb4_unicode_ci,
  `point_of_sale` tinyint(1) DEFAULT '0',
  `point_of_sale_note` text COLLATE utf8mb4_unicode_ci,
  `enable_private_domain` tinyint(1) NOT NULL DEFAULT '0',
  `private_domain` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `private_domain_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sure_bills_active` tinyint(1) NOT NULL DEFAULT '0',
  `sure_bills_client_id` bigint(20) unsigned DEFAULT NULL,
  `sure_bills_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sure_bills_webhook_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suspect_shms` tinyint(4) NOT NULL DEFAULT '1',
  `suspect_scth` tinyint(4) NOT NULL DEFAULT '1',
  `payment_preprocessor` enum('sure-bills','fandaqah','hyperpay','null') COLLATE utf8mb4_unicode_ci DEFAULT 'null',
  `enabled_payment_link` tinyint(1) NOT NULL DEFAULT '0',
  `bank_account` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_fees` double(8,2) DEFAULT NULL,
  `transactions_fees` double(8,2) DEFAULT NULL,
  `transaction_fees_percentage` double(8,2) DEFAULT NULL,
  `visa_percentage` double(8,2) DEFAULT NULL,
  `master_percentage` double(8,2) DEFAULT NULL,
  `stc_percentage` double(8,2) DEFAULT NULL,
  `amex_percentage` double(8,2) DEFAULT NULL,
  `mada_percentage` double(8,2) DEFAULT NULL,
  `applepay_percentage` double(8,2) DEFAULT NULL,
  `buyer_name_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_name_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_country_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_country_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_city_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_city_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_district_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_district_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_street_ar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_street_en` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_building_number` bigint(20) DEFAULT NULL,
  `buyer_cr_number` bigint(20) DEFAULT NULL,
  `buyer_tax_number` bigint(20) DEFAULT NULL,
  `enable_unite_pagination` tinyint(1) DEFAULT '0',
  `per_page` bigint(20) unsigned DEFAULT '10',
  `enable_activity_logs` tinyint(1) NOT NULL DEFAULT '0',
  `enable_integrations_logs` tinyint(1) NOT NULL DEFAULT '0',
  `enable_hyper_split` tinyint(1) DEFAULT NULL,
  `enable_zatca_phase_two` tinyint(1) NOT NULL DEFAULT '0',
  `enable_mytravel` tinyint(1) DEFAULT NULL,
  `allowed_units_count` bigint(20) unsigned DEFAULT NULL,
  `enable_staah` tinyint(1) DEFAULT NULL,
  `enable_aiosell` tinyint(1) DEFAULT NULL,
  `enable_sms` tinyint(1) DEFAULT NULL,
  `enable_digital_signature` tinyint(1) NOT NULL DEFAULT '0',
  `enable_guest_qrcode` tinyint(4) NOT NULL DEFAULT '0',
  `enable_unit_qrcode` tinyint(1) NOT NULL DEFAULT '0',
  `account_manager_id` bigint(20) unsigned DEFAULT NULL,
  `mot_license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_date` date DEFAULT NULL COMMENT 'Current open business date. NULL = night audit never run.',
  `night_audit_cutoff_time` time NOT NULL DEFAULT '03:00:00' COMMENT 'Transactions before this time belong to previous business date.',
  `night_audit_auto_run_time` time NOT NULL DEFAULT '06:00:00' COMMENT 'Scheduler auto-triggers at this time.',
  `night_audit_auto_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = auto-run enabled.',
  `last_night_audit_at` timestamp NULL DEFAULT NULL COMMENT 'Last successful completion.',
  `last_night_audit_by` bigint(20) unsigned DEFAULT NULL COMMENT 'User ID or NULL if auto.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `teams_slug_unique` (`slug`),
  UNIQUE KEY `teams_private_domain_unique` (`private_domain`),
  KEY `teams_owner_id_index` (`owner_id`),
  KEY `city` (`city_id`),
  KEY `country_code` (`country_code`),
  KEY `teams_trial_ends_at_index` (`trial_ends_at`),
  KEY `teams_ends_at_index` (`ends_at`),
  KEY `teams_created_at_index` (`created_at`),
  KEY `teams_updated_at_index` (`updated_at`),
  KEY `teams_deleted_at_index` (`deleted_at`),
  KEY `teams_payment_preprocessor_index` (`payment_preprocessor`),
  KEY `teams_channel_manager_status_index` (`channel_manager_status`),
  KEY `teams_staah_max_id_index` (`staah_max_id`),
  KEY `teams_buyer_name_ar_index` (`buyer_name_ar`),
  KEY `teams_buyer_name_en_index` (`buyer_name_en`),
  KEY `teams_buyer_country_ar_index` (`buyer_country_ar`),
  KEY `teams_buyer_country_en_index` (`buyer_country_en`),
  KEY `teams_buyer_city_ar_index` (`buyer_city_ar`),
  KEY `teams_buyer_city_en_index` (`buyer_city_en`),
  KEY `teams_buyer_cr_number_index` (`buyer_cr_number`),
  KEY `teams_buyer_tax_number_index` (`buyer_tax_number`),
  KEY `teams_enable_activity_logs_index` (`enable_activity_logs`),
  KEY `teams_enable_integrations_logs_index` (`enable_integrations_logs`),
  KEY `teams_bank_account_index` (`bank_account`),
  KEY `teams_enabled_payment_link_index` (`enabled_payment_link`),
  KEY `teams_enable_unit_qrcode_index` (`enable_unit_qrcode`),
  KEY `teams_account_manager_id_foreign` (`account_manager_id`),
  CONSTRAINT `teams_account_manager_id_foreign` FOREIGN KEY (`account_manager_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2813 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.team_contact_persons
CREATE TABLE IF NOT EXISTS `team_contact_persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `person_phone_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_billing_user` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `team_contact_persons_team_id_foreign` (`team_id`),
  CONSTRAINT `team_contact_persons_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.team_counters
CREATE TABLE IF NOT EXISTS `team_counters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_reservation_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `invoice_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_invoice_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `receipt_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_receipt_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payment_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_payment_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `contract_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_contract_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `promissory_number` bigint(20) unsigned DEFAULT '0',
  `last_promissory_number` bigint(20) unsigned DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `service_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_service_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `credit_note_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_credit_note_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2755 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.team_subscriptions
CREATE TABLE IF NOT EXISTS `team_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_plan` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` bigint(20) unsigned NOT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `contract_price` bigint(20) unsigned DEFAULT NULL,
  `contract_note` text COLLATE utf8mb4_unicode_ci,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_reminder_at` timestamp NULL DEFAULT NULL,
  `renewal_at` timestamp NULL DEFAULT NULL,
  `extension_end_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `log_id` bigint(20) unsigned DEFAULT NULL,
  `unit_counts` bigint(20) unsigned NOT NULL DEFAULT '100',
  `installation_fees` double DEFAULT '0',
  `installation_vat` double NOT NULL DEFAULT '0',
  `contract_vat` double NOT NULL DEFAULT '0',
  `allowed_number_of_users` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payment_mean` enum('BANK_TRANSFER','ONLINE_PAYMENT') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3946 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.team_subscription_integrations
CREATE TABLE IF NOT EXISTS `team_subscription_integrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `integration_setting_id` bigint(20) unsigned NOT NULL,
  `team_subscription_id` bigint(20) unsigned NOT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_subscription_integrations_integration_setting_id_foreign` (`integration_setting_id`),
  KEY `team_subscription_integrations_team_subscription_id_foreign` (`team_subscription_id`),
  CONSTRAINT `team_subscription_integrations_integration_setting_id_foreign` FOREIGN KEY (`integration_setting_id`) REFERENCES `integration_settings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_subscription_integrations_team_subscription_id_foreign` FOREIGN KEY (`team_subscription_id`) REFERENCES `team_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.team_users
CREATE TABLE IF NOT EXISTS `team_users` (
  `team_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `team_users_team_id_user_id_unique` (`team_id`,`user_id`),
  KEY `team_users_team_id_index` (`team_id`),
  KEY `team_users_user_id_index` (`user_id`),
  KEY `team_users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.telescope_entries
CREATE TABLE IF NOT EXISTS `telescope_entries` (
  `sequence` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_hash` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.telescope_entries_tags
CREATE TABLE IF NOT EXISTS `telescope_entries_tags` (
  `entry_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.telescope_monitoring
CREATE TABLE IF NOT EXISTS `telescope_monitoring` (
  `tag` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.terms
CREATE TABLE IF NOT EXISTS `terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) unsigned NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `deleteable` bigint(20) unsigned NOT NULL DEFAULT '1',
  `type` bigint(20) unsigned DEFAULT NULL,
  `order` bigint(20) unsigned DEFAULT NULL,
  `status` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `terms_team_id_index` (`team_id`),
  KEY `terms_deleteable_index` (`deleteable`),
  KEY `terms_type_index` (`type`),
  KEY `terms_order_index` (`order`),
  KEY `terms_status_index` (`status`),
  KEY `terms_deleted_at_index` (`deleted_at`),
  KEY `terms_created_at_index` (`created_at`),
  KEY `terms_updated_at_index` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=107303 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.term_qoyods
CREATE TABLE IF NOT EXISTS `term_qoyods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned DEFAULT NULL,
  `qoyod_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `term_qoyods_term_id_index` (`term_id`),
  KEY `term_qoyods_qoyod_id_index` (`qoyod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payable_id` bigint(20) unsigned NOT NULL,
  `wallet_id` bigint(20) unsigned DEFAULT NULL,
  `unit_category_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('deposit','withdraw') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_flag` enum('normal','managerial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'This column for diff between normal transaction and managerial transaction',
  `is_insurance` tinyint(4) NOT NULL DEFAULT '0',
  `amount` bigint(20) NOT NULL,
  `amount_without_tax` bigint(20) NOT NULL DEFAULT '0',
  `enable_tax_on_withdraw` tinyint(4) NOT NULL DEFAULT '0',
  `tax_percentage` double(8,2) NOT NULL DEFAULT '0.00',
  `tax_amount` bigint(20) NOT NULL DEFAULT '0',
  `supplier_tax_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` bigint(20) unsigned NOT NULL DEFAULT '1',
  `is_promissory` tinyint(4) NOT NULL DEFAULT '0',
  `is_attached_to_invoice` tinyint(4) DEFAULT '0',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `kind` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` bigint(20) unsigned DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `receiver_bank_id` bigint(20) unsigned DEFAULT NULL,
  `bill_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `meta_type` bigint(20) GENERATED ALWAYS AS (json_unquote(json_extract(`meta`,_utf8'$.type'))) VIRTUAL,
  `meta_payment_type` varchar(50) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (json_unquote(json_extract(`meta`,_utf8'$.payment_type'))) VIRTUAL,
  `team_id` bigint(20) DEFAULT NULL,
  `correction_of_transaction_id` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to transactions.id â€” links reversal to original.',
  `is_advance_deposit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = deposit received before check-in. Used by Deposit Ledger report.',
  `is_freezed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_uuid_unique` (`uuid`),
  KEY `transactions_payable_type_payable_id_index` (`payable_type`,`payable_id`),
  KEY `transactions_type_index` (`type`),
  KEY `transactions_wallet_id_foreign` (`wallet_id`),
  KEY `is_public` (`is_public`),
  KEY `confirmed` (`confirmed`),
  KEY `is_public_2` (`is_public`),
  KEY `amount` (`amount`),
  KEY `deleted_at` (`deleted_at`),
  KEY `transactions_payable_type_index` (`payable_type`),
  KEY `transactions_transaction_flag_index` (`transaction_flag`),
  KEY `transactions_kind_index` (`kind`),
  KEY `transactions_created_at_index` (`created_at`),
  KEY `transactions_updated_at_index` (`updated_at`),
  KEY `transactions_is_promissory_index` (`is_promissory`),
  KEY `transactions_is_insurance_index` (`is_insurance`),
  KEY `transactions_updated_by_index` (`updated_by`),
  KEY `transactions_number_index` (`number`),
  KEY `transactions_amount_without_tax_index` (`amount_without_tax`),
  KEY `transactions_enable_tax_on_withdraw_index` (`enable_tax_on_withdraw`),
  KEY `transactions_tax_percentage_index` (`tax_percentage`),
  KEY `transactions_tax_amount_index` (`tax_amount`),
  KEY `transactions_unit_category_id_index` (`unit_category_id`),
  KEY `meta_type` (`meta_type`),
  KEY `meta_payment_type` (`meta_payment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.transaction_qoyods
CREATE TABLE IF NOT EXISTS `transaction_qoyods` (
  `id` bigint(20) unsigned NOT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('transaction','pos-transaction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transaction',
  `pos_sales_invoice_id` bigint(20) unsigned DEFAULT NULL,
  `qoyod_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_qoyods_transaction_id_index` (`transaction_id`),
  KEY `transaction_qoyods_qoyod_id_index` (`qoyod_id`),
  KEY `transaction_qoyods_type_index` (`type`),
  KEY `transaction_qoyods_pos_sales_invoice_id_index` (`pos_sales_invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.transfers
CREATE TABLE IF NOT EXISTS `transfers` (
  `id` bigint(20) unsigned NOT NULL,
  `from_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_id` bigint(20) unsigned NOT NULL,
  `to_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_id` bigint(20) unsigned NOT NULL,
  `status` enum('exchange','transfer','paid','refund','gift') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transfer',
  `status_last` enum('exchange','transfer','paid','refund','gift') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_id` bigint(20) unsigned NOT NULL,
  `withdraw_id` bigint(20) unsigned NOT NULL,
  `discount` decimal(64,0) NOT NULL DEFAULT '0',
  `fee` bigint(20) NOT NULL DEFAULT '0',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfers_uuid_unique` (`uuid`),
  KEY `transfers_from_type_from_id_index` (`from_type`,`from_id`),
  KEY `transfers_to_type_to_id_index` (`to_type`,`to_id`),
  KEY `transfers_deposit_id_foreign` (`deposit_id`),
  KEY `transfers_withdraw_id_foreign` (`withdraw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.turnaway_logs
CREATE TABLE IF NOT EXISTS `turnaway_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date_in` timestamp NOT NULL,
  `date_out` timestamp NOT NULL,
  `unit_category_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `turnaway_reason_id` bigint(20) unsigned NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnaway_logs_unit_category_id_foreign` (`unit_category_id`),
  KEY `turnaway_logs_team_id_foreign` (`team_id`),
  KEY `turnaway_logs_created_by_foreign` (`created_by`),
  KEY `turnaway_logs_customer_id_foreign` (`customer_id`),
  KEY `turnaway_logs_turnaway_reason_id_foreign` (`turnaway_reason_id`),
  CONSTRAINT `turnaway_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `turnaway_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `turnaway_logs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `turnaway_logs_turnaway_reason_id_foreign` FOREIGN KEY (`turnaway_reason_id`) REFERENCES `turnaway_reasons` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.turnaway_reasons
CREATE TABLE IF NOT EXISTS `turnaway_reasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reason_en` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason_ar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnaway_reasons_team_id_foreign` (`team_id`),
  CONSTRAINT `turnaway_reasons_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.units
CREATE TABLE IF NOT EXISTS `units` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `unit_category_id` bigint(20) unsigned DEFAULT NULL,
  `unit_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iptv_mac_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(1000) CHARACTER SET utf8mb4 DEFAULT '',
  `status` bigint(20) unsigned DEFAULT NULL,
  `available_to_sync` tinyint(4) DEFAULT '0',
  `sunday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `monday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `tuesday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `wednesday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `thursday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `friday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `saturday_hour_price` double(8,2) NOT NULL DEFAULT '0.00',
  `sunday_day_price` double DEFAULT '0',
  `monday_day_price` double DEFAULT '0',
  `tuesday_day_price` double DEFAULT '0',
  `wednesday_day_price` double DEFAULT '0',
  `thursday_day_price` double DEFAULT '0',
  `friday_day_price` double DEFAULT '0',
  `saturday_day_price` double DEFAULT '0',
  `month_price` double NOT NULL DEFAULT '0',
  `general_features` text COLLATE utf8mb4_unicode_ci,
  `special_features` text COLLATE utf8mb4_unicode_ci,
  `unit_options` text COLLATE utf8mb4_unicode_ci,
  `main_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `short_description` text CHARACTER SET utf8mb4 NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `youtube_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_sunday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_monday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_tuesday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_wednesday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_thursday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_friday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_saturday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_month_price` double(8,2) NOT NULL DEFAULT '0.00',
  `shomoos_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_in_website` tinyint(1) NOT NULL DEFAULT '0',
  `gates_lock_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `units_team_id_index` (`team_id`),
  KEY `units_unit_category_id_index` (`unit_category_id`),
  KEY `units_unit_number_index` (`unit_number`),
  KEY `units_status_index` (`status`),
  KEY `sunday_day_price` (`sunday_day_price`,`monday_day_price`,`tuesday_day_price`,`thursday_day_price`,`friday_day_price`,`wednesday_day_price`,`saturday_day_price`),
  KEY `enabled` (`enabled`),
  KEY `units_available_to_sync_index` (`available_to_sync`),
  KEY `units_iptv_mac_address_index` (`iptv_mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_categories
CREATE TABLE IF NOT EXISTS `unit_categories` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `enable_price_include_tax` tinyint(1) NOT NULL DEFAULT '0',
  `sunday_day_price` double DEFAULT '0',
  `monday_day_price` double DEFAULT '0',
  `tuesday_day_price` double DEFAULT '0',
  `wednesday_day_price` double DEFAULT '0',
  `thursday_day_price` double DEFAULT '0',
  `friday_day_price` double DEFAULT '0',
  `saturday_day_price` double DEFAULT '0',
  `general_features` text COLLATE utf8mb4_unicode_ci,
  `special_features` text COLLATE utf8mb4_unicode_ci,
  `main_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `short_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `youtube_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `order` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `type_id` bigint(20) unsigned DEFAULT NULL,
  `month_price` double DEFAULT NULL,
  `hour_price` double DEFAULT NULL,
  `min_sunday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_monday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_tuesday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_wednesday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_thursday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_friday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_saturday_day_price` double(8,2) NOT NULL DEFAULT '0.00',
  `min_month_price` double(8,2) NOT NULL DEFAULT '0.00',
  `show_in_website` tinyint(4) NOT NULL DEFAULT '1',
  `unit_size` double DEFAULT NULL,
  `number_of_adults` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '1',
  `number_of_children` bigint(20) unsigned DEFAULT '0',
  `number_of_beds` bigint(20) unsigned DEFAULT '0',
  `enable_staah_pricing` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`,`order`,`type_id`,`status`),
  KEY `unit_categories_show_in_website_index` (`show_in_website`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_category_services
CREATE TABLE IF NOT EXISTS `unit_category_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_cat_id` bigint(20) unsigned NOT NULL,
  `reservation_service_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `price` double NOT NULL,
  `old_price` double NOT NULL,
  `vat_percentage` double NOT NULL,
  `is_vat_included` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_category_services_unit_cat_id_foreign` (`unit_cat_id`),
  KEY `unit_category_services_reservation_service_id_foreign` (`reservation_service_id`),
  KEY `unit_category_services_team_id_foreign` (`team_id`),
  CONSTRAINT `unit_category_services_reservation_service_id_foreign` FOREIGN KEY (`reservation_service_id`) REFERENCES `reservation_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_category_services_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_category_services_unit_cat_id_foreign` FOREIGN KEY (`unit_cat_id`) REFERENCES `unit_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_cleanings
CREATE TABLE IF NOT EXISTS `unit_cleanings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `start_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `team_id` bigint(20) unsigned NOT NULL,
  `completed_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_cleanings_unit_id_foreign` (`unit_id`),
  KEY `unit_cleanings_created_by_foreign` (`created_by`),
  KEY `unit_cleanings_completed_by_foreign` (`completed_by`),
  KEY `start_at` (`start_at`),
  KEY `completed_at` (`completed_at`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_feature_icons
CREATE TABLE IF NOT EXISTS `unit_feature_icons` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_general_feature
CREATE TABLE IF NOT EXISTS `unit_general_feature` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `unit_feature_icon_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` bigint(20) unsigned NOT NULL DEFAULT '0',
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `order` (`order`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_maintenances
CREATE TABLE IF NOT EXISTS `unit_maintenances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `start_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `team_id` bigint(20) unsigned NOT NULL,
  `completed_by` bigint(20) unsigned DEFAULT NULL,
  `action_id` bigint(20) unsigned DEFAULT NULL,
  `expected_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_maintenances_unit_id_foreign` (`unit_id`),
  KEY `unit_maintenances_created_by_foreign` (`created_by`),
  KEY `unit_maintenances_completed_by_foreign` (`completed_by`),
  KEY `unit_maintenances_action_id_foreign` (`action_id`),
  CONSTRAINT `unit_maintenances_action_id_foreign` FOREIGN KEY (`action_id`) REFERENCES `action_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_media
CREATE TABLE IF NOT EXISTS `unit_media` (
  `id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned DEFAULT NULL,
  `path` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_options
CREATE TABLE IF NOT EXISTS `unit_options` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.unit_special_features
CREATE TABLE IF NOT EXISTS `unit_special_features` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `unit_feature_icon_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` bigint(20) unsigned NOT NULL DEFAULT '0',
  `status` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.untitled_table_120
CREATE TABLE IF NOT EXISTS `untitled_table_120` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `is_suspended` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_token` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_url` text COLLATE utf8mb4_unicode_ci,
  `uses_two_factor_auth` tinyint(4) NOT NULL DEFAULT '0',
  `authy_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_reset_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint(20) unsigned DEFAULT NULL,
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_billing_plan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_brand` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last_four` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address_line_2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_billing_information` text COLLATE utf8mb4_unicode_ci,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `last_read_announcements_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_expiry_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_control_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_multi_tab` tinyint(1) NOT NULL DEFAULT '0',
  `is_multi_session` tinyint(1) NOT NULL DEFAULT '0',
  `jwt_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `current_team_id` (`current_team_id`),
  KEY `deleted_at` (`deleted_at`),
  KEY `users_is_suspended_index` (`is_suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=4806 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.verificator_tokens
CREATE TABLE IF NOT EXISTS `verificator_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identifier_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` bigint(20) unsigned NOT NULL,
  `status` enum('verified','unverified') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_viewed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` bigint(20) unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `redeemed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amount` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_starts_at_index` (`starts_at`),
  KEY `vouchers_expires_at_index` (`expires_at`),
  KEY `vouchers_redeemed_at_index` (`redeemed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.voucher_entity
CREATE TABLE IF NOT EXISTS `voucher_entity` (
  `voucher_id` bigint(20) unsigned NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `entity` (`voucher_id`,`entity_type`,`entity_id`),
  KEY `voucher_entity_entity_type_entity_id_index` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.wallets
CREATE TABLE IF NOT EXISTS `wallets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `holder_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `holder_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance` bigint(20) NOT NULL DEFAULT '0',
  `decimal_places` smallint(6) NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_holder_type_holder_id_slug_unique` (`holder_type`,`holder_id`,`slug`),
  KEY `wallets_holder_type_holder_id_index` (`holder_type`,`holder_id`),
  KEY `wallets_slug_index` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=1060009 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.webhook_calls
CREATE TABLE IF NOT EXISTS `webhook_calls` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci,
  `exception` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.website_galleries
CREATE TABLE IF NOT EXISTS `website_galleries` (
  `id` bigint(20) unsigned NOT NULL,
  `website_id` bigint(20) unsigned NOT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('gallery','slider') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gallery',
  `object` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `website_galleries_website_id_foreign` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.website_pages
CREATE TABLE IF NOT EXISTS `website_pages` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `order` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `website_pages_team_id_foreign` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table fandaqah.website_settings
CREATE TABLE IF NOT EXISTS `website_settings` (
  `id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `show_in_booking_engine` tinyint(4) NOT NULL DEFAULT '1',
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_background` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `favicon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arabic_lang` tinyint(4) NOT NULL DEFAULT '1',
  `english_lang` tinyint(4) NOT NULL DEFAULT '0',
  `default_lang` enum('ar','en') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ar',
  `featured_unit_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hover_text_color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ea4a4a',
  `footer_text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_hover_click_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_search_box_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_search_button_text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_search_button_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_gallery` tinyint(1) DEFAULT NULL,
  `enable_social` tinyint(1) DEFAULT NULL,
  `social_icons_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_facebook_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_instagram_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_twitter_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_snapchat_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_youtube_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slide_first_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `slide_second_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title_first_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title_second_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `search_box_top_first_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `search_box_top_second_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `search_box_top_third_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `search_button_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `cancellation_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `footer_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `contact_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `contact_map_url` text COLLATE utf8mb4_unicode_ci,
  `contact_map_iframe` text COLLATE utf8mb4_unicode_ci,
  `contact_address_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `description_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `images_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `special_features_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `general_features_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `options_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `video_block_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `note_for_waiting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `view_all` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `header_background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#FFFFFF',
  `bank_transfer` tinyint(4) NOT NULL DEFAULT '0',
  `deposit_percentage` bigint(20) unsigned NOT NULL DEFAULT '100',
  `credit_card` tinyint(4) NOT NULL DEFAULT '0',
  `bank_account_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `enable_about_us` tinyint(4) NOT NULL DEFAULT '0',
  `about_us_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about_us_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `about_us_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `time_payment_completed` bigint(20) unsigned DEFAULT '10',
  `enable_intro_video` tinyint(4) DEFAULT '0',
  `intro_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `intro_video_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro_background` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_console_code` text COLLATE utf8mb4_unicode_ci,
  `google_analytics_code` text COLLATE utf8mb4_unicode_ci,
  `maximum_calendar_date` datetime DEFAULT NULL,
  `minimum_calendar_date` datetime DEFAULT NULL,
  `meta_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `meta_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `meta_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `pay_later` tinyint(4) NOT NULL DEFAULT '0',
  `time_payment_completed_hours` bigint(20) unsigned NOT NULL DEFAULT '0',
  `alert_sms` tinyint(4) NOT NULL DEFAULT '0',
  `enable_sms` tinyint(1) NOT NULL DEFAULT '0',
  `enable_part_time_booking` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `website_settings_team_id_foreign` (`team_id`),
  KEY `website_settings_show_in_booking_engine_index` (`show_in_booking_engine`),
  KEY `website_settings_whatsapp_number_index` (`whatsapp_number`),
  KEY `website_settings_pay_later_index` (`pay_later`),
  KEY `website_settings_time_payment_completed_hours_index` (`time_payment_completed_hours`),
  KEY `website_settings_alert_sms_index` (`alert_sms`),
  KEY `website_settings_enable_part_time_booking_index` (`enable_part_time_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for view fandaqah.zatca_e_invoices
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `zatca_e_invoices` (
	`team_id` BIGINT(20) UNSIGNED NULL,
	`ref_id` BIGINT(20) UNSIGNED NULL,
	`ref_id_number` INT(11) NULL,
	`child_id` BIGINT(20) NULL,
	`child_id_number` VARCHAR(191) NULL COLLATE 'utf8mb4_unicode_ci',
	`payload` LONGTEXT NULL COLLATE 'utf8mb4_unicode_ci',
	`sub_type` VARCHAR(191) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`created_at` VARCHAR(24) NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` TIMESTAMP NULL,
	`model` VARCHAR(11) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`type` VARCHAR(22) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Dumping structure for view fandaqah.zatca_e_invoices
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `zatca_e_invoices`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `zatca_e_invoices` AS SELECT reservation_invoices.team_id, reservation_invoices.id AS ref_id, reservation_invoices.number AS ref_id_number,  NULL AS child_id , NULL AS child_id_number , reservation_invoices.is_reported_to_zatca AS payload, 'invoice' AS sub_type, 
        DATE_FORMAT(reservation_invoices.created_at, '%Y-%m-%d %H:%i:%s') AS created_at, reservation_invoices.deleted_at, 'Reservation' AS model, 
        CASE 
        WHEN reservation_invoices.is_group_reservation = 1 THEN 'tax invoice'
        ELSE 'simplified tax invoice'
        END AS `type`
        FROM reservation_invoices
        LEFT JOIN reservations ON reservations.id = reservation_invoices.reservation_id 

        UNION ALL
        SELECT reservation_invoices.team_id, reservation_invoices.id AS ref_id, reservation_invoices.number AS ref_id_number, invoice_credit_notes.id AS child_id, invoice_credit_notes.number AS child_id_number, invoice_credit_notes.is_reported_to_zatca AS payload, 
                    'credit note' AS sub_type, DATE_FORMAT(invoice_credit_notes.created_at, '%Y-%m-%d %H:%i:%s') AS created_at, invoice_credit_notes.deleted_at, 'Reservation' AS model,
                    CASE 
                    WHEN reservation_invoices.is_group_reservation = 1 THEN 'tax invoice'
                    ELSE 'simplified tax invoice'
                    END AS `type`
        FROM invoice_credit_notes
        LEFT JOIN reservation_invoices ON invoice_credit_notes.reservation_invoice_id = reservation_invoices.id 
        LEFT JOIN reservations ON reservations.id = reservation_invoices.reservation_id 

        UNION ALL
        SELECT service_logs.team_id, service_logs.number AS ref_id, service_logs.number AS ref_id_number,  service_logs_notes.id AS child_id, NULL AS child_id_number, service_logs_notes.payload AS payload, 
                service_logs_notes.type AS sub_type, DATE_FORMAT(service_logs_notes.created_at, '%Y-%m-%d %H:%i:%s') AS created_at, service_logs_notes.deleted_at, 'ServiceLog' AS model,
                'simplified tax invoice' AS `type`
        FROM service_logs_notes
        LEFT JOIN service_logs ON service_logs_notes.service_log_id = service_logs.id ;


