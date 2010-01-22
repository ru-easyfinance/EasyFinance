CREATE TABLE IF NOT EXISTS `images` (
`id` INT NOT NULL ,
`parent_id` INT NOT NULL ,
`path` TEXT NOT NULL ,
`url` TEXT NOT NULL ,
PRIMARY KEY ( `id` )
) COMMENT = 'таблица ссылок на картинки';

CREATE TABLE IF NOT EXISTS `images_articles` (
`image_id` INT NOT NULL ,
`article_id` INT NOT NULL
) COMMENT = 'связь статей и изображений';

ALTER TABLE `images` CHANGE `id` `id` INT( 11 ) AUTO_INCREMENT;

ALTER TABLE `articles` ADD `image_id` INT NOT NULL ;