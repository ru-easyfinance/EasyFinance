# Создаём таблицу для хранения цепочек операций
CREATE TABLE `calendar_chains` (
  `id` bigint(100) unsigned NOT NULL auto_increment COMMENT 'Ид цепочки',
  `user_id` int(100) unsigned NOT NULL COMMENT 'Ид пользователя',
  `start` date NOT NULL COMMENT 'Дата начала',
  `last` date NOT NULL COMMENT 'Дата окончания',
  `every` int(1) unsigned default NULL COMMENT 'Опционально, по-умолчанию 0 [0, 1, 7, 30, 90. 365] //без повторения, каждый день, каждую неделю, месяц, квартал, год\n',
  `repeat` int(1) unsigned default NULL COMMENT 'Опционально, по-умолчанию 1, от 1 до 500.',
  `week` char(7) character set utf8 collate utf8_bin NOT NULL default '0' COMMENT 'Опционально, Двоичная маска (0000011 - выходные, 1111100 - будни) В случае, если выбран период повторения - еженедельный. По-умолчанию - ""\n',
  PRIMARY KEY  (`id`),
  KEY `user_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Цепочка операций';

ALTER TABLE `operation`
    CHANGE COLUMN `draft` `accepted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Подтверждена = 1, Черновик = 0',
    ADD COLUMN `time` TIME  NOT NULL COMMENT 'Время операции' AFTER `money`,
    ADD COLUMN `chain_id` BIGINT(10) UNSIGNED NOT NULL AFTER `accepted`;

# Обновляем все старые операции
UPDATE operation o SET accepted = 1;

# Версия
INSERT INTO versions VALUES('52', NOW(), 'ukko')