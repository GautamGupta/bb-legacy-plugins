<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

/* $Id$ */

function bbmodsuite_modlog_install() {
	global $bbdb;

	$bbdb->query(
		'CREATE TABLE IF NOT EXISTS `' . $bbdb->prefix . 'bbmodsuite_modlog` (
			`ID` int(10) NOT NULL auto_increment,
			`log_user` int(10) NOT NULL,
			`log_level` varchar(3) NOT NULL default \'mod\',
			`log_time` int(10) NOT NULL,
			`log_content` text NOT NULL default \'\',
			`log_type` varchar(50) NOT NULL,
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
	$log_entries = $bbdb->get_results( 'SELECT * FROM `' . $bbdb->prefix . 'bbmodsuite_modlog` ORDER BY `log_time` DESC LIMIT ' . ($page * 30) . ',' . ($page * 30 + 30) );
	$log_count   = $bbdb->get_var( 'SELECT COUNT(*) FROM `' . $bbdb->prefix . 'bbmodsuite_modlog`' );
	$log_types   = $bbdb->get_col( 'SELECT DISTINCT `log_type` FROM `' . $bbdb->prefix . 'bbmodsuite_modlog`' );

?><h2 style="clear: none;"><?php _e( 'Moderation Log', 'bbpress-moderation-suite' ); if ( $page > 0 ) printf( __( ' - Page %d', 'bbpress-moderation-suite' ) , $page + 1 ); ?></h2>

<select id="modlog-filter" style="display:none">
	<option value="all"><?php _e( 'Show all', 'bbpress-moderation-suite' ); ?></option>
<?php foreach ( $log_types as $log_type ) { ?>
	<option value="<?php echo $log_type; ?>"><?php echo bbmodsuite_modlog_get_type_description( $log_type ); ?></option>
<?php } ?>
</select>

<div class="tablenav top">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php
$_page_link_args = array(
	'page' => $page + 1,
	'total' => $log_count,
	'per_page' => 30,
	'mod_rewrite' => false,
	'prev_text' => __( '&laquo;' ),
	'next_text' => __( '&raquo;' )
);
echo $page_number_links = get_page_number_links( $_page_link_args );
?></span>
		<div class="clear"></div>
	</div>
</div>
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
		<tr class="log-<?php echo md5( strip_tags( $log_entry->log_content ) ); ?> log-type-<?php echo $log_entry->log_type; ?> log-type-all">
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
<div class="tablenav bottom">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php echo $page_number_links; ?></span>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(function($){
	function filterDuplicates() {
		$('.modlog-showmore').remove();
		$('tr').each(function(){
			if ($(this).prevAll('.' + this.className.substr(0, 36)).length)
				return;
			var more = $(this).nextAll('.' + this.className.substr(0, 36));
			if (more.length == 0)
				return;
			more.stop().animate({opacity: 'hide', fontSize: 0}, 1, 'swing', function(){
				$(this).css({opacity: ''});
			});
			$('<a href="#">Show ' + more.length + ' more<\/a>').addClass('alignright modlog-showmore').appendTo($(this).children('td:last').append(' ')).toggle(function(){
				$(this).text('Hide repeats');
				more.animate({opacity: 'show', fontSize: '1em'});
			}, function(){
				$(this).text('Show ' + more.length + ' more');
				more.animate({opacity: 'hide', fontSize: 0});
			});
		});
	}
	$('#modlog-filter').change(function(){
		$('tbody tr').animate({opacity: 'hide', fontSize: 0}).filter('.log-type-' + $(this).val()).stop().animate({opacity: 'show', fontSize: '1em'});
		filterDuplicates();
	}).show();
	filterDuplicates();
});
//]]>
</script>
<?php
}

function bbmodsuite_modlog_admin_add_jquery() {
	wp_enqueue_script( 'jquery' );
}
add_action( 'bbpress_moderation_suite_modlog_pre_head', 'bbmodsuite_modlog_admin_add_jquery' );

function bbmodsuite_modlog_can_view() {
	return 'administrate';
}

function bbmodsuite_modlog_admin_add() {
	bb_admin_add_submenu( __( 'Moderation Log', 'bbpress-moderation-suite' ), bbmodsuite_modlog_can_view(), 'bbpress_moderation_suite_modlog', 'bbpress_moderation_suite' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_modlog_admin_add' );

function bbmodsuite_modlog_log( $content, $type ) {
	if ( empty( $content ) )
		return;

	global $bbdb;

	$bbdb->insert(
		$bbdb->prefix . 'bbmodsuite_modlog', array(
			'log_user'    => bb_get_current_user_info( 'ID' ),
			'log_level'   => strtolower( substr( get_user_type( bb_get_current_user_info( 'ID' ) ), 0, 3 ) ),
			'log_time'    => time(),
			'log_content' => stripslashes( $content ),
			'log_type'    => $type
		), array( '%d', '%s', '%d', '%s', '%s' )
	);
}

function bbmodsuite_modlog_get_type_description( $type ) {
	$types = array(
		'bbmodsuite_activate'   => __( 'Moderation Helper activation', 'bbpress-moderation-suite' ),
		'bbmodsuite_deactivate' => __( 'Moderation Helper deactivation', 'bbpress-moderation-suite' ),
		'bbmodsuite_uninstall'  => __( 'Moderation Helper uninstallation', 'bbpress-moderation-suite' ),

		'plugins' => __( 'Plugin (de)activation', 'bbpress-moderation-suite' ),

		'forum_delete' => __( 'Forum deletion', 'bbpress-moderation-suite' ),

		'post_edit'     => __( 'Post editing', 'bbpress-moderation-suite' ),
		'post_delete'   => __( 'Post deletion', 'bbpress-moderation-suite' ),
		'post_undelete' => __( 'Post undeletion', 'bbpress-moderation-suite' ),
		'post_spam'     => __( 'Post spamming', 'bbpress-moderation-suite' ),
		'post_unspam'   => __( 'Post unspamming', 'bbpress-moderation-suite' ),

		'topic_delete'   => __( 'Topic deletion', 'bbpress-moderation-suite' ),
		'topic_undelete' => __( 'Topic undeletion', 'bbpress-moderation-suite' ),
		'topic_close'    => __( 'Topic closing', 'bbpress-moderation-suite' ),
		'topic_open'     => __( 'Topic opening', 'bbpress-moderation-suite' ),
		'topic_sticky'   => __( 'Topic stickying', 'bbpress-moderation-suite' ),
		'topic_unsticky' => __( 'Topic unstickying', 'bbpress-moderation-suite' ),

		'user_bozo'   => __( 'User bozoing', 'bbpress-moderation-suite' ),
		'user_unbozo' => __( 'User unbozoing', 'bbpress-moderation-suite' ),
		'user_delete' => __( 'User deletion', 'bbpress-moderation-suite' ),

		'banplus' => __( 'Ban Plus', 'bbpress-moderation-suite' )
	);

	if ( isset( $types[$type] ) )
		return $types[$type];

	return apply_filters( 'bbmodsuite_modlog_get_type_description', $type, $type );
}

function bbmodsuite_modlog_set_action_handler( $action, $content, $type ) {
	add_action( $action, create_function( '', '$args = func_get_args(); bbmodsuite_modlog_log( vsprintf( \'' . addslashes( $content ) . '\', $args ), ' . addslashes( $type ) . ' );' ), 10, substr_count( $content, '%' ) );
}

// Everything from here on is a trigger for the logging function.

bbmodsuite_modlog_set_action_handler( 'bbmodsuite-install', __( 'activated the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ), 'bbmodsuite_activate' );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-deactivate', __( 'deactivated the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ), 'bbmodsuite_deactivate' );
bbmodsuite_modlog_set_action_handler( 'bbmodsuite-uninstall', __( 'uninstalled the bbPress Moderation Suite %s plugin', 'bbpress-moderation-suite' ), 'bbmodsuite_uninstall' );

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

			if ( $action['deactivated'] == __( 'deactivated plugins: ', 'bbpress-moderation-suite' ) && empty( $action['activated'] ) )
				return $tuple;

			bbmodsuite_modlog_log( implode( __( ' and ', 'bbpress-moderation-suite' ), $action ), 'plugins' );

			break;
	}

	return $tuple;
}
add_filter( 'bb_update_meta', 'bbmodsuite_modlog_check_meta_change' );

function bbmodsuite_modlog_check_query( $query ) {
	global $bbdb;
	if ( strpos( $query, "DELETE FROM {$bbdb->forums} WHERE forum_id = " ) !== false ) {
		$forum = get_forum( (int)substr( $query, strlen( "DELETE FROM {$bbdb->forums} WHERE forum_id = " ) ) );

		bbmodsuite_modlog_log( __( 'deleted forum: ', 'bbpress-moderation-suite' ) . $forum->forum_name, 'forum_delete' );
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

	bbmodsuite_modlog_log( sprintf( __( 'edited %s\'s post on the topic "%s".', 'bbpress-moderation-suite' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . get_post_link( $post_id ) . '">' . get_topic_title( $topic_id ) . '</a>' ), 'post_edit' );

	return $post_text;
}
add_filter( 'pre_post', 'bbmodsuite_modlog_check_post_edit', 10, 3 );

function bbmodsuite_modlog_check_post_delete( $post_id, $new_status, $old_status ) {
	$post = bb_get_post( $post_id );

	if ( $old_status == 0 ) {
		if ( $new_status == 1 ) {
			bbmodsuite_modlog_log( sprintf( __( 'deleted %s\'s post on the topic "%s".' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_delete' );
		} elseif ( $new_status == 2 ) {
			bbmodsuite_modlog_log( sprintf( __( 'marked %s\'s post on the topic "%s" as spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_spam' );
		}
	} elseif ( $new_status == 0 ) {
		if ( $old_status == 1 ) {
			bbmodsuite_modlog_log( sprintf( __( 'undeleted %s\'s post on the topic "%s".' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_undelete' );
		} elseif ( $old_status == 2 ) {
			bbmodsuite_modlog_log( sprintf( __( 'marked %s\'s post on the topic "%s" as not spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_unspam' );
		}
	} elseif ( $new_status == 2 && $old_status == 1 ) {
		bbmodsuite_modlog_log( sprintf( __( 'changed %s\'s post on the topic "%s" from deleted to spam.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_spam' );
	} elseif ( $new_status == 1 && $old_status == 2 ) {
		bbmodsuite_modlog_log( sprintf( __( 'changed %s\'s post on the topic "%s" from spam to deleted.' ), '<a href="' . get_user_profile_link( $post->poster_id ) . '">' . get_user_display_name( $post->poster_id ) . '</a>', '<a href="' . add_query_arg( 'view', 'all', get_post_link( $post_id ) ) . '">' . get_topic_title( $post->topic_id ) . '</a>' ), 'post_delete' );
	}
}
add_action( 'bb_delete_post', 'bbmodsuite_modlog_check_post_delete', 10, 3 );

function bbmodsuite_modlog_check_topic_delete( $topic_id, $new_status, $old_status ) {
	if ( $old_status == 0 && $new_status == 1 ) {
		bbmodsuite_modlog_log( sprintf( __( 'deleted topic "%s".' ), '<a href="' . add_query_arg( 'view', 'all', get_topic_link( $topic_id ) ) . '">' . get_topic_title( $topic_id ) . '</a>' ), 'topic_delete' );
	} elseif ( $old_status == 1 && $new_status == 0 ) {
		bbmodsuite_modlog_log( sprintf( __( 'undeleted topic "%s".' ), '<a href="' . add_query_arg( 'view', 'all', get_topic_link( $topic_id ) ) . '">' . get_topic_title( $topic_id ) . '</a>' ), 'topic_undelete' );
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
				bbmodsuite_modlog_log( sprintf( __( 'marked %s as a bozo.', 'bbpress-moderation-suite' ), $link ), 'user_bozo' );
			elseif ( !$new && $old )
				bbmodsuite_modlog_log( sprintf( __( 'unmarked %s as a bozo.', 'bbpress-moderation-suite' ), $link ), 'user_unbozo' );
			break;
	}

	return $ret;
}
add_filter( 'sanitize_profile_admin', 'bbmodsuite_modlog_check_bozo', 10, 3 );

function bbmodsuite_modlog_check_user_delete( $user_id ) {
	bbmodsuite_modlog_log( sprintf( __( 'deleted %s.', 'bbpress-moderation-suite' ), get_user_display_name( $user_id ) ), 'user_delete' );
}
add_action( 'bb_delete_user', 'bbmodsuite_modlog_check_user_delete' );

function bbmodsuite_modlog_check_banplus( $user_id, $ban ) {
	bbmodsuite_modlog_log( sprintf( __( 'banned %s for %s. Notes: %s', 'bbpress-moderation-suite' ), strpos( $user_id, 'ip_' ) === false ? get_user_display_name( $user_id ) : ( '<em>' . substr( $user_id, 3 ) . '</em>' ), bb_since( time() - $ban['length'] ), $ban['notes'] ), 'banplus' );
}
add_action( 'bbmodsuite_banplus_ban', 'bbmodsuite_modlog_check_banplus', 10, 2 );

function bbmodsuite_modlog_check_banplus_unban( $user_id, $ban ) {
	bbmodsuite_modlog_log( sprintf( __( 'unbanned %s %s early.', 'bbpress-moderation-suite' ), strpos( $user_id, 'ip_' ) === false ? get_user_display_name( $user_id ) : ( '<em>' . substr( $user_id, 3 ) . '</em>' ), bb_since( time() * 2 - $ban['until'] ) ), 'banplus' );
}
add_action( 'bbmodsuite_banplus_unban', 'bbmodsuite_modlog_check_banplus_unban', 10, 2 );

function bbmodsuite_modlog_set_topic_action_handler( $action, $content, $type ) {
	add_action( $action, create_function( '$a', '$a = \'<a href="\' . get_topic_link( $a ) . \'">\' . get_topic_title( $a ) . \'</a>\'; bbmodsuite_modlog_log( sprintf( \'' . addslashes( $content ) . '\', $a ), ' . addslashes( $type ) . ' );' ) );
}

bbmodsuite_modlog_set_topic_action_handler( 'close_topic', __( 'closed topic "%s"', 'bbpress-moderation-suite'), 'topic_close' );
bbmodsuite_modlog_set_topic_action_handler( 'open_topic', __( 'opened topic "%s"', 'bbpress-moderation-suite'), 'topic_open' );
bbmodsuite_modlog_set_topic_action_handler( 'sticky_topic', __( 'stickied topic "%s"', 'bbpress-moderation-suite' ), 'topic_sticky' );
bbmodsuite_modlog_set_topic_action_handler( 'unsticky_topic', __( 'unstickied topic "%s"', 'bbpress-moderation-suite' ), 'topic_unsticky' );
