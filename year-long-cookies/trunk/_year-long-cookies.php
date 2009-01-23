<?php
/*
Plugin Name:  Year Long Cookies - Remember Me
Description:  Force login cookies to last a year instead of the default two days (or two weeks). Works with 0.8.x, 0.9.x and 1.0a
Plugin URI:  http://bbpress.org/plugins/topic/87
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
*/ 

if (defined('BACKPRESS_PATH')) {   //  1.0a+

if ( !function_exists('bb_login') ) :
function bb_login( $login, $password, $remember = false ) {
	$user = bb_check_login( $login, $password );
	if ( $user && !is_wp_error( $user ) ) {
		bb_set_auth_cookie( $user->ID,true);		// true normally is 1209600 (2 weeks)  vs 172800 (2 days)
		do_action('bb_user_login', (int) $user->ID );
	}
	
	return $user;
}
endif;

if ( !function_exists( 'bb_set_auth_cookie' ) ) :
function bb_set_auth_cookie( $user_id, $remember = false, $secure = '' ) {
	global $wp_auth_object;

	if ( $remember ) {
		$expiration = $expire = time() + 31536000; 		// previously 1209600 (2 weeks) now set to a year
	} else {
		$expiration = time() + 172800;
		$expire = 0;
	}
	
	if ( '' === $secure )
		$secure = bb_is_ssl() ? true : false;

	if ( $secure ) {
		$scheme = 'secure_auth';
	} else {
		$scheme = 'auth';
	}

	$wp_auth_object->set_auth_cookie( $user_id, $expiration, $expire, $scheme );
	$wp_auth_object->set_auth_cookie( $user_id, $expiration, $expire, 'logged_in' );
}
endif;

} elseif (defined('BB_SECRET_KEY')) {  	// 0.9

if ( !function_exists('bb_login') ) :
function bb_login($login, $password) {
	if ( $user = bb_check_login( $login, $password ) ) {
		wp_set_auth_cookie($user->ID,true);		// true normally is 1209600 (2 weeks)  vs 172800 (2 days)
		
		do_action('bb_user_login', (int) $user->ID );
	}
	
	return $user;
}
endif;

if ( !function_exists('wp_set_auth_cookie') ) :
function wp_set_auth_cookie($user_id, $remember = false) {
	global $bb;
	
	if ( $remember ) {
		$expiration = $expire = time() + 31536000; 		// previously 1209600 (2 weeks) now set to a year
	} else {
		$expiration = time() + 172800;
		$expire = 0;
	}
	
	$cookie = wp_generate_auth_cookie($user_id, $expiration);
	
	do_action('set_auth_cookie', $cookie, $expire);
	
	setcookie($bb->authcookie, $cookie, $expire, $bb->cookiepath, $bb->cookiedomain);
	if ( $bb->cookiepath != $bb->sitecookiepath )
		setcookie($bb->authcookie, $cookie, $expire, $bb->sitecookiepath, $bb->cookiedomain);
}
endif;

} else {	      // very old password method for 0.8

if ( !function_exists('bb_login') ) :
function bb_login($login, $password) {
	if ( $user = bb_check_login( $login, $password ) ) {
		bb_cookie( bb_get_option( 'usercookie' ), $user->user_login, time() + 31536000 );	// previously 6048000 (70 days) now set to a year
		bb_cookie( bb_get_option( 'passcookie' ), md5( $user->user_pass ) );
		do_action('bb_user_login', (int) $user->ID );
	}

	return $user;
}
endif;

if ( !function_exists('bb_cookie') ) :
function bb_cookie( $name, $value, $expires = 0 ) {
	if ( !$expires )
		$expires = time() + 31536000;		// previously 1209600 (2 weeks) now set to a year
	if ( bb_get_option( 'cookiedomain' ) )
		setcookie( $name, $value, $expires, bb_get_option( 'cookiepath' ), bb_get_option( 'cookiedomain' ) );
	else
		setcookie( $name, $value, $expires, bb_get_option( 'cookiepath' ) );
}
endif;

}
?>