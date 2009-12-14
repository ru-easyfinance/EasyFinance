CREATE TABLE  `easyfinance`.`messages` (
  `id` int(128) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор сообщения',
  `sender_id` int(100) NOT NULL COMMENT 'Идентификатор отправителя (user_id)',
  `receiver_id` int(100) NOT NULL COMMENT 'Идентификатор получателя (user_id)',
  `subject` varchar(128) NOT NULL COMMENT 'Тема (заголовок) письма',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Время создания = отправкиполучения',
  `body` text NOT NULL COMMENT 'Тело сообщения',
  `readed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Прочтено',
  `draft` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='Сообщения пользователей (Внутренняя почта)';

CREATE TABLE  `easyfinance`.`messages_state` (
  `message_id` int(128) NOT NULL,
  `user_id` int(100) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Состояния сообщений пользователей';	
