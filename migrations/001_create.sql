
CREATE TABLE IF NOT EXISTS `ip_geocodes` (
  `ip` bigint(20) NOT NULL,
  `city_id` int(11) NOT NULL,
  KEY `city_id` (`city_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL auto_increment,
  `country` int(11) NOT NULL default '0',
  `name` varchar(200) NOT NULL default '',
  `lat` float default NULL,
  `lng` float default NULL,
  `state` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `kCountry` (`country`),
  KEY `kName` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zip_codes` (
  `id` int(11) NOT NULL auto_increment,
  `zip_code` char(5) NOT NULL,
  `city` varchar(35) NOT NULL,
  `state` char(2) NOT NULL,
  `county` varchar(45) NOT NULL,
  `area_code` varchar(55) NOT NULL,
  `city_type` char(1) default NULL,
  `city_alias_abbreviation` varchar(13) default NULL,
  `city_alias_name` varchar(35) default NULL,
  `latitude` decimal(12,6) NOT NULL,
  `longitude` decimal(12,6) NOT NULL,
  `timezone` char(2) NOT NULL,
  `elevation` bigint(20) default NULL,
  `county_fips` char(3) default NULL,
  `day_light_saving` char(1) default NULL,
  `preferred_last_line_key` varchar(10) default NULL,
  `classification_code` char(1) default NULL,
  `multi_county` char(4) default NULL,
  `state_fips` char(2) default NULL,
  `city_state_key` char(6) default NULL,
  `city_alias_code` varchar(5) default NULL,
  PRIMARY KEY  (`id`),
  KEY `zip_code` (`zip_code`),
  KEY `latitude` (`latitude`),
  KEY `logitude` (`longitude`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
