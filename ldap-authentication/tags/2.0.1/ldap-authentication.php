<?php
/*
Plugin Name: LDAP authentication
Plugin URI: http://bbpress.org/plugins/topic/26
Description: Allows users to authenticate against an LDAP service
Author: Sam Bauers
Version: 2.0.1
Author URI: 

Version History:
1.0 	: Initial Release
1.0.1 	: Small non-critical fixes to ldap_remove_password_capability()
1.0.2	: Cookie hacking vulnerability fixed
		  Disabled password reseting function for LDAP users
		  Added option to disable automatic registration of LDAP users
1.0.3	: Added option to retrieve LDAP users email address on registration
1.0.4	: Added support for new admin menu structure introduced in build 740
2.0		: Moved most functions into a class
		  Amalgamated options into new serialized options
		  Fixed issues with enabling disabling features when using permalinks
		  Added support for bb_admin_add_submenu()
2.0.1	: Made PHP4 compatible
*/


/**
 * LDAP authentication for bbPress version 2.0.1
 * 
 * ----------------------------------------------------------------------------------
 * 
 * Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)
 * 
 * ----------------------------------------------------------------------------------
 * 
 * LICENSE:
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * ----------------------------------------------------------------------------------
 * 
 * PHP version 4 and 5
 * 
 * ----------------------------------------------------------------------------------
 * 
 * @author    Sam Bauers <sam@viveka.net.au>
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   2.0.1
 **/


/**
 * Container class for LDAP authentication
 *
 * @author  Sam Bauers
 * @version 1.0.1
 **/
