<?php
/*
 Bookmarks Data for
 Social It plugin (for bbPress) by www.gaut.am
*/
$socialit_bookmarks_data=array(
	'socialit-scriptstyle'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Script &amp; Style', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Script &amp; Style', 'socialit'),
		'baseUrl'=>'http://scriptandstyle.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blinklist'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Blinklist', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Blinklist', 'socialit'),
		'baseUrl'=>'http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=PERMALINK&amp;Title=TITLE',
	),
	'socialit-delicious'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Delicious', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('del.icio.us', 'socialit'),
		'baseUrl'=>'http://del.icio.us/post?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-digg'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Digg', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Digg this!', 'socialit'),
		'baseUrl'=>'http://digg.com/submit?phase=2&amp;url=PERMALINK&amp;title=TITLE',
	),
	'socialit-diigo'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Diigo', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Post this on ', 'socialit').__('Diigo', 'socialit'),
		'baseUrl'=>'http://www.diigo.com/post?url=PERMALINK&amp;title=TITLE&amp;desc=SOCIALIT_TEASER',
	),
	'socialit-reddit'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Reddit', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Reddit', 'socialit'),
		'baseUrl'=>'http://reddit.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-yahoobuzz'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Yahoo! Buzz', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Buzz up!', 'socialit'),
		'baseUrl'=>'http://buzz.yahoo.com/submit/?submitUrl=PERMALINK&amp;submitHeadline=TITLE&amp;submitSummary=YAHOOTEASER&amp;submitCategory=YAHOOCATEGORY&amp;submitAssetType=YAHOOMEDIATYPE',
	),
	'socialit-stumbleupon'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Stumbleupon', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Stumble upon something good? Share it on StumbleUpon', 'socialit'),
		'baseUrl'=>'http://www.stumbleupon.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-technorati'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Technorati', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Technorati', 'socialit'),
		'baseUrl'=>'http://technorati.com/faves?add=PERMALINK',
	),
	'socialit-mixx'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Mixx', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Mixx', 'socialit'),
		'baseUrl'=>'http://www.mixx.com/submit?page_url=PERMALINK&amp;title=TITLE',
	),
	'socialit-myspace'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('MySpace', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Post this to ', 'socialit').__('MySpace', 'socialit'),
		'baseUrl'=>'http://www.myspace.com/Modules/PostTo/Pages/?u=PERMALINK&amp;t=TITLE',
	),
	'socialit-designfloat'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('DesignFloat', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('DesignFloat', 'socialit'),
		'baseUrl'=>'http://www.designfloat.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-facebook'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Facebook', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Facebook', 'socialit'),
		'baseUrl'=>'http://www.facebook.com/share.php?u=PERMALINK&amp;t=TITLE',
	),
	'socialit-twitter'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Twitter', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Tweet This!', 'socialit'),
		'baseUrl'=>'http://twitter.com/home?status=SHORT_TITLE+-+FETCH_URL+POST_BY',
	),
	'socialit-mail'=>array(
		'check'=>__('Check this box to include the ', 'socialit').__('"Email to a Friend" link', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Email this to a friend?', 'socialit'),
		'baseUrl'=>'mailto:?subject=%22TITLE%22&amp;body='.urlencode( __('I thought this article might interest you.', 'socialit') ).'%0A%0A%22POST_SUMMARY%22%0A%0A'.urlencode( __('You can read the full article here', 'socialit') ).'%3A%20PERMALINK',
	),
	'socialit-tomuse'=>array(
		'check'=>__('Check this box to include the ', 'socialit').__('ToMuse', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Suggest this article to ', 'socialit').__('ToMuse', 'socialit'),
		'baseUrl'=>'mailto:tips@tomuse.com?subject='.urlencode( __('New tip submitted via the Social It Plugin for bbPress!', 'socialit') ).'&amp;body='.urlencode( __('I would like to submit this article', 'socialit') ).'%3A%20%22TITLE%22%20'.urlencode( __('for possible inclusion on ToMuse.', 'socialit') ).'%0A%0A%22POST_SUMMARY%22%0A%0A'.urlencode( __('You can read the full article here', 'socialit') ).'%3A%20PERMALINK',
	),
	'socialit-comfeed'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('a \'Subscribe to Comments\' link', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Subscribe to the comments for this post?', 'socialit'),
		'baseUrl'=>'PERMALINK',
	),
	'socialit-linkedin'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Linkedin', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Linkedin', 'socialit'),
		'baseUrl'=>'http://www.linkedin.com/shareArticle?mini=true&amp;url=PERMALINK&amp;title=TITLE&amp;summary=POST_SUMMARY&amp;source=SITE_NAME',
	),
	'socialit-newsvine'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Newsvine', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Seed this on ', 'socialit').__('Newsvine', 'socialit'),
		'baseUrl'=>'http://www.newsvine.com/_tools/seed&amp;save?u=PERMALINK&amp;h=TITLE',
	),
	'socialit-devmarks'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Devmarks', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Devmarks', 'socialit'),
		'baseUrl'=>'http://devmarks.com/index.php?posttext=POST_SUMMARY&amp;posturl=PERMALINK&amp;posttitle=TITLE',
	),
	'socialit-google'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Google Bookmarks', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Google Bookmarks', 'socialit'),
		'baseUrl'=>'http://www.google.com/bookmarks/mark?op=add&amp;bkmk=PERMALINK&amp;title=TITLE',
	),
	'socialit-misterwong'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Mister Wong', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Mister Wong', 'socialit'),
		'baseUrl'=>'http://'.__('www.mister-wong.com', 'socialit').'/addurl/?bm_url=PERMALINK&amp;bm_description=TITLE&amp;plugin=socialit',
	),
	'socialit-izeby'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Izeby', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Izeby', 'socialit'),
		'baseUrl'=>'http://izeby.com/submit.php?url=PERMALINK',
	),
	'socialit-tipd'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Tipd', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Tipd', 'socialit'),
		'baseUrl'=>'http://tipd.com/submit.php?url=PERMALINK',
	),
	'socialit-pfbuzz'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('PFBuzz', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('PFBuzz', 'socialit'),
		'baseUrl'=>'http://pfbuzz.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-friendfeed'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('FriendFeed', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('FriendFeed', 'socialit'),
		'baseUrl'=>'http://www.friendfeed.com/share?title=TITLE&amp;link=PERMALINK',
	),
	'socialit-blogmarks'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('BlogMarks', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Mark this on ', 'socialit').__('BlogMarks', 'socialit'),
		'baseUrl'=>'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url=PERMALINK&amp;title=TITLE',
	),
	'socialit-twittley'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Twittley', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Twittley', 'socialit'),
		'baseUrl'=>'http://twittley.com/submit/?title=TITLE&amp;url=PERMALINK&amp;desc=POST_SUMMARY&amp;pcat=TWITT_CAT&amp;tags=DEFAULT_TAGS',
	),
	'socialit-fwisp'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Fwisp', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Fwisp', 'socialit'),
		'baseUrl'=>'http://fwisp.com/submit?url=PERMALINK',
	),
	'socialit-designmoo'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('DesignMoo', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Moo this on ', 'socialit').__('DesignMoo', 'socialit').'!',
		'baseUrl'=>'http://designmoo.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-bobrdobr'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('BobrDobr', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Russian)', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('BobrDobr', 'socialit'),
		'baseUrl'=>'http://bobrdobr.ru/addext.html?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-yandex'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Yandex.Bookmarks', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Yandex.Bookmarks', 'socialit'),
		'baseUrl'=>'http://zakladki.yandex.ru/userarea/links/addfromfav.asp?bAddLink_x=1&amp;lurl=PERMALINK&amp;lname=TITLE',
	),
	'socialit-memoryru'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Memory.ru', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Memory.ru', 'socialit'),
		'baseUrl'=>'http://memori.ru/link/?sm=1&amp;u_data[url]=PERMALINK&amp;u_data[name]=TITLE',
	),
	'socialit-100zakladok'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('100 bookmarks', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('100 bookmarks', 'socialit'),
		'baseUrl'=>'http://www.100zakladok.ru/save/?bmurl=PERMALINK&amp;bmtitle=TITLE',
	),
	'socialit-moemesto'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('MyPlace', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('MyPlace', 'socialit'),
		'baseUrl'=>'http://moemesto.ru/post.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-hackernews'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Hacker News', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Hacker News', 'socialit'),
		'baseUrl'=>'http://news.ycombinator.com/submitlink?u=PERMALINK&amp;t=TITLE',
	),
	'socialit-printfriendly'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Print Friendly', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Send this page to ', 'socialit').__('Print Friendly', 'socialit'),
		'baseUrl'=>'http://www.printfriendly.com/print?url=PERMALINK',
	),
	'socialit-designbump'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Design Bump', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Bump this on ', 'socialit').__('DesignBump', 'socialit'),
		'baseUrl'=>'http://designbump.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-ning'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Ning', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Ning', 'socialit'),
		'baseUrl'=>'http://bookmarks.ning.com/addItem.php?url=PERMALINK&amp;T=TITLE',
	),
	'socialit-identica'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Identica', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Post this to ', 'socialit').__('Identica', 'socialit'),
		'baseUrl'=>'http://identi.ca//index.php?action=newnotice&amp;status_textarea=Reading:+&quot;TITLE&quot;+-+from+PERMALINK',
	),
	'socialit-xerpi'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Xerpi', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Save this to ', 'socialit').__('Xerpi', 'socialit'),
		'baseUrl'=>'http://www.xerpi.com/block/add_link_from_extension?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-wikio'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Wikio', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Wikio', 'socialit'),
		'baseUrl'=>'http://www.wikio.com/sharethis?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-techmeme'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('TechMeme', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Tip this to ', 'socialit').__('TechMeme', 'socialit'),
		'baseUrl'=>'http://twitter.com/home/?status=Tip+@Techmeme+PERMALINK+&quot;TITLE&quot;',
	),
	'socialit-sphinn'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Sphinn', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Sphinn this on ', 'socialit').__('Sphinn', 'socialit'),
		'baseUrl'=>'http://sphinn.com/index.php?c=post&amp;m=submit&amp;link=PERMALINK',
	),
	'socialit-posterous'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Posterous', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Post this to ', 'socialit').__('Posterous', 'socialit'),
		'baseUrl'=>'http://posterous.com/share?linkto=PERMALINK&amp;title=TITLE&amp;selection=POST_SUMMARY',
	),
	'socialit-globalgrind'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Global Grind', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Grind this! on ', 'socialit').__('Global Grind', 'socialit'),
		'baseUrl'=>'http://globalgrind.com/submission/submit.aspx?url=PERMALINK&amp;type=Article&amp;title=TITLE',
	),
	'socialit-pingfm'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Ping.fm', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Ping this on ', 'socialit').__('Ping.fm', 'socialit'),
		'baseUrl'=>'http://ping.fm/ref/?link=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-nujij'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('NUjij', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Dutch)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('NUjij', 'socialit'),
		'baseUrl'=>'http://nujij.nl/jij.lynkx?t=TITLE&amp;u=PERMALINK&amp;b=POST_SUMMARY',
	),
	'socialit-ekudos'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('eKudos', 'socialit').__(' in your bookmarking menu', 'socialit').__(' (Dutch)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('eKudos', 'socialit'),
		'baseUrl'=>'http://www.ekudos.nl/artikel/nieuw?url=PERMALINK&amp;title=TITLE&amp;desc=POST_SUMMARY',
	),
	'socialit-netvouz'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Netvouz', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Netvouz', 'socialit'),
		'baseUrl'=>'http://www.netvouz.com/action/submitBookmark?url=PERMALINK&amp;title=TITLE&amp;popup=no',
	),
	'socialit-netvibes'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Netvibes', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Netvibes', 'socialit'),
		'baseUrl'=>'http://www.netvibes.com/share?title=TITLE&amp;url=PERMALINK',
	),
	'socialit-fleck'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Fleck', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Fleck', 'socialit'),
		'baseUrl'=>'http://beta3.fleck.com/bookmarklet.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blogospherenews'=>array(
		'check'=>__('Check this box to include ', 'socialit').__('Blogosphere News', 'socialit').__(' in your bookmarking menu', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Blogosphere News', 'socialit'),
		'baseUrl'=>'http://www.blogospherenews.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
);
?>