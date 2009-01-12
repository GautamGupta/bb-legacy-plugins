<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_warning_install() {
	if (!bb_get_option('bbmodsuite_warning_options'))
		bb_update_option('bbmodsuite_warning_options', array('types' => '', 'min_level' => 'moderate', 'cron_every' => 604800, 'expire_time' => 7776000));
}

function bbmodsuite_warning_uninstall() {
	global $bbdb;
	$bbdb->query("DELETE FROM `$bbdb->usermeta` WHERE `meta_key`='bbmodsuite_warnings' OR `meta_key`='bbmodsuite_warnings_count'");
	bb_delete_option('bbmodsuite_warning_options');
}

function bbmodsuite_warning_cron() {
	global $bbdb;
	$now = time();
	$options = bb_get_option('bbmodsuite_warning_options');
	$all_warnings = $bbdb->get_results("SELECT `user_id`, `meta_value` FROM `$bbdb->usermeta` WHERE `meta_key`='bbmodsuite_warnings'");
	foreach ($all_warnings as $i => $warnings) {
		foreach ($warnings as $j => $warning) {
			if ($warning['date'] < time() - $options['expire_time'])
				unset($warnings[$j]);
		}
		if ($all_warnings[$i] !== $warnings) {
			$warnings = array_values($warnings);
			bb_update_usermeta($i, 'bbmodsuite_warnings', $warnings);
			bb_update_usermeta($i, 'bbmodsuite_warnings_count', count($warnings));
		}
	}
	wp_schedule_single_event(time() + $options['cron_every'], 'bbmodsuite_warning_cron');
}

function bbmodsuite_warning_link($parts) {
	$options = bb_get_option('bbmodsuite_warning_options');
	if (bb_current_user_can($options['min_level'])) {
		$post_id = get_post_id();
		$user_id = get_post_author_id($post_id);
		$user = class_exists('BP_User') ? new BP_User($user_id) : new WP_User($user_id);
		if ($user_id !== bb_get_current_user_info('ID') && (bb_current_user_can('use_keys') || (!$user->has_cap('administrate') && bb_current_user_can('administrate')) || (!$user->has_cap('moderate') && bb_current_user_can('moderate')))) {
			$title = __('Give this user a warning.', 'bbpress-moderation-suite');
			$link =	attribute_escape(
				bb_nonce_url(bb_get_uri(
					'bb-admin/admin-base.php',
					array(
						'page' => 'warn_user',
						'user' => $user_id,
						'post' => $post_id,
						'plugin' => 'bbpress_moderation_suite_warning'
					),
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
				),
				'bbmodsuite-warning-warn_' . $user_id . '_' . $post_id)
			);
			$parts[] = '<a class="warn-user" title="' . $title . '" href="' . $link . '">'.__('Warn', 'bbpress-moderation-suite').'</a>';
		}
	}
	return $parts;
}
add_filter('bb_post_admin', 'bbmodsuite_warning_link');

function bbmodsuite_warning_types() {
	$options = bb_get_option('bbmodsuite_warning_options');
	$types = explode("\n", ".\n" . $options['types']);
	$types = array_filter($types);
	unset($types[0]);
	return $types;
}

function bbmodsuite_warning_admin_css() { ?>
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

#bbBody div.updated p, #bbBody div.error p {
	margin: 0;
}
/* ]]> */
</style>
<?php }
add_action('bbpress_moderation_suite_warning_pre_head', create_function('', "add_action('bb_admin_head', 'bbmodsuite_warning_admin_css');"));

