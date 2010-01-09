<?php
/*
Plugin Name: bbPress Moderation Suite
Description: A set of tools to help moderate your forums.
Plugin URI: http://nightgunner5.wordpress.com/tag/bbpress-moderation-suite/
Author: Nightgunner5
Author URI: http://nightgunner5.wordpress.com/
Version: 0.1-rc1
Requires at least: 1.0
Tested up to: trunk
Text Domain: bbpress-moderation-suite
Domain Path: translations/
*/

/* $Id$ */

function bbmodsuite_init() {
	global $bbmodsuite_plugins, $bbmodsuite_active_plugins, $bbmodsuite_cache;

	load_plugin_textdomain( 'bbpress-moderation-suite', dirname( __FILE__ ) . '/translations' );

	$bbmodsuite_plugins = array(
		'report' => array(
			'name'        => __( 'Report', 'bbpress-moderation-suite' ),
			'description' => __( 'Allows users to report posts for consideration by the moderation team.', 'bbpress-moderation-suite' ),
			'filename'    => 'report.php',
			'panel'       => 'bbpress_moderation_suite_report',
			'can_view'    => 'bbmodsuite_report_can_view'
		),
		'banplus' => array(
			'name'        => __( 'Ban Plus', 'bbpress-moderation-suite' ),
			'description' => __( 'Implements advanced banning features like temporary banning and automated banning (if used with the Warnings assistant)  Ban Plus does not use the core rank system, so removing the plugin will unban everyone banned using this method.', 'bbpress-moderation-suite' ),
			'filename'    => 'ban-plus.php',
			'panel'       => 'bbpress_moderation_suite_ban_plus',
			'can_view'    => 'bbmodsuite_banplus_can_view'
		),
		'warning' => array(
			'name'        => __( 'Warning', 'bbpress-moderation-suite' ),
			'description' => __( 'Allows moderators and higher to warn users that break rules. Can be set to automatically block or (if Ban Plus is active) temporarily ban problematic users from the forums.', 'bbpress-moderation-suite' ),
			'filename'    => 'warning.php',
			'panel'       => 'bbpress_moderation_suite_warning',
			'can_view'    => 'bbmodsuite_warning_can_view'
		),
		'modlog' => array(
			'name'        => __( 'Moderation Log', 'bbpress-moderation-suite' ),
			'description' => __( 'Keeps track of important moderator actions.', 'bbpress-moderation-suite' ),
			'filename'    => 'modlog.php',
			'panel'       => 'bbpress_moderation_suite_modlog',
			'can_view'    => 'bbmodsuite_modlog_can_view'
		),
		'move' => array(
			'name'        => __( 'Move!', 'bbpress-moderation-suite' ),
			'description' => __( 'Allows moderators to move, split, and merge topics and posts.', 'bbpress-moderation-suite' ),
			'filename'    => 'move.php',
			'panel'       => 'bbpress_moderation_suite_move',
			'can_view'    => 'bbmodsuite_move_can_view'
		)
	);

	$bbmodsuite_active_plugins = (array)bb_get_option( 'bbpress_moderation_suite_helpers' );
	$bbmodsuite_cache = array();

	foreach ( $bbmodsuite_plugins as $plugin => $data ) {
		if ( in_array( $plugin, $bbmodsuite_active_plugins ) ) {
			$bbmodsuite_cache[$plugin] = array();
			include_once dirname( __FILE__ ) . '/' . $data['filename'];
		}
	}

	do_action( 'bbmodsuite_init' );
}
add_action( 'bb_init', 'bbmodsuite_init' );

