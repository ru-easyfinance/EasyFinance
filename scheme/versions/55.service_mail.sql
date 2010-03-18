# Добавляем поле для служебной личной почты пользователя для пересылки выписок и счетов
ALTER TABLE `users`
    ADD COLUMN `user_service_mail` VARCHAR(100)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
    COMMENT 'Служебная личная почта пользователя на ресурсе' AFTER `user_type`,
    ADD INDEX `service_mail_idx`(`user_service_mail`);

# Версия
INSERT INTO versions VALUES('54', NOW(), 'ukko');