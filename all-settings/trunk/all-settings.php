<?php
/*
Plugin Name: All Settings
Plugin URI: http://bbpress.org/plugins/topic/133
Description:  Allows all settings, including hidden options to be edited in the admin control panel. Works exactly like the (little-known) WordPress feature. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Instructions:   install, activate, look under Settings in admin menu for All Settings
*/

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 	
	function all_settings_admin_page() {global $bb_submenu; $bb_submenu['options-general.php'][] = array(__('All Settings'), 'use_keys', 'all_settings');}
	add_action( 'bb_admin_menu_generator', 'all_settings_admin_page',200);	// try to be last menu feature
	if (isset($_GET['plugin']) && $_GET['plugin']=="all_settings") {require_once("all-settings-admin.php");} // load entire core only when needed
}
?>