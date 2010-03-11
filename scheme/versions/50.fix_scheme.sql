# Корректирующий скрипт
# Удаляем все старые ненужные таблицы
DROP TABLE IF EXISTS `info_calc`, `info_desc`, `infopanel_desc`, `infopanel_users`, `infopanel_value`,
    `periodic`, `calendar`;

# Для оптимальной работы индексов, приводим их к одному виду
# По пользователям
ALTER TABLE `budget` MODIFY COLUMN `user_id` INT(100) UNSIGNED NOT NULL COMMENT 'ИД пользователя';
ALTER TABLE `target_bill` MODIFY COLUMN `user_id` INT(100) UNSIGNED NOT NULL COMMENT 'Ид пользователя';
# По категориям
ALTER TABLE `category` MODIFY COLUMN `cat_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Ид категории';

# Начинаем вести простейший версионинг БД
CREATE TABLE IF NOT EXISTS `versions` (
  `id` int(10) unsigned NOT NULL COMMENT 'Ид версии скрипта апдейтера',
  `datetime` datetime NOT NULL COMMENT 'Время и дата создания SQL скрипта',
  `username` varchar(10) NOT NULL COMMENT 'Логин пользователя, кто создал скрипт',
  KEY `id_idx` (`id`),
  KEY `dt_idx` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Версии изменения базы данных';
INSERT INTO versions VALUES('50', NOW(), 'ukko');

