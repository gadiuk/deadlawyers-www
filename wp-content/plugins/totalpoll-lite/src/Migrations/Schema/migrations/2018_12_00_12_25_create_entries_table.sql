CREATE TABLE IF NOT EXISTS `{{db.tables.entries}}` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `poll_id` BIGINT(20) UNSIGNED NOT NULL,
  `log_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `fields` LONGTEXT NULL,
  `details` LONGTEXT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `USER_ID` (`user_id`),
  KEY `POLL_ID` (`poll_id`),
  KEY `LOG_ID` (`log_id`),
  KEY `DATE` (`date`)
) {{db.charset}};