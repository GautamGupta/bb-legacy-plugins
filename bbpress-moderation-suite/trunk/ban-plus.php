<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

function bbmodsuite_banplus_install() {
	if ( bb_get_option( 'bbmodsuite_banplus_options' ) ) return;
	bb_update_option( 'bbmodsuite_banplus_current_bans', array() );
	bb_update_option( 'bbmodsuite_banplus_options', array( 'min_level' => 'moderate' ) );
	global $bbmodsuite_cache;
	$bbmodsuite_cache['banplus'] = array( 'bans' => array(), 'options' => array( 'min_level' => 'moderate' ) );
}

function bbmodsuite_banplus_uninstall() {
	bb_delete_option( 'bbmodsuite_banplus_current_bans' );
	bb_delete_option( 'bbmodsuite_banplus_options' );
}
/**
 * bbmodsuite_banplus_set_ban() - Set a ban on a user
 *
 * @param int|string $user_id The ID or username of the user to ban.
 * @param string $type 'temp' for temporary. Other plugins can add more.
 * @param int $length How long the ban should be in seconds. Defaults to 1 day.
 * @param string $notes The reason for the ban.
 * @return bool true on success, false on error
 * @global $bbmodsuite_cache Cache of options and bans
 */
function bbmodsuite_banplus_set_ban( $user_id, $type = 'temp', $length = 86400, $notes = '' ) {
	global $bbmodsuite_cache;

	$the_options = $bbmodsuite_cache['banplus']['options'];
	if ( !bb_current_user_can( $the_options['min_level'] ) )
		return false;
	$user_id = bb_get_user_id( $user_id );
	if ( !$user_id )
		return false;
	if ( $user_id === bb_get_current_user_info( 'ID' ) )
		return false;

	if ( class_exists( 'BP_User' ) )
		$user = new BP_User( $user_id );
	else
		$user = new WP_User( $user_id );

	if ( ( $user->has_cap( 'moderate' ) && !bb_current_user_can( 'administrate' ) ) || ( $user->has_cap( 'administrate' ) && !bb_current_user_can( 'use_keys' ) ) )
		return false;

	$current_bans = $bbmodsuite_cache['bans'];
	if ( $type === 'unban' ) {
		if ( !isset( $current_bans[$user_id] ) )
			return true;
		unset( $current_bans[$user_id] );
		bb_update_option( 'bbmodsuite_banplus_current_bans', $current_bans );
		return true;
	}

	$length = (int)$length;
	if ($length <= 0)
		return false;
	$until = time() + $length;

	if ( !in_array( $type, bbmodsuite_banplus_get_ban_types() ) )
		return false;

	if ( $current_bans[$user_id]['type'] === $type && $current_bans[$user_id]['length'] === $length )
		return true;
	if ( isset( $current_bans[$user_id] ) )
		return false;

	$notes = bb_autop( htmlspecialchars( trim( $notes ) ) );

	$current_bans[$user_id] = array(
		'type'      => $type,
		'length'    => $length,
		'on'        => time(),
		'until'     => $until,
		'banned_by' => bb_get_current_user_info( 'ID' ),
		'notes'     => $notes,
	);

	$bbmodsuite_cache['bans'] = $current_bans;
	bb_update_option( 'bbmodsuite_banplus_current_bans', $current_bans );

	return true;
}

function bbmodsuite_banplus_init() {
	$current_bans = (array)bb_get_option( 'bbmodsuite_banplus_current_bans' );
	$changed = false;
	foreach ( $current_bans as $user_id => $ban ) {
		if ( $ban['until'] < time() ) {
			unset( $current_bans[$user_id] );
			$changed = true;
		}
	}
	if ( $changed )
		bb_update_option( 'bbmodsuite_banplus_current_bans', $current_bans );
	global $bbmodsuite_cache;
	if ( empty( $bbmodsuite_cache['banplus'] ) )
		$bbmodsuite_cache['banplus'] = array( 'bans' => $current_bans, 'options' => bb_get_option( 'bbmodsuite_banplus_options' ) );
}
bbmodsuite_banplus_init();

function bbmodsuite_banplus_maybe_block_user() {
	global $bbmodsuite_cache;
	$current_bans = $bbmodsuite_cache['banplus']['bans'];
	if ( !empty( $current_bans[bb_get_current_user_info( 'ID' )] ) ) {
		switch ( $current_bans[bb_get_current_user_info( 'ID' )]['type'] ) {
			case 'temp':
				bb_die( sprintf( __( 'You are banned from this forum until %s from now.  The person who banned you said the reason was: %s', 'bbpress-moderation-suite' ), bb_since( time() - ( $current_bans[bb_get_current_user_info( 'ID' )]['until'] - time() ), true ), $current_bans[bb_get_current_user_info( 'ID' )]['notes'] ) );
				break;
		}
	}
}
bbmodsuite_banplus_maybe_block_user();

