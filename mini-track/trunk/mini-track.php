<?php
/*
Plugin Name: Mini Track
Plugin URI: http://bbpress.org/plugins/topic/130
Description: A simple way to count and track both members and non-members as they move around your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

To use this, put   <?php mini_track(1); ?>  in your template where you want the output to display. 
If you only want it on the front page you can do it like this: <?php if (is_front() ) {mini_track(1);} ?>
If you also want a list of the member names, use <?php mini_track(2); ?>
You can see a list of users and locations by going to  your-forum-url.com/?mini_track_display
This plugin was written in 15 minutes and demonstrates how easy it is to write plugins for bbPress.
*/

function mini_track($display=0) {  
// $display=2;		// uncomment this line if you want automatic display in every footer without template edits
global $mini_track_done, $bb_current_user; 
if (isset($mini_track_done)) {return;} $mini_track_done=true; // only run once if manually called
$mini_track=bb_get_option('mini_track');
$users=0; $members=0; $bots=0; $onpage=0; $names=""; $index="";
$bots="worm|bot|spider|crawler|Googlebot|mediapartners|MSNBOT|YahooSeeker|Overture|VerticalCrawler|FastSearch|modspider|froogle|"
."Bloglines|ZyBorg|InfoSeek|inktomi|looksmart|Scooter|Slurp|AskJeeves\/Teoma|teoma_agent|teomaagent|MARTINI|Gigabot|Netcraft|SurveyBot|ia_archiver|"
."lycos|scooter|fast\-webcrawler|slurp\@inktomi|turnitinbot|technorati|Findexa|NextLinks|findlinks|Gais|gaisbo|zyborg|surveybot|"
."bloglines|BlogSearch|PubSub|pubsub|Syndic8|userland|gigabot|become\.com";
$index=ip2long($_SERVER["REMOTE_ADDR"]);	// this has some limitations and bugs - disable if you use the next two lines 
/*
// more advanced indexing technique on the next two lines - disabled by default for speed
$indexlist=array('REMOTE_ADDR','HTTP_USER_AGENT','HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM'); 
foreach ($indexlist as $check) {if (isset($_SERVER[$check])) {$index.=$_SERVER[$check];}} $index=md5($index);
*/
$url=addslashes(urlencode($_SERVER["REQUEST_URI"]));	// this also has some issues with dynamic url cruft but is acceptable
if ($bb_current_user->ID && is_array($mini_track) && !array_key_exists($index,$mini_track)) {@bb_update_usermeta($bb_current_user->ID,'mini_track',date('r'));} // store first seen date
$mini_track[$index]->time=time(); 
$mini_track[$index]->location=$url;
$mini_track[$index]->member=$bb_current_user->ID;
if ($bb_current_user->ID) {$mini_track[$index]->name=$bb_current_user->data->user_login;}
elseif (eregi($bots,$_SERVER['HTTP_USER_AGENT'])) {$mini_track[$index]->bot=1;}

$cutoff=time()-30*60; // seconds to consider user "online" :: 30 minute default, reduce if desired
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
foreach ($mini_track as $key=>$value) { 
if ($value->time<$cutoff) {
if ($mini_track[$key]->member) {@bb_update_usermeta($mini_track[$key]->member,'mini_track',date('r',$mini_track[$key]->time));} // store last seen date
unset($mini_track[$key]);}
else {$users++; 
	if ($value->member) {$members++; $names.="<a href='$profile$value->member'>$value->name</a>, ";} 
	if (isset($value->bot)) {$bots++;} 
	if ($value->location==$url) {$onpage++;}
	}
}
if ($display) {
if (bb_current_user_can('administrate')) {$start="<a href='?mini_track_display'>"; $end="</a>";} else {$start=""; $end="";}
echo "<div class='mini_track'>There are <strong>$users</strong> total $start"."users online".$end.".";
echo " <strong>$members</strong> of them are members";
if ($members && ($display==2 || $display=="members")) {echo ": ".rtrim($names,", ").".";} else {echo ".";}
echo " <strong>$onpage</strong> of them are on this page.</div>";
}
@bb_update_option('mini_track',$mini_track);	// this serialized string will get nasty for more than a few dozen people online
}

function mini_track_display() {
if (!bb_current_user_can('administrate')) {return;}
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
if (isset($_GET['mini_track_reset'])) {@bb_update_option('mini_track','');}
echo '<html><head><meta http-equiv="refresh" content="30;url='.$bb_uri.'?mini_track_display" /></head><pre>';
echo "<div style='float:right;'>[<a href='$bb_url?mini_track_reset'><small>reset</small></a>]</div>";
mini_track(1); 
echo "<br clear=both>";
$mini_track=bb_get_option('mini_track');
$mini_track=array_reverse($mini_track,true);
foreach ($mini_track as $key=>$value) {
$url=urldecode($value->location);
if ($value->member) echo "<a href='$profile$value->member'>$value->name</a>";
elseif (isset($value->bot)) {echo "bot";} else {echo "guest";}
echo " - ".ceil(((time())-$value->time+1)/60)." minutes ago - <a href='$url'>$url</a><br>";
}
exit();
}

function mini_track_online($user_id=0) {
global $mini_track_online, $bb_post, $user; 	
	if (!isset($mini_track_online)){	
		$mini_track=bb_get_option('mini_track');
		foreach ($mini_track as $key=>$value) {if ($mini_track[$key]->member) {$mini_track_online[$mini_track[$key]->member]=true;}}
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
	$bb_user_cache[$user->ID]->last_online="<span title='".$bb_user_cache[$user->ID]->mini_track."'>".bb_since($bb_user_cache[$user->ID]->mini_track,1).__(' ago ')."</span>";
	if (mini_track_online()) {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_online'>".__("online")."</span>)";} 
	else {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_offline'>".__("offline")."</span>)";} 
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('last_online' => array(0, __('Last Online'))), array_slice((array) $keys,  1));    
}
return (array) $keys;
}

function mini_track_css() {
echo '<style type="text/css">
.mini_track_online {font-size:90%; color:green;}
.mini_track_offline {font-size:90%; color:#bbb;}
</style>'; 
}

add_action('bb_foot','mini_track');
add_action('bb_head','mini_track_css');
add_filter( 'get_profile_info_keys','mini_track_profile_key');
add_filter( 'post_author_title', 'mini_track_online_filter',100);

if (isset($_GET['mini_track_display']) || isset($_GET['mini_track_reset'])) {add_action('bb_init','mini_track_display');}
?>