function bbpress_moderation_suite_warning() { ?>
<ul id="bbAdminSubSubMenu">
	<li<?php if (!in_array($_GET['page'], array('warn_user', 'admin'))) { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_warning'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span><?php _e('Users with warnings', 'bbpress-moderation-suite') ?></span></a></li>
	<?php if ($_GET['page'] === 'warn_user') { ?><li class="current"><a href="#"><span><?php _e('Warn a user', 'bbpress-moderation-suite') ?></span></a></li><?php } ?>
	<?php if (bb_current_user_can('use_keys')) { ?><li<?php if ($_GET['page'] === 'admin') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_warning', 'page' => 'admin'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span><?php _e('Administration', 'bbpress-moderation-suite') ?></span></a></li><?php } ?>
</ul>
<?php switch ($_GET['page']) {
	case 'warn_user':
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && bb_verify_nonce($_POST['_wpnonce'], 'bbmodsuite-warning-warn-submit_' . $_GET['user'] . '_' . $_GET['post'])) {
			$warnings = bb_get_usermeta($_GET['user'], 'bbmodsuite_warnings');
			if (empty($warnings))
				$warnings = array();
			$warn_type = (int) $_POST['warn_type'];
			if (!in_array($warn_type, bbmodsuite_warning_types()))
				$warn_type = 0;
			$warnings[] = array(
				'from' => bb_get_current_user_info('ID'),
				'type' => $warn_type,
				'date' => time(),
				'notes' => bb_autop(htmlspecialchars(trim($_POST['warn_content']))),
				'post' => $_GET['post']
			);
			bb_mail(bb_get_user_email($_GET['user']), 'Warning', htmlspecialchars(trim($_POST['warn_content'])));
			bb_update_usermeta($_GET['user'], 'bbmodsuite_warnings', $warnings);
			bb_update_usermeta($_GET['user'], 'bbmodsuite_warnings_count', count($warnings)); ?>
<div class="updated"><p><?php _e('User successfully warned.', 'bbpress-moderation-suite') ?></p></div>
<?php		return;
		} elseif (!bb_verify_nonce($_GET['_wpnonce'], 'bbmodsuite-warning-warn_' . $_GET['user'] . '_' . $_GET['post'])) { ?>
<div class="error"><p><?php _e('Invalid warning attempt', 'bbpress-moderation-suite') ?></p></div>
<?php
			return;
		} ?>
<h2><?php _e('Warn a user', 'bbpress-moderation-suite') ?></h2>
<?php
	$post_query = new BB_Query('post', array('post_id' => $_GET['post'], 'post_author' => $_GET['user']));
	$GLOBALS['bb_posts'] =& $post_query->results;
	bb_admin_list_posts(); ?>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array('page' => 'warn_user', 'user' => $_GET['user'], 'post' => $_GET['post'], 'plugin' => 'bbpress_moderation_suite_warning'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
<fieldset>
	<div>
		<label for="warn_type">
			<?php printf(__('Reason for warning %s', 'bbpress-moderation-suite'), get_user_display_name($_GET['user'])); ?>
		</label>
		<div>
			<select name="warn_type" id="warn_type" tabindex="1">
<?php foreach (bbmodsuite_warning_types() as $id => $type) { ?>
				<option value="<?php echo $id; ?>"><?php echo $type; ?></option>
<?php } ?>
				<option value="0" selected="selected"><?php _e('Other', 'bbpress-moderation-suite') ?></option>
			</select>
		</div>
	</div>
	<div>
		<label for="warn_content">
			<?php _e('Notes', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<textarea id="warn_content" name="warn_content" rows="15" cols="43"></textarea>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field('bbmodsuite-warning-warn-submit_' . $_GET['user'] . '_' . $_GET['post']); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e('Warn user', 'bbpress-moderation-suite') ?>" />
</fieldset>
</form>
<?php break;
	case 'admin':
		if (bb_current_user_can('use_keys')) { ?>
<h2><?php _e('Administration', 'bbpress-moderation-suite') ?></h2>
<?php		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$types = trim($_POST['warn_types']);
				$min_level = in_array($_POST['min_level'], array('moderate', 'administrate', 'use_keys')) ? $_POST['min_level'] : 'moderate';
				$cron_every = (int) $_POST['cron_every'];
				if ($cron_every === 0)
					$cron_every = 1;
				$cron_every *= 604800;
				$expire_time = (int) $_POST['expire_time'];
				if ($expire_time === 0)
					$expire_time = 3;
				$expire_time *= 2592000;
				bb_update_option('bbmodsuite_warning_options', compact('types', 'min_level', 'cron_every', 'expire_time'));
				wp_clear_scheduled_hook('bbmodsuite_warning_cron');
				wp_schedule_single_event(time() + $cron_every, 'bbmodsuite_warning_cron'); ?>
<div class="updated"><p><?php _e('Settings successfully saved.', 'bbpress-moderation-suite') ?></p></div>
<?php		}
			$options = bb_get_option('bbmodsuite_warning_options');
?>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array('page' => 'admin', 'plugin' => 'bbpress_moderation_suite_warning'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
<fieldset>
	<div>
		<label for="warn_types">
			<?php _e('Possible reasons for warning users', 'bbpress-moderation-suite') ?>
		</label>
		<div>
			<textarea id="warn_types" name="warn_types" rows="15" cols="43"><?php echo attribute_escape($options['types']) ?></textarea>
		</div>
	</div>
	<div>
		<label for="min_level">
			<?php _e('Minimum level', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<select id="min_level" name="min_level">
				<option value="moderate"<?php if ($options['min_level'] === 'moderate') { ?> selected="selected"<?php } ?>><?php _e('Moderator') ?></option>
				<option value="administrate"<?php if ($options['min_level'] === 'administrate') { ?> selected="selected"<?php } ?>><?php _e('Administrator') ?></option>
				<option value="use_keys"<?php if ($options['min_level'] === 'use_keys') { ?> selected="selected"<?php } ?>><?php _e('Keymaster') ?></option>
			</select>
			<p><?php _e('What should the minimum user level to warn users be?', 'bbpress-moderation-suite'); ?></p>
		</div>
	</div>
	<div>
		<label for="cron_every">
			<?php _e('Check interval', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<input id="cron_every" name="cron_every" class="text short" type="text" value="<?php echo $options['cron_every'] / 604800 ?>" /> weeks
			<p><?php _e('How long should bbPress Moderation Suite wait between checks for expired warnings?', 'bbpress-moderation-suite'); ?></p>
		</div>
	</div>
	<div>
		<label for="expire_time">
			<?php _e('Expiration time', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<input id="expire_time" name="expire_time" class="text short" type="text" value="<?php echo $options['expire_time'] / 2592000 ?>" /> months
			<p><?php _e('How old should warnings be for bbPress Moderation Suite to delete them?', 'bbpress-moderation-suite'); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field('bbmodsuite-warning-admin'); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', 'bbpress-moderation-suite') ?>" />
</fieldset>
</form>
<?php		break;
		}
	default: if (empty($_GET['user'])) {
		global $bbdb; ?>
<h2><?php _e('Users with warnings', 'bbpress-moderation-suite') ?></h2>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('User', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Warnings', 'bbpress-moderation-suite'); ?></th>
			<th class="action"><?php _e('Actions', 'bbpress-moderation-suite'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php $warned_users = $bbdb->get_results("SELECT `meta_value`,`user_id` FROM `$bbdb->usermeta` WHERE `meta_key` = 'bbmodsuite_warnings_count' ORDER BY `meta_value` DESC");
		foreach ($warned_users as $warned_user) {
			$url = bb_get_uri(
					'bb-admin/admin-base.php',
					array(
						'user' => $warned_user->user_id,
						'plugin' => 'bbpress_moderation_suite_warning'
					),
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>
		<tr>
			<td><?php echo get_user_display_name($warned_user->user_id) ?></td>
			<td><?php echo number_format($warned_user->meta_value) ?></td>
			<td>
				<a href="<?php echo $url ?>"><?php _e('View warnings', 'bbpress-moderation-suite') ?></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<?php } else { ?>
<h2><?php printf(__('Warnings given to user "%s"', 'bbpress-moderation-suite'), get_user_display_name($_GET['user'])) ?></h2>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Given by', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Notes', 'bbpress-moderation-suite'); ?></th>
			<th class="action"><?php _e('Actions', 'bbpress-moderation-suite'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php	$warnings = array_reverse((array) bb_get_usermeta($_GET['user'], 'bbmodsuite_warnings'));
		$types = bbmodsuite_warning_types() + array(__('Other', 'bbpress-moderation-suite'));
		foreach ($warnings as $warning) { ?>
		<tr>
			<td><?php echo get_user_display_name($warning['from']) ?></td>
			<td>
				<strong><?php echo $types[$warning['type']] ?></strong>
				<?php echo $warning['notes'] ?>
			</td>
			<td>
				<a href="<?php post_link($warning['post']) ?>"><?php _e('View post', 'bbpress-moderation-suite') ?></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<?php	}
	}
}

function bbmodsuite_warning_admin_add() {
	global $bb_submenu;
	$bb_submenu['users.php'][] = array(__('Warning', 'bbpress-moderation-suite'), 'moderate', 'bbpress_moderation_suite_warning');
}
add_action('bb_admin_menu_generator', 'bbmodsuite_warning_admin_add');

?>