<?php
/**
 * @package After the Deadline
 * @subpackage Public Section
 * @category Proxy Script
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * We dont do this via AJAX because AJAX requires a user ID to be set, and bbPress 1.1 also gives the ability for the non-members to post (and thus, do the spell check too).
 */

/**
 * Browsing Function
 * 
 * @uses WP_Http
 * @uses cURL
 * @uses file_get_contents
 * 
 * @param string $url The URL needed to be visited
 * @param string $method POST or GET (default POST)
 * @param array $data The data needed to be sent (if POST)
 *
 * @return string|bool The source received otherwise false
 */
function atd_http( $url, $method = 'POST', $data = array() ) {
	if ( class_exists( 'WP_Http' ) ) { /* Not necessarily as we avoid loading bb-load.php here */
		return wp_remote_retrieve_body( wp_remote_request( $url, array( 'method' => $method, 'body' => $data, 'user-agent' => 'AtD/bbPress v' . ATD_VER ) ) );
	} elseif ( function_exists( 'curl_init' ) ) { /* Use cURL */
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'AtD/bbPress' );
		if( strtoupper( $method ) == 'POST' ){
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		}
		curl_setopt( $ch, CURLOPT_HEADER, false );
		$source = curl_exec( $ch );
		curl_close( $ch );
		return $source;
	} elseif ( function_exists( 'file_get_contents' ) ) { /* Use file_get_contents() */
		if( strtoupper( $method ) == 'POST' && function_exists( 'stream_context_create' ) ){
		$opts = array('http' =>
			array(
				'method' => 'POST',
				'user_agent' => 'AtD/bbPress',
				'content' => http_build_query( $data )
			)
		);
		$context = stream_context_create($opts);
		return file_get_contents( $url, false, $context );
		} else {
			return file_get_contents( $url );
		}
	}
	
	return false;
}

/* Collect the data to be sent */
$url		= $_GET['url'] ? trim( $_GET['url'] ) : '/checkDocument';
$api_key	= $_POST['key'] ? trim( $_POST['key'] ) : 'bbPress';
$lang		= $_GET['lang'] ? trim( $_GET['lang'] ) : 'en';
$service	= ( in_array( $lang, array( 'pt', 'fr', 'de', 'es' ) ) ) ? $lang . '.service.afterthedeadline.com' : 'service.afterthedeadline.com';
if( !$postdata = trim( $_POST['data'] ) )
	die();

/* Get the Data & echo */
$data = trim( atd_http( 'http://' . $service . $url, 'POST', array( 'data' => $postdata, 'key' => $api_key ) ) );
header( 'Content-Type: text/xml' );
echo $data;

exit;
?>