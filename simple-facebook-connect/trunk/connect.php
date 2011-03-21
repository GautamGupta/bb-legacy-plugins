<?php
/*
Plugin Name: Simple Facebook Connect
Plugin URI: http://bbpress.org/plugins/topic/simple-facebook-connect/
Description: Adds a one-click login/registeration integration with Facebook to bbPress.
Author: moogie 
Version: 1.0 
*/

require "facebook.php";

$_fb_need_sdk = 0;
$_fb_need_loginscript = 0;

function try_fb_connect() 
{
	/* We may need to un-sanitize the cookie; bb_global_sanitize creates an unreadable
	   cookie-string on some environments, by adding extra slashes to it */ 
	$cookie_name = "fbs_" . bb_get_option( 'fb_app_id' );
	if (isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name][0] == "\\") {
		$_COOKIE[$cookie_name] = stripslashes($_COOKIE[$cookie_name]);
	}

	$facebook = new Facebook(array(
		'appId'  => bb_get_option( 'fb_app_id' ),
		'secret' => bb_get_option( 'fb_secret' ),
		'cookie' => true,
	));

	$session = $facebook->getSession();

	$me = null;
	if ($session) {
		try {
			$uid = $facebook->getUser();
			$me = $facebook->api('/me');
		} catch (FacebookApiException $e) {
			error_log($e);
		}
	}	
	return $me;
}

function get_fb_login_button($text = "Login with Facebook", $always_display = false)
{
	if ( $always_display || !bb_is_user_logged_in() ) {
		global $_fb_need_sdk, $_fb_need_loginscript;
		$_fb_need_sdk = 1;
		$_fb_need_loginscript = 1;

		$perms = '';
		if ( bb_get_option ( 'fb_request_email' ) )
			$perms = ' perms="email"';

		return "<fb:login-button$perms onlogin=\"fb_login_action();\">$text</fb:login-button>";
	}
}

function fb_login_button($text = "Login with Facebook", $always_display = false)
{
	echo get_fb_login_button($text, $always_display);
}

function fb_bb_get_avatar($avatar, $id_or_email, $size, $default, $alt)
{
	if (intval($id_or_email) != $id_or_email)
		return $avatar;

	$fbid = fb_get_facebookid_by_userid($id_or_email);
	if (!$fbid)
		return $avatar;

	//$pictype = ($size > 50) ? "?type=large" : "";
	$pictype = "";

	if ( false === $alt)
		$safe_alt = '';
	else
		$safe_alt = esc_attr( $alt );

	$class = 'photo avatar avatar-' . $size;
	$src = "https://graph.facebook.com/$fbid/picture$pictype";
	
	$avatar = '<img alt="' . $safe_alt . '" src="' . $src . '" class="' . $class . '" style="height:' . $size . 'px; width:' . $size . 'px;" />';	

	return $avatar;
}

function fb_bb_current_user_can($retvalue, $capability, $arg)
{
	if ($capability != 'change_user_password' && $capability != 'edit_user')
		return $retvalue;

	if (bb_current_user_can('edit_users'))
		return $retvalue;

	$user_id = bb_get_current_user_info( 'id' );
	if (!$user_id)
		return $retvalue;

	$fbid = fb_get_facebookid_by_userid($user_id);
	if (!$fbid)
		return $retvalue;

	switch ($capability) {
		case 'change_user_password':
			return false;

		case 'edit_user':
			return ( bb_get_option( 'fb_allow_useredit' ) ) ? $retvalue : false;

		default:
			return $retvalue;
	}
}

