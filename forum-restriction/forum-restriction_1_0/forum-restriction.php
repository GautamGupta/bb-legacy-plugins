<?php
/*
Plugin Name: Forum Restriction
Plugin URI:
Description: This is intended to restrict access to any forum to specifically listed individuals.
Author: David Bessler
Author URI: http://davidbessler.com
Version: 1.0
*/

/*  EDIT THE ARRAY BELOW:  FORUM ID ON THE RIGHT, COMMA-SEPARATED LIST OF ALLOWED USERS ON THE RIGHT.*/
		$allowed_in_forum = array(
		"1" => "David Bessler,testman",
		"3" => "David Bessler",
		);
/*  END EDITING. */

/*  NAME or TITLE HIJACKINGS */

function forum_restriction_alter_front_page_forum_name( $r ) {
	global $bb_current_user,$forum,$allowed_in_forum;
	if (is_front()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$forum->forum_id])) {
			$r = '[X]'.$r;
			return $r;
		} else {
			return $r;
		}
	}
}

add_filter('get_forum_name', 'forum_restriction_alter_front_page_forum_name');

function forum_restriction_alter_front_page_topic_name( $r ) {
		global $bb_current_user,$topic,$allowed_in_forum;
	if (is_front()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$topic->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$topic->forum_id])) {
			$r = '[X] restricted topic from'.get_forum_name($topic->forum_id);
			return $r;
		} else {
			$r .='';
			return $r;
		}
	} else {
		return $r;
	}
}

add_filter('get_topic_title', 'forum_restriction_alter_front_page_topic_name');

/*  LINK HIJACKINGS */

function forum_restriction_alter_front_page_forum_link( $r ) {
		global $bb_current_user,$forum,$allowed_in_forum;
	if (is_front()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$forum->forum_id])) {
			$r ='';
			return $r;
		} else {
			return $r;
		}
	}
}

add_filter('get_forum_link', 'forum_restriction_alter_front_page_forum_link');

function forum_restriction_alter_front_page_topic_link( $r ) {
		global $bb_current_user,$topic,$allowed_in_forum;
	if (is_front()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$topic->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$topic->forum_id])) {
			$r ='';
			return $r;
		} else {
			return $r;
		}
	} else {
	return $r;
	}
}

add_filter('get_topic_link', 'forum_restriction_alter_front_page_topic_link');

/*  PAGE HIJACKINGS */

function forum_restriction_hijack_forum_page() {
		global $bb_current_user,$forum,$allowed_in_forum;
	if (is_forum()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$forum->forum_id])) {
			$wheretogo = bb_nonce_url( bb_get_option('uri'));
			header( 'Location:'.$wheretogo );
		} else {
		}
	}
}

add_action('bb_forum.php_pre_db', 'forum_restriction_hijack_forum_page');

function forum_restriction_hijack_topic_page() {
		global $bb_current_user,$topic,$allowed_in_forum;
	if (is_topic()){
		if ($bb_current_user){
			$pos = strpos($allowed_in_forum[$topic->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($allowed_in_forum[$topic->forum_id])) {
			$wheretogo = bb_nonce_url( bb_get_option('uri'));
			header( 'Location:'.$wheretogo );
		} else {
		}
	}
}

add_action('bb_topic.php_pre_db', 'forum_restriction_hijack_topic_page');