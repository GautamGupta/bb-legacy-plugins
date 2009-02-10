<?php
/*
Plugin Name: C*nsor
Description: Filter forum content for bad words, replaces them with asterisks. Created in conjunction with b5media. Based on censor by Michael Nolan.
Author: Terry Smith
Author URI: http://www.icedteapowered.com/
Version: 0.1
*/

/* SETUP */

add_action('bb_activate_plugin_' . bb_plugin_basename(__FILE__), 'censor_activate');

add_action('bb_admin_menu_generator', 'censor_add_admin_page');
add_action('bb_admin-header.php', 'censor_admin_page_process');
add_action('bb_tag_added', 'censor_check_tag', 5, 3);

add_filter('post_text', 'censor_post_text');
add_filter('topic_title', 'censor_post_text');
add_filter('get_forum_name', 'censor_post_text');
add_filter('bb_title', 'censor_post_text');

/* FUNCTIONS */

function censor_add_admin_page() {
	bb_admin_add_submenu(__('Censor Posts'), 'use_keys', 'censor_admin_page');
}

function censor_admin_page() {
	if (bb_get_option('censor_enable')) {
		$enable_checked = ' checked="checked"';
	}
?>
	<h2>Censor Posts</h2>
	<form method="post">
	<p>Enable censoring here:</p>
	<p><input type="checkbox" name="censor_enable" value="1" tabindex="10"<?php echo $enable_checked; ?> /> Enable censor<br /></p>

	<p>Words to censor:</p>
	<p><textarea name="censor_words" rows="5" cols="70"><?php echo implode("\n", bb_get_option('censor_words')); ?></textarea></p>

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
				$words = explode("\n", $_POST['censor_words']);
				for($i = 0; $i < sizeof($words); $i++) $words[$i] = trim($words[$i]);
				bb_update_option('censor_words', $words);
			} else {
				bb_delete_option('censor_words');
			}
		}
	}
}

function censor_activate()
{
	$file = dirname(__FILE__).'/words.txt';
	$words = file($file, FILE_IGNORE_NEW_LINES);
	bb_update_option('censor_words', $words);
}

function censor_post_text($post) {

	if (!bb_get_option('censor_enable')) {return $post;}
	$words = bb_get_option('censor_words');
	if ($words[0]=='') return $post;
	foreach ($words as $key => $word) {
		$words[$key] = '/\b('.preg_quote($word).')\b/i';
	}
	$replace = array();
	for($i = 0; $i < sizeof($words); $i++) {
		array_push($replace, "****");
	}
	$clean_post = preg_replace($words, $replace, $post);
	return $clean_post;

}

function censor_check_tag($ttid = 0, $uid = 0, $tid = 0) {
        if (!bb_get_option('censor_enable')) {return $post;}

        $words = bb_get_option('censor_words');
        if ($words[0]=='') return $post;

	$tag = bb_get_tag($ttid);
        if(in_array($tag->name, $words))
		bb_destroy_tag($ttid);

        return $ttid;
}

?>
