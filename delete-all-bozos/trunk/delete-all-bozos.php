<?php
/*
Plugin Name: Delete All Bozos
Description: Delete all of the bozo users on your forum with just one click.
Plugin URI: http://nightgunner5.wordpress.com/tag/delete-all-bozos/
Author: Ben L. (Nightgunner5)
Author URI: http://nightgunner5.wordpress.com/
Version: 0.1.1
Requires at least: 1.0
Tested up to: trunk
Text Domain: delete-all-bozos
Domain Path: /translations/
*/

function delete_all_bozos() {
	$bozos = bb_user_search( array(
		'append_meta' => false,
		'users_per_page' => 10000, // Yeah, this is a real limit, but who's going to have more than 10,000 bozos on their forum at once? And if they do, they can just visit the page multiple times.
		'roles' => array(
			'bozo'
		)
	) );
	$total_bozos = (int)bb_get_option( 'dabozos_count' );
?>
<h2><?php _e( 'Delete All Bozos', 'delete-all-bozos' ); ?></h2>
<?php if ( !is_array( $bozos ) ) { ?>
<div id="message" class="updated"><p><?php _e( 'Nice going! Your forum is free of bozos!', 'delete-all-bozos' ); ?></p></div>
<?php } elseif ( !empty( $_POST['submit'] ) ) { // We already checked if the nonce is valid, so let's do this!
	global $bbdb;

	$IDs = '';

	foreach ( $bozos as $bozo ) {
		$IDs .= ( (int)$bozo->ID ) . ', ';
		wp_cache_delete( $bozo->ID, 'users' );
		wp_cache_delete( $bozo->user_nicename, 'usernicename' );
		wp_cache_delete( $bozo->user_email, 'useremail' );
		wp_cache_delete( $bozo->user_login, 'userlogins' );
	}

	$IDs = rtrim( $IDs, ', ' );

	// No $bbdb->prepare needed, because everything is already sanitized.
	$bbdb->query( "DELETE FROM `{$bbdb->users}` WHERE `ID` IN ( {$IDs} )" );
	$bbdb->query( "DELETE FROM `{$bbdb->usermeta}` WHERE `user_id` IN ( {$IDs} )" );

	bb_update_option( 'dabozos_count', $total_bozos += count( $bozos ) );
?>
<div id="message" class="updated"><p><?php printf( _n( '%d bozo deleted', '%d bozos deleted.', count( $bozos ), 'delete-all-bozos' ), count( $bozos ) ); ?></p></div>
<?php } else { ?>
<div id="message" class="error"><p><?php printf( _n(
	'You are about to delete %d user from your database permanently. This cannot be reversed, so it is advised that you back up your database and review the list before pushing the button below. If you see someone that isn\'t a spambot, click on their username and remove bozo from their profile.',
	'You are about to delete %d users from your database permanently. This cannot be reversed, so it is advised that you back up your database and review the list before pushing the button below. If you see someone that isn\'t a spambot, click on their username and remove bozo from their profile.',
	count( $bozos ), 'delete-all-bozos' ), count( $bozos ) ); ?></p></div>

<table class="widefat">
<thead>
	<tr>
		<th style="width:40%;">Username</th>
		<th style="width:35%;">E-mail</th>
		<th style="width:25%;">Registered</th>
	</tr>
</thead>

<tfoot>
	<tr>
		<th style="width:40%;">Username</th>
		<th style="width:35%;">E-mail</th>
		<th style="width:25%;">Registered</th>
	</tr>
</tfoot>

<tbody id="role-blocked">
<?php foreach ( $bozos as $bozo ) { ?>
	<tr id="user-<?php echo $bozo->ID; ?>"<?php alt_class( 'dabozos_list' ); ?>>
		<td class="user"><?php echo bb_get_avatar( $bozo->user_email, 16 ); ?><span class="row-title"><a href="<?php profile_tab_link( $bozo->ID, 'edit' ); ?>"><?php echo $bozo->user_login; ?></a></span></td>
		<td><?php echo $bozo->user_email; ?></td>
		<td><?php echo $bozo->user_registered; ?></td>
	</tr>
<?php } ?>
</tbody>
</table>

<form class="settings" action="" method="post">
<fieldset>
<input type="submit" id="submit" class="submit delete" name="submit" value="<?php esc_attr_e( 'Confirm Deletion', 'delete-all-bozos' ); ?>"/>
<?php bb_nonce_field( 'delete-all-bozos' ); ?>
</fieldset>
</form>
<?php }
if ( $total_bozos ) { ?>
<div style="font-size: .75em; position: absolute; bottom: 50px; right: 5px"><?php printf( _n( '%s bozo deleted by Delete All Bozos', '%s bozos deleted by Delete All Bozos', $total_bozos, 'delete-all-bozos' ), bb_number_format_i18n( $total_bozos ) ); ?></div>
<?php }
}

function dabozos_admin_check() {
	if ( !empty( $_POST['submit'] ) )
		bb_check_admin_referer( 'delete-all-bozos' );
}
add_action( 'delete_all_bozos_pre_head', 'dabozos_admin_check' );

function dabozos_admin_add() {
	bb_admin_add_submenu( __( 'Delete All Bozos', 'delete-all-bozos' ), 'use_keys', 'delete_all_bozos', 'users.php' );
}
add_action( 'bb_admin_menu_generator', 'dabozos_admin_add' );

load_plugin_textdomain( 'delete-all-bozos', dirname( __FILE__ ) . '/translations' );
