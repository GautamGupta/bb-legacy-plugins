<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_modlog_install() {
	global $bbdb;

	$bbdb->query( 'CREATE TABLE IF NOT EXISTS `' . $bbdb->prefix . 'bbmodsuite_modlog` (
	`ID` int(10) NOT NULL auto_increment,
	`log_user` int(10) NOT NULL,
	`log_level` varchar(3) NOT NULL default \'mod\',
	`log_time` int(10) NOT NULL,
	`log_content` text NOT NULL default \'\',
	PRIMARY KEY (`ID`)
)' );
}

function bbmodsuite_modlog_uninstall() {
	global $bbdb;

	$bbdb->query( 'DROP TABLE `' . $bbdb->prefix . 'bbmodsuite_modlog`' );
}

function bbpress_moderation_suite_modlog() {
	global $bbdb;
	
	$page = isset( $_GET['page'] ) ? (int)$_GET['page'] - 1 : 0;
	$log_entries = $bbdb->get_results( 'SELECT * FROM `' . $bbdb->prefix . 'bbmodsuite_modlog` LIMIT ' . ($page * 60) . ',' . ($page * 60 + 60) );
	$log_pages = ceil( $bbdb->get_var( 'SELECT COUNT(*) FROM `' . $bbdb->prefix . 'bbmodsuite_modlog`' ) / 60 );

?>
<h2><?php _e( 'Moderation Log', 'bbpress-moderation-suite' ); if ( $page > 0 ) printf( __( ' - Page %d', 'bbpress-moderation-suite' ) , $page + 1 ); ?></h2>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e( 'Date', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'User', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Content', 'bbpress-moderation-suite' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
if ( !$log_entries ) {
?>
		<tr><td colspan="3">No results found.</td></tr>
<?php
} else {
	foreach ( $log_entries as $log_entry ) {
?>
		<tr>
			<td><?php echo date( 'Y-m-d H:i:s', $log_entry->log_time ); ?></td>
			<td><a href="<?php user_profile_link( $log_entry->log_user ); ?>"><?php echo get_user_display_name( $log_entry->log_user ); ?></a> <small>(<?php echo $log_entry->log_level; ?>)</small></td>
			<td><?php echo attribute_escape( $log_entry->log_content ); ?></td>
		</tr>

<?php
	}
}
?>
	</tbody>
</table>
<?php
	paginate_links( array(
		'base'    => bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_modlog' ), BB_URI_CONTEXT_BB_ADMIN ) . '%_%',
		'format'  => '&page=%#%',
		'total'   => $log_pages,
		'current' => $page
	) );
}

function bbmodsuite_modlog_admin_add() {
	global $bb_submenu;
	$bb_submenu['users.php'][] = array( __( 'Moderation Log', 'bbpress-moderation-suite' ), 'administrate', 'bbpress_moderation_suite_modlog' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_modlog_admin_add' );

function bbmodsuite_modlog_log( $content ) {
	if ( empty( $content ) )
		return;

	global $bbdb;

	$bbdb->insert( $bbdb->prefix . 'bbmodsuite_modlog', array(
		'log_user' => bb_get_current_user_info( 'ID' ),
		'log_level' => strtolower( substr( get_user_type( bb_get_current_user_info( 'ID' ) ), 0, 3 ) ),
		'log_time' => time(),
		'log_content' => $content
	), array( '%d', '%s', '%d', '%s' ) );
}

function bbmodsuite_modlog_set_action_handler( $action, $content ) {
	add_action( $action, create_function( '', '
		$args = func_get_args();
		bbmodsuite_modlog_log( vsprintf( \'' . addslashes($content) . '\', $args ) );
	' ), 10, substr_count( $content, '%' ) );
}

// Everything from here on is a trigger for the logging function

bbmodsuite_modlog_set_action_handler( 'bbmodsuite-install', 'activated the bbPress Moderation Suite %s plugin' );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-deactivate', 'deactivated the bbPress Moderation Suite %s plugin' );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-uninstall', 'uninstalled the bbPress Moderation Suite %s plugin' );

function bbmodsuite_modlog_check_meta_change( $tuple ) {
	if ( $tuple['type'] != 'option' )
		return $tuple;

	switch ( $tuple['meta_key'] ) {
		case 'active_plugins':
			$action = array();
			$old_plugins = bb_get_option_from_db( 'active_plugins' );

			$activated = array_diff( $tuple['meta_value'], $old_plugins );
			$deactivated = array_diff( $old_plugins, $tuple['meta_value'] );

			if ( $activated ) {
				$action['activated'] = 'activated plugins: ';
				$first = true;
				foreach ( $activated as $_p ) {
					if ( !$first )
						$action['activated'] .= ', ';
					$p = bb_get_plugin_data( $_p );
					$action['activated'] .= $p['name'];
					$first = false;
				}
			}

			if ( $deactivated ) {
				$action['deactivated'] = 'deactivated plugins: ';
				$first = true;
				foreach ( $deactivated as $_p ) {
					if ( !$first )
						$action['deactivated'] .= ', ';
					$p = bb_get_plugin_data( $_p );
					$action['deactivated'] .= $p['name'];
					$first = false;
				}
			}

			bbmodsuite_modlog_log( implode( ' and ', $action ) );

			break;
	}

	return $tuple;
}
add_filter( 'bb_update_meta', 'bbmodsuite_modlog_check_meta_change' );

function bbmodsuite_modlog_check_query( $query ) {
	global $bbdb;
	if ( strpos( $query, "DELETE FROM {$bbdb->forums} WHERE forum_id = " ) !== false ) {
		$forum = get_forum( (int)substr( $query, strlen( "DELETE FROM $bbdb->forums WHERE forum_id = " ) ) );

		bbmodsuite_modlog_log( 'deleted forum: ' . $forum->name );
	}

	return $query;
}
add_filter( 'query', 'bbmodsuite_modlog_check_query' );

?>