# Добавляем новый тип поля счёта
INSERT INTO Acc_Fields (`id`, `name`, `description`) VALUES (29,'binding', 'Привязка к номеру счёта в банке');

# Связываем это поле с дебетовой карточкой
INSERT INTO Acc_ConnectionTypes (`field_id`, `type_id`) VALUES(29,2);

# И добавляем индекс для быстрого выбора по полю
ALTER TABLE `Acc_Values` ADD INDEX `account_idx`(`account_id`);

# Версия
INSERT INTO versions VALUES('59', NOW(), 'ukko');