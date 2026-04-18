CREATE TABLE IF NOT EXISTS `{{db.tables.votes}}` (
  `choice_uid` VARCHAR(100) NOT NULL,
  `poll_id` BIGINT(20) UNSIGNED NOT NULL,
  `votes` BIGINT(20) NOT NULL,
  `last_vote_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`choice_uid`),
  UNIQUE KEY `CHOICE_UID_UNIQUE` (`choice_uid`),
  KEY `POLL_ID` (`poll_id`),
  KEY `VOTES` (`votes`),
  KEY `LAST_VOTE_AT` (`last_vote_at`)
) {{db.charset}};