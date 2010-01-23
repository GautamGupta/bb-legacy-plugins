<?php

/*
 * Public Section PHP File for
 * After the Deadline Plugin
 * (for bbPress) by www.gaut.am
 */

/* Enqueues & Prints Script and Style (JS & CSS)
 * Also echoes some values needed by the Javascript (now uses wp_localize_script)
 * Compatibility with bbPress 0.9 & lower introduced in v1.2
 * Also checks if the necessary functions are there
 * CSS & JS have been compressed (v1.4) so that the user need not download a larger file
 * Compatibility with anonymous posting feature (bbPress 1.1) introduced in v1.4 (bb1.1 is not released till now, but I added it for future)
 */
function atd_css_js(){
	global $atd_plugopts;
	if( bb_is_user_logged_in() || ( function_exists( 'bb_is_login_required' ) && !bb_is_login_required() ) ){
		$i18n = array(
				'rpc' => ATD_PLUGPATH.'includes/check.php',
				'api_key' => $atd_plugopts['key'],
				'button_proofread' => __('Proofread', 'after-the-deadline'),
				'button_edit_text' => __('Edit Text', 'after-the-deadline'),
				'button_ok' => __('OK', 'after-the-deadline'),
				'button_cancel' => __('Cancel', 'after-the-deadline'),
				'menu_title_spelling' => __('Spelling', 'after-the-deadline'),
				'menu_title_repeated_word' => __('Repeated Word', 'after-the-deadline'),
				'menu_title_no_suggestions' => __('No suggestions', 'after-the-deadline'),
				'menu_option_explain' => __('Explain...', 'after-the-deadline'),
				'menu_option_ignore_once' => __('Ignore suggestion', 'after-the-deadline'),
				'menu_option_ignore_all' => __('Ignore all', 'after-the-deadline'),
				'menu_option_ignore_always' => __('Ignore always', 'after-the-deadline'),
				'menu_option_edit_selection' => __('Edit Selection...', 'after-the-deadline'),
				'message_no_errors_found' => __('No writing errors were found.', 'after-the-deadline'),
				'message_error_no_text' => __('Please enter some text in the post textbox to be checked!', 'after-the-deadline'),
				'message_error' => __('Error!', 'after-the-deadline'),
				'message_no_errors_found' => __('No writing errors were found!', 'after-the-deadline'),
				'message_no_errors' => __('No errors!', 'after-the-deadline'),
				'message_server_error_short' => __('There was a problem communicating with the After the Deadline service.', 'after-the-deadline'),
				'dialog_replace_selection' => __('Replace selection with', 'after-the-deadline'),
				'dialog_replace' => __('Replace', 'after-the-deadline'),
			);
		if( function_exists( 'wp_enqueue_style' ) && function_exists( 'wp_enqueue_script' ) && function_exists( 'wp_localize_script' ) ){ //bb 1.0+
			//wp_enqueue_style('after-the-deadline', ATD_PLUGPATH.'css/atd-uncompressed.css', false, ATD_VER, 'all');
			//wp_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd-uncompressed.js", array('jquery'), ATD_VER, true);
			wp_enqueue_style('after-the-deadline', ATD_PLUGPATH.'css/atd.css', false, ATD_VER, 'all');
			wp_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd.js", array('jquery'), ATD_VER);
			wp_localize_script('after-the-deadline', 'AtD', $i18n);
		}elseif( function_exists( 'bb_register_script' ) && function_exists('bb_enqueue_script' ) && function_exists('bb_localize_script' ) && function_exists( 'bb_deregister_script' ) ){ //bb below 1.0
			bb_deregister_script('jquery'); //old jQuery cannot work for AtD, atleast 1.2.6
			bb_register_script('jquery', ATD_PLUGPATH.'scripts/jquery.js', false, '1.3.2'); //so load 1.3.2
			bb_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd.js", array('jquery'), ATD_VER);
			bb_localize_script('after-the-deadline', 'AtD', $i18n);
			echo "<link rel='stylesheet' id='after-the-deadline-css' href='".ATD_PLUGPATH."css/atd.css' type='text/css' media='all' />";
		}
	}
}

/* Add Actions:
 * (1) Hook 'wp_print_scripts' to 'atd_css_js' - Enqueues Script and Style (JS & CSS), for bbPress 1.0+
 * (2) Hook 'bb_print_scripts' to 'atd_css_js' - Same as (1), for bbPress 0.9 and below
 * Above are only done if the user is logged in or loginless posting is enabled
 */
add_action('wp_print_scripts', 'atd_css_js', 2);
add_action('bb_print_scripts', 'atd_css_js', 2);