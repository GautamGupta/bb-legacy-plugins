<?php
/*
Plugin Name: 68 Gravatar
Plugin URI: http://www.68kb.com
Description: This plugin allows you to generate a gravatar URL complete with rating, size, default, and border options. See the <a href="http://www.68kb.com/2007/10/28/bbpress-plugin-68-gravatars/">documentation</a> for syntax and usage.
Author: Eric Barnes and Tom Werner
Author URI: http://www.mojombo.com/
Version: 1.0
*/

function sixtyeight_gravatar($rating = false, $size = false, $default = false, $border = false) {
	global $bbdb;
	$id = get_post_author_id();
	$user = bb_get_user( $id );
	$usermail = $user->user_email;
	
	$out = "http://www.gravatar.com/avatar.php?gravatar_id=".md5($usermail);
	if($rating && $rating != '')
		$out .= "&amp;rating=".$rating;
	if($size && $size != '')
		$out .="&amp;size=".$size;
	if($default && $default != '')
		$out .= "&amp;default=".urlencode($default);
	if($border && $border != '')
		$out .= "&amp;border=".$border;
	echo $out;
}

?>