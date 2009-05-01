<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_modlog_install() {
	global $bbdb;

	$bbdb->query(
					'CREATE TABLE IF NOT EXISTS `' . $bbdb->prefix . 'bbmodsuite_modlog` (
						`ID` int(10) NOT NULL auto_increment,
						`log_user` int(10) NOT NULL,
						`log_level` varchar(3) NOT NULL default \'mod\',
						`log_time` int(10) NOT NULL,
						`log_content` text NOT NULL default \'\',
						PRIMARY KEY (`ID`)
					)'
	);
}

function bbmodsuite_modlog_uninstall() {
	global $bbdb;

	$bbdb->query( 'DROP TABLE `' . $bbdb->prefix . 'bbmodsuite_modlog`' );
}

function bbpress_moderation_suite_modlog() {
	global $bbdb;

	$page        = isset( $_GET['page'] ) ? (int)$_GET['page'] - 1 : 0;
	$log_entries = $bbdb->get_results( 'SELECT * FROM `' . $bbdb->prefix . 'bbmodsuite_modlog` ORDER BY `log_time` DESC LIMIT ' . ($page * 60) . ',' . ($page * 60 + 60) );
	$log_pages   = ceil( $bbdb->get_var( 'SELECT COUNT(*) FROM `' . $bbdb->prefix . 'bbmodsuite_modlog`' ) / 60 );

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
			<td><?php echo $log_entry->log_content; ?></td>
		</tr>

<?php
	}
}
?>
	</tbody>
</table>
<?php
	paginate_links(
					array(
						'base'    => bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_modlog' ), BB_URI_CONTEXT_BB_ADMIN ) . '%_%',
						'format'  => '&page=%#%',
						'total'   => $log_pages,
						'current' => $page,
					)
	);
}

