ALTER TABLE `operation` 
    MODIFY COLUMN `tags` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci 
    COMMENT 'Поле с тегами. Дублирует теги из таблицы тегов, но позволяет по быстрому получать все теги',
    MODIFY COLUMN `type` TINYINT(1) UNSIGNED NOT NULL 
    COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель';
ALTER TABLE tags` DROP COLUMN `id`,
    ADD INDEX `user_name_idx`(`user_id`, `name`),
    ADD INDEX `op_idx`(`oper_id`);
ALTER TABLE `operation` 
    ADD COLUMN `dt_create` DATETIME  NOT NULL COMMENT 'Дата и время создания проставляется в скрипте' AFTER `type`,
     ADD COLUMN `dt_update` TIMESTAMP  NOT NULL DEFAULT 0 ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата и время модификации' AFTER `dt_create`;

