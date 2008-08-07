<?php
/*
Plugin Name: Mini Track
Plugin URI: http://bbpress.org/plugins/topic/130
Description: A simple way to count and track both members and non-members as they move around your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.8

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Update: This plugin now inserts itself into the footer automatically, no template edits required unless you want custom placement.
To use this MANUALLY, put   <?php mini_track(1); ?>  in your template where you want the output to display. 
If you only want it on the front page you can do it like this: <?php if (is_front() ) {mini_track(1);} ?>
If you also want a list of the member names, use <?php mini_track(2); ?>
You can see a list of users and locations by going to  your-forum-url.com/?mini_track_display
*/

// change any option you don't want to false

$mini_track_options['automatic_in_footer'] = true;		// set false if you place mini_track(1) or mini_track(2) in your templates
$mini_track_options['show_names_in_footer'] = true;		// display user names (optional)
$mini_track_options['show_only_on_front_page'] = false;	// everywhere or just front page
$mini_track_options['last_online_in_profile'] = true;		// automatic in profiles
$mini_track_options['online_status_in_post'] = true;		// automatic in posts
$mini_track_options['track_time'] = 30; 				// minutes

$mini_track_options['display_refresh_time'] = 30; 		// seconds for real-time display update
$mini_track_options['fast_index'] = false;				// false tracks NAT/proxy users better, true = faster IP only

