CREATE TABLE IF NOT EXISTS `%%prefix%%stats` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL DEFAULT '0',
  `ymd_time` int(10) NOT NULL DEFAULT '0',
  `views` bigint(20) NOT NULL DEFAULT '0',
  `visits` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_statistic` (`post_id`,`ymd_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;