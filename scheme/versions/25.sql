CREATE TABLE `budget` (
      `id` BIGINT(10) UNSIGNED NOT NULL COMMENT 'ИД',
      `user_id` BIGINT(10) UNSIGNED NOT NULL COMMENT 'ИД пользователя',
      `category` BIGINT(10) UNSIGNED NOT NULL COMMENT 'ИД категории',
      `drain` TINYINT  NOT NULL COMMENT '1 - расход, 0 - доход',
      `currency` INT UNSIGNED NOT NULL COMMENT 'Валюта',
      `amount` DECIMAL(20,2)  NOT NULL COMMENT 'Сумма',
      `date_start` DATE  NOT NULL COMMENT 'Дата начала периода',
      `date_end` DATE  NOT NULL COMMENT 'Дата окончания периода',
      `dt_create` DATETIME  NOT NULL COMMENT 'Дата создания',
      `dt_update` TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обновления'
)
ENGINE = InnoDB 
COMMENT = 'Бюджет';

ALTER TABLE `budget` ADD PRIMARY KEY (`id`);
