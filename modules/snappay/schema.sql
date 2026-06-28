-- SnappPay tables (InnoDB)

CREATE TABLE IF NOT EXISTS `snappay_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `oid` int unsigned NOT NULL,
  `attempt_no` smallint unsigned NOT NULL,
  `transaction_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `payment_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `amount_toman` bigint unsigned NOT NULL,
  `amount_irr` bigint unsigned NOT NULL,
  `callback_state` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `callback_amount_irr` bigint unsigned DEFAULT NULL,
  `callback_received_at` timestamp NULL DEFAULT NULL,
  `verify_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `verify_attempts` smallint unsigned NOT NULL DEFAULT '0',
  `settle_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `settle_attempts` smallint unsigned NOT NULL DEFAULT '0',
  `snappay_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status_amount_irr` bigint unsigned DEFAULT NULL,
  `final_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `snappay_transaction_id_uq` (`transaction_id`),
  UNIQUE KEY `snappay_payment_token_uq` (`payment_token`),
  UNIQUE KEY `snappay_oid_attempt_uq` (`oid`,`attempt_no`),
  KEY `snappay_oid_id_idx` (`oid`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `snappay_errors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `oid` int unsigned DEFAULT NULL,
  `snappay_tx_id` int unsigned DEFAULT NULL,
  `stage` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `http_status` int DEFAULT NULL,
  `error_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `raw_response_masked` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `browser` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `snappay_errors_oid_idx` (`oid`),
  KEY `snappay_errors_tx_idx` (`snappay_tx_id`),
  KEY `snappay_errors_tx_created_idx` (`snappay_tx_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;
