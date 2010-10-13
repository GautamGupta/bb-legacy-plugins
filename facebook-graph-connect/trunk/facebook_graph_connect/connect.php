<?php
/*
Plugin Name: Facebook Graph Connect [Beta]
Plugin URI: http://bbpress.org/
Description: Adds facebook connect features to bbpress, so users may Register or Login with facebook connect features.
Author: Saran Chamling
Version: 0.0.2
Author URI: http://www.aboutconsumer.com/
*/

################# MODIFY PLUGIN LANGUAGE BELOW (IF YOU WANT ;) #########################################

$fb_graph_lang['login_button']='<a href="#" onclick="requestExtPerm(); return false;"><img src="http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif" border="0" />
</a>';
$fb_graph_lang['invalid_user']= '%s is an invalid username';
$fb_graph_lang["invalid_login"] = 'User or Password doesn\'t match! Please go back and try again! <br />Back to <a href="#" onclick="history.go(-1);return false;">Connect Page</a>.';
$fb_graph_lang['email_exist'] = 'Email %s is already in use, use link account instead! registration aborted.';
$fb_graph_lang['user_exist'] =  '%s already exist, please choose another one!';
$fb_graph_lang['success_linked'] = "You are succesfully linked, Next time just click Facebook connect button once to loggin.";
$fb_graph_lang['fb_option_explained'] = "Facebook account status.";
$fb_graph_lang['fb_linked_option'] =  'Linked';
$fb_graph_lang['fb_unlinked_option'] = "Unlinked. (You must log-in using password.)";

//################# NO NEED TO EDIT BELOW, ENTER YOUR APP DETAILS IN ADMIN PAGE #######################

define("APP_ID",bb_get_option( 'fb_app_id' )); //your application ID
define("APP_SECRET",bb_get_option( 'fb_secret' )); // Application secret

//################### STOP EDITING HERE ################################################

require_once('facebook.php');
//let's try connecting to facebook
function try_connect() 
{
	global $fb_graph_lang;
	$facebook = new Facebook(array(
	'appId'  => APP_ID,
	'secret' => APP_SECRET,
	'cookie' => true,
	));
	$session = $facebook->getSession();
	$me = null;
	if ($session) {
	try {
			$access_token = $facebook->getAccessToken();
			$me = $facebook->api('/me');
			$uid = $facebook->getUser();
		} catch (FacebookApiException $e) {
		error_log($e);
		 }
}
return array($uid,$me,$session,$access_token,$facebook);
}


function fb_add_extra_profile_feild() //show link option on profile page
{
list($uid,$me,$session,$access_token,$facebook) = try_connect();
global $bbdb,$fb_graph_lang;

	if (bb_facebook_location()=="profile.php" && fb_check_local_user() && fb_check_remote_user()) 
	{
	$table_name = $bbdb->prefix . "fbuser";
	$fb_userid=$bbdb->get_var("SELECT fb_linked FROM $table_name WHERE fb_userid=".$uid);
	$option1 = ($fb_userid==1)? 'checked="checked"' : '';
	$option2 = ($fb_userid==0)? 'checked="checked"' : '';
	  echo '<div style="margin:10px;padding:10px;background-color:#E6FFFF;">
	  <div>'.$fb_graph_lang['fb_option_explained'].'</div>
  <div><input name="fb-linked" type="radio" value="val-linked" '.$option1.' /> '.$fb_graph_lang['fb_linked_option'].'</div>
  <div><input name="fb-linked" type="radio" value="val-unlink" '.$option2.' /> '.$fb_graph_lang['fb_unlinked_option'].'</div>
  </div>';
	}
}

//get login button for template
function fb_get_login_button()
{
	global $fb_graph_lang;
	list($uid,$me,$session,$access_token,$facebook) = try_connect();

	if(!$me && !bb_is_user_logged_in())
	{
		echo $fb_graph_lang['login_button'];
	}
echo '<span id="fb-root"></span>';
}

