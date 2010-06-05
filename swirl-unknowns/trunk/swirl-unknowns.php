<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Redirects non-logged-in users to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Version: 0.7
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/

define('SWIRL_ID', 'swirl-unknowns');
define('SWIRL_NAME', 'Swirl Unknowns');


/**
 * Global vars
 */
$swirl_confirmation = '';

// percent-substitution tags
$tags = array(
			  array ( 'code' => '%bb_uri%', 'value' => bb_get_option('uri') ), // do not move this entry, its value is used by the default swirl page
			  array ( 'code' => '%domain%', 'value' => bb_get_option('domain') )
			  );

// swirl-immune pages
$immunes = array(
				'bb-login.php', // do not move this entry, it is used by the default swirl page
				'bb-reset-password.php',
				'register.php',
				'xmlrpc.php',
				'bb-admin/'
				 );

$default_swirl_page = $tags[0]['code'] . $immunes[0]; // default swirl page


/**
 * Add admin panel
 */
function swirl_unknowns_add_admin_panel() {
	bb_admin_add_submenu( __( SWIRL_NAME ), 'use_keys', 'swirl_unknowns_admin_panel' );
}


/**
 * Admin panel
 */
function swirl_unknowns_admin_panel() {
	global $immunes;
	global $swirl_confirmation;
	global $default_swirl_page;
	global $tags;
?>
<link rel="stylesheet" href="<?php echo bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) . SWIRL_ID ?>.css" type="text/css" />
<div id="swirl_unknowns_container">
<h2><?php _e( SWIRL_NAME ); ?></h2>
<h3><?php _e( 'Swirl page' ); ?></h3>
<form method="post" id="swirl_form">
<fieldset>
	<input type="text" name="swirl_page" id="swirl_page" value="<?php echo bb_get_option('swirl_page'); ?>" />
	<input type="hidden" name="action" id="action" value="swirl_unknowns" />
	<p><?php printf( __( '<strong>Note</strong>: if no address is entered, default swirl page ( <code>%s</code> ) will be used.' ), $default_swirl_page ); ?></p>
	<br />
	<input type="submit" name="submit" id="submit" value="Update" /><span id="confirmation"><?php echo $swirl_confirmation; ?></span>
</fieldset>
</form>
<br />

<h3><?php _e( 'Percent-substitution tags' ); ?></h3>
<ul>
<?php
foreach ( $tags as $tag )
	printf( '<li><strong>%1$s</strong> : <code>%2$s</code></li>', $tag['code'], $tag['value'] );
?>
</ul>
<br />

<h3><?php _e( 'Swirl-immune pages' ); ?></h3>
<ul>
<?php
foreach ( $immunes as $immune )
	printf( '<li><code>%s</code></li>', $immune );
?>
</ul>
</div>
<?php
}


/**
 * Process admin panel forms
 */
function swirl_unknowns_admin_panel_forms_process() {
	if ( 'swirl_unknowns' == $_POST['action'] ) {
		global $swirl_confirmation;

		if ( $_POST['swirl_page'] ) {
			bb_update_option('swirl_page', $_POST['swirl_page']);
		} else {
			global $default_swirl_page;

			bb_update_option('swirl_page', $default_swirl_page);
		}

		$swirl_confirmation = 'Swirl page updated';
	}
}


/**
 * Swirl unknonws
 */
function swirl_unknowns() {
	if ( $swirl_page = bb_get_option('swirl_page') ) {
		global $immunes;
		global $tags;

		// create swirl-immune pages' PCRE pattern
		$pattern = '';

		foreach ( $immunes as $immune ) {
			// add shashes to PCRE special chars
			$immune = str_replace( '-', '\-', $immune );
			$immune = str_replace( '.', '\.', $immune );
			$immune = str_replace( '/', '\/', $immune );

			$pattern .= $immune .'|';
		}

		$pattern = substr( $pattern, 0, -1 ); // remove unnecessary tailed "|"

		// process percent-substitution tags
		foreach ( $tags as $tag )
			$redir_page = str_replace( $tag['code'], $tag['value'], $redir_page );

		if (
			!bb_is_user_logged_in()
			&& !preg_match( '/('. $pattern .')/i', $_SERVER['REQUEST_URI'] )
			&& strcasecmp( $_SERVER['REQUEST_URI'], $swirl_page ) // do not redirect (again) the swirl page
		)
			bb_safe_redirect( $swirl_page );
	}
}


/**
 * Remove plugin traces
 */
function swirl_unknowns_uninstall() {
	bb_delete_option('swirl_page');
}


/**
 * Add bbPress actions
 */
add_action( 'bb_admin_menu_generator', 'swirl_unknowns_add_admin_panel' );
add_action( 'bb_admin-header.php', 'swirl_unknowns_admin_panel_forms_process' );
add_action( 'bb_init', 'swirl_unknowns' );


/**
 * Grab bbPress plugin deactivated hook
 */
bb_register_plugin_deactivation_hook( __FILE__, 'swirl_unknowns_uninstall' );
?>