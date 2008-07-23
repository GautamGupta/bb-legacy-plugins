<?php
/*
Plugin Name: Temporary ban
Description: Block, deactivate, or bozo a user for a specified amount of time.
Plugin URI: about:blank
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
	function temporary_ban_admin_page_add() {
		if (function_exists('bb_admin_add_submenu')) { // Build 794+
			bb_admin_add_submenu(__('Temporary ban', 'temporary-ban'), 'moderate', 'temporary_ban_admin_page', 'content.php');
		} else {
			global $bb_submenu;
			$bb_submenu['content.php'][] = array(__('Temporary ban', 'temporary-ban'), 'moderate', 'temporary_ban_admin_page');
		}
	}
	add_action('bb_admin_menu_generator', 'temporary_ban_admin_page_add');

	function temporary_ban_admin_page() {
		global $temporary_bans;
		if($_GET['action'] == 'remove_ban') {
			if (!isset($temporary_bans[(int) $_GET['ban_key']]))
				bb_die(sprintf(__('Error!  Go back to <a href="%s">the temporary bans admin page</a> and try again.', 'temporary-ban'), 'admin-base.php?plugin=temporary_ban_admin_page'));
			$ban = $temporary_bans[$_GET['ban_key']];
			$ban_key = $_GET['ban_key'];
			switch ($ban['type']) {
				case 'bozo':
					bb_update_usermeta($ban['user_id'], 'is_bozo', 0);
					unset($temporary_bans[$ban_key]);
					break;
				case 'blocked':
					bb_fix_password($ban['user_id']);
				case 'inactive':
					bb_give_user_default_role(bb_get_user($ban['user_id']));
					unset($temporary_bans[$ban_key]);
			}
			bb_update_option('temporary_bans', $temporary_bans);
		}
	/*	if ($_GET['action'] == 'update_ban') {
			if (!isset($temporary_bans[(int) $_POST['ban_key']]))
				bb_die(sprintf(__('Error!  Go back to <a href="%s">the temporary bans admin page</a> and try again.', 'temporary-ban'), 'admin-base.php?plugin=temporary_ban_admin_page'));
			$ban = $temporary_bans[$_POST['ban_key']];
			$ban_key = $_POST['ban_key'];
			if (isset($_POST['ban_type']) && $_POST['ban_type'] != $ban['type'] && in_array($_POST['ban_type'], array('bozo', 'inactive', 'blocked'))) {
				switch ($ban['type']) {
					case 'bozo':
						bb_update_usermeta($ban['user_id'], 'is_bozo', 0);
						$temporary_bans[$ban_key]['type'] = $_POST['ban_type'];
						$user = bb_get_user($ban['user_id']);
						$user->set_role($_POST['ban_type']);
						break;
					case 'inactive':
					case 'blocked':
						if ($_POST['ban_type'] == 'bozo') {
							bb_update_usermeta($ban['user_id'], 'is_bozo', 1);
						} else {
							$user = bb_get_user($ban['user_id']);
							$user->set_role($_POST['ban_type']);
						}
						$temporary_bans[$ban_key]['type'] = $_POST['ban_type'];
				}
			}
			bb_update_option('temporary_bans', $temporary_bans);
		}*/
		if ($_GET['action'] == 'add_ban' && $_SERVER['REQUEST_METHOD'] == 'POST') {
			global $bb_table_prefix;
			if (!bb_current_user_can('edit_user', $_POST['user_id']))
				bb_die(sprintf(__('Error!  Go back to <a href="%s">the temporary bans admin page</a> and try again.', 'temporary-ban'), 'admin-base.php?plugin=temporary_ban_admin_page'));
			$new_ban = array();
			if ($_POST['ban_type'] == 'bozo') {
				$new_ban['type'] = 'bozo';
				bb_update_usermeta($_POST['user_id'], 'is_bozo', 1);
			} elseif ($_POST['ban_type'] == 'blocked' || $_POST['ban_type'] == 'inactive') {
				if ($_POST['ban_type'] == 'blocked')
					bb_break_password($_POST['user_id']);
				$new_ban['type'] = $_POST['ban_type'];
				bb_update_usermeta($_POST['user_id'], $bb_table_prefix.'capabilities', array($_POST['ban_type'] => true));
			} else {
				bb_die(sprintf(__('Error!  Go back to <a href="%s">the temporary bans admin page</a> and try again.', 'temporary-ban'), 'admin-base.php?plugin=temporary_ban_admin_page'));
			}
			$new_ban['until'] = time() + ($_POST['length'] * $_POST['lengthmultiplier']);
			$new_ban['user_id'] = $_POST['user_id'];
			global $temporary_bans;
			if (!$temporary_bans)
				$temporary_bans = array();
			$temporary_bans[] = $new_ban;
			bb_update_option('temporary_bans', $temporary_bans);
		}
		if ($_GET['action'] == 'add_ban' && $_SERVER['REQUEST_METHOD'] == 'GET') {
?>
	<h2><?php _e('Add a ban', 'temporary-ban'); ?> <small>&#8212; <a href="admin-base.php?plugin=temporary_ban_admin_page" title="<?php _e('Temporary bans', 'temporary-ban'); ?>">&laquo; <?php _e('Temporary bans', 'temporary-ban'); ?></a></small></h2>
	<form method="post" action="admin-base.php?plugin=temporary_ban_admin_page&amp;action=add_ban">
<?php	global $bb, $bbdb, $bb_current_user;
			if ( $bb->wp_table_prefix ) $users = $bbdb->get_results("SELECT * FROM ".$bb->wp_table_prefix."users ORDER BY user_login");
			else $users = $bbdb->get_results("SELECT * FROM $bbdb->users ORDER BY user_login");
			$usersbanned = array();
			foreach ($temporary_bans as $ban) {
				$usersbanned[] = $ban['user_id'];
			}
?>
		<p><label for="user_id"><?php _e('Select a user', 'temporary-ban'); ?> <select name="user_id">
<?php		foreach ($users as $user) {
				if (in_array($user->ID, $usersbanned) || !bb_current_user_can('edit_user', $user->ID) || $bb_current_user->ID == $user->ID)
					continue;
?>
		<option value="<?php echo $user->ID; ?>"><?php echo apply_filters( 'get_user_name', $user->user_login, $user->ID ); ?></option>
<?php } ?>
		</select></label></p>
		<p><label for="ban_type"><?php _e('Select a punishment', 'temporary-ban'); ?> <select name="ban_type">
		<?php if (function_exists('bb_current_user_is_bozo')) { ?><option value="bozo">Bozo</option><?php } ?>
		<option value="inactive">Deactivate</option>
		<option value="blocked">Block</option>
		</select>
		</label></p>
		<p><label for="length"><?php _e('Select a length for the punishment', 'temporary-ban'); ?> <input type="text" value="1" name="length" /></label>
		<select name="lengthmultiplier">
		<option value="31536000">years</option>
		<option value="2592000">months</option>
		<option value="604800">weeks</option>
		<option value="86400" selected="selected">days</option>
		<option value="3600">hours</option>
		<option value="60">minutes</option>
		<option value="1">seconds</option>
		</select></p>
		<p><input type="submit" value="Submit" /></p>
	</form>
<?php
		} else {
			global $temporary_bans;
			$datetime = bb_get_option('datetime_format');
			if (!$datetime)
				$datetime = 'F j, Y - h:i A';
?>
	<h2><?php _e('Temporary bans', 'temporary-ban'); ?> <small>&#8212; <a href="admin-base.php?plugin=temporary_ban_admin_page&amp;action=add_ban" title="<?php _e('Add a ban', 'temporary-ban'); ?>"><?php _e('Add a ban', 'temporary-ban'); ?> &raquo;</a></small></h2>
	<table class="widefat">
	<tbody>
		<tr class="thead"><th><?php _e('User', 'temporary-ban'); ?></th><th><?php _e('Type', 'temporary-ban'); ?></th><th><?php _e('Until', 'temporary-ban'); ?></th><th><?php _e('Action', 'temporary-ban'); ?></th></tr>
<?php foreach ($temporary_bans as $ban_key => $ban) { ?>
		<tr><td><?php echo get_user_name($ban['user_id']); ?></td><td><?php echo $ban['type'] ?></td><td><?php echo date($datetime, $ban['until']); ?></td><td><a href="admin-base.php?plugin=temporary_ban_admin_page&amp;action=remove_ban&amp;ban_key=<?php echo $ban_key; ?>"><?php _e('Delete', 'temporary-ban'); ?></a></td></tr>
<?php } ?>
	</tbody>
	</table>
<?php
		}
	}
}
?>