<?php
/**
 * @package After the Deadline
 * @subpackage Public Section
 * @category Proxy Script (AJAX)
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

define( 'DOING_AJAX', true ); /* Fake like it is an AJAX call as admin-ajax.php does some more checks which we don't need */

require_once( '../../../bb-load.php' );

if ( !class_exists( 'WP_Http' ) ) /* Should never happen, but still (for future) */
	require_once( BACKPRESS_PATH . 'class.wp-http.php' );

/* Collect the data to be sent */
$user		= bb_get_current_user();
$api_key	= 'BB-' . md5( bb_get_uri() ) . '-'; /* Forum's starting key */
$api_key	.= $user->ID ? $user->ID : mt_rand(); /* User specific */
$lang		= in_array( $atd_plugopts['lang'], array( 'pt', 'fr', 'de', 'es', 'en' ) ) ? $atd_plugopts['lang'] : 'en';
$scheme		= $atd_plugopts['use_ssl'] == true ? 'https' : 'http';
$go		= $scheme . '://' . $lang . '.service.afterthedeadline.com/checkDocument';
if( !$postdata = trim( $_POST['data'] ) ) /* Should never happen */
	die();

/* Get the Data & echo */
$data = wp_remote_retrieve_body( wp_remote_request( $go, array( 'method' => 'POST', 'body' => array( 'data' => $postdata, 'key' => $api_key ), 'user-agent' => 'AtD/bbPress v' . ATD_VER ) ) );
header( 'Content-Type: text/xml' );
echo $data;

exit;
?>