<?php
/*
Plugin Name: LDAP authentication
Plugin URI: http://www.network.net.au/bbpress/plugins/ldap/ldap-authentication.latest.zip
Description: Allows users to authenticate against an LDAP service
Author: Sam Bauers
Version: 1.0.1
Author URI: http://www.network.net.au/

Version History:
1.0 	: Initial Release
1.0.1 	: Small non-critical fixes to ldap_remove_password_capability()
*/

/*
LDAP authentication for bbPress version 1.0
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

add_action( 'bb_admin_menu_generator', 'ldap_add_admin_page' );
add_action( 'bb_admin-header.php','ldap_admin_page_process');

$LDAP_enabled = bb_get_option('LDAP_enable');

function ldap_add_admin_page() {
	global $bb_submenu;
	
	$bb_submenu['site.php'][] = array(__('LDAP authentication'), 'use_keys', 'ldap_admin_page');
}

function ldap_admin_page() {
	if ( bb_get_option('LDAP_enable') ) {
		$enable_checked = ' checked="checked"';
	}
	if ( bb_get_option('LDAP_disable_registration') ) {
		$disable_registration_checked = ' checked="checked"';
	}
	if ( bb_get_option('LDAP_tls') ) {
		$tls_checked = ' checked="checked"';
	}
?>
	<h2>LDAP authentication</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>
		Enable LDAP authentication here, all users who can authenticate against the
		LDAP server specified below will automatically be able to login to the forums
		as a member. The first time an LDAP user logs in, a local user is created for
		them, no password is sent to them:
	</p>
	<p>
		<input type="checkbox" name="LDAP_enable" value="1" tabindex="10"<?php echo $enable_checked; ?> /> Enable LDAP authentication<br />
		&nbsp;
	</p>
	<h3>Disable normal registration</h3>
	<p>
		This will disable the normal registration page. Non-LDAP users are still
		allowed to login normally with this option activated:
	</p>
	<p>
		<input type="checkbox" name="LDAP_disable_registration" value="1" tabindex="20"<?php echo $disable_registration_checked; ?> /> Disable normal registration<br />
		&nbsp;
	</p>
	<h3>Server settings</h3>
	<p>
		Specify LDAP server settings here:
	</p>
	<table>
		<tr>
			<th scope="row">Host:</th>
			<td><input type="text" name="LDAP_host" tabindex="30" value="<?php echo bb_get_option('LDAP_host'); ?>" /> required</td>
		</tr>
		<tr>
			<th scope="row">Port:</th>
			<td><input type="text" name="LDAP_port" tabindex="40" value="<?php echo bb_get_option('LDAP_port'); ?>" /> defaults to 389</td>
		</tr>
		<tr>
			<th scope="row">Domain:</th>
			<td><input type="text" name="LDAP_domain" tabindex="60" value="<?php echo bb_get_option('LDAP_domain'); ?>" /> required</td>
		</tr>
		<tr>
			<th scope="row">TLS:</th>
			<td><input type="checkbox" name="LDAP_tls" value="1" tabindex="50"<?php echo $tls_checked; ?> /> use TLS encryption to connect (sets LDAP protocol to version 3)</td>
		</tr>
		<tr>
			<th scope="row">Options:</th>
			<td><input type="text" name="LDAP_options" tabindex="70" value="<?php echo bb_get_option('LDAP_options'); ?>" /> e.g.: option1:value1|option2:value2|...</td>
		</tr>
	</table>
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="80" />
		<input type="hidden" name="action" value="LDAP_update" />
	</p>
	</form>
<?php
}

function ldap_admin_page_process() {
	if ( isset( $_POST['submit'] ) ) {
		if ('LDAP_update' == $_POST['action']) {
			// Enable LDAP
			if ( $_POST['LDAP_enable'] ) {
				bb_update_option( 'LDAP_enable', $_POST['LDAP_enable'] );
			} else {
				bb_delete_option('LDAP_enable');
			}
			// Disable normal registration
			if ( $_POST['LDAP_disable_registration'] ) {
				bb_update_option( 'LDAP_disable_registration', $_POST['LDAP_disable_registration'] );
			} else {
				bb_delete_option('LDAP_disable_registration');
			}
			
			// Host
			if ( $_POST['LDAP_host'] ) {
				bb_update_option( 'LDAP_host', $_POST['LDAP_host'] );
			} else {
				bb_delete_option('LDAP_host');
			}
			
			// Port
			if ( $_POST['LDAP_port'] ) {
				bb_update_option( 'LDAP_port', $_POST['LDAP_port'] );
			} else {
				bb_delete_option('LDAP_port');
			}
			
			// Domain
			if ( $_POST['LDAP_domain'] ) {
				bb_update_option( 'LDAP_domain', $_POST['LDAP_domain'] );
			} else {
				bb_delete_option('LDAP_domain');
			}
			
			// TLS
			if ( $_POST['LDAP_tls'] ) {
				bb_update_option( 'LDAP_tls', $_POST['LDAP_tls'] );
			} else {
				bb_delete_option('LDAP_tls');
			}
			
			// Options
			if ( $_POST['LDAP_options'] ) {
				bb_update_option( 'LDAP_options', $_POST['LDAP_options'] );
			} else {
				bb_delete_option('LDAP_options');
			}
		}
	}
}

if ($LDAP_enabled) {
	
	add_action( 'bb_init','ldap_disable_registration');
	add_action( 'bb_init','ldap_disable_password_editing');
	
	function ldap_disable_registration() {
		global $LDAP_enabled, $bb;
		if ($LDAP_enabled && bb_get_option('LDAP_disable_registration') && $_SERVER['PHP_SELF'] == $bb->path . 'register.php') {
			bb_die(__('Registration is disabled for this forum, please login using your LDAP username and password.'));
		}
	}
	
	function ldap_disable_password_editing() {
		global $LDAP_enabled, $bb, $bb_current_user;
		
		if ($LDAP_enabled && (($_SERVER['PHP_SELF'] == $bb->path . 'profile.php' && $_GET['tab'] == 'edit') || $_SERVER['PHP_SELF'] == $bb->path . 'profile-edit.php')) {
			add_filter( 'bb_user_has_cap' , 'ldap_remove_password_capability' , 10, 2);
		}
	}
	
	function ldap_remove_password_capability($allcaps, $caps) {
		global $user;
		
		if ($caps[0] == 'change_password' && substr($user->user_pass, 0, 5) == '^LDAP') {
			unset($allcaps['change_password']);
		}
		
		return $allcaps;
	}
	
	function bb_check_login($user, $pass, $already_md5 = false) {
		global $bbdb;
		$user = user_sanitize( $user );
		if ( !$already_md5 ) {
			$user_exists = bb_user_exists( $user );
			if ( !$user_exists ) {
				// Check using LDAP
				if ( ldap_connect_user( $user, $pass ) ) {
					// Create the new user in the local database
					if ( $user_id = ldap_new_user( $user ) ) {
						return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE `ID` = $user_id");
					} else {
						bb_die(__('Failed to add new LDAP user to local database.'));
					}
				
				} else {
					return FALSE;
				}
			} else {
				if ( substr($user_exists->user_pass, 0, 5) == '^LDAP' ) {
					if ( ldap_connect_user( $user, $pass ) ) {
						// Get their record from the local database
						return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING_INDEX( user_pass, '---', 1 ) = '^LDAP'");
					} else {
						return FALSE;
					}
				} else {
					$pass = user_sanitize( md5( $pass ) );
					return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND SUBSTRING_INDEX( user_pass, '---', 1 ) = '$pass'");
				}
			}
		} else {
			return $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_login = '$user' AND MD5( user_pass ) = '$pass'");
		}
	}
	
	function ldap_connect_user($user, $pass) {
		$host = bb_get_option('LDAP_host');
		$port = bb_get_option('LDAP_port');
		$tls = bb_get_option('LDAP_tls');
		$domain = bb_get_option('LDAP_domain');
		$options = bb_get_option('LDAP_options');
		
		if ($port) {
			$connection = ldap_connect($host, $port);
		} else {
			$connection = ldap_connect($host);
		}
		
		if ($options) {
			$options = explode('|', $options);
			foreach ($options as $option) {
				$optionParts = explode(':', $option);
				ldap_set_option($connection, $optionParts[0], $optionParts[1]);
			}
		}
		
		if ($tls) {
			// TLS requires ldap protocol version 3
			ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
			$tlsStart = ldap_start_tls($connection);
		} else {
			$tlsStart = TRUE;
		}
		
		if ($connection && $tlsStart) {
			$bindString = 'uid=' . stripslashes(addslashes($user)) . ',' . $domain;
			
			$result = ldap_bind($connection,$bindString, stripslashes(addslashes($pass)));
			
			if ($result) {
				ldap_close($connection);
				return TRUE;
			} else {
				ldap_close($connection);
				return FALSE;
			}
		} else {
			bb_die(__('Could not connect to LDAP authentication service.'));
		}
	}
	
	function ldap_new_user( $user_login ) {
		global $bbdb, $bb_table_prefix;
		$now       = bb_current_time('mysql');
		$password  = '^LDAP';
		
		$bbdb->query("INSERT INTO $bbdb->users (user_login, user_pass, user_registered) VALUES ('$user_login', '$password', '$now')");
		
		$user_id = $bbdb->insert_id;
		
		bb_update_usermeta( $user_id, $bb_table_prefix . 'capabilities', array('member' => true) );
		do_action('ldap_new_user', $user_id);
		return $user_id;
	}
}
?>