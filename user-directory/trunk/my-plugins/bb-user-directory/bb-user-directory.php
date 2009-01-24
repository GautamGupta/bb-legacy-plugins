<?php
/*
Plugin Name: BBPress User Directory
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/user-directory
Description: Lists users of the forum, linking to their profiles
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.5
*/

require_once('bb-user-directory-renderers.php');
require_once('bb-user-directory-data.php');
require_once('bb-user-directory-pm-integration.php');
require_once('bb-user-directory-admin.php');

ud_register_default_columns();

add_action('bb_admin_menu_generator', 'ud_add_admin_page');

?>