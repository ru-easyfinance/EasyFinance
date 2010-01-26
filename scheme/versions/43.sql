drop table if exists articles ;
CREATE TABLE `articles` (
  `id` int(16) NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userId` int(100) NOT NULL,
  `authorName` varchar(64) default NULL,
  `authorUrl` varchar(128) default NULL,
  `title` varchar(256) NOT NULL,
  `description` varchar(256) default NULL,
  `keywords` varchar(256) default NULL,
  `announce` text NOT NULL,
  `body` longtext NOT NULL,
  `status` tinyint(4) NOT NULL,
  `image_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='Статьи' AUTO_INCREMENT=36 ;
