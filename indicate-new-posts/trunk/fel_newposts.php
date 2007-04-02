<?php
/*
Plugin Name: Indicate New Posts
Plugin URI: 
Description: Makes topics with posts made since the user was last here <strong>bold</strong>. This plugin REQUIRES the <a href="http://bbpress.org/plugins/topic/24?replies=2">Simple Onlinelist</a> plugin.
Author: fel64
Version: 0.7
Author URI: http://www.loinhead.net/
*/

function user_last_online_init() {
	global $bbdb, $bb_current_user, $bb_table_prefix;

	$now = get_online_timeout_time();
	
	if ( bb_is_user_logged_in() ) :
		$user_last_online = $bbdb->get_var("SELECT activity FROM ".$bb_table_prefix."online WHERE user_id=$bb_current_user->ID LIMIT 1");
				
		if ( strtotime($user_last_online) < $now ) :
			bb_update_usermeta($bb_current_user->ID, "last_visit", $user_last_online);
		endif;		
	endif;
}
add_action('bb_init', 'user_last_online_init', 1); // load before online init

if (!function_exists('is_tags')) {
	function is_tags()
	{
		return is_tag();
	}
}

function fel_indicatenew($title)
{
	global $topic, $bb_current_user;
	if ( bb_is_user_logged_in() )
	{
		$feluser = bb_get_user($bb_current_user->ID);
		if( ($topic->topic_time > $feluser->last_visit) && ( $topic->topic_last_poster != $feluser->ID ) )
		{
			$title = '<strong>' . $title . '</strong>';
		}
	}
	return($title);
}
if (is_front() || is_forum() || is_tags()) {
	add_filter('topic_title', 'fel_indicatenew');
}
?>