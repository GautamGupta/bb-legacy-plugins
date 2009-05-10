<?php
/*
Plugin Name: Cross Cookie
Plugin URI: http://bbpress.org/plugins/topic/cross-cookie
Description: Login users across multiple domain names with bbPress 0.9 and/or WordPress installs that share the same user database table. Requires WP 2.5 cookie method (use Ann's plugin for WP 2.7+)
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1
*/

$cross_cookie['url'] = "http://test.example.com/";	  	// change this to the alternate url for the other site on each install

/*   stop editing here  */

global $bb; $cross_cookie['name'] = defined('AUTH_COOKIE') ? AUTH_COOKIE : $bb->authcookie;

$domain=explode(".",$_SERVER['HTTP_HOST']); $count=count($domain); 
$cross_cookie['domain'] =$domain=".".$domain[$count-2].".".$domain[$count-1];

add_action('wp_footer','cross_cookie');
add_action('admin_footer', 'cross_cookie');
add_action('bb_foot','cross_cookie');
add_action('bb_admin_footer','cross_cookie');
add_action('login_form','cross_cookie');

add_action('init','cross_cookie_init',1);
add_action('bb_init','cross_cookie_init',1);

add_action('wp_login','cross_cookie_set');
add_action('bb_user_login','cross_cookie_set');

add_action('wp_logout','cross_cookie_logout');
add_action('bb_user_logout','cross_cookie_logout');

function cross_cookie_logout() {	// logout does a redirect so we set a cookie as a flag which is the only thing that can be sent with headers
	global $cross_cookie; $cross_cookie['ccout']=true;
	if (isset($_COOKIE['ccout'])) {$value=1+intval($_COOKIE['ccout']); if ($value>2) {$value="";}} else {$value=1;}  //  self deleting cookie	
	$expire = time() + ($value ?  1209600 : -1209600);
	setcookie('ccout', $value, $expire, '/', $cross_cookie['domain']);
}

function cross_cookie() {	 // this is where we insert a pixel beacon into the footer to trigger the other side to set/delete it's cookie
	global $current_user,$bb_current_user,$cross_cookie;
	if (!empty($current_user->data->cross_cookie)) {$cc=$current_user->data->cross_cookie; $user_id=$current_user->ID;} 
	elseif (!empty($bb_current_user->data->cross_cookie)) {$cc=$bb_current_user->data->cross_cookie; $user_id=$bb_current_user->ID;} 
	elseif (isset($cross_cookie['ccout']) || isset($_REQUEST['ccout'])) {$cc=""; $user_id=0;}
	else {return;}
	if (!empty($user_id)) {
		$value=isset($_COOKIE[$cross_cookie['name']]) ? $_COOKIE[$cross_cookie['name']] : "";	    // store cookie value now because it's not available on login
		if (function_exists('bb_update_usermeta')) {
			bb_update_usermeta( $user_id, 'cross_cookie_value', $value); 		
		} elseif (function_exists('update_usermeta')) {
			update_usermeta( $user_id, 'cross_cookie_value', $value);		
		}
	}
	$url=add_query_arg(array('cc'=>$cc,'r'=>rand(1,99999)),$cross_cookie['url']);	// random cuts through cache
	echo "<img height='1' width='1' border='0' src='$url' />";
}
	
function cross_cookie_init() {	//  does 2 things,  checks if  ?cc=  is set and sends back the pixel with the set cookie, also handles logout cookie
	global $cross_cookie;	
	if (isset($cross_cookie['ccout']) || isset($_REQUEST['ccout'])) {cross_cookie_logout();}	// first check if we are in a clear cross-cookie loop	
	if (!isset($_GET['cc'])) {return;}			
	$value=""; $cc=trim(mysql_real_escape_string(strip_tags($_GET['cc'])));
	if (!empty($cc)) {
	global $bbdb, $wpdb; if (!empty($bbdb)) {$ccdb=& $bbdb;} else {$ccdb=& $wpdb;}
	$user_id=intval($ccdb->get_var("SELECT user_id FROM $ccdb->usermeta WHERE meta_key='cross_cookie' AND meta_value='$cc' LIMIT 1"));
	if (!empty($user_id)) {
	$value=$ccdb->get_var("SELECT meta_value FROM $ccdb->usermeta WHERE meta_key='cross_cookie_value' AND user_id='$user_id' LIMIT 1");
	if (function_exists('bb_update_usermeta')) {bb_update_usermeta( $user_id, 'cross_cookie', '');}	// clear out our db flag
	elseif (function_exists('update_usermeta')) {update_usermeta( $user_id, 'cross_cookie', '');}
	}
	}
	if (!empty($value)) {$values=explode("|",$value);}	// get true expire time from cookie
	if (!empty($values[1])) {$expire=$values[1];} else {$expire = time() + ($value ?  1209600 : -1209600);}		// negative time = delete cookie
	if ($expire>0 && (time()-$expire)<180000) {$expire=0;}	// it's a session cookie
	// output magic happens here, sets cookie for the other side, sends 1x1 gif
	setcookie($cross_cookie['name'], $value, $expire, '/', $cross_cookie['domain']);	
	header('Content-Type: image/gif');
	header('Content-Length: 43');
	echo pack('H*','47494638396101000100900100FFFFFF00000021F90405140000002C00000000010001000002024401003B');	
	exit;	
}

function cross_cookie_set($ID_or_user_login) {	
	global $cross_cookie,$bbdb, $wpdb; if (!empty($bbdb)) {$ccdb=& $bbdb;} else {$ccdb=& $wpdb;}	
	if (is_numeric($ID_or_user_login)) {$user_id=intval($ID_or_user_login);}	// wordpress is moronic and tracks via user_login instead of ID
	else {
		$ID_or_user_login=mysql_real_escape_string($ID_or_user_login); 
		$user_id=intval($ccdb->get_var("SELECT ID FROM $ccdb->users WHERE user_login='$ID_or_user_login' LIMIT 1"));
	}
	if (empty($user_id)) {return;}
	$cc=md5(rand().$user_id.uniqid(rand(), true).$user_id.uniqid(rand(), true).$user_id);		// make an unguessable sequence	
	if (function_exists('bb_update_usermeta')) {
		bb_update_usermeta( $user_id, 'cross_cookie', $cc); 		
	} elseif (function_exists('update_usermeta')) {
		update_usermeta( $user_id, 'cross_cookie', $cc);		
	}
}

?>