<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

/* $Id$ */

function bbmodsuite_warning_install() {
	if ( !$options = bb_get_option( 'bbmodsuite_warning_options' ) ) {
		bb_update_option( 'bbmodsuite_warning_options', array( 'types' => array(), 'min_level' => 'moderate', 'cron_every' => 604800, 'expire_time' => 7776000, 'ban' => array(), 'version' => '0.1-beta2' ) );
		return;
	}
	$change = false;
	if ( !isset( $options['ban'] ) ) {
		$options['ban'] = array();
		$change = true;
	}
	if ( empty( $options['version'] ) ) {
		global $bbdb;

		$all_warnings = $bbdb->get_results( 'SELECT `user_id`, `meta_value` FROM `' . $bbdb->usermeta . '` WHERE `meta_key` = \'bbmodsuite_warnings\'' );

		$types = array_values( bbmodsuite_warning_types() );

		foreach ( $all_warnings as $warnings ) {
			$user = $warnings->user_id;
			$warnings = unserialize( $warnings->meta_value );

			foreach ( $warnings as &$warning ) {
				$warning['type'] = $types[$warning['type']];
			}

			bb_update_usermeta( $user, 'bbmodsuite_warnings', $warnings );
		}

		$options['version'] = '0.1-beta2';

		$change = true;
	}
	if ( $change )
		bb_update_option( 'bbmodsuite_warning_options', $options );
}

function bbmodsuite_warning_uninstall() {
	global $bbdb;
	$bbdb->query( "DELETE FROM `{$bbdb->usermeta}` WHERE `meta_key`='bbmodsuite_warnings' OR `meta_key`='bbmodsuite_warnings_count'" );
	bb_delete_option( 'bbmodsuite_warning_options' );
}

function bbmodsuite_warning_init() {
	global $bbmodsuite_cache;
	if ( empty( $bbmodsuite_cache['warning'] ) )
		$bbmodsuite_cache['warning'] = bb_get_option( 'bbmodsuite_warning_options' );
}
add_action( 'bbmodsuite_init', 'bbmodsuite_warning_init' );

function bbmodsuite_warning_cron() {
	global $bbdb, $bbmodsuite_cache;
	$options = $bbmodsuite_cache['warning'];
	$the_warnings = $bbdb->get_results( "SELECT `user_id`, `meta_value` FROM `{$bbdb->usermeta}` WHERE `meta_key`='bbmodsuite_warnings'" );
	$now = time();
	$all_warnings = array();
	foreach ( $the_warnings as $warnings ) {
		$all_warnings[$warnings->user_id] = unserialize( $warnings->meta_value );
	}
	foreach ( $all_warnings as $i => $warnings ) {
		foreach ( $warnings as $j => $warning ) {
			if ( $warning['date'] < $now - $options['expire_time'] )
				unset( $warnings[$j] );
		}
		if ( !$warnings || $all_warnings[$i] !== $warnings ) {
			$warnings = array_values( $warnings );
			bbmodsuite_warning_update_user_ban( $i, count( $warnings ), true );
			if ( $warnings ) {
				bb_update_usermeta( $i, 'bbmodsuite_warnings', $warnings );
				bb_update_usermeta( $i, 'bbmodsuite_warnings_count', count( $warnings ) );
			} else {
				bb_delete_usermeta( $i, 'bbmodsuite_warnings' );
				bb_delete_usermeta( $i, 'bbmodsuite_warnings_count' );
			}
		}
	}
	wp_schedule_single_event( time() + $options['cron_every'], 'bbmodsuite_warning_cron' );
}
add_action( 'bbmodsuite_warning_cron', 'bbmodsuite_warning_cron' );

function bbmodsuite_warning_update_user_ban( $user_id, $warning_count, $cron = false ) {
	if ( !$user_id = bb_get_user_id( $user_id ) ) return;
	if ( !function_exists( 'bbmodsuite_banplus_set_ban' ) ) return;
	$ban = 0;
	if ( $warning_count ) {
		global $bbmodsuite_cache;
		$options = $bbmodsuite_cache['warning'];
		foreach ( $options['ban'] as $_ban ) {
			if ( $_ban['at'] <= $warning_count )
				$ban = $_ban;
		}
	}
	if ( $ban['at'] === $warning_count && $cron === false )
		return bbmodsuite_banplus_set_ban( $user_id, 'temp', $ban['length'] * $ban['multiplier'], __( 'Automated ban from Warning moderation helper', 'bbpress-moderation-suite' ) );
	return true;
}