function bb_fb_connect() {
	global $wp_users_object;

	$me = try_fb_connect();

	if (!$me) {
		bb_die("Facebook Connect failed");
		exit;
	}

	$fb_id = intval($me['id']);
	if (!$fb_id) {
		bb_die("Facebook Connect failed, no user id found.");
		exit;
	}

	// Check if the user has already connected before
	$user_id = fb_get_userid_by_facebookid($fb_id);

	if (!$user_id) {	
		// User did not exist yet, lets create the local account

		// First order of business is to find a unused usable account name
		for ($i = 1; ; $i++) {
			$user_login = strtolower(sanitize_user(fb_get_user_displayname($me), true)); 
			$user_login = str_replace(' ', '_', $user_login);

			if (strlen($user_login) > (50 - strlen($i)))
				$user_login = substr($user_login, 0, (50 - strlen($i)));

			if ($i > 1)
				$user_login .= $i;

			// A very rare potential race condition exists here, if two users with the same name
			// happen to register at the same time. On of them would fail, and have to retry.
			if (bb_get_user($user_login, array ('by' => 'login')) === false)
				break;
		}

		$user_nicename = $user_login;
		$user_email = $user_login."@none.local";
		$user_url = trim($me['link']);
		$user_url = $user_url ? bb_fix_link($user_url) : '';
		$user_status = 0;
		$user_pass = bb_generate_password();

		// User may have given permission to use his/her real email. Lets use it if so.
		if (isset($me['email']) && $me['email'] != '' && is_email($me['email'])) {
			$user_email = trim($me['email']);
			if (bb_get_user($user_email, array ('by' => 'email')) !== false) {
				// Uh oh. A user with this email already exists. This does not work out for us.
				bb_die("Error: an user account with the email address '$user_email' already exists.");
			}	
		}

		$user = $wp_users_object->new_user( compact( 'user_login', 'user_email', 'user_url', 'user_nicename', 'user_status', 'user_pass' ) );
		if ( !$user || is_wp_error($user) ) {
			bb_die("Creating new user failed");
			exit;
		}
		$user_id = $user['ID'];

		bb_update_usermeta($user_id, $bbdb->prefix . 'capabilities', array('member' => true) );
		bb_update_usermeta($user_id, 'facebook_id', $fb_id);
		
		bb_update_user($user_id, $user_email, $user_url, fb_get_user_displayname($me));
		bb_update_usermeta($user_id, 'first_name', trim($me['first_name']));
		bb_update_usermeta($user_id, 'last_name', trim($me['last_name']));

		do_action('bb_new_user', $user_id, $user_pass);
		do_action('register_user', $user_id);

	} else {
		if (! bb_get_option( 'fb_allow_useredit' ) ) {
			// enforce first name, last name and display name if the users are not allowed to change them
			bb_update_user($user_id, bb_get_user_email($user_id), get_user_link($user_id), fb_get_user_displayname($me));

			bb_update_usermeta($user_id, 'first_name', trim($me['first_name']));
			bb_update_usermeta($user_id, 'last_name', trim($me['last_name']));
		}
	}

        bb_set_auth_cookie( $user_id, true );
        do_action('bb_user_login', $user_id);

	$redirect_url = $_REQUEST['fb_bb_connect'];
	if (strpos($redirect_url, bb_get_option('uri')) !== 0)
		$redirect_url = bb_get_option('uri');

	bb_safe_redirect($redirect_url);
	exit;
}

function fb_get_user_displayname($me)
{
	switch (bb_get_option('fb_displayname_from')) {
	case 2:
		$name = $me['last_name'];
		break;
	case 1:
		$name = $me['first_name'];
		break;
	case 0:
	default:
		$name = $me['name'];
		break;
	}

	$name = trim($name);
	if (!$name) {
		$name = trim($me['name']);
		if (!$name) 
			$name = "Unknown";
	}
		
	return $name;
}

function fb_get_userid_by_facebookid($fb_id)
{
	global $bbdb;
	$fb_id = intval($fb_id);
	$bb_userid = $bbdb->get_var("SELECT user_id FROM ".$bbdb->prefix."usermeta WHERE meta_key = 'facebook_id' AND meta_value = '".$fb_id."'");
	return ($bb_userid > 0) ? $bb_userid : 0;
}
function fb_get_facebookid_by_userid($u_id)
{
	$bb_fbid = bb_get_usermeta($u_id, 'facebook_id');
	return ($bb_fbid > 0) ? $bb_fbid : 0;
}

