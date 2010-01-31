<?php

/**
 * @package Easy Mentions
 * @subpackage Admin Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/easy-mentions/
 */

/**
 * Check for Updates
 * 
 * @return string|bool Returns version if update is available, else false
 * @uses WP_Http
 */
function em_update_check(){
	$latest_ver = trim( wp_remote_retrieve_body( wp_remote_request( 'http://gaut.am/uploads/plugins/updater.php?pid=7&chk=ver&soft=bb&current=' . EM_VER ) ) );
	if( $latest_ver && version_compare( $latest_ver, EM_VER, '>' ) )
		return strval( $latest_ver );
	
	return false;
}

/**
 * Makes a settings page for the plugin
 * 
 * @uses bb_option_form_element() to generate the page
 */
function em_options(){
	global $em_plugopts;
	
	if ( $_POST['em_opts_submit'] == 1 ) { /* Settings have been recieved, now save them! */
		bb_check_admin_referer( 'em-save-chk' ); /* Security Check */
		/* Checks on options, also saving them */
		$em_plugopts['link-to'] = ( $_POST['link-to'] == 'website' ) ? 'website' : 'profile';
		$em_plugopts['reply-link'] = ( intval( $_POST['reply-link'] ) == 1) ? 1 : 0;
		$em_plugopts['reply-text'] = esc_attr( $_POST['reply-text'] );
		bb_update_option( EM_OPTIONS, $em_plugopts );
		bb_admin_notice( __( 'The options were successfully saved!', 'easy-mentions' ) );
	}
	
	/* Check for Updates */
	$ver = em_update_check();
	if( $ver ) /* If available, then notify */
		bb_admin_notice( sprintf( __( 'New version (%1$s) of Easy Mentions is available! Please download the latest version from <a href="%2$s">here</a>.', 'easy-mentions' ), $ver, 'http://bbpress.org/plugins/topic/easy-mentions/' ) );
	
	/* Options in an array to be printed */
	$options = array(
		'link-to' => array(
			'title' => __( 'Link the user to profile or website?', 'easy-mentions' ),
			'type' => 'radio',
			'value' => $em_plugopts['link-to'] ? $em_plugopts['link-to'] : 'profile',
			'options' => array(
				'profile' => __( 'Profile', 'easy-mentions' ),
				'website' => __( 'Website', 'easy-mentions' ),
			),
			'note' => __( 'If you selected the website option and the user\'s website does not exist, then the user will be linked to his or her profile page.', 'easy-mentions' )
		),
		'reply-link' => array(
			'title' => __( 'Add a reply link below each post?', 'easy-mentions' ),
			'type' => 'checkbox',
			'value' => $em_plugopts['reply-link'] ? $em_plugopts['reply-link'] : '',
			'options' => array(
				'1' => __( 'Yes', 'easy-mentions' ),
			),
			'note' => sprintf( __( 'Before checking this option, please verify that there is a post form below the topic on each page. (<a href="%s">Help</a>)', 'easy-mentions' ), 'http://bbpress.org/plugins/topic/easy-mentions/faq/' )
		),
		'reply-text' => array(
			'title' => __( 'Reply Text', 'easy-mentions' ),
			'class' => array( 'long' ),
			'value' => $em_plugopts['reply-text'] ? stripslashes( $em_plugopts['reply-text'] ) : '<em>Replying to @%%USERNAME%%\'s <a href="%%POSTLINK%%">post</a>:</em>',
			'after' => '<div style="clear:both;"></div>' . sprintf( __( 'This applies when the reply feature is ON. Some HTML is allowed. The following keys can also be used:%1$s - Post\'s author\'s name%2$s - Post\'s link', 'after-the-deadline' ), '<br /><strong>%%USERNAME%%</strong>', '<br /><strong>%%POSTLINK%%</strong>' ) . '<br />'
		)
	);
	?>
	
	<h2><?php _e( 'Easy Mention Options', 'easy-mentions' ); ?></h2>
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
			<?php bb_nonce_field( 'em-save-chk' ); ?>
			<input type="hidden" name="em_opts_submit" value="1"></input>
			<input class="submit" type="submit" name="submit" value="Save Changes" />
		</fieldset>
		<p><?php printf( __( 'Happy with the plugin? Why not <a href="%1$s">buy the author a cup of coffee or two</a> or get him something from his <a href="%2$s">wishlist</a>?', 'easy-mentions' ), 'http://gaut.am/donate/EM/', 'http://gaut.am/wishlist/' ); ?></p>
	</form>
<?php
}

/**
 * Adds a menu link to the setting's page in the Settings section
 *
 * @uses bb_admin_add_submenu()
 */
function em_menu_link() {
	bb_admin_add_submenu( __( 'Easy Mentions', 'easy-mentions' ), 'administrate', 'em_options', 'options-general.php' );
}

add_action('bb_admin_menu_generator', 'em_menu_link', 8, 0); /* Adds a menu link to setting's page */
