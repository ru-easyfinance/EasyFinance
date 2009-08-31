DROP TABLE IF EXISTS `info_calc`;
CREATE TABLE IF NOT EXISTS `info_calc` (
  `m_r` int(11) NOT NULL COMMENT 'мин грань . красная',
  `m_y` int(11) NOT NULL COMMENT 'мин грань.жёлтая',
  `m_b` int(11) NOT NULL COMMENT 'мин грань. зелёная',
  `c_r` int(11) NOT NULL COMMENT 'грубый расчёт.красный',
  `c_y` int(11) NOT NULL COMMENT 'грубый расчёт.жёлтый',
  `c_g` int(11) NOT NULL COMMENT 'грубый расчт.зелёный',
  `u_r` int(11) NOT NULL COMMENT 'повышение.красный',
  `u_y` int(11) NOT NULL COMMENT 'повышение.жёлый',
  `u_g` int(11) NOT NULL COMMENT 'повышение. зелёный',
  `weight` int(11) NOT NULL COMMENT 'вес'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `info_desc`;
CREATE TABLE IF NOT EXISTS `info_desc` (
  `type` varchar(11) NOT NULL,
  `title` varchar(11) NOT NULL,
  `min` int(11) NOT NULL,
  `color` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
