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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;


LOCK TABLES `account_field_descriptions` WRITE;
INSERT INTO `account_field_descriptions` VALUES (1,'Название','name','string','','input',''),(2,'Доступный остаток','demand_balance','numeric','[0-9]+','input',''),(3,'Зарезервировано','reserve','numeric','[0-9]+','input',''),(4,'Годовой процент','annual_percentage','numeric','[0-9]+','input',''),(5,'Примечание','description','html','','input',''),(6,'Начальный баланс','starter_balance','numeric',' 	[0-9]+ ','input',''),(7,'Общий баланс','total_balance','numeric',' 	[0-9]+ ','input',''),(8,'Банк','bank_name','string','','input',''),(9,'Тип карты','card_type','string','','input',''),(10,'Срок действия','card_evaluation_period','string','','input',''),(11,'Кредитный лимит','credit_limit','numeric',' 	[0-9]+ ','input',''),(12,'Грейс период','grace_period','string','','input',''),(13,'Дата выписки','statement_date','string','','input',''),(14,'Использованный кредит','used_credit','string',' 	[0-9]+','input',''),(15,'Доступный остаток','free_limit','string',' 	[0-9]+','input','');
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=564 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `account_field_values` WRITE;
INSERT INTO `account_field_values` VALUES (438,48,NULL,'Альфачка',NULL,17),(439,48,39000,NULL,NULL,5),(440,48,0,NULL,NULL,28),(441,48,7,NULL,NULL,19),(442,48,NULL,'',NULL,24),(443,48,0,NULL,NULL,12),(444,48,NULL,'',NULL,18),(445,48,NULL,'',NULL,22),(446,48,NULL,'',NULL,25),(447,48,0,NULL,NULL,16),(448,49,NULL,'Альфачка',NULL,17),(449,49,39000,NULL,NULL,5),(450,49,0,NULL,NULL,28),(451,49,7,NULL,NULL,19),(452,49,NULL,'',NULL,24),(453,49,0,NULL,NULL,12),(454,49,NULL,'',NULL,18),(455,49,NULL,'',NULL,22),(456,49,NULL,'',NULL,25),(457,49,0,NULL,NULL,16),(458,50,NULL,'123',NULL,17),(459,50,123,NULL,NULL,5),(460,50,123,NULL,NULL,28),(461,50,123,NULL,NULL,19),(462,50,NULL,'123',NULL,24),(463,50,123,NULL,NULL,12),(464,50,NULL,'123',NULL,18),(465,50,NULL,'123',NULL,22),(466,50,NULL,'123',NULL,25),(467,50,123,NULL,NULL,16),(468,51,NULL,'ertwe',NULL,17),(469,51,NULL,'34523',NULL,18),(470,51,2345,NULL,NULL,19),(471,51,2345,NULL,NULL,20),(472,51,NULL,'2345',NULL,21),(473,51,NULL,'2345',NULL,22),(474,51,NULL,'2345',NULL,23),(475,51,NULL,'2345',NULL,24),(476,51,NULL,'2345',NULL,25),(477,51,NULL,'2345',NULL,26),(478,51,NULL,'2345',NULL,27),(479,51,234,NULL,NULL,28),(480,52,NULL,'ertwe',NULL,17),(481,52,NULL,'34523',NULL,18),(482,52,2345,NULL,NULL,19),(483,52,2345,NULL,NULL,20),(484,52,NULL,'2345',NULL,21),(485,52,NULL,'2345',NULL,22),(486,52,NULL,'2345',NULL,23),(487,52,NULL,'2345',NULL,24),(488,52,NULL,'2345',NULL,25),(489,52,NULL,'2345',NULL,26),(490,52,NULL,'2345',NULL,27),(491,52,234,NULL,NULL,28),(492,53,NULL,'12312',NULL,17),(493,53,0,NULL,NULL,5),(494,53,0,NULL,NULL,28),(495,53,0,NULL,NULL,19),(496,53,NULL,'',NULL,24),(497,53,0,NULL,NULL,12),(498,53,NULL,'',NULL,18),(499,53,NULL,'',NULL,22),(500,53,NULL,'',NULL,25),(501,53,0,NULL,NULL,16),(502,54,NULL,'12312',NULL,17),(503,54,0,NULL,NULL,5),(504,54,0,NULL,NULL,28),(505,54,0,NULL,NULL,19),(506,54,NULL,'',NULL,24),(507,54,0,NULL,NULL,12),(508,54,NULL,'',NULL,18),(509,54,NULL,'',NULL,22),(510,54,NULL,'',NULL,25),(511,54,0,NULL,NULL,16),(512,55,NULL,'12341234',NULL,17),(513,55,0,NULL,NULL,5),(514,55,0,NULL,NULL,28),(515,55,0,NULL,NULL,19),(516,55,NULL,'',NULL,24),(517,55,0,NULL,NULL,12),(518,55,NULL,'',NULL,18),(519,55,NULL,'',NULL,22),(520,55,NULL,'',NULL,25),(521,55,0,NULL,NULL,16),(522,56,NULL,'12341234',NULL,17),(523,56,0,NULL,NULL,5),(524,56,0,NULL,NULL,28),(525,56,0,NULL,NULL,19),(526,56,NULL,'',NULL,24),(527,56,0,NULL,NULL,12),(528,56,NULL,'',NULL,18),(529,56,NULL,'',NULL,22),(530,56,NULL,'',NULL,25),(531,56,0,NULL,NULL,16),(532,57,NULL,'5678',NULL,17),(533,57,0,NULL,NULL,5),(534,57,0,NULL,NULL,28),(535,57,0,NULL,NULL,19),(536,57,NULL,'',NULL,24),(537,57,0,NULL,NULL,12),(538,57,NULL,'',NULL,18),(539,57,NULL,'',NULL,22),(540,57,NULL,'',NULL,25),(541,57,0,NULL,NULL,16),(542,58,NULL,'',NULL,17),(543,58,0,NULL,NULL,5),(544,58,0,NULL,NULL,28),(545,58,0,NULL,NULL,19),(546,58,NULL,'',NULL,24),(547,58,0,NULL,NULL,12),(548,58,NULL,'',NULL,18),(549,58,NULL,'',NULL,22),(550,58,NULL,'',NULL,25),(551,58,0,NULL,NULL,16),(552,54,NULL,'Налики',NULL,17),(553,54,5000,NULL,NULL,5),(554,54,NULL,'Примечание к кошельку',NULL,24),(555,54,0,NULL,NULL,12),(556,54,0,NULL,NULL,28),(557,54,0,NULL,NULL,16),(558,55,NULL,'Багз',NULL,17),(559,55,500,NULL,NULL,5),(560,55,NULL,'',NULL,24),(561,55,0,NULL,NULL,12),(562,55,0,NULL,NULL,28),(563,55,0,NULL,NULL,16);
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `account_fields` WRITE;
INSERT INTO `account_fields` VALUES (1,1,1),(2,1,2),(3,1,5),(4,2,1),(5,2,2),(6,2,3),(7,2,4),(8,2,5),(9,1,6),(10,1,3),(11,1,7),(12,2,6),(13,2,8),(14,2,9),(15,2,10),(16,2,7),(17,8,1),(18,8,8),(19,8,4),(20,8,11),(21,8,12),(22,8,9),(23,8,13),(24,8,5),(25,8,10),(26,8,14),(27,8,15),(28,8,3);
UNLOCK TABLES;

