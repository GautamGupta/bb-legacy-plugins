<?php
/*
Plugin Name:  New User Notification Email
Plugin URI: http://bbpress.org/plugins/topic/94
Description: Notifies the admin by email that a new user has registered.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

add_action('bb_new_user', 'new_user_notification');

function new_user_notification($user_id=0) {
	if (!$user_id) {$user_id=bb_get_current_user_info( 'id' );}
	$user=bb_get_user($user_id); 
		
	$message  = sprintf(__('New user registration on %s:'), bb_get_option('name')) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), stripslashes($user->user_login)) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), stripslashes($user->user_email)) . "\r\n\r\n";	
		
	$message .= sprintf(__('Agent: %s'), substr(stripslashes($_SERVER["HTTP_USER_AGENT"]),0,80)) . "\r\n\r\n";	
	$message .= sprintf(__('IP: %s'), $_SERVER['REMOTE_ADDR']) . "\r\n\r\n";	
	
	// $host = `host $_SERVER['REMOTE_ADDR']`;  	$host=end(explode(' ',$host));    	$host=substr($host,0,strlen($host)-2);	// safer rDNS process with timeout
   	// $chk=split("\(",$host);  if($chk[1]) {$message.=$_SERVER['REMOTE_ADDR']." (".$chk[1].")". "\r\n\r\n";} else {$message.=$host. "\r\n\r\n";}
	
	$message .= sprintf(__('Profile: %s'), get_user_profile_link($user_id)) . "\r\n\r\n";
		
$to=bb_get_option('admin_email'); if (!$to) {$to=bb_get_option('from_email');}
@bb_mail($to , sprintf(__('[%s] New User Registration'), bb_get_option('name')), $message, '' );
}

?>