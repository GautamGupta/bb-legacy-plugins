<?php
/*
Plugin Name: OpenID for bbPress
Plugin URI: http://bbpress.org/plugins/topic/openid
Description:  Adds OpenID login support to bbPress so users may login using an identity from another provider. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

// error_reporting(E_ALL);

$openid_options['profile_text']="OpenID";
$openid_options['add_text']="Add OpenID providers to your account:";
$openid_options['remove_text']="Remove OpenID provider";
$openid_options['debug']=true;
$openid_options['root']=bb_get_option('uri');
$openid_options['whitelist']="";  // todo
$openid_options['blacklist']="";  // todo
$openid_options['icon']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/openid.png'; 

add_action('bb_init', 'openid');
add_action('bb_init', 'openid_remove');
add_action('extra_profile_info', 'openid_profile_edit',100);
add_action('openid_login','openid_login');

if (isset($_GET['openid_help'])) {echo "<body style='font-size:14px;margin-left:75px;' onload='document.getElementById(\"openid_help\").style.visibility=\"visible\";'>"; openid_help(); exit;}

function openid() {
global $openid_options, $bb_current_user, $bbdb; 

   if (isset($_POST['openid_url'])) {$openid_url=$_POST['openid_url'];} 
elseif (isset($_GET['openid_url'])) {$openid_url=$_GET['openid_url'];}

if (!empty($openid_url)){ 		
	@require_once('class.openid.php');
	$openid = new OpenIDService;
	$openid->SetIdentity($openid_url);
	$openid->SetTrustRoot($openid_options['root']);
	$openid->SetRequiredFields(array('email'));
	// $openid->SetOptionalFields(array('fullname','dob','gender','postcode','country','language','timezone'));
	if ($openid->GetOpenIDServer()){
		$openid_approve="http://".$_SERVER['HTTP_HOST'].remove_query_arg('openid_url'); // add_query_arg( 're',urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"].(empty($_SERVER["QUERY_STRING"]) ? "" : '?'.$_SERVER["QUERY_STRING"])));
		$openid->SetApprovedURL($openid_approve);
		$openid->Redirect(); 	// This will redirect user to OpenID Server
		exit;
	}else{
		$error = $openid->GetError();
		$error ="ERROR CODE: " . $error['code'] . "<br>".
			"ERROR DESCRIPTION: " . $error['description'] . "<br>";
		openid_redirect($error);
	}
}

elseif (isset($_GET['openid_mode'])) {
	if ($_GET['openid_mode'] == 'cancel') {openid_redirect(__("USER CANCELED REQUEST"));}
	elseif ($_GET['openid_mode'] != 'id_res') {openid_redirect(__("INVALID OPENID REQUEST"));} 	

	@require_once('class.openid.php');
	$openid = new OpenIDService;
	$openid->SetIdentity($_GET['openid_identity']); 
	$openid_result = $openid->ValidateWithServer();
	
	if ($openid->IsError() == true) {
		$error = $openid->GetError(); 
		$error="ERROR CODE: " . $error['code'] . "<br>".
			"ERROR DESCRIPTION: " . $error['description'] . "<br>";
		openid_redirect($error);			
	}
	
	elseif (empty($openid_result)===true || empty($_GET['openid_identity'])===true) {openid_redirect(__("INVALID AUTHORIZATION"));}
	
	else { // valid result
						
	$openid=addslashes(substr($_GET['openid_identity'],0,256));		// openid_assoc_handle						
	$openid_user_id=$bbdb->get_var("SELECT user_id FROM $bbdb->usermeta WHERE meta_key='openid' AND meta_value LIKE '%:\"$openid\";%' LIMIT 1");

	if (!$bb_current_user) {

		if ($openid_user_id) {		// we found a user, give them a cookie		
			if ($openid_options['debug']) {bb_update_usermeta($openid_user_id,'openid_debug',$_GET);}
			wp_set_auth_cookie( (int) $openid_user_id, 0 );	// 0 = don't remember, short login, todo: use form value
			do_action('bb_user_login', (int) $openid_user_id );		
			openid_redirect();			
		} else { 	 // openid is valid but no user_id, we need to create a user			
			$error="Sorry, account creation is not supported yet.<br>The following OpenID is not attached to any forum account:<br>$openid";
			openid_redirect($error);			
		}
		
	}
	
	elseif (!empty($bb_current_user->ID) && bb_get_location()=="profile-page" && isset($_GET['tab']) && $_GET['tab']=="edit") {
		
		if (!$openid_user_id) {	// no one owns this ID 
			$user = bb_get_user($bb_current_user->ID);
			(array) $user->openid[]=$openid;			
			bb_update_usermeta($bb_current_user->ID,'openid',(array) $user->openid);							
			openid_redirect();				
		} else {			
			if ($openid_user_id==$bb_current_user->ID) {openid_redirect(__("THIS PROVIDER IS ALREADY ON YOUR ACCOUNT"));}
			else {openid_redirect(__("THIS PROVIDER IS OWNED BY ANOTHER ACCOUNT"));}
		}					
	}
	
	} // valid result
	
}	// openid_mode
}	


function openid_profile_edit() {
global $bbdb,$user_id,$openid_options;
$user = bb_get_user( $user_id );
if (bb_is_user_logged_in()) :
echo '</fieldset><a name="openid"></a><fieldset><legend>'.$openid_options['profile_text'].'</legend>';
if (isset($_GET['openid_error'])) {echo "<div onclick='this.style.display=\"none\"' style='color:#000;width:75%;overflow:hidden;padding:3px 10px;background:#FFF6BF;border:1px solid #FFD324;'>".substr(addslashes(strip_tags($_GET['openid_error'],"<br>")),0,200)."</div>";}
echo '<p>'.$openid_options['add_text'].'</p><table><tr class="form-field"><th scope="row"><label for="openid_url">OpenID URL</label></th>';
echo '<td><input style="width:50%;padding-left:20px;background: #fff url('.$openid_options['icon'].') no-repeat 2px 50%;" name="openid_url" id="openid_url"> [<a onclick=openid_help() href="#openid">help</a>]'; 
openid_help(); echo '</td></tr>';
if (!empty($user->openid)) {
foreach ((array) $user->openid  as $url ) {
	echo '<tr class="form-field"><th scope="row" style="padding-left:20px;background: url('.$openid_options['icon'].') no-repeat  50% 50%;"><label>[<a title="'.$openid_options['remove_text'].'" href="'.add_query_arg('remove_openid',urlencode($url)).'"><strong>x</strong></a>]</label></th><td> '.$url.' </td></tr>';
}
}
echo '</table></fieldset>';
endif;
}

function openid_login() {
global $openid_options;
?>
<h2 id="register"><?php _e('OpenID Login'); ?></h2>

<form method="post" action="<?php bb_option('uri'); ?>bb-login.php">
<fieldset>
<?php 
if (isset($_GET['openid_error'])) {echo "<div onclick='this.style.display=\"none\"' style='margin:0 0 1em 0; color:#000;width:75%;overflow:hidden;padding:3px 10px;background:#FFF6BF;border:1px solid #FFD324;'>".substr(addslashes(strip_tags($_GET['openid_error'],"<br>")),0,200)."</div>";}
?>
<table>
	<tr valign="top">
		<th scope="row"><?php _e('OpenID URL:'); ?></th>
		<td><input style="width:50%;padding-left:20px;background: #fff url(<?php echo $openid_options['icon']; ?>) no-repeat 2px 50%;" name="openid_url" id="openid_url"> [<a onclick=openid_help() href="#openid">help</a>]
		<?php openid_help(); ?></td>
	</tr>
</table>
</fieldset>
</form>
<?php
}

function openid_help() {
echo "
<script type='text/javascript'>
    function openid_help() {   
    var e=document.getElementById('openid_help'); 
    if (e.style.visibility == 'hidden') {e.style.visibility = 'visible';} else {e.style.visibility = 'hidden';}
    setTimeout(\"document.getElementById('openid_url').focus()\",1000);
    return false;
    } </script>
<table id='openid_help' onclick='this.style.visibility=\"hidden\"' border='0' cellpadding='3' cellspacing='0' nowrap style='font-size:1.2em;width:50%;visibility:hidden;margin:3px 0 0 -70px;white-space:nowrap;position:absolute;color:#000;overflow:hidden;padding10px;background:#FFF6BF;border:1px solid #FFD324;'>
<tr><td colspan=2>
There are <a target='_blank' href='http://wiki.openid.net/OpenIDServers'>dozens of OpenID providers</a>. Here's what to enter for some.<br>
<em>Parts in <b>bold</b> need to be replaced with your username on that service.</em></br>
</td></tr>
<tr><td style='text-align:right;color:navy;'>AOL</td><td>openid.aol.com/<b>screenname</b> <em>(AIM usernames work too)</em></td></tr>
<tr><td style='text-align:right;color:navy;'>Blogger</td><td><b>blogname</b>.blogspot.com</td></tr>
<tr><td style='text-align:right;color:navy;'>Flickr</td><td>flickr.com/photos/<b>username</b></td></tr>
<tr><td style='text-align:right;color:navy;'>Googe</td><td>google.com/accounts/o8/id <em>(enter exactly as is)</em></td></tr>
<tr><td style='text-align:right;color:navy;'>LiveJournal</td><td><b>username</b>.livejournal.com</td></tr>
<tr><td style='text-align:right;color:navy;'>LiveDoor</td><td>profile.livedoor.com/<b>username</b> <em>(Japan)</em></td></tr>
<tr><td style='text-align:right;color:navy;'>Orange</td><td>openid.orange.fr <em>(France)</em></td></tr>
<tr><td style='text-align:right;color:navy;'>SmugMug </td><td><b>username</b>.smugmug.com</td></tr>
<tr><td style='text-align:right;color:navy;'>Technorati</td><td>technorati.com/people/technorati/<b>username</b></td></tr>
<tr><td style='text-align:right;color:navy;'>Vox</td><td><b>member</b>.vox.com</td></tr>
<tr><td style='text-align:right;color:navy;'>Yahoo</td><td>openid.yahoo.com</td></tr>
<tr><td style='text-align:right;color:navy;'>WordPress.com</td><td><b>username</b>.wordpress.com <em>(first login to wordpress.com)</em></td></tr>
</table>";
}

function openid_remove() {
if (isset($_GET['remove_openid'])) {	
	bb_repermalink();
	global $bbdb,$user_id,$bb_current_user,$openid_options;
	$user = bb_get_user( $user_id );  
	if ($user_id==$bb_current_user->ID || bb_current_user_can('administrate')) :
		$remove_openid=intval($_GET['remove_openid']);
		$user->openid=(array) $user->openid;
		$key=array_search($remove_openid,$user->openid);
		if ($key!==false) {unset($user->openid[$key]); bb_update_usermeta($user_id,'openid',(array) $user->openid);}
	endif;				
}
}

function openid_redirect($error="") {
global $openid_options;
	if (isset($_POST['openid_return_to'])) {$re=$_POST['openid_return_to'];} 
	elseif (isset($_GET['openid_return_to'])) {$re=$_GET['openid_return_to'];} 	
	else {$re=$_SERVER['REQUEST_URI'];}	// else {$re =$openid_options['root'];}
	$re=remove_query_arg('openid_mode',$re);
	$re=remove_query_arg('openid_url',$re);
	if ($error) {$re=add_query_arg('openid_error',urlencode($error),$re);}
	// if ($error) {echo "$error"; exit;}
	bb_safe_redirect($re);	
	exit;
}

?>