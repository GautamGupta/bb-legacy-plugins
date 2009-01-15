<?php
/*
Plugin Name: BBPress User Directory
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/user-directory
Description: Lists users of the forum, linking to their profiles
Author: Paul Hawke
Change Log: 0.1 Initial cut
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.1
*/

function ud_user_list() {
	global $bbdb, $bb_current_user, $bb;
	if ( $bb->wp_table_prefix )
		$users = $bbdb->get_results("SELECT * FROM ".$bb->wp_table_prefix."users WHERE user_status = 0 ORDER BY user_login");
	else
		$users = $bbdb->get_results("SELECT * FROM $bbdb->users WHERE user_status = 0 ORDER BY user_login");

	return $users;
}

function ud_tiny_user_pm_link( $userid ) {
	if ( bb_current_user_can('write_post') ) {
		echo '<a title="PM This User" href="';
		echo apply_filters('pm_user_link', bb_get_pm_link( '?user='.$userid ) );
		echo '">';
		echo '<img src="'.bb_get_active_theme_uri();
		echo 'newmail.png" border="0" align="top" /></a>';
	}
}

?>