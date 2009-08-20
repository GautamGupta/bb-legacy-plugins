<?php
/*
Plugin Name: Members Online
Plugin URI: http://bbpress.org/plugins/topic/members-online
Description: Shows which members are currently online or visited today. Tracks the total time online and last visit for each member in their profile.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2
*/

$members_online['footer']	= true;  	   //  automatically show in footer at bottom of page

$members_online['front_only'] = true; 	   //    true =  show only front page,  	false =  show in EVERY page footer

$members_online['show_anyone'] = true;    //   true = anyone can see list,    false = only logged in members can see

$members_online['posts']	= true;   	 //  automatically show member online status in their posts

$members_online['profile']	= true;    	 //  automatically show member online status in their profile

$members_online['timeout'] = 10;	  	 //  (minutes) how long should members be considered still online since last activity, default 10

$members_online['today'] = 'midnight';	//   'midnight' = today will show members online since midnight, or  '24' =  past 24 hours,  48 = 48 hours, etc.

/*    stop editing here    */

$members_online['timeout']*=60;		 //  convert to seconds

add_action('bb_init','members_online',99);
add_action('bb_user_logout', 'members_online_update');
add_action('members_online_now','members_online_now');
add_action('members_online_today','members_online_today');
if ($members_online['profile']) {add_filter( 'get_profile_info_keys','members_online_profile',100);}
if ($members_online['footer']) {add_filter('bb_foot','members_online_footer',100);}
if ($members_online['posts']) {
add_filter( 'post_author_title', 'members_online_post',100); 
add_filter( 'post_author_title_link', 'members_online_post',100);
}

function members_online_footer() {	
	global $members_online;
	if (!empty($members_online['front_only']) && !is_front()) {return;}
	if (empty($members_online['show_anyone']) && !bb_is_user_logged_in()) {return;}
	
	echo "<div class='members_online' style='text-align:center; width:760px; margin:0 auto;'>	
	<div style='text-align:left;'><h2>".__('Members Online')."</h2>
		<p><strong>".__('now').' :</strong> '; members_online_now();
	echo "<br /><strong>".__('today').' :</strong> '; members_online_today(); 
	echo "</p></div>";
}

function members_online($action='') {
global $bbdb,$bb_current_user, $bb_user_cache, $members_online;
if (empty($bb_current_user->ID)) {return;} 
$time=time(); $id=$bb_current_user->ID; $user=bb_get_user($id); 
if (isset($user->last_online)) { 
	$since=$time - intval($user->last_online);
	if (!empty($action) || $since >$members_online['timeout']) {	// user's previous session has expired, update total online time before we lose track
		(int) $user->time_online=intval($user->time_online)+( intval($user->last_online)-intval($user->first_online));
		$bbdb->query("UPDATE $bbdb->usermeta SET meta_value='$user->time_online' WHERE user_id='$id' AND meta_key='time_online' LIMIT 1");
		if (empty($action)) {
		$user->first_online=$user->last_online=$time;
		$bbdb->query("UPDATE $bbdb->usermeta SET meta_value='$time' WHERE user_id='$id' AND (meta_key='first_online' OR  meta_key='last_online') LIMIT 2");		
		} else { 	// logout, trick first_online to be higher than last_online to indicate
		$user->first_online=(int) $user->last_online+1;
		$bbdb->query("UPDATE $bbdb->usermeta SET meta_value='$user->first_online' WHERE user_id='$id' AND meta_key='first_online' LIMIT 1");						
		}		
	} elseif ($since>60) {	// regular update, save queries by not bothering to update unless it's been over a minute
		$bbdb->query("UPDATE $bbdb->usermeta SET meta_value='$time' WHERE user_id='$id' AND meta_key='last_online' LIMIT 1");
		$user->last_online=$time;
	}
} else {		 // plugin has never seen this member before 
	$bbdb->query("INSERT INTO $bbdb->usermeta ( user_id , meta_key , meta_value) VALUES ('$id', 'last_online', '$time') , ('$id', 'first_online', '$time') , ('$id', 'time_online', '1')");
	$user->first_online=$user->last_online=$time; $user->time_online=1;	
}
if (function_exists('wp_cache_add')) {wp_cache_set($user->ID, $user, 'users' );}  else {$bb_user_cache[$user->ID]=$user;}
}

function members_online_update() {members_online('logout');}

