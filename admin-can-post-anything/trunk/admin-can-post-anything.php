<?php
/*
Plugin Name: Admin Can Post Anything
Plugin URI: http://bbpress.org/plugins/topic/55
Description: allows keymaster/administrators to post any content regardless of tag restrictions, including javascript and flash video embed
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.05
*/

function bb_admin_post_anything($text) {
if (bb_current_user_can('administrate') ) {
remove_filter('pre_post', 'trim');
remove_filter('pre_post', 'bb_encode_bad');
remove_filter('pre_post', 'bb_code_trick');
remove_filter('pre_post', 'force_balance_tags');
remove_filter('pre_post', 'stripslashes', 40); 
remove_filter('pre_post', 'bb_filter_kses', 50);
remove_filter('pre_post', 'addslashes', 55);
remove_filter('pre_post', 'bb_autop', 60);

$text = preg_replace("/<(script|noscript|embed|noembed|style)(.*?)<\/\\1>/se", 'str_replace(array("\r\n", "\r", "\n"), "<ADMINPreserveNewline>", "\\0")', $text);
$text=bb_autop($text,1);
$text = stripslashes(str_replace('<ADMINPreserveNewline>', "\n", $text));

// the following two lines are untested code to allow compatibility with the "allow images" plugin
// it's safe to remove it's filters because this only happens if admin are trying to post
remove_filter( 'pre_post', 'allow_images_encode_bad', 9 );
remove_filter( 'pre_post', 'allow_images', 52 );
}
return $text;
}
add_filter('pre_post', 'bb_admin_post_anything',8);

?>