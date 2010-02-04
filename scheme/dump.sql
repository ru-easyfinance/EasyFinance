DROP TABLE IF EXISTS `Acc_Fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Acc_Fields` (
  `id` tinyint(1) unsigned NOT NULL COMMENT 'Ид поля',
  `name` varchar(50) NOT NULL COMMENT 'Техническое название поля',
  `description` varchar(50) NOT NULL COMMENT 'Описание поля',
  `account_type` tinyint(1) unsigned NOT NULL COMMENT 'Ид типа счёта',
  KEY `id_idx` (`id`),
  KEY `id_acct_idx` (`account_type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Оределяем поля по типу счетов';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `Acc_Object`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Acc_Object` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ИД счёта',
  `account_name` varchar(50) DEFAULT NULL COMMENT 'Название счёта',
  `account_type_id` tinyint(1) unsigned DEFAULT NULL COMMENT 'Ид типа счёта',
  `account_description` varchar(255) DEFAULT NULL COMMENT 'Описание - комментарий к счёту',
  `account_currency_id` int(1) unsigned DEFAULT NULL COMMENT 'Ид валюты счёта',
  `user_id` int(100) unsigned DEFAULT NULL COMMENT 'Ид пользователя',
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица счетов с базовыми полями';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `Acc_Values`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `Acc_Values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `field_id` int(10) unsigned NOT NULL COMMENT 'Ид поля',
  `field_value` varchar(255) DEFAULT NULL COMMENT 'Значение поля',
  `account_id` int(100) unsigned NOT NULL COMMENT 'Ид счёта',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Значения для дополнительных полей счетов';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `account_field_descriptions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `account_field_descriptions` (
  `field_description_id` int(20) NOT NULL AUTO_INCREMENT,
  `field_visual_name` varchar(150) DEFAULT NULL,
  `field_name` varchar(64) NOT NULL,
  `field_type` enum('numeric','string','text','html','enum','set') DEFAULT NULL,
  `field_regexp` text NOT NULL,
  `field_permissions` enum('view','select','input','add','hidden') NOT NULL,
  `field_default_value` text NOT NULL,
  PRIMARY KEY (`field_description_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `account_field_values`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `account_field_values` (
  `field_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_fieldsaccount_field_id` int(11) NOT NULL,
  `int_value` int(11) DEFAULT NULL,
  `string_value` varchar(255) DEFAULT NULL,
  `date_value` date DEFAULT NULL,
  `accountsaccount_id` int(11) NOT NULL,
  PRIMARY KEY (`field_value_id`),
  KEY `FK_01` (`account_fieldsaccount_field_id`),
  KEY `FK_02` (`accountsaccount_id`)
) ENGINE=InnoDB AUTO_INCREMENT=662 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `account_fields`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `account_fields` (
  `account_field_id` int(10) NOT NULL AUTO_INCREMENT,
  `account_typesaccount_type_id` int(11) NOT NULL,
  `field_descriptionsfield_description_id` int(10) NOT NULL,
  PRIMARY KEY (`account_field_id`),
  KEY `FKaccount_fi261530` (`field_descriptionsfield_description_id`),
  KEY `FKaccount_fi715328` (`account_typesaccount_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `account_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `account_types` (
  `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `accounts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `account_description` varchar(255) DEFAULT NULL,
  `account_currency_id` int(11) NOT NULL,
  `user_id` int(100) unsigned NOT NULL,
  PRIMARY KEY (`account_id`),
  KEY `FKaccounts554525` (`account_type_id`),
  KEY `user_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `budget`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `budget` (
  `user_id` bigint(10) unsigned NOT NULL COMMENT 'ИД пользователя',
  `category` bigint(10) unsigned NOT NULL COMMENT 'ИД категории',
  `drain` tinyint(4) NOT NULL COMMENT '1 - расход, 0 - доход',
  `currency` int(10) unsigned NOT NULL COMMENT 'Валюта',
  `amount` decimal(20,2) NOT NULL COMMENT 'Сумма',
  `date_start` date NOT NULL COMMENT 'Дата начала периода',
  `date_end` date NOT NULL COMMENT 'Дата окончания периода',
  `dt_create` datetime NOT NULL COMMENT 'Дата создания',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  `key` varchar(50) NOT NULL COMMENT 'Ид пользователя + Ид категории + drain + Дата начала',
  UNIQUE KEY `key_uniq` (`key`),
  KEY `start_idx` (`date_start`,`date_end`,`user_id`) USING BTREE,
  KEY `subs_idx1` (`date_start`,`category`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Бюджет';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `calend`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `calend` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Календарь';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `calendar`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `calendar` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид события',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `chain` bigint(10) unsigned NOT NULL COMMENT 'Ид цепочки событий',
  `title` varchar(255) NOT NULL COMMENT 'Заголовок',
  `near_date` datetime NOT NULL COMMENT 'Ближайшая дата',
  `start_date` date NOT NULL COMMENT 'Начальная дата',
  `last_date` date NOT NULL COMMENT 'Последняя дата',
  `type_repeat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Тип повторений: 0 - Без повторения, 1 - Ежедневно, 3 - Каждый Пн., Ср. и Пт., 4 - Каждый Вт. и Чт., \n5 - По будням, 6 - По выходным, 7 - Еженедельно, 30 - Ежемесячно, 90 - Ежеквартально, 365 - Ежегодно',
  `count_repeat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Повторять каждые ... раз/дней/недель/месяцев\n',
  `comment` text COMMENT 'Комментарий к событию',
  `dt_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Когда создали',
  `dt_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последнее обновление',
  `infinity` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Повторять бесконечно',
  `week` varchar(50) NOT NULL COMMENT 'Сериализованный массив с днями недели',
  `event` enum('cal','per') NOT NULL DEFAULT 'cal' COMMENT 'Событие календаря или периодической транзакции',
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'Сумма для периодических транзакций',
  `category` bigint(10) unsigned NOT NULL COMMENT 'Ид категории для периодических транзакций',
  `close` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Статус цели, по умолчанию = 0, т.е. открыта',
  PRIMARY KEY (`id`),
  KEY `cal_idx` (`close`,`near_date`,`user_id`),
  KEY `del_per_idx` (`user_id`,`chain`,`near_date`)
) ENGINE=InnoDB AUTO_INCREMENT=6292 DEFAULT CHARSET=utf8 COMMENT='Календарь';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `calendar_events`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `calendar_events` (
  `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT,
  `cal_id` bigint(100) unsigned NOT NULL,
  `date` date NOT NULL,
  `accept` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cal_idx` (`cal_id`),
  KEY `date_idx` (`date`,`cal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COMMENT='События календаря';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `category`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `category` (
  `cat_id` int(255) NOT NULL AUTO_INCREMENT COMMENT 'Ид категории',
  `cat_parent` int(255) NOT NULL DEFAULT '0' COMMENT 'Ид родительской категории (если 0, то она сам себе родитель)',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `system_category_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Ид системной категории',
  `cat_name` varchar(255) NOT NULL COMMENT 'Имя категории',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Тип категории (-1 - расходная, 0 - универсальная, 1 - доходная)',
  `cat_active` int(11) NOT NULL DEFAULT '1' COMMENT 'Активна ли категория? WTF???',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Видимость категории. 0 - не видно, 1 - видно',
  `custom` tinyint(1) NOT NULL COMMENT '1 - Создана пользователем, 0 - системная',
  `dt_create` datetime NOT NULL COMMENT 'Таймштамп при создании',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Таймштамп при обновлении',
  PRIMARY KEY (`cat_id`),
  KEY `user_id` (`user_id`,`visible`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=837 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `currency`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `currency` (
  `cur_id` int(11) NOT NULL AUTO_INCREMENT,
  `cur_name` varchar(10) NOT NULL,
  `cur_char_code` varchar(15) NOT NULL,
  `cur_name_value` varchar(255) NOT NULL,
  `cur_okv_id` varchar(4) NOT NULL,
  `cur_country` varchar(255) NOT NULL COMMENT 'ИД страны',
  `cur_uses` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Обновлять курс',
  PRIMARY KEY (`cur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `daily_currency`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `daily_currency` (
  `currency_id` int(11) NOT NULL,
  `currency_date` date NOT NULL,
  `currency_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Системный курс валют',
  `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
  `currency_user_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Пользовательский курс валют',
  `user_id` bigint(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид пользователя',
  KEY `idx` (`currency_date`,`currency_id`,`user_id`) USING BTREE,
  KEY `date_idx` (`currency_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `experts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts` (
  `id` int(11) NOT NULL,
  `min_desc` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `experts_plugins`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_plugins` (
  `id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `title` int(11) NOT NULL,
  `rem` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='коментировать!!';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `feedback_message`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feedback_message` (
  `uid` int(100) NOT NULL COMMENT 'id пользователя',
  `user_settings` text NOT NULL COMMENT 'сис настройки пользователя',
  `messages` text NOT NULL COMMENT 'сообщение',
  `user_name` varchar(32) NOT NULL COMMENT 'имя пользователя',
  `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'новое',
  `rating` int(8) NOT NULL DEFAULT '0' COMMENT 'рэйтинг сообщения',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс сообщений для быстрого пользователя',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='таблица сообщений от тестеров';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `info_calc`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `info_calc` (
  `m_r` int(11) DEFAULT NULL,
  `m_y` int(11) DEFAULT NULL,
  `m_b` int(11) DEFAULT NULL,
  `c_r` int(11) DEFAULT '1',
  `c_y` int(11) DEFAULT '2',
  `c_g` int(11) DEFAULT '3',
  `u_r` int(11) DEFAULT '0',
  `u_y` int(11) DEFAULT '1',
  `u_g` int(11) DEFAULT '2',
  `weight` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `info_desc`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `info_desc` (
  `type` varchar(11) DEFAULT NULL,
  `title` varchar(11) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `color` int(11) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `infopanel_desc`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `infopanel_desc` (
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') CHARACTER SET latin1 NOT NULL,
  `start` int(1) NOT NULL,
  `end` int(1) NOT NULL,
  `desc` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `infopanel_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `infopanel_users` (
  `user_id` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') CHARACTER SET latin1 DEFAULT NULL,
  `settings` text CHARACTER SET latin1,
  `state` enum('0','1','2') CHARACTER SET latin1 DEFAULT NULL,
  `order` enum('0','1','2') CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `infopanel_value`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `infopanel_value` (
  `uid` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat','akc_year','pif_year','ofbu_year','oms_year','estat_year') CHARACTER SET latin1 NOT NULL,
  `dete` date NOT NULL,
  `value` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `mail`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `text` text NOT NULL,
  `category` varchar(10) NOT NULL,
  `title` varchar(10) NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `a_vis` tinyint(1) NOT NULL,
  `t_vis` tinyint(1) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `messages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `messages` (
  `id` int(128) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор сообщения',
  `sender_id` int(100) NOT NULL COMMENT 'Идентификатор отправителя (user_id)',
  `receiver_id` int(100) NOT NULL COMMENT 'Идентификатор получателя (user_id)',
  `subject` varchar(128) NOT NULL COMMENT 'Тема (заголовок) письма',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время создания = отправкиполучения',
  `body` text NOT NULL COMMENT 'Тело сообщения',
  `readed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Прочтено',
  `draft` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Сообщения пользователей (Внутренняя почта)';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `messages_state`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `messages_state` (
  `message_id` int(128) NOT NULL,
  `user_id` int(100) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Состояния сообщений пользователей';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `operation`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `operation` (
  `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид операции',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `money` decimal(20,2) NOT NULL COMMENT 'Деньги',
  `date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата операции',
  `cat_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид категории',
  `account_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид счёта',
  `drain` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Расход = 1, Доход = 0',
  `comment` text COMMENT 'Комментарий к операции',
  `transfer` int(255) unsigned DEFAULT '0' COMMENT 'Счёт, на который мы переводим денежку',
  `tr_id` bigint(255) unsigned DEFAULT NULL COMMENT 'Ид трансферта (номер транзакции)',
  `imp_date` datetime DEFAULT NULL,
  `imp_id` varchar(32) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL COMMENT 'Поле с тегами. Дублирует теги из таблицы тегов, но позволяет по быстрому получать все теги',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель',
  `exchange_rate` float NOT NULL DEFAULT '0',
  `dt_create` datetime NOT NULL COMMENT 'Дата и время создания проставляется в скрипте',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время модификации',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `periodic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `periodic` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид события',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `category` int(100) unsigned NOT NULL COMMENT 'Ид категории',
  `account` int(100) unsigned NOT NULL COMMENT 'Ид счёта',
  `drain` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1=расход, 0=доход',
  `title` varchar(255) NOT NULL COMMENT 'Заголовок',
  `date` date NOT NULL COMMENT 'Дата начала',
  `amount` decimal(20,2) NOT NULL COMMENT 'БАБКИ',
  `type_repeat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Тип повторений: 0 - Без повторения, 1 - Ежедневно, 3 - Каждый Пн., Ср. и Пт., 4 - Каждый Вт. и Чт., \n5 - По будням, 6 - По выходным, 7 - Еженедельно, 30 - Ежемесячно, 90 - Ежеквартально, 365 - Ежегодно',
  `count_repeat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Повторять каждые ... раз/дней/недель/месяцев\n',
  `comment` text COMMENT 'Комментарий к событию',
  `dt_create` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Когда создали',
  `dt_edit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последнее обновление',
  `infinity` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Повторять бесконечно',
  `last_date` date NOT NULL COMMENT 'Последняя дата события',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Периодическая транзакция';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `registration`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `registration` (
  `user_id` int(100) unsigned NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reg_id` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `system_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system_categories` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tags` (
  `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
  `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
  `name` varchar(50) NOT NULL COMMENT 'Имя тега',
  KEY `name_idx` (`name`),
  KEY `user_name_idx` (`user_id`,`name`),
  KEY `op_idx` (`oper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Теги';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `target`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `target` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ИД ',
  `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
  `category_id` int(10) unsigned NOT NULL COMMENT 'ИД категории',
  `title` varchar(255) NOT NULL COMMENT 'Заголовок, наименование цели',
  `type` enum('r','d') NOT NULL DEFAULT 'r' COMMENT 'Тип цели: r- расходная, d- доходная',
  `amount` decimal(20,2) NOT NULL COMMENT 'Сумма цели. Уточнить количество цифр, в сумме',
  `date_begin` date NOT NULL COMMENT 'Дата начала',
  `date_end` date NOT NULL COMMENT 'Дата окончания цели',
  `percent_done` tinyint(4) NOT NULL COMMENT 'Уровень достижения цели ',
  `forecast_done` tinyint(4) NOT NULL COMMENT ' Прогноз достижения цели',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Видимость в статистике общих целей',
  `photo` text NOT NULL COMMENT 'Ссылка на фотку',
  `url` text NOT NULL COMMENT 'Ссылка на страничку, или ещё куда',
  `comment` text NOT NULL COMMENT 'Описание цели',
  `target_account_id` int(255) unsigned NOT NULL COMMENT 'Счёт, где будут накапливаться деньги на фин.цель',
  `amount_done` decimal(20,2) NOT NULL COMMENT 'Уже накопленная сумма',
  `close` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `done` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_index` (`user_id`),
  KEY `date_end_index` (`date_end`),
  KEY `title_index` (`title`,`visible`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COMMENT='Финансовые цели';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `target_bill`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `target_bill` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `bill_id` int(255) unsigned NOT NULL COMMENT 'Ид счёта на котором храним',
  `target_id` int(255) unsigned NOT NULL COMMENT 'Ид финцели',
  `user_id` bigint(10) unsigned NOT NULL COMMENT 'Ид пользователя',
  `money` decimal(10,2) NOT NULL COMMENT 'Деньги, до 9 миллионов можно хранить',
  `dt` datetime NOT NULL COMMENT 'Дата и время пополнения финцели',
  `date` date NOT NULL COMMENT 'Дата операции',
  `comment` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `dt_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `target_idx` (`user_id`,`date`,`bill_id`),
  KEY `bill_idx` (`bill_id`),
  KEY `target_sidx` (`target_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='Виртуальный субсчёт для финансовой цели';
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Наш новый ИД для пользователей',
  `user_name` varchar(100) DEFAULT NULL COMMENT 'Псевдоним, который будет виден остальным на форуме',
  `user_login` varchar(100) NOT NULL COMMENT 'Логин пользователя',
  `user_pass` varchar(40) NOT NULL COMMENT 'Пароль пользователя в формате SHA1',
  `user_mail` varchar(100) DEFAULT NULL COMMENT 'Почта пользователя',
  `user_created` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата создания пользователя',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Если 0, значит забанен',
  `user_new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Если 1, значит новый',
  `user_currency_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Валюта пользователя по умолчанию',
  `user_currency_list` text NOT NULL COMMENT 'Сериализованный массив валют пользователя',
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'тип пользователя 0-юзер 1-админ 2-эксперт',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_login` (`user_login`,`user_pass`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `versions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `versions` (
  `id` int(10) unsigned NOT NULL COMMENT 'Ид версии скрипта апдейтера',
  `datetime` datetime NOT NULL COMMENT 'Время и дата создания SQL скрипта',
  `username` varchar(10) NOT NULL COMMENT 'Логин пользователя, кто создал скрипт',
  KEY `id_idx` (`id`),
  KEY `dt_idx` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Версии изменения базы данных';
SET character_set_client = @saved_cs_client;
