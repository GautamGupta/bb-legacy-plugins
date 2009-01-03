<?php
/*
Plugin Name: bbPress Moderation Suite
Description: A set of tools to help moderate your forums.
Plugin URI: http://llamaslayers.net/daily-llama/tag/bbpress-moderation-suite
Author: Nightgunner5
Author URI: http://llamaslayers.net/
Version: 0.1-alpha1
*/

function bbmodsuite_init() {
	global $bbmod_plugins, $active_bbmod_plugins;
	$bbmod_plugins = array(
		'report' => array(
			'name' => __('Report', 'bbpress-moderation-suite'),
			'description' => '<p>' . __('Allows users to report posts for consideration by the moderation team.', 'bbpress-moderation-suite') . '</p>',
			'filename' => 'report.php'
		)
	);
	$active_bbmod_plugins = (array) bb_get_option('bbpress_moderation_suite_helpers');
	foreach ($active_bbmod_plugins as $plugin) {
		require_once $bbmod_plugins[$plugin]['filename'];
	}
}
add_action('bb_init', 'bbmodsuite_init');

function bbmodsuite_admin_add() {
	global $bb_submenu;
	$plugins = array($bb_submenu['plugins.php'][5]);
	$plugins[] = array(__('bbPress Moderation Suite', 'bbpress-moderation-suite'), 'use_keys', 'bbpress_moderation_suite');
	$bb_submenu['plugins.php'] = array_merge($plugins, array_slice($bb_submenu['plugins.php'], 1));
}
add_action('bb_admin_menu_generator', 'bbmodsuite_admin_add');

function bbmodsuite_admin_parse() {
	global $bbmod_plugins, $active_bbmod_plugins;
	$plugin = $_GET['mod_helper'];
	$action = $_GET['action'];
	if ($plugin && $action && bb_verify_nonce($_GET['_wpnonce'], $action . '-plugin_' . $plugin)) {
		switch ($action) {
			case 'activate':
				if (in_array($plugin, $active_bbmod_plugins) ||
					!isset($bbmod_plugins[$plugin])) break;
				$active_bbmod_plugins[] = $plugin;
				bb_update_option('bbpress_moderation_suite_helpers', $active_bbmod_plugins);
				require_once $bbmod_plugins[$plugin]['filename'];
				call_user_func('bbmodsuite_' . $plugin . '_install');
				bb_admin_notice(sprintf(__('Plugin "%s" <strong>activated</strong>', 'bbpress-moderation-suite'), $bbmod_plugins[$plugin]['name']));
				break;
			case 'deactivate':
				if (!in_array($plugin, $active_bbmod_plugins) ||
					!isset($bbmod_plugins[$plugin])) break;
				$active_bbmod_plugins = array_flip($active_bbmod_plugins);
				unset($active_bbmod_plugins[$plugin]);
				$active_bbmod_plugins = array_flip($active_bbmod_plugins);
				bb_update_option('bbpress_moderation_suite_helpers', $active_bbmod_plugins);
				bb_admin_notice(sprintf(__('Plugin "%s" <strong>deactivated</strong>', 'bbpress-moderation-suite'), $bbmod_plugins[$plugin]['name']));
			case 'uninstall':
				if (!in_array($plugin, $active_bbmod_plugins) ||
					!isset($bbmod_plugins[$plugin])) break;
				$active_bbmod_plugins = array_flip($active_bbmod_plugins);
				unset($active_bbmod_plugins[$plugin]);
				$active_bbmod_plugins = array_flip($active_bbmod_plugins);
				bb_update_option('bbpress_moderation_suite_helpers', $active_bbmod_plugins);
				call_user_func('bbmodsuite_' . $plugin . '_uninstall');
				bb_admin_notice(sprintf(__('Plugin "%s" <strong>deactivated</strong> and <strong>uninstalled</strong>', 'bbpress-moderation-suite'), $bbmod_plugins[$plugin]['name']));
		}
	}
}
add_action('bbpress_moderation_suite_pre_head', 'bbmodsuite_admin_parse');

function bbpress_moderation_suite() {
	global $bbmod_plugins, $active_bbmod_plugins;
?>
<h2><?php _e('bbPress Moderation Suite', 'bbpress-moderation-suite'); ?></h2>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Moderation Assistants', 'bbpress-moderation-suite'); ?></th>
			<th><?php _e('Description', 'bbpress-moderation-suite'); ?></th>
			<th class="action"><?php _e('Actions', 'bbpress-moderation-suite'); ?></th>
		</tr>
	</thead>
	<tbody>

<?php
	foreach ( $bbmod_plugins as $plugin => $plugin_data ) :
		$class = '';
		$action = 'activate';
		$action_class = 'edit';
		$action_text = __('Activate', 'bbpress-moderation-suite');
		if ( in_array($plugin, $active_bbmod_plugins) ) {
			$class =  'active';
			$action = 'deactivate';
			$action_class = 'delete';
			$action_text = __('Deactivate', 'bbpress-moderation-suite');
		}
		$href = attribute_escape(
			bb_nonce_url(
				bb_get_uri(
					'bb-admin/admin-base.php',
					array(
						'mod_helper' => urlencode($plugin),
						'action' => $action,
						'plugin' => 'bbpress_moderation_suite'
					),
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
				),
				$action . '-plugin_' . $plugin
			)
		);
?>

		<tr<?php alt_class( 'normal_plugin', $class ); ?>>
			<td><?php echo $plugin_data['name']; ?></td>
			<td>
				<?php echo $plugin_data['description']; ?>
			</td>
			<td class="action">
				<a class="<?php echo $action_class; ?>" href="<?php echo $href; ?>"><?php echo $action_text; ?></a>
<?php if (in_array($plugin, $active_bbmod_plugins)) { ?>
				<a class="delete" href="<?php echo attribute_escape(
	bb_nonce_url(
		bb_get_uri(
			'bb-admin/admin-base.php',
			array(
				'mod_helper' => urlencode($plugin),
				'action' => 'uninstall',
				'plugin' => 'bbpress_moderation_suite'
			),
			BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
		),
		'uninstall-plugin_' . $plugin
	)
); ?>"><?php _e('Uninstall', 'bbpress-moderation-suite'); ?></a>
<?php } ?>
			</td>
		</tr>

<?php
	endforeach;
?>

	</tbody>
</table>
<?php
}
?>