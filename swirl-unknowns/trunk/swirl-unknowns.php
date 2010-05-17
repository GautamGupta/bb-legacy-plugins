<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Non-logged-in users get redirected to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Version: 0.6
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/


/**
 * Global settings
 */
$redir_confirmation = '';
$su_default_redir_page = bb_get_option('uri') .'bb-login.php';
if ( !bb_get_option('su_redir_page') )
	bb_update_option('su_redir_page', $su_default_redir_page);


/**
 * Add admin panel
 */
function su_add_admin_panel() {
	bb_admin_add_submenu( __('Swirl Unknowns'), 'use_keys', 'su_admin_panel' );
}


/**
 * Admin panel
 */
function su_admin_panel() {
	global $su_default_redir_page;
	global $redir_confirmation;
?>
	<link rel="stylesheet" href="<?php echo bb_get_plugin_uri(bb_plugin_basename(__FILE__)) ?>swirl-unknowns.css" type="text/css" />
	<h2><?php _e('Swirl Unknowns'); ?></h2>
	<h3><?php _e('Define the page non-logged-in users will be redirected to'); ?></h3>
	<form method="post" id="redir_form">
	<fieldset>
		<label for="redir_page"><?php _e('Page URI: '); ?></label>
		<input type="text" name="redir_page" id="redir_page" value="<?php echo bb_get_option('su_redir_page') ?>" />
		<input type="submit" name="submit" id="submit" value="Update" /><span id="confirmation"><?php echo $redir_confirmation ?></span>
	</fieldset>
	</form>
	<br />
	<?php printf( __('<strong>Note</strong>: if no address is entered, default page ( <code>%s</code> ) will be used.'), $su_default_redir_page ); ?>
	<br /><br />
	<h3><?php _e('Allowed pages'); ?></h3>
	<ul class="allowed_pages">
		<li><code><?php printf( '%sbb-login.php', bb_get_option('uri') ); ?></code></li>
		<li><code><?php printf( '%sbb-reset-password.php', bb_get_option('uri') ); ?></code></li>
		<li><code><?php printf( '%sregister.php', bb_get_option('uri') ); ?></code></li>
		<li><code><?php printf( '%sxmlrpc.php', bb_get_option('uri') ); ?></code></li>
		<li><code><?php printf( '%sbb-admin/', bb_get_option('uri') ); ?></code></li>
	</ul>
<?php
}


/**
 * Process admin panel forms
 */
function su_admin_panel_forms_process() {
	if ( $_POST ) {
		global $redir_confirmation;

		if ( $_POST['redir_page'] ) {
			bb_update_option('su_redir_page', $_POST['redir_page']);
			$redir_confirmation = 'Page updated';
		} else {
			global $su_default_redir_page;

			bb_update_option('su_redir_page', $su_default_redir_page);
			$redir_confirmation = 'Page reset';
		}
	}
}


/**
 * Redirection function
 */
function swirl_unknowns() {
	$redir_page = bb_get_option('su_redir_page');
	$server_uri = $_SERVER['REQUEST_URI'];

	if (
		!bb_is_user_logged_in()
		&& !preg_match( '/((bb\-login|bb\-reset\-password|register|xmlrpc)\.php|\/bb\-admin\/)/i', $server_uri )
		&& strcasecmp( $server_uri, $redir_page )
	)
		bb_safe_redirect( $redir_page );
}


/**
 * Remove plugin traces
 */
function su_uninstall() {
	bb_delete_option('su_redir_page');
}


/**
 * Add bbPress actions
 */
add_action( 'bb_admin_menu_generator', 'su_add_admin_panel' );
add_action( 'bb_admin-header.php', 'su_admin_panel_forms_process' );
add_action( 'bb_init', 'swirl_unknowns' );


/**
 * Grab bbPress plugin deactivated hook
 */
bb_register_plugin_deactivation_hook( __FILE__, 'su_uninstall' );
?>