<?php
/*
Plugin Name: User Photo for bbPress
Plugin URI: http://alumnos.dcc.uchile.cl/~egraells/
Description: Allows users to associate photos and avatars with their accounts by accessing their Profile page. Based on the User Photo plugin for WordPress.
Version: 1.1
Author: Eduardo Graells
Author URI: http://alumnos.dcc.uchile.cl/~egraells
License: GPL3
*/

if (!defined('ABSPATH')) 
	define('ABSPATH', BB_PATH);

define('USERPHOTO_PATH', ABSPATH . "my-plugins/user-photo-for-bbpress/avatars/");
define('USERPHOTO_URL', bb_get_option('uri') . 'my-plugins/user-photo-for-bbpress/avatars/');

define('USE_GRAVATARS_IF_NO_PHOTO', 0);	

define('USERPHOTO_FULL_SIZE', 150);
define('USERPHOTO_THUMBNAIL_SIZE', 80);
define('USERPHOTO_JPEG_COMPRESSION', 90);

$userphoto_validtypes = array(
	"image/jpeg" => true,
	"image/pjpeg" => true,
	"image/gif" => true,
	"image/png" => true,
	"image/x-png" => true
);

bb_register_activation_hook(__FILE__, 'userphoto_activation');
load_plugin_textdomain('user-photo');

function userphoto_activation() {
	bb_update_option("userphoto_jpeg_compression", USERPHOTO_JPEG_COMPRESSION);
	bb_update_option("userphoto_maximum_dimension", USERPHOTO_FULL_SIZE);
	bb_update_option("userphoto_thumb_dimension", USERPHOTO_THUMBNAIL_SIZE);
}

bb_register_deactivation_hook(__FILE__, 'userphoto_deactivation');

function userphoto_deactivation() {
	bb_delete_option("userphoto_jpeg_compression");
	bb_delete_option("userphoto_maximum_dimension");
	bb_delete_option("userphoto_thumb_dimension");
}
 

