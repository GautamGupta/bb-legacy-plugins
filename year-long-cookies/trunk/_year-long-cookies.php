<?php
/*
Plugin Name:  Year Long Cookies - Remember Me
Description:  Force login cookies to last a year instead of the default two days (or two weeks). Works with both the old 0.8.x password method and the new technique in the trunk.
Plugin URI:  http://bbpress.org/plugins/topic/87
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.01

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
*/ 

if (defined('BB_SECRET_KEY')) {  		// new password method

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

} else {		// old password method

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