ALTER TABLE `system_categories` CHANGE COLUMN `system_category_id` `id` INT(255)  NOT NULL DEFAULT NULL AUTO_INCREMENT,
 CHANGE COLUMN `system_category_name` `name` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
 DROP COLUMN `system_group_id`,
 DROP COLUMN `parent_id`,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY  (`id`);

