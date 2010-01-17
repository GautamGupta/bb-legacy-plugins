<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.4
Author: Gautam Gupta
Author URI: http://gaut.am/

	Original After the Deadline Plugin Copyright 2010 Gautam (email: admin@gaut.am) (website: http://gaut.am)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/*
 * Main PHP File for
 * After the Deadline - Spell Checker Plugin
 * (for bbPress) by www.gaut.am
 */

/* Create Text Domain For Translations */
load_plugin_textdomain( 'after-the-deadline', dirname(__FILE__) . '/languages' );

/*
 * Defines
 */

/* IF statement introduced in v1.3
 * If you have problems (the directory of the plugin could not be matched), then define ATD_PLUGPATH in bb-config.php file to the full URL path to the plugin directory
 * Eg. - http://www.example-domain.tld/forums/my-plugins/after-the-deadline/
 */
if(!defined('ATD_PLUGPATH')){
	/* Define ATD_PLUGPATH if value is not set - Full URL path to the plugin */
	define('ATD_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/');
}
/* Version */
define('ATD_VER', '1.4-dev');
/* AtD Option Name */
define('ATD_OPTIONS','After-the-Deadline');

/* Set the Options if they are not set */
$atd_plugopts = bb_get_option(ATD_OPTIONS);
if(!is_array($atd_plugopts)){
	/* Add defaults to an array */
	$atd_plugopts = array(
		'key' => ''
	);
	/* Update the options */
	bb_update_option(ATD_OPTIONS, $atd_plugopts);
}

/*
 * Functions
 */

/* Inserts (echoes) AtD Spell Check Button (actually, Link) when called
 * Only when a user is logged in
 * NOTE - Now this function is not used, now the button is inserted via jQuery wherever it finds post_content textarea on the forums - Edit in 1.4
 * Compatibility with anonymous posting feature (bbPress 1.1) introduced in v1.4 (bb1.1 is not released till now, but I added it for future)
 */
function atd_button(){
	if(bb_is_user_logged_in() || (function_exists('bb_is_login_required') && !bb_is_login_required())){
		$string = '<span class="atd_container"><img src="'.ATD_PLUGPATH.'images/atdbuttontr.gif"><a href="#postform" id="checkLink">'.__('Check Spelling', 'after-the-deadline').'</a></span>';
		echo $string;
	}
}

/* Enqueues & Prints Script and Style (JS & CSS)
 * Also echoes some values needed by the Javascript (now uses wp_localize_script)
 * Compatibility with bbPress 0.9 & lower introduced in v1.2
 * Also checks if the necessary functions are there
 * CSS & JS have been compressed (v1.4) so that the user need not download a larger file
 * Compatibility with anonymous posting feature (bbPress 1.1) introduced in v1.4 (bb1.1 is not released till now, but I added it for future)
 */
function atd_css_js(){
	global $atd_plugopts;
	if(bb_is_user_logged_in() || (function_exists('bb_is_login_required') && !bb_is_login_required())){
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
				//'i18n' => "{ menu_title_spelling: '".__('Spelling', 'after-the-deadline')."', menu_title_repeated_word: '".__('Repeated Word', 'after-the-deadline')."', menu_title_no_suggestions: '".__('No suggestions', 'after-the-deadline')."', menu_option_explain: '".__('Explain...', 'after-the-deadline')."', menu_option_ignore_once: '".__('Ignore suggestion', 'after-the-deadline')."', menu_option_ignore_all: '".__('Ignore all', 'after-the-deadline')."', menu_option_ignore_always: '".__('Ignore always', 'after-the-deadline')."', menu_option_edit_selection: '".__('Edit Selection...', 'after-the-deadline')."', message_no_errors_found: '".__('No writing errors were found.', 'after-the-deadline')."', message_server_error_short: '".__('There was a problem communicating with the After the Deadline service.', 'after-the-deadline')."', dialog_replace_selection: '".__('Replace selection with:', 'after-the-deadline')."' };",
			);
		if(function_exists('wp_enqueue_style') && function_exists('wp_register_script') && function_exists('wp_print_scripts') && function_exists('wp_print_styles')){ //bb 1.0+
			//wp_enqueue_style('after-the-deadline', ATD_PLUGPATH.'css/atd-uncompressed.css', false, ATD_VER, 'all');
			//wp_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd-uncompressed.js", array('jquery'), ATD_VER, true);
			wp_enqueue_style('after-the-deadline', ATD_PLUGPATH.'css/atd.css', false, ATD_VER, 'all');
			wp_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd.js", array('jquery'), ATD_VER);
			wp_localize_script('after-the-deadline', 'AtD', $i18n);
		}elseif(function_exists('bb_register_script') && function_exists('bb_print_scripts') && function_exists('bb_deregister_script')){ //bb below 1.0
			bb_deregister_script('jquery'); //old jQuery cannot work for AtD, atleast 1.2.6
			bb_register_script('jquery', ATD_PLUGPATH.'scripts/jquery.js', false, '1.3.2'); //so load 1.3.2
			bb_enqueue_script('after-the-deadline', ATD_PLUGPATH."scripts/atd.js", array('jquery'), ATD_VER);
			bb_localize_script('after-the-deadline', 'AtD', $i18n);
			echo "<link rel='stylesheet' id='after-the-deadline-css' href='".ATD_PLUGPATH."css/atd.css' type='text/css' media='all' />";
		}
	}
}