function bbmodsuite_modlog_admin_add() {
	global $bb_submenu;
	$bb_submenu['plugins.php'][] = array( __( 'Moderation Log', 'bbpress-moderation-suite' ), 'administrate', 'bbpress_moderation_suite_modlog' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_modlog_admin_add' );

function bbmodsuite_modlog_log( $content ) {
	if ( empty( $content ) )
		return;

	global $bbdb;

	$bbdb->insert(
					$bbdb->prefix . 'bbmodsuite_modlog', array(
						'log_user'    => bb_get_current_user_info( 'ID' ),
						'log_level'   => strtolower( substr( get_user_type( bb_get_current_user_info( 'ID' ) ), 0, 3 ) ),
						'log_time'    => time(),
						'log_content' => $content,
					), array( '%d', '%s', '%d', '%s' )
	);
}

function bbmodsuite_modlog_set_action_handler( $action, $content ) {
	add_action( $action, create_function( '', '$args = func_get_args(); bbmodsuite_modlog_log( vsprintf( \'' . addslashes( $content ) . '\', $args ) );' ), 10, substr_count( $content, '%' ) );
}

// Everything from here on is a trigger for the logging function

bbmodsuite_modlog_set_action_handler( 'bbmodsuite-install', __( 'activated the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ) );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-deactivate', __( 'activated the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ) );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-uninstall', __( 'activated the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ) );

function bbmodsuite_modlog_check_meta_change( $tuple ) {
	if ( $tuple['type'] != 'option' )
		return $tuple;

	switch ( $tuple['meta_key'] ) {
		case 'active_plugins':
			$action      = array();
			$old_plugins = bb_get_option_from_db( 'active_plugins' );
			$activated   = array_diff( $tuple['meta_value'], $old_plugins );
			$deactivated = array_diff( $old_plugins, $tuple['meta_value'] );

			if ( $activated ) {
				$action['activated'] = __( 'activated plugins: ', 'bbpress-moderation-suite' );
				$first = true;
				foreach ( $activated as $_p ) {
					if ( !$first )
						$action['activated'] .= ', ';
					$p                     = bb_get_plugin_data( $_p );
					$action['activated']  .= $p['name'];
					$first                 = false;
				}
			}

			if ( $deactivated ) {
				$action['deactivated'] = __( 'deactivated plugins: ', 'bbpress-moderation-suite' );
				$first = true;
				foreach ( $deactivated as $_p ) {
					if ( !$first )
						$action['deactivated'] .= ', ';
					$p                       = bb_get_plugin_data( $_p );
					$action['deactivated']  .= $p['name'];
					$first                   = false;
				}
			}

			bbmodsuite_modlog_log( implode( __( ' and ', 'bbpress-moderation-suite' ), $action ) );

			break;
	}

	return $tuple;
}
add_filter( 'bb_update_meta', 'bbmodsuite_modlog_check_meta_change' );

function bbmodsuite_modlog_check_query( $query ) {
	global $bbdb;
	if ( strpos( $query, "DELETE FROM {$bbdb->forums} WHERE forum_id = " ) !== false ) {
		$forum = get_forum( (int)substr( $query, strlen( "DELETE FROM {$bbdb->forums} WHERE forum_id = " ) ) );

		bbmodsuite_modlog_log( __( 'deleted forum: ', 'bbpress-moderation-suite' ) . $forum->forum_name );
	}

	return $query;
}
add_filter( 'query', 'bbmodsuite_modlog_check_query' );

function bbmodsuite_modlog_check_post_edit( $post_text, $post_id, $topic_id ) {
	if (!$post_id) // New posts are not important.
		return $post_text;

	$post = bb_get_post( $post_id );
	$current_id = bb_get_current_user_info( 'ID' );

	if ( $current_id == $post->poster_id ) // Editing your own post? *yawn*
		return $post_text;

	if ( $post_text == $post->post_text ) // This is not the hook we are looking for.
		return $post_text;

	bbmodsuite_modlog_log( sprintf( __( 'edited %s\'s post on the topic "%s".', 'bbpress-moderation-suite' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $topic_id ) . '</a>' ) );

	return $post_text;
}
add_filter( 'pre_post', 'bbmodsuite_modlog_check_post_edit', 10, 3 );

function bbmodsuite_modlog_check_post_delete( $post_id, $new_status, $old_status ) {
	$post = bb_get_post( $post_id );

	if ( $old_status == 0 ) {
		if ( $new_status == 1 ) {
			bbmodsuite_modlog_log( sprintf( __( 'deleted %s\'s post on the topic "%s".' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
		} elseif ( $new_status == 2 ) {
			bbmodsuite_modlog_log( sprintf( __( 'marked %s\'s post on the topic "%s" as spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
		}
	} elseif ( $new_status == 0 ) {
		if ( $old_status == 1 ) {
			bbmodsuite_modlog_log( sprintf( __( 'undeleted %s\'s post on the topic "%s".' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
		} elseif ( $old_status == 2 ) {
			bbmodsuite_modlog_log( sprintf( __( 'marked %s\'s post on the topic "%s" as not spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
		}
	} elseif ( $new_status == 2 && $old_status == 1 ) {
		bbmodsuite_modlog_log( sprintf( __( 'changed %s\'s post on the topic "%s" from deleted to spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
	} elseif ( $new_status == 1 && $old_status == 2 ) {
		bbmodsuite_modlog_log( sprintf( __( 'changed %s\'s post on the topic "%s" from spam to deleted.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ) );
	}
}
add_action( 'bb_delete_post', 'bbmodsuite_modlog_check_post_delete', 10, 3 );

function bbmodsuite_modlog_check_topic_delete( $topic_id, $new_status, $old_status ) {
	if ( $old_status == 0 && $new_status == 1 ) {
		bbmodsuite_modlog_log( sprintf( __( 'deleted topic "%s".' ), '<a href="' . get_topic_link( $topic_id ) . '?view=all">' . get_topic_title( $topic_id ) . '</a>' ) );
	} elseif ( $old_status == 1 && $new_status == 0 ) {
		bbmodsuite_modlog_log( sprintf( __( 'undeleted topic "%s".' ), '<a href="' . get_topic_link( $topic_id ) . '">' . get_topic_title( $topic_id ) . '</a>' ) );
	}
}
add_action( 'bb_delete_topic', 'bbmodsuite_modlog_check_topic_delete', 10, 3 );

function bbmodsuite_modlog_check_bozo( $ret, $key, $new ) {
	if ( !in_array( $key, array( 'is_bozo' ) ) )
		return $ret;

	global $user_id;
	$link = '<a href="' . get_user_profile_link( $user_id ) . '">' . get_user_display_name( $user_id ) . '</a>';
	$old  = bb_get_usermeta( $user_id, $key );

	switch ( $key ) {
		case 'is_bozo':
			if ( $new && !$old )
				bbmodsuite_modlog_log( sprintf( __( 'marked %s as a bozo.', 'bbpress-moderation-suite' ), $link ) );
			elseif ( !$new && $old )
				bbmodsuite_modlog_log( sprintf( __( 'unmarked %s as a bozo.', 'bbpress-moderation-suite' ), $link ) );
			break;
	}

	return $ret;
}
add_filter( 'sanitize_profile_admin', 'bbmodsuite_modlog_check_bozo', 10, 3 );

function bbmodsuite_modlog_check_user_delete( $user_id ) {
	bbmodsuite_modlog_log( sprintf( __( 'deleted %s.', 'bbpress-moderation-suite' ), get_user_display_name( $user_id ) ) );
}
add_action( 'bb_delete_user', 'bbmodsuite_modlog_check_user_delete' );


function bbmodsuite_modlog_set_topic_action_handler( $action, $content, $viewall = false ) {
	add_action( $action, create_function( '$a', '$a = \'<a href="\' . get_topic_link( $a ) . \'' . ( $viewall ? '?view=all' : '' ) . '">\' . get_topic_title( $a ) . \'</a>\'; bbmodsuite_modlog_log( sprintf( \'' . addslashes( $content ) . '\', $a ) );' ) );
}

bbmodsuite_modlog_set_topic_action_handler( 'close_topic', __( 'closed topic "%s"', 'bbpress-moderation-suite', true ) );
bbmodsuite_modlog_set_topic_action_handler( 'open_topic', __( 'opened topic "%s"', 'bbpress-moderation-suite' ) );
bbmodsuite_modlog_set_topic_action_handler( 'sticky_topic', __( 'stickied topic "%s"', 'bbpress-moderation-suite' ) );
bbmodsuite_modlog_set_topic_action_handler( 'unsticky_topic', __( 'unstickied topic "%s"', 'bbpress-moderation-suite' ) );

?>