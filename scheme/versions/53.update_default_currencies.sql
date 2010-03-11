# Делаем дефолтными валюты по-умолчанию для новых пользователей (руб, доллар, евро, гривна, бел.руб)
ALTER TABLE `users`
MODIFY COLUMN `user_currency_list` VARCHAR(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
    DEFAULT 'a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"6";}'
    COMMENT 'Сериализованный массив валют пользователя';

# Обновляем списки валют уже существующих пользователей, которые не поменяли валюты
UPDATE users u SET user_currency_list='a:5:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";i:4;s:1:"6";}'
WHERE user_currency_list='a:2:{i:0;i:1;i:1;i:2;}';

# Версия
INSERT INTO versions VALUES('53', NOW(), 'ukko');