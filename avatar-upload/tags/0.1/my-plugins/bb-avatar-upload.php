<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://www.classical-webdesigns.co.uk/articles/43_bbpress-plugin-avatar-upload.html
Version: 0.1
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress. Admins can configure maximum allowed file size and image dimensions.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

function display_avatar($id, $status='')
{
	if ($a = get_avatar($id))
	{
		echo '<img src="'.bb_get_option('uri').'avatars/'.$a[0];
		echo ($status == 'new') ? '?'.time() : '';
		echo'" width="'.$a[1].'" height="'.$a[2].'" alt="Avatar" />';
	}
}

function get_avatar($id)
{
	global $bbdb;

	$bb_query = "SELECT meta_value FROM $bbdb->usermeta WHERE meta_key='avatar_file' AND user_id='$id' LIMIT 1";

	if ( $avatar = $bbdb->get_results($bb_query) ) {
		return explode("|", $avatar[0]->meta_value);
	} else {
		return false;
	}
}

?>