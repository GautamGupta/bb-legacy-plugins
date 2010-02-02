<?php
/*
Plugin Name: Transient Cleanup
Plugin URI: http://nightgunner5.wordpress.com/tag/transient-cleanup/
Description: Clean up the transients that are used to store temporary data
Version: 0.1
Author: Ben L. (Nightgunner5)
Author URI: http://nightgunner5.wordpress.com/
*/

/**
 * The forum's meta table will be optimized if it has more than this
 * many bytes of overhead after deleting the expired transients.
 *
 * Set to -1 to disable optimization, or 0 to always optimize even if
 * it is not needed.
 */
define( 'TRANSIENT_CLEANUP_MAX_OVERHEAD', 10240 );

/* Stop editing here. */

function transient_cleanup_activate() {
	wp_schedule_event( bb_offset_time( mktime( 0, 0, 0 ) ), 'daily', 'transient_cleanup' );
}
//bb_register_plugin_activation_hook( __FILE__, 'transient_cleanup_activate' ); // This function doesn't work on Windows
add_action( 'bb_activate_plugin_' . str_replace( '/', DIRECTORY_SEPARATOR, bb_plugin_basename( __FILE__ ) ), 'transient_cleanup_activate' );

function transient_cleanup_cron() {
	global $bbdb;

	$bbdb->query( "DELETE FROM `{$bbdb->meta}` WHERE `object_type` = 'bb_option' AND (`meta_key` IN (SELECT * FROM (SELECT INSERT(`meta_key`, 10, 8, '') FROM `{$bbdb->meta}` WHERE `object_type` = 'bb_option' AND `meta_key` LIKE '_transient_timeout_%' AND `meta_value` < " . time() . ") AS _t) OR `meta_key` IN (SELECT * FROM (SELECT `meta_key` FROM `{$bbdb->meta}` WHERE `object_type` = 'bb_option' AND `meta_key` LIKE '_transient_timeout_%' AND `meta_value` < " . time() . ") AS _t))" );

	if ( TRANSIENT_CLEANUP_MAX_OVERHEAD != -1 ) {
		$status = $bbdb->get_row( "SHOW TABLE STATUS LIKE '{$bbdb->meta}'" );
		if ( (int)$status->Data_free >= TRANSIENT_CLEANUP_MAX_OVERHEAD )
			$bbdb->query( "OPTIMIZE TABLE `{$bbdb->meta}`" );
	}
}
add_action( 'transient_cleanup', 'transient_cleanup_cron' );


function transient_cleanup_deactivate() {
	wp_clear_scheduled_hook( 'transient_cleanup' );
}
//bb_register_plugin_deactivation_hook( __FILE__, 'transient_cleanup_deactivate' ); // This function doesn't work on Windows
add_action( 'bb_deactivate_plugin_' . str_replace( '/', DIRECTORY_SEPARATOR, bb_plugin_basename( __FILE__ ) ), 'transient_cleanup_deactivate' );
