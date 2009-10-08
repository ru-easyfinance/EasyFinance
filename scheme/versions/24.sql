ALTER TABLE `calendar`
ADD COLUMN `category` BIGINT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ид категории для периодических транзакций' AFTER `amount`,
ADD COLUMN `close` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Статус цели, по умолчанию = 0, т.е. открыта' AFTER `category`;
