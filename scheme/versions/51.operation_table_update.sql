# Обновляем таблицу операций
ALTER TABLE `operation`
    MODIFY COLUMN `imp_id` DECIMAL(20,2)  COMMENT 'Первоначальная сумма перевода до конвертации',
    MODIFY COLUMN `exchange_rate` DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Курс обмена валюты';

# Удаляем ненужное поле
ALTER TABLE `operation` DROP COLUMN `imp_date`;

# Добавляем новые поля, для хранения источника ввода операции, и определения, черновик ли это
ALTER TABLE `operation`
    ADD COLUMN `source_id` INT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Источник ввода операции. По умолчанию = 1, т.е. ввод с сайта easyfinanc.ru' AFTER `type`,
    ADD COLUMN `draft` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Черновик = 1, нормальная подтверждённая операция = 0, по умолчанию = 0' AFTER `source_id`;

CREATE TABLE `sources` (
  `id` INT(1) UNSIGNED NOT NULL COMMENT 'Ид',
  `name` VARCHAR(255)  NOT NULL COMMENT 'Имя источника',
  `url` TEXT  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Адрес сайта',
  `comment` TEXT  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Комментарий',
  `image` VARCHAR(255)  NOT NULL COMMENT 'Путь к изображению'
) ENGINE = InnoDB
COMMENT = 'Источники ввода операций';

# Версия 
INSERT INTO versions VALUES('51', NOW(), 'ukko');

