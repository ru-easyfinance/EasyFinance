ALTER TABLE `periodic` ADD COLUMN `last_date` DATE  NOT NULL COMMENT 'Последняя дата события' AFTER `infinity`;

