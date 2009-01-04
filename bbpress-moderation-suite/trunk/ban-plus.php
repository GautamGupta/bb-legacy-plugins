<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_banplus_install() {
	bb_add_option('bbmodsuite_banplus_current_bans', array());
}

function bbmodsuite_banplus_uninstall() {
	bb_delete_option('bbmodsuite_banplus_current_bans');
}

function bbmodsuite_banplus_set_ban($user_id, $type = 'temp', $length = 86400) { // Default 1 day. Returns true if action was unnecessary or completed successfully and false on error.
	$user_id = bb_get_user_id($user_id);
	if (!$user_id)
		return false;
	if ($user_id === bb_get_current_user_info('ID'))
		return false;

	$user = bb_get_user($user_id);
	if (($user->has_cap('moderate') && !bb_current_user_can('administrate')) || ($user->has_cap('administrate') && !bb_current_user_can('use_keys')))
		return false;

	$current_bans = bb_get_option('bbmodsuite_banplus_current_bans');
	if ($type === 'unban') {
		if (!isset($current_bans[$user_id]))
			return true;
		unset($current_bans[$user_id]);
		bb_update_option('bbmodsuite_banplus_current_bans', $current_bans);
		return true;
	}

	$length = (int) $length;
	if ($length < 0)
		return false;
	$until = time() + $length;

	if (!in_array($type, bbmodsuite_banplus_get_ban_types()))
		return false;

	if ($current_bans[$user_id]['type'] === $type && $current_bans[$user_id]['length'] === $length)
		return true;
	if (isset($current_bans[$user_id]))
		return false;

	$current_bans[$user_id] = array(
		'type' => $type,
		'length' => $length,
		'on' => time(),
		'until' => $until,
		'banned_by' => bb_get_current_user_info('ID')
	);

	bb_update_option('bbmodsuite_banplus_current_bans', $current_bans);

	return true;
}

function bbmodsuite_banplus_get_ban_types() {
	global $bbmodsuite_active_plugins;
	$types = array('temp');
	if (isset($bbmodsuite_active_plugins['probation']))
		$types[] = 'probation';
	return $types;
}

function bbpress_moderation_suite_ban_plus() { ?>
<p>Nothing to see here... Yet.</p>
<?php
}

function bbmodsuite_banplus_admin_add() {
	global $bb_submenu;
	$bb_submenu['users.php'][] = array(__('Ban Plus', 'bbpress-moderation-suite'), 'moderate', 'bbpress_moderation_suite_ban_plus');
}
add_action('bb_admin_menu_generator', 'bbmodsuite_banplus_admin_add');

?>