function members_online_now($time=0) {
global $bbdb, $members_online;  $members_online['footer']=false;
if (empty($members_online['show_anyone']) && !bb_is_user_logged_in()) {return;}

if (empty($time)) {$time=time()-$members_online['timeout'];}
	$results=$bbdb->get_results("SELECT ID,user_login FROM $bbdb->users LEFT JOIN $bbdb->usermeta ON ID=user_id WHERE meta_key='last_online' AND cast(meta_value AS unsigned)>'$time' ORDER BY meta_value DESC");
	if (!empty($results)) {				
		$output="";  $rewrite = bb_get_option( 'mod_rewrite' ); $bb_uri=bb_get_option('uri');
		if (empty($rewrite)) {$uri=$bb_uri."profile.php?id=";} else {$uri=$bb_uri."profile/"; }
		foreach ($results as $result) { $key=$result->ID; $value=$result->user_login;
			if (empty($rewrite) || $rewrite !== 'slugs' ) {$stub=$key;} else {$stub= bb_user_nicename_sanitize($value);}
			$output.=" <a rel='nofollow' href='".attribute_escape($uri.$stub)."'>$value</a>, ";
		}	
		echo rtrim($output,", ");
	} 
}

function members_online_today() {
	global $members_online; $time=time();
	$today=intval($members_online['today']);
	if (empty($today)) {$today=mktime(0, 0, 0,date('n',$time), date('j',$time), date('Y',$time) )+bb_get_option("gmt_offset")*3600;}
	else {$today=$time-3600*$today;}
	members_online_now($today);
}

function members_online_post($titlelink='') {
global $bbdb, $posts, $bb_post, $members_online; static $ids;
if (isset($bb_post) && !empty($bb_post->poster_id)) {
	if (!isset($ids)) {
		$time=time()-$members_online['timeout'];
		if (!empty($posts)) {
		$keys=array_keys($posts);	 //  weird PHP bug where I can't loop through $posts directly?
		foreach ($keys as $key) {if (!empty($posts[$key]->poster_id)) {$members[$posts[$key]->poster_id]=$posts[$key]->poster_id;} } 
		$members=implode(',',$members); 
		} else {$members=$bb_post->poster_id;}
		$ids=$bbdb->get_col("SELECT user_id FROM $bbdb->usermeta  WHERE user_id IN ($members) AND meta_key='last_online' AND cast(meta_value AS unsigned)>'$time'");		
		if (is_array($ids)) {$ids=array_flip($ids);}
	}
	if (isset($ids[$bb_post->poster_id])) {echo "<div style='color:#00aa00'>".__("online")."</div>";} 
	else {echo "<div style='color:#aaa'>".__("offline")."</div>";} 
}	
return $titlelink;
}

function members_online_profile($keys) {	// inserts post_count into profile without hacking
global $self, $user, $bb_user_cache, $members_online;  
if (empty($self)==true && isset($_GET['tab'])==false && bb_get_location()=="profile-page") {	  
	
	if (isset($user->last_online)) {
	$time=time();
	$status="<span style='border-bottom:1px dashed #eee' title='".date('r',$user->last_online)."'>".bb_since(-1+$user->last_online,1)." ".__('ago')."</span>";
	if ($user->last_online>=$user->first_online && $user->last_online>$time-$members_online['timeout']) {
		$status.=" (<span style='color:#00aa00'>".__("online")."</span>".(($user->first_online<$user->last_online) ? ' '.bb_since($user->first_online) : '').")";
	} else {$status.=" (<span style='color:#aaa'>".__("offline")."</span>)";} 
	$total=1+$user->time_online+($user->last_online-$user->first_online);
	$hours=floor($total/3600); $minutes=ceil(($total-$hours*3600)/60);
	$total=array(); if ($hours) {$total[]=" $hours ".__('hours');} if ($minutes) {$total[]=" $minutes ".__('minutes');} $total=implode(", ",$total);
	} else {$total=$status=" (<span style='color:#aaa'>".__("unknown")."</span>)";} 	
	
	$user->total_online=$total;
	$user->last_activity=$status;
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('total_online' => array(0, __('Total Time Online'))), array_slice((array) $keys,  1));    
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('last_activity' => array(0, __('Last Activity'))), array_slice((array) $keys,  1));    
	if (function_exists('wp_cache_add')) {wp_cache_set($user->ID, $user, 'users' );}  else {$bb_user_cache[$user->ID]=$user;}	
}
return (array) $keys;
}


?>