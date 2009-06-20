<?php
/*
Plugin Name: bb-NoSpamUser
Version: 0.7
Plugin URI: http://nightgunner5.wordpress.com/tag/nospamuser/
Description: Prevents known Spam Users from registering on your forum.
Author: Nightgunner5
Author URI: http://llamaslayers.net/
*/

define( 'NOSPAMUSER_MAX', 3 );

function nospamuser_check( $type, $data ) {
	$ch = curl_init( 'http://www.stopforumspam.com/api?' . $type . '=' . urlencode( $data ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	$response = curl_exec( $ch );
	curl_close( $ch );

	if ( strpos( $response, '<response success="true">' ) === false )
		return null;
	if ( strpos( $response, '<appears>no</appears>' ) !== false )
		return false;

	return (int)substr( $response, strpos( $response, '<frequency>' ) + 11 );
}

function nospamuser_check_email( $r, $email ) {
	if ( $r ) {
		$response = nospamuser_check( 'email', $email );
		if ( $response >= NOSPAMUSER_MAX )
			bb_die( 'Your email address has been marked as spam by StopForumSpam.com. Contact the forum administrator if you are not a spambot.' );
		return $r;
	} else
		return $r;
}

function nospamuser_check_username( $username ) {
	if ( $username ) {
		$response = nospamuser_check( 'username', $username );
		if ( $response >= NOSPAMUSER_MAX )
			bb_die( 'Your username has been marked as spam by StopForumSpam.com. Contact the forum administrator if you are not a spambot.' );
		return $username;
	} else return $username;
}

function nospamuser_check_ip() {
	$response = nospamuser_check( 'ip', $_SERVER['REMOTE_ADDR'] );
	if ( $response >= NOSPAMUSER_MAX )
		bb_die( 'Your IP address has been marked as spam by StopForumSpam.com. Contact the forum administrator if you are not a spambot.' );
}

add_filter( 'bb_verify_email', 'nospamuser_check_email', 10, 2 );
add_filter( 'sanitize_user', 'nospamuser_check_username' );
if ( bb_get_location() == 'register-page' )
	nospamuser_check_ip();

?>