DROP TABLE IF EXISTS `account_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `account_types` (
  `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `account_types` WRITE;
INSERT INTO `account_types` VALUES (1,'Наличные'),(2,'Дебетовая карта'),(5,'Депозит'),(6,'Займ выданный'),(7,'Займ полученый'),(8,'Кредитная карта'),(9,'Кредит'),(10,'Металлический счет'),(11,'Акции'),(12,'ПИФ'),(13,'ОФБУ'),(14,'Имущество'),(15,'Электронный кошелек');
UNLOCK TABLES;

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
  KEY `FKaccounts554525` (`account_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `articles`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `article` longtext NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `budget`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `budget` (
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
) ENGINE=MyISAM AUTO_INCREMENT=2267 DEFAULT CHARSET=utf8;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4183 DEFAULT CHARSET=utf8 COMMENT='Календарь';
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `categories_often`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories_often` (
  `user_id` int(100) unsigned NOT NULL,
  `category_id` int(11) NOT NULL,
  `cnt` int(11) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `category`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `category` (
  `cat_id` int(255) NOT NULL AUTO_INCREMENT,
  `cat_parent` int(255) NOT NULL DEFAULT '0',
  `user_id` int(100) unsigned NOT NULL,
  `system_category_id` int(11) NOT NULL DEFAULT '0',
  `cat_name` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `cat_active` int(11) NOT NULL DEFAULT '1',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `often` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `currency`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `currency` (
  `cur_id` int(11) NOT NULL AUTO_INCREMENT,
  `cur_name` varchar(20) NOT NULL DEFAULT '',
  `cur_char_code` varchar(15) NOT NULL,
  `cur_name_value` varchar(255) NOT NULL,
  PRIMARY KEY (`cur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `currency` WRITE;
INSERT INTO `currency` VALUES (1,'руб.','RUB','Российский рубль'),(2,'$','USD','Доллар США'),(3,'&euro;','EUR','Евро'),(4,'грн.','UAH','Украинских гривен');
UNLOCK TABLES;

DROP TABLE IF EXISTS `daily_currency`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `daily_currency` (
  `currency_id` int(11) NOT NULL,
  `currency_date` date NOT NULL,
  `currency_sum` varchar(11) NOT NULL,
  `direction` enum('+','-','0') NOT NULL COMMENT 'Направление роста валюты. + = растёт, - = падает, 0 = без изменений',
  KEY `new_index` (`currency_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts` (
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_attach_content`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_attach_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expert_id` int(11) NOT NULL,
  `file_name` varchar(36) DEFAULT NULL,
  `about_file` longtext,
  `url_article` varchar(255) DEFAULT NULL,
  `article` longtext,
  `article_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_categories` (
  `expert_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_cost`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_cost` (
  `expert_id` int(11) NOT NULL,
  `cost_id` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `desc` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_post`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `from_expert_id` int(11) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `date_created` date NOT NULL,
  `report` longtext,
  `is_new` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_rank`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_rank` (
  `user_id` int(11) NOT NULL,
  `expert_id` int(11) NOT NULL,
  `voice_up` int(1) NOT NULL DEFAULT '0',
  `voice_down` int(1) NOT NULL DEFAULT '0',
  `topic_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_review`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `expert_id` int(11) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `review` longtext NOT NULL,
  `review_date` date NOT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `experts_topic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `experts_topic` (
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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
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
  `tr_id` bigint(255) unsigned DEFAULT NULL COMMENT 'Ид трансферта, только вот зачем он нам',
  `imp_date` datetime DEFAULT NULL,
  `imp_id` varchar(32) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `periodic`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `periodic` (
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
) ENGINE=MyISAM AUTO_INCREMENT=966 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `plan_accounts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plan_accounts` (
  `user_id` varchar(32) NOT NULL,
  `account_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `plan_fact`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plan_fact` (
  `plan_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `drain` int(1) NOT NULL DEFAULT '1',
  `total_sum` decimal(11,2) DEFAULT '0.00',
  `total_sum_plan` decimal(11,2) DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `plan_settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plan_settings` (
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
) ENGINE=MyISAM AUTO_INCREMENT=139 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `registration`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `registration` (
  `user_id` int(100) unsigned NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reg_id` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `system_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system_categories` (
  `system_category_id` int(255) NOT NULL AUTO_INCREMENT,
  `system_category_name` varchar(255) NOT NULL,
  `system_group_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`system_category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `system_categories` WRITE;
INSERT INTO `system_categories` VALUES (1,'Коммунальные услуги',0,0),(2,'Личные расходы',0,0),(3,'Обучение',0,0),(4,'Одежда',0,0),(5,'Налоги',0,0),(6,'Налоги2',1,0),(7,'Налоги4',0,0);
UNLOCK TABLES;

DROP TABLE IF EXISTS `system_categories_group`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system_categories_group` (
  `system_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_group_name` varchar(255) NOT NULL,
  PRIMARY KEY (`system_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

LOCK TABLES `system_categories_group` WRITE;
INSERT INTO `system_categories_group` VALUES (1,'комунальные');
UNLOCK TABLES;

DROP TABLE IF EXISTS `system_experts_categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system_experts_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `system_experts_cost`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `system_experts_cost` (
  `cost_id` int(11) NOT NULL AUTO_INCREMENT,
  `cost_name` varchar(255) NOT NULL,
  PRIMARY KEY (`cost_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `tags`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `tags` (
  `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ИД',
  `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
  `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
  `name` varchar(50) NOT NULL COMMENT 'Имя тега',
  PRIMARY KEY (`id`),
  KEY `name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='Теги';
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
  PRIMARY KEY (`id`),
  KEY `user_index` (`user_id`),
  KEY `date_end_index` (`date_end`),
  KEY `title_index` (`title`,`visible`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='Финансовые цели';
SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `target_bill`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `target_bill` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ид',
  `bill_id` int(255) unsigned NOT NULL COMMENT 'Ид счёта на котором храним',
  `target_id` int(255) unsigned NOT NULL COMMENT 'Ид финцели',
  `user_id` varchar(32) NOT NULL COMMENT 'Ид пользователя',
  `money` decimal(10,2) NOT NULL COMMENT 'Деньги, до 9 миллионов можно хранить',
  `dt` datetime NOT NULL COMMENT 'Дата и время пополнения финцели',
  `date` date NOT NULL COMMENT 'Дата операции',
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='Виртуальный субсчёт для финансовой цели';
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
  `user_currency_default` tinyint(1) unsigned NOT NULL COMMENT 'Валюта пользователя по умолчанию',
  `user_currency_list` varchar(255) NOT NULL COMMENT 'Сериализованный массив валют пользователя',
  PRIMARY KEY (`id`) ,
  KEY `user_login` (`user_login`,`user_pass`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;
