--
-- ���� ������: `homemoney`
--

-- --------------------------------------------------------

--
-- ��������� ������� `info_calc`
--

DROP TABLE IF EXISTS `info_calc`;
CREATE TABLE IF NOT EXISTS `info_calc` (
  `m_r` int(11) NOT NULL COMMENT '��� ����� . �������',
  `m_y` int(11) NOT NULL COMMENT '��� �����.�����',
  `m_b` int(11) NOT NULL COMMENT '��� �����. ������',
  `c_r` int(11) NOT NULL COMMENT '������ ������.�������',
  `c_y` int(11) NOT NULL COMMENT '������ ������.�����',
  `c_g` int(11) NOT NULL COMMENT '������ �����.������',
  `u_r` int(11) NOT NULL COMMENT '���������.�������',
  `u_y` int(11) NOT NULL COMMENT '���������.����',
  `u_g` int(11) NOT NULL COMMENT '���������. ������',
  `weight` int(11) NOT NULL COMMENT '���'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- ��������� ������� `info_desc`
--

DROP TABLE IF EXISTS `info_desc`;
CREATE TABLE IF NOT EXISTS `info_desc` (
  `type` varchar(11) NOT NULL,
  `title` varchar(11) NOT NULL,
  `min` int(11) NOT NULL,
  `color` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
