DROP TABLE IF EXISTS `experts`;
CREATE TABLE IF NOT EXISTS `experts` (
  `id` int(11) NOT NULL,
  `min_desc` text NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `experts_plugins`;
CREATE TABLE IF NOT EXISTS `experts_plugins` (
  `id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `title` int(11) NOT NULL,
  `rem` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='коментировать!!';
DROP TABLE IF EXISTS `mail`;
CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `text` text NOT NULL,
  `category` varchar(10) NOT NULL,
  `title` varchar(10) NOT NULL,
  `is_new` tinyint(1) NOT NULL,
  `a_vis` tinyint(1) NOT NULL,
  `t_vis` tinyint(1) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;