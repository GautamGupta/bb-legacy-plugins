<?php
/*
Plugin Name: MD5 insecurity for bbPress
Plugin URI: http://bbpress.org/plugins/topic/72
Description: Changes the password hashing in bbPress to use old-skool MD5
Author: Sam Bauers
Author URI: 
Version: 1.0

Version History:
1.0		: Initial Release
*/



if ( !function_exists( 'bb_check_login' ) ) {
	function bb_check_login($user, $pass, $already_md5 = false) {
		global $bbdb;
		$user = bb_user_sanitize( $user );
		if ($user == '') {
			return false;
		}
		$user = bb_get_user_by_name( $user );
		
		if ( !$already_md5 ) {
			if ( wp_check_password($pass, $user->user_pass) ) {
				// If using new phpass password, rehash.
				if ( strlen($user->user_pass) > 32 ) {
					$hash = wp_hash_password($pass);
					$bbdb->query("UPDATE $bbdb->users SET user_pass = '$hash' WHERE ID = '$user->ID'");
					global $bb_cache;
					$bb_cache->flush_one( 'user', $user->ID );
					$user = bb_get_user( $user->ID );
				}
			
				return $user;
			}
		} else {
			if ( md5($user->user_pass) == $pass ) {
				return $user;
			}
		}
		
		return false;
	}
}

if ( !function_exists( 'wp_hash_password' ) ) {
	function wp_hash_password($password) {
		return md5($password);
	}
}
?>