function fb_foot_script()
{
	global $_fb_need_sdk, $_fb_need_loginscript;
?>
	<!-- begin Simple Facebook Connect footer -->
<?php if (bb_get_option( 'fb_sdkinit' ) != 2) : ?>
<?php  if (bb_get_option( 'fb_sdkinit' ) == 1 || $_fb_need_sdk) : ?>
	<div id="fb-root"></div>
	<script src="http://connect.facebook.net/en_US/all.js"></script>
	<script>
	FB.init({ 
	  appId   : '<?php echo bb_get_option( 'fb_app_id'); ?>',
	  cookie  : true, 
	  status  : true, 
	  xfbml   : true 
	});
	</script>
<?php  endif; ?>
<?php endif; ?>
<?php if ($_fb_need_loginscript) : ?>
	<script>
	var addUrlParam = function(search, key, val){
	  var newParam = key + '=' + val,
	  params = '?' + newParam;

	  if (search) {
	    params = search.replace(new RegExp('[\?&]' + key + '[^&]*'), '$1' + newParam);
	    if (params === search) {
	      params += '&' + newParam;
	    }
	  }
	  return params;
	};

	function fb_login_action(){
	  FB.getLoginStatus(function(response) { 
	    if (response.session) {
	      // User clicked Connect and he is connected
	      document.location = document.location.pathname + addUrlParam(document.location.search, 'fb_bb_connect', escape(document.location));
	    } else { 
	      // user clicked Cancel 
	    }
	  });
	}; 
	</script>
<?php endif; ?>
	<!-- end Simple Facebook Connect footer -->
<?php
}

