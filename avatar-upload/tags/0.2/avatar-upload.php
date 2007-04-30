<?php
/*
Plugin Name: Avatar Upload
Plugin URI: http://www.classical-webdesigns.co.uk/articles/43_bbpress-plugin-avatar-upload.html
Version: 0.2
Description: Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress. Admins can configure maximum allowed file size and image dimensions.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

require_once('./bb-load.php'); // load bbPress config 
bb_auth(); // logged in?

/* --- CONFIGURATION VARIABLES (you can edit these) --- */

// Avatar folder location (default is 'avatars' in the bbPress root, 'BBPATH')
// You must create the 'avatars' folder before you install this plugin.
$avatar_dir = BBPATH . "avatars/"; // remember to include trailing slash

// Define maximum values allowed
$max_width = 150; // pixels
$max_height = 150; // pixels
$max_bytes = 51200; // default is 50 KB (51200 bytes)

// Allowed file extensions (*.gif, *.jpeg, *.jpg, *.png)
$allowed_extns = array("gif", "jpg", "jpeg", "png");

// Content types -- more stringent check on file types.
// Eg. a file with a '.png' extension MUST have an 'image/png' content-type!
// You probably want to leave this alone.
$allowed_types = array('gif'=>"image/gif", 'jpeg'=>"image/jpeg", 'jpg'=>"image/jpeg", 'png'=>"image/png");

/* --- STOP EDITING --- */

/* --- Start bbPress Stuff --- */

// If the script filename is not correct.
$file = '';
foreach ( array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']) as $name ) {
	if ( false !== strpos($name, '.php') ) {$file = $name;}
}
if (bb_find_filename($file) != 'avatar-upload.php') { // not the correct form!
	$sendto = bb_get_option('uri');
	wp_redirect( $sendto );
}

// Grab user id
if ( isset($_GET['id']) ) {
	$user_id = (int) $_GET['id'];
} else {
	$user_id = intval( 0 );
}

$user = bb_get_user( $user_id ); // user info

if ( !$user ) {
	bb_die(__('User not found.')); // no user found
}

// Do not let users upload an avatar if user is a bozo OR user id not equal to current user's id
// (AND user is not allowed to moderate).
if ( $user->is_bozo || ($user->ID != bb_get_current_user_info( 'id' ) && !bb_current_user_can( 'moderate' )) )
{
	bb_die(__('You do not have permission to upload an avatar for this user.'));
}
/* --- End bbPress Stuff --- */


$max_kbytes = round($max_bytes/1024, 2); // Just a pretty value for output use

// Some potential error messages in human readable form
$errorcodes = array(
	"- no error (this message will never be shown) -",
	"The image file is too big, the maximum file size allowed is $max_kbytes KB.",
	"The image file is too big, the maximum file size allowed is $max_kbytes KB.",
	"The file was only partially uploaded - the connection may have been interrupted.",
	"The image file does not appear to have been uploaded - did you select an image?",
	"The file does not appear to be a valid GIF, JPG/JPEG or PNG image type.",
	"The image file could not be saved to the avatars folder.",
	"Image dimensions must not be greater than $max_width x $max_height pixels.",
	"The avatar filename may only contain upper/lower case letters, numbers, underscores or dashes."
);

/* --- Start Avatar Upload --- */

if (!empty($_FILES['p_browse']))
{
	$current_avatar = explode("|", $user->avatar_file); // for comparison later

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

	if ($error == 0 && ($img_errs == 1 || $img_errs == 2)  || $img_size > $max_bytes) { 
		// File size exceeds max_bytes
		$img_errs = 1;
		$error++;
	}

	if ($error == 0 && (!in_array($img_type, $allowed_types) || !in_array($img_ext, $allowed_extns)) ) {
		// file extension and file type check #1 - simple check to weed out bad filenames
		$img_errs = 5;
		$error++;
	}

	if ($error == 0 &&  $allowed_types[$img_ext] != $img_type) {
		// file extension and file type check #2 - ok, they are in the list, but to do match?
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

		if ($img_w > $max_width || $img_h > $max_height) { // File dims greater than max_width/max_height
			$img_errs = 7;
			$error++;
		}
	}

	if ($error == 0 && !move_uploaded_file($img_temp, $avatar_dir . $user_filename))
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
			unlink(BBPATH. "avatars/" . $current_avatar[0]);
		}

		$meta_avatar = $user_filename . "|" . $img_w . "|" . $img_h;
		bb_update_usermeta( $user_id, 'avatar_file', $meta_avatar );
		$success_message = "Your avatar has been uploaded.";
	}
}

// this array will be used in the template
$img_requirements = array(
	'img_types' => $allowed_extns,
	'max_width' => $max_width,
	'max_height' => $max_height,
	'max_bytes' => $max_bytes,
	'max_kbytes' => $max_kbytes,
	'img_ct' => $allowed_types
);

bb_load_template( 'avatar.php', array('success_message', 'img_requirements') );
?>