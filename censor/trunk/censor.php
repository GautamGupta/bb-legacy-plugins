<?php
/*
Plugin Name: Censor
Description: Filter posts for bad words
Author: Michael Nolan
Author URI: http://www.michaelnolan.co.uk/
Version: 0.1
*/

add_action('bb_admin_menu_generator', 'censor_add_admin_page');
add_action('bb_admin-header.php', 'censor_admin_page_process');

add_filter('post_text', 'censor_post_text');


function censor_add_admin_page() {
	global $bb_submenu;

	$bb_submenu['site.php'][] = array (
		__('Censor Posts'
	), 'use_keys', 'censor_admin_page');
}

function censor_admin_page() {
	if (bb_get_option('censor_enable')) {
		$enable_checked = ' checked="checked"';
	}
	if (bb_get_option('censor_disable_automatic_registration')) {
		$disable_automatic_registration_checked = ' checked="checked"';
	}
	if (bb_get_option('censor_disable_registration')) {
		$disable_registration_checked = ' checked="checked"';
	}
?>
	<h2>Censor Posts</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>Enable censor posts here:</p>
	<p><input type="checkbox" name="censor_enable" value="1" tabindex="10"<?php echo $enable_checked; ?> /> Enable censor authentication<br /></p>

	<p>Words to censor:</p>
	<p><textarea name="censor_words" rows="5" cols="70"><?php echo bb_get_option('censor_words'); ?></textarea></p>

	<p>Replace with:</p>
	<p><input name="censor_replace" value="<?php echo bb_get_option('censor_replace'); ?>" /></p>

	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="90" />
		<input type="hidden" name="action" value="censor_update" />
	</p>
	</form>
<?php


}

function censor_admin_page_process() {
	if (isset ($_POST['submit'])) {
		if ('censor_update' == $_POST['action']) {
			// Enable censor
			if ($_POST['censor_enable']) {
				bb_update_option('censor_enable', $_POST['censor_enable']);
			} else {
				bb_delete_option('censor_enable');
			}

			// Words
			if ($_POST['censor_words']) {
				bb_update_option('censor_words', $_POST['censor_words']);
			} else {
				bb_delete_option('censor_words');
			}
			// Words
			if ($_POST['censor_replace']) {
				bb_update_option('censor_replace', $_POST['censor_replace']);
			} else {
				bb_delete_option('censor_replace');
			}
		}
	}
}

function censor_post_text($post) {

	if (!bb_get_option('censor_enable')) {return $post;}
	$bad_words = bb_get_option('censor_words');
	$replace = bb_get_option('censor_replace');
	$words = preg_split("/[\s,]+/", $bad_words);
	if ($words[0]=='') return $post;
	foreach ($words as $key => $word) {
		$words[$key] = '/\b('.preg_quote($word).')\b/i';
	}
	$clean_post = preg_replace($words, $replace, $post);
	return $clean_post;

}

?>