<?php
/*
Plugin Name: User Roles Table for bbPress
Plugin URI: http://bbpress.org/plugins/topic/user-roles-table-for-bbpress/
Description: Stores user roles in a separate roles table for faster reading purposes, helps speed up queries based on roles for sites with lots of users.
Author: Sam Bauers
Version: 1.3
Author URI: http://unlettered.org/
*/



// These are to let other plugins know that the user roles table is in da house
define( 'BB_URT_INSTALLED', true );
if ( bb_urt_get_option( 'active' ) ) {
	define( 'BB_URT_ACTIVE', true );
} else {
	define( 'BB_URT_ACTIVE', false );
}

function bb_urt_get_option( $option )
{
	if ( !$options = bb_get_option( 'bb_urt_options' ) ) {
		return;
	}

	if ( !isset( $options[$option] ) ) {
		return;
	}

	return $options[$option];
}

function bb_urt_add_table()
{
	global $bbdb;

	if ( !isset( $bbdb->tables['userroles'] ) || !isset( $bbdb->userroles ) ) {
		$bbdb->tables['userroles'] = false;
		$bbdb->set_prefix( $bbdb->prefix, array( 'userroles' => false ) );
	}
}

add_action( 'bb_plugins_loaded', 'bb_urt_add_table', 1 );

function bb_urt_upgrade_schema( $schema )
{
	global $bbdb;

	$schema['userroles'] = "CREATE TABLE IF NOT EXISTS `$bbdb->userroles` (
	`user_id` bigint(20) unsigned NOT NULL default 0,
	`role` varchar(32) NOT NULL default '',
	KEY `user_id` (`user_id`),
	KEY `role` (`role`),
	UNIQUE KEY `user_id__role` (`user_id`, `role`)
);";

	return $schema;
}

add_filter( 'bb_schema_pre_charset', 'bb_urt_upgrade_schema' );

function bb_urt_create_table()
{
	global $bbdb;

	require_once( BB_PATH . 'bb-admin/includes/functions.bb-upgrade.php' );
	require_once( BB_PATH . 'bb-admin/includes/defaults.bb-schema.php' );
	require_once( BACKPRESS_PATH . 'class.bp-sql-schema-parser.php' );
	$delta = BP_SQL_Schema_Parser::delta( $bbdb, $bb_queries );

	if ( is_array( $delta ) ) {
		$log = $delta;
	} else {
		$log = array( 'messages' => array(), 'errors' => array() );
	}

	$log['messages'] = array_filter( $log['messages'] );
	$log['errors'] = array_filter( $log['errors'] );

	return $log;
}

function bb_urt_truncate_table()
{
	global $bbdb;
	$bbdb->query( "TRUNCATE TABLE $bbdb->userroles" );

	return 'User Roles table truncated';
}

function bb_urt_drop_table()
{
	global $bbdb;
	$bbdb->query( "DROP TABLE $bbdb->userroles" );

	return 'User Roles table dropped';
}

function bb_urt_populate_table()
{
	global $bbdb;
	global $wp_roles;

	$offset = 0;

	while ( $users = $bbdb->get_results( "SELECT `umeta_id`, `user_id`, `meta_value` FROM `{$bbdb->usermeta}` WHERE `meta_key` = '{$bbdb->prefix}capabilities' ORDER BY `user_id` ASC LIMIT {$offset},1000;" ) ) {

		foreach ( $users as $user ) {
			// find any usermeta capabilites that are roles, and insert them as userroles rows
			$caps = unserialize( $user->meta_value );

			foreach ( $caps as $cap => $active ) {
				if ( $wp_roles->is_role( $cap ) ) {
					if ( $active ) {
						$role = strtolower($cap);
						$bbdb->insert( $bbdb->userroles, array( 'user_id' => $user->user_id, 'role' => $role ) );
					}
				}
			}
		}

		$offset += 1000;
		$bbdb->queries = array();
	}

	return 'User Roles copied to userroles table';
}

function bb_urt_install( $bb_init = false )
{
	global $bbdb;
	$log = array();

	if ( !$bb_init ) {
		bb_urt_add_table();
	}

	$bbdb->suppress_errors();
	$test = $bbdb->get_results( "DESCRIBE `" . $bbdb->userroles . "`;" );
	$bbdb->suppress_errors(false);

	if ( !$test || ( is_array( $test ) && !count( $test ) ) ) {
		$log = bb_urt_create_table();
	}

	return $log;
}

bb_register_plugin_activation_hook( __FILE__, 'bb_urt_install' );



function bb_urt_get_ids_by_role( $tuple )
{
	if ( !bb_urt_get_option( 'active' ) ) {
		return $tuple;
	}

	global $bbdb;

	// $tuple values are already sanitised
	extract( $tuple );

	if ( is_array( $role ) ) {
		$_and_where = "`role` IN ('" . join( "','", $role ) . "')";
	} else {
		$_and_where = "`role` = '" . $role . "'";
	}

	if ( $ids = (array) $bbdb->get_col( "SELECT user_id FROM $bbdb->userroles WHERE $_and_where ORDER BY user_id $sort LIMIT $limit" ) ) {
		$tuple['ids'] = $ids;
	}

	return $tuple;
}