/* this function comes almost verbatim from akismet.php... its allowing me to get rid of the curl dependence */
function atd_http_get($host, $path, $port = 80)
{ $http_request  = "GET $path HTTP/1.0\r\n"; $http_request .= "Host: $host\r\n"; $http_request .= "User-Agent: AtD/bbPress\r\n"; $http_request .= "\r\n";
 $response = ''; if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {    fwrite($fs, $http_request);
    while ( !feof($fs) )    {        $response .= fgets($fs);    }    fclose($fs);    $response = explode("\r\n\r\n", $response, 2); } return $response;
}

/* Check for updates
 * Returns latest version if there is a newer version
 * Returns false if call was unsuccesfull or newer version isn't available
 */
function atd_update_check(){
	$latest_ver = AtD_http_get("gaut.am", "/uploads/plugins/updater.php?pid=5&chk=ver&soft=bb&current=".ATD_VER);
	$latest_ver = trim($latest_ver[1]);
	if($latest_ver && version_compare($latest_ver, ATD_VER, '>')){
		return $latest_ver;
	}
	return false;
}

/* Connect to the AtD service and verify the key the user entered */
function atd_verify_key( $key ) {
	$session = atd_http_get( 'service.afterthedeadline.com', '/verify?key=' . urlencode( $key ));
	return $session[1];
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
		bb_admin_notice(sprintf(__('New version%s of After the Deadline is available! Please download the latest version from <a href="%s">here</a>.', 'after-the-deadline'), ' (v'.$ver.')', 'http://bbpress.org/plugins/topic/after-the-deadline/'));
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
		<p><?php printf(__('Happy with the plugin? Why not <a href="%s">buy the author a cup of coffee or two</a> or get him something from his <a href="%s">wishlist</a>?', 'after-the-deadline'), 'http://gaut.am/donate/AtD/', 'http://gaut.am/wishlist/'); ?></p>
	</form>
<?php
}

/* Adds a menu link to After the Deadline setting's page in the Settings section */
function atd_menu_link() {
	bb_admin_add_submenu( __( 'After the Deadline', 'after-the-deadline' ), 'administrate', 'atd_options', 'options-general.php' );
}

/* Add Actions:
 * Removed - (1) Hook 'post_form_pre_post' to 'atd_button' - Inserts (echoes) AtD Spell Check Button (actually, Link) when called, should be after anything
 * Removed - (2) Hook 'edit_form_pre_post' to 'atd_button' - Same as (1)
 * (3) Hook 'wp_print_scripts' to 'atd_css_js' - Enqueues Script and Style (JS & CSS), for bbPress 1.0+
 * (4) Hook 'bb_print_scripts' to 'atd_css_js' - Same as (3), for bbPress 0.9 and below
 * (5) Hook 'bb_admin_menu_generator' to 'atd_menu_link' - Adds a menu link to After the Deadline setting's page
 * Above are only done if the user is logged in
 */
/*
 * Removed because now the button is inserted right before the textbox via jQuery
add_action('post_form_pre_post', 'atd_button', 9999999);
add_action('edit_form_pre_post', 'atd_button', 9999999);
*/
add_action('wp_print_scripts', 'atd_css_js', 2);
add_action('bb_print_scripts', 'atd_css_js', 2);
add_action('bb_admin_menu_generator', 'atd_menu_link', 3);
