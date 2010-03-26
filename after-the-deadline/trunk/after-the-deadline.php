<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.6.1
Author: Gautam Gupta
Author URI: http://gaut.am/
*/

/**
 * @package After the Deadline
 * @subpackage Main Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 * @license GNU General Public License version 3 (GPLv3):
 * http://www.opensource.org/licenses/gpl-3.0.html
 */

/** Create Text Domain For Translations */
bb_load_plugin_textdomain( 'after-the-deadline', dirname( __FILE__ ) . '/translations' );

/**
 * Defines
 */
define( 'ATD_VER'		, '1.6.1-dev'						); /** Version */
define( 'ATD_OPTIONS'		, 'AftertheDeadline'					); /** Option Name */
define( 'ATD_USER_OPTIONS'	, 'AtDuserOptions'					); /** User Option Name */
define( 'ATD_PLUGPATH'		, bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) )	); /** Plugin URL */

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
	if ( defined( 'BB_LANG' ) && BB_LANG ) { /* Language check */
		foreach( array_keys( $atd_supported_langs ) as $lang ) {
			if ( strpos( BB_LANG, $lang ) !== false ) {
				$save_lang = $lang;
				break;
			}
		}
	}
	$atd_plugopts = array(
		'lang'		=> $save_lang ? $save_lang : 'en',
		'enableuser'	=> array() /* autoproofread and/or ignorealways and/or ignoretypes */
	);
	bb_update_option( ATD_OPTIONS, $atd_plugopts );
}

/**
 * Require Admin/Public/AJAX File
 */
if ( defined( 'DOING_AJAX' ) && DOING_AJAX == true && in_array( 'ignorealways', (array) $atd_plugopts['enableuser'] ) ) /* Load Ignore Phrase file as we are doing AJAX */
	require_once( 'includes/ajax-ignore.php' );
elseif ( bb_is_admin() ) /* Load admin.php file if it is the admin area */
	require_once( 'includes/admin.php' );
else /* Else load public.php file as it is the public area */
	require_once( 'includes/public.php' );
