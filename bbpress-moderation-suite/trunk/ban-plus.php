<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_banplus_install() {
	bb_add_option('bbmodsuite_banplus_current_bans', array());
	bb_add_option('bbmodsuite_banplus_options', array('min_level' => 'moderate'));
}

function bbmodsuite_banplus_uninstall() {
	bb_delete_option('bbmodsuite_banplus_current_bans');
	bb_delete_option('bbmodsuite_banplus_options');
}

function bbmodsuite_banplus_set_ban($user_id, $type = 'temp', $length = 86400, $notes = '') { // Default 1 day. Returns true if action was unnecessary or completed successfully and false on error.
	$the_options = bb_get_option('bbmodsuite_banplus_options');
	if (!bb_current_user_can($the_options['min_level']))
		return false;
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

	$notes = bb_autop(htmlspecialchars(trim($notes)));

	$current_bans[$user_id] = array(
		'type' => $type,
		'length' => $length,
		'on' => time(),
		'until' => $until,
		'banned_by' => bb_get_current_user_info('ID'),
		'notes' => $notes
	);

	bb_update_option('bbmodsuite_banplus_current_bans', $current_bans);

	return true;
}

function bbmodsuite_banplus_init() {
	$current_bans = bb_get_option('bbmodsuite_banplus_current_bans');
	$changed = false;
	foreach ($current_bans as $user_id => $ban) {
		if ($ban['until'] < time()) {
			unset($current_bans[$user_id]);
			$changed = true;
		}
	}
	if ($changed)
		bb_update_option('bbmodsuite_banplus_current_bans', $current_bans);
}

function bbmodsuite_banplus_maybe_block_user() {
	$current_bans = bb_get_option('bbmodsuite_banplus_current_bans');
	if (!empty($current_bans[bb_get_current_user_info('ID')])) {
		switch ($current_bans[bb_get_current_user_info('ID')]['type']) {
		case 'temp':
			bb_die(sprintf(__('You are banned from this forum until %s from now.  The person who banned you said the reason was: %s', 'bbpress-moderation-suite'), substr(bb_since($current_bans[bb_get_current_user_info('ID')]['until']), 1), $current_bans[bb_get_current_user_info('ID')]['notes']));
			break;
		}
	}
}
bbmodsuite_banplus_maybe_block_user();

function bbmodsuite_banplus_get_ban_types() {
	global $bbmodsuite_active_plugins;
	$types = array('temp');
	if (isset($bbmodsuite_active_plugins['probation']))
		$types[] = 'probation';
	return $types;
}

function bbmodsuite_ban_plus_admin_css() { ?>
<style type="text/css">
/* <![CDATA[ */
#bbAdminSubSubMenu {
	margin: .2em .2em 1em;
}

#bbAdminSubSubMenu li {
	display: inline;
	margin-right: 1em;
}

#bbAdminSubSubMenu li a {
	text-decoration: none;
	color: rgb(40, 140, 60);
	line-height: 1.6em;
}

#bbAdminSubSubMenu li a span {
	font-size: 1.5em;
}

#bbAdminSubSubMenu li a:hover {
	color: rgb(230, 145, 0);
}

#bbAdminSubSubMenu li.current a {
	color: rgb(230, 145, 0);
}
/* ]]> */
</style>
<?php }
add_action('bbpress_moderation_suite_ban_plus_pre_head', create_function('', "add_action('bb_admin_head', 'bbmodsuite_ban_plus_admin_css');"));

function bbpress_moderation_suite_ban_plus() { ?>
<ul id="bbAdminSubSubMenu">
	<li<?php if (!in_array($_GET['page'], array('new_ban', 'admin'))) { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'current_bans'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span><?php _e('Current bans', 'bbpress-moderation-suite') ?></span></a></li>
	<li<?php if ($_GET['page'] === 'new_ban') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'new_ban'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span><?php _e('Ban a user', 'bbpress-moderation-suite') ?></span></a></li>
	<?php if (bb_current_user_can('use_keys')) { ?><li<?php if ($_GET['page'] === 'admin') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'admin'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span>Administration</span></a></li><?php } ?>
</ul>
<?php switch ($_GET['page']) {
	case 'new_ban': ?>
<p>Nothing to see here... Yet.</p>
<?php case 'admin':
		if (bb_current_user_can('use_keys')) { ?>
<p>Nothing to see here... Yet.</p>
<?php		break;
		}
	default: ?>
<p>Nothing to see here... Yet.</p>
<?php
	}
}

function bbmodsuite_banplus_admin_add() {
	global $bb_submenu;
	$the_options = bb_get_option('bbmodsuite_banplus_options');
	$bb_submenu['users.php'][] = array(__('Ban Plus', 'bbpress-moderation-suite'), $the_options['min_level'], 'bbpress_moderation_suite_ban_plus');
}
add_action('bb_admin_menu_generator', 'bbmodsuite_banplus_admin_add');

?>