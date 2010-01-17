<?php
/*
 * AtD Proxy Script for
 * After the Deadline - Spell Checker Plugin
 * (for bbPress) by www.gaut.am
 */

/* Collect the data to be sent */
$API_KEY = ($_POST['key']) ? $_POST['key'] : 'cssproxy';
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ){
   $postText = trim(file_get_contents('php://input'));
}
if (strcmp($API_KEY, "") != 0){
   $postText .= '&key=' . $API_KEY;
}
$url = $_GET['url'];

/* Taken from akismet.php by Matt
 * Navigates to a given link
 */
function AtD_http_post($request, $host, $path, $port = 80){
   $http_request  = "POST $path HTTP/1.0\r\n";
   $http_request .= "Host: $host\r\n";
   $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
   $http_request .= "Content-Length: " . strlen($request) . "\r\n";
   $http_request .= "User-Agent: AtD/bbPress\r\n";
   $http_request .= "\r\n";
   $http_request .= $request;
   $response = '';                 
   if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ){
      fwrite($fs, $http_request);
      while ( !feof($fs) ){
         $response .= fgets($fs);
      }
      fclose($fs);
      $response = explode("\r\n\r\n", $response, 2);
   }
   return $response;
}

/* Get the Data & echo */
$data = AtD_http_post($postText, "service.afterthedeadline.com", $url);
header("Content-Type: text/xml");
echo $data[1];
?>