function fb_configuration_page()
{
?>
<h2><?php _e( 'Simple Facebook Connect Settings' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'fb_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<p>A Facebook Application ID and Secret Key are needed. These can be obtained from <a href="http://developers.facebook.com/setup/">Facebook developer pages</a>.</p>
	<p>Remember to check that OAuth 2.0 support is enabled. This setting is located under Advanced-tab of your Facebook application page.</p>
	<fieldset class="submit">
<?php
	bb_option_form_element( 'fb_app_id', array(
		'title' => __( 'Facebook Application ID' ),
		'attributes' => array( 'maxlength' => 20),
		'after' => '[Numeric] Example: 123456789012345' 
	) );

	bb_option_form_element( 'fb_secret', array(
		'title' => __( 'Facebook Application Secret' ),
		'attributes' => array( 'maxlength' => 40 ),
		'after' => "[Alphanumeric] Example: abcdef123456abcdef123456abcdef123456"
	) );

        bb_option_form_element( 'fb_displayname_from', array(
                'title' => __( 'Set as Display Name' ),
                'type' => 'select',
                'options' => array(
			0 => __( 'Full Name' ),
			1 => __( 'First Name' ),
			2 => __( 'Last Name' )
                ),
		'after' => "The users Display Name will be set to this value as provided by Facebook"
        ) );

        bb_option_form_element( 'fb_allow_useredit', array(
                'title' => __( 'Allow User Edit' ),
                'type' => 'checkbox',
                'options' => array(
                        1 => __( 'Allow users to edit their own profile information, such as first name, last name and display name' )
                )
        ) );

        bb_option_form_element( 'fb_request_email', array(
                'title' => __( 'Request Real Email' ),
                'type' => 'checkbox',
                'options' => array(
                        1 => __( 'Request users real email address from Facebook (user must accept this). A dummy email is set to new users if this is disabled.' )
                )
        ) );

        bb_option_form_element( 'fb_hide_post_login', array(
                'title' => __( 'Hide login in post form' ), 
                'type' => 'checkbox', 
                'options' => array(
                        1 => __( 'Hide the "You must login to reply" in post-form for non-logged in users. This links to the traditional login page otherwise, which Facebook Connected users cannot use.' ) 
                )  
        ) );

        bb_option_form_element( 'fb_sdkinit', array(
                'title' => __( 'Facebook Javascript SDK initialization' ), 
                'type' => 'select', 
                'options' => array(
			0 => __( 'Automatic' ),
                	1 => __( 'Always' ),
			2 => __( 'Never' )
		),
		'after' => "Select how Facebook Javascript SDK is initialized. This can be used to enable compatibility with conflicting/overlapping themes and/or plugins."
        ) ); 

?>		<?php bb_nonce_field( 'options-fbconnect-update' ); ?>
		<input type="hidden" name="action" value="update-fb-settings" />
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
	</fieldset>
</form>
<?php
}
function fb_configuration_page_add()
{
	bb_admin_add_submenu( __( 'Facebook Connect' ), 'moderate', 'fb_configuration_page', 'options-general.php' );
}
add_action( 'bb_admin_menu_generator', 'fb_configuration_page_add' );

function fb_configuration_page_process()
{
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-fb-settings') {
		bb_check_admin_referer( 'options-fbconnect-update' );

		$fb_app_id = trim( $_POST['fb_app_id'] );
		$fb_secret = strtolower ( trim( $_POST['fb_secret'] ) );

		if (!preg_match('|^[0-9]+$|', $fb_app_id)) {
			bb_admin_notice( __('Invalid Application ID. Configuration not updated!') );
			return;
		}
		if (!preg_match('|^[0-9a-f]+$|', $fb_secret)) {
			bb_admin_notice( __('Invalid Application Secret. Configuration not updated!') );
			return;
		}

		bb_update_option('fb_app_id', $fb_app_id);
		bb_update_option('fb_secret', $fb_secret);

		if (!isset($_POST['fb_displayname_from']) || $_POST['fb_displayname_from'] < 0 || $_POST['fb_displayname_from'] > 2) {
			$_POST['fb_displayname_from'] = 0;
		}
		bb_update_option('fb_displayname_from', intval($_POST['fb_displayname_from']));

		if (!isset($_POST['fb_sdkinit']) || $_POST['fb_sdkinit'] < 0 || $_POST['fb_sdkinit'] > 2) {
			$_POST['fb_sdkinit'] = 0;
		}
		bb_update_option('fb_sdkinit', intval($_POST['fb_sdkinit']));

		if (!isset($_POST['fb_allow_useredit']) || true !== (bool) $_POST['fb_allow_useredit']) {
			bb_delete_option('fb_allow_useredit');
		} else {
			bb_update_option('fb_allow_useredit', 1);
		} 

		if (!isset($_POST['fb_request_email']) || true !== (bool) $_POST['fb_request_email']) {
			bb_delete_option('fb_request_email');
		} else {
			bb_update_option('fb_request_email', 1);
		}

		if (!isset($_POST['fb_hide_post_login']) || true !== (bool) $_POST['fb_hide_post_login']) {
			bb_delete_option('fb_hide_post_login');
		} else {
			bb_update_option('fb_hide_post_login', 1);
		}

		bb_admin_notice( __('Configuration saved.') );
	}
}

add_action('fb_configuration_page_pre_head', 'fb_configuration_page_process');

// Exit if configuration has not been done
if ( !bb_get_option( 'fb_app_id' ) || !bb_get_option( 'fb_secret' )) {
	return;
}

add_filter('bb_get_avatar', 'fb_bb_get_avatar', 10, 5);
add_filter('bb_current_user_can', 'fb_bb_current_user_can', 10, 3);

add_action('bb_foot', 'fb_foot_script');

if ( bb_get_option( 'fb_hide_post_login' ) ) {
	add_action('pre_post_form', 'fb_pre_hide_post_login');
	add_action('post_post_fomr', 'fb_post_hide_post_login');
}

function fb_pre_hide_post_login() {
	if ( !bb_is_user_logged_in() ) {
		echo "<div class=\"hide_login\" style=\"display:none;\">\n";
	}
}
function fb_post_hide_post_login() {
	if ( !bb_is_user_logged_in() ) {
		echo "</div>\n";
	}
}

if ( isset( $_REQUEST['fb_bb_connect'] ) ) {
	add_action('bb_send_headers', 'bb_fb_connect');	
} 
?>
