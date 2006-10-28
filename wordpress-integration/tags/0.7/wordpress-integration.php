<?php
/*
Plugin Name: WordPress Integration
Plugin URI: http://bbpress.org/#
Description: Tightly integrates user accounts between WordPress and bbPress
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.7
*/

function wpbb_user_sanitize( $text, $raw, $strict ) {
	if ( $strict )
		return $text;

	$username = strip_tags($raw);
	// Kill octets
	$username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
	$username = preg_replace('/&.+?;/', '', $username); // Kill entities
	return $username;
}

add_filter( 'user_sanitize', 'wpbb_user_sanitize', -1, 3);

?>
