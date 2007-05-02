<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://bbpress.org/plugins/topic/46
Version: 0.3
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

require_once('./bb-load.php'); // load bbPress config 
bb_auth(); // logged in?

// Grab user id
if ( isset($_GET['id']) ) {
	$user_id = (int) $_GET['id'];
} else {
	$user_id = intval( 0 );
}

// This user may NOT be the user who's avatar is being uploaded,
// this allows an Admin/Moderator to update another user's avatar
// (in the event that the user's avatar is objectionable!)
$current_user = bb_get_user(bb_get_current_user_info('id'));

// User who's profile is being updated
$user = bb_get_user( $user_id ); // user info

// No user found with that ID
if ( !$user ) {
	bb_die(__('User not found.')); // no user found
}

// Only allow the correct User or an Admin/Moderator to upload
// but not if they are a bozo!
if ( ($user->ID != $current_user->ID && !bb_current_user_can( 'moderate' )) || $current_user->is_bozo )
{
	bb_die(__('You do not have permission to upload an avatar for this user.'));
}

// Get config variables
$av_opts = avatarupload_config();
$av_opts['mime_types']['jpeg'] = $av_opts['mime_types']['jpg'];
$av_opts['max_kbytes'] = round($av_opts['max_bytes']/1024, 2); // Just a pretty value for output use

// Some potential error messages in human readable form
$errorcodes = array(
	"- no error (this message will never be shown) -",
	"The image file is too big, the maximum file size allowed is {$av_opts['max_kbytes']} KB.",
	"The image file is too big, the maximum file size allowed is {$av_opts['max_kbytes']} KB.",
	"The file was only partially uploaded - the connection may have been interrupted.",
	"The image file does not appear to have been uploaded - did you select an image?",
	"The file does not appear to be a valid GIF, JPG/JPEG or PNG image type.",
	"The image file could not be saved to the avatars folder.",
	"Image dimensions must not be greater than {$av_opts['max_width']} x {$av_opts['max_height']} pixels.",
	"The avatar filename may only contain upper/lower case letters, numbers, underscores or dashes."
);

/* --- Start Avatar Upload --- */

if (!empty($_FILES['p_browse']))
{
	$current_avatar = avatarupload_get_avatar($user_id, 0, 1); // for comparison later

	$img_errs = 0;
	$error = 0;

	$img = $_FILES['p_browse']; // grab image upload
	$img_name = $img['name'];
	$img_type = $img['type'];
	$img_temp = $img['tmp_name'];
	$img_size = $img['size'];
	$img_errs = $img['error'];

	$img_ext = substr($img_name, strrpos($img_name, ".")+1); // file extension

	$user_filename = strtolower($user->user_login) . "." . $img_ext; // build filename

	if (!eregi("^([-a-z0-9_]+)\.([a-z]+)$", $img_name)) { // filename not valid [A-Z/a-z, 0-9, _, -]
		// we don't worry about file extension here, this is to stop things like: 'nasty.exe?.jpg'
		$img_errs = 8;
		$error++;
	}

	if ($img_errs == 4) { // No image was uploaded
		$error++;
	}

	if ($error == 0 && $img_errs == 3) { // The image was partially uploaded
		$error++;
	}

	if ($error == 0 && ($img_errs == 1 || $img_errs == 2)  || $img_size > $av_opts['max_bytes']) { 
		// File size exceeds max_bytes
		$img_errs = 1;
		$error++;
	}

	if ($error == 0 && (!in_array($img_type, $av_opts['mime_types'][$img_ext]) || 
			!in_array($img_ext, $av_opts['file_extns'])) ) {
		// Check for invalid and/or mismatched mime-type and file extensions
		$img_errs = 5;
		$error++;
	}

	if ($error == 0 && !file_exists($img_temp)) { // File not saved to temp folder
		$img_errs = 4;
		$error++;
	}

	if ($error == 0 && !is_uploaded_file($img_temp)) { // File not saved to temp folder
		$img_errs = 4;
		$error++;
	}

	if ($error == 0)
	{
		// Get the dims and file type
		$dims = getimagesize($img_temp);
		$img_w = $dims[0];
		$img_h = $dims[1];

		if ($img_w > $av_opts['max_width'] || $img_h > $av_opts['max_height']) {
			// File dims greater than max_width/max_height
			$img_errs = 7;
			$error++;
		}
	}

	if ($error == 0 && !move_uploaded_file($img_temp, BBPATH . $av_opts['avatar_dir'] . $user_filename))
	{ // Can save to avatars folder (does it exist?)
		$img_errs = 6;
		$error++;
	}

	if ($img_errs > 0) {
		bb_die(__($errorcodes[$img_errs])); // Display appropriate error message
	} else {
		if (!empty($current_avatar[0]) && $user_filename != $current_avatar[0])
		{	// compare 'new' and 'current' avatar filenames - if different, delete 'current'
			// this will most likely only happen when the new avatar has a different extension
			unlink(BBPATH . $av_opts['avatar_dir'] . $current_avatar[0]);
		}

		$meta_avatar = $user_filename . "|" . $img_w . "|" . $img_h . "|avatar-upload";
		bb_update_usermeta( $user_id, 'avatar_file', $meta_avatar );
		$success_message = "Your avatar has been uploaded.";
	}
}

bb_load_template( 'avatar.php', array('success_message', 'av_opts') );
?>