<?php

/**
 * Импорт структуры из существующей базы
 */
class Migration001_InitialImport extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->rawQuery("SET NAMES utf8");

        $dump = "
            CREATE TABLE `users` (
              `id` int(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Наш новый ИД для пользователей',
              `user_name` varchar(100) DEFAULT NULL COMMENT 'Псевдоним, который будет виден остальным на форуме',
              `user_login` varchar(100) NOT NULL COMMENT 'Логин пользователя',
              `user_pass` varchar(40) NOT NULL COMMENT 'Пароль пользователя в формате SHA1',
              `user_mail` varchar(100) DEFAULT NULL COMMENT 'Почта пользователя',
              `user_created` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата создания пользователя',
              `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Если 0, значит забанен',
              `user_new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Если 1, значит новый',
              `getNotify` tinyint(4) NOT NULL DEFAULT '1',
              `user_currency_default` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Валюта пользователя по умолчанию',
              `user_currency_list` varchar(255) NOT NULL DEFAULT 'a:5:{i:0;s:1:\"1\";i:1;s:1:\"2\";i:2;s:1:\"3\";i:3;s:1:\"4\";i:4;s:1:\"6\";}' COMMENT 'Сериализованный массив валют пользователя',
              `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'тип пользователя 0-юзер 1-админ 2-эксперт',
              `user_service_mail` varchar(100) NOT NULL COMMENT 'Служебная личная почта пользователя на ресурсе',
              `referrerId` int(8) DEFAULT NULL COMMENT 'Идентификатор реферра пользователя при регистрации',
              PRIMARY KEY (`id`),
              KEY `user_login` (`user_login`,`user_pass`),
              KEY `id` (`id`),
              KEY `service_mail_idx` (`user_service_mail`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `Acc_ConnectionTypes` (
              `field_id` int(11) NOT NULL,
              `type_id` int(11) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='связи полей и типов счетов';


            CREATE TABLE `Acc_Fields` (
              `id` tinyint(1) unsigned NOT NULL COMMENT 'Ид поля',
              `name` varchar(50) NOT NULL COMMENT 'Техническое название поля',
              `description` varchar(50) NOT NULL COMMENT 'Описание поля',
              KEY `id_idx` (`id`),
              KEY `id_acct_idx` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Оределяем поля по типу счетов';


            CREATE TABLE `Acc_Object` (
              `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ИД счёта',
              `account_name` varchar(50) DEFAULT NULL COMMENT 'Название счёта',
              `account_type_id` tinyint(1) unsigned DEFAULT NULL COMMENT 'Ид типа счёта',
              `account_description` varchar(255) DEFAULT NULL COMMENT 'Описание - комментарий к счёту',
              `account_currency_id` int(1) unsigned DEFAULT NULL COMMENT 'Ид валюты счёта',
              `user_id` int(100) unsigned DEFAULT NULL COMMENT 'Ид пользователя',
              PRIMARY KEY (`account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица счетов с базовыми полями';


            CREATE TABLE `Acc_Values` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
              `field_id` int(10) unsigned NOT NULL COMMENT 'Ид поля',
              `field_value` varchar(255) DEFAULT NULL COMMENT 'Значение поля',
              `account_id` int(100) unsigned NOT NULL COMMENT 'Ид счёта',
              PRIMARY KEY (`id`),
              KEY `account_idx` (`account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Значения для дополнительных полей счетов';


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


            CREATE TABLE `account_fields` (
              `account_field_id` int(10) NOT NULL AUTO_INCREMENT,
              `account_typesaccount_type_id` int(11) NOT NULL,
              `field_descriptionsfield_description_id` int(10) NOT NULL,
              PRIMARY KEY (`account_field_id`),
              KEY `FKaccount_fi261530` (`field_descriptionsfield_description_id`),
              KEY `FKaccount_fi715328` (`account_typesaccount_type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `account_types` (
              `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
              `account_type_name` varchar(255) NOT NULL,
              PRIMARY KEY (`account_type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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


            CREATE TABLE `articles` (
              `id` int(16) NOT NULL AUTO_INCREMENT,
              `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `userId` int(100) NOT NULL,
              `authorName` varchar(64) DEFAULT NULL,
              `authorUrl` varchar(128) DEFAULT NULL,
              `title` varchar(256) NOT NULL,
              `description` varchar(256) DEFAULT NULL,
              `keywords` varchar(256) DEFAULT NULL,
              `announce` text NOT NULL,
              `body` longtext NOT NULL,
              `status` tinyint(4) NOT NULL,
              `image_id` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Статьи';


            CREATE TABLE `budget` (
              `key` varchar(50) NOT NULL COMMENT 'Ид пользователя + Ид категории + drain + Дата начала',
              `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
              `category` bigint(10) unsigned NOT NULL COMMENT 'ИД категории',
              `drain` tinyint(4) NOT NULL COMMENT '1 - расход, 0 - доход',
              `currency` int(10) unsigned NOT NULL COMMENT 'Валюта',
              `amount` decimal(20,2) NOT NULL COMMENT 'Сумма',
              `date_start` date NOT NULL COMMENT 'Дата начала периода',
              `date_end` date NOT NULL COMMENT 'Дата окончания периода',
              `dt_create` datetime NOT NULL COMMENT 'Дата создания',
              `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обновления',
              UNIQUE KEY `key_uniq` (`key`),
              KEY `start_idx` (`date_start`,`date_end`,`user_id`) USING BTREE,
              KEY `subs_idx1` (`date_start`,`category`,`user_id`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Бюджет';


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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Календарь';


            CREATE TABLE `calendar_chains` (
              `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид цепочки',
              `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
              `start` date NOT NULL COMMENT 'Дата начала',
              `last` date NOT NULL COMMENT 'Дата окончания',
              `every` int(1) unsigned DEFAULT NULL COMMENT 'Опционально, по-умолчанию 0 [0, 1, 7, 30, 90. 365] //без повторения, каждый день, каждую неделю, месяц, квартал, год\n',
              `repeat` int(1) unsigned DEFAULT NULL COMMENT 'Опционально, по-умолчанию 1, от 1 до 500.',
              `week` char(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0' COMMENT 'Опционально, Двоичная маска (0000011 - выходные, 1111100 - будни) В случае, если выбран период повторения - еженедельный. По-умолчанию - \"\"\n',
              PRIMARY KEY (`id`),
              KEY `user_idx` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Цепочка операций';


            CREATE TABLE `calendar_events` (
              `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT,
              `cal_id` bigint(100) unsigned NOT NULL,
              `date` date NOT NULL,
              `accept` tinyint(1) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `cal_idx` (`cal_id`),
              KEY `date_idx` (`date`,`cal_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='События календаря';


            CREATE TABLE `categories_often` (
              `user_id` int(100) unsigned NOT NULL,
              `category_id` int(11) NOT NULL,
              `cnt` int(11) NOT NULL,
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `category` (
              `cat_id` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид категории',
              `cat_parent` int(255) NOT NULL DEFAULT '0',
              `user_id` int(100) unsigned NOT NULL,
              `system_category_id` int(11) NOT NULL DEFAULT '0',
              `cat_name` varchar(255) NOT NULL,
              `type` tinyint(1) NOT NULL DEFAULT '0',
              `cat_active` int(11) NOT NULL DEFAULT '1',
              `visible` tinyint(1) NOT NULL DEFAULT '1',
              `custom` tinyint(1) NOT NULL COMMENT '1 - Создана пользователем, 0 - системная',
              `dt_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата и время создания категории',
              `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время обновления категории',
              PRIMARY KEY (`cat_id`),
              KEY `user_id` (`user_id`,`visible`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `certificates` (
              `cert_id` int(16) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор сертификата',
              `cert_user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя, users',
              `cert_img` varchar(128) NOT NULL COMMENT 'Изображение сертификата',
              `cert_img_thumb` varchar(128) NOT NULL COMMENT 'Превью изображения сертификата',
              `cert_details` varchar(64) NOT NULL COMMENT 'Комментарий',
              `cert_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Статус сертификата: 0 - в обработке, 1 - одобрен, 2 - не допущен',
              PRIMARY KEY (`cert_id`),
              KEY `cert_user_id` (`cert_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Сертификаты экспертов';


            CREATE TABLE `currency` (
              `cur_id` int(11) NOT NULL AUTO_INCREMENT,
              `cur_name` varchar(10) NOT NULL,
              `cur_char_code` varchar(15) NOT NULL,
              `cur_name_value` varchar(255) NOT NULL,
              `cur_okv_id` varchar(4) NOT NULL,
              `cur_country` varchar(255) NOT NULL COMMENT 'ИД страны',
              `cur_uses` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Обновлять курс',
              PRIMARY KEY (`cur_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `daily_currency` (
              `currency_id` int(11) NOT NULL,
              `currency_from` int(11) NOT NULL DEFAULT '1',
              `currency_date` date NOT NULL,
              `currency_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Системный курс валют',
              `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
              `currency_user_sum` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Пользовательский курс валют',
              `user_id` bigint(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид пользователя',
              KEY `new_index` (`currency_date`),
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `downloads` (
              `id` int(100) NOT NULL AUTO_INCREMENT COMMENT 'Ид загрузки',
              `user_id` int(100) DEFAULT NULL COMMENT 'Ид пользователя. 0 - аноним',
              `type` varchar(10) CHARACTER SET latin1 NOT NULL COMMENT 'Тип загрузки, идентификатор в виде строки',
              `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время загрузки',
              PRIMARY KEY (`id`),
              KEY `user_idx` (`user_id`),
              KEY `type_idx` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Чего и сколько загружено';


            CREATE TABLE `feedback_message` (
              `uid` int(100) NOT NULL COMMENT 'id пользователя',
              `user_settings` text NOT NULL COMMENT 'сис настройки пользователя',
              `messages` text NOT NULL COMMENT 'сообщение',
              `user_name` varchar(32) NOT NULL COMMENT 'имя пользователя',
              `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'новое',
              `rating` int(8) NOT NULL DEFAULT '0' COMMENT 'рэйтинг сообщения',
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс сообщений для быстрого пользователя',
              `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `close` tinyint(1) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица сообщений от тестеров';


            CREATE TABLE `images` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) NOT NULL,
              `path` text NOT NULL,
              `url` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='таблица ссылок на картинки';


            CREATE TABLE `images_articles` (
              `image_id` int(11) NOT NULL,
              `article_id` int(11) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='связь статей и изображений';


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


            CREATE TABLE `messages_state` (
              `message_id` int(128) NOT NULL,
              `user_id` int(100) NOT NULL,
              `trash` tinyint(1) NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Состояния сообщений пользователей';


            CREATE TABLE `operation` (
              `id` bigint(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид операции',
              `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
              `money` decimal(20,2) NOT NULL COMMENT 'Деньги',
              `time` time NOT NULL COMMENT 'Время операции',
              `date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата операции',
              `cat_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид категории',
              `account_id` int(255) unsigned NOT NULL DEFAULT '0' COMMENT 'Ид счёта',
              `drain` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Расход = 1, Доход = 0',
              `comment` text COMMENT 'Комментарий к операции',
              `transfer` int(255) unsigned DEFAULT '0' COMMENT 'Счёт, на который мы переводим денежку',
              `tr_id` bigint(255) unsigned DEFAULT NULL COMMENT 'Ид трансферта, только вот зачем он нам',
              `imp_id` decimal(20,2) DEFAULT NULL COMMENT 'Первоначальная сумма перевода до конвертации',
              `tags` varchar(255) DEFAULT NULL COMMENT 'Поле с тегами. Дублирует теги из таблицы тегов, но позволяет по быстрому получать все теги',
              `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель',
              `source_id` char(8) DEFAULT NULL,
              `accepted` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Подтверждена = 1, Черновик = 0',
              `chain_id` bigint(10) unsigned NOT NULL,
              `exchange_rate` decimal(12,6) unsigned NOT NULL DEFAULT '0.000000' COMMENT 'Курс обмена валюты',
              `dt_create` datetime NOT NULL COMMENT 'Дата и время создания проставляется в скрипте',
              `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время модификации',
              PRIMARY KEY (`id`),
              KEY `account_id` (`account_id`),
              KEY `user_id` (`user_id`),
              CONSTRAINT `operation_VS_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `referrers` (
              `id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор партнёра (сайта)',
              `host` varchar(128) NOT NULL COMMENT 'УРл сайта - источника',
              `title` varchar(128) DEFAULT NULL COMMENT 'Для удобства отображения в будущем',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица партнёров (сайтов) - источников зарегистрированных п';


            CREATE TABLE `registration` (
              `user_id` int(100) unsigned NOT NULL,
              `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `reg_id` varchar(40) NOT NULL,
              KEY `user_idx` (`user_id`),
              KEY `reg_idx` (`reg_id`),
              KEY `date_idx` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `services` (
              `service_id` int(16) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор услуги',
              `service_name` varchar(32) NOT NULL COMMENT 'Название услуги',
              `service_desc` varchar(255) NOT NULL COMMENT 'Описание услуги',
              `service_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Индикатор доступности услуги (включена 1, выключена 0)',
              PRIMARY KEY (`service_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Услуги экспертов';


            CREATE TABLE `services_expert` (
              `service_id` int(16) NOT NULL COMMENT 'Идентификатор услуги',
              `user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя',
              `service_price` int(64) NOT NULL COMMENT 'Цена услуги',
              `service_cur_id` int(11) NOT NULL COMMENT 'Идентификатор валюты',
              `service_term` int(8) NOT NULL COMMENT 'Срок исполнения услуги',
              PRIMARY KEY (`service_id`,`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Услуги эксперта';


            CREATE TABLE `source_operations` (
              `operation_id` bigint(20) unsigned NOT NULL DEFAULT '0',
              `source_uid` char(8) NOT NULL,
              `source_operation_uid` varchar(32) NOT NULL,
              PRIMARY KEY (`operation_id`),
              UNIQUE KEY `source_operation_idx` (`source_uid`,`source_operation_uid`),
              CONSTRAINT `source_operation_VS_operation` FOREIGN KEY (`operation_id`) REFERENCES `operation` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `system_categories` (
              `id` int(255) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              KEY `name_idx` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            CREATE TABLE `tags` (
              `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
              `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
              `name` varchar(50) NOT NULL COMMENT 'Имя тега',
              KEY `name_idx` (`name`),
              KEY `user_name_idx` (`user_id`,`name`),
              KEY `op_idx` (`oper_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Теги';


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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Финансовые цели';


            CREATE TABLE `target_bill` (
              `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
              `bill_id` int(255) unsigned NOT NULL COMMENT 'Ид счёта на котором храним',
              `target_id` int(255) unsigned NOT NULL COMMENT 'Ид финцели',
              `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
              `money` decimal(10,2) NOT NULL COMMENT 'Деньги, до 9 миллионов можно хранить',
              `dt` datetime NOT NULL COMMENT 'Дата и время пополнения финцели',
              `date` date NOT NULL COMMENT 'Дата операции',
              `comment` varchar(255) NOT NULL,
              `tags` varchar(255) NOT NULL COMMENT 'Теги операции',
              `chain_id` bigint(100) unsigned NOT NULL DEFAULT '0',
              `accepted` tinyint(1) unsigned NOT NULL DEFAULT '1',
              `dt_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'таймштамп создания',
              `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'таймштамп обновления',
              PRIMARY KEY (`id`),
              KEY `bill_idx` (`bill_id`),
              KEY `target_idx` (`user_id`,`date`,`bill_id`),
              KEY `target_sidx` (`target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Виртуальный субсчёт для финансовой цели';


            CREATE TABLE `user_fields_expert` (
              `user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя (эксперта)\nuser.id',
              `user_info_short` text COMMENT 'Краткая информация ',
              `user_info_full` text COMMENT 'Полная информация',
              `user_img` varchar(128) DEFAULT NULL COMMENT 'Фотография эксперта',
              `user_img_thumb` varchar(128) DEFAULT NULL COMMENT 'Превью фотографии эксперта',
              PRIMARY KEY (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Дополнительные поля для экспертов';


            CREATE TABLE `versions` (
              `id` int(10) unsigned NOT NULL COMMENT 'Ид версии скрипта апдейтера',
              `datetime` datetime NOT NULL COMMENT 'Время и дата создания SQL скрипта',
              `username` varchar(10) NOT NULL COMMENT 'Логин пользователя, кто создал скрипт',
              KEY `id_idx` (`id`),
              KEY `dt_idx` (`datetime`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Версии изменения базы данных';
        ";

        foreach (explode(";\n", $dump) as $query) {
            $query = trim($query);
            if ($query) {
                $this->rawQuery($query);
            }
        }
    }


    /**
     * Down
     */
    public function down()
    {
        $this->rawQuery("
        DROP TABLE IF EXISTS
            Acc_ConnectionTypes,
            Acc_Fields,
            Acc_Object,
            Acc_Values,
            account_field_descriptions,
            account_field_values,
            account_fields,
            account_types,
            accounts,
            articles,
            budget,
            calend,
            calendar_chains,
            calendar_events,
            categories_often,
            category,
            certificates,
            currency,
            daily_currency,
            downloads,
            feedback_message,
            images,
            images_articles,
            messages,
            messages_state,
            source_operations,
            operation,
            referrers,
            registration,
            services,
            services_expert,
            system_categories,
            tags,
            target,
            target_bill,
            user_fields_expert,
            users,
            versions;
        ");
    }

}
