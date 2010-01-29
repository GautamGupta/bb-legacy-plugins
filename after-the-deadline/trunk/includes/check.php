<?php
/**
 * @package Easy Mentions
 * @subpackage Public Section
 * @category Proxy Script
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 */

/**
 * Browsing Function
 * 
 * @uses WP_Http
 * @uses cURL
 * @uses file_get_contents
 * 
 * @param $url The URL needed to be visited
 * @param $method POST or GET (default POST)
 * @param $data array The data needed to be sent (if POST)
 *
 * @return The source received
 */
function atd_http( $url, $method = 'POST', $data = array() ){
   if( class_exists( 'WP_Http' ) ){ //not necessarily as we avoid loading bb-load
      return wp_remote_retrieve_body( wp_remote_request( $url, array( 'method' => $method, 'body' => $data, 'user-agent' => 'AtD/bbPress v' . ATD_VER ) ) );
   } elseif ( function_exists( 'curl_init' ) ) { // Use cURL
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
   } elseif ( function_exists( 'file_get_contents' ) ) { // use file_get_contents()
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
      }else{
         return file_get_contents( $url );
      }
   } else {
      return false;
   }
}

/* Collect the data to be sent */
$url = ($_GET['url']) ? $_GET['url'] : '/checkDocument';
$api_key = ($_POST['key']) ? $_POST['key'] : 'cssproxy';
$postdata = ($_POST['data']) ? $_POST['data'] : '';
if( !$postdata )
   die();

/* Get the Data & echo */
$data = trim( atd_http( 'http://service.afterthedeadline.com' . trim($url), 'POST', array( 'data' => trim( $postdata ), 'key' => trim( $api_key ) ) ) );
header( "Content-Type: text/xml" );
echo $data;
?>