function userphoto_profile_update($userID){
	global $userphoto_validtypes, $errors;

	$current_user = bb_current_user();
	$userdata = bb_get_user($userID);

	#Delete photo
	if (@$_POST['userphoto_delete']) {
		if ($userdata->userphoto_image_file) {
			$imagepath = USERPHOTO_PATH . basename($userdata->userphoto_image_file);
			
			if(file_exists($imagepath) && !@unlink($imagepath))
				$errors->add('userphoto_error', __("Unable to delete photo.", 'user-photo'));
			else {
				bb_delete_usermeta($userID, "userphoto_image_file");
//				bb_delete_usermeta($userID, "userphoto_image_width");
//				bb_delete_usermeta($userID, "userphoto_image_height");
			}
		}
		
		if ($userdata->userphoto_thumb_file) {
			$thumbpath = USERPHOTO_PATH . basename($userdata->userphoto_thumb_file);

			if (file_exists($thumbpath) && !@unlink($thumbpath))
				$errors->add('userphoto_error', __("Unable to delete photo thumbnail.", 'user-photo'));
			else {
				bb_delete_usermeta($userID, "userphoto_thumb_file");
//				bb_delete_usermeta($userID, "userphoto_thumb_width");
//				bb_delete_usermeta($userID, "userphoto_thumb_height");
			}
		}
		
	}
	#Upload photo or change approval status
	else {
		#Upload the file
		if(isset($_FILES['userphoto_image_file']) && @$_FILES['userphoto_image_file']['name']){
			
			#Upload error
			$error = '';
			if($_FILES['userphoto_image_file']['error']){
				switch($_FILES['userphoto_image_file']['error']){
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$error = __("The uploaded file exceeds the max upload size.", 'user-photo');
						break;
					case UPLOAD_ERR_PARTIAL:
						$error = __("The uploaded file was only partially uploaded.", 'user-photo');
						break;
					case UPLOAD_ERR_NO_FILE:
						$error = __("No file was uploaded.", 'user-photo');
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$error = __("Missing a temporary folder.", 'user-photo');
						break;
					case UPLOAD_ERR_CANT_WRITE:
						$error = __("Failed to write file to disk.", 'user-photo');
						break;
					case UPLOAD_ERR_EXTENSION:
						$error = __("File upload stopped by extension.", 'user-photo');
						break;
					default:
						$error = __("File upload failed due to unknown error.", 'user-photo');
				}
			}
			else if(!$_FILES['userphoto_image_file']['size'])
				$error = sprintf(__("The file &ldquo;%s&rdquo; was not uploaded. Did you provide the correct filename?", 'user-photo'), $_FILES['userphoto_image_file']['name']);
			else if(@!$userphoto_validtypes[$_FILES['userphoto_image_file']['type']]) //!preg_match("/\.(" . join('|', $userphoto_validextensions) . ")$/i", $_FILES['userphoto_image_file']['name'])) ||
				$error = sprintf(__("The uploaded file type &ldquo;%s&rdquo; is not allowed.", 'user-photo'), $_FILES['userphoto_image_file']['type']);
			
			$tmppath = $_FILES['userphoto_image_file']['tmp_name'];
			
			$imageinfo = null;
			$thumbinfo = null;
			if (!$error) {
				$userphoto_maximum_dimension = bb_get_option( 'userphoto_maximum_dimension' );
				
				$imageinfo = getimagesize($tmppath);
				if (!$imageinfo || !$imageinfo[0] || !$imageinfo[1])
					$error = __("Unable to get image dimensions.", 'user-photo');
				else if ($imageinfo[0] > $userphoto_maximum_dimension || $imageinfo[1] > $userphoto_maximum_dimension){
					if (userphoto_resize_image($tmppath, null, $userphoto_maximum_dimension, $error))
						$imageinfo = getimagesize($tmppath);
				}
			}
			
			if (!$error) {
				$dir = USERPHOTO_PATH;
				
				if (!file_exists($dir) && !mkdir($dir, 0777))
					$error = __("The userphoto upload content directory does not exist and could not be created. Please ensure that you have write permissions for the /wp-content/uploads/ directory.", 'user-photo');
				
				if (!$error) {

					$imagefile = preg_replace('/^.+(?=\.\w+$)/', $userdata->user_nicename, $_FILES['userphoto_image_file']['name']);
					$imagepath = $dir . '/' . $imagefile;
					$thumbfile = preg_replace("/(?=\.\w+$)/", '.thumbnail', $imagefile);
					$thumbpath = $dir . '/' . $thumbfile;
					
					if(!move_uploaded_file($tmppath, $imagepath))
						$error = __("Unable to move the file to the user photo upload content directory.", 'user-photo');
					else {
						chmod($imagepath, 0666);
						
						$userphoto_thumb_dimension = bb_get_option( 'userphoto_thumb_dimension' );

						if (!($userphoto_thumb_dimension >= $imageinfo[0] && $userphoto_thumb_dimension >= $imageinfo[1]))
							userphoto_resize_image($imagepath, $thumbpath, $userphoto_thumb_dimension, $error);
						else {
							copy($imagepath, $thumbpath);
							chmod($thumbpath, 0666);
						}
						$thumbinfo = getimagesize($thumbpath);
						
						#Update usermeta
						
						bb_update_usermeta($userID, "userphoto_image_file", $imagefile); 
						//bb_update_usermeta($userID, "userphoto_image_width", $imageinfo[0]); 
						//bb_update_usermeta($userID, "userphoto_image_height", $imageinfo[1]);
						bb_update_usermeta($userID, "userphoto_thumb_file", $thumbfile);
						//bb_update_usermeta($userID, "userphoto_thumb_width", $thumbinfo[0]);
						//bb_update_usermeta($userID, "userphoto_thumb_height", $thumbinfo[1]);
			
						#if($oldFile && $oldFile != $newFile)
						#	@unlink($dir . '/' . $oldFile);
					}
				}
			}
		}
	}
	
	if ($error)
		bb_update_usermeta($userID, 'userphoto_error', $error);
	else
		bb_delete_usermeta($userID, "userphoto_error");

}

add_action('bb_delete_user', 'userphoto_delete_user');

