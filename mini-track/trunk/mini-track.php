<?php
/*
Plugin Name: Mini Track
Plugin URI: http://bbpress.org/plugins/topic/130
Description: A simple way to count and track both members and non-members as they move around your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.0

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Update: This plugin now inserts itself into the footer automatically, no template edits required unless you want custom placement.
To use this MANUALLY, put   <?php mini_track(1); ?>  in your template where you want the output to display. 
If you only want it on the front page you can do it like this: <?php if (is_front() ) {mini_track(1);} ?>
If you also want a list of the member names, use <?php mini_track(2); ?>
For statistics, use <?php mini_track_statistics(); ?>
You can see a list of users and locations by going to  your-forum-url.com/?mini_track_display
*/

// change any option you don't want to false

$mini_track_options['automatic_in_footer'] = true;		// set false if you place mini_track(1) or mini_track(2) in your templates
$mini_track_options['show_names_in_footer'] = true;		// display user names (optional)
$mini_track_options['show_only_on_front_page'] = false;         // everywhere or just front page
$mini_track_options['last_online_in_profile'] = true;		// automatic in profiles
$mini_track_options['online_status_in_post'] = true;		// automatic in posts

$mini_track_options['track_time']['members'] = 30;		// minutes before timeout  (logout is immediate timeout)
$mini_track_options['track_time']['guests'] = 30; 			// minutes
$mini_track_options['track_time']['bots'] = 15; 			// minutes

$mini_track_options['statistics_in_footer'] = true;		// set false if you place mini_track_statistics() manually in your templates
$mini_track_options['statistics_only_on_front_page'] = false;	// everywhere or just front page

$mini_track_options['display_refresh_time'] = 30; 		// seconds for real-time display update
$mini_track_options['fast_index'] = false;				// false = accurately tracks NAT/proxy/spoof users // true = faster by IP only

