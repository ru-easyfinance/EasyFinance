--
-- Структура таблицы `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(255) NOT NULL,
  `account_type_id` int(11) NOT NULL,
  `account_description` varchar(255) DEFAULT NULL,
  `account_currency_id` int(11) NOT NULL,
  `user_id` int(100) unsigned NOT NULL,
  PRIMARY KEY (`account_id`),
  KEY `FKaccounts554525` (`account_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `account_fields`
--

DROP TABLE IF EXISTS `account_fields`;
CREATE TABLE IF NOT EXISTS `account_fields` (
  `account_field_id` int(10) NOT NULL AUTO_INCREMENT,
  `account_typesaccount_type_id` int(11) NOT NULL,
  `field_descriptionsfield_description_id` int(10) NOT NULL,
  PRIMARY KEY (`account_field_id`),
  KEY `FKaccount_fi261530` (`field_descriptionsfield_description_id`),
  KEY `FKaccount_fi715328` (`account_typesaccount_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `account_field_descriptions`
--

DROP TABLE IF EXISTS `account_field_descriptions`;
CREATE TABLE IF NOT EXISTS `account_field_descriptions` (
  `field_description_id` int(20) NOT NULL AUTO_INCREMENT,
  `field_visual_name` varchar(150) DEFAULT NULL,
  `field_name` varchar(64) NOT NULL,
  `field_type` enum('numeric','string','text','html','enum','set') DEFAULT NULL,
  `field_regexp` text NOT NULL,
  `field_permissions` enum('view','select','input','add','hidden') NOT NULL,
  `field_default_value` text NOT NULL,
  PRIMARY KEY (`field_description_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `account_field_values`
--

DROP TABLE IF EXISTS `account_field_values`;
CREATE TABLE IF NOT EXISTS `account_field_values` (
  `field_value_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_fieldsaccount_field_id` int(11) NOT NULL,
  `int_value` int(11) DEFAULT NULL,
  `string_value` varchar(255) DEFAULT NULL,
  `date_value` date DEFAULT NULL,
  `accountsaccount_id` int(11) NOT NULL,
  PRIMARY KEY (`field_value_id`),
  KEY `FK_01` (`account_fieldsaccount_field_id`),
  KEY `FK_02` (`accountsaccount_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `account_types`
--

DROP TABLE IF EXISTS `account_types`;
CREATE TABLE IF NOT EXISTS `account_types` (
  `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `article` longtext NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `budget`
--

DROP TABLE IF EXISTS `budget`;
CREATE TABLE IF NOT EXISTS `budget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `bill_id` int(11) NOT NULL DEFAULT '0',
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `money` varchar(255) NOT NULL,
  `drain` int(11) NOT NULL DEFAULT '0',
  `date_from` date NOT NULL DEFAULT '0000-00-00',
  `date_to` date NOT NULL DEFAULT '0000-00-00',
  `comment` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `calendar`
--

DROP TABLE IF EXISTS `calendar`;
CREATE TABLE IF NOT EXISTS `calendar` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Календарь';

-- --------------------------------------------------------

--
-- Структура таблицы `categories_often`
--

DROP TABLE IF EXISTS `categories_often`;
CREATE TABLE IF NOT EXISTS `categories_often` (
  `user_id` int(100) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  `cnt` int(11) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `cur_id` int(11) NOT NULL AUTO_INCREMENT,
  `cur_name` varchar(20) NOT NULL DEFAULT '',
  `cur_char_code` varchar(15) NOT NULL,
  `cur_name_value` varchar(255) NOT NULL,
  PRIMARY KEY (`cur_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `daily_currency`
--

DROP TABLE IF EXISTS `daily_currency`;
CREATE TABLE IF NOT EXISTS `daily_currency` (
  `currency_id` int(11) NOT NULL,
  `currency_date` date NOT NULL,
  `currency_sum` varchar(11) NOT NULL,
  `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
  KEY `new_index` (`currency_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `experts`
--

DROP TABLE IF EXISTS `experts`;
CREATE TABLE IF NOT EXISTS `experts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(55) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `photo` varchar(40) DEFAULT NULL,
  `about` longtext NOT NULL,
  `voice_up` int(11) NOT NULL DEFAULT '0',
  `voice_down` int(11) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '0',
  `date_created` date NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_attach_content`
--

DROP TABLE IF EXISTS `experts_attach_content`;
CREATE TABLE IF NOT EXISTS `experts_attach_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expert_id` int(11) NOT NULL,
  `file_name` varchar(36) DEFAULT NULL,
  `about_file` longtext,
  `url_article` varchar(255) DEFAULT NULL,
  `article` longtext,
  `article_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_categories`
--

DROP TABLE IF EXISTS `experts_categories`;
CREATE TABLE IF NOT EXISTS `experts_categories` (
  `expert_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_cost`
--

DROP TABLE IF EXISTS `experts_cost`;
CREATE TABLE IF NOT EXISTS `experts_cost` (
  `expert_id` int(11) NOT NULL,
  `cost_id` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `desc` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_post`
--

DROP TABLE IF EXISTS `experts_post`;
CREATE TABLE IF NOT EXISTS `experts_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `from_expert_id` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `date_created` date NOT NULL,
  `report` longtext,
  `is_new` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_rank`
--

DROP TABLE IF EXISTS `experts_rank`;
CREATE TABLE IF NOT EXISTS `experts_rank` (
  `user_id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `voice_up` int(1) NOT NULL DEFAULT '0',
  `voice_down` int(1) NOT NULL DEFAULT '0',
  `topic_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_review`
--

DROP TABLE IF EXISTS `experts_review`;
CREATE TABLE IF NOT EXISTS `experts_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `expert_id` int(11) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `review` longtext NOT NULL,
  `review_date` date NOT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `experts_topic`
--

DROP TABLE IF EXISTS `experts_topic`;
CREATE TABLE IF NOT EXISTS `experts_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `expert_id` int(32) NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `cost_id` int(11) NOT NULL DEFAULT '0',
  `date_created` date NOT NULL,
  `is_new` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `infopanel_desc`
--

DROP TABLE IF EXISTS `infopanel_desc`;
CREATE TABLE IF NOT EXISTS `infopanel_desc` (
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') NOT NULL,
  `start` int(1) NOT NULL,
  `end` int(1) NOT NULL,
  `desc` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `infopanel_users`
--

DROP TABLE IF EXISTS `infopanel_users`;
CREATE TABLE IF NOT EXISTS `infopanel_users` (
  `user_id` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') DEFAULT NULL,
  `settings` text,
  `state` enum('0','1','2') DEFAULT NULL,
  `order` enum('0','1','2') DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `infopanel_value`
--

DROP TABLE IF EXISTS `infopanel_value`;
CREATE TABLE IF NOT EXISTS `infopanel_value` (
  `uid` int(100) unsigned NOT NULL,
  `type` enum('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat','akc_year','pif_year','ofbu_year','oms_year','estat_year') NOT NULL,
  `date` date DEFAULT NULL,
  `value` int(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `operation`
--

DROP TABLE IF EXISTS `operation`;
CREATE TABLE IF NOT EXISTS `operation` (
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
  `type` tinyint(1) unsigned NOT NULL COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель',
  `dt_create` datetime NOT NULL COMMENT 'Дата и время создания проставляется в скрипте',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время модификации',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `periodic`
--

DROP TABLE IF EXISTS `periodic`;
CREATE TABLE IF NOT EXISTS `periodic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `user_id` text NOT NULL COMMENT 'Ид пользователя',
  `bill_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Ид счёта',
  `period` text NOT NULL,
  `date_from` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата с которой начинается отсчёт',
  `povtor` int(11) NOT NULL DEFAULT '0' COMMENT 'Повторять?',
  `insert` text NOT NULL,
  `remind` int(11) NOT NULL DEFAULT '0',
  `remind_num` int(11) NOT NULL DEFAULT '0',
  `drain` int(11) NOT NULL DEFAULT '1',
  `money` int(11) NOT NULL DEFAULT '0',
  `cat_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Ид категории',
  `comment` mediumtext NOT NULL COMMENT 'Комментарий',
  `povtor_num` int(11) NOT NULL DEFAULT '0' COMMENT 'Количество повторов',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_accounts`
--

DROP TABLE IF EXISTS `plan_accounts`;
CREATE TABLE IF NOT EXISTS `plan_accounts` (
  `user_id` varchar(32) NOT NULL,
  `account_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_fact`
--

DROP TABLE IF EXISTS `plan_fact`;
CREATE TABLE IF NOT EXISTS `plan_fact` (
  `plan_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `drain` int(1) NOT NULL DEFAULT '1',
  `total_sum` decimal(11,2) DEFAULT '0.00',
  `total_sum_plan` decimal(11,2) DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_settings`
--

DROP TABLE IF EXISTS `plan_settings`;
CREATE TABLE IF NOT EXISTS `plan_settings` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(32) NOT NULL,
  `planning_horizon` int(1) NOT NULL,
  `planning_control` int(1) NOT NULL,
  `total_income` decimal(11,2) NOT NULL DEFAULT '0.00',
  `is_detalize_income` int(1) NOT NULL DEFAULT '0',
  `total_outcome` decimal(11,2) NOT NULL DEFAULT '0.00',
  `is_detalize_outcome` int(1) NOT NULL DEFAULT '0',
  `date_start_plan` date NOT NULL,
  `date_finish_plan` date NOT NULL,
  `check_is_p_operations` int(1) NOT NULL DEFAULT '0',
  `notice_sms` int(1) NOT NULL DEFAULT '0',
  `notice_email` int(1) NOT NULL DEFAULT '0',
  `comment` longtext NOT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `registration`
--

DROP TABLE IF EXISTS `registration`;
CREATE TABLE IF NOT EXISTS `registration` (
  `user_id` int(100) unsigned NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reg_id` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `system_categories`
--

DROP TABLE IF EXISTS `system_categories`;
CREATE TABLE IF NOT EXISTS `system_categories` (
  `system_category_id` int(255) NOT NULL AUTO_INCREMENT,
  `system_category_name` varchar(255) NOT NULL,
  `system_group_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`system_category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `system_categories_group`
--

DROP TABLE IF EXISTS `system_categories_group`;
CREATE TABLE IF NOT EXISTS `system_categories_group` (
  `system_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_group_name` varchar(255) NOT NULL,
  PRIMARY KEY (`system_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `system_experts_categories`
--

DROP TABLE IF EXISTS `system_experts_categories`;
CREATE TABLE IF NOT EXISTS `system_experts_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `system_experts_cost`
--

DROP TABLE IF EXISTS `system_experts_cost`;
CREATE TABLE IF NOT EXISTS `system_experts_cost` (
  `cost_id` int(11) NOT NULL AUTO_INCREMENT,
  `cost_name` varchar(255) NOT NULL,
  PRIMARY KEY (`cost_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Структура таблицы `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
  `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
  `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
  `name` varchar(50) NOT NULL COMMENT 'Имя тега',
  KEY `name_idx` (`name`),
  KEY `user_name_idx` (`user_id`,`name`),
  KEY `op_idx` (`oper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Теги';

-- --------------------------------------------------------

--
-- Структура таблицы `target`
--

DROP TABLE IF EXISTS `target`;
CREATE TABLE IF NOT EXISTS `target` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Финансовые цели';

-- --------------------------------------------------------

--
-- Структура таблицы `target_bill`
--

DROP TABLE IF EXISTS `target_bill`;
CREATE TABLE IF NOT EXISTS `target_bill` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `bill_id` int(255) unsigned NOT NULL COMMENT 'Ид счёта на котором храним',
  `target_id` int(255) unsigned NOT NULL COMMENT 'Ид финцели',
  `user_id` varchar(32) NOT NULL COMMENT 'Ид пользователя',
  `money` decimal(10,2) NOT NULL COMMENT 'Деньги, до 9 миллионов можно хранить',
  `dt` datetime NOT NULL COMMENT 'Дата и время пополнения финцели',
  `date` date NOT NULL COMMENT 'Дата операции',
  `comment` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL COMMENT 'Теги операции',
  `dt_create` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'таймштамп создания',
  `dt_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'таймштамп обновления',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Виртуальный субсчёт для финансовой цели';

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Наш новый ИД для пользователей',
  `user_name` varchar(100) DEFAULT NULL COMMENT 'Псевдоним, который будет виден остальным на форуме',
  `user_login` varchar(100) NOT NULL COMMENT 'Логин пользователя',
  `user_pass` varchar(40) NOT NULL COMMENT 'Пароль пользователя в формате SHA1',
  `user_mail` varchar(100) DEFAULT NULL COMMENT 'Почта пользователя',
  `user_created` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата создания пользователя',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Если 0, значит забанен',
  `user_new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Если 1, значит новый',
  `user_currency_default` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Валюта пользователя по умолчанию',
  `user_currency_list` varchar(255) NOT NULL DEFAULT 'a:2:{i:0;i:1;i:1;i:2;}' COMMENT 'Сериализованный массив валют пользователя',
  PRIMARY KEY (`id`),
  KEY `user_login` (`user_login`,`user_pass`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
