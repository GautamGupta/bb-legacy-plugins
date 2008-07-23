<?php
/*
Plugin Name: Temporary ban
Description: Block, deactivate, or bozo a user for a specified amount of time.
Plugin URI: http://bbpress.org/plugins/topic/temporary-ban/
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Version: 1.0
*/

$temporary_bans = bb_get_option('temporary_bans');
if (!is_array($temporary_bans))
	$temporary_bans = array();
$temp_ban_over = false;
if (is_array($temporary_bans)) {
	foreach ($temporary_bans as $ban_key => $ban) {
		if ($ban['until'] < time()) {
			switch ($ban['type']) {
				case 'bozo':
					bb_update_usermeta($ban['user_id'], 'is_bozo', 0);
					unset($temporary_bans[$ban_key]);
					$temp_ban_over = true;
					break;
				case 'blocked':
					bb_fix_password($ban['user_id']);
				case 'inactive':
					bb_give_user_default_role(bb_get_user($ban['user_id']));
					unset($temporary_bans[$ban_key]);
					$temp_ban_over = true;
			}
		}
	}
	if ($temp_ban_over) {
		bb_update_option('temporary_bans', $temporary_bans);
	}
}

if (BB_IS_ADMIN) { // Only load admin code if we're in the admin panel.
	include_once 'temporary-ban-admin.php';
}
?>