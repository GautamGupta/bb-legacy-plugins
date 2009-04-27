<?php
/*
Plugin Name: Integration API
Version: 0.5
Plugin URI: http://greenfabric.com/page/integration_api_home_page
Description: Enable single sign-on between bbPress and Ruby on Rails.
Author: Scott Bonds
Author URI: http://ggr.com/
*/

  /*
   * This plugin was made possible by the great work done by:
   *
	 * Daniel Westermann-Clark on the HTTP Authentication plugin for WordPress
	 * Robb Shecter on the Integration API plugin for WordPress
	 * Sam Bauers on the bbPress LDAP authentication plugin for bbPress
	 * 
   */

/*  Copyright (C) 2009 Scott Bonds ( ggr.com )

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */

require_once 'integration_api_lib.php';

$API_DEBUG   = false;

if (! class_exists('BBIntegrationApiPlugin')) {
  class BBIntegrationApiPlugin {
    public $api;

    function IntegrationApiPlugin() {
    }
	  
	
    /*
     * Do simple caching of the IntegrationApi instance.
     * There's probably a simpler way to do this.
     */
    function api() {
      if (! $this->api)
				$this->api = new BBIntegrationApi(bb_get_option('i_api_api_url'));
      	return $this->api;
    }

    /*************************************************************
     * Plugin hooks
     *************************************************************/
    
    /*
     * Add options for this plugin to the database.
     */
    function initialize_options() {
			echo('hi there');
	
	    if (bb_current_user_can('manage_options')) {
				bb_update_option('i_api_auto_create_user', false); // Should a new user be created automatically if not already in the bbPress database?
				bb_update_option('i_api_api_url', 'http://localhost:3000/integration_api/'); // Should a new user be created automatically if not already in the bbPress database?
				bb_update_option('i_api_user_username',  ''); // How do you store the username in your Rails app?
				bb_update_option('i_api_user_firstname', ''); // How do you store the first name in your Rails app?
				bb_update_option('i_api_user_lastname',  ''); // How do you store the last name in your Rails app?
				bb_update_option('i_api_user_email',     ''); // How do you store the user email in your Rails app?
				bb_update_option('i_api_user_website',   ''); // How do you store the user's website in your Rails app?
				bb_update_option('i_api_single_signon', false); // Automatically detect if a user is logged in?
				bb_update_option('i_api_user_nickname', '');
				bb_update_option('i_api_user_display_name', '');
				bb_update_option('i_api_user_description', '');
      }
    }
    
		/**
		 * Returns whether the plugin is active or not
		 *
		 * @return boolean
		 * @author Sam Bauers
		 **/
		function isActive() {
			// if ($this->enabled && $this->active) {
			// 	return true;
			// } else {
			// 	return false;
			// }
			return true;
		}

	  /*
	   * Check if the current person is logged in.  If so,
	   * return the corresponding BB_User.
	   */
		function authenticate($username, $password) {
			if ( $this->api()->is_logged_in() ) {
				$username = $this->api()->user_info()->{bb_get_option('i_api_user_username')};
				$password = $this->_get_password();
			} else {
				$this->redirect_to_login();
			}
			$user = bb_get_user_by_name($username);

			if (! $user or $user->user_login != $username) {
				// User is logged into the API, but there's no 
				// bbPress user for them.  Are we allowed to 
				// create one?
				if ((bool) bb_get_option('i_api_auto_create_user')) {
					$this->_create_user($username);
					$user = bb_get_user_by_name($username);
				} else {
					// Bail out to avoid showing the login form
					bb_die("User $username does not exist in the bbPress database and user auto-creation is disabled.");
				}
			}

			wp_set_auth_cookie($user->ID, $remember);
			do_action('bb_user_login', (int) $user->ID );
			return new BB_User($user->ID);
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
		function disablePasswordRecovery()
		{
			if ($this->isActive() && $this->locationIs('bb-reset-password.php')) {
				$user_login = user_sanitize($_POST['user_login']);
				if (!empty($user_login)) {
					$user = bb_get_user_by_name($user_login);
					bb_die(__('Password recovery is not possible for this account because it uses an LDAP username and password to login. To change your LDAP password, please contact your system administrator.'));
				}
			}
		}


		/**
		 * Disables password editing for users who have LDAP passwords
		 *
		 * @return void
		 * @author Sam Bauers
		 **/
		function disablePasswordEditing()
		{
			global $bb_current_user;

			if ($this->isActive() && ($this->locationIs('profile.php') || $this->locationIs('profile-edit.php'))) {
				add_filter('bb_user_has_cap', array($this, 'removePasswordCapability'), 10, 2);
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

	  
    /*
     * Send the user to the login page given by the API.
     */
    function redirect_to_login() {
      header('Location: ' . $this->api()->login_url());
      exit();
    }
    

    /*
     * Generate a password for the user. This plugin does not
     * require the user to enter this value, but we want to set it
     * to something nonobvious.
     */
    function generate_password($username, $password1, $password2) {
      $password1 = $password2 = $this->_get_password();
    }


    /*************************************************************
     * Private methods
     *************************************************************/
    
    
    /*
     * Generate a random password.
     */
    function _get_password($length = 10) {
      return substr(md5(uniqid(microtime())), 0, $length);
    }


    /*
     * Create a new bbPress account for the specified username.
     */
    function _create_user($username) {
      require_once(BBINC . DIRECTORY_SEPARATOR . 'registration-functions.php');
      $api_info = (array) $this->api()->user_info();
      $u = array();

      $u['user_pass']      = $this->_get_password();
      $u['user_login']     = $username;
      $u['user_email']     = $api_info[bb_get_option('i_api_user_email')];
      $u['user_url']       = $api_info[bb_get_option('i_api_user_website')];
      // $u['user_firstname'] = $api_info[bb_get_option('i_api_user_firstname')];
      // $u['user_lastname']  = $api_info[bb_get_option('i_api_user_lastname')];
      
      // $u['nickname']       = $api_info[bb_get_option('i_api_user_nickname')];
      // $u['display_name']   = $api_info[bb_get_option('i_api_user_display_name')];
      // $u['description']    = $api_info[bb_get_option('i_api_user_description')];
 
			$u['id'] = bb_new_user( $u['user_login'], $u['user_email'], $u['user_url'] );
			bb_update_user_password( $u['id'], $u['user_pass'] );
    }
    
    
	}
}

// initialize the plugin
$integration_api_plugin = new BBIntegrationApiPlugin();

// if active, then add filters via API
if ($integration_api_plugin->isActive()) {	
	
	// initialize variables on activation
	if (isset($_GET['action']) and $_GET['action'] == 'activate') {
		add_action('bb_init', array($integration_api_plugin, 'initialize_options'));
  }

	add_action('bb_init', array($integration_api_plugin, 'disableRegistration'));
	add_action('bb_init', array($integration_api_plugin, 'disablePasswordRecovery'));
	add_action('bb_init', array($integration_api_plugin, 'disablePasswordEditing'));

  /*
   * Check if the current person is logged in.  If so,
   * return the corresponding BB_User.
   */
	if ( ! function_exists('bb_login') ) :
		function bb_login($username, $password) {
			$integration_api_plugin = new BBIntegrationApiPlugin();
			return $integration_api_plugin->authenticate($username, $password);
		}
	endif;
	

	// override logout function
	if ( ! function_exists('bb_logout') ) :
		function bb_logout() {
			$integration_api_plugin = new BBIntegrationApiPlugin();
	    bb_set_current_user(0);
	    wp_clear_auth_cookie();
	    header('Location: ' . $integration_api_plugin->api()->logout_url());
	    exit();
		}
	endif;


	/*
	 * Overriding 'bb_get_current_user' to provide the single sign-on function.  The user
	 * doesn't have to click the login link; the system will automatically
	 * log them in or out to match the current state returned by the API.
	 */
	if ( ((bool)bb_get_option('i_api_single_signon')) && (! function_exists('bb_get_current_user')) ) :
	  function bb_get_current_user() {
	    global $bb_current_user;

	    if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST )
	      return false;

	    if ( ! empty($bb_current_user) )
	      return $bb_current_user;

	    $api = new BBIntegrationApi(bb_get_option('i_api_api_url'));

	    /*
	     * If the API reports "logged out", make sure we're logged out in
	     * bbPress as well.
	     */
	    if (! $api->is_logged_in()) {
	      bb_set_current_user(0);
	      wp_clear_auth_cookie();
	      return false;
	    }

	    if ( ! $user = wp_validate_auth_cookie() ) {
	      if ( empty($_COOKIE[LOGGED_IN_COOKIE]) || !$user = wp_validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in') ) {
					/*
		 			  * The API reports "logged in", but we're not logged in to
		 				* bbPress.  Therefore, here we force the log in.
		 				*/
					$plugin      = new BBIntegrationApiPlugin();
					$user_record = $plugin->authenticate($api->user_info()->{'nickname'}, "pass");
					if ( is_wp_error($user_record) )
		  			return false;
					wp_set_auth_cookie($user_record->ID, false, false);
					$user = $user_record->ID;
	      }
	    }
			bb_set_current_user($user);
	  }
	endif;	
	

	/*
	 * Extending this function purely for extra error checking; this will
	 * stop execution if the API is out of sync with bbPress's "logged
	 * in" status.
	 */
	if ($API_DEBUG && (! function_exists('is_user_logged_in()'))) :
	  function is_user_logged_in() {
	    $result = '';
	    $user = bb_get_current_user();

	    if ( $user->id == 0 )
	      $result = false;
	    else
	      $result = true;

	    $api = new BBIntegrationApi(bb_get_option('i_api_api_url'));
	    if ($api->is_logged_in()) {
	      if (! $result)
		die ("Integration_API error: api yes, wp no.");
	    }
	    else {
	      if ($result)
		die("Integration_API error: api no, wp yes.");
	    }

	    return $api->is_logged_in();
	  }
	endif;

}

/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'integration_api_admin_page_add');
add_action('bb_admin-header.php','integration_api_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function integration_api_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Integration API'), 'use_keys', 'integration_api_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Integration API'), 'use_keys', 'integration_api_admin_page');
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
function integration_api_admin_page() {
	$api_url           = bb_get_option('i_api_api_url');
	$auto_create_user  = (bool) bb_get_option('i_api_auto_create_user');
	$user_username     = bb_get_option('i_api_user_username');
	$user_firstname    = bb_get_option('i_api_user_firstname');
	$user_lastname     = bb_get_option('i_api_user_lastname');
	$user_email        = bb_get_option('i_api_user_email');
	$user_website      = bb_get_option('i_api_user_website');
	$single_signon     = (bool) bb_get_option('i_api_single_signon');
	$user_nickname     = bb_get_option('i_api_user_nickname');
	$user_display_name = bb_get_option('i_api_user_display_name');
	$user_description  = bb_get_option('i_api_user_description');
	?>
	<div class="wrap">
	  <h2>Integration API Settings</h2>

	  <form method="post">
	    <input type="hidden" name="action" value="update" />
	    <input type="hidden" name="page_options" value="i_api_user_nickname,i_api_user_display_name,i_api_user_description,i_api_single_signon,i_api_user_username,i_api_user_firstname,i_api_user_lastname,i_api_user_email,i_api_user_website,,i_api_api_url,i_api_auto_create_user,i_api_auto_create_email_domain" />
	    <?php if (function_exists('bb_nonce_field')): bb_nonce_field('update-options'); endif; ?>

	    <table class="form-table">
	      <tr valign="top">
	        <th scope="row"><label for="i_api_api_url">API web service URL</label></th>
	        <td>
	          <input type="text" name="i_api_api_url" id="i_api_api_url" value="<?php echo htmlspecialchars($api_url) ?>" size="50" /><br />
	          e.g., http://localhost:3000/integration_api/							   
	        </td>
	      </tr>
	      <tr valign="top">
	        <th scope="row"><label for="i_api_single_signon">Enable single sign-on?</label></th>
	        <td>
	          <input type="checkbox" name="i_api_single_signon" id="i_api_single_signon"<?php if ($single_signon) echo ' checked="checked"' ?> value="1" />
		  <p>
		    When this is enabled, users will not have to
		    click <i>login</i> or <i>logout</i>; bbPress will simply
		    recognize their login state.  <span style="color: red;
		    font-weight: bold">Important:</span> activate this feature
		    only after verifying that login and logout is functioning
		    via the plugin.  Otherwise, you may be locked out from
		    bbPress.
		  </p>
	        </td>
	      </tr>
	      <tr valign="top">
	        <th scope="row"><label for="i_api_auto_create_user">Automatically create accounts?</label></th>
	        <td>
	          <input type="checkbox" name="i_api_auto_create_user" id="i_api_auto_create_user"<?php if ($auto_create_user) echo ' checked="checked"' ?> value="1" />
		  <p>
	            Should a new user be created automatically if not already
	            in the bbPress database?<br />  Created users will
	            obtain the role defined under &quot;New User Default
	            Role&quot; on the <a href="options-general.php">General
	            Options</a> page.
		  </p>
	        </td>
	      </tr>
	    </table>


	    <h3>User data mapping</h3>
	    <p>
	      If you've enabled <i>automatically create accounts</i>, above,
	      then when this plugin creates the new bbPress user, it will
	      fill in some of the bbPress user attributes.  This plugin gets
	      the info directly from your Rails app. But you must first set up
	      a &quot;mapping&quot; to specify what corresponds with what.
	      Here are the Rails user attributes that your app is currently
	      sending via the API:
	    </p>
	    <table style="margin-left: 40px;">
	      <?
				$integration_api_plugin = new BBIntegrationApiPlugin();
	
		    $user_array = (array) $integration_api_plugin->api()->user_info(); 
	            if ((count(array_keys($user_array))) == 0)
	              echo "<p style=\"background-color: #fab; padding: 0.5em\"><span style=\"color: red; font-weight: bold\">Error: No Rails data.</span> Check (1) your API URL setting above, (2) that your Rails app is properly configured, and (3) that you are logged in to your Rails app.</p>";
	            else {
		    echo "<tr><th>Rails attribute</th><th>Sample value</th></tr>";
		    foreach (array_keys($user_array) as $attribute) {
		      echo "<tr>";
		      echo "<td><b>";
		      echo $attribute;
		      echo "</b></td>";
		      echo "<td>";
		      echo $user_array[$attribute];
		      echo "</td>";
		      echo "</tr>";
		      }
	            }
	      ?>
	    </table>
	    <p>
	      In each text field, enter the appropriate attribute name from
	      the left column above. Leave a field blank if there's no
	      corresponding field in your Rails user model or if you don't want
	      the particular attribute to be automatically set.
	    </p>

	    <table class="form-table">
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_username">Username</label></th>
	        <td>
	          <input type="text" name="i_api_user_username" id="i_api_user_username" value="<?php echo htmlspecialchars($user_username) ?>" size="50" /><br/>
		  (Required) The login name for the user.  The user will not be able to change this.
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_email">E-mail</label></th>
	        <td>
	          <input type="text" name="i_api_user_email" id="i_api_user_email" value="<?php echo htmlspecialchars($user_email) ?>" size="50" /><br/>
		  (Required)
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_firstname">First Name</label></th>
	        <td>
	          <input type="text" name="i_api_user_firstname" id="i_api_user_firstname" value="<?php echo htmlspecialchars($user_firstname) ?>" size="50" /><br/>
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_lastname">Last Name</label></th>
	        <td>
	          <input type="text" name="i_api_user_lastname" id="i_api_user_lastname" value="<?php echo htmlspecialchars($user_lastname) ?>" size="50" /><br/>
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_nickname">Nickname</label></th>
	        <td>
	          <input type="text" name="i_api_user_nickname" id="i_api_user_nickname" value="<?php echo htmlspecialchars($user_nickname) ?>" size="50" /><br/>
		  Defaults to the user's username.
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_display_name">Display Name</label></th>
	        <td>
	          <input type="text" name="i_api_user_display_name" id="i_api_user_display_name" value="<?php echo htmlspecialchars($user_display_name) ?>" size="50" /><br/>
		  A string that will be shown on the site. Defaults to user's username.
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_website">Website</label></th>
	        <td>
	          <input type="text" name="i_api_user_website" id="i_api_user_website" value="<?php echo htmlspecialchars($user_website) ?>" size="50" /><br/>
		  A string containing the user's URL for their web site.
	        </td>
	      </tr>
	      <tr valign="top">
		<th scope="row"><label for="i_api_user_description">Biographical Info</label></th>
	        <td>
	          <input type="text" name="i_api_user_description" id="i_api_user_description" value="<?php echo htmlspecialchars($user_description) ?>" size="50" /><br/>
		  A string containing content about the user.
	        </td>
	      </tr>

	    </table>

			<p class="submit alignleft">
				<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
				<input type="hidden" name="action" value="integration_api_update" />
			</p>
	  </form>
	</div>
	<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 **/
function integration_api_admin_page_process() {
	if (isset($_POST['submit'])) {
		if ('integration_api_update' == $_POST['action']) {
			
			// API web service URL
			if ($_POST['i_api_api_url']) {
				bb_update_option('i_api_api_url', $_POST['i_api_api_url']);
			}
			
			// Enable single sign-on
			if ($_POST['i_api_single_signon']) {
				bb_update_option('i_api_single_signon', $_POST['i_api_single_signon']);
			} else {
				bb_update_option('i_api_single_signon', '');
			}
			
			// Automatically create accounts
			if ($_POST['i_api_auto_create_user']) {
				bb_update_option('i_api_auto_create_user', $_POST['i_api_auto_create_user']);
			} else {
				bb_update_option('i_api_auto_create_user', '');
			}

			// User data mapping - username
			if ($_POST['i_api_user_username']) {
				bb_update_option('i_api_user_username', $_POST['i_api_user_username']);
			} else {
				bb_update_option('i_api_user_username', '');
			}

			// User data mapping - email
			if ($_POST['i_api_user_email']) {
				bb_update_option('i_api_user_email', $_POST['i_api_user_email']);
			} else {
				bb_update_option('i_api_user_email', '');
			}

			// User data mapping - firstname
			if ($_POST['i_api_user_firstname']) {
				bb_update_option('i_api_user_firstname', $_POST['i_api_user_firstname']);
			} else {
				bb_update_option('i_api_user_firstname', '');
			}

			// User data mapping - lastname
			if ($_POST['i_api_user_lastname']) {
				bb_update_option('i_api_user_lastname', $_POST['i_api_user_lastname']);
			} else {
				bb_update_option('i_api_user_lastname', '');
			}

			// User data mapping - nickname
			if ($_POST['i_api_user_nickname']) {
				bb_update_option('i_api_user_nickname', $_POST['i_api_user_nickname']);
			} else {
				bb_update_option('i_api_user_nickname', '');
			}

			// User data mapping - display_name
			if ($_POST['i_api_user_display_name']) {
				bb_update_option('i_api_user_display_name', $_POST['i_api_user_display_name']);
			} else {
				bb_update_option('i_api_user_display_name', '');
			}

			// User data mapping - website
			if ($_POST['i_api_user_website']) {
				bb_update_option('i_api_user_website', $_POST['i_api_user_website']);
			} else {
				bb_update_option('i_api_user_website', '');
			}

			// User data mapping - description
			if ($_POST['i_api_user_description']) {
				bb_update_option('i_api_user_description', $_POST['i_api_user_description']);
			} else {
				bb_update_option('i_api_user_description', '');
			}

		}
	}
}


?>
