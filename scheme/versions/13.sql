DROP TABLE `periodic`;
CREATE TABLE  `periodic` (
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='Периодическая транзакция';