$mini_track_options['style']="
	.mini_track, .mini_track_statistics {font-size:1em; color:black; text-align:center;} 
	.mini_track_num, .mini_track strong, mini_track a {color: #006400;}
	.mini_track_num {font-family: monospaced;}
	.mini_track_wrap {white-space: nowrap;}
	.mini_track_online {font-size:90%; color:green;} 
	.mini_track_offline {font-size:90%; color:#aaa;}
	.mini_track_title {border-bottom: 1px dashed #ccc; cursor: help;}
";

$mini_track_options['bots']="Googlebot\-Image|Googlebot|mediapartners|MSNBOT|MSN stealth|froogle|"
."Yahoo\! Slurp|Slurp|YahooSeeker|Yahoo Test|Yahoo stealth|Ask Jeeves|AskJeeves|teoma_agent|Teoma|teomaagent|slurp\@inktomi|inktomi|"
."Gigabot|Netcraft|ia_archiver|lycos|fast\-webcrawler|turnitinbot|technorati|Findexa|NextLinks|findlinks|"
."ZyBorg|InfoSeek|looksmart|Scooter|MARTINI|SurveyBot|Overture|VerticalCrawler|FastSearch|modspider|"
."Gais|gaisbo|zyborg|surveybot|Bloglines|BlogSearch|blogpulse|thumbshots|Snapbot|"
."Feedfetcher|BlogSearch|PubSub|pubsub|Syndic8|userland|become\.com|Yeti|naver|Sogou|Netcache|Netapp|BlogTick|Baiduspider|"
."Toluu|SimplePie|"
."Virtual Reach|Wordpress\/2\.|Yandex|linkcheck|idbot|id\-search|Nutch|larbin|heritrix|WebAlta|Indy Library|"	
."Java\/|Wget\/|libcurl|libwww|Python-urllib|urllib|lwp-trivial|GT::WWW|Snoopy|HTTP::Lite|PHPCrawl|URI::Fetch|Zend_Http_Client|http client|PECL::HTTP|"	// scrapers
."parser|crawler|indexer|archiver|worm|spider|bot";	// generic

/* STOP EDITING HERE */

$mini_track_options['debug'] = false;

$bb->load_options = true;	// better db performance, but probably won't work here, put it into your bb-config.php

$mini_track_options['bots']=explode("|",str_replace("\\","", strtolower($mini_track_options['bots']) ));

// hooks and triggers
add_action('bb_foot','mini_track');
add_action('bb_init','mini_track_init');
add_action('bb_admin_footer', 'mini_track',999);
add_action('bb_user_login', 'mini_track_login');
add_action('bb_user_logout', 'mini_track_logout');
bb_register_activation_hook( __FILE__,  'mini_track_activation');
if ($mini_track_options['style']) {add_action('bb_head','mini_track_style');}
if ($mini_track_options['last_online_in_profile']) {add_filter( 'get_profile_info_keys','mini_track_profile_key',100);}
if ($mini_track_options['online_status_in_post']) {add_filter( 'post_author_title', 'mini_track_online_filter',100);}
if (isset($_GET['mini_track_display']) || isset($_GET['mini_track_reset'])) {add_action('bb_init','mini_track_display',100);}
if (isset($_GET['mini_track_ip'])) {add_action('bb_init','mini_track_ip',100);}
// statistics hooks
if ($mini_track_options['statistics_in_footer']) {add_action('bb_foot','mini_track_statistics',200);}
add_action( 'bb_new_post','mini_track_statistics_update');
add_action( 'bb_delete_post','mini_track_statistics_update');
add_action('register_user','mini_track_statistics_update');
add_action('user_register','mini_track_statistics_update');

function mini_track_init() {  
global $mini_track, $mini_track_options,  $mini_track_current, $bb_current_user, $bbdb;
$users=0; $members=0; $bots=0; $onpage=0; $names=""; $index=""; $debug="";
$mini_track=bb_get_option('mini_track');
list($index,$debug)=mini_track_index($bb_current_user->ID);

// store first seen date (and referer when they arrive)
if (!is_array($mini_track) || !array_key_exists($index,$mini_track)) {
$mini_track[$index]->seen=time(); if ($bb_current_user->ID) {@bb_update_usermeta($bb_current_user->ID,'mini_track',date('r'));}
$referer=mini_track_safe_url($_SERVER['HTTP_REFERER']); $uri=bb_get_option('uri'); $found=strpos($referer,str_replace(array("https://","http://","www."),"",trim($uri,"/ ")));
if (!empty($referer) && (strpos($referer,"http://")===0 || strpos($referer,"https://")===0)  && strpos($uri,$referer)===false && ($found===false || $found>20)) {$mini_track[$index]->referer=$referer;}

$mini_track[$index]->id=intval($bb_current_user->ID);	// save current user id

// determine bots from agent - moved to first time seen to save cpu cycles and re-check unnecessary
if ($bb_current_user->ID) {$mini_track[$index]->name=$bb_current_user->data->user_login;}
else {if ($bot=mini_track_bot_lookup()) {$mini_track[$index]->bot=$bot;}}	// detect/save bots

} // end of first seen checks

if ($mini_track_options['debug']) {$mini_track[$index]->debug=$debug;}  // save debug info if in debug mode

$mini_track[$index]->time=time(); 			 
$mini_track[$index]->ip=mini_track_remote_addr();
++$mini_track[$index]->pages;	// count how many pages they've viewed
$mini_track[$index]->url=mini_track_safe_url($_SERVER['REQUEST_URI']); // current page

// caclulate cutoff time for deletion
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
$time=time(); 
$cutoff['guests']=$time-$mini_track_options['track_time']['guests']*60; 	// seconds to consider user "online" 
$cutoff['members']=$time-$mini_track_options['track_time']['members']*60; 	// seconds to consider user "online" 
$cutoff['bots']=$time-$mini_track_options['track_time']['bots']*60; 		// seconds to consider user "online" 

foreach ($mini_track as $key=>$value) { 
if ($value->id) {$type="members";} elseif (isset($value->bot)) {$type="bots";} else {$type="guests";}
if ($value->time<$cutoff[$type]) {
// store last seen date when they leave
if ($value->id) {mini_track_logout($value->id);} 
unset($mini_track[$key]);
} else  {$users++; 
	if ($value->id) {$members++; $names.="<a href='$profile$value->id'>$value->name</a>, ";} 
	if (isset($value->bot)) {$bots++;} 
	if ($value->url==$mini_track[$index]->url) {$onpage++;}
           }
}
// @bb_update_option('mini_track',$mini_track);	// argh stupid bbPress read before write wastes queries
// $bbdb->get_var("UPDATE bb_topicmeta SET `meta_value` = '' WHERE topic_id = '0' AND meta_key = 'mini_track' LIMIT 1");
// $bbdb->update( $bbdb->topicmeta, array( 'meta_value' => bb_maybe_serialize( $mini_track )), array( 'topic_id' => 0, 'meta_key' => 'mini_track' ) );

if (bb_get_option('bb_db_version')>1600) {$table="$bbdb->meta"; $where="WHERE object_type='bb_option'";}		// 1.0 compatibility
else {$table="$bbdb->topicmeta"; $where="WHERE topic_id = 0";}								// 0.9 compatibility
$value=addslashes(bb_maybe_serialize( $mini_track));
@$bbdb->query("UPDATE $table SET meta_value='$value' $where AND meta_key='mini_track'  LIMIT 1");

// this serialized string will get nasty for more than a few dozen people online
$mini_track_current['users']=$users;
$mini_track_current['members']=$members;
$mini_track_current['bots']=$bots;
$mini_track_current['names']=rtrim($names,", ");
$mini_track_current['onpage']=$onpage;
}	// mini_track_init


function mini_track($display=0) {
global $mini_track, $mini_track_options, $mini_track_current, $mini_track_done; 
if (isset($mini_track_done)) {return;} $mini_track_done=true;	// only run once if manually called
if (empty($display)) {
	if ($mini_track_options['automatic_in_footer']) {$display=1;}
	if ($mini_track_options['show_names_in_footer']) {$display=2;}
	if ($display && $mini_track_options['show_only_on_front_page'] && !is_front()) {$display=0;}
}
if ($display) {	// to do: internationalize i18n
if (bb_current_user_can('administrate')) {$start="<a href='?mini_track_display'>"; $end="</a>";} else {$start=""; $end="";}
echo "<div class='mini_track'>There are <span class='mini_track_num'>".$mini_track_current['users']."</span> total $start"."users online".$end.".";
if ($mini_track_current['onpage']>1 && !$mini_track_options['show_only_on_front_page']) {echo " <span class='mini_track_num'>".$mini_track_current['onpage']."</span> of them are on this page.";}
if ($mini_track_current['members']>0) {echo " <span class='mini_track_num'>".$mini_track_current['members']."</span> of them are members";}
if ($mini_track_current['members']>0 && ($display==2 || $display=="members")) {echo ": ".$mini_track_current['names'];} 
elseif ($mini_track_current['members']>0 && $display==1) {echo ".";}
echo "</div>";
}
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

function mini_track_style() {global $mini_track_options; echo "<style type='text/css'>".$mini_track_options['style']."</style>"; }

function mini_track_safe_url($url) {return substr(strip_tags(stripslashes(trim(urldecode($url),"?& "))),0,128);}

function mini_track_activation() {
global $mini_track, $mini_track_statistics, $mini_track_done; unset($mini_track_done); 
$mini_track=array(); @bb_update_option('mini_track',$mini_track);
$mini_track_statistics=bb_get_option('mini_track_statistics'); 
if (empty($mini_track_statistics)) {$mini_track_statistics=array(); @bb_update_option('mini_track_statistics',$mini_track_statistics);}
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
function mini_track_login($id=0) {
global $mini_track;
$mini_track=bb_get_option('mini_track');
list($index,$debug)=mini_track_index(0); unset($mini_track[$index]); 	// remove  the entity with same info but 0 user id
@bb_update_option('mini_track',$mini_track);
}

function mini_track_ip(){
if (!bb_current_user_can('administrate') || !$_GET['mini_track_ip']) {return;}
$ip=$_GET['mini_track_ip']; $rdns=gethostbyaddr($ip); if ($rdns==$ip) {$rdns="(no rDNS)";}
echo "<html><pre><h2>IP ".$ip."</h2><h3>".$rdns."</h3>"; 
$data=mini_track_ip_lookup($ip); 
foreach ($data as $key=>$value) {
if (eregi("abuse|tech|nettype|comment|remark|ReferralServer|signature|auth|encryption",$key)===false) {echo "$key: $value <br />";}
}
exit();
}

function mini_track_ip_lookup($ip,$server=0){
$host=array('ws.arin.net','wq.apnic.net','www.db.ripe.net','lacnic.net','www.afrinic.net');
$keyword=array('arin.net','apnic.net','ripe.net','lacnic.net','afrinic.net');
$path=array('/whois/?queryinput=','/apnic-bin/whois.pl?searchtext=','/whois/?form_type=simple&searchtext=','/cgi-bin/lacnic/whois?query=','/cgi-bin/whois?form_type=simple&searchtext=');
do {unset($data); 
if ($fp = fsockopen ($host[$server], 80, &$errno, &$errstr, 10)) {
	$request = "GET $path[$server]$ip HTTP/1.0\r\nHost: $host[$server]\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\r\n\r\n"; 
	$page=''; fputs ($fp, $request); while (!feof($fp)) {$page.=fgets ($fp,1024);} fclose ($fp); 	// echo $page;
	preg_match("/\<pre\>(.*)\<\/pre\>/sim",$page,$temp); $lines=explode("\n",strip_tags($temp[0]));
	foreach ($lines as $line) {$line=trim($line);if ((!ereg('^\#|\%.*$',$line)) && ($line>'')) {$temp=explode(":",$line,2); $data[trim($temp[0])] = trim($temp[1]);}}
} else {$data['error'] = "$errstr ($errno)\n";}         
$server=0; for ($i = 1; $i <= count($host); $i++){if (strpos($data['ReferralServer'],$keyword[$i])){$server=$i;break;}}
} while ($server>0);
return $data;
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

function mini_track_display() {
if (!bb_current_user_can('administrate')) {return;}
global $mini_track, $mini_track_current, $mini_track_options;
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
if (isset($_GET['mini_track_reset'])) {mini_track_activation(); mini_track_init();}
echo '<html><head><title>'.count($mini_track).' Users Online &laquo; '.bb_get_option('name').'</title>
<meta http-equiv="refresh" content="'.$mini_track_options['display_refresh_time'].';url='.$bb_uri.'?mini_track_display" />
<style>table {border:1px solid #111; font-size:14px; font-family: arial, san-serif; line-height:150%;} table td {text-align:center; white-space:nowrap;} table .link {text-align:left;} table th.link {padding-left:5em;}
table th {background: #aaa;} .alt {background: #eee;} .tiny {font-size:12px;} .bot {color:red; font-size:90%;} .guest {color:green;} 
.link div {padding-left: 5px; width:500px; white-space:nowrap; overflow; hidden;} </style>
<script>window.onload=titlelink; function titlelink() {blank="_blank"; for (i=0;x=document.links[i];++i){x.target=blank; if (!x.title.length) {x.title=x.href;}}};</script>
</head><body>';
echo "<div style='float:right;'>[<a href='$bb_url?mini_track_reset'><small>reset</small></a>]</div>";
mini_track(2); 
echo "<br clear=both /><table width='99%' cellpadding=1 cellspacing=1>
<tr class=alt><th>#</th><th>user</th><th>ip</th><th>referer</th><th>pages</th><th>time online</th><th>last activity</th><th class=link>last URL</th></tr>";
$mini_track=array_reverse($mini_track,true);
$counter=0;
foreach ($mini_track as $key=>$value) {
$url=urldecode($value->url);
echo "<tr".(($counter % 2) ? " class=alt" : "")."><td align=right class=tiny> ".(++$counter)." </td><td>";	// line number
if ($value->id) echo "<a  href='$profile$value->id'>$value->name</a>";			// member profile link
elseif (isset($value->bot)) {echo "<span class=bot>".preg_replace("/[^A-Za-z_ ]+?/is","",$mini_track_options['bots'][$value->bot-1])."</span>";} else {echo "<span class=guest>guest</span>";}
echo "</td><td class=tiny><a ".(($mini_track_options['debug']) ?" title='$value->debug' " : "")." href='?mini_track_ip=$value->ip'>$value->ip</a></td>";
if (isset($value->referer)) {$parse_url=parse_url($value->referer);	// referer
		$host=substr(ereg_replace("^(www[0-9]?|search|talk|community|support|foro|mitmachen|board[s]?|forum[s]?)(\.?)","",$parse_url['host']),0,30); 
		echo "<td><a href='$value->referer'>".$host."</a></td>";} else {echo "<td>&nbsp;</td>";
} 
echo "<td>".intval($value->pages)."</td>";						// page count
echo "<td class=tiny>".ceil((($value->time)-$value->seen+1)/60)." minutes</td>";	// total activity time
echo "<td class=tiny>".ceil(((time())-$value->time+1)/60)." minutes ago</td>";		// last activity time
echo "<td class=link><div style='overflow:hidden;'><a href='$url'>$url</a></div></td></tr>"; // last url
}
echo "</table></body></html>";
exit();
}
?>