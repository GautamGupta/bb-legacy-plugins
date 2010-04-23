<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Non-logged-in users get redirected to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Author: mr_pelle
Author URI:
Version: 0.4.2.2
Requires at least: 1.0.2
Tested up to: 1.0.2
*/


// global vars
$default_swirl_page = bb_get_option('path')."index.php";
$aap_confirmation = '';
$swirl_confirmation = '';


/**
 * Add admin page
 **/
function swirl_unknowns_add_admin_page() {
	bb_admin_add_submenu(__('Swirl Unknowns'), 'use_keys', 'swirl_unknowns_admin_page');
}


/**
 * Admin page
 **/
function swirl_unknowns_admin_page() {
	global $aap_confirmation;
	global $default_swirl_page;
	global $swirl_confirmation;
	$path = bb_get_option('path');
?>
	<link rel="stylesheet" href="<?=bb_get_plugin_uri(bb_plugin_basename(__FILE__))?>swirl-unknowns.css" type="text/css" />
	<h2><?php _e('Swirl Unknowns'); ?></h2>
	<div id="container">
	<h3><?php _e('Enter the address of the page non-logged-in users will be redirected to:'); ?></h3>
	<form method="post" id="swirl_form"><fieldset>
	<input type="hidden" name="action" value="swirl" />
	<label for="swirl_page"><?php _e('Page address:'); ?></label>
	<input type="text" name="swirl_page" id="swirl_page" value="<?=bb_get_option('swirl_page')?>" /><br /><br />
	<label for="disable"><?php _e('Disable plugin?'); ?></label> <input type="checkbox" name="disable" id="disable" />
	<input type="submit" name="submit" id="submit" value="Update" /><span id="swirl_confirmation"><?=$swirl_confirmation?></span>
	</fieldset></form>
	<br />
	<strong><?php _e('Notes:'); ?></strong>
	<ul class="notes">
		<li><?php printf('Example: <code>%s</code>', $path."custom-page.php"); ?></li>
		<li><?php printf('Example (no permalinks): <code>%s</code>', $path."topic.php?id=1"); ?></li>
		<li><?php printf('Example (numeric permalinks activated): <code>%s</code>', $path."topic/1"); ?></li>
		<li><?php printf('Example (name based permalinks activated): <code>%s</code>', $path."topic/topic-name"); ?></li>
		<li><?php printf('You may also enter just <code>/</code> to redirect to the website root (i.e. <code>%s</code>).', bb_get_option('domain')."/"); ?></li>
		<li><?php printf('If no address is entered, default page (<code>%s</code>) will be used.', $default_swirl_page); ?></li>
	</ul>
	<br />
	<h3><?php _e('Allowed pages:'); ?></h3>
	<ul class="allowed_pages">
		<li><?php _e('bb-login.php'); ?></li>
		<li><?php _e('bb-reset-password.php'); ?></li>
		<li><?php _e('register.php'); ?></li>
		<li><?php _e('xmlrpc.php'); ?></li>
		<li><?php printf('%s', $path."bb-admin/"); ?></li>
		<li><form method="post" id="aap_form">
		<input type="hidden" name="action" value="allow_page" />
		<input type="text" name="another_allowed_page" id="another_allowed_page" value="<?=bb_get_option('$another_allowed_page')?>" />
		<span id="aap_confirmation"><?=$aap_confirmation?></span>
		</form></li>
	</ul>
	<br />
	<strong><?php _e('Notes:'); ?></strong>
	<ul class="notes">
		<li><?php _e('Allowed page address follows the rules above.'); ?></li>
		<li><?php _e('If no address is entered, last entered page will be removed from allowed pages list.'); ?></li>
	</ul>
	</div>
<?php
}


/**
 * Process admin page forms
 **/
function swirl_unknowns_admin_page_process() {
	if ($_POST)
	{
		if ($_POST['action'] == 'swirl') // request coming from "page address" form
		{
			global $swirl_confirmation;

			if ($_POST['disable']) // disable plugin
			{
				bb_delete_option('swirl_page');
				bb_delete_option('another_allowed_page'); // not really needed, but it's better to remove any trace of the plugin
				$swirl_confirmation = "Plugin disabled";
			}
			else // update swirl page
			{
				global $default_swirl_page;
				$swirl_page = ($_POST['swirl_page']) ?
													$_POST['swirl_page'] :
													$default_swirl_page;

				bb_update_option('swirl_page', $swirl_page);
				$swirl_confirmation = "Page updated";
			}
		}
		elseif ($_POST['action'] == 'allow_page') // request coming from "allow page" form
		{
			global $aap_confirmation;

			if ($_POST['another_allowed_page']) // add another allowed page
			{
				bb_update_option('another_allowed_page', $_POST['another_allowed_page']);
				$aap_confirmation = "Page added";
			}
			else // remove another allowed page
			{
				bb_delete_option('another_allowed_page');
				$aap_confirmation = "Page removed";
			}
		}
	}
}


/**
 * Redirect non-logged-in users to "swirl page", if the option is defined
 **/
function swirl_unknowns() {
	if ($swirl_page = bb_get_option('swirl_page')) // plugin is active
	{
		$path = bb_get_option('path');
		$server_uri = $_SERVER['REQUEST_URI'];

		if (!bb_is_user_logged_in()
			&& strcasecmp($server_uri, $path.'bb-login.php') != 0
			&& strcasecmp($server_uri, $path.'bb-reset-password.php') != 0
			&& strcasecmp($server_uri, $path.'register.php') != 0
			&& strcasecmp($server_uri, $path.'xmlrpc.php') != 0
			&& strcasecmp($server_uri, $path.'bb-admin/') != 0
			&& strcasecmp($server_uri, $path."bb-login.php?re=".$path."bb-admin/") != 0
			&& strcasecmp($server_uri, bb_get_option('another_allowed_page')) != 0 // no problem even if the option is not defined, as `$server_uri` will never be NULL
			&& strcasecmp($server_uri, $swirl_page) != 0 // user is on swirl page
			)
		{
			nocache_headers();
			header("HTTP/1.1 302 Moved Temporarily");
			bb_safe_redirect($swirl_page);
			header("Status: 302 Moved Temporarily");
			exit();
		}
		else ; // user is requesting an allowed page
	}
	else ; // plugin disabled
}


/*
 * Add bbPress actions
 */
add_action('bb_admin_menu_generator', 'swirl_unknowns_add_admin_page');
add_action('bb_admin-header.php', 'swirl_unknowns_admin_page_process');
add_action('bb_init', 'swirl_unknowns');
?>