function bbmodsuite_admin_add() {
	bb_admin_add_menu( __( 'Moderation', 'bbpress-moderation-suite' ), 'moderate', 'bbpress_moderation_suite', false, '', 'bbmodsuite-menu' );
	bb_admin_add_submenu( __( 'bbPress Moderation Suite', 'bbpress-moderation-suite' ), 'moderate', 'bbpress_moderation_suite', 'bbpress_moderation_suite' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_admin_add' );

function bbmodsuite_admin_global_css() {
	echo '<style type="text/css">ul#bbAdminMenu li#bbmodsuite-menu.bb-menu a .bb-menu-icon{background:url(../' . basename( dirname( dirname( __FILE__ ) ) ) . '/' . basename( dirname( __FILE__ ) ) . '/icon.png) 0 0}ul#bbAdminMenu li#bbmodsuite-menu.bb-menu a:hover .bb-menu-icon{background-position:22px 0}</style>';
}
add_action( 'bb_admin_head', 'bbmodsuite_admin_global_css' );

function bbmodsuite_admin_parse() {
	global $bbmodsuite_plugins, $bbmodsuite_active_plugins;
	$plugin = $_GET['mod_helper'];
	$action = $_GET['action'];
	if ( $plugin && $action && bb_verify_nonce( $_GET['_wpnonce'], $action . '-plugin_' . $plugin ) ) {
		switch ( $action ) {
			case 'activate':
				if ( in_array( $plugin, $bbmodsuite_active_plugins ) ||
					!isset( $bbmodsuite_plugins[$plugin] ) ) break;
				$bbmodsuite_active_plugins[] = $plugin;
				bb_update_option( 'bbpress_moderation_suite_helpers', $bbmodsuite_active_plugins );
				include_once $bbmodsuite_plugins[$plugin]['filename'];
				call_user_func( 'bbmodsuite_' . $plugin . '_install' );
				bb_admin_notice( sprintf( __( 'Plugin "%s" <strong>activated</strong>', 'bbpress-moderation-suite' ), $bbmodsuite_plugins[$plugin]['name'] ) );
				do_action( 'bbmodsuite-install', $bbmodsuite_plugins[$plugin]['name'] );
				break;
			case 'deactivate':
				if ( !in_array( $plugin, $bbmodsuite_active_plugins ) ||
					!isset($bbmodsuite_plugins[$plugin] ) ) break;
				$bbmodsuite_active_plugins = array_flip( $bbmodsuite_active_plugins );
				unset( $bbmodsuite_active_plugins[$plugin] );
				$bbmodsuite_active_plugins = array_flip( $bbmodsuite_active_plugins );
				bb_update_option( 'bbpress_moderation_suite_helpers', $bbmodsuite_active_plugins );
				bb_admin_notice( sprintf( __( 'Plugin "%s" <strong>deactivated</strong>', 'bbpress-moderation-suite' ), $bbmodsuite_plugins[$plugin]['name'] ) );
				do_action( 'bbmodsuite-deactivate', $bbmodsuite_plugins[$plugin]['name'] );
				break;
			case 'uninstall':
				if ( !in_array( $plugin, $bbmodsuite_active_plugins ) ||
					!isset( $bbmodsuite_plugins[$plugin] ) ) break;
				$bbmodsuite_active_plugins = array_flip( $bbmodsuite_active_plugins );
				unset( $bbmodsuite_active_plugins[$plugin] );
				$bbmodsuite_active_plugins = array_flip( $bbmodsuite_active_plugins );
				bb_update_option( 'bbpress_moderation_suite_helpers', $bbmodsuite_active_plugins );
				call_user_func( 'bbmodsuite_' . $plugin . '_uninstall' );
				bb_admin_notice( sprintf( __( 'Plugin "%s" <strong>deactivated</strong> and <strong>uninstalled</strong>', 'bbpress-moderation-suite' ), $bbmodsuite_plugins[$plugin]['name'] ) );
				do_action( 'bbmodsuite-uninstall', $bbmodsuite_plugins[$plugin]['name'] );
		}
	}
}
add_action( 'bbpress_moderation_suite_pre_head', 'bbmodsuite_admin_parse' );

function bbpress_moderation_suite() {
	global $bbmodsuite_plugins, $bbmodsuite_active_plugins;

	if ( strncmp( dirname( __FILE__ ), realpath( BB_PLUGIN_DIR ), strlen( realpath( BB_PLUGIN_DIR ) ) ) ) {
?>
<div class="error"><p style="margin:0"><?php printf( __( 'Your <code>%1$s</code> folder needs to be moved to the <code>my-plugins</code> folder.  It is currently in the <code>%2$s</code> folder.', 'bbpress-moderation-suite' ), basename( dirname( __FILE__ ) ), basename( dirname( dirname( __FILE__ ) ) ) ); ?></p></div>
<?php } ?>
<h2><?php _e( 'bbPress Moderation Suite', 'bbpress-moderation-suite' ); ?></h2>
<p><?php _e( 'bbPress Moderation Suite is a set of tools to help moderate your forums.  There are multiple parts, each able to function separately from the others.  You can activate or deactivate each part separately.  It even includes an uninstaller so if you don\'t want to use a part anymore, you can remove all of its database usage!', 'bbpress-moderation-suite' ); ?></p>
	<table id="plugins-list" class="widefat">
		<thead>
			<tr>
				<th><?php _e( 'Moderation Assistants', 'bbpress-moderation-suite' ); ?></th>
				<th><?php _e( 'Description', 'bbpress-moderation-suite' ); ?></th>
			</tr>

		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Moderation Assistants', 'bbpress-moderation-suite' ); ?></th>
				<th><?php _e( 'Description', 'bbpress-moderation-suite' ); ?></th>
			</tr>
		</tfoot>
		<tbody>
<?php
	foreach ( $bbmodsuite_plugins as $plugin => $plugin_data ) {
		if ( !bb_current_user_can( 'manage_plugins' ) && ( !in_array( $plugin, $bbmodsuite_active_plugins ) || empty( $plugin_data['can_view'] ) || !function_exists( $plugin_data['can_view'] ) || !bb_current_user_can( call_user_func( $plugin_data['can_view'] ) ) ) )
			continue;

		$class        = 'inactive';
		$action       = 'activate';
		$action_class = 'edit';
		$action_text  = __( 'Activate', 'bbpress-moderation-suite' );
		if ( in_array( $plugin, $bbmodsuite_active_plugins ) ) {
			$class        = 'active';
			$action       = 'deactivate';
			$action_class = 'delete';
			$action_text  = __( 'Deactivate', 'bbpress-moderation-suite' );
		}
		$href = attribute_escape(
			bb_nonce_url(
				bb_get_uri(
					'bb-admin/admin-base.php',
					array(
						'mod_helper' => urlencode( $plugin ),
						'action' => $action,
						'plugin' => 'bbpress_moderation_suite',
					),
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
				),
				$action . '-plugin_' . $plugin
			)
		);
?>		<tr class="<?php echo $class; ?>">
			<td class="plugin-name">
				<span class="row-title"><?php echo $plugin_data['name']; ?></span>
				<div><span class="row-actions">
				<?php if ( bb_current_user_can( 'manage_plugins' ) ) { ?>
					<a class="<?php echo $action_class; ?>" href="<?php echo $href; ?>"><?php echo $action_text; ?></a>
<?php if ( in_array( $plugin, $bbmodsuite_active_plugins ) ) { ?>
					<a class="delete" href="<?php echo attribute_escape(
	bb_nonce_url(
		bb_get_uri(
			'bb-admin/admin-base.php',
			array(
				'mod_helper' => urlencode( $plugin ),
				'action' => 'uninstall',
				'plugin' => 'bbpress_moderation_suite',
			),
			BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
		),
		'uninstall-plugin_' . $plugin
	)
); ?>"><?php _e( 'Uninstall', 'bbpress-moderation-suite' ); ?></a>
<?php if ( !empty( $plugin_data['panel'] ) ) { ?>
				<a href="<?php echo attribute_escape(
	bb_get_uri(
		'bb-admin/admin-base.php',
		array( 'plugin' => $plugin_data['panel'] ),
		BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
	)
);
?>"><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></a>
<?php }
} } else { ?>
<?php if ( !empty( $plugin_data['panel'] ) ) { ?>
				<a href="<?php echo attribute_escape(
	bb_get_uri(
		'bb-admin/admin-base.php',
		array( 'plugin' => $plugin_data['panel'] ),
		BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
	)
);
?>"><?php _e( 'View', 'bbpress-moderation-suite' ); ?></a>
<?php } ?></span></div>
			</td>
<?php } ?>
			<td class="plugin-description">
				<p><?php echo $plugin_data['description']; ?></p>
			</td>
		</tr>
<?php
}
?>

	</tbody>
</table>
<?php
}

function bbmodsuite_stripslashes( $string ) {
	if ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() )
		return stripslashes( $string );
	return $string;
}
