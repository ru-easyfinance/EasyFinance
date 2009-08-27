ALTER TABLE `calendar` 
    ADD COLUMN `event` ENUM('cal','per')  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cal' COMMENT 'Событие календаря или периодической транзакции' AFTER `week`;

ALTER TABLE `calendar` 
    MODIFY COLUMN `week` VARCHAR(50)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Сериализованный массив с днями недели',
    ADD COLUMN `amount` DECIMAL(20,2)  NOT NULL DEFAULT 0 COMMENT 'Сумма для периодических транзакций' AFTER `event`;

