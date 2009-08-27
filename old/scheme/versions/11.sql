ALTER TABLE `users` 
    ADD COLUMN `user_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'тип пользователя 0-юзер 1-админ 2-эксперт' AFTER `user_currency_list`;
