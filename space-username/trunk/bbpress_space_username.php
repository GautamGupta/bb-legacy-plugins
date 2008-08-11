<?php
/*
Plugin Name: BBpress Space Username
Plugin URI: http://www.howtogeek.com
Description: Users can be use a username with a space
Author: The Geek & Frederic Petit
Author URI: http://www.howtogeek.com/
Version: 0.0.2
*/

function htg_resanitize( $text, $raw ) {
return preg_replace('/[^ \ta-z0-9_-]/i', '', str_replace("%20"," ",$raw));
}

function htg_repermalinkfix($permalink){
return str_replace(" ","%20",$permalink);
}

function htg_wp_redirect($location,$status){
global $is_IIS;
if(!stristr($location,' ')){
return $location;
}

$location = preg_replace('|[^ \ta-z0-9-~+_.?#=&;,/:%]|i', '', $location);
$location = wp_kses_no_null($location);

$strip = array('%0d', '%0a');
$location = str_replace($strip, '', $location);

if ( $is_IIS ) {
header("Refresh: 0;url=$location");
} else {
if ( php_sapi_name() != 'cgi-fcgi' )
status_header($status); // This causes problems on IIS and some FastCGI setups
header("Location: $location");
}
return false;
}

add_filter('wp_redirect','htg_wp_redirect',1,2);
add_filter('bb_repermalink_result','htg_repermalinkfix',1,1);
remove_filter('bb_user_sanitize','bb_user_sanitize');
add_filter('bb_user_sanitize','htg_resanitize',1,2);
?>
