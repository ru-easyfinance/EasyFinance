CREATE TABLE  `articles` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userId` int(100) NOT NULL,
  `authorName` varchar(64) DEFAULT NULL,
  `authorUrl` varchar(128) DEFAULT NULL,
  `title` varchar(256) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `keywords` varchar(256) DEFAULT NULL,
  `announce` text NOT NULL,
  `body` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Статьи';