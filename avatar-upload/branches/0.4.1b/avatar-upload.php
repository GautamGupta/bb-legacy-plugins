<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Branch: 0.4.1b
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress, with Identicon support.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

require_once('./bb-load.php'); // load bbPress config 
bb_auth(); // logged in?
bb_repermalink(); // Fix pretty-permalinks

// The current user may NOT be the user who's avatar is being uploaded,
// so we need to allow an Admin/Moderator to update another user's
// avatar (in the event that the user's avatar is objectionable!)
$current_user = bb_get_user(bb_get_current_user_info('id'));

// User who's profile is actually being updated (not necessarily the current user!)
$user = bb_get_user( $user_id );

// No user found with that ID
if ( !$user ) {
	bb_die(__('User not found.')); // no user found
}

// Only allow the correct User or an Admin/Moderator to upload but not if they are a bozo!
if ( ($user->ID != $current_user->ID && !bb_current_user_can( 'moderate' )) || $current_user->is_bozo )
{
	bb_die(__('You do not have permission to upload an avatar for this user.'));
}

/* --- Start Avatar Upload --- */

// Get Configuration Settings
$config = new avatarupload_config();

if (!empty($_FILES['p_browse']))
{
	$current_avatar = avatarupload_get_avatar($user_id, 0, 1); // for comparison later

	// Grab the uploaded image
	$img = $_FILES['p_browse'];
	$img_name = $img['name'];
	$img_type = $img['type'];
	$img_temp = $img['tmp_name'];
	$img_size = $img['size'];
	$img_errs = $img['error'];

	// Grab file extension
	$img_ext = strtolower(substr($img_name, strrpos($img_name, ".")+1));

	// Build the user's avatar filename
	$user_filename = strtolower($user->user_login) . "." . $img_ext;

	// Manual checks - some manual checks duplicate the PHP error codes where
	// they were introduced in later versions (e.g. PHP 5.x).

	// Does filesize exceeds max_bytes? You can't trust MAX_FILE_SIZE form field.
	if ($img_errs == 0 && $img_size > $config->max_bytes)
	{
		$img_errs = 2;
	}

	// Is file uploaded to temp folder?
	if ($img_errs == 0 && (!@file_exists($img_temp) || !@is_uploaded_file($img_temp)) )
	{
		$img_errs = 4;
	}

	// Is file extension valid and does it match the mime-type?
	if ($img_errs == 0 && (!@in_array($img_type, $config->mime_types[$img_ext]) || !@in_array($img_ext, $config->file_extns)) )
	{
		$img_errs = 8;
	}

	// Is it a valid filename? Stops things like 'nasty.exe?.jpg'
	if ($img_errs == 0 && !eregi("^([-a-z0-9_]+)\.([a-z]+)$", $img_name))
	{
		$img_errs = 9;
	}
		
	// Are file dimensions greater than max_width/max_height allowed?
	if  ($img_errs == 0)
	{
		// Get the dimensions
		$dims = @getimagesize($img_temp);
		$img_w = $dims[0];
		$img_h = $dims[1];

		if ($img_w > $config->max_width || $img_h > $config->max_height)
		{
			$img_errs = 10;
		}
	}

	// Did we move the image to the avatar folder successfully?
	if ($img_errs == 0 && !@move_uploaded_file($img_temp, BBPATH . $config->avatar_dir . $user_filename) )
	{
		$img_errs = 11;
	}


	// If we still have no errors add avatar to database, else show errors
	if ($img_errs == 0)
	{
		// Compare 'new' and 'current' avatar filenames
		if (!empty($current_avatar[0]) && $user_filename != $current_avatar[0])
		{
			// If different, delete 'current' - this will only occur when
			// the new and current avatars have different file extensions.
			@unlink(BBPATH . $config->avatar_dir . $current_avatar[0]);
		}

		// Add avatar to database as usermeta data.
		$meta_avatar = $user_filename . "|" . $img_w . "|" . $img_h . "|avatar-upload";
		bb_update_usermeta( $user_id, 'avatar_file', $meta_avatar );
		$success_message = "Your avatar has been uploaded.";
	}
	else
	{
		// Display an appropriate error message
		switch ($img_errs)
		{
			case 0: // UPLOAD_ERR_OK (no error)
				break;
			case 1: // UPLOAD_ERR_INI_SIZE
				bb_die(__("The file exceeds the maximum filesize of {$config->max_kbytes} KB"));
				break;
			case 2: // UPLOAD_ERR_FORM_SIZE
				bb_die(__("The file exceeds the maximum filesize of {$config->max_kbytes} KB"));
				break;
			case 3: // UPLOAD_ERR_PARTIAL
				bb_die(__("The file was only partially uploaded. Please try again."));
				break;
			case 4: // UPLOAD_ERR_NO_FILE
				bb_die(__("No file was uploaded - did you select an image to upload?"));
				break;
			case 6: // UPLOAD_ERR_NO_TMP_DIR (since PHP 4.3.10 and PHP 5.0.3)
				bb_die(__("Could not upload the file - there is no temporary folder."));
				break;
			case 7: // UPLOAD_ERR_CANT_WRITE (since PHP 5.1.0)
				bb_die(__("Failed to write file to disk - the server settings may not be correct."));
				break;
			case 8: // UPLOAD_ERR_EXTENSION (since PHP 5.2.0)
				bb_die(__("The file is not a valid GIF, JPG/JPEG or PNG image-type."));
				break;
			case 9: // custom error code
				bb_die(__("Filenames may only contain upper/lower case letters, numbers, underscores or dashes."));
				break;
			case 10: // custom error code
				bb_die(__("Image dimensions must not be greater than {$config->max_width} x {$config->max_height} pixels."));
				break;
			case 11: // custom error code
				bb_die(__("The file could not be saved to the 'avatars' folder."));
				break;
			default: // unknown error (this probably won't ever happen)
				bb_die(__("An unknown error has occurred."));
				break;
		}
	}
}

// Has user checked the "Use Identicon" option?
if( $_POST['identicon'] )
{
	felapplyidenticon( $user_id ); // create an identicon
}

bb_load_template( 'avatar.php', array('success_message', 'config') );
?>