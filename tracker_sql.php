CREATE TABLE peers 
(
  pid int(1) unsigned NOT NULL AUTO_INCREMENT,
  tid int(1) unsigned NOT NULL,
  uid int(1) unsigned NOT NULL,
  peerId varbinary(20) NOT NULL,
  ip varchar(39) COLLATE utf8_unicode_ci NOT NULL,
  port smallint(1) unsigned NOT NULL DEFAULT '0',
  residual bigint(1) unsigned NOT NULL DEFAULT '0',
  uploaded bigint(1) unsigned NOT NULL DEFAULT '0',
  downloaded bigint(1) unsigned NOT NULL DEFAULT '0',
  added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (pid),
  UNIQUE KEY uid (uid,tid),
  KEY tid (tid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE torrents 
(
  tid int(1) unsigned NOT NULL AUTO_INCREMENT,
  infohash binary(20) NOT NULL,
  name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  size bigint(1) unsigned NOT NULL DEFAULT '0',
  downloaded int(1) unsigned NOT NULL DEFAULT '0',
  banned tinyint(1) NOT NULL DEFAULT '0',
  active tinyint(1) NOT NULL DEFAULT '0',
  description LONGTEXT NOT NULL,
  category varchar(50)  COLLATE utf8_unicode_ci NOT NULL,
  uploader varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tid),
  UNIQUE KEY infohash (infohash),
  KEY banned (banned),
  KEY active (active),
  FULLTEXT KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
