
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;