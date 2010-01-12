CREATE TABLE  `referrers` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор партнёра (сайта)',
  `host` varchar(128) NOT NULL COMMENT 'УРл сайта - источника',
  `title` varchar(128) DEFAULT NULL COMMENT 'Для удобства отображения в будущем',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица партнёров (сайтов) - источников зарегистрированных п'

ALTER TABLE `users` 
ADD COLUMN `referrerId` int(8)  DEFAULT NULL 
COMMENT 'Идентификатор реферра пользователя при регистрации' 
AFTER `user_type`;
