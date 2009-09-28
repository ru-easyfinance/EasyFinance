CREATE TABLE `currency` (
      `cur_id` int(11) NOT NULL AUTO_INCREMENT,
      `cur_name` varchar(10) NOT NULL,
      `cur_char_code` varchar(15) NOT NULL,
      `cur_name_value` varchar(255) NOT NULL,
      `cur_okv_id` varchar(4) NOT NULL,
      `cur_country` varchar(255) NOT NULL COMMENT 'ИД страны',
      `cur_uses` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Обновлять курс',
      PRIMARY KEY (`cur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `daily_currency` (
      `currency_id` int(11) NOT NULL,
      `currency_date` date NOT NULL,
      `currency_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Системный курс валют',
      `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
      `currency_user_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Пользовательский курс валют',
      `user_id` bigint(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид пользователя',
      KEY `new_index` (`currency_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
