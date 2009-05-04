<?php
/*
Plugin Name: Skip Akismet
Plugin URI: http://bbpress.org/plugins/topic/skip-akismet
Description: Defines a list of roles (ie. moderator) that should never be checked against the Akismet spam filter to prevent false positives. Works in both bbPress and WordPress. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1
*/

$skip_akismet=array('administrator','keymaster','moderator','editor','author');		// add any other custom roles to this list

/* 	 stop editing here 	 */

add_action('init','skip_akismet',9);
add_action('bb_init','skip_akismet',9);

function skip_akismet() {
global $skip_akismet,$current_user,$bb_current_user,$bbdb,$wpdb;
if (empty($current_user->ID) && empty($bb_current_user->ID)) {return;}
if (empty($bbdb)) {$capabilities=$wpdb->prefix."capabilities"; $capabilities=$current_user->data->$capabilities; }
else {$capabilities=$bbdb->prefix."capabilities"; $capabilities=$bb_current_user->data->$capabilities; }
$role=reset(array_keys($capabilities)); 
if (!in_array($role,$skip_akismet)) { 
	if (empty($bbdb)) {return;} 
	elseif (!bb_current_user_can('throttle') || !bb_current_user_can('moderate')) {return;}
}
remove_action( 'pre_post', 'bb_ksd_check_post', 1 );
remove_filter( 'bb_new_post', 'bb_ksd_new_post' );
remove_filter( 'pre_post_status', 'bb_ksd_pre_post_status' );
remove_action('init', 'akismet_init');
remove_action('wp_set_comment_status', 'akismet_submit_spam_comment');
remove_action('edit_comment', 'akismet_submit_spam_comment');
remove_action('preprocess_comment', 'akismet_auto_check_comment', 1);
}

?>