function bbmodsuite_warning_link( $parts, $args ) {
	global $bbmodsuite_cache;
	$options = $bbmodsuite_cache['warning'];
	if ( bb_current_user_can( $options['min_level'] ) ) {
		$post_id = get_post_id();
		$user_id = get_post_author_id( $post_id );
		if ( class_exists( 'BP_User' ) )
			$user = new BP_User( $user_id );
		else
			$user = new WP_User( $user_id );

		if ( $user_id !== bb_get_current_user_info( 'ID' ) && ( bb_current_user_can( 'use_keys' ) || ( !$user->has_cap( 'administrate' ) && bb_current_user_can( 'administrate' ) ) || ( !$user->has_cap( 'moderate' ) && bb_current_user_can( 'moderate' ) ) ) ) {
			$title   = __( 'Give this user a warning.', 'bbpress-moderation-suite' );
			$link    =	attribute_escape(
				bb_nonce_url(
					bb_get_uri(
						'bb-admin/admin-base.php',
						array(
							'page'   => 'warn_user',
							'user'   => $user_id,
							'post'   => $post_id,
							'plugin' => 'bbpress_moderation_suite_warning',
						),
						BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
					),
					'bbmodsuite-warning-warn_' . $user_id . '_' . $post_id
				)
			);
			$parts['warn'] = $args['before_each'] . '<a class="warn-user" title="' . $title . '" href="' . $link . '">' . __( 'Warn', 'bbpress-moderation-suite' ) . '</a>' . $args['after_each'];
		}
	}
	return $parts;
}
add_filter( 'bb_post_admin', 'bbmodsuite_warning_link', 10, 2 );

function bbmodsuite_warning_types() {
	$options = bb_get_option( 'bbmodsuite_warning_options' );
	$types = $options['types'];
	if ( !is_array( $types ) ) {
		$types = explode( "\n", $types );
		$types = array_values( array_filter( $types ) );
		$types = array_combine( array_map( 'md5', $types ), $types );
		$options['types'] = $types;
		bb_update_option( 'bbmodsuite_warning_options', $options );
	}
	$types['d41d8cd98f00b204e9800998ecf8427e'] = __( 'Other', 'bbpress-moderation-suite' );
	return $types;
}

function bbmodsuite_warning_parse_types( $raw ) {
	$parsed = array();

	$raw = array_map( 'trim', explode( "\n", bbmodsuite_stripslashes( $raw ) ) );

	foreach ( $raw as $type ) {
		if ( strlen( $type ) ) {
			$parsed[md5( $type )] = $type;
		}
	}

	return $parsed;
}

function bbmodsuite_warning_admin_add_jquery() {
	wp_enqueue_script( 'jquery' );
}
add_action( 'bbpress_moderation_suite_warning_pre_head', 'bbmodsuite_warning_admin_add_jquery' );