class LDAP_Authentication
{
	/**
	 * The current version of the plugin
	 *
	 * @var string
	 **/
	var $version = '2.0.1';
	
	
	/**
	 * Whether the plugin is enabled
	 *
	 * @var boolean
	 **/
	var $enabled;
	
	
	/**
	 * An array of settings for the LDAP server connection
	 *
	 * @var array
	 **/
	var $server;
	
	
	/**
	 * Whether the plugin has enough settings to work
	 *
	 * @var boolean
	 **/
	var $active = false;
	
	
	/**
	 * Additional plugin options in an array
	 *
	 * @var array
	 **/
	var $options;
	
	
	/**
	 * Pulls out database settings
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function LDAP_Authentication()
	{
		// An integer set to 1 for enabled or 0 for disabled
		$this->enabled = bb_get_option('ldap_authentication_enabled');
		
		$this->server = bb_get_option('ldap_authentication_server');
		
		if (isset($this->server['host']) && isset($this->server['domain'])) {
			$this->active = true;
		}
		
		$this->options = bb_get_option('ldap_authentication_options');
	}
	
	
	/**
	 * Returns whether the plugin is active or not
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function isActive()
	{
		if ($this->enabled && $this->active) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Determines whether we are viewing the given page
	 *
	 * Mostly adapted from bb_get_location();
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function locationIs($page)
	{
		$names = array(
			$_SERVER['PHP_SELF'],
			$_SERVER['SCRIPT_FILENAME'],
			$_SERVER['SCRIPT_NAME']
		);
		
		foreach ($names as $name) {
			if (false !== strpos($name, '.php')) {
				$file = $name;
			}
		}
		
		if (bb_find_filename($file) == $page) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Disables standard registration
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function disableRegistration()
	{
		if ($this->isActive() && $this->options['disable_registration'] && $this->locationIs('register.php')) {
			bb_die(__('Registration is disabled for this forum, please login using your LDAP username and password.'));
		}
	}
	
	
	/**
	 * Disables password recovery for users who have LDAP passwords
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function disableLDAPpasswordRecovery()
	{
		if ($this->isActive() && $this->locationIs('bb-reset-password.php')) {
			$user_login = user_sanitize($_POST['user_login']);
			if (!empty($user_login)) {
				$user = bb_get_user_by_name($user_login);
				if (substr($user->user_pass, 0, 5) == '^LDAP') {
					bb_die(__('Password recovery is not possible for this account because it uses an LDAP username and password to login. To change your LDAP password, please contact your system administrator.'));
				}
			}
		}
	}
	
	
	/**
	 * Disables password editing for users who have LDAP passwords
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function disableLDAPpasswordEditing()
	{
		global $bb_current_user;
		
		if ($this->isActive() && ($this->locationIs('profile.php') || $this->locationIs('profile-edit.php'))) {
			if (substr($bb_current_user->data->user_pass, 0, 5) == '^LDAP') {
				add_filter('bb_user_has_cap', array($this, 'removePasswordCapability'), 10, 2);
			}
		}
	}
	
	
	/**
	 * Removes the change password capability for the current user
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function removePasswordCapability($allcaps, $caps)
	{
		if ($caps[0] == 'change_password') {
			unset($allcaps['change_password']);
		}
		
		return $allcaps;
	}
	
	
	/**
	 * Replacement for bb_check_login
	 *
	 * @return object
	 * @author Sam Bauers
	 **/
	function checkLogin($user, $pass, $already_md5 = false)
	{
		global $bbdb;
		
		$user = user_sanitize($user);
		
		if (!$already_md5) {
			$user_exists = bb_user_exists($user);
			if (!$user_exists) {
				// Check using LDAP
				if (!$this->options['disable_automatic_ldap_registration'] && $mail = $this->connectUser($user, $pass, true)) {
					// Create the new user in the local database
					if ($user_id = $this->newUser($user, $pass, $mail)) {
						return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE `ID` = $user_id");
					} else {
						bb_die(__('Failed to add new LDAP user to local database.'));
					}
				} else {
					return false;
				}
			} else {
				if (substr($user_exists->user_pass, 0, 5) == '^LDAP') {
					if ($this->connectUser($user, $pass)) {
						// Update their MD5 hash in case their password has changed
						$bbdb->query("UPDATE $bbdb->users SET user_pass = '^LDAP-" . md5($pass) . "' WHERE user_login = '$user'");
						
						// Get their record from the local database
						return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING(SUBSTRING_INDEX(user_pass, '---', 1), 1, 5) = '^LDAP'");
					} else {
						return false;
					}
				} else {
					$pass = user_sanitize(md5($pass));
					return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING_INDEX(user_pass, '---', 1) = '$pass'");
				}
			}
		} else {
			return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND MD5( user_pass ) = '$pass'");
		}
	}
	
	
	/**
	 * Connects the user to the LDAP server
	 *
	 * @return mixed
	 * @author Sam Bauers
	 **/
	function connectUser($user, $pass, $register = false)
	{
		if ($this->server['port']) {
			$connection = ldap_connect($this->server['host'], $this->server['port']);
		} else {
			$connection = ldap_connect($this->server['host']);
		}
		
		if ($this->server['options']) {
			$options = explode('|', $this->server['options']);
			foreach ($options as $option) {
				$optionParts = explode(':', $option);
				ldap_set_option($connection, $optionParts[0], $optionParts[1]);
			}
		}
		
		if ($this->server['tls']) {
			// TLS requires ldap protocol version 3
			ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			$tlsStart = ldap_start_tls($connection);
		} else {
			$tlsStart = true;
		}
		
		if ($connection && $tlsStart) {
			$uid = 'uid=' . stripslashes(addslashes($user));
			$bindString = $uid . ',' . $this->server['domain'];
			
			$result = ldap_bind($connection, $bindString, stripslashes(addslashes($pass)));
			
			if ($result) {
				$mail = true;
				
				if ($register && $this->options['enable_email_retrieval_from_ldap']) {
					if ($search = ldap_search($connection, $this->server['domain'], '(' . $uid . ')', array('mail'))) {
						if ($entry = ldap_first_entry($connection, $search)) {
							$attributes = ldap_get_attributes($connection, $entry);
							$mail = $attributes['mail'][0];
						}
					}
				}
				
				ldap_unbind($result);
				ldap_close($connection);
				
				return $mail;
			} else {
				ldap_close($connection);
				return FALSE;
			}
		} else {
			bb_die(__('Could not connect to LDAP authentication service.'));
		}
	}
	
	
	/**
	 * Creates a new user with an LDAP password
	 *
	 * @return integer
	 * @author Sam Bauers
	 **/
	function newUser($user_login, $user_pass, $user_email)
	{
		global $bbdb;
		global $bb_table_prefix;
		
		$now = bb_current_time('mysql');
		$password = '^LDAP-' . md5($user_pass);
		
		if ($user_email !== true) {
			$email = $user_email;
		}
		
		$bbdb->query("INSERT INTO $bbdb->users (user_login, user_pass, user_email, user_registered) VALUES ('$user_login', '$password', '$email', '$now')");
		$user_id = $bbdb->insert_id;
		
		bb_update_usermeta($user_id, $bb_table_prefix . 'capabilities', array('member' => true));
		
		do_action('ldap_authentication_new_user', $user_id);
		
		return $user_id;
	}
} // END class LDAP_Authentication


