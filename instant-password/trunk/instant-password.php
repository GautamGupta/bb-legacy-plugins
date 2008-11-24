<?php
/*
Plugin Name: Instant Password
Plugin URI:  http://bbpress.org/plugins/topic/instant-password
Description:  Allows users to pick their own password during registration and log in immediately without checking email.
Version: 0.0.3
Author: _ck_
Author URI: http://bbshowcase.org

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/ 

if (bb_get_location()=="register-page") {

add_action( 'extra_profile_info', 'instant_password',9);	
add_action('register_user', 'instant_password_success');

function instant_password() {
echo '  <fieldset><legend>'.__("Select Password").'</legend>
	<table width="100%"><p>'.__("Your password must be at least six characters.").'</p>	
	<tr class="required"><th scope="row" nowrap>	<label for="password"><sup class="required">*</sup> '.__("Password").':</label></th>
	<td><input name="password" id="password" type="password" value="" autocomplete="off" /> <span id="ip_error" style="color:red;font-weight:bold;"></span></td>
	<tr class="required"><th scope="row" nowrap><label for="pass2"><sup class="required">*</sup> '.__("Confirm").':</label></th>
	<td><input name="pass2" id="pass2" type="password" value="" autocomplete="off" /></td></table>
	<p>'.__('Hint: Use upper and lower case characters, numbers and symbols like !"?$%^&amp;( in your password.').'</p>
	</fieldset>
	<script type="text/javascript"> 
	if (window.attachEvent) {window.attachEvent("onload", instant_password_init);} 
	else if (window.addEventListener) {window.addEventListener("load", instant_password_init, false);} 
	else {document.addEventListener("load", instant_password_init, false);}
	function instant_password_init() {
	for (var j=0; j<document.forms.length; j++) {if (document.forms[j].password) {registerform=document.forms[j]; registerform.onsubmit=instant_password;}}
	}
	function instant_password() {
	var bad="";
	if (registerform.password.value.length<6) {bad="'.__("Your password is too short").'";}	
	if (!bad && (registerform.password.value.indexOf(registerform.user_login.value)!=-1 || registerform.user_login.value.indexOf(registerform.password.value)!=-1)) {bad="'.__("Your password cannot contain your username").'";}
	if (!bad && registerform.password.value!=registerform.pass2.value) {bad="'.__("Your password confirmation doesn't match").'";}	
	if (bad) {document.getElementById("ip_error").innerHTML="<br />"+bad; registerform.password.focus(); return false;}
	}
	</script>
';
} 

function instant_password_success($user_id=0) {
if (!$user_id) {return;}
global $bbdb;
$bbdb->query("UPDATE $bbdb->users SET user_status=0 WHERE ID=$user_id LIMIT 1");	// 0.9 has a typo bug in update_user_status function
wp_set_auth_cookie( (int) $user_id, 0 );
do_action('bb_user_login', (int) $user_id );
if (isset($_REQUEST['re'])) {$re=$_REQUEST['re'];} else {$re=bb_get_option('uri');}
bb_safe_redirect($re);			
}

function wp_generate_password( $length = 12, $special_chars = true ) {	// only replaces pluggable on register.php page
if (empty($_POST) || empty($_POST['password']) || empty($_POST['pass2']) || $_POST['password']!=$_POST['pass2']) {$bad=__("Your password confirmation doesn't match");}
else {$password=trim(stripslashes($_POST['password'])); $pass2=trim(stripslashes($_POST['pass2'])); if ($password!=$pass2 || strlen($password)<6) {$bad=__("Your password is too short");}}
if (empty($_POST['user_login']) || strpos($password,$_POST['user_login'])!==false  || strpos($_POST['user_login'],$password)!==false) {$bad=__("Your password cannot contain your username");}
if (!empty($bad)) {instant_password_error($bad);}
return $password;
}

function instant_password_error($error) {
bb_send_headers();
bb_get_header();
echo "<br clear='both' /><h2 id='register' style='margin-left:2em;'>".__("Error")."</h2><p align='center'><font size='+1'>".
	$error.", <br />
	<a onclick='history.go(-1); return false;' href='register.php'>".__("please go back and try again")."</a>.
	</font></p><br />";
bb_get_footer();
exit;
}

if (!defined('BACKPRESS_PATH')) {  

//  bbPress 0.9 unfortunately allows the same email address to be used over and over, this prevents it
add_filter('bb_verify_email','instant_password_no_duplicate_email',9); 
function instant_password_no_duplicate_email($email) {
if ($email) {global $bbdb; if (!$bbdb->get_row($bbdb->prepare("SELECT user_email FROM $bbdb->users WHERE user_email = %s", $email))) {return $email;}
else {instant_password_error(__("That email address is already registered"));}}
}

} else {   // this freakin' nightmare brought to you by bbPress 1.0 and non-pluggable classes, thanks guys!

function bb_new_user( $user_login, $user_email, $user_url, $user_status = 1 ) {
	global $wp_users_object, $bbdb;

	// is_email check + dns
	if ( !$user_email = bb_verify_email( $user_email ) )
		return new WP_Error( 'user_email', __( 'Invalid email address' ), $user_email );

	if ( !$user_login = sanitize_user( $user_login, true ) )
		return new WP_Error( 'user_login', __( 'Invalid username' ), $user_login );
	
	// user_status = 1 means the user has not yet been verified
	$user_status = is_numeric($user_status) ? (int) $user_status : 1;
	if ( defined( 'BB_INSTALLING' ) )
		$user_status = 0;
	
	$user_nicename = $_user_nicename = bb_user_nicename_sanitize( $user_login );
	if ( strlen( $_user_nicename ) < 1 )
		return new WP_Error( 'user_login', __( 'Invalid username' ), $user_login );

	while ( is_numeric($user_nicename) || $existing_user = bb_get_user_by_nicename( $user_nicename ) )
		$user_nicename = bb_slug_increment($_user_nicename, $existing_user->user_nicename, 50);
	
	$user_url = $user_url ? bb_fix_link( $user_url ) : '';
	
	$user_pass = wp_generate_password();	  // workaround - this line and the next are the only two different from the core

	$user = $wp_users_object->new_user( compact( 'user_login', 'user_email', 'user_url', 'user_nicename', 'user_status', 'user_pass' ) );
	if ( is_wp_error($user) ) {
		if ( 'user_nicename' == $user->get_error_code() )
			return new WP_Error( 'user_login', $user->get_error_message() );
		return $user;
	}

	if (BB_INSTALLING) {
		bb_update_usermeta( $user['ID'], $bbdb->prefix . 'capabilities', array('keymaster' => true) );
	} else {		
		bb_update_usermeta( $user['ID'], $bbdb->prefix . 'capabilities', array('member' => true) );
		bb_send_pass( $user['ID'], $user['plain_pass'] );
	}

	do_action('bb_new_user', $user['ID'], $user['plain_pass']);
	return $user['ID'];
}
}  // end 1.0 nightmare

} // end register.php check

?>