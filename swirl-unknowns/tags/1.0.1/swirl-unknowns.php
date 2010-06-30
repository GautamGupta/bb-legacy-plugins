<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Redirects non-logged-in users to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Version: 1.0.1
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/

/**
 * @license CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
 */

/**
 * Define constants
 */
define( 'SWIRL_UNKNOWNS_ID',   'swirl-unknowns' );
define( 'SWIRL_UNKNOWNS_NAME', 'Swirl Unknowns' );

// Create text domain for translations
bb_load_plugin_textdomain( SWIRL_UNKNOWNS_ID, dirname( __FILE__ ) . '/translations' );

/**
 * Global vars
 */

/*
$notices is an array of arrays used for feedback.

To access its data:
foreach ( $notices as $notice )

$notice[0] is notice content (string).
$notice[1] is notice class (string).

e.g.
( 'updated notice', '' )
( 'error notice', 'error' )
*/
$notices = array();

// Percent-substitution tags
$tags = array(
			array ( 'code' => '%bb_uri%', 'value' => bb_get_option( 'uri' ) ), // Do not move this entry, its value is used by the default swirl page
			array ( 'code' => '%domain%', 'value' => bb_get_option( 'domain' ) )
		);

// Swirl-immune pages
$immunes = array(
				'bb-login.php', // Do not move this entry, it is used by the default swirl page
				'bb-reset-password.php',
				'register.php',
				'xmlrpc.php',
				'bb-admin/',
				'rss/'
			);

$default_swirl_page = $tags[0]['code'] . $immunes[0];

if ( bb_is_admin() ) // Load admin.php if on admin area
	require_once( 'includes/admin.php' );

/**
 * Add plugin actions
 */
add_action( 'bb_init', 'swirl_unknowns', 3 ); // Action priority should be pretty high

// Run function on plugin deactivation
bb_register_plugin_deactivation_hook( __FILE__, 'swirl_unknowns_deactivate' );

/**
 * Functions
 */

/**
 * Redirect non-logged-in users requesting any forum page to swirl page
 *
 * @uses $immunes
 * @uses $tags
 * @uses bb_get_option()
 * @uses bb_is_user_logged_in()
 * @uses bb_safe_redirect()
 * 
 * @return void
 */
function swirl_unknowns() {
	if ( $swirl_page = bb_get_option( 'swirl_page' ) ) {
		global $immunes;
		global $tags;

		// Create swirl-immune pages' PCRE pattern
		$pattern = '';

		foreach ( $immunes as $immune ) {
			// Add shashes to PCRE special chars
			$immune = str_replace( '-', '\-', $immune );
			$immune = str_replace( '.', '\.', $immune );
			$immune = str_replace( '/', '\/', $immune );

			$pattern .= $immune . '|';
		}

		$pattern = substr( $pattern, 0, -1 ); // Remove unnecessary tailed "|"

		// Process percent-substitution tags
		foreach ( $tags as $tag )
			$swirl_page = str_replace( $tag['code'], $tag['value'], $swirl_page );

		if (
			!bb_is_user_logged_in()
			&& !preg_match( '/('. $pattern .')/i', $_SERVER['REQUEST_URI'] )
			&& strcasecmp( $_SERVER['REQUEST_URI'], $swirl_page ) // Do not redirect swirl page to itself
		)
			bb_safe_redirect( $swirl_page );
	}
}
?>