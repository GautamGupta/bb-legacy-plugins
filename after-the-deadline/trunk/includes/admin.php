<?php

/**
 * @package After the Deadline
 * @subpackage Admin Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * Check for Updates
 *
 * @uses WP_Http
 * @return string|bool Returns version if update is available, else false
 */
function atd_update_check() {
	$latest_ver = trim( wp_remote_retrieve_body( wp_remote_request( $url, array( 'user-agent' => 'AtD/bbPress v' . ATD_VER ) ) ) );
	if( $latest_ver && version_compare( $latest_ver, ATD_VER, '>' ) )
		return $latest_ver;
	
	return false;
}

/**
 * Makes a settings page for the plugin
 * 
 * @uses bb_option_form_element() to generate the page
 */
function atd_options() {
	global $atd_plugopts, $atd_supported_langs;
	if ( $_POST['atd_opts_submit'] == 1 ) { /* Settings have been received, now save them! */
		
		bb_check_admin_referer( 'atd-save-chk' ); /* Security Check */
		
		/* Sanity Checks */
		$atd_plugopts['lang']		= ( in_array( $_POST['lang'], array_keys( $atd_supported_langs ) ) ) ? $_POST['lang'] : 'en';
		$atd_plugopts['enableuser']	= array();
		foreach ( (array) $_POST['enableuser'] as $option ) {
			if ( in_array( $option, array( 'autoproofread', 'ignorealways', 'ignoretypes' ) ) )
				$atd_plugopts['enableuser'][] = $option;
		}
		
		/* Save the options and notify user */
		bb_update_option( ATD_OPTIONS, $atd_plugopts );
		bb_admin_notice( __( 'The options have been successfully saved!', 'after-the-deadline' ) );
	}
	
	/* Check for updates and if available, then notify */
	if( $ver = atd_update_check() )
		bb_admin_notice( sprintf( __( 'New version (v. %1$s) of After the Deadline is available! Please download the latest version from <a href="%2$s">here</a>.', 'after-the-deadline' ), $ver, 'http://bbpress.org/plugins/topic/after-the-deadline/' ), 'error' );
	
	/* Options in an array to be printed */
	$atd_options = array(
		'lang' => array(
			'title'		=> __( 'Language', 'after-the-deadline' ),
			'value' 	=> $atd_plugopts['lang'] ? $atd_plugopts['lang'] : 'en',
			'type'		=> 'select',
			'options'	=> $atd_supported_langs,
			'note'		=> sprintf( __( 'Proofreading should be done for which language? The plugin currently supports the following languages - %s.', 'after-the-deadline' ), implode( ', ', $atd_supported_langs ) )
		),
		'enableuser[]' => array(
			'title'		=> __( 'Enable the user to select the option for:', 'after-the-deadline' ),
			'type'		=> 'checkbox',
			'note'		=> __( 'These options will be shown on the user\'s profile page. All of these options are disabled by default.', 'after-the-deadline' ),
			'options'	=> array(
				'autoproofread' => array(
					'label' => __( 'Autoproofreading the content if it is not proofread once before posting', 'after-the-deadline' ),
					'value' => in_array( 'autoproofread', (array) $atd_plugopts['enableuser'] ) ? 'autoproofread' : ''
				),
				'ignorealways' => array(
					'label' => __( 'Ignoring a term forever (ignored terms can be removed from the profile page)', 'after-the-deadline' ),
					'value' => in_array( 'ignorealways', (array) $atd_plugopts['enableuser'] ) ? 'ignorealways' : ''
				),
				'ignoretypes' => array(
					'label' => __( 'Setting ignore types', 'after-the-deadline' ),
					'value' => in_array( 'ignoretypes', (array) $atd_plugopts['enableuser'] ) ? 'ignoretypes' : ''
				)
			)
		)
	);
	?>
	
	<h2><?php _e( 'After the Deadline Options', 'after-the-deadline' ); ?></h2>
	<?php do_action( 'bb_admin_notices' ); ?>
	<form method="post" class="settings options">
		<fieldset>
			<?php
			foreach ( $atd_options as $option => $args )
				bb_option_form_element( $option, $args );
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
