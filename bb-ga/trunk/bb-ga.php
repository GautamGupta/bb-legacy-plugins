<?php
/*
Plugin Name: bb-ga
Plugin URI: http://bbpress.org/plugins/topic/bb-ga/
Description: add latest Google Analytics code to your bbpress head。using most code of the plugin:Google Analitycs by jfisbein and change core code to google's offical one.
Author: yexingzhe
Author URI: http://bbpcn.net
Version: 0.2
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
 */
 
bb_load_plugin_textdomain( 'bb-ga', dirname( __FILE__ ). '/translations'  ); /* Create Text Domain For Translations */

add_action('bb_admin_menu_generator', 'bb_ga_configuration_page_add');
add_action('bb_admin-header.php', 'bb_ga_configuration_page_process');

function bb_ga_configuration_page_add() {
	bb_admin_add_submenu(__('Google Analitycs Configuration'), 'use_keys', 'bb_ga_configurtion_page');
}

function bb_ga_configurtion_page(){?>
<h2><?php _e('Google Analitycs Configuration'); ?></h2>

<form class="options" method="post" action="">
	<fieldset>
		<label for="ga_key">
			<?php _e('Google Analitycs User Id:') ?>
		</label>
		<div>
			<input class="text" name="ga_key" id="ga_key" value="<?php bb_form_option('ga_key'); ?>" />
			<?php _e('Like UA-12345-6'); ?>
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

function bb_ga_configuration_page_process() {
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

function createGACode($accountId) {
$code=<<<EOT

<!-- Google Analytics Begin-->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $accountId;?>']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<!-- Google Analytics End -->

EOT;
return $code;
}

function insertGACode() {
	$accountId = bb_get_option('ga_key');
	if ($accountId != '') {
		echo createGACode($accountId);
	}
}
add_action('bb_head', 'insertGACode');
?>