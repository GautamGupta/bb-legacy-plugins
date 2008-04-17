<?php
/*
Plugin Name: Read-Only Forums
Description: Prevent all or certain members from starting topics or just replying in certain forums while allowing posting in others. Moderators and administrators can always post. Note that this does not hide forums, just prevents posting.
Plugin URI:  http://bbpress.org/plugins/topic/103
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions: tinker with settings below, install, activate
*/

global $read_only_forums,$bb_current_user, $bb_roles;

// edit users (and forums) by NUMBER below until an admin menu can be made

$read_only_forums['deny_all_start_topic']=false;  // true = stop ALL members from starting topics in ALL forums 
$read_only_forums['deny_all_reply']=false;	  // true = stop ALL members from replying to topics in ALL forums

$read_only_forums['deny_forums_start_topic']=array(9,15,22);  // which forums should ALL members NOT be able to start topics
$read_only_forums['deny_forums_reply']=array(9,15,22);  	  // which forums should ALL members NOT be able to reply to posts

$read_only_forums['allow_members_start_topic']= array(1=>array(1,2,3,4,5,6,7), 2=>array(9,10,11));  // allow override for this member=>forums
$read_only_forums['allow_members_reply']=	array(1=>array(1,2,3,4,5,6,7), 2=>array(9,10,11)); 	// allow override for this member=>forums

$read_only_forums['deny_members_start_topic']= array(54321=>array(1,2,3,4,5,6,7), 34567=>array(1,2,3)); // deny this specific member=>forums
$read_only_forums['deny_members_reply'] =      array(54321=>array(1,2,3,4,5,6,7), 34567=>array(1,2,3)); // deny this specific member=>forums

$read_only_forums['allow_roles_always']=array('moderator','administrator','keymaster'); // these types of users can always start/reply

// stop editing here


function read_only_forums($retvalue, $capability, $args) {

if ($capability!="write_post" && $capability!="write_topic") {return $retvalue;} // not our problem

global $read_only_forums,$bb_roles,$bb_current_user;

if (!$bb_current_user->ID) {return $retvalue;}	// not logged in

if (in_array(reset($bb_current_user->roles),$read_only_forums['allow_roles_always'])) {return true;}	// role in override list

if ($capability=='write_topic') {	// $args = forum_id	
	$forum=intval($args[1]);	
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['allow_members_start_topic'])) {echo "a:true"; return true;}
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['deny_members_start_topic']))  {echo "a:false"; return false;}

	if (in_array($forum,$read_only_forums['deny_forums_start_topic'])) {return false;} // check specific forum blocks
	if ($read_only_forums['deny_all_start_topic']) {return false;}	// stop all members from starting topics
}

if ($capability=='write_post') {	// $args = topic_id
	$topic=get_topic(intval($args[1])); $forum=$topic->forum_id;
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['allow_members_reply'])) {return true;}
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['deny_members_reply']))  {return false;}
	
	if (in_array($forum,$read_only_forums['deny_forums_reply'])) {return false;} // check specific forum blocks
	if ($read_only_forums['deny_all_reply']) {return false;} // stop all members from replying to topics	
}

return $retvalue;
}

function read_only_forums_dig($user,$forum,$list) {
	if (!is_array($list)) {return false;}	// should never happen
	if (!isset($list[$user])) {return false;} // user not even listed 
	if (!is_array($list[$user])) {if (strpos($list[$user],",")) {$list[$user]=explode(",",$list[$user]);} else {$list[$user]=array(intval($list[$user]));}} // nasty
	if (in_array($forum,$list[$user])) {return true;}
return false;	
}

add_filter('bb_current_user_can','read_only_forums',10,3);

function read_only_forums_list_forums() {
if (isset($_GET['listforums']) && bb_current_user_can('administrate')) {
foreach (get_forums() as $forum) {echo "$forum->forum_id -> $forum->forum_name <br><br>";} exit();
}
} if (isset($_GET['listforums'])) {add_action('bb_init','read_only_forums_list_forums');}

/*	not going to use this for now because it prevents overrides
if ($read_only_forums['deny_all_start_topic']) {$bb_roles->remove_cap('member','write_topics');}
if ($read_only_forums['deny_all_reply']) {$bb_roles->remove_cap('member','write_posts');}
*/
?>