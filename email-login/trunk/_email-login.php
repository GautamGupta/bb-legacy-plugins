<?php
/*
Plugin Name:  Email Login
Description:  Allows users to login via their email address in addition to username.
Plugin URI:  http://bbpress.org/plugins/topic/email-login
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.0.1
*/ 

function bb_login( $login, $password, $remember = false ) {
	$user = bb_check_login( $login, $password );	// first check the login the standard way
	if (empty($user)) {	
	$user = bb_check_email_login( $login, $password );	// then check the login the email way	
	}		
	if ($user) {
		if (defined('BACKPRESS_PATH')) { // 1.0
		bb_set_auth_cookie($user->ID, $remember);
		} else { // 0.9
		wp_set_auth_cookie($user->ID, $remember);
		}
		do_action('bb_user_login', (int) $user->ID );
	}	
	return $user;
}

function bb_check_email_login($email, $pass) {	
	if (strpos($email, '@') === false || strpos($email, '.') === false) {return false;}  // not an email
	if (!preg_match("/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i", $email)) {return false;}   // not an email
	
	global $bbdb;
	$user_id = $bbdb->get_var($bbdb->prepare("SELECT ID FROM $bbdb->users WHERE user_email = '%s' LIMIT 1", $email));
	if (empty($user_id)) {return false;}   // email not found

	$user=bb_get_user( $user_id );	

	if (defined('BACKPRESS_PATH')) { // 1.0
		if ( !bb_check_password($pass, $user->user_pass, $user->ID) ) {return false;}
	} else {  // 0.9
		if ( !wp_check_password($pass, $user->user_pass, $user->ID) ) {return false;}
	}
	
	if ( 1 == $user->user_status ) {update_user_status( $user->ID, 0 );} 	// User is logging in for the first time, update status
	
	return $user;
}

?>