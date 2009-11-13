ALTER TABLE `category` 
    DROP COLUMN `often`,
    DROP INDEX `user_id`,
    ADD INDEX `user_id` USING BTREE(`user_id`, `visible`),
    ADD COLUMN `custom` TINYINT(1)  NOT NULL COMMENT '1 - Создана пользователем, 0 - системная' AFTER `visible`;

