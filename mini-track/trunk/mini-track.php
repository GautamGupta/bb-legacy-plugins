<?php
/*
Plugin Name: Mini Track
Plugin URI: http://bbpress.org/plugins/topic/130
Description: A simple way to count and track both members and non-members as they move around your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

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
$users=0; $members=0; $onpage=0; $names=""; $index="";
$index=ip2long($_SERVER["REMOTE_ADDR"]);	// this has some limitations and bugs - disable if you use the next two lines 
/*
// more advanced indexing technique on the next two lines - disabled by default for speed
$indexlist=array('REMOTE_ADDR','HTTP_USER_AGENT','HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM'); 
foreach ($indexlist as $check) {if (isset($_SERVER[$check])) {$index.=$_SERVER[$check];}} $index=md5($index);
*/
$url=addslashes(urlencode($_SERVER["REQUEST_URI"]));	// this also has some issues with dynamic url cruft but is acceptable
$mini_track[$index]->location=$url;
$mini_track[$index]->member=$bb_current_user->ID;
$mini_track[$index]->name=$bb_current_user->data->user_login;
$mini_track[$index]->time=time()-1217548800;	// the subtraction is to keep the number small since it's serialized and not stored integer
$cutoff=(time()-1217548800)-30*60; 	// seconds to consider user "online" :: 30 minute default, reduce if desired
$bb_uri=bb_get_option('uri'); $profile=$bb_uri."profile.php?id=";
foreach ($mini_track as $key=>$value) { if ($value->time<$cutoff) {unset($mini_track[$key]);}
else {$users++; if ($value->member) {$members++; $names.="<a href='$profile$value->member'>$value->name</a>, ";} if ($value->location==$url) {$onpage++;}}}
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
echo '<html><head><meta http-equiv="refresh" content="30;url='.$bb_uri.'?mini_track_display" /></head><pre>';
mini_track(1); echo "<br>";
$mini_track=bb_get_option('mini_track');
foreach ($mini_track as $key=>$value) {
$url=urldecode($value->location);
echo (($value->member) ? "<a href='$profile$value->member'>$value->name</a>" : "anonymous")." - ".ceil(((time()-1217548800)-$value->time+1)/60)." minutes ago - <a href='$url'>$url</a><br>";
}
exit();
}

add_action('bb_foot','mini_track');
if (isset($_GET['mini_track_display'])) {add_action('bb_init','mini_track_display');}
?>