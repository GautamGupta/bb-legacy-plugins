<?php
/*
Plugin Name: bb-NoSpamUser
Version: 0.6
Plugin URI: http://danielgilbert.de/nospamuser/
Description: Prevents known Spam Users from registering on your forum.
Author: Daniel Gilbert
Author URI: http://danielgilbert.de
Ported By: Nightgunner5
Porter URI: http://llamaslayers.net/daily-llama/
*/

function nospamuser_check($type, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.stopforumspam.com/api?'.$type.'='.urlencode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	if (strpos($response, 'response success="true">') !== 1) return null;
	if (strpos($response, '<appears>no</appears>')) return false;
	return true;
}

function nospamuser_check_email($r, $email) {
	if ($r) {
		$response = nospamuser_check('email', $email);
		if ($response) bb_die('Your email address has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
		return $r;
	} else return $r;
}

function nospamuser_check_username($username) {
	if ($username) {
		$response = nospamuser_check('username', $username);
		if ($response) bb_die('Your username has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
		return $username;
	} else return $username;
}

function nospamuser_check_ip() {
	if (!class_exists('SimpleXMLElement')) return;
	$response = nospamuser_check('ip', $_SERVER['REMOTE_ADDR']);
	if ($response === true) {
		bb_die('Your IP address has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
	}
}

add_filter('bb_verify_email', 'nospamuser_check_email', 10, 2);
add_filter('sanitize_user', 'nospamuser_check_username');
if (bb_get_location() == 'register-page')
	nospamuser_check_ip();

?>