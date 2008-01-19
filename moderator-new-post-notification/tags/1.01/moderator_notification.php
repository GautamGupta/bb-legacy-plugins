<?php
/**
 * Plugin Name: Moderator New Post Notification
 * Plugin Description: Sends a notification e-mail to all moderators and admins if there is a new post from a regular member.
 * Author: Olaf Lederer
 * Author URI: http://www.finalwebsites.com/portal
 * Plugin URI: http://www.finalwebsites.com/bbpress/moderator-notification.php
 * Version: 1.01
 */

 
function notification_select_all_mods() {
	global $bbdb;
	$sql = "
		SELECT u.ID, u.user_email 
		FROM $bbdb->users AS u, $bbdb->usermeta AS um 
		WHERE u.ID = um.user_id
		AND um.meta_key = 'bb_capabilities'
		AND um.meta_value REGEXP '^.*\"(moderator|administrator|keymaster)\".*$' 
		AND u.user_status = 0
	";
	$all_mods = $bbdb->get_results($sql);
	
	return $all_mods;
}

function is_moderator($user_id) {
	global $bbdb;
	$mods = array('moderator', 'administrator', 'keymaster');
	$is_mod = false;
	$row = $bbdb->get_row("SELECT um.meta_value AS role
		FROM bb_users AS u, bb_usermeta AS um 
		WHERE u.ID = um.user_id
		AND um.meta_key = 'bb_capabilities'
		AND u.user_status = 0
		AND u.ID = $user_id
	");
	$arr = unserialize($row->role);
	foreach($mods as $val) {
		if (array_key_exists($val, $arr)) {
			$is_mod = true;
			break;
		}
	}
	return $is_mod;

}


function mod_notification_is_activated($user_id) {
	$user = bb_get_user($user_id);
	if (!empty($user->mod_notification)) {
		return true;
	}else {
		return false;
	}
}
 
function mod_notification_new_post() {
	global $bbdb, $topic_id, $bb_current_user;
	
	$all_moderators = notification_select_all_mods();
	
	$topic = get_topic($topic_id);
	
	$header = 'From: '.bb_get_option('admin_email')."\n";
	$header .= 'MIME-Version: 1.0'."\n";
	$header .= 'Content-Type: text/plain; charset="'.BBDB_CHARSET.'"'."\n";
	$header .= 'Content-Transfer-Encoding: 7bit'."\n";
	
	$subject = 'There is a new post on: '.$topic->topic_title;
	if (!is_moderator($bb_current_user->ID)) {
		foreach ($all_moderators as $userdata) {
			if (mod_notification_is_activated($userdata->ID)) {
				$msg = "Hello,\n\n".get_user_name($bb_current_user->ID)." has posted here:\n".get_topic_link($topic_id);
				mail($userdata->user_email, $subject, $msg, $header);
				
			}
		} 
	}
}

function mod_notification_profile() {
	global $user_id, $mods;
	
	if (bb_is_user_logged_in() && is_moderator($user_id)) {
	
		$checked = "";
		
		if (mod_notification_is_activated($user_id)) {
			$checked = ' checked="checked"';
		}
	
		echo '
			<fieldset>
				<legend>Moderator Post Notification</legend>
				<p>If you want to get an email when there is a new post to topics written by members.</p>
				<table width="100%">
					<tr>
						<th width="21%" scope="row">Activate:</th>
						<td width="79%">
							<input name="mod_notification" id="mod_notification" type="checkbox" value="1"'.$checked.' />
						</td>
					</tr>
				</table>
			</fieldset>';
	}
}

function mod_notification_profile_edit() {
	global $user_id;
	if (is_moderator($user_id)) {
		bb_update_usermeta($user_id, "mod_notification", $_POST['mod_notification']);
	}
}

add_action('bb_new_post', 'mod_notification_new_post');

add_action('extra_profile_info', 'mod_notification_profile');

add_action('profile_edited', 'mod_notification_profile_edit');
?>