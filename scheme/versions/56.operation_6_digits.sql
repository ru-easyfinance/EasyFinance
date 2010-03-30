# Теперь операция должна сохранять 12 символов в курсе обмена валюты, где 6 - до точки + 1 символ - точка
ALTER TABLE `operation`
    MODIFY COLUMN `exchange_rate` DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT '0.0' COMMENT 'Курс обмена валюты';

# Версия
INSERT INTO versions VALUES('56', NOW(), 'ukko');


