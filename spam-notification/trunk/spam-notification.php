<?php
/*
Plugin Name: Spam Notification
Plugin URI:  http://bbpress.org/plugins/topic/spam-notification
Description:  notifies admin when a post is marked as spam by akismet
Version: 0.0.1
Author: _ck_
Author URI: http://bbshowcase.org
*/

$spam_notification['email']=array(bb_get_option('from_email'),"etc.");     // add additional emails here, each in quotes, separate by commas


/*  	 stop editing here 	 */

add_action( 'bb_new_post', 'spam_notification', 999);

function spam_notification($post_id=0) {
	global $spam_notification, $bb_ksd_pre_post_status;
	if ( empty($bb_ksd_pre_post_status) || $bb_ksd_pre_post_status != "2" ) {return;}	   //  if akismet found it as spam, not manually marked by admin
	
	$post=bb_get_post($post_id);	
	if ($post->post_status != "2") {return;}	// should not happen, but safety check	
	
	$user=bb_get_user($post->poster_id);
	
	$message="A post has been marked as spam by Akismet ... \r\n\r\n";		
	$message.=bb_get_option('uri') ."bb-admin/".(defined('BACKPRESS_PATH') ? 'content-' : '')."posts.php?post_status=2\r\n\r\n";		
	$message .= sprintf(__('Username: %s'), stripslashes($user->user_login)) . "\r\n";
	$message .= sprintf(__('Profile: %s'), get_user_profile_link($user->ID)) . "\r\n";
	$message .= sprintf(__('Email: %s'), stripslashes($user->user_email)) . "\r\n";		
	$message .= sprintf(__('IP address: %s'), $_SERVER['REMOTE_ADDR']) . "\r\n";
	$message .= sprintf(__('Agent: %s'), substr(stripslashes($_SERVER["HTTP_USER_AGENT"]),0,255)) . "\r\n\r\n";			

	$name=bb_get_option('name');
	foreach ($spam_notification['email'] as $to) {if (empty($to) || strlen($to)<8) {continue;}
		@bb_mail($to , "[$name] post sent to spam", $message);
	}	
}
