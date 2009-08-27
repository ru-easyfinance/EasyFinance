DROP TABLE `periodic`;
CREATE TABLE  `periodic` (
      `id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Ид события',
      `user_id` INT(100) UNSIGNED NOT NULL COMMENT 'Ид пользователя',
      `category` INT(100) UNSIGNED NOT NULL COMMENT 'Ид категории',
      `account` INT(100) UNSIGNED NOT NULL COMMENT 'Ид счёта',
      `drain` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1=расход, 0=доход',
      `title` VARCHAR(255) NOT NULL COMMENT 'Заголовок',
      `date` DATE NOT NULL COMMENT 'Дата начала',
      `amount` DECIMAL(20,2) NOT NULL COMMENT 'БАБКИ',
      `type_repeat` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Тип повторений: 0 - Без повторения, 1 - Ежедневно, 3 - Каждый Пн., Ср. и Пт., 4 - Каждый Вт. и Чт., \n5 - По будням, 6 - По выходным, 7 - Еженедельно, 30 - Ежемесячно, 90 - Ежеквартально, 365 - Ежегодно',
      `count_repeat` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Повторять каждые ... раз/дней/недель/месяцев\n',
      `comment` TEXT COMMENT 'Комментарий к событию',
      `dt_create` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Когда создали',
      `dt_edit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последнее обновление',
      `infinity` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Повторять бесконечно',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Периодическая транзакция'
