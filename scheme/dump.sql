DROP TABLE IF EXISTS`account_field_descriptions`;
CREATE TABLE `account_field_descriptions` (
  `field_description_id` int(20) NOT NULL AUTO_INCREMENT,
  `field_visual_name` varchar(150) DEFAULT NULL,
  `field_name` varchar(64) NOT NULL,
  `field_type` enum('numeric','string','text','html','enum','set') DEFAULT NULL,
  `field_regexp` text NOT NULL,
  `field_permissions` enum('view','select','input','add','hidden') NOT NULL,
  `field_default_value` text NOT NULL,
  PRIMARY KEY (`field_description_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`account_field_values`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`account_fields`;
CREATE TABLE `account_fields` (
  `account_field_id` int(10) NOT NULL AUTO_INCREMENT,
  `account_typesaccount_type_id` int(11) NOT NULL,
  `field_descriptionsfield_description_id` int(10) NOT NULL,
  PRIMARY KEY (`account_field_id`),
  KEY `FKaccount_fi261530` (`field_descriptionsfield_description_id`),
  KEY `FKaccount_fi715328` (`account_typesaccount_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`account_types`;
CREATE TABLE `account_types` (
  `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`accounts`;
CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `account_description` varchar(255) DEFAULT NULL,
  `account_currency_id` int(11) NOT NULL,
  `user_id` int(100) unsigned NOT NULL,
  PRIMARY KEY (`account_id`),
  KEY `FKaccounts554525` (`account_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`calendar`;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Календарь';

DROP TABLE  IF EXISTS`category`;
CREATE TABLE `category` (
  `cat_id` int(255) NOT NULL AUTO_INCREMENT COMMENT 'Ид категории',
  `cat_parent` int(255) NOT NULL DEFAULT '0' COMMENT 'Ид родительской категории (если 0, то она сам себе родитель)',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `system_category_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Ид системной категории',
  `cat_name` varchar(255) NOT NULL COMMENT 'Имя категории',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Тип категории (-1 - расходная, 0 - универсальная, 1 - доходная)',
  `cat_active` int(11) NOT NULL DEFAULT '1' COMMENT 'Активна ли категория? WTF???',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Видимость категории. 0 - не видно, 1 - видно',
  `often` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Частота использования (Уточнить)',
  `dt_create` datetime NOT NULL COMMENT 'Таймштамп при создании',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Таймштамп при обновлении',
  PRIMARY KEY (`cat_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

DROP TABLE  IF EXISTS`currency`;
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

DROP TABLE  IF EXISTS`daily_currency`;
CREATE TABLE `daily_currency` (
      `currency_id` int(11) NOT NULL,
      `currency_date` date NOT NULL,
      `currency_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Системный курс валют',
      `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
      `currency_user_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Пользовательский курс валют',
      `user_id` bigint(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид пользователя',
      KEY `new_index` (`currency_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE  IF EXISTS`feedback_message`;
CREATE TABLE `feedback_message` (
  `uid` int(100) NOT NULL COMMENT 'id пользователя',
  `user_settings` text NOT NULL COMMENT 'сис настройки пользователя',
  `messages` text NOT NULL COMMENT 'сообщение',
  `user_name` varchar(32) NOT NULL COMMENT 'имя пользователя',
  `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'новое',
  `rating` int(8) NOT NULL DEFAULT '0' COMMENT 'рэйтинг сообщения',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс сообщений для быстрого пользователя',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица сообщений от тестеров';

DROP TABLE  IF EXISTS`infopanel_desc`;
CREATE TABLE `infopanel_desc` (
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') NOT NULL,
  `start` int(1) NOT NULL,
  `end` int(1) NOT NULL,
  `desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`infopanel_users`;
CREATE TABLE `infopanel_users` (
  `user_id` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') DEFAULT NULL,
  `settings` text,
  `state` enum('0','1','2') DEFAULT NULL,
  `order` enum('0','1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`infopanel_value`;
CREATE TABLE `infopanel_value` (
  `uid` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat','akc_year','pif_year','ofbu_year','oms_year','estat_year') NOT NULL,
  `dete` date NOT NULL,
  `value` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`operation`;
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
  `tr_id` bigint(255) unsigned DEFAULT NULL COMMENT 'Ид трансферта, только вот зачем он нам',
  `imp_date` datetime DEFAULT NULL,
  `imp_id` varchar(32) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL COMMENT 'Поле с тегами. Дублирует теги из таблицы тегов, но позволяет по быстрому получать все теги',
  `type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель',
  `dt_create` datetime NOT NULL COMMENT 'Дата и время создания проставляется в скрипте',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время модификации',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`periodic`;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Периодическая транзакция';

DROP TABLE  IF EXISTS`registration`;
CREATE TABLE `registration` (
  `user_id` int(100) unsigned NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reg_id` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`system_categories`;
CREATE TABLE `system_categories` (
  `system_category_id` int(255) NOT NULL AUTO_INCREMENT,
  `system_category_name` varchar(255) NOT NULL,
  `system_group_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`system_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE  IF EXISTS`tags`;
CREATE TABLE `tags` (
  `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
  `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
  `name` varchar(50) NOT NULL COMMENT 'Имя тега',
  KEY `name_idx` (`name`),
  KEY `user_name_idx` (`user_id`,`name`),
  KEY `op_idx` (`oper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Теги';

DROP TABLE  IF EXISTS`target`;
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
  PRIMARY KEY (`id`),
  KEY `user_index` (`user_id`),
  KEY `date_end_index` (`date_end`),
  KEY `title_index` (`title`,`visible`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Финансовые цели';

DROP TABLE  IF EXISTS`target_bill`;
CREATE TABLE `target_bill` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `bill_id` int(255) unsigned NOT NULL COMMENT 'Ид счёта на котором храним',
  `target_id` int(255) unsigned NOT NULL COMMENT 'Ид финцели',
  `user_id` varchar(32) NOT NULL COMMENT 'Ид пользователя',
  `money` decimal(10,2) NOT NULL COMMENT 'Деньги, до 9 миллионов можно хранить',
  `dt` datetime NOT NULL COMMENT 'Дата и время пополнения финцели',
  `date` date NOT NULL COMMENT 'Дата операции',
  `comment` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `dt_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Виртуальный субсчёт для финансовой цели';
