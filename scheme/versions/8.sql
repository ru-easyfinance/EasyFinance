ALTER TABLE `wwwhomemoneyru`.`category` MODIFY COLUMN `cat_id` INT(255)  NOT NULL AUTO_INCREMENT COMMENT 'Ид категории',
 MODIFY COLUMN `cat_parent` INT(255)  NOT NULL DEFAULT 0 COMMENT 'Ид родительской категории (если 0, то она сам себе родитель)',
 MODIFY COLUMN `user_id` INT(100) UNSIGNED NOT NULL COMMENT 'Ид пользователя',
 MODIFY COLUMN `system_category_id` INTEGER  NOT NULL DEFAULT 0 COMMENT 'Ид системной категории',
 MODIFY COLUMN `cat_name` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Имя категории',
 MODIFY COLUMN `type` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'Тип категории (-1 - расходная, 0 - универсальная, 1 - доходная)',
 MODIFY COLUMN `cat_active` INTEGER  NOT NULL DEFAULT 1 COMMENT 'Активна ли категория? WTF???',
 MODIFY COLUMN `visible` TINYINT(1)  NOT NULL DEFAULT 1 COMMENT 'Видимость категории. 0 - не видно, 1 - видно',
 MODIFY COLUMN `often` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'Частота использования (Уточнить)',
 ADD COLUMN `dt_create` DATETIME  NOT NULL COMMENT 'Таймштамп при создании' AFTER `often`,
 ADD COLUMN `dt_update` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Таймштамп при обновлении' AFTER `dt_create`;

