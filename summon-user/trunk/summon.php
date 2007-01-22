<?php
/**
 * Plugin Name: Summon User
 * Plugin Description: You can summon a user on a specified topic. Inspired by http://bbpress.org/forums/topic/458
 * Author: Thomas Klaiber
 * Author URI: http://www.la-school.com
 * Plugin URI: http://www.la-school.com/2006/bbpress_summon/
 * Version: 1.0
 */
 

function summon_get_users() {
	global $bbdb;
	// maybe cached in future versions.
	$users = $bbdb->get_results("SELECT ID, user_login FROM $bbdb->users ORDER BY user_login");
	
	return $users;
}

function summon_user_dropdown() {
	global $forum_id;
	$summon_users = summon_get_users();
	echo '<select name="summon_user_id" id="summon_user_id" tabindex="6">';
		echo '<option value="0" selected="selected">Select user...</option>';
	foreach ( $summon_users as $summon_user ) :
		echo "<option value='$summon_user->ID'>$summon_user->user_login</option>";
	endforeach;
	echo '</select>';
}

function summon_user_post() {
	global $bbdb, $bb_table_prefix, $topic_id, $bb_current_user;
	
	if ($_POST['summon_user_id'] > 0):
		$summon_user = bb_get_user($_POST['summon_user_id']);
		
		$topic = get_topic($topic_id);		
		$message = __("You have been summoned to: %1\$s \n\n%2\$s ");
			mail( $summon_user->user_email, bb_get_option('name') . ':' . __('Notification'), 
				sprintf( $message, $topic->topic_title, get_topic_link($topic_id) ), 
				'From: ' . bb_get_option('admin_email') 
			);
	endif;
}
add_action('bb_new_post', 'summon_user_post');

?>