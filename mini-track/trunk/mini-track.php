<?php
/*
Plugin Name: Mini Track
Plugin URI: http://bbpress.org/plugins/topic/130
Description: A simple way to count and track both members and non-members as they move around your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Update: This plugin now inserts itself into the footer automatically, no template edits required unless you want custom placement.
To use this MANUALLY, put   <?php mini_track(1); ?>  in your template where you want the output to display. 
If you only want it on the front page you can do it like this: <?php if (is_front() ) {mini_track(1);} ?>
If you also want a list of the member names, use <?php mini_track(2); ?>
For statistics also, use <?php mini_track_statistics(); ?> in addition.
You can see a list of users and locations by going to  your-forum-url.com/?mini_track_display
*/

// change any option you don't want to false

$mini_track_options['automatic_in_footer'] = true;		// set false if you place mini_track(1) or mini_track(2) in your templates
$mini_track_options['show_names_in_footer'] = true;		// display member names next to member count (optional)
$mini_track_options['show_only_on_front_page'] = false;         // everywhere or just front page
$mini_track_options['last_online_in_profile'] = true;		// show automatically in profiles
$mini_track_options['online_status_in_post'] = true;		// show automatically in posts

$mini_track_options['track_time']['members'] = 30;		// minutes before timeout  (logout is immediate timeout, so large is okay)
$mini_track_options['track_time']['guests'] = 30; 			// minutes (busy sites may want 5-10 minutes)
$mini_track_options['track_time']['bots'] = 15; 			// minutes (busy sites may want 5 minutes)

$mini_track_options['statistics_in_footer'] = true;		// set false if you place mini_track_statistics() manually in your templates
$mini_track_options['statistics_only_on_front_page'] = false;	// everywhere or just front page

$mini_track_options['display_refresh_time'] = 30; 		// seconds for real-time display update
$mini_track_options['fast_index'] = false;				// false = accurately tracks NAT/proxy/spoofing users  //  true = faster but by IP only

$mini_track_options['ban_speed'] = 50;				// temporarily ban any user for the track_time if they exceed this many pages per minute 
									// (not recommended to set this lower than 50 ppm because some bots like google move that fast)
									// set to 0 (zero) to disable.
$mini_track_options['ban_pages'] = 200;				// temporarily ban any user for the track_time if they exceed this many pages in a single session
									// (not recommended to set this lower than 200 because some bots like google take that many at once)
									// set to 0 (zero) to disable.
									
									// you will be able to also temporarily ban (or unban) via the realtime display panel
									// note about banning: it still loads the bbPress core, just doesn't serve any content
