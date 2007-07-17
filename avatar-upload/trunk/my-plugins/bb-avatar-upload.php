<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.6.1
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

		// Default avatar - set 'use_default' to '0' to display Identicon instead of default
		// The default URI is in the '$this->avatar_dir' folder.
		$this->default_avatar = array( 	
			'use_default' => 1,
			'uri' =>  bb_get_option('uri') . $this->avatar_dir . 'default.png',
			'width' => 80,
			'height' => 80,
			'alt' => "User has not uploaded an avatar"
		);

		// Identicon dimensions (width/height are equal):
		$config->identicon_size = 100; // pixels

		// Allowed file extensions
		$this->file_extns = array("gif", "jpg", "jpeg", "png");

		// Just pretty values (Kilobytes/megabytes) for output use
		$this->max_kbytes = round($this->max_bytes / 1024, 2);
		$this->max_mbytes = round($this->max_bytes / 1048576, 2);
	}
}

// Display the avatar image
function avatarupload_display($id)
{
	if ($a = avatarupload_get_avatar($id))
	{
		echo '<img src="'.$a[0].'" width="'.$a[1].'" height="'.$a[2].'" alt="'.$a[4].'" />';
	} else {
		$config = new avatarupload_config();

		if ($config->default_avatar['use_default'] == 1)
		{
			// Use a "genric" default avatar
			echo '<img src="'.$config->default_avatar['uri'].'" width="'.$config->default_avatar['width']
			.'" height="'.$config->default_avatar['height'].'" alt="'.$config->default_avatar['alt'].'" />';
		} else {
			// Or use Identicons instead.  New users will have an identicon automatically
			// created when they join, but this is for existing users with no avatar.

			felapplyidenticon($id); // create identicon

			// now fetch it from the database
			if ($a = avatarupload_get_avatar($id))
			{
				echo '<img src="'.$a[0].'" width="'.$a[1].'" height="'.$a[2].'" alt="'.$a[4].'" />';
			}
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


//  bbPress Identicon function by Fel64
function felapplyidenticon( $felID )
{
	$config = new avatarupload_config();
	$user = bb_get_user( $felID );

	$ifilename = strtolower($user->user_login) . "." . 'png';
	$ifilepath = BBPATH . $config->avatar_dir . $ifilename;

	// include the Identicon class.
	require_once("identicon.php");

	if (class_exists("identicon")) { $identicon = new identicon; }

	if( $identicon )
	{
		$felidenticon = $identicon->identicon_build( $user->user_login, '', false, '', false );

		if( imagepng( $felidenticon, $ifilepath ) )
		{
			$meta_avatar = $ifilename."?".time().'|'.$config->identicon_size.'|'.$config->identicon_size.'|identicon';
			bb_update_usermeta( $felID, 'avatar_file', $meta_avatar );
			$success_message = "Your identicon has been made.";
		}
	}
}

// Is user using an Identicon?
function usingidenticon($id)
{
	if ($a = avatarupload_get_avatar($id, 0, 1))
	{
		return ($a[3] == "identicon") ? true : false;
	} else {
		return false;
	}
}

?>