<?php
/*
Plugin Name: Hidden Forums
Description:  Make selected forums completely hidden except to certain members or roles. Faster than other solutions without their quirks.
Plugin URI:  http://bbpress.org/plugins/topic/
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Instructions:  add hidden forum list and exceptions below, install, activate 
*/

/* 
in the default example below:
1. forums # 500 & 501 are complete hidden 
2. roles KEYMASTER can see ANY hidden forum, ADMINISTRATOR + MODERATOR can see 500 + 501
3. users #1 can see ANY hidden forum,  # 12345 + # 34567  can see 500 + 501

(to get a list of forums by number, use forumname.com?forumlist when this plugin is active)
*/

$hidden_forums['hidden_forums']=array(500,501,502);	// hide these forums, list by comma seperated number

$hidden_forums['allow_roles']['all_forums']=array('keymaster');		// these roles can always see ALL forums regardless
$hidden_forums['allow_roles'][500]=array('administrator','moderator');	// exact formal role name, *not* ability
$hidden_forums['allow_roles'][501]=array('administrator','moderator');	// exact formal role name, *not* ability

$hidden_forums['allow_users']['all_forums']=array(1);		// these users can always see ALL forums regardless
$hidden_forums['allow_users'][500]=array(12345,34567);	// list of users by number
$hidden_forums['allow_users'][501]=array(12345,34567);	// list of users by number

/*    stop  editing  here    */

add_action('bb_init','hidden_forums_init');

function hidden_forums_init() {
global $hidden_forums, $hidden_forums_list, $hidden_forums_array, $bb_current_user;

$id=(isset($bb_current_user)) ? $bb_current_user->ID : 0;
$hidden_forums_list=array_flip($hidden_forums['hidden_forums']);

if ($id>0) {	// if id=0, don't bother searching allows
	if (in_array($id,$hidden_forums['allow_users']['all_forums'])) {return;}		// quit - don't filter anything
	$role=@reset($bb_current_user->roles);  	
	if ($role=='keymaster' && (isset($_GET['listforums']) || isset($_GET['forumlist']))) {echo "<h2>Forum List</h2>"; foreach (get_forums() as $forum) {echo "$forum->forum_id -> $forum->forum_name <br><br>";} exit();}
	if (in_array($role,$hidden_forums['allow_roles']['all_forums'])) {return;}	// quit - don't filter anything
	foreach ($hidden_forums['allow_roles'] as $key=>$value) {if (in_array($role,$value)) {unset($hidden_forums_list[$key]);}}
	foreach ($hidden_forums['allow_users'] as $key=>$value) {if (in_array($id,$value)) {unset($hidden_forums_list[$key]);}}
}

if (!empty($hidden_forums_list)) {
	$hidden_forums_array=$hidden_forums_list;
	$hidden_forums_list=implode(",",array_keys($hidden_forums_list));
 
$filters=array('get_forums', 	
	'get_topic',
	'get_thread',
	'get_thread_post_ids',	
	'get_latest_posts',	
	'get_recent_user_replies',	
	'get_user_favorites',
	'get_latest_topics',
	'get_recent_user_threads',
	'get_recent_user_threads',
	'get_latest_forum_posts',
	'get_recent_user_replies',
	'get_tagged_topic_posts',
	'get_sticky_topics',	
	'get_tagged_topics',
	'bb_is_first',	
	'bb_recent_search',
	'bb_relevant_search',
	'bb_get_first_post');

	foreach ($filters as $filter) {add_action($filter.'_where','hidden_forums_filter');}
/*
	add_action( 'bb_head', 'hidden_forums_scrub');
	add_action( 'bb_rss.php',  'hidden_forums_scrub');	
	add_action( 'do_search',  'hidden_forums_scrub');	
*/	
}
}

function hidden_forums_filter($where='') {
	global $hidden_forums_list; 
	return $where.((empty($where)) ? " WHERE " : " AND ").((strpos($where," p.")) ? "p." : "" )."forum_id NOT IN ($hidden_forums_list) ";
}

function hidden_forums_scrub() {
	global $hidden_forum_array, $forums, $topics, $super_stickies, $stickies, $posts, $titles, $recent, $relevant;
	
	$list=array('forums', 'topics', 'super_stickies', 'stickies', 'posts', 'titles', 'recent', 'relevant');
	
	foreach ($list as $item) {	
		$items=eval("$".$item);
		if (!empty($items)) {
			$flag=false;
			foreach ($items as $key=>$value) {
				if (array_key_exists($value->forum_id,$hidden_forum_array)) {unset($items[$key]); $flag=true;}
			}
			if ($flag) {eval("$".$item."=".$items);}
		}	
	}
}
?>