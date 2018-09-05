CREATE TABLE `sitesMeta` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `siteID` int(11) NOT NULL,
  `metaTag` varchar(20) NOT NULL DEFAULT '',
  `metaValue` varchar(255) NOT NULL DEFAULT '',
  `timeUpdated` timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP,
  `timeCreated` timestamp NOT NULL DEFAULT "0000-00-00 00:00:00",
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;