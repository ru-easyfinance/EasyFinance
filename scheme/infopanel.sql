DROP TABLE IF EXISTS `info_panel`;

DROP TABLE IF EXISTS `infopanel_users`;

CREATE TABLE `info_panel` (
`type` ENUM('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') NOT NULL,
`start` INT(1) SIGNED NOT NULL,
`end` INT(1) SIGNED NOT NULL,
`desc` TEXT NOT NULL
) ENGINE = MyISAM;

CREATE TABLE `infopanel_users` (
`user_id` INT(100) UNSIGNED NOT NULL,
`type` ENUM('fcon','money','budget','cost','credit','akc','pif','ofbu','oms','estat') ,
`settings` TEXT,
`state` ENUM('0','1','2') ,
`order` ENUM('0','1','2')
)ENGINE = MyISAM;