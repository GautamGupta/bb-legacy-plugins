<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.5
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

// Configuration Settings
class avatarupload_config
{
	function avatarupload_config()
	{
		// Avatar folder location (default is 'avatars' in the bbPress root folder)
		// You must create the folder before you install this plugin.
		$this->avatar_dir = "avatars/"; // remember to include trailing slash

		// Define maximum values allowed
		$this->max_width = 150; // pixels
		$this->max_height = 150; // pixels
		$this->max_bytes = 1048576; // filesize (1024 bytes = 1 KB / 1048576 bytes = MB)

		// Default avatar - set 'use_default' to '0' to display no image instead of default
		// The default URI is in the '$this->avatar_dir' folder.
		$this->default_avatar = array( 	
			'use_default' => 1,
			'uri' =>  bb_get_option('uri') . $this->avatar_dir . 'default.png',
			'width' => 80,
			'height' => 80,
			'alt' => "User has not uploaded an avatar"
		);

		// Allowed file extensions
		$this->file_extns = array("gif", "jpg", "jpeg", "png");

		// Just pretty values (Kilobytes/megabytes) for output use
		$this->max_kbytes = round($this->max_bytes / 1024, 2);
		$this->max_mbytes = round($this->max_bytes / 1048576, 2);
	}
}

// Display the avatar image
function avatarupload_display($id, $status='')
{
	if ($a = avatarupload_get_avatar($id))
	{
		echo '<img src="'.$a[0];
		echo ($status == 'new') ? '?'.time() : '';
		echo'" width="'.$a[1].'" height="'.$a[2].'" alt="'.$a[4].'" />';
	} else {
		$config = new avatarupload_config();

		if ($config->default_avatar['use_default'] == 1)
		{
			echo '<img src="'.$config->default_avatar['uri'].'" width="'.$config->default_avatar['width']
			.'" height="'.$config->default_avatar['height'].'" alt="'.$config->default_avatar['alt'].'" />';
		}
	}
}

// Get the avatar URI ($id = user->ID, $fulluri = full url to image,
// $force_db = get avatar from database where 'usermeta' not already available)
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
		$config = new avatarupload_config();
		$a[0] = bb_get_option('uri') . $config->avatar_dir . $a[0];
	}

	// Add the username for use in 'alt' attribute to end of array
	$a[] = $user->user_login;

	return $a;
}

// Add an "Upload Avatar" tab to the Profile menu
function add_avatar_tab()
{
	global $self;

	if ($self != 'avatar-upload.php') {
		add_profile_tab(__('Avatar'), 'edit_profile', 'moderate', 'avatar-upload.php');
	}
}
add_action( 'bb_profile_menu', 'add_avatar_tab' );

?>