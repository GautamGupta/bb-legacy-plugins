<?php
/*
Plugin Name: BBPress User Directory
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/user-directory
Description: Lists users of the forum, linking to their profiles
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.4
*/

function ud_format_pagination($current, $pagecount) {
    global $SELF;
    $pagination = '';
    
    if ($current > 1) {
        $pagination = '<a href="'.$SELF.'?page='.($current-1).'">&laquo;&nbsp;Prev</a>&nbsp;|&nbsp;';
    } else {
        $pagination = '&laquo;&nbsp;Prev&nbsp;|&nbsp;';
    }
    
    $pagination .= 'Page '.$current.' of '.$pagecount;
    
    if ($current < $pagecount) {
        $pagination .= '&nbsp;|&nbsp;<a href="'.$SELF.'?page='.($current+1).'">Next&nbsp;&raquo;</a>';
    } else {
        $pagination .= '&nbsp;|&nbsp;Next&nbsp;&raquo;';
    }
    
    return $pagination;
}

function ud_get_max_pages() {
	global $bbdb, $bb_current_user, $bb;
	if ( $bb->wp_table_prefix ) {
		$usercount = $bbdb->get_var("SELECT count(*) FROM ".$bb->wp_table_prefix."users WHERE user_status = 0");
	} else {
		$usercount = $bbdb->get_var("SELECT count(*) FROM $bbdb->users WHERE user_status = 0");
	}
	
	return ceil($usercount / 20);
}

function ud_user_list($current = 1) {
	global $bbdb, $bb_current_user, $bb;
	if ( $bb->wp_table_prefix ) {
		$users = $bbdb->get_results(
		  "SELECT * FROM ".
		  $bb->wp_table_prefix.
		  "users WHERE user_status = 0 ORDER BY user_login limit ".
		  (20 * ($current - 1)).
		  ',20');
	} else {
		$users = $bbdb->get_results(
		  "SELECT * FROM ".
		  $bbdb->users.
		  " WHERE user_status = 0 ORDER BY user_login limit ".
		  (20 * ($current - 1)).
		  ',20');
	}
	
	return $users;
}

function ud_tiny_user_pm_link( $userid ) {
	if ( bb_current_user_can('write_post') ) {
		echo '<a title="PM This User" href="';
		echo apply_filters('pm_user_link', bb_get_pm_link( '?user='.$userid ) );
		echo '" rel="nofollow">';
		echo '<img src="'.bb_get_active_theme_uri();
		echo 'newmail.png" border="0" align="top" /></a>';
	}
}

?>