<?php
/*
Plugin Name: Skip Akismet
Plugin URI: http://bbpress.org/plugins/topic/skip-akismet
Description: Defines a list of roles (ie. moderator) that should never be checked against the Akismet spam filter to prevent false positives. Works in both bbPress and WordPress. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2
*/

$skip_akismet=array('administrator','keymaster','moderator','editor','author');		// add any other custom roles to this list

/* 	 stop editing here 	 */

add_action('init','skip_akismet',9);
add_action('bb_init','skip_akismet',9);

function skip_akismet() {
global $skip_akismet,$current_user,$bb_current_user,$wpdb,$bbdb;
if (!empty($current_user->ID) && !empty($wpdb->prefix)) {	// wordpress	 
	$role=reset(array_keys($current_user->data->{$wpdb->prefix."capabilities"}));
	if (in_array($role,$skip_akismet)) {
		remove_action('wp_set_comment_status', 'akismet_submit_spam_comment');
		remove_action('edit_comment', 'akismet_submit_spam_comment');
		remove_action('preprocess_comment', 'akismet_auto_check_comment', 1);
	}
}
if (!empty($bb_current_user->ID) && !empty($bbdb->prefix)) {	// bbpress
	$role=reset(array_keys($bb_current_user->data->{$bbdb->prefix."capabilities"})); 
	if (in_array($role,$skip_akismet) || bb_current_user_can('throttle')) {
		remove_action( 'pre_post', 'bb_ksd_check_post', 1 );
		remove_filter( 'bb_new_post', 'bb_ksd_new_post' );
		remove_filter( 'pre_post_status', 'bb_ksd_pre_post_status' );
	}
}
}

?>