<?php
/*
Plugin Name: Unread Posts
Description:  Indicates previously read topics with new unread posts. Features "mark all topics read". Builds on concepts by fel64 and henrybb with feature and performance improvements. No additional plugins or tables required.
Plugin URI:  http://bbpress.org/plugins/
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.80

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
Instructions:   install, activate, edit unread style and number of topics tracked per user

todo: mark topics read for specific forums instead of just all, maybe change title link to jump to last read post
*/

$unread_posts_style=".unread_posts {color:blue;}";	// optional style - or put it into your css file and disable this line
$unread_posts_topics_per_user=100;			// how many topics to watch for each user - on a fast, small forum you could probably do 1000 

function unread_posts_init() {
global $bb_current_user;
if ($bb_current_user->ID) {		// only bother with the overhead if a user is logged in        - prep page, arrays, etc.
	if (isset($_GET['mark_all_topics_read'])) {add_action('bb_send_headers', 'up_mark_all_read');}	//  can't hook to automatically place links for this???
	
	elseif (is_topic()) {add_action('topicmeta','up_update_topics_read',200);}	// topic pages is where all the heavy lifting is done

	elseif (in_array(bb_get_location(),array('front-page','forum-page', 'tag-page','search-page','favorites-page','profile-page','view-page'))) {	// where should we affect titles
		global $up_read_topics, $up_last_posts, $unread_posts_style;
		$user = bb_get_user($bb_current_user->ID);  
		$up_read_topics=explode(",",$user->up_read_topics);  settype($up_read_topics,"array"); // unpack once, use many times
		$up_last_posts=explode(",",$user->up_last_posts); settype($up_last_posts,"array");	 // unpack once, use many times			
		add_filter('topic_title', 'up_mark_title_unread');
		if ($unread_posts_style) {add_action('bb_head', 'up_add_css');}
	}	
}
} add_action('bb_init','unread_posts_init',200);

function up_add_css() {global $unread_posts_style; echo '<style type="text/css">'.$unread_posts_style.'</style>'; } 

function up_mark_title_unread($title)  {
global $topic, $up_read_topics, $up_last_posts;	
	$up_key=array_search($topic->topic_id ,$up_read_topics);	
	if ($up_key!=false &&  $up_last_posts[$up_key]!=$topic->topic_last_post_id) {$title = '<span class=unread_posts>' . $title . '</span>';}
return $title;
}

function up_mark_all_read() {	// actually, just delete all it's meta and start fresh - eventually this could be made to just remove topics in one sub-forum only
global $bb_current_user;	
	bb_delete_usermeta($bb_current_user->ID, "up_read_topics");
	bb_delete_usermeta($bb_current_user->ID, "up_last_posts");
	wp_redirect(str_replace("mark_all_topics_read","",$GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"]));			
} 

function up_update_topics_read() {
global  $bbdb, $bb_current_user, $topic, $unread_posts_topics_per_user;
	$user = bb_get_user($bb_current_user->ID);  
		
	$up_read_topics=explode(",",$user->up_read_topics);  settype($up_read_topics,"array"); 
	$up_last_posts=explode(",",$user->up_last_posts); settype($up_last_posts,"array");	
	
	$up_key=array_search($topic->topic_id ,$up_read_topics);	
	
	if ($up_key===false) {
		$up_read_topics[]=$topic->topic_id;
		$up_last_posts[]=$topic->topic_last_post_id;
		$up_key=-2;							// flag to save save both topics and last post
	} elseif ($up_last_posts[$up_key]!=$topic->topic_last_post_id) {
		$up_last_posts[$up_key]=$topic->topic_last_post_id;		
		$up_key=-1;							// flag to save just last post update
	}
	if ($up_key==-2 && count($up_read_topics)>$unread_posts_topics_per_user) {		// trim arrays since we are going to do a full save anyway
		$up_read_topics=array_slice($up_read_topics,25-$unread_posts_topics_per_user);	// offset by 25 so we aren't constantly trimming
		$up_last_posts=array_slice($up_last_posts,25-$unread_posts_topics_per_user);
	}			

/* how we would simply do it if bbpress wasn't abusing mysql calls
if ($up_key<-1)  {bb_update_usermeta($bb_current_user->ID, "up_read_topics",implode(",",$up_read_topics));}
if ($up_key<0)     {bb_update_usermeta($bb_current_user->ID, "up_last_posts",implode(",",$up_last_posts));}
*/
// how we have to do it instead to save mysql calls - we don't even need the results cached 
// oh, because usermeta has no keys to track duplicates, we cannot use ON DUPLICATE KEY :-( woe
if ($up_key<-1)  {
	if (isset($user->up_read_topics)) {
	$bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".implode(",",$up_read_topics)."' WHERE user_id = $bb_current_user->ID AND meta_key = 'up_read_topics' LIMIT 1");
	} else {
	$bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ($bb_current_user->ID, 'up_read_topics', '".implode(",",$up_read_topics)."')");
	}
}
if ($up_key<0)   {
	if (isset($user->up_read_topics)) {
	$bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".implode(",",$up_last_posts)."' WHERE user_id = $bb_current_user->ID AND meta_key = 'up_last_posts' LIMIT 1");
	} else {
	$bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ($bb_current_user->ID, 'up_last_posts', '".implode(",",$up_last_posts)."')");
	}
}
} 

?>