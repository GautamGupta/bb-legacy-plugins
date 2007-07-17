<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.6.1
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress.
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
	$img_name = basename($img['name']);
	$img_temp = $img['tmp_name'];
	$img_size = $img['size'];
	$img_errs = $img['error'];

	// Grab file extension
	$img_ext = strtolower(substr($img_name, strrpos($img_name, ".")+1));

	// Build the user's avatar filename
	$user_filename = strtolower($user->user_login) . "." . $img_ext;

	// Manual checks - some manual checks duplicate the PHP error codes where
	// they were introduced in later versions (e.g. PHP 5.x).

	// Is file uploaded to temp folder?
	if ($img_errs == 0 && (!@file_exists($img_temp) || !@is_uploaded_file($img_temp)) )
	{
		$img_errs = 4;
	}

	// Is file extension and mime-type valid? Use getimagesize() to check mime-type.
	// Removes need for mime-type array - if it isn't a valid image, it won't work!
	// (we also grab dimensions for later use)
	if ($img_errs == 0 && (!@in_array($img_ext, $config->file_extns) || 
			!list($img_w, $img_h, $img_type) = @getimagesize($img_temp)) )
	{
		$img_errs = 8;
	}

	// Is it a valid filename? Stops things like 'nasty.exe?.jpg'
	if ($img_errs == 0 && eregi("\#|\?|\&|\%|\"|\||\'|\*|\`", $img_name))
	{
		$img_errs = 9;
	}
		
	// Are file dimensions greater than max_width/max_height allowed? (Resize if so)
	if  ($img_errs == 0)
	{
		if ( !$resized = avatar_resize($img_temp, $img_w, $img_h, $img_type) )
		{
			$img_errs = 10;
		} else {
			$img_size = @filesize($img_temp);
			list($img_w, $img_h) = $resized; // overwrite image width / height
		}
	}

	// Does filesize exceeds max_bytes? You can't trust MAX_FILE_SIZE form field.
	if ($img_errs == 0 && $img_size > $config->max_bytes)
	{
		$img_errs = 2;
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

		// Add avatar to database as usermeta data (append unix time to help browser caching probs).
		$meta_avatar = $user_filename . "?" . time() . "|" . $img_w . "|" . $img_h . "|avatar-upload";
		bb_update_usermeta( $user_id, 'avatar_file', $meta_avatar );
		$success_message = "Your avatar has been uploaded.";
		sleep(3); // give image a chance to copy from temp area
	}
	else
	{
		// Display an appropriate error message
		switch ($img_errs)
		{
			case 0: // UPLOAD_ERR_OK (no error)
				break;
			case 1: // UPLOAD_ERR_INI_SIZE
				$error_message = __("The file exceeds the maximum filesize of {$config->max_kbytes} KB");
				break;
			case 2: // UPLOAD_ERR_FORM_SIZE
				$error_message = __("The file exceeds the maximum filesize of {$config->max_kbytes} KB");
				break;
			case 3: // UPLOAD_ERR_PARTIAL
				$error_message = __("The file was only partially uploaded. Please try again.");
				break;
			case 4: // UPLOAD_ERR_NO_FILE
				$error_message = __("No file was uploaded - did you select an image to upload?");
				break;
			case 6: // UPLOAD_ERR_NO_TMP_DIR (since PHP 4.3.10 and PHP 5.0.3)
				$error_message = __("Could not upload the file - there is no temporary folder.");
				break;
			case 7: // UPLOAD_ERR_CANT_WRITE (since PHP 5.1.0)
				$error_message = __("Failed to write file to disk - the server settings may not be correct.");
				break;
			case 8: // UPLOAD_ERR_EXTENSION (since PHP 5.2.0)
				$error_message = __("The file is not a valid GIF, JPG/JPEG or PNG image-type.");
				break;
			case 9: // custom error code
				$error_message = __("Filenames may not include the following: # ? &amp; % \" | ' * `");
				break;
			case 10: // custom error code
				$error_message = __("The image could not be resized, please contact your forum admin.");
				break;
			case 11: // custom error code
				$error_message = __("The file could not be saved to the 'avatars' folder.");
				break;
			default: // unknown error (this probably won't ever happen)
				$error_message = __("An unknown error has occurred.");
				break;
		}
	}
}

// Has user checked the "Use Identicon" option?
if( $_POST['identicon'] )
{
	felapplyidenticon( $user_id ); // create an identicon
}

bb_load_template( 'avatar.php', array('success_message', 'error_message', 'config') );


/* Image Resize */

function avatar_resize($img_temp, $img_w, $img_h, $img_type)
{
	global $config;

	// if either the image width or height is greater than the maximums allowed
	if ($img_w > $config->max_width || $img_h > $config->max_height)
	{
		// To maintain aspect ratio we need to resize proportionally

		if ($img_w > $img_h)
		{
			// width is greater - make width 'max_width' and proportion height
			$new_width = $config->max_width;
			$new_height = round($img_h * ($config->max_width/$img_w));
		}
		else if ($img_w < $img_h)
		{
			// height is greater - make height 'max_height' and proportion width
			$new_width = round($img_w * ($config->max_height/$img_h));
			$new_height = $config->max_height;
		}
		else
		{
			// equal (square) - make both 'max' values
			$new_width = $config->max_width;
			$new_height = $config->max_height;
		}
	}
	else
	{
		// image already within maximum limits - do no resize
		return array($img_w, $img_h);
	}

	// Resize the image preserving image type

	switch ($img_type)
	{
		case 1:
			// GIF
			$im1 = @imagecreatefromgif($img_temp);
			$im2 = @imagecreate($new_width, $new_height) or $error = 1;
			@imagecopyresampled ($im2, $im1, 0, 0, 0, 0, $new_width, $new_height, $img_w, $img_h);
			@imagegif($im2, $img_temp);
			break;

		case 2:
			// JPEG
			$im1 = @imagecreatefromjpeg($img_temp);
			$im2 = @imagecreatetruecolor($new_width, $new_height) or $error = 1;
			@imagecopyresampled ($im2, $im1, 0, 0, 0, 0, $new_width, $new_height, $img_w, $img_h);
			@imagejpeg($im2, $img_temp, 100); // quality integer might become config variable
			break;

		case 3:
			// PNG
			$im1 = @imagecreatefrompng($img_temp);
			$im2 = @imagecreatetruecolor($new_width, $new_height) or $error = 1;
			@imagecopyresampled ($im2, $im1, 0, 0, 0, 0, $new_width, $new_height, $img_w, $img_h);
			@imagepng($im2, $img_temp);
			break;

		default:
			$error = 1;
			break;
	}

	@imagedestroy($im2);
	@imagedestroy($im1);

	if ($error > 0)
	{
		// Something went wrong.
		return false;
	} else {
		// return the new sizes
		return array($new_width, $new_height);
	}
}

?>