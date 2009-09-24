CREATE TABLE `mail` (
  `id` INT  NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(8)  NOT NULL COMMENT 'название',
  `text` TEXT  NOT NULL COMMENT 'текст',
  `from` INT  NOT NULL COMMENT 'автор сообщения',
  `to` INT  NOT NULL COMMENT 'кому адресованно сообщение',
  `visible` TINYINT  NOT NULL DEFAULT 0 COMMENT '0 - all -1 only "to" 1 only from ',
  `is_new` BOOL  NOT NULL DEFAULT 1 COMMENT '0 - old 1 - new',
  `date` TIMESTAMP  NOT NULL COMMENT 'дата отправки сообщения',
  `category` VARCHAR(8)  NOT NULL COMMENT 'категория сообщения',
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB DEFAULT CHARSET=utf8,
COMMENT = 'Вся почта';