// fb userdata.. usage eg: fb_get_userdata('name')
function fb_get_userdata($para)
{
	list($uid,$me,$session,$access_token,$facebook) = try_connect();
	echo $me[$para];
}

//let's clean username before displaying on the inputbox
function fb_get_clean_username()
{
	list($uid,$me,$session,$access_token,$facebook) = try_connect();
	echo clean_username($me['name']);
}


function get_post_data() {	// examine post and do actions accordingly
list($uid,$me,$session,$access_token,$facebook) = try_connect();
global $bbdb, $fb_graph_lang;

	if ($_POST && (isset($_POST['linktype']) && $_POST['linktype']=="new" && bb_facebook_location()=='bb-fb-connect.php')) {
		
		$user_login = '';
		$user_safe = true;
		$_POST = stripslashes_deep( $_POST );
		$_POST['fb_username'] = trim( $_POST['fb_username'] );
		$user_login = sanitize_user( $_POST['fb_username'], true );	
		
		if ( $user_login !== $_POST['fb_username'] ) {
		$bad_input = true;
			if ( $user_login ) {
				bb_die(printf($fb_graph_lang['invalid_user'],$_POST['fb_username']));
			}
		}
			
		if ( !$bad_input ) {
			$table_users = $bbdb->prefix . "users";
			$bb_userexist=$bbdb->get_var("SELECT user_email,user_login FROM $table_users WHERE user_login = '$user_login'"); //check user
			if($bb_userexist){
				bb_die(printf($fb_graph_lang['user_exist'],$_POST['fb_username']));
			}
			elseif($bb_userexist==$me['email'])
			{
				bb_die(printf($fb_graph_lang['email_exist'],$me['email']));
			}else{
				echo $bb_userexist;
				$new_user = bb_new_user( $user_login, $me['email'], $me['link'] );
				do_action('register_user', $new_user);
				 
				
				//$new_user_id = str_replace('Resource id #','',mysql_insert_id()); //tried using this method to get last registered user id, but it returns meta_users last id.. i don't know another way to retrive it with bbpress functions, hence using method below :
				$table_user = $bbdb->prefix . "users";
				$new_user_id=$bbdb->get_var("SELECT ID FROM $table_user ORDER BY ID DESC LIMIT 1 ");
				if($new_user_id)
				{
				$table_name = $bbdb->prefix . "fbuser";
				
				$link_data_still_exist=$bbdb->get_var("SELECT bb_userid FROM $table_name WHERE fb_userid =$uid"); 
				if($link_data_still_exist){ //if old fb row still exist in table.. do something
				$bbdb->query("UPDATE $table_name SET bb_userid=$new_user_id WHERE fb_userid=$uid");
				}else{
				$bbdb->query("INSERT INTO $table_name(bb_userid,fb_userid,fb_linked)VALUES($new_user_id,$uid,1)");
				}
				
				if($me['first_name'] && $me['last_name']){
					$table_usermeta = $bbdb->prefix . "usermeta";
					$bbdb->query("UPDATE $table_user SET display_name='".$me['first_name']."' WHERE ID=$new_user_id");
					$bbdb->query("INSERT INTO $table_usermeta(user_id,meta_key,meta_value)VALUES($new_user_id,'first_name','".$me['first_name']."')");
					$bbdb->query("INSERT INTO $table_usermeta(user_id,meta_key,meta_value)VALUES($new_user_id,'last_name','".$me['last_name']."')");
					}
				bb_load_template( 'register-success.php', $_globals );
				exit;
				}
			}
		}
	}elseif($_POST && (isset($_POST['linktype']) && $_POST['linktype']=="link" && bb_facebook_location()=='bb-fb-connect.php')) {
		$_POST = stripslashes_deep( $_POST );
		$_POST['username'] = trim($_POST['username']);
		$user_login = sanitize_user($_POST['username'],true);	
		$user_pass = $_POST['password'];
		
	$user_try_login = bb_login($user_login, $user_pass);
	if ( $user_try_login && !is_wp_error( $user_try_login ) ) 
		{
			$table_name = $bbdb->prefix . "fbuser";
			$local_userid = fb_get_local_existing_userid($user_login);
			$bbdb->query("INSERT INTO $table_name(bb_userid,fb_userid,fb_linked)VALUES($local_userid,$uid,1)");
			bb_load_template( 'register-success.php', $_globals );
			exit;
		}else{
		bb_die($fb_graph_lang['invalid_login']);
		}
	}
	
}
function credit_tx(){
return 
'PGRpdiBzdHlsZT0iZm9udC1zaX
plOjEycHg7dGV4dC1hbGlnbjpjZ
W50ZXIiPkZhY2Vib29rIGNvbm5l
Y3QgYnkgPGEgaHJlZj0iaHR0cDo
vL3d3dy5hYm91dGNvbnN1bWVyLm
NvbSIgdGFyZ2V0PSJuZXciPkFDP
C9hPjwvZGl2Pg==';
}
function fb_local_session_check() //redirect fb user to connect page
{
global $bbdb, $fb_graph_lang;
 list($uid,$me,$session,$access_token,$facebook) = try_connect();
 if(!fb_check_local_user() && !fb_check_remote_user() && bb_facebook_location()=='bb-fb-connect.php') //redirect unknown user on connect page
 {
	 bb_safe_redirect('index.php'); 
	 exit();
 }
	 if(!fb_check_local_user() && fb_check_remote_user() && bb_facebook_location()!='bb-fb-connect.php') //redirect fb user to connect page
	 {
		 bb_safe_redirect('bb-fb-connect.php'); 
		 exit();
	 }
 if(fb_check_local_user() && fb_check_remote_user() && fb_get_local_fb_status($uid)) //login existing users
	 {
		if(!bb_is_user_logged_in())
		{
			wp_set_auth_cookie( (int)fb_get_local_userid($uid), 0 );
			//do_action('bb_user_login', (int)fb_get_local_userid($uid));
			if(bb_facebook_location()=='bb-fb-connect.php')
				{
				bb_safe_redirect(bb_get_option('uri'));	//if user is still in connect page send them to main page
				}else{
				bb_safe_redirect('bb-login.php');	//login page checks logged user and sends user back to main page. this way user wont see unrefreshed page
				}
		}else{
			if(bb_facebook_location()=='bb-fb-connect.php')
			{
			bb_safe_redirect(bb_get_option('uri'));
			}
		//Show fb logout link
		add_filter( 'bb_logout_link','fb_logout_link');
		}
	}
if(fb_check_local_user() && fb_check_remote_user() && !fb_get_local_fb_status($uid) && bb_facebook_location()=='bb-fb-connect.php') //redirect unlinked users.
	{
	bb_safe_redirect(bb_get_option('uri'));
	}

//Unlink fb user
if($_POST && isset($_POST['fb-linked']) && $_POST['fb-linked']=="val-unlink" && bb_facebook_location()=='profile.php') {
		$table_name = $bbdb->prefix . "fbuser";
		$bbdb->query("UPDATE $table_name SET fb_linked=0 WHERE fb_userid=".$uid);
	}elseif($_POST && isset($_POST['fb-linked']) && $_POST['fb-linked']=="val-linked" && bb_facebook_location()=='profile.php') {
		$table_name = $bbdb->prefix . "fbuser";
		$bbdb->query("UPDATE $table_name SET fb_linked=1 WHERE fb_userid=".$uid);
	}
}

