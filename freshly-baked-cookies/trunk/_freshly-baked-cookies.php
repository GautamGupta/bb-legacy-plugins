<?php
/*
Plugin Name:  Freshly Baked Cookies for bbPress
Description:  Allows bbPress 0.9 to use WordPress 2.7 or 2.8 cookies during stand-alone (simple) integration.
Plugin URI:  http://bbpress.org/plugins/topic/freshly-baked-cookies
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.0.2
*/ 

define('LOGGED_IN_KEY',   'replace this with your WP key');	// get this from wp-config.php
define('LOGGED_IN_SALT', 'replace this');			// get this from http://your-site.com/wp-admin/options.php

/*   stop editing here   */

define('COOKIEHASH', md5(rtrim($bb->wp_siteurl, "/")));			// if you set a custom COOKIEHASH for some reason, you'll have to edit this
define('LOGGED_IN_COOKIE', 'wordpress_logged_in_' . COOKIEHASH);	// you only have to edit this if you changed WordPress's cookie name

/*   seriously, stop editing here   */

function wp_validate_auth_cookie( $cookie = null, $scheme = 'logged_in_cookie' ) {	
	
	if ( empty($_COOKIE[LOGGED_IN_COOKIE]) ) {return false;}	
	
	$cookie_elements = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
	if ( count($cookie_elements) != 3 ) {return false;}

	list($username, $expiration, $hmac) = $cookie_elements;
	$expired = $expiration;

	// Allow a grace period for POST and AJAX requests
	if ( defined('DOING_AJAX') || 'POST' == $_SERVER['REQUEST_METHOD'] ) {	$expired += 3600;}

	if ( $expired < time() ) {return false;}
	
	$data = $username . '|' . $expiration;
	$salt = apply_filters('salt', LOGGED_IN_KEY . LOGGED_IN_SALT, $scheme);		
	$key=hash_hmac('md5', $data, $salt);
	$hash = hash_hmac('md5', $username . '|' . $expiration, $key);
	
	if ( $hmac != $hash ) {return false;}
	
	$user = bb_get_user_by_name($username);
	if ( !$user ) {return $user;}

	return $user->ID;
}

function wp_generate_auth_cookie( $user_id, $expiration, $scheme = 'logged_in' ) {
	$user = bb_get_user( $user_id ); 
	if (!$user) {return $user;}

	$data = $user->user_login . '|' . $expiration;
	$salt = apply_filters('salt', LOGGED_IN_KEY . LOGGED_IN_SALT, $scheme);		
	$key=hash_hmac('md5', $data, $salt);
	$hash = hash_hmac('md5', $user->user_login . '|' . $expiration, $key);
	$cookie = $user->user_login . '|' . $expiration . '|' . $hash;

	return apply_filters('auth_cookie', $cookie, $user_id, $expiration, $scheme);
}

function wp_set_auth_cookie($user_id, $remember = false) {
	global $bb;	
	if ( $remember ) {$expiration = $expire = time() + 1209600;} 
	else {$expiration = time() + 172800; $expire = 0;}
	
	$cookie = wp_generate_auth_cookie($user_id, $expiration);	
	do_action('set_auth_cookie', $cookie, $expire);
	do_action('set_logged_in_cookie', $cookie, $expire, $expiration, $user_id, 'logged_in');
	
	setcookie(LOGGED_IN_COOKIE, $cookie, $expire, $bb->cookiepath, $bb->cookiedomain);		// . '; HttpOnly'
	if ( $bb->cookiepath != $bb->sitecookiepath ) {
		setcookie(LOGGED_IN_COOKIE, $cookie, $expire, $bb->sitecookiepath, $bb->cookiedomain);
	}
}

function wp_clear_auth_cookie() {
	do_action('clear_auth_cookie');
	global $bb;
	setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, $bb->cookiepath, $bb->cookiedomain);
	setcookie(LOGGED_IN_COOKIE, ' ', time() - 31536000, $bb->sitecookiepath, $bb->cookiedomain);
		
	// Older cookies
	setcookie($bb->authcookie, ' ', time() - 31536000, $bb->cookiepath, $bb->cookiedomain);
	setcookie($bb->authcookie, ' ', time() - 31536000, $bb->sitecookiepath, $bb->cookiedomain);
	
	// Oldest cookies
	setcookie($bb->usercookie, ' ', time() - 31536000, $bb->cookiepath, $bb->cookiedomain);
	setcookie($bb->usercookie, ' ', time() - 31536000, $bb->sitecookiepath, $bb->cookiedomain);
	setcookie($bb->passcookie, ' ', time() - 31536000, $bb->cookiepath, $bb->cookiedomain);
	setcookie($bb->passcookie, ' ', time() - 31536000, $bb->sitecookiepath, $bb->cookiedomain);
}

?>