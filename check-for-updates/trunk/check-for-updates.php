<?php
/*
Plugin Name: Check For Updates
Description: Shows which bbPress plugins may be out of date.
Plugin URI:  http://bbpress.org/plugins/topic/check-for-updates
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2
*/ 
if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 	
	function check_for_updates_admin_page() {global $bb_submenu; $bb_submenu['plugins.php'][] = array(__('Check for Updates'), 'administrate', 'check_for_updates');}
	add_action( 'bb_admin_menu_generator', 'check_for_updates_admin_page',255);	// try to be last menu feature
	if (isset($_GET['plugin']) && $_GET['plugin']=="check_for_updates") {require_once("check-for-updates-admin.php");}
}
?>