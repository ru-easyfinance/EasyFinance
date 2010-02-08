DROP TABLE IF EXISTS `calend`;

CREATE TABLE  `calend` (
  `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид события (цепочки)',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `type` enum('e','p') NOT NULL COMMENT 'Тип события: e - Обычное событие календаря, p - событие регулярной транзакции',
  `title` varchar(100) NOT NULL COMMENT 'Заголовок события (только для событий)',
  `start` date NOT NULL,
  `date` date NOT NULL,
  `last` date NOT NULL,
  `time` time NOT NULL,
  `every` int(1) unsigned DEFAULT NULL,
  `repeat` int(1) unsigned DEFAULT NULL,
  `week` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0000000' COMMENT 'Двоичная маска (0000011 - выходные, 1111100 - будни)\n',
  `comment` varchar(255) NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `cat_id` int(255) unsigned NOT NULL,
  `account_id` int(11) NOT NULL,
  `op_type` int(1) unsigned NOT NULL,
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Календарь';

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE  `calendar_events` (
  `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT,
  `cal_id` bigint(100) unsigned NOT NULL,
  `date` date NOT NULL,
  `accept` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cal_idx` (`cal_id`),
  KEY `date_idx` (`date`,`cal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='События календаря';
