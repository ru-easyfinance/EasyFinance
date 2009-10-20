CREATE TABLE  `budget` (
      `key` varchar(50) NOT NULL COMMENT 'Ид пользователя + Ид категории + drain + Дата начала',
      `user_id` bigint(10) unsigned NOT NULL COMMENT 'ИД пользователя',
      `category` bigint(10) unsigned NOT NULL COMMENT 'ИД категории',
      `drain` tinyint(4) NOT NULL COMMENT '1 - расход, 0 - доход',
      `currency` int(10) unsigned NOT NULL COMMENT 'Валюта',
      `amount` decimal(20,2) NOT NULL COMMENT 'Сумма',
      `date_start` date NOT NULL COMMENT 'Дата начала периода',
      `date_end` date NOT NULL COMMENT 'Дата окончания периода',
      `dt_create` datetime NOT NULL COMMENT 'Дата создания',
      `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обновления',
      UNIQUE KEY `key_uniq` (`key`),
      KEY `start_idx` (`date_start`,`date_end`,`user_id`),
      KEY `subs_idx1` (`date_start`,`category`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Бюджет'
