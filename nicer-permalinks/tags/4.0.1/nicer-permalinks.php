<?php
/*
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Description: Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes hierarchy. Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.
Version: 4.0.1
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/

/**
 * @license CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
 */

/**
 * Define constants
 */
define( 'NICER_PERMALINKS_ID',   'nicer-permalinks' );
define( 'NICER_PERMALINKS_NAME', 'Nicer Permalinks' );

// Create text domain for translations
bb_load_plugin_textdomain( NICER_PERMALINKS_ID, dirname( __FILE__ ) . '/translations' );

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

if ( bb_is_admin() ) // Load admin.php if on admin area
	require_once( 'includes/admin.php' );

if ( nicer_permalinks_enabled() ) { // Load plugin core if plugin is on
	// Load admin.php if on admin area
	require_once( 'includes/nicer-filters.php' );

	// Add plugin filters
	add_filter( 'get_forum_link',           'get_nicer_forum_link_filter', 10, 2 );
	add_filter( 'bb_get_forum_bread_crumb', 'bb_get_nicer_forum_bread_crumb_filter', 10, 2 );
	add_filter( 'get_topic_link',           'get_nicer_topic_link_filter', 10, 2 );
	add_filter( 'get_post_link',            'get_nicer_post_link_filter', 10, 3 );
}

// Run function on plugin deactivation
bb_register_plugin_deactivation_hook( __FILE__, 'deactivate_nicer_permalinks' );

/**
 * Functions
 */

/**
 * Check if plugin is enabled
 *
 * @uses bb_get_option()
 *
 * @return int
 */
function nicer_permalinks_enabled() {
	$status = (string) bb_get_option( 'nicer_permalinks_enabled' );

	return ( '1' == $status ) ? 1 : 0;
}
?>