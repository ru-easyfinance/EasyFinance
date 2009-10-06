ALTER TABLE `calendar` ADD COLUMN `close` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Статус цели, по умолчанию = 0, т.е. открыта' AFTER `category`;
