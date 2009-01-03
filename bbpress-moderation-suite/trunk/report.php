<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_report_install() {
	global $bbdb;
	$bbdb->query('CREATE TABLE IF NOT EXISTS `' . $bbdb->prefix . 'bbmodsuite_reports` (
	`ID` int(10) NOT NULL auto_increment,
	`report_reason` int(10) NOT NULL default \'0\',
	`report_from` int(10) NOT NULL,
	`reported_post` int(10) NOT NULL,
	`report_content` text NOT NULL,
	`report_type` varchar(250) NOT NULL default \'new\',
	`resolved_by` int(10) NOT NULL default \'0\',
	`resolve_type` int(10) NOT NULL default \'0\',
	`resolve_content` text NOT NULL default \'\',
	`reported_at` datetime NOT NULL,
	`resolved_at` datetime,
	PRIMARY KEY (`ID`)
)');
}

function bbmodsuite_report_uninstall() {
	global $bbdb;
	$bbdb->query('DROP TABLE `' . $bbdb->prefix . 'bbmodsuite_reports`');
	bb_delete_option('bbmodsuite_report_types');
	bb_delete_option('bbmodsuite_report_resolve_types');
}

if (!defined('BB_PATH') && isset($_GET['report'])) {
	if (file_exists('../bb-load.php'))
		require_once '../bb-load.php';
	elseif (file_exists('../../bb-load.php'))
		require_once '../../bb-load.php';
	if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
		if (!bb_verify_nonce($_GET['_nonce'], 'bbmodsuite-report-' . $_GET['report'])) bb_die(__('Invalid report', 'bbpress-moderation-suite'));

		global $forums, $bb_post;
		$bb_post = bb_get_post($_GET['report']);
		if ($bb_post->post_status) bb_die(__('Invalid report', 'bbpress-moderation-suite'));

		function bbmodsuite_report_form_uri($a, $b) {
			if ($b === 'bb-post.php') return str_replace('\\', '/', substr(BB_PLUGIN_URL, 0, -1) . str_replace(realpath(BB_PLUGIN_DIR), '', dirname(__FILE__)) . '/' . basename(__FILE__) . '?report=' . $_GET['report']);
			return $a;
		}
		add_filter('bb_get_uri', 'bbmodsuite_report_form_uri', 10, 2);
		$forums = false;

		function bbmodsuite_report_form($a, $b) {
			if ($b === 'post-form.php') {
				return dirname(__FILE__) . '/report-form.php';
			}
			return $a;
		}
		add_filter('bb_template', 'bbmodsuite_report_form', 10, 2);
		
		bb_load_template('front-page.php');
	} else {
		if (!($_POST['report_reason'] === '0' || array_key_exists($_POST['report_reason'], bbmodsuite_report_reasons()))) bb_die(__('Invalid report', 'bbpress-moderation-suite'));
		$report_reason = $_POST['report_reason'];
		$report_content = htmlspecialchars($_POST['report_content']);
		$reported_post = $_GET['report'];
		$report_from = bb_get_current_user_info('ID');
		$reported_at = bb_current_time('mysql');
		global $bbdb;
		$bbdb->insert($bbdb->prefix . 'bbmodsuite_reports', compact('report_reason', 'report_content', 'reported_post', 'report_from', 'reported_at'));
		bb_die(__('<p>Your report was submitted. The moderation staff will review the post in question.</p>', 'bbpress-moderation-suite'));
	}
}

