<?php
/*
Plugin Name: AtAL Auto URI
Plugin URI: 
Description: 自動切換 bbPress 的 Domain 和 URI 適應多網址或分流。
Author: 蒼時弦や
Author URI: http://frost.tw/
Version: 0.1
*/
function atal_auto_domain($r){
	return 'http://'.$_SERVER['HTTP_HOST'];
}

function atal_auto_uri($r){
	$host = $_SERVER['HTTP_HOST'];
	$r = preg_replace("/http:\/\/(.+?[^\/])([$\/].*?)/i", "http://{$host}$2", $r);
	return $r;
}

add_action('bb_get_option_domain', 'atal_auto_domain');
add_action('bb_get_option_uri', 'atal_auto_uri');

?>