function userphoto_delete_user($userID){
	$userdata = bb_get_user($userID);
	if($userdata->userphoto_image_file)
		@unlink(USERPHOTO_PATH . basename($userdata->userphoto_image_file));
	if($userdata->userphoto_thumb_file)
		@unlink(USERPHOTO_PATH . basename($userdata->userphoto_thumb_file));
}


function userphoto_display_selector_fieldset($userID){
	global $userphoto_error;
	
	$current_user = bb_current_user();
	$profileuser = bb_get_user($userID);
	$isSelf = ($profileuser->ID == $current_user->ID);
	
	if ($isSelf && !bb_current_user_can('write_posts'))
		return;
	
    ?>
    <fieldset id='userphoto'>
        <script type="text/javascript">
		var form = document.getElementById('your-profile');
		//form.enctype = "multipart/form-data"; //FireFox, Opera, et al
		form.encoding = "multipart/form-data"; //IE5.5
		form.setAttribute('enctype', 'multipart/form-data'); //required for IE6 (is interpreted into "encType")
		
		function userphoto_onclick(){
			var is_delete = document.getElementById('userphoto_delete').checked;
			document.getElementById('userphoto_image_file').disabled = is_delete;
		}
		
        </script>
        <legend><?php echo $isSelf ? _e("Your Photo", 'user-photo') : _e("Photo", 'user-photo') ?></legend>
        <?php if ($profileuser->userphoto_image_file): ?>
            <p class='image'><img src="<?php echo USERPHOTO_URL . $profileuser->userphoto_image_file . "?" . rand() ?>" alt="<?php _e("Full size image", 'user-photo'); ?>" /><br />
			<?php _e("Full size image", 'user-photo'); ?>
			</p>
			<p class='image'><img src="<?php echo USERPHOTO_URL . $profileuser->userphoto_thumb_file . "?" . rand() ?>" alt="<?php _e("Thumbnail image", 'user-photo'); ?>" /><br />
			<?php _e("Thumbnail image", 'user-photo'); ?>
			</p>
			<hr />

        <?php endif; ?>

        <?php if ($profileuser->userphoto_error): ?>
		<p id='userphoto-upload-error'><strong>Upload error:</strong> <?php echo $profileuser->userphoto_error ?></p>
		<?php endif; ?>
        <p id='userphoto_image_file_control'>
        <label><?php _e("Upload image:", 'user-photo') ?>
		<input type="file" name="userphoto_image_file" id="userphoto_image_file" /></label>
		</p>
		<?php if($profileuser->userphoto_image_file): ?>
		<p><label><input type="checkbox" name="userphoto_delete" id="userphoto_delete" onclick="userphoto_onclick()" /> <?php _e('Delete image?', 'user-photo')?></label></p>
		<?php endif; ?> 
    </fieldset>
    <?php
}

add_action('profile_edited', 'userphoto_profile_update');

