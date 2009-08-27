ALTER TABLE `target_bill` 
    ADD COLUMN `tags` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
        COMMENT 'Теги операции',
    ADD COLUMN `dt_create` DATETIME  NOT NULL DEFAULT 0 
        COMMENT 'таймштамп создания',
    ADD COLUMN `dt_update` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP 
        COMMENT 'таймштамп обновления';

ALTER TABLE `operation` MODIFY COLUMN `dt_update` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP 
    COMMENT 'Дата и время модификации';
