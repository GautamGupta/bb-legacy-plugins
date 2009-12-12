<?php

/* This is not an individual plugin, but a part of the bbPress Moderation Suite. */

/* $Id$ */

function bbmodsuite_banplus_install() {
	if ( bb_get_option( 'bbmodsuite_banplus_options' ) ) return;
	bb_update_option( 'bbmodsuite_banplus_current_bans', array() );
	bb_update_option( 'bbmodsuite_banplus_options', array( 'min_level' => 'moderate' ) );
	global $bbmodsuite_cache;
	$bbmodsuite_cache['banplus'] = array( 'bans' => array(), 'options' => array( 'min_level' => 'moderate' ) );
}

function bbmodsuite_banplus_uninstall() {
	global $bbmodsuite_cache;
	foreach ( $bbmodsuite_cache['banplus']['bans'] as $user_id => $ban ) {
		do_action( 'bbmodsuite_banplus_unban', $user_id, $ban );
	}
	bb_delete_option( 'bbmodsuite_banplus_current_bans' );
	bb_delete_option( 'bbmodsuite_banplus_options' );
}
/**
 * bbmodsuite_banplus_set_ban() - Set a ban on a user
 *
 * @param int|string $user_id The ID of the user to ban or an IP address or CIDR range with the prefix ip_.
 * @param string $type 'temp' for temporary. Other plugins can add more.
 * @param int $length How long the ban should be in seconds. Defaults to 1 day.
 * @param string $notes The reason for the ban.
 * @param bool $override Set this to true if you want to ban a user without checking for permissions.
 * @return bool true on success, false on error
 * @global $bbmodsuite_cache Cache of options and bans
 */
