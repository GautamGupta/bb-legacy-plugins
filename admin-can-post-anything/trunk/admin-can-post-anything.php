<?php
/*
Plugin Name: Admin Can Post Anything
Plugin URI:
Description: allows keymaster/administrators to post any content regardless of tag restrictions
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.04
*/

function bb_admin_post_anything($text) {
if (bb_current_user_can('administrate') ) {
remove_filter('pre_post', 'encode_bad' );
remove_filter('pre_post', 'bb_encode_bad' );
remove_filter('pre_post', 'bb_filter_kses', 50);
remove_filter('pre_post', 'addslashes', 55);
remove_filter('pre_post', 'bb_autop', 60);
$text=bb_autop(addslashes($text));  // I don't completely understand why this is necessary here but it is

// the following two lines are untested code to allow compatibility with the allow images plugin
// it's safe to remove it's filters because this only happens if admin are trying to post
remove_filter( 'pre_post', 'allow_images_encode_bad', 9 );
remove_filter( 'pre_post', 'allow_images', 52 );
}
return $text;
}
add_filter('pre_post', 'bb_admin_post_anything',8);
?>
