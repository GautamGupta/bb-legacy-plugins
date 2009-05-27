<?php
/*
Plugin Name: Skip Akismet
Plugin URI: http://bbpress.org/plugins/topic/skip-akismet
Description: Defines a list of roles (ie. moderator) that should never be checked against the Akismet spam filter to prevent false positives. Works in both bbPress and WordPress. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.4
*/

$skip_akismet=array('administrator','keymaster','moderator','editor','author');		// add any other custom roles to this list

/* 	 stop editing here 	 */

if (isset($_POST['comment'])) {add_action('init','skip_akismet_wp',9);}
if (isset($_POST['post_content'])) {add_action('bb_init','skip_akismet_bb',9);}

function skip_akismet_wp() { 	 // wordpress
global $wpdb; $user=wp_get_current_user(); 
if (!empty($user->ID) && skip_akismet_check($user->data->{$wpdb->prefix."capabilities"})) {	
	remove_action('wp_set_comment_status', 'akismet_submit_spam_comment');
	remove_action('edit_comment', 'akismet_submit_spam_comment');
	remove_action('preprocess_comment', 'akismet_auto_check_comment', 1);
	add_filter('pre_comment_approved', 'skip_akismet_approved',999);	
}
}

function skip_akismet_bb() {	 // bbpress
global $bbdb; $user=bb_get_current_user();
if (!empty($user->ID) && (skip_akismet_check($user->data->{$bbdb->prefix."capabilities"}) || bb_current_user_can('throttle'))) {	
	remove_action( 'pre_post', 'bb_ksd_check_post', 1 );
	remove_filter( 'bb_new_post', 'bb_ksd_new_post' );
	remove_filter( 'pre_post_status', 'bb_ksd_pre_post_status' );
}
}

function skip_akismet_check($roles) {
global $skip_akismet;	
if (!empty($roles) && is_array($roles) && in_array(reset(array_keys($roles)),$skip_akismet)) {return true;} else {return false;}
}

function skip_akismet_approved($approved=1) {return 1;}

?>