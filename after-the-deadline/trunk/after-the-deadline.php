<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.4
Author: Gautam Gupta
Author URI: http://gaut.am/

@license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3
*/

/**
 * @package After the Deadline
 * @subpackage Main Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

bb_load_plugin_textdomain( 'after-the-deadline', dirname(__FILE__) . '/languages' ); /* Create Text Domain For Translations */

/* Defines */

/**
 * If you have problems (the directory of the plugin could not be matched), then define ATD_PLUGPATH in bb-config.php file to the full URL path to the plugin directory
 * @example http://www.example-domain.tld/forums/my-plugins/after-the-deadline/
 * @since 1.3
 */
if( !defined( 'ATD_PLUGPATH' ) ) /* Define ATD_PLUGPATH if value is not set - Full URL path to the plugin */
	define( 'ATD_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/' );
define( 'ATD_VER', '1.4.1-dev' ); /* Version */
define( 'ATD_OPTIONS','After-the-Deadline' ); /* AtD Option Name */

$atd_plugopts = bb_get_option(ATD_OPTIONS);
if( !is_array( $atd_plugopts ) ){ /* Set the Options if they are not set */
	$atd_plugopts = array(
		'key' => ''
	);
	bb_update_option( ATD_OPTIONS, $atd_plugopts );
}

if( bb_is_admin() ) /* Load admin.php file if it is the admin area */
	require_once( 'includes/admin.php' );
else /* Else load public.php file if it is the public area */
	require_once( 'includes/public.php' );
