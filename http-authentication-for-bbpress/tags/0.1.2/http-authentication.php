<?php
/*
Plugin Name: HTTP authentication
Plugin URI: http://www.network.net.au/bbpress/plugins/http/http-authentication.latest.zip
Description: Allows users to authenticate using basic or digest HTTP authentication
Author: Sam Bauers
Version: 0.1.2
Author URI: http://www.network.net.au/

Version History:
0.1 	: Initial Release - based on LDAP Authentication plugin
0.1.1	: Removed need to login twice
0.1.2	: Added function to call bb_login with null arguments
*/

/*
HTTP authentication for bbPress version 0.1.2
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

add_action( 'bb_admin_menu_generator', 'http_add_admin_page' );
add_action( 'bb_admin-header.php','http_admin_page_process');

$HTTP_enabled = bb_get_option('HTTP_enable');

function http_add_admin_page() {
	global $bb_submenu;
	
	$bb_submenu['plugins.php'][] = array(__('HTTP authentication'), 'use_keys', 'http_admin_page');
}

function http_admin_page() {
	if ( bb_get_option('HTTP_enable') ) {
		$enable_checked = ' checked="checked"';
	}
	if ( bb_get_option('HTTP_disable_automatic_registration') ) {
		$disable_automatic_registration_checked = ' checked="checked"';
	}
	if ( bb_get_option('HTTP_disable_registration') ) {
		$disable_registration_checked = ' checked="checked"';
	}
?>
	<h2>HTTP authentication</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>
		Enable HTTP authentication here. You will also need to add HTTP authentication directives to your webserver configuration:
	</p>
	<p>
		<input type="checkbox" name="HTTP_enable" value="1" tabindex="10"<?php echo $enable_checked; ?> /> Enable HTTP authentication<br />
		&nbsp;
	</p>
	<h3>Disable automatic registration of HTTP users</h3>
	<p>
		In normal use, HTTP users are registered in bbPress on their first successful
		login. If automatic registration is disabled here, then HTTP users will have
		to be added manually in the database, they cannot be created through the normal
		registration process:
	</p>
	<p>
		<input type="checkbox" name="HTTP_disable_automatic_registration" value="1" tabindex="20"<?php echo $disable_automatic_registration_checked; ?> /> Disable automatic registration of HTTP users<br />
		&nbsp;
	</p>
	<h3>Disable normal registration</h3>
	<p>
		This will disable the normal registration page:
	</p>
	<p>
		<input type="checkbox" name="HTTP_disable_registration" value="1" tabindex="30"<?php echo $disable_registration_checked; ?> /> Disable normal registration<br />
		&nbsp;
	</p>
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
		<input type="hidden" name="action" value="HTTP_update" />
	</p>
	</form>
<?php
}

function http_admin_page_process() {
	if ( isset( $_POST['submit'] ) ) {
		if ('HTTP_update' == $_POST['action']) {
			// Enable HTTP
			if ( $_POST['HTTP_enable'] ) {
				bb_update_option( 'HTTP_enable', $_POST['HTTP_enable'] );
			} else {
				bb_delete_option('HTTP_enable');
			}
			
			// Disable automatic registration
			if ( $_POST['HTTP_disable_automatic_registration'] ) {
				bb_update_option( 'HTTP_disable_automatic_registration', $_POST['HTTP_disable_automatic_registration'] );
			} else {
				bb_delete_option('HTTP_disable_automatic_registration');
			}
			
			// Disable normal registration
			if ( $_POST['HTTP_disable_registration'] ) {
				bb_update_option( 'HTTP_disable_registration', $_POST['HTTP_disable_registration'] );
			} else {
				bb_delete_option('HTTP_disable_registration');
			}
		}
	}
}

if ($HTTP_enabled) {
	
	add_action('bb_init', 'http_disable_registration');
	add_action('bb_init', 'http_disable_http_password_recovery');
	add_action('bb_init', 'http_disable_password_editing');
	
	function http_disable_registration() {
		global $HTTP_enabled, $bb;
		if ($HTTP_enabled && bb_get_option('HTTP_disable_registration') && $_SERVER['PHP_SELF'] == $bb->path . 'register.php') {
			bb_die(__('Registration is disabled for this forum, please login using your HTTP username and password.'));
		}
	}
	
	function http_disable_http_password_recovery() {
		global $HTTP_enabled, $bb;
		if ($HTTP_enabled && $_SERVER['PHP_SELF'] == $bb->path . 'bb-reset-password.php') {
			$user_login = user_sanitize($_POST['user_login']);
			if (!empty($user_login)) {
				$user = bb_get_user_by_name($user_login);
				if (substr($user->user_pass, 0, 5) == '^HTTP') {
					bb_die(__('Password recovery is not possible for this account because it uses an HTTP username and password to login. To change your HTTP password, please contact your system administrator.'));
				}
			}
		}
	}
	
	function http_disable_password_editing() {
		global $HTTP_enabled, $bb, $bb_current_user;
		if ($HTTP_enabled && (($_SERVER['PHP_SELF'] == $bb->path . 'profile.php' && $_GET['tab'] == 'edit') || $_SERVER['PHP_SELF'] == $bb->path . 'profile-edit.php')) {
			add_filter( 'bb_user_has_cap' , 'http_remove_password_capability' , 10, 2);
		}
	}
	
	function http_remove_password_capability($allcaps, $caps) {
		global $user;
		
		if ($caps[0] == 'change_password' && substr($user->user_pass, 0, 5) == '^HTTP') {
			unset($allcaps['change_password']);
		}
		
		return $allcaps;
	}
	
	function bb_check_login($user = null, $pass = null, $already_md5 = false) {
		global $bbdb;
		if (!$user) {
			$user = $_SERVER['PHP_AUTH_USER'];
		}
		if (!$pass) {
			$pass = $_SERVER['PHP_AUTH_PW'];
		}
		$user = user_sanitize( $user );
		if ( !$already_md5 ) {
			$user_exists = bb_user_exists( $user );
			if ( !$user_exists ) {
				// Check using HTTP
				if ( !bb_get_option('HTTP_disable_automatic_registration') ) {
					// Create the new user in the local database
					if ( $user_id = http_new_user( $user, $pass ) ) {
						return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE `ID` = $user_id");
					} else {
						bb_die(__('Failed to add new HTTP user to local database.'));
					}
				} else {
					return FALSE;
				}
			} else {
				if ( substr($user_exists->user_pass, 0, 5) == '^HTTP' ) {
					$bbdb->query("UPDATE $bbdb->users SET user_pass = '^HTTP-" . md5($pass) . "' WHERE user_login = '$user'");
					
					// Get their record from the local database
					return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING(SUBSTRING_INDEX( user_pass, '---', 1 ), 1, 5) = '^HTTP'");
				} else {
					$pass = user_sanitize( md5( $pass ) );
					return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING_INDEX( user_pass, '---', 1 ) = '$pass'");
				}
			}
		} else {
			return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND MD5( user_pass ) = '$pass'");
		}
	}
	
	function http_new_user($user_login, $user_pass) {
		global $bbdb, $bb_table_prefix;
		$now       = bb_current_time('mysql');
		$password  = '^HTTP-' . md5($user_pass);
		
		$bbdb->query("INSERT INTO $bbdb->users (user_login, user_pass, user_registered) VALUES ('$user_login', '$password', '$now')");
		
		$user_id = $bbdb->insert_id;
		
		bb_update_usermeta( $user_id, $bb_table_prefix . 'capabilities', array('member' => true) );
		do_action('http_new_user', $user_id);
		return $user_id;
	}
	
	function http_login_init() {
		return bb_login(null, null);
	}
	
	add_action( 'bb_got_roles', 'http_login_init' );
}
?>