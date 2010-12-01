<?php
/*
Plugin Name: BB-to-WP users copier
Plugin URI: www.numediastudio.com
Description: BB-to-WP Users Copier (Aka bbwpuc) copies users from BBpress forum to Wordpress users database, useful when integrating BBpress and Wordpress.
Author: Abdessamad Idrissi
Author URI: www.numediastudio.com
Version: 1.0

Version History:
1.0		: Oct 26, 2010 | First release
*/

require_once('functions.php');

// hook functions
add_action('bb_admin_menu_generator', 'bbwpuc_configuration_page_add');

?>