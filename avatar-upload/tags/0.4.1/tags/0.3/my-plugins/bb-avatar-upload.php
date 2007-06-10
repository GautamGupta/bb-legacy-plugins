<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.3
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

// Configuration Settings
function avatarupload_config()
{
	return array(

		// Avatar folder location (default is 'avatars' in the bbPress root folder)
		// You must create the folder before you install this plugin.
		'avatar_dir' => "avatars/", // remember to include trailing slash

		// Define maximum values allowed
		'max_width' => 150, // (pixels)
		'max_height' => 150, // (pixels)
		'max_bytes' => 51200, // filesize (bytes; 1024 bytes = 1 KB)

		// Default avatar - set 'use_default' to '0' to display no image instead of default
		'default_avatar' => array( 	
			'use_default' => 1,
			'uri' => bb_get_option('uri').'avatars/default.png', // full uri of image
			'width' => 80,
			'height' => 80,
			'alt' => "User has not uploaded an avatar"
		),

		// Allowed file extensions
		'file_extns' => array("gif", "jpg", "jpeg", "png"),

		// Mime-Types (list thanks to SamBauers) - you probably want to leave this alone.
		'mime_types' => array(
			'gif' => array(
				'image/gif',
				'image/gi_'
			),
			'jpg' => array(
				'image/jpeg',
				'image/jpg',
				'image/jp_',
				'image/pjpeg',
				'image/pjpg',
				'image/pipeg',
				'application/jpg',
				'application/x-jpg'
			),
			'png' => array(
				'image/png',
				'image/x-png',
				'application/png',
				'application/x-png'
			)
		)
	);
}

// Display the avatar image
function avatarupload_display($id, $status='')
{
	if ($a = avatarupload_get_avatar($id))
	{
		echo '<img src="'.$a[0];
		echo ($status == 'new') ? '?'.time() : '';
		echo'" width="'.$a[1].'" height="'.$a[2].'" alt="Avatar" />';
	} else {
		$config = avatarupload_config();
		$default = $config['default_avatar'];
		if ($default['use_default'] == 1)
		{
			echo '<img src="'.$default['uri'].'" width="'.$default['width'].'" height="'.$default['height']
			.'" alt="'.$d['alt'].'" />';
		}
	}
}

// Get the avatar URI
function avatarupload_get_avatar($id, $fulluri=1, $force_db=0)
{
	global $bbdb, $user;

	if ($id == $user->ID && $force_db == 0)
	{
		if (!empty($user->avatar_file)) {
			$a = explode("|", $user->avatar_file);
		} else {
			return false;
		}
	}
	else
	{
		$bb_query = "SELECT meta_value FROM $bbdb->usermeta WHERE meta_key='avatar_file' AND user_id='$id' LIMIT 1";

		if ( $avatar = $bbdb->get_results($bb_query) ) {
			$a = explode("|", $avatar[0]->meta_value);
		} else {
			return false;
		}
	}
	
	// do we want the full uri?
	if ($fulluri == 1)
	{
		$config = avatarupload_config();
		$a[0] = bb_get_option('uri') . $config['avatar_dir'] . $a[0];
	}
	return $a;
}

// Add an "Upload Avatar" tab to the Profile menu
function add_avatar_tab()
{
	add_profile_tab(__('Avatar'), 'edit_profile', 'moderate', 'avatar-upload.php');
}
add_action( 'bb_profile_menu', 'add_avatar_tab' );

?>