function userphoto_resize_image($filename, $newFilename, $maxdimension, &$error){
	if(!$newFilename)
		$newFilename = $filename;
	$userphoto_jpeg_compression = (int) bb_get_option( 'userphoto_jpeg_compression' );
	
	$info = @getimagesize($filename);
	if(!$info || !$info[0] || !$info[1])
		$error = __("Unable to get image dimensions.", 'user-photo');
	//From WordPress image.php line 22
	else if (
		!function_exists( 'imagegif' ) && $info[2] == IMAGETYPE_GIF
		||
		!function_exists( 'imagejpeg' ) && $info[2] == IMAGETYPE_JPEG
		||
		!function_exists( 'imagepng' ) && $info[2] == IMAGETYPE_PNG
	)
		$error = __( 'Filetype not supported.', 'user-photo' );
	else {
		// create the initial copy from the original file
		if ( $info[2] == IMAGETYPE_GIF )
			$image = imagecreatefromgif( $filename );
		elseif ( $info[2] == IMAGETYPE_JPEG )
			$image = imagecreatefromjpeg( $filename );
		elseif ( $info[2] == IMAGETYPE_PNG )
			$image = imagecreatefrompng( $filename );
		if (!isset($image)) {
			$error = __("Unrecognized image format.", 'user-photo');
			return false;
		}
		if ( function_exists( 'imageantialias' ))
			imageantialias( $image, TRUE );

		// figure out the longest side

		if ( $info[0] > $info[1] ) {
			$image_width = $info[0];
			$image_height = $info[1];
			$image_new_width = $maxdimension;

			$image_ratio = $image_width / $image_new_width;
			$image_new_height = $image_height / $image_ratio;
			//width is > height
		} else {
			$image_width = $info[0];
			$image_height = $info[1];
			$image_new_height = $maxdimension;

			$image_ratio = $image_height / $image_new_height;
			$image_new_width = $image_width / $image_ratio;
			//height > width
		}

		$imageresized = imagecreatetruecolor( $image_new_width, $image_new_height);
		@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );

		// move the thumbnail to its final destination
		if ( $info[2] == IMAGETYPE_GIF ) {
			if (!imagegif( $imageresized, $newFilename ) )
				$error = __( "Thumbnail path invalid", 'user-photo');
		}
		elseif ( $info[2] == IMAGETYPE_JPEG ) {
			if (!imagejpeg( $imageresized, $newFilename, $userphoto_jpeg_compression ) )
				$error = __( "Thumbnail path invalid", 'user-photo');
		}
		elseif ( $info[2] == IMAGETYPE_PNG ) {
			if (!imagepng( $imageresized, $newFilename ) )
				$error = __( "Thumbnail path invalid", 'user-photo');
		}
	}
	if(!empty($error))
		return false;
	return true;
}

/// Avatar/Photo display

if (USE_GRAVATARS_IF_NO_PHOTO):
/// This is the original function. We'll use the original if no photo was found.
function original_bb_get_avatar( $id_or_email, $size = 80, $default = '' ) {
	if ( !bb_get_option('avatars_show') )
		return false;

	if ( !is_numeric($size) )
		$size = 80;

	if ( $email = bb_get_user_email($id_or_email) ) {
		$class = 'photo ';
	} else {
		$class = '';
		$email = $id_or_email;
	}

	if ( !$email )
		$email = '';

	if ( empty($default) )
		$default = bb_get_option('avatars_default');

	switch ($default) {
		case 'logo':
			$default = '';
			break;
		case 'monsterid':
		case 'wavatar':
		case 'identicon':
			break;
		case 'default':
		default:
			$default = 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=' . $size;
			// ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
			break;
			break;
	}

	$src = 'http://www.gravatar.com/avatar/';
	$class .= 'avatar avatar-' . $size;

	if ( !empty($email) ) {
		$src .= md5( strtolower( $email ) );
	} else {
		$src .= 'd41d8cd98f00b204e9800998ecf8427e';
		// d41d8cd98f00b204e9800998ecf8427e == md5('')
		$class .= ' avatar-noemail';
	}

	$src .= '?s=' . $size;
	$src .= '&amp;d=' . urlencode( $default );

	$rating = bb_get_option('avatars_rating');
	if ( !empty( $rating ) )
		$src .= '&amp;r=' . $rating;

	$avatar = '<img alt="" src="' . $src . '" class="' . $class . '" style="height:' . $size . 'px; width:' . $size . 'px;" />';

	return apply_filters('bb_get_avatar', $avatar, $id_or_email, $size, $default);
}
endif;

if (!function_exists('bb_get_avatar')):
function bb_get_avatar($id) {
	if (!bb_get_option('avatars_show'))
		return false;
		
	if ($avatar = bb_get_usermeta($id, 'userphoto_thumb_file'))
		return '<img class="avatar" src="' . USERPHOTO_URL . $avatar . '" alt="" />';
	else if (USE_GRAVATARS_IF_NO_PHOTO)
		return original_bb_get_avatar($id, bb_get_option('userphoto_thumb_dimension'));
		
	return false;
}
endif;

function bb_get_photo($id) {
		
	if ($avatar = bb_get_usermeta($id, 'userphoto_image_file'))
		return '<img class="avatar" src="' . USERPHOTO_URL . $avatar . '" alt="" />';
	else if (USE_GRAVATARS_IF_NO_PHOTO)
		return original_bb_get_avatar($id, bb_get_option('userphoto_maximum_dimension'));
		
	return false;
}


?>