CREATE TABLE IF NOT EXISTS `bb_fbuser` (
  `bb_userid` int(20) NOT NULL,
  `fb_userid` int(20) NOT NULL,
  `fb_linked` tinyint(1) NOT NULL,
  `fb_use_img` tinyint(1) NOT NULL,
  `fb_publish` tinyint(1) NOT NULL,
  PRIMARY KEY (`fb_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;