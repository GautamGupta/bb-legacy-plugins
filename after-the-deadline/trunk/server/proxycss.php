<?php

/*
 Proxy CSS File for
 After the Deadline - Spell Checker Plugin
 (for bbPress) by www.gaut.am
*/

$API_KEY = 'cssproxy';

$postText = 'data=' . $_GET['data'];

if (strcmp($API_KEY, '') != 0)
{
   $postText .= '&key=' . $API_KEY;
}

$url = '/checkDocument';

/* this function directly from akismet.php by Matt Mullenweg.  *props* */
function AtD_http_post($request, $host, $path, $port = 80) 
{
   $http_request  = "POST $path HTTP/1.0\r\n";
   $http_request .= "Host: $host\r\n";
   $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
   $http_request .= "Content-Length: " . strlen($request) . "\r\n";
   $http_request .= "User-Agent: AtD/0.1\r\n";
   $http_request .= "\r\n";
   $http_request .= $request;            

   $response = '';                 
   if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) 
   {                 
      fwrite($fs, $http_request);

      while ( !feof($fs) )
      {
          $response .= fgets($fs);
      }
      fclose($fs);
      $response = explode("\r\n\r\n", $response, 2);
   }
   return $response;
}

require("cssencode.php");

$data = AtD_http_post($postText, "service.afterthedeadline.com", $url);
header("Content-Type: text/css");
echo encode_css($data[1]);

?>