// Initialise the class
$ldap_authentication = new LDAP_Authentication();


// If active, then add filters via API
if ($ldap_authentication->isActive()) {
	add_action('bb_init', array($ldap_authentication, 'disableRegistration'));
	add_action('bb_init', array($ldap_authentication, 'disableLDAPpasswordRecovery'));
	add_action('bb_init', array($ldap_authentication, 'disableLDAPpasswordEditing'));
	
	
	/**
	 * Alias that hooks into LDAP_Authentication class checkLogin function
	 *
	 * This is a pluggable function, so it must sit outside the class like this
	 *
	 * @return object
	 * @author Sam Bauers
	 **/
	function bb_check_login($user, $pass, $already_md5 = false)
	{
		global $ldap_authentication;
		return $ldap_authentication->checkLogin($user, $pass, $already_md5);
	}
}


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'ldap_authentication_admin_page_add');
add_action('bb_admin-header.php','ldap_authentication_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function ldap_authentication_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('LDAP authentication'), 'use_keys', 'ldap_authentication_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('LDAP authentication'), 'use_keys', 'ldap_authentication_admin_page');
		if (isset($bb_submenu['plugins.php'])) { // Build 740-793
			$bb_submenu['plugins.php'][] = $submenu;
		} else { // Build 277-739
			$bb_submenu['site.php'][] = $submenu;
		}
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return string
 * @author Sam Bauers
 **/
