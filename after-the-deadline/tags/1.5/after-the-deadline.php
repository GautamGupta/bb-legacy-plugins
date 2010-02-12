<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.5
Author: Gautam Gupta
Author URI: http://gaut.am/
*/

/**
 * @package After the Deadline
 * @subpackage Main Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 * @license GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html
 */

bb_load_plugin_textdomain( 'after-the-deadline', dirname( __FILE__ ) . '/languages' ); /** Create Text Domain For Translations */

/**
 * Defines
 */
define( 'ATD_VER'	, '1.5'							); /** Version */
define( 'ATD_PLUGPATH'	, bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) )	); /** Plugin URL */
define( 'ATD_OPTIONS'	, 'AftertheDeadline'					); /** Option Name */

/**
 * Options
 */
$atd_supported_langs = array(
	'en' => __( 'English'	, 'after-the-deadline' ),
	'pt' => __( 'Portuguese', 'after-the-deadline' ),
	'fr' => __( 'French'	, 'after-the-deadline' ),
	'de' => __( 'German'	, 'after-the-deadline' ),
	'es' => __( 'Spanish'	, 'after-the-deadline' )
);
$atd_plugopts = bb_get_option( ATD_OPTIONS );
if ( is_string( $atd_plugopts['key'] ) ) { /* Delete if there are old options, will be removed in v1.7 */
	bb_delete_option( ATD_OPTIONS );
	unset( $atd_plugopts );
}
if ( !is_array( $atd_plugopts ) ) { /* Set the Options if they are not set */
	if ( defined( 'BB_LANG' ) ){
		foreach( array_keys( $atd_supported_langs ) as $lang ){
			if ( strpos( BB_LANG, $lang ) !== false ) {
				$save_lang = $lang;
				break;
			}
		}
	}
	$atd_plugopts = array(
		'lang' => $save_lang ? $save_lang : 'en'
	);
	bb_update_option( ATD_OPTIONS, $atd_plugopts );
}

/**
 * Require Admin/Public File
 */
if ( bb_is_admin() ) /* Load admin.php file if it is the admin area */
	require_once( 'includes/admin.php' );
else /* Else load public.php file if it is the public area */
	require_once( 'includes/public.php' );