function bbmodsuite_banplus_get_ban_types() {
	global $bbmodsuite_active_plugins;
	$types = array( 'temp' );
	if ( isset( $bbmodsuite_active_plugins['probation'] ) )
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

#bbBody div.updated p, #bbBody div.error p {
	margin: 0;
}
/* ]]> */
</style>
<?php }

function bbmodsuite_ban_plus_add_admin_css() {
	add_action( 'bb_admin_head', 'bbmodsuite_ban_plus_admin_css' );
}
add_action( 'bbpress_moderation_suite_ban_plus_pre_head', 'bbmodsuite_ban_plus_add_admin_css' );

function bbpress_moderation_suite_ban_plus() { ?>
<ul id="bbAdminSubSubMenu">
	<li<?php if ( !in_array( $_GET['page'], array( 'new_ban', 'admin' ) ) ) echo ' class="current"'; ?>><a href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'current_bans' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">
		<span><?php _e( 'Current bans', 'bbpress-moderation-suite' ); ?></span>
	</a></li>
	<li<?php if ( $_GET['page'] === 'new_ban' ) echo ' class="current"'; ?>><a href="<?php echo bb_nonce_url( bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'new_ban' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'bbmodsuite-banplus-new' ); ?>">
		<span><?php _e( 'Ban a user', 'bbpress-moderation-suite' ); ?></span>
	</a></li>
	<?php if ( bb_current_user_can( 'use_keys' ) ) { ?><li<?php if ( $_GET['page'] === 'admin' ) echo ' class="current"'; ?>><a href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'admin' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>">
		<span><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></span>
	</a></li><?php } ?>
</ul>
<?php switch ( $_GET['page'] ) {
	case 'new_ban':
		if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
			if ( !bb_verify_nonce( $_GET['_wpnonce'], 'bbmodsuite-banplus-new' ) ) { ?>
<div class="error"><p><?php _e( 'Invalid banning attempt.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			} ?>
<h2><?php _e( 'Ban a user', 'bbpress-moderation-suite' ); ?></h2>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'new_ban', 'plugin' => 'bbpress_moderation_suite_ban_plus' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="user_id">
			<?php _e( 'Username', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="user_id" name="user_id" type="text" class="text" /><?php /* TODO: Autocomplete */ ?>
			<p><?php _e( 'Who are you banning? (Username)', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
	<div>
		<label for="time">
			<?php _e( 'Time', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="time" name="time" type="text" value="1" class="text short" />
			<select id="time_multiplier" name="time_multiplier">
				<option value="60"><?php _e( 'minutes', 'bbpress-moderation-suite' ); ?></option>
				<option value="3600"><?php _e( 'hours', 'bbpress-moderation-suite' ); ?></option>
				<option value="86400" selected="selected"><?php _e( 'days', 'bbpress-moderation-suite' ); ?></option>
				<option value="604800"><?php _e( 'weeks', 'bbpress-moderation-suite' ); ?></option>
				<option value="2592000"><?php _e( 'months', 'bbpress-moderation-suite' ); ?></option>
			</select>
			<p><?php _e( 'How long will the ban last?', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
	<div>
		<label for="notes">
			<?php _e( 'Notes', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<textarea id="notes" name="notes" rows="15" cols="43"></textarea>
			<p><?php _e( 'Why are you banning this user?  This will be shown to the user.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-banplus-new-submit' ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Ban user', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<?php	} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' && bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-banplus-new-submit' ) ) {
			$user_id = bb_get_user_id( $_POST['user_id'] );
			if ( !$user_id ) { ?>
<div class="error"><p><?php _e( 'User not found', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			}

			$username   = get_user_display_name( $user_id );
			$time       = (int)$_POST['time'];
			$multiplier = (int)$_POST['time_multiplier'];
			$length     = $time * $multiplier;
			$notes      = $_POST['notes'];
			if ( bbmodsuite_banplus_set_ban( $user_id, 'temp', $length, $notes ) ) {
?>
<div class="updated"><p><?php printf( __( 'The user "%s" has been successfully banned.', 'bbpress-moderation-suite' ), $username ); ?></p></div>
<?php } else { ?>
<div class="error"><p><?php _e( 'The banning attempt failed.', 'bbpress-moderation-suite' ); ?></p></div>
<?php } ?>
<?php	} else { ?>
<div class="error"><p><?php _e( 'Invalid banning attempt.', 'bbpress-moderation-suite' ); ?></p></div>
<?php	}
		break;
	case 'unban_user':
		if ( bb_verify_nonce( $_GET['_wpnonce'], 'bbmodsuite-banplus-unban_' . $_GET['user'] ) ) {
			if ( bbmodsuite_banplus_set_ban( $_GET['user'], 'unban' ) ) { ?>
<div class="updated"><p><?php _e( 'User successfully unbanned.', 'bbpress-moderation-suite' ); ?></p></div>
<?php		} else { ?>
<div class="error"><p><?php _e( 'User could not be unbanned.', 'bbpress-moderation-suite' ); ?></p></div>
<?php		}
			break;
		}
	case 'admin':
		if ( bb_current_user_can( 'use_keys' ) && $_GET['page'] === 'admin' ) {
			global $bbmodsuite_cache;
			$the_options = $bbmodsuite_cache['banplus']['options'];
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
				if ( bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-banplus-admin-submit' ) ) {
					$change    = false;
					$min_level = $_POST['min_level'];
					if ( !in_array( $min_level, array( 'moderate', 'administrate', 'use_keys' ) ) )
						$min_level = 'moderate';
					if ( $the_options['min_level'] != $min_level ) {
						$the_options['min_level'] = $min_level;
						$change = true;
					}
					if ( $change )
						bb_update_option( 'bbmodsuite_banplus_options', $the_options ); ?>
<div class="updated"><p><?php _e( 'Options successfully saved.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			} else { ?>
<div class="error"><p><?php _e( 'Failed to save options.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			}
			} else { ?>
<h2><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></h2>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'admin', 'plugin' => 'bbpress_moderation_suite_ban_plus' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="min_level">
			<?php _e( 'Minimum user level to ban', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<select id="min_level" name="min_level">
				<option value="moderate"<?php if ( $the_options['min_level'] == 'moderate' ) echo ' selected="selected"'; ?>><?php _e( 'Moderator' ); ?></option>
				<option value="administrate"<?php if ( $the_options['min_level'] == 'administrate' ) echo ' selected="selected"'; ?>><?php _e( 'Administrator' ); ?></option>
				<option value="use_keys"<?php if ( $the_options['min_level'] == 'use_keys' ) echo ' selected="selected"'; ?>><?php _e( 'Keymaster' ); ?></option>
			</select>
			<p><?php _e( 'Users can only ban other users of a lower rank.  Keymasters can ban anyone.  What user level should be the lowest allowed to ban users?', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-banplus-admin-submit' ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Save settings', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<?php		}
			break;
		}
	default:
		global $bbmodsuite_cache;
		$current_bans = $bbmodsuite_cache['banplus']['bans'];
?><h2><?php _e( 'Current bans', 'bbpress-moderation-suite' ); ?></h2>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e( 'User', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Banned by', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Until', 'bbpress-moderation-suite' ); ?></th>
			<th><?php _e( 'Notes', 'bbpress-moderation-suite' ); ?></th>
			<th class="action"><?php _e( 'Actions', 'bbpress-moderation-suite' ); ?></th>
		</tr>
	</thead>
	<tbody>

<?php
	foreach ( $current_bans as $user_id => $ban ) {
		$unban_link = attribute_escape(
						bb_nonce_url(
										bb_get_uri(
														'bb-admin/admin-base.php',
														array(
															'page' => 'unban_user',
															'user' => $user_id,
															'plugin' => 'bbpress_moderation_suite_ban_plus',
														),
														BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN
										),
										'bbmodsuite-banplus-unban_' . $user_id
						)
		);
?>

		<tr<?php alt_class( 'banned_user' ); ?>>
			<td><?php echo get_user_display_name( $user_id ); ?></td>
			<td><?php echo get_user_display_name( $ban['banned_by'] ); ?></td>
			<td><?php echo date( 'Y-m-d H:i:s', $ban['until'] ); ?></td>
			<td><?php echo $ban['notes']; ?></td>
			<td class="action">
				<a href="<?php echo $unban_link; ?>"><?php _e( 'Unban', 'bbpress-moderation-suite' ); ?></a>
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

function bbmodsuite_banplus_admin_add() {
	global $bb_submenu, $bbmodsuite_cache;
	$the_options = $bbmodsuite_cache['banplus']['options'];
	$bb_submenu['users.php'][] = array( __( 'Ban Plus', 'bbpress-moderation-suite' ), $the_options['min_level'], 'bbpress_moderation_suite_ban_plus' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_banplus_admin_add' );

?>