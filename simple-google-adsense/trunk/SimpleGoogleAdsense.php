<?php
/*
Plugin Name: Simple Google Adsense
Plugin URI: http://www.strapontins.org/people/rentendre/simple-google-adsense-bbpress/
Description: Add Google Adsense code in your bbPress forum :)
Author: Frédéric Petit
Author URI: http://www.strapontins.org/
Version: 0.1
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
*/

add_action('bb_admin_menu_generator', 'bb_gad_configuration_page_add');
add_action('bb_admin-header.php', 'bb_gad_configuration_page_process');

function bb_gad_configuration_page_add() {
	bb_admin_add_submenu(__('Google Adsense Configuration'), 'use_keys', 'bb_gad_configurtion_page');
}

function bb_gad_configurtion_page(){?>
<h2><?php _e('Google Adsense Configuration'); ?></h2>

<form class="options" method="post" action="">
	<fieldset>
		<label for="ga_key">
			<?php _e('Google Adsense User Id:') ?>
		</label>
		<div>
			<textarea class="text" name="ga_key" id="ga_key" value="<?php bb_form_option('ga_key'); ?>" ></textarea>
			<?php _e('Put your adsense code, without the code of the script pagead2.googlesyndication.com/pagead/show_ads.js'); ?>
		</div>
	</fieldset>
	<fieldset>
		<?php bb_nonce_field( 'ga-configuration' ); ?>
		<input type="hidden" name="action" id="action" value="update-ga-configuration" />
		<div class="spacer">
			<input type="submit" name="submit" id="submit" value="<?php _e('Update Configuration &raquo;') ?>" />
		</div>
	</fieldset>
</form>
<?php
}

function bb_gad_configuration_page_process() {
	if ($_POST['action'] == 'update-ga-configuration') {
		
		bb_check_admin_referer('ga-configuration');
		
		if ($_POST['ga_key']) {
			$value = stripslashes_deep( trim( $_POST['ga_key'] ) );
			if ($value) {
				bb_update_option('ga_key', $value);
			} else {
				bb_delete_option('ga_key' );
			}
		} else {
			bb_delete_option('ga_key');
		}
		
		$goback = add_query_arg('ga-updated', 'true', wp_get_referer());
		bb_safe_redirect($goback);
	}
	
	if ($_GET['ga-updated']) {
		bb_admin_notice( __('Configuration saved.') );
	}
}

// Bail here if no key is set
if (!bb_get_option( 'ga_key' ))
	return;

function createGAdCode($accountId) {
	$code .= '<div class=\'bbpress_adsense\'>' . "\n";

	$code .= ' ' . $accountId . '  ' . "\n";

	$code .= '<script type=\'text/javascript\' src=\'http://pagead2.googlesyndication.com/pagead/show_ads.js\'></script>' . "\n";
	$code .= '</div>' . "\n";




	return $code;
}

function insertGAdCode() {
	$accountId = bb_get_option('ga_key');
	if ($accountId != '') {
		echo createGAdCode($accountId);
	}
}
add_action('bb_foot', 'insertGAdCode');
?>
