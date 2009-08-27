ALTER TABLE `calendar` 
    ADD COLUMN `category` BIGINT(10) UNSIGNED NOT NULL COMMENT 'Ид категории для периодических транзакций' AFTER `amount`;