$mini_track_options['style']="
	.mini_track, .mini_track_statistics {font-size:1em; color:black; text-align:center;} 
	.mini_track_num, .mini_track strong, mini_track a {color: #006400;}
	.mini_track_num {font-family: monospaced;}
	.mini_track_wrap {white-space: nowrap;}
	.mini_track img {margin: 0 3px -2px 1px; border:0;}
	.mini_track_online {font-size:90%; color:green;} 
	.mini_track_offline {font-size:90%; color:#aaa;}
	.mini_track_title {border-bottom: 1px dashed #ccc; cursor: help;}
";

/* STOP EDITING HERE */

$mini_track_options['geoip'] =  "mysql";		// false;   	 // "ip2c"; 		// cc lookup ->  false | mysql | ip2c
$mini_track_options['flags'] = "/images/flags/";	// false; 	// "/images/flags/"; 	// images instead of cc - path to flags

$mini_track_options['debug'] = true;       // true = shows more info when you hover over IP in display panel - makes saved data very large, don't use regularly

$bb->load_options = true;	// better db performance, but probably won't work here, put it into your bb-config.php

// I don't recommend fiddling with this - if you know of other common bots, let me know for addition

$mini_track_options['bots']="msnbot\-Products|msnbot\-media|MS Search|MSNBOT|MSN Stealth|" // Microsoft
."Googlebot\-Image|Googlebot\/Test|AdsBot-Google|Mediapartners|gsa-crawler|Feedfetcher|Froogle|Googlebot|Google Stealth|"   // Google
."Ask Jeeves|AskJeeves|teoma_agent|teomaagent|Teoma|Ask Stealth|"	// Ask (uses Teoma)
."YahooSeeker|YahooFeedSeeker|YahooVideoSearch|Yahoo-MMCrawler|Yahoo\-MMAudVid|Yahoo\-Blogs|Yahoo\! Mindset|YahooYSMcm|Yahoo Test|Scooter|Yahoo\! Slurp|Slurp\/cat|DE Slurp|Slurp China|Y\!J-BSC|Fast Crawler|slurp\@inktomi|slurp|inktomi|Yahoo stealth|" // Yahoo
."Gigabot|Netcraft|ia_archiver|fast\-webcrawler|turnitinbot|technorati|Findexa|NextLinks|findlinks|Scooter|Lycos|almaden\.ibm|almaden|"
."ZyBorg|InfoSeek|looksmart|MARTINI|SurveyBot|Overture|VerticalCrawler|FastSearch|modspider|NAMEPROTECT|WebFilter|Robozilla|"
."Gais|gaisbo|zyborg|surveybot|Bloglines|BlogSearch|blogpulse|internetseer|sitecheck|MarkWatch|flunky|SlySearch|"
."AideRSS|BlogSearch|PubSub|pubsub|Syndic8|userland|become\.com|Cowbot|Yeti|naver|Sogou|Netcache|Netapp|BlogTick|Baiduspider|"
."Toluu|SimplePie|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|dtSearchSpider|eidetica|fantom|Girafa|grub\.org|IncyWincy|Sqworm|larbin|ScoutAbout|"
."UbiCrawler|Webclipping|Webrank|Websquash|Whizbang|cosmos|Zealbot|semanticdiscovery|Snapbot|thumbshots|"
."Virtual Reach|Wordpress\/2\.|Yandex|linkcheck|idbot|id\-search|Nutch|heritrix|WebAlta|Indy Library|Intelliseek|LNSpiderguy|LinkWalker|"	
."Java\/|Wget\/|libcurl|libwww|Python-urllib|urllib|lwp-trivial|GT::WWW|Snoopy|Microsoft URL Control|HTTP::Lite|PHPCrawl|URI::Fetch|PECL::HTTP|Zend_Http_Client|http client|HTTPClient|"	// scrapers
."parser|crawler|indexer|archiver|worm|spider|bot";	// generic

$mini_track_options['bots']=explode("|",str_replace("\\","", strtolower($mini_track_options['bots']) ));	// prep bot list

// hooks and triggers
add_action('bb_init','mini_track_init');
add_action('bb_foot','mini_track',99);
add_action('bb_admin_footer', 'mini_track',99);
add_action('bb_user_login', 'mini_track_login');
add_action('bb_user_logout', 'mini_track_logout');
if ($mini_track_options['style']) {add_action('bb_head','mini_track_style');}
if ($mini_track_options['last_online_in_profile']) {add_filter( 'get_profile_info_keys','mini_track_profile_key',100);}
if ($mini_track_options['online_status_in_post']) {add_filter( 'post_author_title', 'mini_track_online_filter',100);}

// statistics hooks
if ($mini_track_options['statistics_in_footer']) {add_action('bb_foot','mini_track_statistics',100);}
add_action( 'bb_new_post','mini_track_statistics_update');
add_action( 'bb_delete_post','mini_track_statistics_update');
add_action('register_user','mini_track_statistics_update');
add_action('user_register','mini_track_statistics_update');


// admin hooks
if (defined('BB_IS_ADMIN') && BB_IS_ADMIN && isset($_GET['action']) && $_GET['action']=="activate" && $_GET['plugin'] && strpos($_GET['action'],basename(__FILE__)) ) {
	bb_register_activation_hook( __FILE__,  'mini_track_activation');		
	@require_once("mini-track-admin.php");
}
if (isset($_GET['mini_track_display']) || isset($_GET['mini_track_reset']) || isset($_GET['mini_track_ip']) || isset($_GET['mini_track_ban']) || isset($_GET['mini_track_unban']))  {
	if (isset($_GET['mini_track_ip'])) {add_action('bb_init','mini_track_ip',100);}
	else {add_action('bb_init','mini_track_display',100);}
	@require_once("mini-track-admin.php");
}

function mini_track_init() {  
global $mini_track, $mini_track_options,  $mini_track_current, $bb_current_user, $bbdb;

$mini_track=bb_get_option('mini_track');	// start with latest data from db
$time=time(); 					// snapshot time for all calculations

if (!empty($mini_track)) {
// clean up expired entries (especially if plugin has been inactive)
$cutoff['guests']=$time-$mini_track_options['track_time']['guests']*60; 	// seconds to consider guests "online" 
$cutoff['members']=$time-$mini_track_options['track_time']['members']*60; 	// seconds to consider members "online" 
$cutoff['bots']=$time-$mini_track_options['track_time']['bots']*60; 		// seconds to consider bots "online" 

foreach ($mini_track as $key=>$value) { 
	if ($value->id) {$type="members";} elseif (isset($value->bot)) {$type="bots";} else {$type="guests";}
	if ($value->time<$cutoff[$type]) { // bye-bye
		if ($value->id) {mini_track_logout($value->id);}	// store last seen date when members leave
		unset($mini_track[$key]);
	}
}
}

list($index,$debug)=mini_track_index($bb_current_user->ID);	// calculate "index" for current user

// store first seen date (and referer when they arrive)
if (empty($mini_track) || !array_key_exists($index,$mini_track)) {

$mini_track[$index]->seen=$time; 	// we can't store the time before this as it might create a new index and we need to know that
if ($bb_current_user->ID) {@bb_update_usermeta($bb_current_user->ID,'mini_track',date('r'));}
if (isset($_SERVER['HTTP_REFERER'])) {
$referer=mini_track_safe_url($_SERVER['HTTP_REFERER']); $uri=bb_get_option('uri'); $found=strpos($referer,str_replace(array("https://","http://","www."),"",trim($uri,"/ ")));
if (!empty($referer) && (strpos($referer,"http://")===0 || strpos($referer,"https://")===0)  && strpos($uri,$referer)===false && ($found===false || $found>20)) {$mini_track[$index]->referer=$referer;}
}

$mini_track[$index]->id=intval($bb_current_user->ID);	// save current user id
$mini_track[$index]->ip=mini_track_remote_addr();	// grap ip or proxy/cache/nat ip if valid
if ($mini_track_options['geoip']=="mysql" || $mini_track_options['geoip']===true) {	// geoip methods
	$mini_track[$index]->cc=$bbdb->get_var("SELECT cc FROM maxmind WHERE  start <= inet_aton('".$mini_track[$index]->ip."') ORDER BY `start` DESC LIMIT 1");
} elseif ($mini_track_options['geoip']=="ip2c") {  // slow but easy ip2c
	require_once(rtrim(dirname(__FILE__),' /\\')."/ip2c/ip2c.php");
 	$ip2c = new ip2country(rtrim(dirname(__FILE__),' /\\')."/ip2c/ip-to-country.bin");
 	$res = $ip2c->get_country($mini_track[$index]->ip);
 	if ($res == false) {$mini_track[$index]->cc="??";} else {$mini_track[$index]->cc=$res['id2'];}
}

// determine bots from agent - moved to first seen to save cpu cycles and re-check unnecessary
if ($bb_current_user->ID) {$mini_track[$index]->name=$bb_current_user->data->user_login;}
else {if ($bot=mini_track_bot_lookup()) {$mini_track[$index]->bot=$bot;}}	// detect/save bots

$mini_track[$index]->pages=1;
} // end of first seen checks
else {
$mini_track[$index]->pages++;	// count how many pages they've viewed

// check for ban-able activity
$active=$time - $mini_track[$index]->seen;	// seconds active
if ($mini_track[$index]->pages>30 && $active>30 && !($bb_current_user->ID && bb_current_user_can('administrate'))) {
if ($mini_track_options['ban_speed'] && ($mini_track[$index]->pages/$active)>$mini_track_options['ban_speed']/60) {$mini_track[$index]->ban=1;}
if ($mini_track_options['ban_pages'] && $mini_track[$index]->pages>$mini_track_options['ban_pages']) {$mini_track[$index]->ban=1;}
} 

} // end repeat user

$mini_track[$index]->time=$time;	
$mini_track[$index]->url=mini_track_safe_url($_SERVER['REQUEST_URI']); // current page
if ($mini_track_options['debug']) {$mini_track[$index]->debug=$debug;}  // save debug info if in debug mode

// tally new tracking data for all users
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
$users=0; $members=0; $bots=0; $onpage=0; $names=""; $cc="";
foreach ($mini_track as $key=>$value) { 
	++$users; 
	if ($value->id) {++$members; $names.="<a href='$profile$value->id'>$value->name</a>, ";} 
	if (isset($value->bot)) {++$bots;} 
	if ($value->url==$mini_track[$index]->url) {$onpage++;}
	if (isset($value->ban)) {$ban[$value->ip]=1;}	// build list of banned IPs for double-check later
	if ($mini_track_options['geoip'] && isset($value->cc)) {@$cc[$value->cc]++;} 
} // foreach

mini_track_save(); 	// store the data

// enforce bans
if ((isset($mini_track[$index]->ban) || isset($ban[$index->ip])) && !($bb_current_user->ID && bb_current_user_can('administrate'))) { // don't let admin ban themselves
	@header("HTTP/1.1 503 Service Temporarily Unavailable");
	@header("Status: 503 Service Temporarily Unavailable");
	@header("Connection: Close");
	@exit(); 	// user has a temporary ban either because of page count or speed (or same IP as ban)  no content for them until the timeout clears	
}			

// remember results for possible use later in page
$mini_track_current['users']=$users;		// user count
$mini_track_current['members']=$members;	// member count
$mini_track_current['bots']=$bots;			// bot count
$mini_track_current['names']=rtrim($names,", ");// member names
$mini_track_current['onpage']=$onpage;		// number on same page
if ($mini_track_options['geoip'] && is_array($cc)) {			// country code counts
$mini_track_current['cc']=" from "; arsort($cc); 
foreach ($cc as $key=>$value) {
if ($mini_track_options['flags']) {$mini_track_current['cc'].="<img alt=' $key ' title='$key [$value]' src='".$mini_track_options['flags'].strtolower($key).".png'>";} else {$mini_track_current['cc'].="<span title='[$value]'>$key</span>, ";}}
$mini_track_current['cc']=rtrim($mini_track_current['cc'],", ").(($mini_track_options['flags']) ? ' ' : '.');	
}

}	// end mini_track_init

function mini_track($display=0) {	// displays formatted output
global $mini_track, $mini_track_options, $mini_track_current, $mini_track_done; 
if (isset($mini_track_done)) {return;} $mini_track_done=true;	// only run once if manually called
if (empty($display)) {
	if ($mini_track_options['automatic_in_footer']) {$display=1;}
	if ($mini_track_options['show_names_in_footer']) {$display=2;}
	if ($display && $mini_track_options['show_only_on_front_page'] && !is_front()) {$display=0;}
}
if ($display) {	// to do: internationalize i18n
if (bb_current_user_can('administrate')) {$start="<a target='_self' href='?mini_track_display'>"; $end="</a>";} else {$start=""; $end="";}
$output="<div class='mini_track'>";
$output.="There are <span class='mini_track_num'>".$mini_track_current['users']."</span> $start"."users online".$end;
if ($mini_track_options['geoip'] && isset($mini_track_current['cc'])) {$output.=$mini_track_current['cc'];} else {$output.=".";}
if ($mini_track_current['onpage']>1 && !$mini_track_options['show_only_on_front_page']) {$output.=" <span class='mini_track_num'>".$mini_track_current['onpage']."</span> of them are on this page.";}
if ($mini_track_current['members']>0) {$output.=" <span class='mini_track_num'>".$mini_track_current['members']."</span> of them are members";}
if ($mini_track_current['members']>0 && ($display==2 || $display=="members")) {$output.=": ".$mini_track_current['names'];} 
elseif ($mini_track_current['members']>0 && $display==1) {$output.=".";}
echo $output."</div>";
}
}

function mini_track_safe_url($url) {return substr(strip_tags(stripslashes(trim(urldecode($url),"?& "))),0,128);}

function mini_track_style() {global $mini_track_options; echo "<style type='text/css'>".$mini_track_options['style']."</style>"; } //stylesheet injection

function mini_track_save() {
global $mini_track, $bbdb;
// @bb_update_option('mini_track',$mini_track);	// argh stupid bbPress read before write wastes queries
// $bbdb->get_var("UPDATE bb_topicmeta SET `meta_value` = '' WHERE topic_id = '0' AND meta_key = 'mini_track' LIMIT 1");
// $bbdb->update( $bbdb->topicmeta, array( 'meta_value' => bb_maybe_serialize( $mini_track )), array( 'topic_id' => 0, 'meta_key' => 'mini_track' ) );

if (bb_get_option('bb_db_version')>1600) {$table="$bbdb->meta"; $where="WHERE object_type='bb_option'";}		// 1.0 compatibility
else {$table="$bbdb->topicmeta"; $where="WHERE topic_id = 0";}								// 0.9 compatibility
$value=addslashes(bb_maybe_serialize($mini_track));    	// this serialized string will get nasty for more than a few dozen people online		 
@$bbdb->query("UPDATE $table SET meta_value='$value' $where AND meta_key='mini_track'  LIMIT 1");	// save serialized results to db
}

function mini_track_index($id=0) {
global $mini_track_options;
$id=intval($id);
if ($mini_track_options['fast_index']) {
// this way has some limitations due to re-use of ip's by proxies and NATs 
$index="$id_".mini_track_remote_addr();
} else {
// more advanced indexing technique on the next two lines - disable for speed at expense of no NAT/proxy detection
$indexlist=array('REMOTE_ADDR','HTTP_USER_AGENT','HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM','HTTP_CLIENT_IP'); 
$meta=$id; foreach ($indexlist as $check) {if (isset($_SERVER[$check])) {$meta.=" ".$_SERVER[$check];}} $index=md5($meta);
}
// $array['index']=$index; $array['debug']=$meta; return $array;
return array($index,$meta);
}

function mini_track_remote_addr() {	// detects true IP of known proxies and NATs
// 67\.195\.|74\.6\. == inktomi/yahoo slurp, sometimes masqurades as regular browser too!
if (ereg("^(67\.195\.|74\.6\.)",$_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
return $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {return $_SERVER['REMOTE_ADDR'];} 
}

function mini_track_bot_lookup() { 
global $mini_track_options; $bot=0;
$agent=strtolower($_SERVER['HTTP_USER_AGENT']);
foreach ($mini_track_options['bots'] as $key=>$name) {if (!(strpos($agent,$name)===false)) {$bot=$key+1; break;}}
if ($bot==0)  {
if (ereg("^(67\.195\.|74\.6\.)",$_SERVER['REMOTE_ADDR'])) {$bot=1+array_search("yahoo stealth",$mini_track_options['bots']);} // yahoo fakes it
elseif (ereg("^(65.5[2-5]\.)",$_SERVER['REMOTE_ADDR'])) {$bot=1+array_search("msn stealth",$mini_track_options['bots']);} // microsoft fakes it
}
return $bot;
}

function mini_track_online($user_id=0) {
global $mini_track_online, $bb_post, $user; 	
	if (!isset($mini_track_online)){	
		$mini_track=bb_get_option('mini_track'); $mini_track_online=array();
		if (is_array($mini_track)) {foreach ($mini_track as $key=>$value) {if ($mini_track[$key]->id) {$mini_track_online[$mini_track[$key]->id]=true;}}}
	}
if (!$user_id) {if (isset($bb_post)) {$user_id=$bb_post->poster_id;} elseif (isset($user)) {$user_id=$user->ID;}}
return array_key_exists($user_id,$mini_track_online);
}

function mini_track_online_filter($titlelink) {
	if (mini_track_online()) {echo "<div class='mini_track_online'>".__("online")."</div>";} 
	else {echo "<div class='mini_track_offline'>".__("offline")."</div>";} 
return $titlelink;
}

function mini_track_profile_key($keys) {	// inserts post_count into profile without hacking
global $self, $user, $bb_user_cache;  // nasty trick
if (empty($self)==true && isset($_GET['tab'])==false && bb_get_location()=="profile-page") {
	if ($mini_track=$bb_user_cache[$user->ID]->mini_track) {
	$bb_user_cache[$user->ID]->last_online="<span class='mini_track_title' title='".$mini_track."'>".bb_since($mini_track,1)." ".__('ago')."</span>";
	if (mini_track_online()) {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_online'>".__("online")."</span>)";} 
	else {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_offline'>".__("offline")."</span>)";} 
	} else {$bb_user_cache[$user->ID]->last_online=" <span class='mini_track_offline'>".__("unknown")."</span> ";}
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('last_online' => array(0, __('Last Online'))), array_slice((array) $keys,  1));    
}
return (array) $keys;
}

function mini_track_login($id=0) {
global $mini_track;
$mini_track=bb_get_option('mini_track');
list($index,$debug)=mini_track_index(0); unset($mini_track[$index]); 	// remove  the entity with same info but 0 user id
@bb_update_option('mini_track',$mini_track);
}

function mini_track_logout($id=0) {
global $mini_track, $bb_current_user;
$mini_track=bb_get_option('mini_track');
if (!$id) {$id=$bb_current_user->ID;}
if ($id) {
	foreach ($mini_track as $key=>$value) {
		if ($value->id==$id) {@bb_update_usermeta($value->id,'mini_track',date('r',$value->time)); unset($mini_track[$key]); @bb_update_option('mini_track',$mini_track); break;}
	}
}
}

function mini_track_statistics() {
global $mini_track, $mini_track_options, $mini_track_statistics_done; 
if (isset($mini_track_statistics_done)) {return;} $mini_track_statistics_done=true;	// only run once if manually called
if ($mini_track_options['statistics_only_on_front_page'] && !is_front()) {return;}
$results=bb_get_option('mini_track_statistics'); if (!$results) {$results=mini_track_statistics_update();}
$months=ceil((time()-strtotime($results->days)) / (3600 * 24 * 30));
$output="<div class='mini_track_statistics'>";
$output.="<span class='mini_track_wrap'>";
$output.=" <span class='mini_track_num'>".$results->posts."</span> ".__('posts in'); 
$output.=" <span class='mini_track_num'>".$results->topics."</span> ".__('topics over'); 
$output.=" <span class='mini_track_num'>$months</span> ".__('months by'); 
$output.=" <span class='mini_track_num'>".$results->active."</span> ".__('of')." <span class='mini_track_num'>".$results->members."</span> ".__('members.'); 
$output.=" </span> <span class='mini_track_wrap'> ";
if (!empty($results->latest)) {$output.=__('Latest:'); $uri=bb_get_option('uri')."profile.php?id="; foreach ($results->latest as $key=>$value) {$output.=" <a href='$uri$key'>$value</a>, ";}}
$output=trim($output,", ")."</span></div> ";
echo $output;
}

function mini_track_statistics_update($x="") {
global $bbdb, $mini_track;	// this is kind of insane overkill to do each new post / new user - might want to calculate incrementally somehow
$query="SELECT count(*) as posts, count(distinct topic_id) as topics, count(distinct poster_id) as active, min(post_time) as days FROM bb_posts WHERE post_status=0";
$results=$bbdb->get_results($query);
$usertable=$bbdb->users; $usermeta=$bbdb->usermeta;
$base="FROM $usertable as t1 LEFT JOIN $usermeta as t2 on t1.ID=t2.user_id WHERE user_status=0 AND (meta_key='bb_capabilities' AND NOT (meta_value LIKE '%inactive%' OR meta_value LIKE '%blocked%'))";
$query="SELECT user_login,ID $base ORDER BY user_registered DESC LIMIT 3";
$results2=$bbdb->get_results($query);
$results2=array_reverse($results2);
foreach ($results2 as $key=>$value) {$results[0]->latest[$value->ID]=$value->user_login;}
$query="SELECT count(*) as members $base";
$results2=$bbdb->get_results($query);
$results[0]->members=$results2[0]->members;
bb_update_option('mini_track_statistics',$results[0]);
return $results[0];
}

?>