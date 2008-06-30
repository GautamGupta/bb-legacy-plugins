<?php
/*
BBVideo Plugin v0.24 definition file
http://www.naden.de/blog/bbvideo-bbpress-video-plugin
*/

/*
Support for:

+ myvideo
+ funnyordie
+ collegehumor
+ redtube
+ dailymotion 
+ sevenload
+ glumbert
+ youtube
+ googlevideo
+ liveleak
+ metacafe
+ clipfish
+ gametrailers
+ vimeo
*/

$this->provider = array(
	'youtube' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'youtube\.(.*)/watch\?v=([a-zA-Z0-9_-]*)',
		'index' => 2,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]"><param name="movie" value="http://www.youtube.com/v/[ID]&rel=1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/[ID]&rel=1" type="application/x-shockwave-flash" wmode="transparent" width="[WIDTH]" height="[HEIGHT]"></embed></object>',
		'page_url' => 'http://www.youtube.com'
	),
	'myvideo' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'myvideo\.de/watch/([a-zA-Z0-9_-]*)',
		'index' => 1,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]" type="application/x-shockwave-flash" data="http://www.myvideo.de/movie/[ID]"><param name="movie" value="http://www.myvideo.de/movie/[ID]"></param><param name="AllowFullscreen" value="true" /></object>',
		'page_url' => 'http://www.myvideo.de'
	),
	'funnyordie' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'funnyordie\.com/videos/([0-9a-fA-F]*)',
		'index' => 1,
		'code' => '<embed type="application/x-shockwave-flash" src="http://www2.funnyordie.com/public/flash/fodplayer.swf?1182461048" scale="noScale" salign="TL" flashvars="&ratename=IMMORTAL&rating=5.0&ratedby=12&canrate=no&VID=7417&file=http://www2.funnyordie.com/[ID].flv&autoStart=false&key=[ID]" allowfullscreen="true" height="[HEIGHT]" width="[WIDTH]"></embed>',
		'page_url' => 'http://www.funnyordie.com'
	),
	'gametrailers' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'gametrailers\.com/player/([0-9].*)\.html(.*)',
		'index' => 1,
		'code' => '<object id="gtembed" width="[WIDTH]" height="[HEIGHT]"><param name="allowScriptAccess" value="sameDomain" /> <param name="movie" value="http://www.gametrailers.com/remote_wrap.php?mid=[ID]"/> <param name="quality" value="high" /><embed src="http://www.gametrailers.com/remote_wrap.php?mid=[ID]" swLiveConnect="true" name="gtembed" align="middle" allowScriptAccess="sameDomain" quality="high" type="application/x-shockwave-flash" width="[WIDTH]" height="[HEIGHT]"></embed></object>',
		'page_url' => 'http://www.gametrailers.com'
	),
	'collegehumor' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'collegehumor\.com/video:([0-9]*)',
		'index' => 1,
		'code' => '<embed src="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=[ID]" quality="best" width="[WIDTH]" height="[HEIGHT]" type="application/x-shockwave-flash"></embed>',
		'page_url' => 'http://www.collegehumor.com'
	),
	'dailymotion' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'dailymotion\.com/(.*)video/(.*?)_',
		'index' => 2,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]"><param name="movie" value="http://www.dailymotion.com/swf/[ID]"></param><param name="allowfullscreen" value="true"></param><embed src="http://www.dailymotion.com/swf/[ID]" type="application/x-shockwave-flash" width="[WIDTH]" height="[HEIGHT]" allowfullscreen="true"></embed></object>',
		'page_url' => 'http://www.dailymotion.com'
	),
	'glumbert' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'glumbert\.com\/media\/(.*?)$',
		'index' => 1,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]"><param name="movie" value="http://www.glumbert.com/embed/[ID]"></param><param name="wmode" value="transparent"></param><embed src="http://www.glumbert.com/embed/[ID]" type="application/x-shockwave-flash" wmode="transparent" width="[WIDTH]" height="[HEIGHT]"></embed></object>',
		'page_url' => 'http://www.glumbert.com'
	),
	'liveleak' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'liveleak.com/view[?]i=([0-9a-zA-Z_]*)',
		'index' => 1,
		'code' => '<embed src="http://www.liveleak.com/player.swf" width="[WIDTH]" height="[HEIGHT]" type="application/x-shockwave-flash" flashvars="autostart=false&token=[ID]" scale="showall" name="index"></embed>',
		'page_url' => 'http://www.liveleak.com'
	),
  'redtube' => array(
    'width' => '434',
		'height' => '344',
		'pattern' => 'redtube.com/([0-9].*)',
		'index' => 1,
    'code' => '<object height="[HEIGHT]" width="[WIDTH]"><param name="movie" value="http://embed.redtube.com/player/"><param name="FlashVars" value="id=[ID]&style=redtube"><embed src="http://embed.redtube.com/player/?id=[ID]&style=redtube" type="application/x-shockwave-flash" height="[HEIGHT]" width="[WIDTH]"></object>',
    'page_url' => 'http://www.redtube.com'
  ),
	'googlevideo' => array(
		'width' => '425',
		'height' => '350',
    'pattern' => 'video\.google\.(.*)/(videoplay)?(url)?\?docid=([a-zA-Z0-9_-]*)',
		'index' => 1,
		'code' => '<embed style="width:[WIDTH]px; height:[HEIGHT]px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=[ID]&hl=de" flashvars=""></embed>',
		'page_url' => 'http://video.google.com'
	),
	'sevenload' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'sevenload\.com/videos\/([a-zA-Z0-9_-]*)',
		'index' => 1,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]"><param name="FlashVars" value="slxml=de.sevenload.com"/><param name="movie" value="http://de.sevenload.com/pl/[ID]/[WIDTH]x[HEIGHT]/swf" /><embed src="http://de.sevenload.com/pl/[ID]/[WIDTH]x[HEIGHT]/swf" type="application/x-shockwave-flash" width="[WIDTH]" height="[HEIGHT]" FlashVars="slxml=de.sevenload.com"></embed></object>',
		'page_url' => 'http://de.sevenload.com'
	),
	'metacafe' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'metacafe\.com/watch/([0-9].*?)/',
		'index' => 1,
		'code' => '<embed flashVars="altServerURL=http://www.metacafe.com&playerVars=showStats=yes|autoPlay=no|blogName=naden.de|blogURL=http://www.naden.de" src="http://www.metacafe.com/fplayer/[ID]/what_if.swf" width="[WIDTH]" height="[HEIGHT]" wmode="transparent"></embed>',
		'page_url' => 'http://www.metacafe.com'
	),
	'clipfish' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'clipfish\.de/player\.php\?videoid=([a-zA-z0-9]*)',
		'index' => 1,
		'code' => '<embed src="http://www.clipfish.de/videoplayer.swf?as=0&videoid=[ID]&r=1" quality="high" bgcolor="#cacaca" width="[WIDTH]" height="[HEIGHT]" name="player" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>',
		'page_url' => 'http://www.clipfish.de'
	),
	'vimeo' => array(
		'width' => '425',
		'height' => '350',
		'pattern' => 'vimeo\.com/([0-9]*)',
		'index' => 1,
		'code' => '<object width="[WIDTH]" height="[HEIGHT]"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=[ID]&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" /><embed src="http://www.vimeo.com/moogaloop.swf?clip_id=[ID]&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="[WIDTH]" height="[HEIGHT]"></embed></object>',
		'page_url' => 'http://www.vimeo.com'
	)
);

?>