ALTER TABLE `operation` 
    MODIFY COLUMN `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Тип операции : 0-расход, 1-доход, 2-перевод со счёта, 3-Покупка валюты (отключено), 4-Перевод на фин.цель';

