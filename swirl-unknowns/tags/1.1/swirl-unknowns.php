<?php
/*
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Description: Redirects non-logged-in users to a page of your choice. Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">voodoo code from Trent Adams and Sam Bauers</a>.
Version: 1.1
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
bb_load_plugin_textdomain( SWIRL_UNKNOWNS_ID, dirname( __FILE__ ) . '/languages' );


/**
 * Wrapper class for plugin settings
 */
class Swirl_Unknowns_Settings {
	/**
	 * Swirl page
	 *
	 * @var string
	 */
	var $swirl_page;

	/**
	 * Default swirl page
	 *
	 * @var string
	 */
	var $default_swirl_page;

	/**
	 * List of available percent-substitution tags (tag => value)
	 *
	 * @var array
	 */
	var $tags;

	/**
	 * List of swirl-immune pages
	 *
	 * @var array
	 */
	var $immunes;


	/**
	 * Class constructor
	 */
	function Swirl_Unknowns_Settings() {
		$this->swirl_page = (string) bb_get_option( 'swirl_page' );

		$this->tags = array(
			'%bb_uri%' => bb_get_option( 'uri' ),
			'%domain%' => bb_get_option( 'domain' )
		);

		$this->immunes = array(
			'bb-login.php',
			'bb-reset-password.php',
			'register.php',
			'xmlrpc.php',
			'bb-admin/',
			'rss/'
		);

		$this->default_swirl_page = $this->tags['%bb_uri%'] . 'bb-login.php';
	}


	/**
	 * Functions
	 */

	/**
	 * Whether or not plugin is enabled
	 *
	 * @return boolean
	 */
	function isEnabled() {
		return !empty( $this->swirl_page );
	}
}

// Initialize the class
$swirl_unknowns_settings = new Swirl_Unknowns_Settings();

if ( bb_is_admin() ) // Load admin.php if on admin area
	require_once( 'includes/admin.php' );


/**
 * Add plugin actions
 */
add_action( 'bb_init', 'swirl_unknowns', 2 ); // Very high action priority


/**
 * Functions
 */

/**
 * Redirect non-logged-in users to the swirl page
 *
 * @global $swirl_unknowns_settings
 *
 * @uses bb_is_user_logged_in()
 * @uses wp_redirect()
 * 
 * @return void
 */
function swirl_unknowns() {
	global $swirl_unknowns_settings;

	if ( false === (bool) $swirl_unknowns_settings->isEnabled() )
		return;

	$swirl_page = $swirl_unknowns_settings->swirl_page;
	$immunes =    $swirl_unknowns_settings->immunes;

	foreach ( $immunes as &$immune ) { // Prepend '&' to modify var value
		// Add shashes to PCRE special chars
		$immune = str_replace( '-', '\-', $immune );
		$immune = str_replace( '.', '\.', $immune );
		$immune = str_replace( '/', '\/', $immune );
	}

	// Create swirl-immune pages' PCRE pattern
	$pattern = implode( '|', $immunes );

	// Process percent-substitution tags
	foreach ( $swirl_unknowns_settings->tags as $tag => $value )
		$swirl_page = str_replace( $tag, $value, $swirl_page );

	if (
		!bb_is_user_logged_in()
		&& !preg_match( '/('. $pattern .')/i', $_SERVER['REQUEST_URI'] )
		&& strcasecmp( $_SERVER['REQUEST_URI'], $swirl_page ) // Do not redirect swirl page to itself
	) {
		wp_redirect( $swirl_page );
		exit;
	}
}
