<?php
/*
Plugin Name: Mass Delete Users
Plugin URI: http://bbpress.org/plugins/topic/mass-delete-users
Description:  Allows administrators to physically delete multiple users at once from the bbPress / WordPress database based on a variety of search attributes.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5
*/

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 	
	function mass_delete_users_admin_page() {bb_admin_add_submenu(__('Mass Delete'), 'administrate', 'mass_delete_users','users.php');}
	add_action( 'bb_admin_menu_generator', 'mass_delete_users_admin_page',200);	// try to be last menu feature
	if (isset($_GET['plugin']) && $_GET['plugin']=="mass_delete_users") {require_once("mass-delete-users-admin.php");}
}
?>