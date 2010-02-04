
CREATE TABLE  `versions` (
  `id` int(10) unsigned NOT NULL COMMENT 'Ид версии скрипта апдейтера',
  `datetime` datetime NOT NULL COMMENT 'Время и дата создания SQL скрипта',
  `username` varchar(10) NOT NULL COMMENT 'Логин пользователя, кто создал скрипт',
  KEY `id_idx` (`id`),
  KEY `dt_idx` (`datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Версии изменения базы данных'

INSERT INTO versions (`id`, `datetime`, `username`) VALUES(37, NOW(), 'ukko');

