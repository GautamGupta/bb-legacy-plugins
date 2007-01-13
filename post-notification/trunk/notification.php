<?php
/**
 * Plugin Name: Post Notification
 * Plugin Description: Sends an Notification email if there's a new post to an favorite topic.
 * Author: Thomas Klaiber
 * Author URI: http://www.la-school.com
 * Plugin URI: http://www.la-school.com/2006/bbpress-notification/
 * Version: 1.3
 */
 
function notification_new_post() {
	global $bbdb, $bb_table_prefix, $topic_id, $bb_current_user;
	
	$all_users = notification_select_all_users();
	foreach ($all_users as $userdata) :
		if ( notification_is_activated( $userdata->ID ) ) :
			if ( is_user_favorite( $userdata->ID, $topic_id ) ) :
				$topic = get_topic($topic_id);	
				$message = __("There is a new post on: %1\$s \nReply by: %2\$s \n\n%3\$s ");
					mail( $userdata->user_email, bb_get_option('name') . ':' . __('Notification'), 
						sprintf( $message, $topic->topic_title, get_user_name($bb_current_user->ID), get_topic_link($topic_id) ), 
						'From: ' . bb_get_option('admin_email') 
					);
			endif;
		endif;
	endforeach; 
}
add_action('bb_new_post', 'notification_new_post');

function notification_select_all_users() {
	global $bbdb;
	
	$all_users = $bbdb->get_results("SELECT ID, user_email FROM $bbdb->users WHERE user_status=0");
	
	return $all_users;
}

function notification_profile() {
	global $user_id, $bb_current_user;
	
	if ( bb_is_user_logged_in() ) :
	
		$checked = "";
		if (notification_is_activated($user_id)) :
			$checked = "checked='checked'";
		endif;
	
		echo "<fieldset>
<legend>Favorite Notification</legend>
<p> " . __('If you want to get an email when there is a new post to a topic in your favorites.') . "</p>
<table width=\"100%\">
<tr>
<th width=\"21%\" scope=\"row\">" . __('Activate') . ":</th>
<td width=\"79%\" ><input name=\"favorite_notification\" id=\"favorite_notification\" type=\"checkbox\" value=\"1\"" . $checked . " /></td>
</tr>
</table>
</fieldset>\n\n";
	endif;
}
add_action('extra_profile_info', 'notification_profile');

function notification_profile_edit() {
	global $user_id;
		
	bb_update_usermeta($user_id, "favorite_notification", $_POST['favorite_notification']);
}
add_action('profile_edited', 'notification_profile_edit');

function notification_is_activated($user_id) {
	$user = bb_get_user( $user_id );
	if ($user->favorite_notification) :
		return true;
	else :
		return false;
	endif;
}
?>