<?php
/*
Plugin Name: bb-NoSpamUser
Version: 0.4
Plugin URI: http://danielgilbert.de/nospamuser/
Description: Prevents known Spam Users from registering on your forum.
Author: Daniel Gilbert
Author URI: http://danielgilbert.de
Ported By: Nightgunner5
Porter URI: http://llamaslayers.net/daily-llama/

This plugin requires allow_url_fopen.
This plugin also requires PHP5.  If PHP5 is not available, this plugin will do nothing.
If somebody wants to make a port of this for PHP4, I'd be glad to add it in.
*/
if (ini_get('allow_url_fopen')) {
	function nospamuser_check_email($r, $email) {
		if ($r) {
			if (!class_exists('SimpleXMLElement')) return $r;
			$response = new SimpleXMLElement(file_get_contents('http://www.stopforumspam.com/api?email='.urlencode($email)));
			if ($response['success']) {
				if ($response->appears == 'yes') bb_die('Your email address has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
				else return $r;
			} else return $r;
		} else return $r;
	}

	function nospamuser_check_username($username) {
		if ($username) {
			if (!class_exists('SimpleXMLElement')) return $username;
			$response = new SimpleXMLElement(file_get_contents('http://www.stopforumspam.com/api?username='.urlencode($username)));
			if ($response['success']) {
				if ($response->appears == 'yes') bb_die('Your username has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
				else return $username;
			} else return $username;
		} else return $username;
	}

	function nospamuser_check_ip() {
		if (!class_exists('SimpleXMLElement')) return;
		$response = new SimpleXMLElement(file_get_contents('http://www.stopforumspam.com/api?ip='.urlencode($_SERVER['REMOTE_ADDR'])));
		if ($response['success']) {
			if ($response->appears == 'yes') bb_die('Your IP address has been marked as spam by StopForumSpam.com.  Contact the forum administrator if you are not a spambot.');
		}
	}

	add_filter('bb_verify_email', 'nospamuser_check_email', 10, 2);
	add_filter('sanitize_user', 'nospamuser_check_username');
	if (bb_get_location() == 'register-page')
		nospamuser_check_ip();
}

?>