add_filter( 'bb_get_ids_by_role', 'bb_urt_get_ids_by_role' );

function bb_urt_update_role_to_table( $tuple )
{
	if ( !bb_urt_get_option( 'active' ) ) {
		return;
	}

	global $bbdb;
	global $wp_roles;

	if ( 'usermeta' === $tuple['meta_table'] && $bbdb->prefix . 'capabilities' === $tuple['meta_key'] ) {
		$prepared_query = $bbdb->prepare(
			'DELETE FROM `' . $bbdb->userroles . '` WHERE `user_id` = %d;',
			(int) $tuple['id']
		);
		$bbdb->query( $prepared_query );
		foreach ( $tuple['meta_value'] as $role => $switch ) {
			if ( $switch && $wp_roles->is_role( $role ) ) {
				$prepared_query = $bbdb->prepare(
					'INSERT INTO `' . $bbdb->userroles . '` VALUES (%d, %s);',
					(int) $tuple['id'],
					(string) $role
				);
				$bbdb->query( $prepared_query );
			}
		}
	}

	return $tuple;
}

add_filter( 'WP_Users::update_meta', 'bb_urt_update_role_to_table' );

function bb_urt_delete_role_from_table( $tuple )
{
	if ( !bb_urt_get_option( 'active' ) ) {
		return;
	}

	global $bbdb;
	global $wp_roles;

	if ( 'usermeta' === $tuple['meta_table'] && $bbdb->prefix . 'capabilities' === $tuple['meta_key'] ) {
		if ( !$tuple['meta_value'] ) {
			$prepared_query = $bbdb->prepare(
				'DELETE FROM `' . $bbdb->userroles . '` WHERE `user_id` = %d;',
				(int) $tuple['id']
			);
			$bbdb->query( $prepared_query );
		}
		foreach ( $tuple['meta_value'] as $role => $switch ) {
			if ( $switch && $wp_roles->is_role( $role ) ) {
				$prepared_query = $bbdb->prepare(
					'DELETE FROM `' . $bbdb->userroles . '` WHERE `user_id` = %d AND `role` = %s;',
					(int) $tuple['id'],
					(string) $role
				);
				$bbdb->query( $prepared_query );
			}
		}
	}

	return $tuple;
}

add_action( 'WP_Users::delete_meta', 'bb_urt_delete_role_from_table' );



if ( !BB_IS_ADMIN ) {
	return;
}

// Load the gettext textdomain
load_plugin_textdomain( 'user-roles-table-for-bbpress', dirname( __FILE__ ) . '/languages' );

// Define these functions so that the potentially slow ones don't get run on the admin dashboard
if ( !function_exists( 'get_total_users' ) ) {
	function get_total_users()
	{
		return bb_urt_get_total_users();
	}
}

function bb_urt_get_total_users()
{
	global $bbdb;
	$total = $bbdb->get_var('SELECT COUNT(DISTINCT(`user_id`)) FROM `' . $bbdb->userroles . '`;');
	return $total;
}

add_filter( 'bb_get_total_users', 'bb_urt_get_total_users' );

if ( !function_exists( 'get_recent_registrants' ) ) {
	function get_recent_registrants( $num = 0 )
	{
		return false;
	}
}

// Add filters for the admin area
add_action( 'bb_admin_menu_generator', 'bb_urt_admin_page_add' );
add_action( 'bb_admin-header.php', 'bb_urt_admin_page_process' );

function bb_urt_admin_page_add()
{
	bb_admin_add_submenu( __( 'User Roles Table', 'user-roles-table-for-bbpress' ), 'use_keys', 'bb_urt_admin_page', 'options-general.php' );
}