function bbpress_moderation_suite_warning() {
	global $bbdb; ?>
<h2><?php _e( 'Warning', 'bbpress-moderation-suite' ); ?></h2>
<div class="table-filter">
	<a<?php if ( !in_array( $_GET['page'], array( 'warn_user', 'admin' ) ) && !$_GET['user'] ) echo ' class="current"'; ?> href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_warning' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Users with warnings', 'bbpress-moderation-suite' ); ?> <span class="count">(<?php echo bb_number_format_i18n( $bbdb->get_var( "SELECT COUNT(`user_id`) FROM `{$bbdb->usermeta}` WHERE `meta_key` = 'bbmodsuite_warnings_count'" ) ); ?>)</span></a>
	<?php if ( !in_array( $_GET['page'], array( 'warn_user', 'admin' ) ) && $_GET['user'] ) { ?><a class="current" href="#"><?php printf( __( 'Warnings given to user "%s"', 'bbpress-moderation-suite' ), get_user_display_name( $_GET['user'] ) ); ?> <span class="count">(<?php echo bb_number_format_i18n( bb_get_usermeta( $_GET['user'], 'bbmodsuite_warnings_count' ) ); ?>)</span></a><?php } ?>
	<?php if ( $_GET['page'] === 'warn_user' ) { ?>| <a class="current" href="#"><?php _e( 'Warn a user', 'bbpress-moderation-suite' ); ?></a><?php } ?>
	<?php if ( bb_current_user_can( 'use_keys' ) ) { ?>| <a<?php if ( $_GET['page'] === 'admin' ) echo ' class="current"'; ?> href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_warning', 'page' => 'admin' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></a><?php } ?>
</div>
<?php switch ( $_GET['page'] ) {
		case 'warn_user':
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-warning-warn-submit_' . $_GET['user'] . '_' . $_GET['post'] ) ) {
				$options = bb_get_option( 'bbmodsuite_warning_options' );

				$warnings = bb_get_usermeta( $_GET['user'], 'bbmodsuite_warnings' );
				if ( empty( $warnings ) )
					$warnings = array();

				$warn_type = (int)$_POST['warn_type'];
				if ( !in_array( $warn_type, bbmodsuite_warning_types() ) )
					$warn_type = 0;

				$warnings[] = array(
					'from'  => bb_get_current_user_info( 'ID' ),
					'type'  => $warn_type,
					'date'  => time(),
					'notes' => bb_autop( htmlspecialchars( trim( bbmodsuite_stripslashes( $_POST['warn_content'] ) ) ) ),
					'post'  => $_GET['post'],
				);

				bbmodsuite_warning_update_user_ban( $_GET['user'], count( $warnings ) );

				if ( class_exists( 'bbPM' ) && $options['bbpm'] ) {
					global $bbpm;

					$user_id = bb_get_current_user_info( 'ID' );
					bb_set_current_user( $_POST['user'] );

					if ( !$bbpm->send_message( $_GET['user'], __( 'Warning', 'bbpress-moderation-suite' ), __( '<strong>You have been warned. This message was automatically sent with your username.</strong>', 'bbpress-moderation-suite' ) . "\n\n" . esc_html( bbmodsuite_stripslashes( $_POST['warn_content'] ) ) ) )
						bb_mail( bb_get_user_email( $_GET['user'] ), __( 'Warning', 'bbpress-moderation-suite' ), trim( bbmodsuite_stripslashes( $_POST['warn_content'] ) ) );

					bb_set_current_user( $user_id );
				} else
					bb_mail( bb_get_user_email( $_GET['user'] ), __( 'Warning', 'bbpress-moderation-suite' ), trim( bbmodsuite_stripslashes( $_POST['warn_content'] ) ) );

				bb_update_usermeta( $_GET['user'], 'bbmodsuite_warnings', $warnings );
				bb_update_usermeta( $_GET['user'], 'bbmodsuite_warnings_count', count( $warnings ) ); ?>
<div class="updated"><p><?php _e( 'User successfully warned.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			} elseif ( !bb_verify_nonce( $_GET['_wpnonce'], 'bbmodsuite-warning-warn_' . $_GET['user'] . '_' . $_GET['post'] ) ) { ?>
<div class="error"><p><?php _e( 'Invalid warning attempt', 'bbpress-moderation-suite' ); ?></p></div>
<?php
				return;
			}
	$post_query = new BB_Query( 'post', array( 'post_id' => $_GET['post'], 'post_author' => $_GET['user'] ) );
	$GLOBALS['bb_posts'] =& $post_query->results;
	bb_admin_list_posts(); ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'warn_user', 'user' => $_GET['user'], 'post' => $_GET['post'], 'plugin' => 'bbpress_moderation_suite_warning' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="warn_type">
			<?php printf( __( 'Reason for warning %s', 'bbpress-moderation-suite' ), get_user_display_name( $_GET['user'] ) ); ?>
		</label>
		<div>
			<select name="warn_type" id="warn_type" tabindex="1">
<?php foreach ( bbmodsuite_warning_types() as $id => $type ) { ?>
				<option value="<?php echo $id; ?>"<?php if ( $id == 'd41d8cd98f00b204e9800998ecf8427e' ) echo ' selected="selected"'; ?>><?php echo $type; ?></option>
<?php } ?>
			</select>
		</div>
	</div>
	<div>
		<label for="warn_content">
			<?php _e( 'Notes', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<textarea id="warn_content" name="warn_content" rows="15" cols="43"></textarea>
			<p><?php _e( 'This <strong>will</strong> be shown to the user.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-warning-warn-submit_' . $_GET['user'] . '_' . $_GET['post'] ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Warn user', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<?php break;
		case 'admin':
			if ( bb_current_user_can( 'use_keys' ) ) { ?>
<h2><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></h2>
<?php			if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
					$types = bbmodsuite_warning_parse_types( trim( $_POST['warn_types'] ) );
					$min_level  = in_array( $_POST['min_level'], array( 'moderate', 'administrate', 'use_keys' ) ) ? $_POST['min_level'] : 'moderate';
					$cron_every = (int)$_POST['cron_every'];
					if ( $cron_every === 0 )
						$cron_every = 1;
					$cron_every *= 604800;
					$expire_time = (int)$_POST['expire_time'];
					if ( $expire_time === 0 )
						$expire_time = 3;
					$expire_time *= 2592000;
					$ban = array();
					foreach ( $_POST['ban'] as $i => $the_ban ) {
						$the_ban = (int)$the_ban;
						if ( !$the_ban ) continue;
						$ban_at = (int)$_POST['banat'][$i];
						if ( !$ban_at || isset( $ban[$ban_at] ) ) continue;
						$mult = (int)$_POST['banmultiplier'][$i];
						if ( !$mult ) continue;
						$ban[$ban_at] = array( 'at' => $ban_at, 'length' => $the_ban, 'multiplier' => $mult );
					}
					ksort( $ban );
					$ban = array_values( $ban );
					$bbpm = $_POST['bbpm'] == 'bbpm';
					bb_update_option( 'bbmodsuite_warning_options', compact( 'types', 'min_level', 'cron_every', 'expire_time', 'ban', 'bbpm' ) );
					global $bbmodsuite_cache;
					$bbmodsuite_cache['warning'] = compact( 'types', 'min_level', 'cron_every', 'expire_time', 'ban' );
					wp_clear_scheduled_hook( 'bbmodsuite_warning_cron' );
					wp_schedule_single_event( time() + $cron_every, 'bbmodsuite_warning_cron' ); ?>
<div class="updated"><p><?php _e( 'Settings successfully saved.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			}
				$options = bb_get_option( 'bbmodsuite_warning_options' );
?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'admin', 'plugin' => 'bbpress_moderation_suite_warning' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="warn_types">
			<?php _e( 'Possible reasons for warning users', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<textarea id="warn_types" name="warn_types" rows="15" cols="43"><?php echo esc_html( implode( "\n", $options['types'] ) ); ?></textarea>
		</div>
	</div>
	<div>
		<label for="min_level">
			<?php _e( 'Minimum level', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<select id="min_level" name="min_level">
				<option value="moderate"<?php if ( $options['min_level'] === 'moderate' ) echo ' selected="selected"'; ?>><?php _e( 'Moderator' ); ?></option>
				<option value="administrate"<?php if ( $options['min_level'] === 'administrate' ) echo ' selected="selected"'; ?>><?php _e( 'Administrator' ); ?></option>
				<option value="use_keys"<?php if ( $options['min_level'] === 'use_keys' ) echo ' selected="selected"'; ?>><?php _e( 'Key master' ); ?></option>
			</select>
			<p><?php _e( 'What should the minimum user level to warn users be?', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
	<div>
		<label for="cron_every">
			<?php _e( 'Check interval', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="cron_every" name="cron_every" class="text short" type="text" value="<?php echo $options['cron_every'] / 604800 ?>" /> <?php _e( 'weeks', 'bbpress-moderation-suite' ); ?>
			<p><?php _e( 'How long should bbPress Moderation Suite wait between checks for expired warnings?', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
	<div>
		<label for="expire_time">
			<?php _e( 'Expiration time', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="expire_time" name="expire_time" class="text short" type="text" value="<?php echo $options['expire_time'] / 2592000 ?>" /> <?php _e( 'months', 'bbpress-moderation-suite' ); ?>
			<p><?php _e( 'How old should warnings be for bbPress Moderation Suite to delete them?', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
<?php if ( class_exists( 'bbPM' ) ) { ?>
	<div>
		<label>
			<?php _e( 'bbPM Integration', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="bbpm_only" name="bbpm" class="radio" type="radio" value="bbpm"<?php if ( $options['bbpm'] ) echo ' checked="checked"'; ?>/> <?php _e( 'Send a private message', 'bbpress-moderation-suite' ); ?><br/>
			<input id="email_only" name="bbpm" class="radio" type="radio" value="email"<?php if ( !$options['bbpm'] ) echo ' checked="checked"'; ?>/> <?php _e( 'Send an email', 'bbpress-moderation-suite' ); ?>
		</div>
	</div>
<?php } ?>
</fieldset>
<?php global $bbmodsuite_active_plugins; if ( !in_array( 'banplus', $bbmodsuite_active_plugins ) ) { ?>
<div class="updated"><p><?php _e( 'Ban Plus is not active. The banning settings will be saved, but not used.', 'bbpress-moderation-suite' ); ?></p></div>
<?php } ?>
<fieldset id="banning-options">
<?php for ( $i = 0; $i < max( count( $options['ban'] ), 1 ); $i++ ) { ?>
	<div>
		<label for="banat[<?php echo $i ?>]">
			<?php _e( 'Ban automatically after:', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input type="text" class="text short" id="banat[<?php echo $i; ?>]" name="banat[]" value="<?php echo $options['ban'][$i]['at']; ?>" />
			<?php _e( ' warnings, for ', 'bbpress-moderation-suite' ); ?>
			<input type="text" class="text short" id="ban[<?php echo $i; ?>]" name="ban[]" value="<?php echo $options['ban'][$i]['length'] ?>" />
			<select name="banmultiplier[]" id="banmultiplier[<?php echo $i; ?>]">
				<option value="60"<?php if ( $options['ban'][$i]['multiplier'] === 60 ) echo ' selected="selected"'; ?>><?php _e( 'minutes', 'bbpress-moderation-suite' ); ?></option>
				<option value="3600"<?php if ( $options['ban'][$i]['multiplier'] === 3600 ) echo ' selected="selected"'; ?>><?php _e( 'hours', 'bbpress-moderation-suite' ); ?></option>
				<option value="86400"<?php if ( $options['ban'][$i]['multiplier'] === 86400 ) echo ' selected="selected"'; ?>><?php _e( 'days', 'bbpress-moderation-suite' ); ?></option>
				<option value="604800"<?php if ( $options['ban'][$i]['multiplier'] === 604800 ) echo ' selected="selected"'; ?>><?php _e( 'weeks', 'bbpress-moderation-suite' ); ?></option>
				<option value="2592000"<?php if ( $options['ban'][$i]['multiplier'] === 2592000 ) echo ' selected="selected"'; ?>><?php _e( 'months', 'bbpress-moderation-suite' ); ?></option>
			</select>
		</div>
	</div>
<?php } ?>
<noscript>
	<div>
		<label for="banat[<?php echo $i; ?>]">
			<?php _e( 'Ban automatically after:', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input type="text" class="text short" id="banat[<?php echo $i; ?>]" name="banat[]" />
			<?php _e( ' warnings, for ', 'bbpress-moderation-suite' ); ?>
			<input type="text" class="text short" id="ban[<?php echo $i; ?>]" name="ban[]" />
			<select name="banmultiplier[]" id="banmultiplier[<?php echo $i; ?>]">
				<option value="60"><?php _e( 'minutes', 'bbpress-moderation-suite' ); ?></option>
				<option value="3600"><?php _e( 'hours', 'bbpress-moderation-suite' ); ?></option>
				<option value="86400" selected="selected"><?php _e( 'days', 'bbpress-moderation-suite' ); ?></option>
				<option value="604800"><?php _e( 'weeks', 'bbpress-moderation-suite' ); ?></option>
				<option value="2592000"><?php _e( 'months', 'bbpress-moderation-suite' ); ?></option>
			</select>
		</div>
	</div>
</noscript>
	<script type="application/javascript">
	// <![CDATA[
		jQuery(function ($) {
			var currentID = <?php echo $i; ?>;
			function appendBanBox() {
				var id = currentID++;
				$('<div/>').append($('<label/>').attr('for', 'banat[' + id + ']').text('<?php echo addslashes( __( 'Ban automatically after:', 'bbpress-moderation-suite' ) ); ?>')).append($('<div/>').append($('<input type="text"/>').addClass('text short').attr({id: 'banat[' + id + ']', name: 'banat[]'})).append('<?php echo addslashes( __( ' warnings, for ' ) ); ?>').append($('<input type="text"/>').addClass('text short').attr({id: 'ban[' + id + ']', name: 'ban[]'})).append('<select id="banmultiplier[' + id + ']" name="banmultiplier[]"><option value="60"><?php echo addslashes( __( 'minutes', 'bbpress-moderation-suite' ) ); ?></option><option value="3600"><?php echo addslashes( __( 'hours', 'bbpress-moderation-suite' ) ); ?></option><option value="86400" selected="selected"><?php echo addslashes( __( 'days', 'bbpress-moderation-suite' ) ); ?></option><option value="604800"><?php echo addslashes( __( 'weeks', 'bbpress-moderation-suite' ) ); ?></option><option value="2592000"><?php echo addslashes( __( 'months', 'bbpress-moderation-suite' ) ); ?></option></select>')).insertBefore('#banning-options>:last');
			}
			$('<div/>').append($('<input type="button"/>').val('<?php echo addslashes( __( 'Add more', 'bbpress-moderation-suite' ) ); ?>').addClass('submit').click(function(){appendBanBox()})).appendTo('#banning-options');
		})
	// ]]>
	</script>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-warning-admin' ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<?php			break;
			}
		default: if ( empty( $_GET['user'] ) ) {
			global $bbdb;
			$page = max( 1, (int)$_GET['page'] ) - 1; ?>
<div class="tablenav top">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php

$warned_user_count = $bbdb->get_var( "SELECT COUNT(`user_id`) FROM `{$bbdb->usermeta}` WHERE `meta_key` = 'bbmodsuite_warnings_count'" );

$_page_link_args = array(
	'page' => $page + 1,
	'total' => $warned_user_count,
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
			<th><?php _e( 'User', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Warnings', 'bbpress-moderation-suite' ); ?></th>
			<th class="action"><?php _e( 'Actions', 'bbpress-moderation-suite' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php		$warned_users = (array)$bbdb->get_results( "SELECT `meta_value`,`user_id` FROM `{$bbdb->usermeta}` WHERE `meta_key` = 'bbmodsuite_warnings_count' ORDER BY `meta_value` DESC LIMIT " . ($page * 30 ) . ",30" );
			foreach ( $warned_users as $warned_user ) {
				$url = bb_get_uri(
					'bb-admin/admin-base.php',
					array(
						'user' => $warned_user->user_id,
						'plugin' => 'bbpress_moderation_suite_warning',
					),
					BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
				); ?>
		<tr>
			<td><?php echo get_user_display_name( $warned_user->user_id ); ?></td>
			<td><?php echo bb_number_format_i18n( $warned_user->meta_value ); ?></td>
			<td>
				<a href="<?php echo $url; ?>"><?php _e( 'View warnings', 'bbpress-moderation-suite' ); ?></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<div class="tablenav bottom">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php echo $page_number_links; ?></span>
		<div class="clear"></div>
	</div>
</div>
<?php } else {
		$page = max( 1, (int)$_GET['page'] ) - 1; ?>
<div class="tablenav top">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php

$warnings = array_reverse( (array)bb_get_usermeta( $_GET['user'], 'bbmodsuite_warnings' ) );

$_page_link_args = array(
	'page' => $page + 1,
	'total' => count( $warnings ),
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
			<th><?php _e( 'Given by', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Notes', 'bbpress-moderation-suite' ); ?></th>
			<th class="action"><?php _e( 'Actions', 'bbpress-moderation-suite' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php		$types = bbmodsuite_warning_types();
			$warnings = count( $warnings ) < $page * 30 ? array() : array_slice( $warnings, $page * 30, 30 );
			foreach ( $warnings as $warning ) { ?>
		<tr>
			<td><?php echo get_user_display_name( $warning['from'] ); ?></td>
			<td>
				<strong><?php echo $types[$warning['type']]; ?></strong>
				<?php echo $warning['notes']; ?>
			</td>
			<td>
				<a href="<?php post_link( $warning['post'] ); ?>"><?php _e( 'View post', 'bbpress-moderation-suite' ); ?></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<div class="tablenav bottom">
	<div class="tablenav-pages">
		<span class="displaying-pages"><?php echo $page_number_links; ?></span>
		<div class="clear"></div>
	</div>
</div>
<?php	}
	}
}

function bbmodsuite_warning_can_view() {
	$options = bb_get_option( 'bbmodsuite_warning_options' );
	return $options['min_level'];
}

function bbmodsuite_warning_admin_add() {
	bb_admin_add_submenu( __( 'Warning', 'bbpress-moderation-suite' ), bbmodsuite_warning_can_view(), 'bbpress_moderation_suite_warning', 'bbpress_moderation_suite' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_warning_admin_add' );
