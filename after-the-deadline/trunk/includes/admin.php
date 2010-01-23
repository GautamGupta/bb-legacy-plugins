<?php

/*
 * Admin Section PHP File for
 * After the Deadline Plugin
 * (for bbPress) by www.gaut.am
 */

/* Now uses WP_Http since v1.5
 * Includes WP Http Class file if it doesn't exist (for bbPress 0.9 and below)
 */
function atd_http_get($url, $method = 'GET', $data = array()){
	if( !class_exists( 'WP_Http' ) ){
		require_once( 'class.wp-http.php' );
	}
	$request = new WP_Http;
	$result = wp_remote_retrieve_body( $request->request( $url , array( 'method' => $method, 'body' => $data, 'user-agent' => 'AtD/bbPress ' . ATD_VER ) ) );
	return $result;
}

/* Check for updates
 * Returns latest version if there is a newer version
 * Returns false if call was unsuccesfull or newer version isn't available
 */
function atd_update_check(){
	$latest_ver = trim(atd_http_get("http://gaut.am/uploads/plugins/updater.php?pid=5&chk=ver&soft=bb&current=".ATD_VER));
	if($latest_ver && version_compare($latest_ver, ATD_VER, '>')){
		return $latest_ver;
	} else {
		return false;
	}
}

/* Connect to the AtD service and verify the key the user entered */
function atd_verify_key( $key ) {
	$session = trim( atd_http_get( 'http://service.afterthedeadline.com/verify?key=' . urlencode( $key ) ) );
	return $session;
}

/* Makes a settings page for AtD API key */
function atd_options(){
	global $atd_plugopts;
	if ( $_POST['atd_opts_submit'] == 1 ) { /* Settings have been recieved, now save them! */
		bb_check_admin_referer( 'atd-save-chk' ); /* Security Check */
		/* Checks on the key, also saving it */
		$key = trim(strip_tags(stripslashes($_POST['key'])));
		$key_status = trim(atd_verify_key($key));
		if ( $key && (strcmp( $key_status, 'valid' ) == 0 || strcmp( $key_status, 'Got it!' ) == 0)){
			$atd_plugopts['key'] = $key;
			bb_update_option(ATD_OPTIONS, $atd_plugopts);
			bb_admin_notice(__('The API key was successfully saved!', 'after-the-deadline'));
		}elseif(!$key){
			$atd_plugopts['key'] = '';
			bb_update_option(ATD_OPTIONS, $atd_plugopts);
			bb_admin_notice(__('Please enter an API key.', 'after-the-deadline'));
		}else{
			$atd_plugopts['key'] = '';
			bb_update_option(ATD_OPTIONS, $atd_plugopts);
			bb_admin_notice(__('The API key you entered is invalid! Please enter the key again.', 'after-the-deadline'));
		}
	}
	/* Check for Updates */
	$ver = atd_update_check();
	if($ver){ /* If available, then notify */
		bb_admin_notice(sprintf(__('New version%1$s of After the Deadline is available! Please download the latest version from <a href="%2$s">here</a>.', 'after-the-deadline'), ' (v'.$ver.')', 'http://bbpress.org/plugins/topic/after-the-deadline/'));
	}
	/* Options in an array to be printed, only for bb 1.0+ */
	$options = array(
		'key' => array(
			'title' => __('Your AtD API Key', 'after-the-deadline'),
			'class' => array( 'long' ),
			'value' => $atd_plugopts['key'] ? $atd_plugopts['key'] : '',
			'after' => '<div style="clear:both;"></div><strong>'.sprintf(__('You can get a key from the <a href="%s">After the Deadline</a> website.', 'after-the-deadline'), 'http://www.afterthedeadline.com/profile.slp').'</strong><br />'.__('The disadvantage of not entering a key is that AtD only allows one call at a time/key. This means if a lot of people are using the same (default) key, then their & your performance will degrade as more people use it.', 'after-the-deadline')
		)
	);
	?>
	
	<h2><?php _e('After the Deadline Options', 'after-the-deadline'); ?></h2>
	<?php do_action( 'bb_admin_notices' ); ?>
	<form method="post" class="settings options">
		<fieldset>
			<?php
			if(function_exists('bb_option_form_element')){ //bb 1.0+
				foreach ( $options as $option => $args ) {
					bb_option_form_element( $option, $args );
				}
			}else{ //bb 0.9 or less
			?>
			<label for="key"><?php _e('Your AtD API Key', 'after-the-deadline'); ?>:</label>
			<div>
				<input type='text' class='text' name='key' id='key' value='<?php echo $atd_plugopts['key'] ? $atd_plugopts['key'] : ''; ?>' />
				<p>
					<?php printf(__('You can get a key from the <a href="%s">After the Deadline</a> website.', 'after-the-deadline'), 'http://www.afterthedeadline.com/profile.slp'); ?><br />
					<?php _e('The disadvantage of not entering a key is that AtD only allows one call at a time/key. This means if a lot of people are using the same (default) key, then their & your performance will degrade as more people use it.', 'after-the-deadline'); ?>
				</p>
			</div>
			<?php } ?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'atd-save-chk' ); ?>
			<input type="hidden" name="atd_opts_submit" value="1"></input>
			<input class="submit" type="submit" name="submit" value="Save Changes" />
		</fieldset>
		<p><?php printf(__('Happy with the plugin? Why not <a href="%1$s">buy the author a cup of coffee or two</a> or get him something from his <a href="%2$s">wishlist</a>?', 'after-the-deadline'), 'http://gaut.am/donate/AtD/', 'http://gaut.am/wishlist/'); ?></p>
	</form>
<?php
}

/* Adds a menu link to After the Deadline setting's page in the Settings section */
function atd_menu_link() {
	bb_admin_add_submenu( __( 'After the Deadline', 'after-the-deadline' ), 'administrate', 'atd_options', 'options-general.php' );
}

/* Hook 'bb_admin_menu_generator' to 'atd_menu_link' - Adds a menu link to After the Deadline setting's page */
add_action('bb_admin_menu_generator', 'atd_menu_link', 3);