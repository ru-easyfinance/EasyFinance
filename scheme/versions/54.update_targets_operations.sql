# Добавляем признак подтверждённости перевода на финцель
ALTER TABLE `target_bill`
    ADD COLUMN `accepted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `tags`;
# Добавляем цепочку
ALTER TABLE `target_bill`
    ADD COLUMN `chain_id` BIGINT(100) UNSIGNED NOT NULL DEFAULT 0 AFTER `tags`;


# Обновляем все существующие переводы
UPDATE target_bill t SET accepted=1;

# Версия
INSERT INTO versions VALUES('54', NOW(), 'ukko');