function bbmodsuite_banplus_set_ban( $user_id, $type = 'temp', $length = 86400, $notes = '', $override = false ) {
	global $bbmodsuite_cache;

	$the_options = $bbmodsuite_cache['banplus']['options'];
	if ( !$override && !bb_current_user_can( $the_options['min_level'] ) )
		return false;
	if ( !$override && strpos( $user_id, 'ip_' ) === false ) {
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
	}

	$current_bans = $bbmodsuite_cache['banplus']['bans'];
	if ( $type == 'unban' ) {
		if ( !isset( $current_bans[$user_id] ) )
			return true;
		do_action( 'bbmodsuite_banplus_unban', $user_id, $current_bans[$user_id] );
		unset( $current_bans[$user_id] );
		$bbmodsuite_cache['bans'] = $current_bans;
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

	do_action( 'bbmodsuite_banplus_ban', $user_id, $current_bans[$user_id] );

	$bbmodsuite_cache['bans'] = $current_bans;
	bb_update_option( 'bbmodsuite_banplus_current_bans', $current_bans );

	return true;
}

function bbmodsuite_banplus_init() {
	$current_bans = (array)bb_get_option( 'bbmodsuite_banplus_current_bans' );
	$changed = false;
	foreach ( $current_bans as $user_id => $ban ) {
		if ( $ban['until'] < time() ) {
			do_action( 'bbmodsuite_banplus_unban', $user_id, $current_bans[$user_id] );
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

function bbmodsuite_banplus_maybe_block_ip() {
	$cur_ip = vsprintf( '%08b%08b%08b%08b', explode( '.', $_SERVER['REMOTE_ADDR'] ) );
	global $bbmodsuite_cache;
	$current_bans = $bbmodsuite_cache['banplus']['bans'];
	foreach ( $current_bans as $id => $ban ) {
		if ( strpos( $id, 'ip_' ) !== false ) {
			list( $ip, $cidr ) = explode( '/', substr( $id, 3 ) );
			if ( !$cidr )
				$cidr = 32;
			$ip = vsprintf( '%08b%08b%08b%08b', explode( '.', $ip ) );
			if ( substr( $ip, 0, $cidr ) == substr( $ip, 0, $cidr ) ) {
				if ( bb_get_template( 'ban-plus-ip.php', false ) ) {
					bb_load_template( 'ban-plus-ip.php', array( 'ban' => $ban, 'ban_ip' => substr( $id, 3 ) ), $ban );
					exit;
				}
				bb_die( sprintf( __( 'Your IP address (%s) is banned from this forum until %s from now.  The person who banned %s said the reason was: </p>%s<p>If you are a moderator or administrator, you can still <a href="%s">log in</a>.', 'bbpress-moderation-suite' ), $_SERVER['REMOTE_ADDR'], substr( $id, 3 ), bb_since( time() * 2 - $ban['until'], true ), $ban['notes'], bb_get_uri( 'bb-login.php', null, BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_USER_FORMS ) ) );
			}
		}
	}
}

function bbmodsuite_banplus_maybe_block_user() {
	global $bbmodsuite_cache;
	$current_bans = $bbmodsuite_cache['banplus']['bans'];
	if ( !empty( $current_bans[bb_get_current_user_info( 'ID' )] ) ) {
		switch ( $current_bans[bb_get_current_user_info( 'ID' )]['type'] ) {
			case 'temp':
				if ( bb_get_template( 'ban-plus.php', false ) ) {
					bb_load_template( 'ban-plus.php', array( 'ban' => $current_bans[bb_get_current_user_info( 'ID' )] ), $current_bans[bb_get_current_user_info( 'ID' )] );
					exit;
				}
				bb_die( sprintf( __( 'You are banned from this forum until %s from now.  The person who banned you said the reason was: %s', 'bbpress-moderation-suite' ), bb_since( time() * 2 - $current_bans[bb_get_current_user_info( 'ID' )]['until'], true ), $current_bans[bb_get_current_user_info( 'ID' )]['notes'] ) );
		}
	}
	if ( !bb_current_user_can( 'moderate' ) ) // Moderators and up are excempt from IP bans.
		bbmodsuite_banplus_maybe_block_ip();
}
if ( bb_get_location() != 'login-page' ) // Let them log in and out
	bbmodsuite_banplus_maybe_block_user();

function bbmodsuite_banplus_get_ban_types() {
	global $bbmodsuite_active_plugins;
	$types = array( 'temp' );
	return apply_filters( 'bbmodsuite_banplus_ban_types', $types );
}

function bbmodsuite_banplus_admin_add_jquery() {
	wp_enqueue_script( 'jquery' );
}
if ( $_GET['page'] == 'new_ban' )
	add_action( 'bbpress_moderation_suite_ban_plus_pre_head', 'bbmodsuite_banplus_admin_add_jquery' );

function bbmodsuite_banplus_admin_newbanajax() {
	header( 'Content-Type: text/plain' );

	if ( !bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-banplus-new-ajax' ) )
		exit;

	$name = bbmodsuite_stripslashes( $_POST['text'] );
	$name = str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $_POST['text'], 0, $_POST['pos'] ) ) . '%' . str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $_POST['text'], $_POST['pos'] ) );

	global $bbdb;
	$results = $bbdb->get_col( $bbdb->prepare( 'SELECT `user_nicename` FROM `' . $bbdb->users . '` WHERE ( `user_nicename` LIKE %s OR `user_login` LIKE %s OR `ID` = %d ) AND `ID` != %d ORDER BY LENGTH(`user_nicename`) ASC LIMIT 15', $name, $name, $_POST['text'], bb_get_current_user_info( 'ID' ) ) );

	exit( '["' . implode( '","', array_map( 'addslashes', $results ) ) . '"]' );
}
if ( $_GET['page'] == 'new_ban_ajax' )
	add_action( 'bbpress_moderation_suite_ban_plus_pre_head', 'bbmodsuite_banplus_admin_newbanajax' );

function bbpress_moderation_suite_ban_plus() { ?>
<h2><?php _e( 'Ban Plus', 'bbpress-moderation-suite' ); ?></h2>
<div class="table-filter">
	<a<?php if ( !in_array( $_GET['page'], array( 'new_ban', 'admin' ) ) ) echo ' class="current"'; ?> href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'current_bans' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Current bans', 'bbpress-moderation-suite' ); ?> <span class="count">(<?php echo bb_number_format_i18n( count( $GLOBALS['bbmodsuite_cache']['banplus']['bans'] ) ); ?>)</span></a> |
	<a<?php if ( $_GET['page'] === 'new_ban' ) echo ' class="current"'; ?> href="<?php echo bb_nonce_url( bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'new_ban' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'bbmodsuite-banplus-new' ); ?>"><?php _e( 'Ban a user', 'bbpress-moderation-suite' ); ?></a> |
	<a<?php if ( $_GET['page'] === 'new_ip_ban' ) echo ' class="current"'; ?> href="<?php echo bb_nonce_url( bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'new_ip_ban' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'bbmodsuite-banplus-new-ip' ); ?>"><?php _e( 'Ban an IP address', 'bbpress-moderation-suite' ); ?></a>
	<?php if ( bb_current_user_can( 'use_keys' ) ) { ?>| <a<?php if ( $_GET['page'] === 'admin' ) echo ' class="current"'; ?> href="<?php echo bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'admin' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Administration', 'bbpress-moderation-suite' ); ?></a><?php } ?>
</div>
<?php switch ( $_GET['page'] ) {
	case 'new_ip_ban':
		if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'GET' ) {
			if ( !bb_verify_nonce( $_GET['_wpnonce'], 'bbmodsuite-banplus-new-ip' ) ) { ?>
<div class="error"><p><?php _e( 'Invalid banning attempt.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			} ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'new_ip_ban', 'plugin' => 'bbpress_moderation_suite_ban_plus' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="ip">
			<?php _e( 'IP address or CIDR range', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="ip" name="ip" type="text" class="text"/>
			<p><?php printf( __( 'As an example, your IP address is %s. <a href="http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing">CIDR ranges</a> are limited to /16-/32.', 'bbpress-moderation-suite' ), $_SERVER['REMOTE_ADDR'] ); ?></p>
			<p><?php _e( 'Moderators, administrators, and key masters are not be affected by IP bans.', 'bbpress-moderation-suite' ); ?></p>
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
			<p><?php _e( 'Why are you banning this IP?  This might be shown to anyone who is blocked.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-banplus-new-ip-submit' ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Ban user', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<?php	} elseif ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-banplus-new-ip-submit' ) ) {
			if ( !preg_match( '#^[12]?[0-9]{1,2}(\.[12]?[0-9]{1,2}){3}(/1[6-9]|/2[0-9]|/3[0-2])?$#', $_POST['ip'] ) ) { ?>
<div class="error"><p><?php _e( 'Invalid IP. IP addresses must be <a href="http://en.wikipedia.org/wiki/IPv4">IPv4</a> with optional <a href="http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing">CIDR</a>.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			}

			$time       = (int)$_POST['time'];
			$multiplier = (int)$_POST['time_multiplier'];
			$length     = $time * $multiplier;
			$notes      = bbmodsuite_stripslashes( $_POST['notes'] );
			if ( bbmodsuite_banplus_set_ban( 'ip_' . $_POST['ip'], 'temp', $length, $notes ) ) {
?>
<div class="updated"><p><?php printf( __( 'The IP "%s" has been successfully banned.', 'bbpress-moderation-suite' ), $_POST['ip'] ); ?></p></div>
<?php } else { ?>
<div class="error"><p><?php _e( 'The banning attempt failed.', 'bbpress-moderation-suite' ); ?></p></div>
<?php } } else { ?>
<div class="error"><p><?php _e( 'Invalid banning attempt.', 'bbpress-moderation-suite' ); ?></p></div>
<?php	}
		break;
	case 'new_ban':
		if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'GET' ) {
			if ( !bb_verify_nonce( $_GET['_wpnonce'], 'bbmodsuite-banplus-new' ) ) { ?>
<div class="error"><p><?php _e( 'Invalid banning attempt.', 'bbpress-moderation-suite' ); ?></p></div>
<?php			return;
			} ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'page' => 'new_ban', 'plugin' => 'bbpress_moderation_suite_ban_plus' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
<fieldset>
	<div>
		<label for="user_id">
			<?php _e( 'Username', 'bbpress-moderation-suite' ); ?>
		</label>
		<div>
			<input id="user_id" name="user_id" type="text" class="text"/>
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
			<p><?php _e( 'Why are you banning this user?  This might be shown to the user.', 'bbpress-moderation-suite' ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<?php bb_nonce_field( 'bbmodsuite-banplus-new-submit' ); ?>
	<input class="submit" type="submit" name="submit" value="<?php _e( 'Ban user', 'bbpress-moderation-suite' ); ?>" />
</fieldset>
</form>
<script type="text/javascript">
jQuery(function($){
	var autocompleteTimeout, ul = $('<ul/>').css({
		position: 'absolute',
		zIndex: 10000,
		backgroundColor: '#fff',
		fontSize: '1.2em',
		padding: 2,
		marginTop: -1,
		MozBorderRadius: 2,
		WebkitBorderRadius: 2,
		borderRadius: 2,
		border: '1px solid #ccc',
		borderTopWidth: '0'
	}).insertAfter('#user_id');
	$('#user_id').attr('autocomplete', 'off').keyup(function(){
		// IE compat
		if(document.selection) {
			// The current selection
			var range = document.selection.createRange();
			// We'll use this as a 'dummy'
			var stored_range = range.duplicate();
			// Select all text
			stored_range.moveToElementText(this);
			// Now move 'dummy' end point to end point of original range
			stored_range.setEndPoint('EndToEnd', range);
			// Now we can calculate start and end points
			this.selectionStart = stored_range.text.length - range.text.length;
			this.selectionEnd = this.selectionStart + range.text.length;
		}

		try {
			clearTimeout(autocompleteTimeout);
		} catch (ex) {}

		if (!this.value.length)
			return;

		autocompleteTimeout = setTimeout(function(text, pos){
			$.post('<?php echo addslashes( str_replace( '&amp;', '&', bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bbpress_moderation_suite_ban_plus', 'page' => 'new_ban_ajax' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ) ) ); ?>', {
				text: text,
				pos: pos,
				_wpnonce: '<?php echo bb_create_nonce( 'bbmodsuite-banplus-new-ajax' ); ?>'
			}, function(data){
				ul.empty();
				$.each(data, function(i, name){
					if (name.length)
						$('<li/>').css({
							listStyle: 'none'
						}).text(name).click(function(){
							$('#user_id').val($(this).text());
							ul.empty();
						}).appendTo(ul);
				});
			}, 'json');
		}, 750, this.value, this.selectionStart);
	});
});
</script>
<?php	} elseif ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && bb_verify_nonce( $_POST['_wpnonce'], 'bbmodsuite-banplus-new-submit' ) ) {
			if ( !$user = bb_get_user( $_POST['user_id'] ) )
				if ( !$user = bb_get_user( $_POST['user_id'], array( 'by' => 'nicename' ) ) )
					if ( !$user = bb_get_user( $_POST['user_id'], array( 'by' => 'username' ) ) ) { ?>
<div class="error"><p><?php _e( 'User not found', 'bbpress-moderation-suite' ); ?></p></div>
<?php					return;
					}

			$user_id = $user->ID;

			$username   = get_user_display_name( $user_id );
			$time       = (int)$_POST['time'];
			$multiplier = (int)$_POST['time_multiplier'];
			$length     = $time * $multiplier;
			$notes      = bbmodsuite_stripslashes( $_POST['notes'] );
			if ( bbmodsuite_banplus_set_ban( $user_id, 'temp', $length, $notes ) ) {
?>
<div class="updated"><p><?php printf( __( 'The user "%s" has been successfully banned.', 'bbpress-moderation-suite' ), $username ); ?></p></div>
<?php } else { ?>
<div class="error"><p><?php _e( 'The banning attempt failed.', 'bbpress-moderation-suite' ); ?></p></div>
<?php } } else { ?>
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
			<p><?php _e( 'Users can only ban other users of a lower rank. Keymasters can ban anyone.  What user level should be the lowest allowed to ban users?', 'bbpress-moderation-suite' ); ?></p>
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
?><table class="widefat">
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
			<td><?php echo strpos( $user_id, 'ip_' ) === false ? get_user_display_name( $user_id ) : ( strpos( $user_id, '/' ) ? '<strong>IP range:</strong> ' . substr( $user_id, 3 ) : '<strong>IP address:</strong> ' . substr( $user_id, 3 ) ); ?></td>
			<td><?php echo get_user_display_name( $ban['banned_by'] ); ?></td>
			<td><?php echo date( 'Y-m-d H:i:s', $ban['until'] ); ?></td>
			<td><?php echo $ban['notes']; ?></td>
			<td class="action">
				<a href="<?php echo $unban_link; ?>"><?php _e( 'Unban', 'bbpress-moderation-suite' ); ?></a>
			</td>
		</tr>

<?php
	} // foreach current_bans as ban
?>

	</tbody>
</table>
<?php
	}
}

function bbmodsuite_banplus_can_view() {
	global $bbmodsuite_cache;
	return $bbmodsuite_cache['banplus']['options']['min_level'];
}

function bbmodsuite_banplus_admin_add() {
	bb_admin_add_submenu( __( 'Ban Plus', 'bbpress-moderation-suite' ), bbmodsuite_banplus_can_view(), 'bbpress_moderation_suite_ban_plus', 'bbpress_moderation_suite' );
}
add_action( 'bb_admin_menu_generator', 'bbmodsuite_banplus_admin_add' );