$mini_track_options['style']="
	.mini_track {font-size:1em; color:black; text-align:center;} 
	.mini_track strong, mini_track a {color: #006400;}
	.mini_track_online {font-size:90%; color:green;} 
	.mini_track_offline {font-size:90%; color:#bbb;}
";

$mini_track_options['bots']="worm|bot|spider|crawler|Googlebot|mediapartners|MSNBOT|YahooSeeker|Overture|VerticalCrawler|FastSearch|modspider|froogle|"
."Bloglines|ZyBorg|InfoSeek|inktomi|looksmart|Scooter|Slurp|AskJeeves\/Teoma|teoma_agent|teomaagent|MARTINI|Gigabot|Netcraft|SurveyBot|ia_archiver|"
."lycos|scooter|fast\-webcrawler|slurp\@inktomi|turnitinbot|technorati|Findexa|NextLinks|findlinks|Gais|gaisbo|zyborg|surveybot|"
."bloglines|BlogSearch|PubSub|pubsub|Syndic8|userland|gigabot|become\.com";

/* STOP EDITING HERE */

$bb->load_options = true;	// better db performance, but probably won't work here, put it into your bb-config.php

add_action('bb_foot','mini_track');
add_action('bb_admin_footer', 'mini_track',999);
bb_register_activation_hook( __FILE__,  'mini_track_activation');
if ($mini_track_options['style']) {add_action('bb_head','mini_track_style');}
if ($mini_track_options['last_online_in_profile']) {add_filter( 'get_profile_info_keys','mini_track_profile_key');}
if ($mini_track_options['online_status_in_post']) {add_filter( 'post_author_title', 'mini_track_online_filter',100);}
if (isset($_GET['mini_track_display']) || isset($_GET['mini_track_reset'])) {add_action('bb_init','mini_track_display');}

function mini_track($display=0) {  
global $mini_track_options, $mini_track_done, $mini_track, $bb_current_user; 

if (isset($mini_track_done)) {return;} $mini_track_done=true; // only run once if manually called
if (empty($display)) {
	if ($mini_track_options['automatic_in_footer']) {$display=1;}
	if ($mini_track_options['show_names_in_footer']) {$display=2;}
	if ($display && $mini_track_options['show_only_on_front_page'] && !is_front()) {$display=0;}
}
$mini_track=bb_get_option('mini_track');
$users=0; $members=0; $bots=0; $onpage=0; $names=""; $index="";

if ($mini_track_options['fast_index']) {
$index=ip2long($_SERVER["REMOTE_ADDR"]);	// this has some limitations and bugs - disable if you use the next two lines 
} else {
// more advanced indexing technique on the next two lines - disable for speed at expense of no NAT/proxy detection
$indexlist=array('REMOTE_ADDR','HTTP_USER_AGENT','HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM'); 
foreach ($indexlist as $check) {if (isset($_SERVER[$check])) {$index.=$_SERVER[$check];}} $index=md5($index);
}

// store first seen date when they arrive
if ($bb_current_user->ID && is_array($mini_track) && !array_key_exists($index,$mini_track)) {@bb_update_usermeta($bb_current_user->ID,'mini_track',date('r'));} 

$mini_track[$index]->time=time(); 
$mini_track[$index]->url=addslashes(urlencode($_SERVER["REQUEST_URI"]));	// this also has some issues with dynamic url cruft but is acceptable
$mini_track[$index]->id=$bb_current_user->ID;
if ($bb_current_user->ID) {$mini_track[$index]->name=$bb_current_user->data->user_login;}
elseif (eregi($mini_track_options['bots'],$_SERVER['HTTP_USER_AGENT'])) {$mini_track[$index]->bot=1;}	// detect/save bots
++$mini_track[$index]->pages;	// count how many pages they've viewed

$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
$cutoff=time()-$mini_track_options['track_time']*60; 	// seconds to consider user "online" 

foreach ($mini_track as $key=>$value) { 
if ($value->time<$cutoff) {
// store last seen date when they leave
if ($mini_track[$key]->id) {@bb_update_usermeta($mini_track[$key]->id,'mini_track',date('r',$mini_track[$key]->time));} 
unset($mini_track[$key]);}
else  {$users++; 
	if ($value->id) {$members++; $names.="<a href='$profile$value->id'>$value->name</a>, ";} 
	if (isset($value->bot)) {$bots++;} 
	if ($value->url==$mini_track[$index]->url) {$onpage++;}
           }
}

if ($display) {	// to do: internationalize i18n
if (bb_current_user_can('administrate')) {$start="<a href='?mini_track_display'>"; $end="</a>";} else {$start=""; $end="";}
echo "<div class='mini_track'>There are <strong>$users</strong> total $start"."users online".$end.".";
echo " <strong>$members</strong> of them are members";
if ($members && ($display==2 || $display=="members")) {echo ": ".rtrim($names,", ").".";} else {echo ".";}
if ($onpage>1 && !$mini_track_options['show_only_on_front_page']) {echo " <strong>$onpage</strong> of them are on this page.</div>";}
}

@bb_update_option('mini_track',$mini_track);	// this serialized string will get nasty for more than a few dozen people online
}

function mini_track_display() {
if (!bb_current_user_can('administrate')) {return;}
global $mini_track_options;
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
if (isset($_GET['mini_track_reset'])) {@bb_update_option('mini_track','');}
echo '<html><head><meta http-equiv="refresh" content="'.$mini_track_options['display_refresh_time'].';url='.$bb_uri.'?mini_track_display" />
<style>table {border:1px solid #111;} table td {text-align:center;} table .link {text-align:left;} table th.link {padding-left:5em;}
table th {background: #aaa;} .alt {background: #eee;} .time {font-size:85%;} .bot {color:red;} .guest {color:green;} </style></head><body>';
echo "<div style='float:right;'>[<a href='$bb_url?mini_track_reset'><small>reset</small></a>]</div>";
mini_track(1); 
echo "<br clear=both /><br /><table width='99%' cellpadding=1 cellspacing=1>
<tr class=alt><th>#</th><th>user</th><th>pages</th><th>last activity</th><th class=link>last URL</th></tr>";
$mini_track=bb_get_option('mini_track');
$mini_track=array_reverse($mini_track,true);
$counter=0;
foreach ($mini_track as $key=>$value) {
$url=urldecode($value->url);
echo "<tr".(($counter % 2) ? " class=alt" : "")."><td align=right>".(++$counter)."</td><td>";
if ($value->id) echo "<a href='$profile$value->id'>$value->name</a>";
elseif (isset($value->bot)) {echo "<span class=bot>bot</span>";} else {echo "<span class=guest>guest</span>";}
echo "</td><td>".intval($value->pages)."</td><td class=time>".ceil(((time())-$value->time+1)/60)." minutes ago</td><td class=link><a href='$url'>$url</a></td></tr>";
}
echo "</table></body></html>";
exit();
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
	$bb_user_cache[$user->ID]->last_online="<span title='".$bb_user_cache[$user->ID]->mini_track."'>".bb_since($bb_user_cache[$user->ID]->mini_track,1).__(' ago ')."</span>";
	if (mini_track_online()) {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_online'>".__("online")."</span>)";} 
	else {$bb_user_cache[$user->ID]->last_online.=" (<span class='mini_track_offline'>".__("offline")."</span>)";} 
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('last_online' => array(0, __('Last Online'))), array_slice((array) $keys,  1));    
}
return (array) $keys;
}

function mini_track_style() {global $mini_track_options; echo "<style type='text/css'>".$mini_track_options['style']."</style>"; }

function mini_track_activation() {bb_update_option('mini_track','');}
?>