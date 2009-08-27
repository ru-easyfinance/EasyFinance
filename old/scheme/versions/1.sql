CREATE TABLE  `tags` (
      `id` bigint(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ИД',
      `user_id` int(100) unsigned NOT NULL COMMENT 'ИД пользователя',
      `oper_id` int(100) unsigned NOT NULL COMMENT 'Ид операции',
      `name` varchar(50) NOT NULL COMMENT 'Имя тега',
      PRIMARY KEY (`id`),
      KEY `name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Теги'
