<?php
/*
Plugin Name: Restrict registration
Plugin URI: http://bbpress.org/plugins/topic/44
Description: Limits registration to email addresses from specific domains
Author: Sam Bauers
Version: 2.0.2
Author URI: 

Version History:
1.0		: Initial Release
2.0		: Complete re-write
		  Added blacklist functionality and admin page
		  Domains can now optionally be specified with wildcards
2.0.1	: Some comments cleanup
		  Added support for bb_admin_add_submenu()
2.0.2	: Made PHP4 compatible
*/


/**
 * Restrict registration for bbPress version 2.0.2
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
 * The restrict registration class handles all the whitelist and blacklist juggling and email parsing
 *
 * @author  Sam Bauers
 * @version 1.0
 **/
class Restrict_Registration
{
	/**
	 * The whitelist as imported from the database
	 *
	 * @var string
	 **/
	var $whitelist = null;
	
	
	/**
	 * The blacklist as imported from the database
	 *
	 * @var string
	 **/
	var $blacklist = null;
	
	
	/**
	 * A user defined flag stating whether the plugin is to be used
	 *
	 * @var integer
	 **/
	var $enabled = 0;
	
	
	/**
	 * Whether restrictions are active
	 *
	 * @var boolean
	 **/
	var $active = false;
	
	
	/**
	 * The array of whitelist domains
	 *
	 * @var array
	 **/
	var $_whitelist = null;
	
	
	/**
	 * The array of blacklist domains
	 *
	 * @var array
	 **/
	var $_blacklist = null;
	
	
	/**
	 * The email to verify
	 *
	 * @var string
	 **/
	var $email = null;
	
	
	/**
	 * The allowed rank of the emails domain
	 *
	 * @var integer
	 **/
	var $allowed = 0;
	
	
	/**
	 * The denied rank of the emails domain
	 *
	 * @var integer
	 **/
	var $denied = 0;
	
	
	/**
	 * Gets the whitelist and blacklist from the database and determines whether restrictions are active
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function Restrict_Registration()
	{
		// A comma separated list of email address domains that are allowed to register
		$this->whitelist = bb_get_option('restrict_registration_whitelist');
		
		// A comma separated list of email address domains that are not allowed to register
		$this->blacklist = bb_get_option('restrict_registration_blacklist');
		
		// An integer set to 1 for enabled or 0 for disabled
		$this->enabled = bb_get_option('restrict_registration_enabled');
		
		// If there is a whitelist then restrictions are active
		if (trim($this->whitelist)) {
			$this->active = true;
		}
		
		// If there is a blacklist then restrictins are active
		if (trim($this->blacklist)) {
			$this->active = true;
		}
	}
	
	
	/**
	 * Returns the value of $this->active
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
	 * Cleans the provided list and turns it into an array of domains
	 *
	 * @return mixed
	 * @author Sam Bauers
	 **/
	function clean($list)
	{
		// If there is a list passed
		if (trim($list)) {
			// Trim whitespace
			$list = trim($list);
			
			// Change to lowercase
			$list = strtolower($list);
			
			// Tidy up comma delimiting
			$list = preg_replace('|(\s)*\,(\s)*|', ',', $list);
			
			// Split at the commas into an array
			$list = split(',', $list);
			
			// Throw out entries that aren't valid domains
			$list = preg_grep('|^([\-0-9a-z\*]+\.)+([\-0-9a-z\*]+){1,1}$|', $list);
			
			// If there are no entries left in the list destroy it
			if (count($list) <= 0) {
				$list = null;
			}
		} else {
			// There is no list
			$list = null;
		}
		
		return $list;
	}
	
	
	/**
	 * Removes items from the blacklist that are in the whitelist
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function removeWhiteFromBlack()
	{
		if ($this->_whitelist && $this->_blacklist) {
			$this->_blacklist = array_diff($this->_blacklist, $this->_whitelist);
		}
	}
	
	
	/**
	 * Turns items in a list into strings ready for use in preg functions
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function quote($list)
	{
		$list = preg_replace(
			array(
				'|\-|', // Hyphens
				'|\.|', // Periods
				'|\*|'  // Wildcard
			),
			array(
				'\-',
				'\.',
				'((?:[\-0-9a-z\*]+\.)*(?:[\-0-9a-z\*]+){1,1})'
			),
			$list
		);
		
		return $list;
	}
	
	
	/**
	 * Sets a 'rank' for the domain being tested based on the domains matching to the list
	 *
	 * @return integer
	 * @author Sam Bauers
	 **/
	function evaluate($list)
	{
		// Set up the list for use in preg function
		$list = $this->quote($list);
		
		// Set up the results array with a default index with value 0
		$results = array(0);
		
		// Loop through the list
		foreach ($list as $domain) {
			// If the email matches the domain
			if (preg_match('|@' . $domain . '$|i', $this->email, $matches)) {
				if (isset($matches[1])) {
					// Matched from wildcard
					$results[] = 2;
				} else {
					// Exact match
					$results[] = 3;
				}
			}
		}
		
		// Return the highest value in the results array
		return max($results);
	}
	
	
	/**
	 * Checks the whitelist to see if the domain is allowed
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function allow()
	{
		if ($this->_whitelist) {
			$this->allowed = $this->evaluate($this->_whitelist);
		} else {
			// No whitelist
			$this->allowed = 1;
		}
	}
	
	
	/**
	 * Checks the blacklist to see if the domain is denied
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function deny()
	{
		if ($this->_blacklist) {
			$this->denied = $this->evaluate($this->_blacklist);
		} else {
			// No blacklist
			$this->denied = 1;
		}
	}
	
	
	/**
	 * Determines whether the given email address is from an allowed domain
	 *
	 * @return mixed
	 * @author Sam Bauers
	 **/
	function verifyEmail($email)
	{
		if (!$email) {
			return false;
		}
		
		// Not allowed by default
		$this->allowed = 0;
		
		// Set the class variable
		$this->email = $email;
		
		// Clean up the lists
		$this->_whitelist = $this->clean($this->whitelist);
		$this->_blacklist = $this->clean($this->blacklist);
		
		// Remove duplicates between lists
		$this->removeWhiteFromBlack();
		
		// Evaluate the domains ranks
		$this->allow();
		$this->deny();
		
		// If the domain has a value for allowed, then make sure it outranks the denied value
		if ($this->allowed && $this->allowed - $this->denied > -1) {
			return $email;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Creates a cleaned string representing the whitelist and inserts it into bbPress' $profile_info_keys global
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function getWhitelist($profile_info_keys)
	{
		if (trim($this->whitelist) && bb_get_option('restrict_registration_whitelist_shown')) {
			$whitelist = $this->clean($this->whitelist);
			$profile_info_keys['user_email'][1] .= ' <span style="font-weight:normal;">(';
			$profile_info_keys['user_email'][1] .= join(', ', $whitelist);
			$profile_info_keys['user_email'][1] .= ' ' . __('only') . ')</span>';
		}
		return $profile_info_keys;
	}
}


// Initialise the class
$restrict_registration = new Restrict_Registration();


// If active, then add filters via API
if ($restrict_registration->isActive()) {
	add_filter('bb_verify_email', array($restrict_registration, 'verifyEmail'));
	add_filter('get_profile_info_keys', array($restrict_registration, 'getWhitelist'));
}


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'restrict_registration_admin_page_add');
add_action('bb_admin-header.php', 'restrict_registration_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function restrict_registration_admin_page_add()
{
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Restrict registration'), 'use_keys', 'restrict_registration_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Restrict registration'), 'use_keys', 'restrict_registration_admin_page');
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
function restrict_registration_admin_page()
{
	if (bb_get_option('restrict_registration_enabled')) {
		$enabled_checked = ' checked="checked"';
	}
	if (bb_get_option('restrict_registration_whitelist_shown')) {
		$whitelist_shown_checked = ' checked="checked"';
	}
?>
	<h2>Restrict registration</h2>
	<h3>Enabled</h3>
	<form method="post">
	<p>
		<input type="checkbox" name="restrict_registration_enabled" value="1" tabindex="10"<?php echo $enabled_checked; ?> /> Enable registration restriction<br />
		&nbsp;
	</p>
	<h3>Whitelist</h3>
	<p>
		<textarea name="restrict_registration_whitelist" tabindex="20" style="width:99%; height:50px;"><?php echo(bb_get_option('restrict_registration_whitelist')); ?></textarea>
	</p>
	<p>
		<input type="checkbox" name="restrict_registration_whitelist_shown" value="1" tabindex="30"<?php echo $whitelist_shown_checked; ?> /> Reveal whitelist on registration form<br />
		&nbsp;
	</p>
	<h3>Blacklist</h3>
	<p>
		<textarea name="restrict_registration_blacklist" tabindex="40" style="width:99%; height:100px;"><?php echo(bb_get_option('restrict_registration_blacklist')); ?></textarea>
	</p>
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="50" />
		<input type="hidden" name="action" value="restrict_registration_update" />
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
function restrict_registration_admin_page_process()
{
	if (isset($_POST['submit'])) {
		if ($_POST['action'] == 'restrict_registration_update') {
			// Enabled
			if ($_POST['restrict_registration_enabled']) {
				bb_update_option('restrict_registration_enabled', $_POST['restrict_registration_enabled']);
			} else {
				bb_delete_option('restrict_registration_enabled');
			}
			
			// Whitelist
			if ($_POST['restrict_registration_whitelist']) {
				bb_update_option('restrict_registration_whitelist', $_POST['restrict_registration_whitelist']);
			} else {
				bb_delete_option('restrict_registration_whitelist');
			}
			
			// Whitelist shown
			if ($_POST['restrict_registration_whitelist_shown']) {
				bb_update_option('restrict_registration_whitelist_shown', $_POST['restrict_registration_whitelist_shown']);
			} else {
				bb_delete_option('restrict_registration_whitelist_shown');
			}
			
			// Blacklist
			if ($_POST['restrict_registration_blacklist']) {
				bb_update_option('restrict_registration_blacklist', $_POST['restrict_registration_blacklist']);
			} else {
				bb_delete_option('restrict_registration_blacklist');
			}
		}
	}
}
?>