function fb_logout_link() // fb log out link
	{
	$fb_logout_link= '<a href="#" onclick="javascript:fb_logout(); return false;">Log Out</a>';
	return $fb_logout_link;
	}

function fb_check_remote_user()
{
	list($uid,$me,$session,$access_token,$facebook) = try_connect();
	if (isset($uid) && $uid > 0 && isset($session))
	{return true;}else{return false;}
}

function fb_check_local_user() // simple local user checking system
{
	list($uid,$me,$session,$access_token,$facebook) = try_connect();
	if (fb_get_local_userid($uid) > 0 ) 
	{return true;}else{return false;}
}

function fb_get_local_userid($u_id) {
global $bbdb;
$bb_userid=$bbdb->get_var("SELECT ID as bbid FROM ".$bbdb->prefix."users 
LEFT JOIN ".$bbdb->prefix."fbuser ON ".$bbdb->prefix."users.ID=".$bbdb->prefix."fbuser.bb_userid WHERE ".$bbdb->prefix."fbuser.fb_userid =".$u_id);

if($bb_userid)
	{return $bb_userid;}else{return 0;}
}
function fb_get_local_fb_status($u_id) 
{
global $bbdb;
$bb_userid=$bbdb->get_var("SELECT fb_linked as lnkid FROM ".$bbdb->prefix."users 
LEFT JOIN ".$bbdb->prefix."fbuser ON ".$bbdb->prefix."users.ID=".$bbdb->prefix."fbuser.bb_userid WHERE ".$bbdb->prefix."fbuser.fb_userid =".$u_id);
if($bb_userid>0)
	{return true;}else{return false;}
}

function fb_get_local_existing_userid($user_name) {
global $bbdb;
$bb_userid=$bbdb->get_var("SELECT ID FROM ".$bbdb->prefix."users WHERE user_login='$user_name'");
if($bb_userid)
	{return $bb_userid;}else{return 0;}	

}
//output head scripts..
function fb_foot_script()
{
list($uid,$me,$session,$access_token,$facebook) = try_connect();
echo base64_decode(credit_tx()).'<script>
      window.fbAsyncInit = function() {
        FB.init({
          appId   : \''.APP_ID.'\',
          session : '.json_encode($session).',
          status  : true,
          cookie  : true,
          xfbml   : true
        });
		
        FB.Event.subscribe(\'auth.login\', function() {
          window.location.reload();
        });
      };

      (function() {
        var e = document.createElement(\'script\');
        e.src = document.location.protocol + \'//connect.facebook.net/en_US/all.js\';
        e.async = true;
        document.getElementById(\'fb-root\').appendChild(e);
		
      }());
	 function fb_logout()
	 {
		 FB.logout(function(response) {
			  // user is now logged out
			  window.location = "bb-login.php?logout=1"
		});
	 }
   	 function requestExtPerm(){
	  FB.login(function(response) {
		   if (response.session) {
			 if (response.perms) {
			    window.location.reload();
			 } else {
			   // user is logged in, but did not grant the permissions
			 }
		   } else {
			  // user is not logged in
		   }
		 }, {perms: \'publish_stream,email\'});
   
		}
    </script>';
}

//prepare installations
function fb_install()
{
global $bbdb;
$table_name = $bbdb->prefix . "fbuser";
$table_query = "CREATE TABLE IF NOT EXISTS `$table_name` (
  `bb_userid` int(20) NOT NULL,
  `fb_userid` int(20) NOT NULL,
  `fb_linked` tinyint(1) NOT NULL,
  `fb_publish` tinyint(1) NOT NULL,
  PRIMARY KEY (`fb_userid`)
)";
$bbdb->query($table_query);
}

//get script location
function bb_facebook_location() {
	$file = '';
	foreach ( array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']) as $name )
		if ( false !== strpos($name, '.php') )
			$file = $name;

	return bb_find_filename( $file );
}

function fb_connect_actions(){

	if(!fb_check_remote_user() && bb_facebook_location()=='bb-fb-connect.php' && bb_facebook_location()!='bb-login.php')
	{
	bb_safe_redirect('index.php'); 
	//echo '<meta http-equiv="refresh" content="0;URL=bb-login.php" />';
	exit();
	}
	else
	{
	bb_load_template( 'fb_connect.php', array('bb_db_override', 'page_id') );
	}

}
//style for template
function fb_head_script()
{
if(bb_facebook_location()=='bb-fb-connect.php')
	{
	echo '<style type="text/css">
	<!--
	#fb_connect_wraper {
		margin-right: 275px;
		width: 680px;
		padding-top: 1em;
		float: left;
		clear: both;
		margin:0;
	}
	#fb_connect_wraper h2 {
	-moz-border-radius-topleft:3px;
	-moz-border-radius-topright:3px;
		background:#FFEB75;
		border:1px solid #9CA7B8;
		padding:4px 8px;
		text-shadow:0 1px 0 #FFFBE6;
		margin:0;
		font-size: 1.2em;
		color: #AA1515;
	
	}
	.fb_connect_table {
		margin:0;
		padding:0;
		-moz-border-radius-bottomleft:3px;
		-moz-border-radius-bottomright:3px;
		border-color:-moz-use-text-color #9CA7B8 #9CA7B8;
		border-right:1px solid #9CA7B8;
		border-style:none solid solid;
		border-width:0 1px 1px;
		background: #FFFFF2;
		margin-bottom: 1em;
		clear:both;
	}
	.fb_connect_table_inner{
		margin: 0;
		padding: 0.5em 0.5em 0.5em 1em;
		list-style: none;
		border-bottom: 1px solid #CCCCCC;
		background: #FFFFCC;
		font: 12px Georgia, "Times New Roman", Times, serif;
	
	}
	-->
	</style>';
	}
}
//just clean fb username
function clean_username($string)
{
$specialCharacters = array('#' => '','$' => '','%' => '','&' => '','@' => '','.' => '','€' => '','+' => '','=' => '','§' => '','\\' => '','/' => '','\'' => '',);
while (list($character, $replacement) = each($specialCharacters)) {
$string = str_replace($character, '-' . $replacement . '-', $string);
}
$string = strtr($string,"ÀÁÂÃÄÅ�áâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
// Remove all remaining other unknown characters
$string = preg_replace('/[^a-zA-Z0-9\-]/', '', $string);
$string = preg_replace('/^[\-]+/', '', $string);
$string = preg_replace('/[\-]+$/', '', $string);
$string = preg_replace('/[\-]{2,}/', '', $string);
return strtolower($string.rand(1, 200));
}

function fb_configuration_page()
{
?>
<h2><?php _e( 'Facebook Connect Settings' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'fb_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<p><?php printf( __( 'You will need facebook application ID and Secret key from Facebook, <a href="%s">Click here</a> to create an application.' ), 'http://developers.facebook.com/setup/' ); ?></p>
	</fieldset>
	<fieldset class="submit">
<?php
	$after = '';
	bb_option_form_element( 'fb_app_id', array(
		'title' => __( 'Facebook Application ID' ),
		'attributes' => array( 'maxlength' => 20),
		'value' =>bb_get_option('fb_app_id'),
		'after' => $after
	) );
	bb_option_form_element( 'fb_secret', array(
		'title' => __( 'Facebook Application Secret' ),
		'attributes' => array( 'maxlength' => 40 ),
		'value'=> bb_get_option('fb_secret'),
		'after' => "You need Application Secret, not API key"
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
		bb_update_option('fb_app_id', $_POST['fb_app_id']);
		bb_update_option('fb_secret', $_POST['fb_secret']);
		bb_admin_notice( __('Configuration saved.') );
	}
}
add_action('bb_admin-header.php', 'fb_configuration_page_process');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'fb_install');


//here exit the heck out if app details isn't entered
if ( !bb_get_option( 'fb_app_id' ) || !bb_get_option( 'fb_secret' )) {
	return;
}

add_action('bb_init', 'fb_local_session_check');
add_action('bb_head', 'fb_head_script');
add_action('bb_foot', 'fb_foot_script');
add_action('extra_profile_info', 'fb_add_extra_profile_feild',8);	
if (bb_facebook_location()=='bb-fb-connect.php') {	// determines if we're actually on register.php and only hooks in that case
	add_action('bb_send_headers', 'get_post_data');	// check before headers finish sending
} 


?>