function bbmodsuite_report_admin_css() { ?>
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
add_action('bbpress_moderation_suite_report_pre_head', create_function('', "add_action('bb_admin_head', 'bbmodsuite_report_admin_css');"));

function bbpress_moderation_suite_report() { ?>
<ul id="bbAdminSubSubMenu">
	<li<?php if (!in_array($_GET['page'], array('resolve_reports', 'new_reports', 'resolved_reports', 'resolve_report', 'admin')) || $_GET['page'] === 'new_reports') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_report', 'page' => 'new_reports'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span>New</span></a></li>
	<li<?php if ($_GET['page'] === 'resolved_reports') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_report', 'page' => 'resolved_reports'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span>Resolved</span></a></li>
	<?php if ($_GET['page'] === 'resolve_reports') { ?><li class="current"><a href="#"><span>Resolve</span></a></li><?php } ?>
	<?php if (bb_current_user_can('use_keys')) { ?><li<?php if ($_GET['page'] === 'admin') { ?> class="current"<?php } ?>><a href="<?php echo bb_get_uri('bb-admin/admin-base.php', array('plugin' => 'bbpress_moderation_suite_report', 'page' => 'admin'), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN); ?>"><span>Administration</span></a></li><?php } ?>
</ul>
<?php
	switch ($_GET['page']) {
	case 'resolve_report':
		if (!bb_verify_nonce($_POST['_wpnonce'], 'bbmodsuite-report-resolve-submit_' . $_GET['report'])) return;
		global $bbdb;
		if (trim($_POST['resolve_content']) && ($_POST['resolve_type'] === '0' || array_key_exists((int) $_POST['resolve_type'], bbmodsuite_report_resolve_types())))
			$report_id = $bbdb->get_var($bbdb->prepare('SELECT `ID` FROM `' . $bbdb->prefix . 'bbmodsuite_reports` WHERE `report_type`=\'new\' AND `ID`=%d', $_GET['report']));
		if (!$report_id) { ?>
<div class="error"><p>Invalid resolve attempt.</p></div>
<?php	} else {
			if ($bbdb->update($bbdb->prefix . 'bbmodsuite_reports', array('report_type' => 'resolved', 'resolve_content' => trim($_POST['resolve_content']), 'resolved_at' => bb_current_time('mysql'), 'resolved_by' => bb_get_current_user_info('ID'), 'resolve_type' => (int) $_POST['resolve_type']), array('ID' => $report_id))) { ?>
<div class="updated"><p>Successfully resolved report.</p></div>
<?php		}
		}
		break;
	case 'resolve_reports':
		if (!bb_verify_nonce($_GET['_wpnonce'], 'bbmodsuite-report-resolve_' . $_GET['report'])) return; ?>
<h2>Resolve Report #<?php echo $_GET['report'] ?></h2>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array('page' => 'resolve_report', 'report' => $_GET['report'], 'plugin' => 'bbpress_moderation_suite_report'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
<fieldset>
	<div>
		<label for="resolve_type">
			<?php _e('Method of Resolving', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<select name="resolve_type" id="resolve_type" tabindex="1">
<?php foreach (bbmodsuite_report_resolve_types() as $id => $reason) { ?>
				<option value="<?php echo $id; ?>"><?php echo $reason; ?></option>
<?php } ?>
				<option value="0" selected="selected"><?php _e('Other', 'bbpress-moderation-suite') ?></option>
			</select>
		</div>
	</div>
	<div>
		<label for="resolve_content">
			<?php _e('Notes', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<textarea id="resolve_content" name="resolve_content" rows="15" cols="43"></textarea>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field('bbmodsuite-report-resolve-submit_' . $_GET['report']); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e('Resolve', 'bbpress-moderation-suite') ?>" />
</fieldset>
</form>
<?php	break;
	case 'resolved_reports':
		global $bbdb;
		$reports = $bbdb->get_results('SELECT ID, report_reason, report_from, reported_post, report_content, resolved_by, resolve_type, resolve_content FROM `' . $bbdb->prefix . 'bbmodsuite_reports` WHERE `report_type`=\'resolved\'');
		$reasons = bbmodsuite_report_reasons() + array(__('Other', 'bbpress-moderation-suite'));
		$resolve_types = bbmodsuite_report_resolve_types() + array(__('Other', 'bbpress-moderation-suite'));
?><h2><?php _e('Resolved Reports', 'bbpress-moderation-suite'); ?></h2>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Reported By', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Description', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Resolved By', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Notes', 'bbpress-moderation-suite'); ?></th>
			<th class="action"><?php _e('Actions', 'bbpress-moderation-suite'); ?></th>
		</tr>
	</thead>
	<tbody>

<?php
	foreach ( $reports as $report ) {
		$resolve_url = bb_nonce_url(bb_get_uri(
						'bb-admin/admin-base.php',
						array(
							'page' => 'resolve_reports',
							'report' => $report->ID,
							'plugin' => 'bbpress_moderation_suite_report'
						),
						BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
					), 'bbmodsuite-report-resolve_' . $report->ID);
?>

		<tr<?php alt_class( 'reported_post'); ?>>
			<td><?php echo get_user_display_name($report->report_from); ?></td>
			<td><strong><?php echo $reasons[$report->report_reason]; ?></strong>
				<?php echo stripslashes(bb_autop($report->report_content)); ?>
			</td>
			<td><?php echo get_user_display_name($report->resolved_by); ?></td>
			<td><strong><?php echo $resolve_types[$report->resolve_type]; ?></strong>
				<?php echo stripslashes(bb_autop($report->resolve_content)); ?>
			</td>
			<td class="action">
				<a target="_blank" href="<?php post_link($report->reported_post); ?>"><?php _e('View reported post', 'bbpress-moderation-suite'); ?></a>
			</td>
		</tr>

<?php
	} // foreach reports as report
?>

	</tbody>
</table>
<?php
	break;
	case 'admin':
		if (bb_current_user_can('use_keys')) { ?>
<h2><?php _e('Administration', 'bbpress-moderation-suite'); ?></h2>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (bb_verify_nonce($_POST['_wpnonce'], 'bbmodsuite-report-admin')) {
		$report_types = trim($_POST['report_types']);
		$resolve_types = trim($_POST['resolve_types']);
		bb_update_option('bbmodsuite_report_types', $report_types);
		bb_update_option('bbmodsuite_report_resolve_types', $resolve_types); ?>
<div class="updated"><p><?php _e('Settings successfully saved.', 'bbpress-moderation-suite') ?></p></div>
<?php } else { ?>
<div class="error"><p><?php _e('Saving the settings failed.', 'bbpress-moderation-suite') ?></p></div>
<?php }
} ?>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array('page' => 'admin', 'plugin' => 'bbpress_moderation_suite_report'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
<fieldset>
	<div>
		<label for="report_types">
			<?php _e('Report types', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<textarea id="report_types" name="report_types" rows="15" cols="43"><?php bb_form_option('bbmodsuite_report_types'); ?></textarea>
			<p><?php _e('Fill this box with generic reasons to report posts. (One per line)', 'bbpress-moderation-suite'); ?></p>
		</div>
	</div>
	<div>
		<label for="resolve_types">
			<?php _e('Resolve types', 'bbpress-moderation-suite'); ?>
		</label>
		<div>
			<textarea id="resolve_types" name="resolve_types" rows="15" cols="43"><?php bb_form_option('bbmodsuite_report_resolve_types'); ?></textarea>
			<p><?php _e('Fill this box with generic ways of resolving reports. (One per line)', 'bbpress-moderation-suite'); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field('bbmodsuite-report-admin'); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', 'bbpress-moderation-suite') ?>" />
</fieldset>
</form>
<?php		break;
		}
	case 'new_reports':
	default:
		global $bbdb;
		$reports = $bbdb->get_results('SELECT ID, report_reason, report_from, reported_post, report_content FROM `' . $bbdb->prefix . 'bbmodsuite_reports` WHERE `report_type`=\'new\'');
		$reasons = bbmodsuite_report_reasons() + array(__('Other', 'bbpress-moderation-suite'));
?><h2><?php _e('New Reports', 'bbpress-moderation-suite'); ?></h2>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Reported By', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Description', 'bbpress-moderation-suite'); ?></th>
			<th class="action"><?php _e('Actions', 'bbpress-moderation-suite'); ?></th>
		</tr>
	</thead>
	<tbody>

<?php
	foreach ( $reports as $report ) {
		$resolve_url = bb_nonce_url(bb_get_uri(
						'bb-admin/admin-base.php',
						array(
							'page' => 'resolve_reports',
							'report' => $report->ID,
							'plugin' => 'bbpress_moderation_suite_report'
						),
						BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
					), 'bbmodsuite-report-resolve_' . $report->ID);
?>

		<tr<?php alt_class( 'reported_post'); ?>>
			<td><?php echo get_user_display_name($report->report_from); ?></td>
			<td><strong><?php echo $reasons[$report->report_reason]; ?></strong>
				<?php echo stripslashes(bb_autop($report->report_content)); ?>
			</td>
			<td class="action">
				<a target="_blank" href="<?php post_link($report->reported_post); ?>"><?php _e('View reported post', 'bbpress-moderation-suite'); ?></a><br />
				<a href="<?php echo $resolve_url; ?>">Resolve</a>
			</td>
		</tr>

<?php
	} // foreach reports as report
?>

	</tbody>
</table>
<?php
	}
}

function bbmodsuite_report_admin_add() {
	global $bb_submenu;
	$bb_submenu['content.php'][] = array(__('Reports', 'bbpress-moderation-suite'), 'moderate', 'bbpress_moderation_suite_report');
}
add_action('bb_admin_menu_generator', 'bbmodsuite_report_admin_add');

function bbmodsuite_report_get_reports_css() {
	global $posts, $bbdb;
	if (!$posts) return;
	$new_reports = $bbdb->get_results($bbdb->prepare('SELECT `reported_post` FROM `' . $bbdb->prefix . 'bbmodsuite_reports` WHERE `report_type`=%s', 'new'));
	$reported_post_ids = array();
	foreach ($new_reports as $new_report) {
		foreach ($posts as $post) {
			if ($new_report->reported_post === $post->post_id)
				$reported_post_ids[] = $new_report->reported_post;
		}
	}
	return '#post-' . implode(', #post-', $reported_post_ids) . ' {
		background: #900;
		color: #fff;
	}';
}

function bbmodsuite_report_css() {
	echo '<style type="text/css">
/* <![CDATA[ */
	.reports_waiting {
		position: fixed;
		bottom: 1em;
		left: 30%;
		width: 40%;
		padding: .5em;
		font-size: 3em;
		font-weight: normal;
		text-align: center;
		border: 5px solid #f00;
		background: #f99;
		z-index: 9999;
		opacity: .8;
	}
	.reports_waiting p {
		margin: 0;
	}
	.reports_waiting:hover {
		opacity: 1;
	}
	.reports_waiting a, .reports_waiting a:hover {
		color: #000 !important;
	}
	.reports_waiting span {
		text-decoration: blink;
	}' . bbmodsuite_report_get_reports_css() . '
/* ]]> */
</style>';
}
add_action('bb_head', 'bbmodsuite_report_css');

function bbmodsuite_report_reasons() {
	$reasons = explode("\n", ".\n" . bb_get_option('bbmodsuite_report_types'));
	$reasons = array_filter($reasons);
	unset($reasons[0]);
	return $reasons;
}

function bbmodsuite_report_resolve_types() {
	$reasons = explode("\n", ".\n" . bb_get_option('bbmodsuite_report_resolve_types'));
	$reasons = array_filter($reasons);
	unset($reasons[0]);
	return $reasons;
}

function bbmodsuite_report_header() {
	if (!bb_current_user_can('moderate')) return;
	$link = bb_get_uri(
				'bb-admin/admin-base.php',
				array(
					'page' => 'new_reports',
					'plugin' => 'bbpress_moderation_suite_report'
				),
				BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
			);
	$number = number_format(bbmodsuite_report_count('new'));
	if ($number == 0) return;
	if ($number == 1) echo '<div class="reports_waiting"><p><a href="' . $link . '">' . __('There is a new report waiting for you!', 'bbpress-moderation-suite') . '</a></p></div>';
	else echo '<div class="reports_waiting"><p><a href="' . $link . '">' . sprintf(__('There are <span>%s</span> new reports waiting for you!', 'bbpress-moderation-suite'), $number) . '</a></p></div>';
}
add_action('bb_logged-in.php', 'bbmodsuite_report_header');
add_action('bb_login-form.php', 'bbmodsuite_report_header');

function bbmodsuite_report_count($type = 'all') {
	global $bbdb;
	if ($type === 'all') return $bbdb->get_var('SELECT COUNT(*) FROM `' . $bbdb->prefix . 'bbmodsuite_reports`');
	return $bbdb->get_var($bbdb->prepare('SELECT COUNT(*) FROM `' . $bbdb->prefix . 'bbmodsuite_reports` WHERE `report_type`=%s', $type));
}

function bbmodsuite_report_link($parts) { 
	$post_id = get_post_id();
	if (bb_current_user_can('participate') && !bb_current_user_can('delete_post', $post_id)) {
		$post_author_id = get_post_author_id($post_id);
		$post_author = new WP_User($post_author_id);
		if ($post_author_id != bb_get_current_user_info('ID') && !$post_author->has_cap('moderate')) {
			$title = __('Report this post to a moderator.', 'bbpress-moderation-suite');
			$bbreport_confirm = __('Are you sure you want to report this post?', 'bbpress-moderation-suite');
			$bbreport_href = str_replace('\\', '/', substr(BB_PLUGIN_URL, 0, -1) . str_replace(realpath(BB_PLUGIN_DIR), '', dirname(__FILE__)) . '/' . basename(__FILE__));
			$parts[] = '<a class="report_post" title="' . $title . '" href="' . $bbreport_href . '?report=' . $post_id . '&amp;_nonce=' . bb_create_nonce('bbmodsuite-report-' . $post_id) . '">'.__("Report", 'bbpress-moderation-suite').'</a>';
		}
	}
	return $parts;
}
add_filter('bb_post_admin', 'bbmodsuite_report_link');

?>