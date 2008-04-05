<?php
/*
Plugin Name: Mouldy old cookies for bbPress
Plugin URI: http://bbpress.org/plugins/topic/mouldy-old-cookies-for-bbPress
Description: Reverts bbPress to use old style authentication cookies
Author: Sam Bauers
Author URI: 
Version: 1.0

Version History:
1.0		: Initial Release
*/

if ( !function_exists('wp_validate_auth_cookie') ) {
	function wp_validate_auth_cookie($cookie = '') {
		if ( empty($cookie) ) {
			if ( !$cookies = get_mouldy_old_cookie_login() ) {
				return false;
			}
		}
		
		$user = bb_get_user_by_name($cookies['login']);
		if ( ! $user ) {
			return false;
		}
		if ( md5($user->user_pass) !== $cookies['password'] ) {
			return false;
		}
		
		return $user->ID;
	}
}

if ( !function_exists('wp_generate_auth_cookie') ) {
	function wp_generate_auth_cookie($user_id, $expiration) {
		$user = bb_get_user($user_id);
		mouldy_old_cookie( bb_get_option( 'usercookie' ), $user->user_login, $expiration );
		mouldy_old_cookie( bb_get_option( 'passcookie' ), md5( $user->user_pass ) );
		return true;
	}
}

if ( !function_exists('wp_set_auth_cookie') ) {
	function wp_set_auth_cookie($user_id, $remember = false) {
		if ( $remember ) {
			$expiration = $expire = time() + 1209600;
		} else {
			$expiration = time() + 172800;
			$expire = 0;
		}
		
		wp_generate_auth_cookie($user_id, $expiration);
	}
}

function mouldy_old_cookie( $name, $value, $expires = 0 ) {
	if ( !$expires ) {
		$expires = time() + 604800;
	}
	
	if ( bb_get_option( 'cookiedomain' ) ) {
		setcookie( $name, $value, $expires, bb_get_option( 'cookiepath' ), bb_get_option( 'cookiedomain' ) );
	} else {
		setcookie( $name, $value, $expires, bb_get_option( 'cookiepath' ) );
	}
}

function get_mouldy_old_cookie_login() {
	if ( empty($_COOKIE[bb_get_option( 'usercookie' )]) || empty($_COOKIE[bb_get_option( 'passcookie' )]) ) {
		return false;
	}
	
	return array('login' => $_COOKIE[bb_get_option( 'usercookie' )], 'password' => $_COOKIE[bb_get_option( 'passcookie' )]);
}
?>