function bb_urt_admin_page()
{
?>

<h2><?php _e( 'User Roles Table', 'user-roles-table-for-bbpress' ); ?></h2>

<?php _e( '<p>The <em>User Roles Table</em> plugin provides a faster way to look up user roles for certain functions using a dedicated roles table.</p><p>The table is created on plugin activation if it doesn\'t already exist, but the initial roles need to be copied to it manually here.', 'user-roles-table-for-bbpress' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array('plugin' => 'bb_urt_admin_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<?php
	if ( bb_urt_get_option( 'active' ) ) {
?>
	<fieldset>
		<legend><?php _e( 'Stop', 'user-roles-table-for-bbpress' ); ?></legend>
		<p><?php _e( 'This action will stop bbPress from referring to the user roles table to obtain user role information.', 'user-roles-table-for-bbpress' ); ?></p>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'user-roles-table-stop' ); ?>
		<input class="submit" type="submit" name="stop" value="<?php _e( 'Stop User Roles Table', 'user-roles-table-for-bbpress' ) ?>" />
	</fieldset>
<?php
	} else {
?>
	<fieldset>
		<legend><?php _e( 'Start', 'user-roles-table-for-bbpress' ); ?></legend>
		<p><?php _e( 'This action will create the user roles table if it doesn\'t exist and then synchronise it with existing roles data from the usermeta table.', 'user-roles-table-for-bbpress' ); ?></p>
		<p><?php _e( 'WARNING!: This action could take a long time on a site with lots of users.', 'user-roles-table-for-bbpress' ); ?></p>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'user-roles-table-start' ); ?>
		<input class="submit" type="submit" name="start" value="<?php _e( 'Start User Roles Table', 'user-roles-table-for-bbpress' ) ?>" />
	</fieldset>
<?php
	}
?>
</form>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array('plugin' => 'bb_urt_admin_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<legend><?php _e( 'Repair', 'user-roles-table-for-bbpress' ); ?></legend>
		<p><?php _e( 'This action will synchronise the roles stored in the standard usermeta location with the user roles table.', 'user-roles-table-for-bbpress' ); ?></p>
		<p><?php _e( 'WARNING!: This action could take a long time on a site with lots of users.', 'user-roles-table-for-bbpress' ); ?></p>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'user-roles-table-repair' ); ?>
		<input class="submit" type="submit" name="repair" value="<?php _e( 'Repair User Roles Table', 'user-roles-table-for-bbpress' ) ?>" />
	</fieldset>
</form>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array('plugin' => 'bb_urt_admin_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<legend><?php _e( 'Rebuild', 'user-roles-table-for-bbpress' ); ?></legend>
		<p><?php _e( 'This action will recreate the user roles table from scratch and then populate it with existing roles data from the usermeta table.', 'user-roles-table-for-bbpress' ); ?></p>
		<p><?php _e( 'WARNING!: This action could take a long time on a site with lots of users.', 'user-roles-table-for-bbpress' ); ?></p>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'user-roles-table-rebuild' ); ?>
		<input class="submit" type="submit" name="rebuild" value="<?php _e( 'Rebuild User Roles Table', 'user-roles-table-for-bbpress' ) ?>" />
	</fieldset>
</form>

<?php
}

function bb_urt_remove_query_args( $url )
{
	$url = remove_query_arg( 'started', $url );
	$url = remove_query_arg( 'stopped', $url );
	$url = remove_query_arg( 'repaired', $url );
	$url = remove_query_arg( 'rebuilt', $url );
	return $url;
}

function bb_urt_admin_page_process()
{
	if ( !empty( $_GET['started'] ) ) {
		bb_admin_notice( __( 'User Roles Table has been started.', 'user-roles-table-for-bbpress' ) );
	} elseif ( !empty( $_GET['stopped'] ) ) {
		bb_admin_notice( __( 'User Roles Table has been stopped.', 'user-roles-table-for-bbpress' ) );
	} elseif ( !empty( $_GET['repaired'] ) ) {
		bb_admin_notice( __( 'User Roles Table has been repaired.', 'user-roles-table-for-bbpress' ) );
	} elseif ( !empty( $_GET['rebuilt'] ) ) {
		bb_admin_notice( __( 'User Roles Table has been rebuilt.', 'user-roles-table-for-bbpress' ) );
	}

	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) ) {
		if ( isset( $_POST['start'] ) ) {
			bb_check_admin_referer( 'user-roles-table-start' );

			bb_urt_install( true );
			bb_urt_populate_table();

			bb_update_option( 'bb_urt_options', array( 'active' => true ) );

			$goback = add_query_arg( 'started', 'true', bb_urt_remove_query_args( wp_get_referer() ) );
			bb_safe_redirect( $goback );
		} elseif ( isset( $_POST['stop'] ) ) {
			bb_check_admin_referer( 'user-roles-table-stop' );

			bb_update_option( 'bb_urt_options', array( 'active' => false ) );

			$goback = add_query_arg( 'stopped', 'true', bb_urt_remove_query_args( wp_get_referer() ) );
			bb_safe_redirect( $goback );
		} elseif ( isset( $_POST['repair'] ) ) {
			bb_check_admin_referer( 'user-roles-table-repair' );

			bb_urt_truncate_table();
			bb_urt_populate_table();

			$goback = add_query_arg('repaired', 'true', bb_urt_remove_query_args( wp_get_referer() ) );
			bb_safe_redirect( $goback );
		} elseif ( isset( $_POST['rebuild'] ) ) {
			bb_check_admin_referer( 'user-roles-table-rebuild' );

			bb_urt_drop_table();
			bb_urt_install( true );
			bb_urt_populate_table();
			
			$goback = add_query_arg( 'rebuilt', 'true', bb_urt_remove_query_args( wp_get_referer() ) );
			bb_safe_redirect( $goback );
		}
	}
}
?>
