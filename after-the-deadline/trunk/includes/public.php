<?php

/**
 * @package After the Deadline
 * @subpackage Admin Section
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/**
 * Enqueue the Javascript and CSS
 *
 * @uses wp_enqueue_script()
 * @uses wp_enqueue_style()
 * @uses wp_localize_script()
 */
function atd_css_js() {
	if ( bb_is_user_logged_in() || ( function_exists( 'bb_is_login_required' ) && !bb_is_login_required() ) ) {
		global $atd_plugopts, $atd_supported_langs;
		
		$i18n		= array(
				'rpc'				=> ATD_PLUGPATH . 'includes/check.php'				,
				'button_proofread'		=> __( 'Proofread'			, 'after-the-deadline' ),
				'button_edit_text'		=> __( 'Edit Text'			, 'after-the-deadline' ),
				'button_accept_all'		=> __( 'Accept All'			, 'after-the-deadline' ),
				'button_ok'			=> __( 'OK'				, 'after-the-deadline' ),
				'button_cancel'			=> __( 'Cancel'				, 'after-the-deadline' ),
				'menu_title_spelling'		=> __( 'Spelling'			, 'after-the-deadline' ),
				'menu_title_repeated_word'	=> __( 'Repeated Word'			, 'after-the-deadline' ),
				'menu_title_no_suggestions'	=> __( 'No suggestions'			, 'after-the-deadline' ),
				'menu_title_confused_word'	=> __( 'Did you mean...'		, 'after-the-deadline' ),
				'menu_option_explain'		=> __( 'Explain...'			, 'after-the-deadline' ),
				'menu_option_ignore_once'	=> __( 'Ignore suggestion'		, 'after-the-deadline' ),
				'menu_option_ignore_all'	=> __( 'Ignore all'			, 'after-the-deadline' ),
				'menu_option_ignore_always'	=> __( 'Ignore always'			, 'after-the-deadline' ),
				'menu_option_edit_selection'	=> __( 'Edit Selection...'		, 'after-the-deadline' ),
				'message_error'			=> __( 'Error!'				, 'after-the-deadline' ),
				'message_no_errors_found'	=> __( 'No writing errors were found!'	, 'after-the-deadline' ),
				'message_no_errors'		=> __( 'No errors!'			, 'after-the-deadline' ),
				'dialog_replace_selection'	=> __( 'Replace selection with:'	, 'after-the-deadline' ),
				'dialog_replace'		=> __( 'Replace'			, 'after-the-deadline' ),
				'message_error_no_text'		=> __( 'Please enter some text in the post textbox to be checked!'		, 'after-the-deadline' ),
				'message_server_error_short'	=> __( 'There was a problem communicating with the After the Deadline service.'	, 'after-the-deadline' )
			);
		if ( bb_is_topic() || bb_is_topic_edit() ) { /* It is not "your reply" everywhere */
			$i18n['dialog_confirm_post']	= __( 'The proofreader has suggestions for your reply. Are you sure you want to post it?'	, 'after-the-deadline' );
			$i18n['dialog_confirm_post2']	= __( 'Press OK to post your reply, or Cancel to view the suggestions and edit your reply.'	, 'after-the-deadline' );
		} else {
			$i18n['dialog_confirm_post']	= __( 'The proofreader has suggestions for your post. Are you sure you want to submit it?'	, 'after-the-deadline' );
			$i18n['dialog_confirm_post2']	= __( 'Press OK to submit your post, or Cancel to view the suggestions and edit your post.'	, 'after-the-deadline' );
		}
		if( count( $atd_plugopts['enableuser'] ) >= 1 && bb_is_user_logged_in() ) { /* Only for logged in users */
			$user = bb_get_current_user();
			if ( $user && $user->ID != 0 ) {
				$user_options = bb_get_usermeta( $user->ID, ATD_USER_OPTIONS );
				if ( in_array( 'ignoretypes',	(array) $atd_plugopts['enableuser'] ) && !is_null( $user_options['ignoretypes'] ) )
					$i18n['ignoreTypes']	= $user_options['ignoretypes'];
				if ( in_array( 'ignorealways',	(array) $atd_plugopts['enableuser'] ) ) {
					$i18n['ignoreStrings']	= $user_options['ignorealways'];
					$i18n['rpc_ignore']	= esc_url( bb_get_uri() . 'bb-admin/admin-ajax.php?phrase=' );
				}
				if ( in_array( 'autoproofread',	(array) $atd_plugopts['enableuser'] ) ) /* Can be 0 too, so no is_null check, rather we do sanity check below */
					$i18n['autoproofread']	= ( intval( $user_options['autoproofread'] ) == 1 ) ? 1 : 0;
			}
		}
		//wp_enqueue_script(	'after-the-deadline-po', ATD_PLUGPATH . 'scripts/profile.dev.js', array( 'jquery' ), ATD_VER	); //Compressed js is echoed on the profile edit page itself
		//wp_enqueue_script(	'after-the-deadline', ATD_PLUGPATH . 'scripts/atd.dev.js', array( 'jquery' )	, ATD_VER	);
		wp_enqueue_script(	'after-the-deadline', ATD_PLUGPATH . 'scripts/atd.js'	, array( 'jquery' )	, ATD_VER	);
		wp_localize_script(	'after-the-deadline', 'AtD'				, $i18n					);
		wp_enqueue_style(	'after-the-deadline', ATD_PLUGPATH . 'css/atd.css'	, false			, ATD_VER, 'all');
	}
}

if( bb_is_profile() && count( $atd_plugopts['enableuser'] ) >= 1 )
	require_once( 'profile-options.php' );

add_action( 'wp_print_scripts', 'atd_css_js', 2 ); /* Enqueues Script and Style */
