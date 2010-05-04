<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Non-logged-in users get redirected to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Version: 0.5
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/


// global vars
$su_default_redir_page = bb_get_option('path')."index.php";
$redir_confirmation = '';
$allow_confirmation = '';


/**
 * Add admin page
 **/
function su_add_admin_page()
{
	bb_admin_add_submenu(__('Swirl Unknowns'), 'use_keys', 'su_admin_page');
}


/**
 * Admin page
 **/
function su_admin_page()
{
	global $su_default_redir_page;
	global $redir_confirmation;
	global $allow_confirmation;
	$path = bb_get_option('path');
?>
	<link rel="stylesheet" href="<?php echo bb_get_plugin_uri(bb_plugin_basename(__FILE__)) ?>swirl-unknowns.css" type="text/css" />
	<h2><?php _e('Swirl Unknowns'); ?></h2>
	<div id="container">
	<h3><?php _e('Enter the address of the page non-logged-in users will be redirected to'); ?></h3>
	<form method="post" id="redir_form">
	<fieldset>
		<label for="redir_page"><?php _e('Page address'); ?></label>
		<input type="text" name="redir_page" id="redir_page" value="<?php echo bb_get_option('su_redir_page') ?>" /><br /><br />
		<label for="disable"><?php _e('Disable plugin?'); ?></label> <input type="checkbox" name="disable" id="disable" />
		<input type="submit" name="submit" id="submit" value="Update" /><span id="redir_confirmation"><?php echo $redir_confirmation ?></span>
		<input type="hidden" name="action" value="redir" />
	</fieldset>
	</form>
	<br />
	<strong><?php _e('Notes'); ?></strong>
	<ul class="notes">
		<li><?php printf('Example (no permalinks): <code>%s</code>.', $path."topic.php?id=1"); ?></li>
		<li><?php printf('Example (numeric permalinks activated): <code>%s</code>.', $path."topic/1"); ?></li>
		<li><?php printf('Example (name based permalinks activated): <code>%s</code>.', $path."topic/topic-name"); ?></li>
		<li><?php _e('You may also enter <code>/custom-page.php</code> to redirect out of the forum.'); ?></li>
		<li><?php printf('If no address is entered, default page (<code>%s</code>) will be used.', $su_default_redir_page); ?></li>
	</ul>
	<br />
	<h3><?php _e('Allowed pages'); ?></h3>
	<ul class="allowed_pages">
		<li><?php _e('bb-login.php'); ?></li>
		<li><?php _e('bb-reset-password.php'); ?></li>
		<li><?php _e('register.php'); ?></li>
		<li><?php _e('xmlrpc.php'); ?></li>
		<li><?php printf('%s', $path."bb-admin/"); ?></li>
		<li><form method="post" id="allow_form">
			<input type="text" name="allowed_page" id="allowed_page" value="<?php echo bb_get_option('$su_allowed_page') ?>" />
			<span id="allow_confirmation"><?php echo $allow_confirmation ?></span>
			<input type="hidden" name="action" value="allow_page" />
		</form></li>
	</ul>
	<br />
	<strong><?php _e('Notes'); ?></strong>
	<ul class="notes">
		<li><?php _e('Allowed page address follows the rules above.'); ?></li>
		<li><?php _e('If no address is entered, last entered page will be removed from allowed pages list.'); ?></li>
	</ul>
	</div>
<?php
}


/**
 * Process admin page
 **/
function su_admin_page_process()
{
	if ($_POST)
	{
		if ($_POST['action'] == 'redir')
		{
			global $redir_confirmation;

			if ($_POST['disable']) // disable plugin
			{
				bb_delete_option('su_redir_page');
				bb_delete_option('su_allowed_page'); // not really needed, but it's better to remove any trace of the plugin
				$redir_confirmation = "Plugin disabled";
			}
			else // update redirection page
			{
				global $su_default_redir_page;

				// if $_POST['redir_page'] is not defined, use default redirection page
				$redir_page = ($_POST['redir_page']) ? $_POST['redir_page'] : $su_default_redir_page;

				bb_update_option('su_redir_page', $redir_page);
				$redir_confirmation = "Page updated";
			}
		}
		elseif ($_POST['action'] == 'allow')
		{
			global $allow_confirmation;

			if ($_POST['allowed_page']) // update allowed page
			{
				bb_update_option('su_allowed_page', $_POST['allowed_page']);
				$allow_confirmation = "Page added";
			}
			else // remove allowed page
			{
				bb_delete_option('su_allowed_page');
				$allow_confirmation = "Page removed";
			}
		}
	}
}


/**
 * Redirection function
 **/
function swirl_unknowns()
{
	if ($redir_page = bb_get_option('su_redir_page')) // plugin is active
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
			&& strcasecmp($server_uri, bb_get_option('su_allowed_page')) != 0 // no problem if the option is not defined
			&& strcasecmp($server_uri, $redir_page) != 0 // user is already on the redirection page
			)
		{
			nocache_headers();
			bb_safe_redirect($redir_page);
			exit();
		}
		else ; // user is requesting an allowed page
	}
	else ; // plugin is disabled
}


/*
 * Add bbPress actions
 */
add_action('bb_admin_menu_generator', 'su_add_admin_page');
add_action('bb_admin-header.php', 'su_admin_page_process');
add_action('bb_init', 'swirl_unknowns');
?>