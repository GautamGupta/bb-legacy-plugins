<?php
/*
 Bookmarks Data for
 Social It plugin (for bbPress) by www.gaut.am
*/

// dynamic mister wong link generator
if(BB_LANG == 'de_DE') {
	$wong_tld = '.de';
} elseif(BB_LANG == 'zh_CN' || BB_LANG == 'zh_HK' || BB_LANG == 'zh_TW') {
	$wong_tld = '.cn';
} elseif(BB_LANG == 'es_CL'  || BB_LANG == 'es_ES' || BB_LANG == 'es_PE' || BB_LANG == 'es_VE') {
	$wong_tld = '.es';
} elseif(BB_LANG == 'fr_FR' || BB_LANG == 'fr_BE') {
	$wong_tld = '.fr';
} elseif(BB_LANG =='ru_RU' || BB_LANG == 'ru_MA') {
	$wong_tld = '.ru';
}else{
        $wong_tld = '.com';
}

// array of bookmarks
$socialit_bookmarks_data=array(
	'socialit-scriptstyle' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Script &amp; Style', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Script &amp; Style', 'socialit'),
		'baseUrl'=>'http://scriptandstyle.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blinklist' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Blinklist', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Blinklist', 'socialit'),
		'baseUrl'=>'http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=PERMALINK&amp;Title=TITLE',
	),
	'socialit-delicious' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Delicious', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('del.icio.us', 'socialit'),
		'baseUrl'=>'http://del.icio.us/post?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-digg' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Digg', 'socialit' ) ),
		'share'=>__('Digg this!', 'socialit'),
		'baseUrl'=>'http://digg.com/submit?phase=2&amp;url=PERMALINK&amp;title=TITLE',
	),
	'socialit-diigo' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Diigo', 'socialit' ) ),
		'share'=>__('Post this on ', 'socialit').__('Diigo', 'socialit'),
		'baseUrl'=>'http://www.diigo.com/post?url=PERMALINK&amp;title=TITLE&amp;desc=socialit_TEASER',
	),
	'socialit-reddit' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Reddit', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Reddit', 'socialit'),
		'baseUrl'=>'http://reddit.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-yahoobuzz' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Yahoo! Buzz', 'socialit' ) ),
		'share'=>__('Buzz up!', 'socialit'),
		'baseUrl'=>'http://buzz.yahoo.com/submit/?submitUrl=PERMALINK&amp;submitHeadline=TITLE&amp;submitSummary=YAHOOTEASER&amp;submitCategory=YAHOOCATEGORY&amp;submitAssetType=YAHOOMEDIATYPE',
	),
	'socialit-stumbleupon' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Stumbleupon', 'socialit' ) ),
		'share'=>__('Stumble upon something good? Share it on StumbleUpon', 'socialit'),
		'baseUrl'=>'http://www.stumbleupon.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-technorati' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Technorati', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Technorati', 'socialit'),
		'baseUrl'=>'http://technorati.com/faves?add=PERMALINK',
	),
	'socialit-mixx' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Mixx', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Mixx', 'socialit'),
		'baseUrl'=>'http://www.mixx.com/submit?page_url=PERMALINK&amp;title=TITLE',
	),
	'socialit-myspace' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('MySpace', 'socialit' ) ),
		'share'=>__('Post this to ', 'socialit').__('MySpace', 'socialit'),
		'baseUrl'=>'http://www.myspace.com/Modules/PostTo/Pages/?u=PERMALINK&amp;t=TITLE',
	),
	'socialit-designfloat' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('DesignFloat', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('DesignFloat', 'socialit'),
		'baseUrl'=>'http://www.designfloat.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-facebook' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Facebook', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Facebook', 'socialit'),
		'baseUrl'=>'http://www.facebook.com/share.php?v=4&amp;src=bm&amp;u=PERMALINK&amp;t=TITLE',
	),
	'socialit-twitter' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Twitter', 'socialit' ) ),
		'share'=>__('Tweet This!', 'socialit'),
		'baseUrl'=>'http://twitter.com/home?status=SHORT_TITLE+-+FETCH_URL+POST_BY',
	),
	'socialit-mail' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('"Email to a Friend" link', 'socialit' ) ),
		'share'=>__('Email this to a friend?', 'socialit'),
                'baseUrl'=>'mailto:?subject=%22TITLE%22&amp;body='.urlencode( __('I thought this article might interest you.', 'socialit') ).'%0A%0A%22POST_SUMMARY%22%0A%0A'.urlencode( __('You can read the full article here', 'socialit') ).'%3A%20PERMALINK',
	),
	'socialit-tomuse' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('ToMuse', 'socialit' ) ),
		'share'=>__('Suggest this article to ', 'socialit').__('ToMuse', 'socialit'),
                'baseUrl'=>'mailto:tips@tomuse.com?subject='.urlencode( __('New tip submitted via the socialit Plugin!', 'socialit') ).'&amp;body='.urlencode( __('I would like to submit this article', 'socialit') ).'%3A%20%22TITLE%22%20'.urlencode( __('for possible inclusion on ToMuse.', 'socialit') ).'%0A%0A%22POST_SUMMARY%22%0A%0A'.urlencode( __('You can read the full article here', 'socialit') ).'%3A%20PERMALINK',
	),
	'socialit-comfeed' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('a \'Subscribe to Comments\' link', 'socialit' ) ),
		'share'=>__('Subscribe to the comments for this post?', 'socialit'),
		'baseUrl'=>'PERMALINK',
	),
	'socialit-linkedin' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Linkedin', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Linkedin', 'socialit'),
		'baseUrl'=>'http://www.linkedin.com/shareArticle?mini=true&amp;url=PERMALINK&amp;title=TITLE&amp;summary=POST_SUMMARY&amp;source=SITE_NAME',
	),
	'socialit-newsvine' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Newsvine', 'socialit' ) ),
		'share'=>__('Seed this on ', 'socialit').__('Newsvine', 'socialit'),
		'baseUrl'=>'http://www.newsvine.com/_tools/seed&amp;save?u=PERMALINK&amp;h=TITLE',
	),
	'socialit-google' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Google Bookmarks', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('Google Bookmarks', 'socialit'),
		'baseUrl'=>'http://www.google.com/bookmarks/mark?op=add&amp;bkmk=PERMALINK&amp;title=TITLE',
	),
	'socialit-misterwong' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Mister Wong', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('Mister Wong', 'socialit'),
		'baseUrl'=>'http://www.mister-wong'.$wong_tld.'/addurl/?bm_url=PERMALINK&amp;bm_description=TITLE&amp;plugin=socialit',
	),
	'socialit-izeby' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Izeby', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('Izeby', 'socialit'),
		'baseUrl'=>'http://izeby.com/submit.php?url=PERMALINK',
	),
	'socialit-tipd' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Tipd', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Tipd', 'socialit'),
		'baseUrl'=>'http://tipd.com/submit.php?url=PERMALINK',
	),
	'socialit-pfbuzz' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('PFBuzz', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('PFBuzz', 'socialit'),
		'baseUrl'=>'http://pfbuzz.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-friendfeed' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('FriendFeed', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('FriendFeed', 'socialit'),
		'baseUrl'=>'http://www.friendfeed.com/share?title=TITLE&amp;link=PERMALINK',
	),
	'socialit-blogmarks' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('BlogMarks', 'socialit' ) ),
		'share'=>__('Mark this on ', 'socialit').__('BlogMarks', 'socialit'),
		'baseUrl'=>'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url=PERMALINK&amp;title=TITLE',
	),
	'socialit-twittley' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Twittley', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Twittley', 'socialit'),
		'baseUrl'=>'http://twittley.com/submit/?title=TITLE&amp;url=PERMALINK&amp;desc=POST_SUMMARY&amp;pcat=TWITT_CAT&amp;tags=DEFAULT_TAGS',
	),
	'socialit-fwisp' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Fwisp', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Fwisp', 'socialit'),
		'baseUrl'=>'http://fwisp.com/submit?url=PERMALINK',
	),
	'socialit-designmoo' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('DesignMoo', 'socialit' ) ),
		'share'=>__('Moo this on ', 'socialit').__('DesignMoo', 'socialit').'!',
		'baseUrl'=>'http://designmoo.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-bobrdobr' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('BobrDobr', 'socialit')).__(' (Russian)', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('BobrDobr', 'socialit'),
		'baseUrl'=>'http://bobrdobr.ru/addext.html?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-yandex' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Yandex.Bookmarks', 'socialit')).__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Yandex.Bookmarks', 'socialit'),
		'baseUrl'=>'http://zakladki.yandex.ru/userarea/links/addfromfav.asp?bAddLink_x=1&amp;lurl=PERMALINK&amp;lname=TITLE',
	),
	'socialit-memoryru' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Memory.ru', 'socialit')).__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('Memory.ru', 'socialit'),
		'baseUrl'=>'http://memori.ru/link/?sm=1&amp;u_data[url]=PERMALINK&amp;u_data[name]=TITLE',
	),
	'socialit-100zakladok' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('100 bookmarks', 'socialit')).__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('100 bookmarks', 'socialit'),
		'baseUrl'=>'http://www.100zakladok.ru/save/?bmurl=PERMALINK&amp;bmtitle=TITLE',
	),
	'socialit-moemesto' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('MyPlace', 'socialit')).__(' (Russian)', 'socialit'),
		'share'=>__('Add this to ', 'socialit').__('MyPlace', 'socialit'),
		'baseUrl'=>'http://moemesto.ru/post.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-hackernews' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Hacker News', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Hacker News', 'socialit'),
		'baseUrl'=>'http://news.ycombinator.com/submitlink?u=PERMALINK&amp;t=TITLE',
	),
	'socialit-printfriendly' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Print Friendly', 'socialit' ) ),
		'share'=>__('Send this page to ', 'socialit').__('Print Friendly', 'socialit'),
		'baseUrl'=>'http://www.printfriendly.com/print?url=PERMALINK',
	),
	'socialit-designbump' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Design Bump', 'socialit' ) ),
		'share'=>__('Bump this on ', 'socialit').__('DesignBump', 'socialit'),
		'baseUrl'=>'http://designbump.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-ning' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Ning', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('Ning', 'socialit'),
		'baseUrl'=>'http://bookmarks.ning.com/addItem.php?url=PERMALINK&amp;T=TITLE',
	),
	'socialit-identica' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Identica', 'socialit' ) ),
		'share'=>__('Post this to ', 'socialit').__('Identica', 'socialit'),
		'baseUrl'=>'http://identi.ca//index.php?action=newnotice&amp;status_textarea=Reading:+&quot;SHORT_TITLE&quot;+-+from+FETCH_URL',
	),
	'socialit-xerpi' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Xerpi', 'socialit' ) ),
		'share'=>__('Save this to ', 'socialit').__('Xerpi', 'socialit'),
		'baseUrl'=>'http://www.xerpi.com/block/add_link_from_extension?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-wikio' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Wikio', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Wikio', 'socialit'),
		'baseUrl'=>'http://www.wikio.com/sharethis?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-techmeme' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('TechMeme', 'socialit' ) ),
		'share'=>__('Tip this to ', 'socialit').__('TechMeme', 'socialit'),
		'baseUrl'=>'http://twitter.com/home/?status=Tip+@Techmeme+PERMALINK+&quot;TITLE&quot;',
	),
	'socialit-sphinn' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Sphinn', 'socialit' ) ),
		'share'=>__('Sphinn this on ', 'socialit').__('Sphinn', 'socialit'),
		'baseUrl'=>'http://sphinn.com/index.php?c=post&amp;m=submit&amp;link=PERMALINK',
	),
	'socialit-posterous' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Posterous', 'socialit' ) ),
		'share'=>__('Post this to ', 'socialit').__('Posterous', 'socialit'),
		'baseUrl'=>'http://posterous.com/share?linkto=PERMALINK&amp;title=TITLE&amp;selection=POST_SUMMARY',
	),
	'socialit-globalgrind' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Global Grind', 'socialit' ) ),
		'share'=>__('Grind this! on ', 'socialit').__('Global Grind', 'socialit'),
		'baseUrl'=>'http://globalgrind.com/submission/submit.aspx?url=PERMALINK&amp;type=Article&amp;title=TITLE',
	),
	'socialit-pingfm' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Ping.fm', 'socialit' ) ),
		'share'=>__('Ping this on ', 'socialit').__('Ping.fm', 'socialit'),
		'baseUrl'=>'http://ping.fm/ref/?link=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-nujij' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('NUjij', 'socialit')).__(' (Dutch)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('NUjij', 'socialit'),
		'baseUrl'=>'http://nujij.nl/jij.lynkx?t=TITLE&amp;u=PERMALINK&amp;b=POST_SUMMARY',
	),
	'socialit-ekudos' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('eKudos', 'socialit')).__(' (Dutch)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('eKudos', 'socialit'),
		'baseUrl'=>'http://www.ekudos.nl/artikel/nieuw?url=PERMALINK&amp;title=TITLE&amp;desc=POST_SUMMARY',
	),
	'socialit-netvouz' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Netvouz', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Netvouz', 'socialit'),
		'baseUrl'=>'http://www.netvouz.com/action/submitBookmark?url=PERMALINK&amp;title=TITLE&amp;popup=no',
	),
	'socialit-netvibes' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Netvibes', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Netvibes', 'socialit'),
		'baseUrl'=>'http://www.netvibes.com/share?title=TITLE&amp;url=PERMALINK',
	),
	'socialit-fleck' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Fleck', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Fleck', 'socialit'),
		'baseUrl'=>'http://beta3.fleck.com/bookmarklet.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blogospherenews' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Blogosphere News', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Blogosphere News', 'socialit'),
		'baseUrl'=>'http://www.blogospherenews.com/submit.php?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-webblend' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Web Blend', 'socialit' ) ),
		'share'=>__('Blend this!', 'socialit'),
		'baseUrl'=>'http://thewebblend.com/submit?url=PERMALINK&amp;title=TITLE&amp;body=POST_SUMMARY',
	),
	'socialit-wykop' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Wykop', 'socialit')).__(' (Polish)', 'socialit'),
		'share'=>__('Add this to Wykop!', 'socialit'),
		'baseUrl'=>'http://www.wykop.pl/dodaj?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blogengage' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('BlogEngage', 'socialit' ) ),
		'share'=>__('Engage with this article!', 'socialit'),
		'baseUrl'=>'http://www.blogengage.com/submit.php?url=PERMALINK',
	),
	'socialit-hyves' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Hyves', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Hyves', 'socialit'),
		'baseUrl'=>'http://www.hyves.nl/profilemanage/add/tips/?name=TITLE&amp;text=POST_SUMMARY+-+PERMALINK&amp;rating=5',
	),
	'socialit-pusha' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Pusha', 'socialit')).__(' (Swedish)', 'socialit'),
		'share'=>__('Push this on ', 'socialit').__('Pusha', 'socialit'),
		'baseUrl'=>'http://www.pusha.se/posta?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-hatena' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Hatena Bookmarks', 'socialit')).__(' (Japanese)', 'socialit'),
		'share'=>__('Bookmarks this on ', 'socialit').__('Hatena Bookmarks', 'socialit'),
		'baseUrl'=>'http://b.hatena.ne.jp/add?mode=confirm&amp;url=PERMALINK&amp;title=TITLE',
	),
	'socialit-mylinkvault' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('MyLinkVault', 'socialit' ) ),
		'share'=>__('Store this link on ', 'socialit').__('MyLinkVault', 'socialit'),
		'baseUrl'=>'http://www.mylinkvault.com/link-page.php?u=PERMALINK&amp;n=TITLE',
	),
	'socialit-slashdot' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('SlashDot', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('SlashDot', 'socialit'),
		'baseUrl'=>'http://slashdot.org/bookmark.pl?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-squidoo' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Squidoo', 'socialit' ) ),
		'share'=>__('Add to a lense on ', 'socialit').__('Squidoo', 'socialit'),
		'baseUrl'=>'http://www.squidoo.com/lensmaster/bookmark?PERMALINK',
	),
	'socialit-propeller' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Propeller', 'socialit' ) ),
		'share'=>__('Submit this story to ', 'socialit').__('Propeller', 'socialit'),
		'baseUrl'=>'http://www.propeller.com/submit/?url=PERMALINK',
	),
	'socialit-faqpal' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('FAQpal', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('FAQpal', 'socialit'),
		'baseUrl'=>'http://www.faqpal.com/submit?url=PERMALINK',
	),
	'socialit-evernote' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Evernote', 'socialit' ) ),
		'share'=>__('Clip this to ', 'socialit').__('Evernote', 'socialit'),
		'baseUrl'=>'http://www.evernote.com/clip.action?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-meneame' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Meneame', 'socialit')).__(' (Spanish)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Meneame', 'socialit'),
		'baseUrl'=>'http://meneame.net/submit.php?url=PERMALINK',
	),
	'socialit-bitacoras' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Bitacoras', 'socialit')).__(' (Spanish)', 'socialit'),
		'share'=>__('Submit this to ', 'socialit').__('Bitacoras', 'socialit'),
		'baseUrl'=>'http://bitacoras.com/anotaciones/PERMALINK',
	),
	'socialit-jumptags' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('JumpTags', 'socialit' ) ),
		'share'=>__('Submit this link to ', 'socialit').__('JumpTags', 'socialit'),
		'baseUrl'=>'http://www.jumptags.com/add/?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-bebo' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Bebo', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Bebo', 'socialit'),
		'baseUrl'=>'http://www.bebo.com/c/share?Url=PERMALINK&amp;Title=TITLE',
	),
	'socialit-n4g' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('N4G', 'socialit' ) ),
		'share'=>__('Submit tip to ', 'socialit').__('N4G', 'socialit'),
		'baseUrl'=>'http://www.n4g.com/tips.aspx?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-strands' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Strands', 'socialit' ) ),
		'share'=>__('Submit this to ', 'socialit').__('Strands', 'socialit'),
		'baseUrl'=>'http://www.strands.com/tools/share/webpage?title=TITLE&amp;url=PERMALINK',
	),
	'socialit-orkut' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Orkut', 'socialit' ) ),
		'share'=>__('Promote this on ', 'socialit').__('Orkut', 'socialit'),
		'baseUrl'=>'http://promote.orkut.com/preview?nt=orkut.com&amp;tt=TITLE&amp;du=PERMALINK&amp;cn=POST_SUMMARY',
	),
	'socialit-tumblr' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Tumblr', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Tumblr', 'socialit'),
		'baseUrl'=>'http://www.tumblr.com/share?v=3&amp;u=PERMALINK&amp;t=TITLE',
	),
	'socialit-stumpedia' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Stumpedia', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('Stumpedia', 'socialit'),
		'baseUrl'=>'http://www.stumpedia.com/submit?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-current' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Current', 'socialit' ) ),
		'share'=>__('Post this to ', 'socialit').__('Current', 'socialit'),
		'baseUrl'=>'http://current.com/clipper.htm?url=PERMALINK&amp;title=TITLE',
	),
	'socialit-blogger' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Blogger', 'socialit' ) ),
		'share'=>__('Blog this on ', 'socialit').__('Blogger', 'socialit'),
		'baseUrl'=>'http://www.blogger.com/blog_this.pyra?t&amp;u=PERMALINK&amp;n=TITLE&amp;pli=1',
	),
	'socialit-plurk' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Plurk', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Plurk', 'socialit'),
		'baseUrl'=>'http://www.plurk.com/m?content=TITLE+-+PERMALINK&amp;qualifier=shares',
	),
	'socialit-dzone' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('DZone', 'socialit' ) ),
		'share'=>__('Add this to ', 'socialit').__('DZone', 'socialit'),
		'baseUrl'=>'http://www.dzone.com/links/add.html?url=PERMALINK&title=TITLE&description=POST_SUMMARY',
	),	
	'socialit-kaevur' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Kaevur', 'socialit')).__(' (Estonian)', 'socialit'),
		'share'=>__('Share this on ', 'socialit').__('Kaevur', 'socialit'),
		'baseUrl'=>'http://kaevur.com/submit.php?url=PERMALINK',
	),
	'socialit-virb' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Virb', 'socialit' ) ),
		'share'=>__('Share this on ', 'socialit').__('Virb', 'socialit'),
		'baseUrl'=>'http://virb.com/share?external&v=2&url=PERMALINK&title=TITLE',
	),	
	'socialit-boxnet' => array(
		'check' => sprintf( __( 'Check this box to include %s in your bookmarking menu', 'socialit'), __('Box.net', 'socialit' ) ),
		'share'=>__('Add this link to ', 'socialit').__('Box.net', 'socialit'),
		'baseUrl'=>'https://www.box.net/api/1.0/import?url=PERMALINK&name=TITLE&description=POST_SUMMARY&import_as=link',
	),	
);
ksort( $socialit_bookmarks_data, SORT_STRING ); //sort array by keys
?>