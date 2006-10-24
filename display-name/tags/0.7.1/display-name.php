<?php
/*
Plugin Name: Use Display Name
Plugin URI: http://trac.bbpress.org/ticket/430
Description: Uses the display name as set by WordPress for a bbPress moderator's name instead of the login name.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.7.1

Requires at least: 0.72
Tested up to: 0.73
*/

function bb_use_display_name( $name, $id ) {
	$user = bb_get_user( $id );
	if ( empty($user->display_name) )
		return $name;
	// Delete the lines marked with //Mod to use the display name for ALL users.
	$user_obj = new BB_User( $id ); //Mod
	if ( $user_obj->has_cap('moderate') ) //Mod
		return $user->display_name;
	return $name; //Mod
}

add_filter( 'topic_last_poster', 'bb_use_display_name', 1, 2 );
add_filter( 'topic_author', 'bb_use_display_name', 1, 2 );
add_filter( 'get_post_author', 'bb_use_display_name', 1, 2 );
add_filter( 'get_user_name', 'bb_use_display_name', 1, 2 );

?>
