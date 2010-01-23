<?php
/*
 * AtD Proxy Script for
 * After the Deadline Plugin
 * (for bbPress) by www.gaut.am
 */

/* Browsing Function */
function atd_http( $url, $method = 'POST', $data = array() ){
   if( class_exists( 'WP_Http' ) ){
      $request = new WP_Http;
      return wp_remote_retrieve_body( $request->request( $url, array( 'method' => $method, 'body' => $data, 'user-agent' => 'AtD/bbPress' ) ) );
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
if( !$postdata ){
   die();
}

/* Get the Data & echo */
$data = trim( atd_http( 'http://service.afterthedeadline.com' . trim($url), 'POST', array( 'data' => trim( $postdata ), 'key' => trim( $api_key ) ) ) );
header( "Content-Type: text/xml" );
echo $data;
?>