<?php
/*
Plugin Name: MD5 insecurity for bbPress
Plugin URI: http://bbpress.org/plugins/topic/72
Description: Changes the password hashing in bbPress to use old-skool MD5
Author: Sam Bauers
Author URI: 
Version: 1.1

Version History:
1.0		: Initial Release
1.1		: Update for compatibility with new auth cookies
*/



if ( !function_exists( 'bb_check_login' ) ) {
	function bb_check_login($user, $pass, $already_md5 = false) {
		global $bbdb;
		$user = sanitize_user( $user );
		if ($user == '') {
			return false;
		}
		$user = bb_get_user_by_name( $user );
		
		if ( !wp_check_password($pass, $user->user_pass) ) {
			return false;
		}
		
		// If using old md5 password, rehash.
		if ( strlen($user->user_pass) > 32 ) {
			$hash = wp_hash_password($pass);
			$bbdb->query( $bbdb->prepare( "UPDATE $bbdb->users SET user_pass = %s WHERE ID = %d", $hash, $user->ID ) );
			global $bb_cache;
			$bb_cache->flush_one( 'user', $user->ID );
			$user = bb_get_user( $user->ID );
		}
		
		return $user;
	}
}

if ( !function_exists( 'wp_hash_password' ) ) {
	function wp_hash_password($password) {
		return md5($password);
	}
}
?>