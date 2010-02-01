<?php

/**
 * @package After the Deadline
 * @subpackage Admin Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * Navigation Function
 * @uses WP_Http
 */
function atd_http( $url, $method = 'GET', $data = array() ){
	return trim( wp_remote_retrieve_body( wp_remote_request( $url, array( 'method' => $method, 'body' => $data, 'user-agent' => 'AtD/bbPress v' . ATD_VER ) ) ) );
}

/**
 * Check for Updates
 * 
 * @return string|bool Returns version if update is available, else false
 */
function atd_update_check(){
	$latest_ver = atd_http( 'http://gaut.am/uploads/plugins/updater.php?pid=5&chk=ver&soft=bb&current=' . ATD_VER );
	if( $latest_ver && version_compare( $latest_ver, ATD_VER, '>' ) )
		return $latest_ver;
	
	return false;
}

/**
 * Connect to the AtD service and verify the key the user entered
 */
function atd_verify_key( $key ) {
	return atd_http( 'http://service.afterthedeadline.com/verify?key=' . urlencode( $key ) );
}

/**
 * Makes a settings page for the plugin
 * 
 * @uses bb_option_form_element() to generate the page
 */
function atd_options(){
	global $atd_plugopts;
	if ( $_POST['atd_opts_submit'] == 1 ) { /* Settings have been received, now save them! */
		
		bb_check_admin_referer( 'atd-save-chk' ); /* Security Check */
		
		$key = trim( strip_tags( stripslashes( $_POST['key'] ) ) ); /* Parse key */
		$key_status = atd_verify_key( $key ); /* Verify key */
		
		if ( $key && ( strcmp( $key_status, 'valid' ) == 0 || strcmp( $key_status, 'Got it!' ) == 0 ) ){ /* Checks on the key */
			$atd_plugopts['key'] = $key;
			$message = __( 'The API key was successfully saved!', 'after-the-deadline' );
		}else{
			$atd_plugopts['key'] = '';
			$message = __( 'The API key you entered is invalid! Please enter a valid key.', 'after-the-deadline' );
		}
		
		/* Save key and notify user */
		bb_update_option( ATD_OPTIONS, $atd_plugopts );
		bb_admin_notice( __( $message ) );
	}
	
	/* Check for updates and if available, then notify */
	if( $ver = atd_update_check() )
		bb_admin_notice( sprintf( __( 'New version%1$s of After the Deadline is available! Please download the latest version from <a href="%2$s">here</a>.', 'after-the-deadline' ), ' (v'.$ver.')', 'http://bbpress.org/plugins/topic/after-the-deadline/' ) );
	
	/* Options in an array to be printed */
	$options = array(
		'key' => array(
			'title' => __( 'Your AtD API Key', 'after-the-deadline' ),
			'class' => array( 'long' ),
			'value' => $atd_plugopts['key'] ? $atd_plugopts['key'] : '',
			'after' => '<div style="clear:both;"></div><strong>' . sprintf( __( 'You can get a key from the <a href="%s">After the Deadline</a> website.', 'after-the-deadline' ), 'http://www.afterthedeadline.com/profile.slp' ) . '</strong><br />' . __( 'The disadvantage of not entering a key is that AtD only allows one call at a time/key. This means if a lot of people are using the same (default) key, then their & your performance will degrade as more people use it.', 'after-the-deadline' )
		)
	);
	?>
	
	<h2><?php _e( 'After the Deadline Options', 'after-the-deadline' ); ?></h2>
	<?php do_action( 'bb_admin_notices' ); ?>
	<form method="post" class="settings options">
		<fieldset>
			<?php
			foreach ( $options as $option => $args ) {
				bb_option_form_element( $option, $args );
			}
			?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'atd-save-chk' ); ?>
			<input type="hidden" name="atd_opts_submit" value="1"></input>
			<input class="submit" type="submit" name="submit" value="Save Changes" />
		</fieldset>
		<p><?php printf( __( 'Happy with the plugin? Why not <a href="%1$s">buy the author a cup of coffee or two</a> or get him something from his <a href="%2$s">wishlist</a>?', 'after-the-deadline' ), 'http://gaut.am/donate/AtD/', 'http://gaut.am/wishlist/' ); ?></p>
	</form>
<?php
}

/**
 * Adds a menu link to the setting's page in the Settings section
 *
 * @uses bb_admin_add_submenu()
 */
function atd_menu_link() {
	bb_admin_add_submenu( __( 'After the Deadline', 'after-the-deadline' ), 'administrate', 'atd_options', 'options-general.php' );
}

add_action( 'bb_admin_menu_generator', 'atd_menu_link', 3 ); /* Adds a menu link to setting's page */
