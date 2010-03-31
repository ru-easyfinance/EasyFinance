

DROP TABLE IF EXISTS `migration_version`;

CREATE TABLE `migration_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `migration_version` VALUES (37);
