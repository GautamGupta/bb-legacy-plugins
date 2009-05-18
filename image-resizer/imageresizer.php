<?php
/*
Plugin Name: Image Resizer
Description: Sets a maximum width for an image
Author: Rhys Wynne
Author URI: http://www.gospelrhys.co.uk/
Plugin URI: 
Version: 0.1
*/

add_action('bb_admin_menu_generator', 'image_resizer_add_admin_page');
add_action('bb_admin-header.php', 'image_resizer_admin_page_process');

add_filter('post_text', 'image_resizer_text');

function image_resizer_add_admin_page() {
	bb_admin_add_submenu(__('Image Resizer'), 'use_keys', 'image_resizer_admin_page');
}

function image_resizer_admin_page() {
	if (bb_get_option('image_resizer_enable')) {
		$enable_checked = ' checked="checked"';
	}
	if (bb_get_option('image_resizer_automatic_registration')) {
		$disable_automatic_registration_checked = ' checked="checked"';
	}
	if (bb_get_option('image_resizer_disable_registration')) {
		$disable_registration_checked = ' checked="checked"';
	}
?>
	<h2>Image Resizer</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>Enable Image Resizer posts here:</p>
	<p><input type="checkbox" name="image_resizer_enable" value="1" tabindex="10"<?php echo $enable_checked; ?> />
      Enable Image Resizer authentication<br />
	</p>

	<p>Maximum Image Width: <input type="text" name="max_image_width" value="<?php echo bb_get_option('max_image_width'); ?>">    
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
		<input type="hidden" name="action" value="image_resizer_update" />
	</p>
	</form>
<?php


}

function image_resizer_admin_page_process() {
	if (isset ($_POST['submit'])) {
		if ('image_resizer_update' == $_POST['action']) {
			// Enable Image Resizer
			if ($_POST['image_resizer_enable']) {
				bb_update_option('image_resizer_enable', $_POST['image_resizer_enable']);
			} else {
				bb_delete_option('image_resizer_enable');
			}

			// Width
			if ($_POST['max_image_width']) {
				bb_update_option('max_image_width', $_POST['max_image_width']);
			} else {
				bb_delete_option('max_image_width');
			}
			// Alternative Image
			if ($_POST['alternative_image']) {
				bb_update_option('alternative_image', $_POST['alternative_image']);
			} else {
				bb_delete_option('alternative_image');
			}
		}
	}
}

function get_all_strings_between($string, $start, $end){
preg_match_all( "/$start(.*)$end/U", $string, $match );
return $match[1];
}


function image_resizer_text($post) {

	 if (!bb_get_option('image_resizer_enable')) {return $post;}
	
	$maxwidth = bb_get_option('max_image_width');
	$imagearray = get_all_strings_between($post, '<img src="', '">');
	if ($imagearray[0]=='') return $post;
	
	for ($i = 0; $i < count($imagearray); $i++)
	{
		if($imagearray[$i] != '')
		{
		list($width, $height, $type, $attr) = getimagesize($imagearray[$i]);
		if($width > $maxwidth)
		{
			$post = str_replace('<img src="' .$imagearray[$i] , '<img style="width: ' . $maxwidth . 'px;" src="' . $imagearray[$i] , $post);
		}
		}
	}
	return $post; 

}

?>
