<?php
/*
Plugin Name: Admin Can Post Anything
Plugin URI: http://bbpress.org/plugins/topic/55
Description: allows administrators to post any content regardless of member restrictions, including javascript and flash video embed
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.0
*/

add_filter('pre_post', 'bb_admin_post_anything',8);
add_action('edit_form','bb_admin_post_anything_edit',9);
add_action('post_form','bb_admin_post_anything_post',9);

function bb_admin_post_anything($text) {
if (empty($_POST['admin_post_anything']) || !bb_current_user_can('administrate') ) {return $text;}

remove_filter('pre_post', 'trim');
remove_filter('pre_post', 'bb_encode_bad');
remove_filter('pre_post', 'bb_code_trick');
remove_filter('pre_post', 'stripslashes', 40); 
remove_filter('pre_post', 'bb_filter_kses', 50);
remove_filter('pre_post', 'addslashes', 55);
remove_filter('pre_post', 'bb_autop', 60);
remove_filter( 'pre_post', 'allow_images_encode_bad', 9 );
remove_filter( 'pre_post', 'allow_images', 52 );

$text=str_replace(array("\r\n","\r"),"\n",$text);
$text = preg_replace("|(\n+)(`.*?`)|simU","<ADMINPreserve>\n\n\\2", $text);
$text=bb_code_trick($text);
$text = preg_replace_callback("/<(script|noscript|object|param|embed|noembed|style|pre|code|ul|ol).*?<\/\\1>\n?/s","bb_admin_post_anything_preserve", $text);
$text = preg_replace_callback("/(<\/?(blockquote|bq|p|h.|del|ins|div|pre|li|tr|td)( [^>]*)?>)\n/s","bb_admin_post_anything_preserve", $text);
// $text=bb_autop($text,1);
$text=nl2br($text);
$text = stripslashes(str_replace('<ADMINPreserve>', "\n", $text));

return $text;
}

function bb_admin_post_anything_preserve($text) {return str_replace("\n","<ADMINPreserve>",$text[0]);}

function bb_admin_post_anything_edit() {bb_admin_post_anything_post(1);}
function bb_admin_post_anything_post($edit=0) {
if (!bb_current_user_can('administrate')) {return;}
global $bb_post; $checked="";
$tags=bb_allowed_tags(); $tags['br']=true; $tags['p']=true; $tags = implode('|',array_keys($tags))."|\s|\/";
$style="margin:0.4em 0;height:1.4em; width:1.4em;".(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') ? 'font-size:1.4em;' : '')."clear:left; vertical-align:middle;";
if (!empty($edit) && !empty($bb_post->post_text) && preg_match("/<(?!($tags))/si", $bb_post->post_text)) {$checked="checked='checked'";}
echo "<label><input style='$style' name='admin_post_anything' type='checkbox' value='1' $checked><strong> ".__("override posting restrictions")." </strong></label>";
}
?>