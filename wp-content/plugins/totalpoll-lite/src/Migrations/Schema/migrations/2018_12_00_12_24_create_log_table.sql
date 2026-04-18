CREATE TABLE IF NOT EXISTS `{{db.tables.log}}` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `useragent` VARCHAR(512) NULL,
  `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `poll_id` BIGINT(20) UNSIGNED NOT NULL,
  `choices` LONGTEXT NULL,
  `action` VARCHAR(100) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `details` LONGTEXT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IP` (`ip`),
  KEY `USER_ID` (`user_id`),
  KEY `POLL_ID` (`poll_id`),
  KEY `ACTION` (`action`),
  KEY `STATUS` (`status`),
  KEY `DATE` (`date`)
) {{db.charset}};