ALTER TABLE `users` 
    MODIFY COLUMN `user_currency_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 
    COMMENT 'Валюта пользователя по умолчанию';
ALTER TABLE `users`
    MODIFY COLUMN `user_currency_list` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'a:2:{i:0;i:0;i:1;i:1;}' 
    COMMENT 'Сериализованный массив валют пользователя';

