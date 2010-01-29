<?php
/*
Plugin Name: Easy Mentions
Plugin URI: http://gaut.am/bbpress/plugins/easy-mentions/
Description: Easy Mentions allows the users to link to other users' profiles in posts by using @username (like Twitter).
Version: 0.1.1
Author: Gautam Gupta
Author URI: http://gaut.am/

@license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3
*/

/**
 * @package Easy Mentions
 * @subpackage Main Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 */

bb_load_plugin_textdomain( 'easy-mentions', dirname( __FILE__ ) . '/languages' ); /* Create Text Domain For Translations */

/* Defines */
/* If you have problems (the directory of the plugin could not be matched), then define EM_PLUGPATH in bb-config.php file to the full URL path to the plugin directory
 * @example http://www.example-domain.tld/forums/my-plugins/easy-mentions/
 */
if( !defined( 'EM_PLUGPATH' ) )  /* Define EM_PLUGPATH if value is not set - Full URL path to the plugin */
	define( 'EM_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/' );
define( 'EM_VER', '0.1.1' ); /* Version */
define( 'EM_OPTIONS','Easy-Mentions' ); /* Option Name */

$em_plugopts = bb_get_option( EM_OPTIONS );
if( !is_array( $em_plugopts ) ){ /* Set the Options if they are not set */
	$em_plugopts = array(
		'link-to' => 'profile',
		'reply-link' => '',
	);
	bb_update_option( EM_OPTIONS, $em_plugopts );
}

if( bb_is_admin() ) /* Load admin.php file if it is the admin area */
	require_once( 'includes/admin.php' );
else /* Else load public.php file if it is the public area */
	require_once( 'includes/public.php' );
