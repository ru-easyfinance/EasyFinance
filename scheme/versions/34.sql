DROP TABLE `experts`, `experts_plugins`, `mail`;

CREATE TABLE  `certificates` (
  `cert_id` int(16) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор сертификата',
  `cert_user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя, users',
  `cert_img` varchar(128) NOT NULL COMMENT 'Изображение сертификата',
  `cert_img_thumb` varchar(128) NOT NULL COMMENT 'Превью изображения сертификата',
  `cert_details` varchar(64) NOT NULL COMMENT 'Комментарий',
  `cert_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Статус сертификата: 0 - в обработке, 1 - одобрен, 2 - не допущен',
  PRIMARY KEY (`cert_id`),
  KEY `cert_user_id` (`cert_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Сертификаты экспертов';

CREATE TABLE  `user_fields_expert` (
  `user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя (эксперта)\nuser.id',
  `user_info_short` text COMMENT 'Краткая информация ',
  `user_info_full` text COMMENT 'Полная информация',
  `user_img` varchar(128) DEFAULT NULL COMMENT 'Фотография эксперта',
  `user_img_thumb` varchar(128) DEFAULT NULL COMMENT 'Превью фотографии эксперта',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Дополнительные поля для экспертов';

CREATE TABLE  `services_expert` (
  `service_id` int(16) NOT NULL COMMENT 'Идентификатор услуги',
  `user_id` int(100) NOT NULL COMMENT 'Идентификатор пользователя',
  `service_price` int(64) NOT NULL COMMENT 'Цена услуги',
  `service_cur_id` int(11) NOT NULL COMMENT 'Идентификатор валюты',
  `service_term` int(8) NOT NULL COMMENT 'Срок исполнения услуги',
  PRIMARY KEY (`service_id`,`user_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Услуги эксперта';