function ldap_authentication_admin_page() {
	$enabled = bb_get_option('ldap_authentication_enabled');
	$options = bb_get_option('ldap_authentication_options');
	$server = bb_get_option('ldap_authentication_server');
	
	if ($enabled) {
		$enabled_checked = ' checked="checked"';
	}
	if ($options['enable_email_retrieval_from_ldap']) {
		$enable_email_retrieval_from_ldap_checked = ' checked="checked"';
	}
	if ($options['disable_automatic_ldap_registration']) {
		$disable_automatic_ldap_registration_checked = ' checked="checked"';
	}
	if ($options['disable_registration']) {
		$disable_registration_checked = ' checked="checked"';
	}
	if ($server['tls']) {
		$tls_checked = ' checked="checked"';
	}
?>
	<h2>LDAP authentication</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>
		Enable LDAP authentication here:
	</p>
	<p>
		<input type="checkbox" name="ldap_authentication_enabled" value="1" tabindex="10"<?php echo $enabled_checked; ?> /> Enable LDAP authentication<br />
		&nbsp;
	</p>
	<h3>Retrieve email address when LDAP users register</h3>
	<p>
		If checked, the LDAP registration process will attempt to retrieve the LDAP
		users email address from the LDAP repository:
	</p>
	<p>
		<input type="checkbox" name="ldap_authentication_enable_email_retrieval_from_ldap" value="1" tabindex="20"<?php echo $enable_email_retrieval_from_ldap_checked; ?> /> Retrieve email address when LDAP users register<br />
		&nbsp;
	</p>
	<h3>Disable automatic registration of LDAP users</h3>
	<p>
		In normal use, LDAP users are registered in bbPress on their first successful
		login. If automatic registration is disabled here, then LDAP users will have
		to be added manually in the database, they cannot be created through the normal
		registration process:
	</p>
	<p>
		<input type="checkbox" name="ldap_authentication_disable_automatic_ldap_registration" value="1" tabindex="20"<?php echo $disable_automatic_ldap_registration_checked; ?> /> Disable automatic registration of LDAP users<br />
		&nbsp;
	</p>
	<h3>Disable normal registration</h3>
	<p>
		This will disable the normal registration page. Non-LDAP users are still
		allowed to login normally with this option activated:
	</p>
	<p>
		<input type="checkbox" name="ldap_authentication_disable_registration" value="1" tabindex="30"<?php echo $disable_registration_checked; ?> /> Disable normal registration<br />
		&nbsp;
	</p>
	<h3>Server settings</h3>
	<p>
		Specify LDAP server settings here:
	</p>
	<table>
		<tr>
			<th scope="row">Host:</th>
			<td><input type="text" name="ldap_authentication_host" tabindex="40" value="<?php echo $server['host']; ?>" /> required</td>
		</tr>
		<tr>
			<th scope="row">Port:</th>
			<td><input type="text" name="ldap_authentication_port" tabindex="50" value="<?php echo $server['port']; ?>" /> defaults to 389</td>
		</tr>
		<tr>
			<th scope="row">Domain:</th>
			<td><input type="text" name="ldap_authentication_domain" tabindex="60" value="<?php echo $server['domain']; ?>" /> required</td>
		</tr>
		<tr>
			<th scope="row">TLS:</th>
			<td><input type="checkbox" name="ldap_authentication_tls" value="1" tabindex="70"<?php echo $tls_checked; ?> /> use TLS encryption to connect (sets LDAP protocol to version 3)</td>
		</tr>
		<tr>
			<th scope="row">Options:</th>
			<td><input type="text" name="ldap_authentication_options" tabindex="80" value="<?php echo $server['options']; ?>" /> e.g.: option1:value1|option2:value2|...</td>
		</tr>
	</table>
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
		<input type="hidden" name="action" value="ldap_authentication_update" />
	</p>
	</form>
<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 **/
function ldap_authentication_admin_page_process() {
	if (isset($_POST['submit'])) {
		if ('ldap_authentication_update' == $_POST['action']) {
			// Enable LDAP
			if ($_POST['ldap_authentication_enabled']) {
				bb_update_option('ldap_authentication_enabled', $_POST['ldap_authentication_enabled']);
			} else {
				bb_delete_option('ldap_authentication_enabled');
			}
			
			// Set an empty options array
			$options = array();
			
			// Enable email retrieval from LDAP
			if ($_POST['ldap_authentication_enable_email_retrieval_from_ldap']) {
				$options['enable_email_retrieval_from_ldap'] = $_POST['ldap_authentication_enable_email_retrieval_from_ldap'];
			}
			
			// Disable automatic LDAP registration
			if ($_POST['ldap_authentication_disable_automatic_ldap_registration']) {
				$options['disable_automatic_ldap_registration'] = $_POST['ldap_authentication_disable_automatic_ldap_registration'];
			}
			
			// Disable normal registration
			if ($_POST['ldap_authentication_disable_registration']) {
				$options['disable_registration'] = $_POST['ldap_authentication_disable_registration'];
			}
			
			// Save or delete the options
			if (count($options)) {
				bb_update_option('ldap_authentication_options', $options);
			} else {
				bb_delete_option('ldap_authentication_options');
			}
			
			// Set an empty server array
			$server = array();
			
			// Host
			if ($_POST['ldap_authentication_host']) {
				$server['host'] = $_POST['ldap_authentication_host'];
			}
			
			// Port
			if ($_POST['ldap_authentication_port']) {
				$server['port'] = $_POST['ldap_authentication_port'];
			}
			
			// Domain
			if ($_POST['ldap_authentication_domain']) {
				$server['domain'] = $_POST['ldap_authentication_domain'];
			}
			
			// TLS
			if ($_POST['ldap_authentication_tls']) {
				$server['tls'] = $_POST['ldap_authentication_tls'];
			}
			
			// Host
			if ($_POST['ldap_authentication_host']) {
				$server['options'] = $_POST['ldap_authentication_options'];
			}
			
			// Save or delete the server
			if (count($server)) {
				bb_update_option('ldap_authentication_server', $server);
			} else {
				bb_delete_option('ldap_authentication_server');
			}
		}
	}
}
?>