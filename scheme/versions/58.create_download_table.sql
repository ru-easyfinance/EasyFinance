# Создаём таблицу где отмечаем кто и что загружает
CREATE TABLE  `downloads` (
  `id` int(100) NOT NULL auto_increment COMMENT 'Ид загрузки',
  `user_id` int(100) default NULL COMMENT 'Ид пользователя. 0 - аноним',
  `type` varchar(10) character set latin1 NOT NULL COMMENT 'Тип загрузки, идентификатор в виде строки',
  `dt` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Время загрузки',
  PRIMARY KEY  (`id`),
  KEY `user_idx` (`user_id`),
  KEY `type_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Чего и сколько загружено';

# Версия
INSERT INTO versions VALUES('58', NOW(), 'ukko');