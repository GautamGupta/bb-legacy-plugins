<?php
/*
Plugin Name:  Post Count Plus - Dynamic.Titles & More!
Plugin URI:  http://bbpress.org/plugins/topic/83
Description: An enhanced "user post count" with "custom titles" for topics and profiles, based on posts and membership, with cached results for faster pages. No template edits required.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 1.1.11

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/

Instructions:   install, activate, tinker with settings in admin menu
*/

global $post_count_plus;
if (!is_bb_feed()) {add_action( 'bb_init', 'post_count_plus_initialize');}

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 
	function post_count_plus_add_admin_page() {bb_admin_add_submenu(__('Post Count Plus'), 'administrate', 'post_count_plus_admin');}
	add_action( 'bb_admin_menu_generator', 'post_count_plus_add_admin_page' );	
	if (isset($_GET['plugin']) && $_GET['plugin']=="post_count_plus_admin") {require_once("post-count-plus-admin.php");} // load entire core only when needed
}

function post_count_plus($user_id=0, $posts_only=0, $titlelink='') {
global $post_count_plus;
if ($posts_only || (bb_get_location()=="profile-page" && $posts_only==0)) {echo __("Posts: ").bb_number_format_i18n(post_count_plus_get_count($user_id));}
else {
	// if (!$titlelink || $post_count_plus['custom_title']) { // calculate custom titles here
	if (!$user_id) {
		$location=bb_get_location(); 
		if ($location=="profile-page") {global $user; $user_id=$user->ID;}
		else {$user_id=get_post_author_id();} 
		// else {$user_id=bb_get_current_user_info( 'id' );}
	} 
	$user = bb_get_user( $user_id );
	if (!empty($user->ID)) {			 		
		if ($post_count_plus['custom_title']) { 
			if ($user->title) {$title=$user->title;}
			else {$title=post_count_plus_custom_title($user_id);}
		} else {$title=get_post_author_title();}  // $title=$user->title; // old title
		$url=""; 
		switch ($post_count_plus['title_link']) :
			case "Profile" : $url=get_user_profile_link($user_id); break;
			case "Author URL" : $url=$user->user_url; break; 
			case "Nothing" : $url=""; break;
		endswitch;
		if ($url) {$titlelink='<a href="' . attribute_escape($url) . '">' . $title . '</a>';} else {$titlelink=$title;}
	}

echo "<p class='post_count_plus'>";
	echo "<strong>$titlelink</strong><br /><small>";	
	if ($post_count_plus['join_date']) {post_count_plus_join_date($user_id);  echo "<br />";}
	if ($post_count_plus['post_count'] && $posts=post_count_plus_get_count($user_id)) {echo __("Posts: ").bb_number_format_i18n($posts);}
echo "</small></p>";
}
}

function post_count_plus_get_count($user_id=0) {
if (empty($user_id)) {
	$location=bb_get_location(); 	
	if ($location=="profile-page") {global $user; $user_id=$user->ID;}
	else {$user_id=get_post_author_id();} 
}
if ($user_id) {
	$user=bb_get_user($user_id); 	
	$posts=$user->post_count;  // bb_get_usermeta( $user_id, 'post_count');	// even this should be bypassed at some point with a simple cache check & mysql query - sometimes causes 2 queries
	if (strlen($posts)==0) {
		global $post_count_plus,$bb,$bbdb;  $posts=0; $comments=0;
		$query="SELECT count(post_status) FROM $bbdb->posts WHERE post_status=0 AND poster_id=$user_id";
		$posts=intval($bbdb->get_var($query));
		
		if ($post_count_plus['wp_comments'] && !empty($bb->wp_table_prefix)) {		
			$query="SELECT COUNT( comment_approved ) FROM $bb->wp_table_prefix"."comments WHERE comment_approved=1 AND user_id=$user_id";
			$comments=intval($bbdb->get_var($query));
			$posts+=$comments;
		}		
		
		// bb_update_usermeta( $user_id, 'post_count', $posts);  // uses too many queries, we'll do it directly
		// $bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ('".$user_id."', 'post_count', '".$posts."') ");				
		if (empty($post_count_plus['read_only'])) {bb_update_meta( $user_id, "post_count", $posts, 'user' );}
	}
}	
if ($posts) {return  $posts;} else {return 0;}
}

function post_count_plus_update() {
// $user_id = bb_get_current_user_info( 'id' );  $posts=intval(bb_get_usermeta( $user_id, 'post_count'));
// if ($user_id && $posts) {global $bbdb; $bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".($posts+1)."' WHERE user_id = '".$user_id."' AND meta_key = 'post_count' LIMIT 1");}
global $bb_current_user; 
if (!empty($bb_current_user->ID)) {
	$user_id=$bb_current_user->ID;    
	$user=bb_get_user($user_id); bb_update_meta( $user_id, "post_count", (intval($user->post_count)+1), 'user' );
}
} 

function post_count_plus_join_date($user_id=0,$date_format='') {
global $post_count_plus; 
if (!$date_format) {$date_format=$post_count_plus['join_date_format'];}
	if (!$user_id) {
		$location=bb_get_location(); 
		if ($location=="profile-page") {global $user; $user_id=$user->ID;}
		else {$user_id=get_post_author_id();} 
		// else {$user_id=bb_get_current_user_info( 'id' );}
	}
	$user = bb_get_user( $user_id );
	if (!empty($user->ID)) {	 
		echo  __('Joined: ').bb_gmdate_i18n(__($date_format), bb_gmtstrtotime( $user->user_registered )); 
	}	
}

function post_count_plus_find_title($user_id=0,$posts=0,$days=0,$role='') {	
global $post_count_plus, $post_count_plus_title_cache;
if ($user_id) {	
	if (isset($post_count_plus_title_cache[$user_id])) {return $post_count_plus_title_cache[$user_id];}		
	if (!$posts) {$posts=post_count_plus_get_count($user_id);}
	$user = bb_get_user($user_id);
	if (!$days) {$days=intval((bb_current_time('timestamp') - bb_gmtstrtotime( $user->user_registered ))/86400);}						
	$capabilities=(isset($user->bb_capabilities)) ? $user->bb_capabilities : $user->capabilities;  // makes compatibile for 0.8.x - 1.0a
	if (!$role && !empty($capabilities)) {		
		$role=reset(array_keys($capabilities)); 
//		$role=array_pop(array_intersect(array_keys($capabilities),array_keys($GLOBALS['bb_roles']->role_names)));	// grabs all the roles, nice!
	}
}
$found=0; $width=5; $rows=floor(count($post_count_plus['custom_titles'])/$width);
for ($i=1; $i<$rows; $i++) {
if ($post_count_plus['custom_titles'][$i*$width]) {
	$posts0=$post_count_plus['custom_titles'][$i*$width+1];
	$days0=$post_count_plus['custom_titles'][$i*$width+2];
	$role0=$post_count_plus['custom_titles'][$i*$width+3];		
	if ((!$posts0 || $posts>=$posts0) && (!$days0 || $days>=$days0) && (!$role0 || $role==$role0)) {$found=$i*$width;}
}	
} // we don't break out of the loop because we need to see if there is a higher value more appropriate - array needs to be in sorted order though

// echo " <!-- f:$found p0:$posts0 p:$posts d0:$days0 d:$days r0:$role0 r:$role \n\n --> "; // diagnostic

if ($user_id)  {$post_count_plus_title_cache[$user_id]=$found;}	// cache for same page queries
return $found;
}

function post_count_plus_custom_title($user_id=0,$posts=0,$days=0,$role='') {
global $post_count_plus;
$title=""; 
$found=post_count_plus_find_title($user_id,$posts,$days,$role);
if ($found) { 
	$title=$post_count_plus['custom_titles'][$found];
	$color=$post_count_plus['custom_titles'][$found+4];
	if ($color) {$title="<span style='color:$color;'>$title</span>";}
}
return $title;
}

function post_count_plus_user_color($user_name, $user_id) {
global $post_count_plus;
// if (bb_get_location()=="topic-page") {
	if ($post_count_plus['user_color']) {		
		$found=post_count_plus_find_title($user_id);	
		$color=$post_count_plus['custom_titles'][$found+4];
		if ($color) {$user_name="<span class='post_count_plus' style='color:$color;'>$user_name</span>";}
	}
return $user_name;
}

function post_count_plus_delete_link_cleanup($r,$post_status,$post_id) {	// nasty fix for nasty mess that ajaxPostDelete creates
if (strpos($r,'ajaxPostDelete')!==false && strpos($r,'post_count_plus')!==false) {
$r=preg_replace("/(.*?)<span class\='post\_count\_plus' style\='.*?'>(.*?)<\/span>(.*?)/i","\${1}\${2}\${3}",$r);
}
return $r;
}

function post_count_plus_user_link( $url, $user_id) {	// forces links to profile instead of author's url on topic pages
global $post_count_plus;
// if (bb_get_location()=="topic-page") {
	switch ($post_count_plus['user_link']) :
		case "Profile" : return attribute_escape( get_user_profile_link( $user_id)); break;
		case "Author URL" : return $url; break;
		case "Nothing" : return ""; break;
	endswitch;
return $url;
}

function post_count_plus_profile_key($keys) {	// inserts post_count into profile without hacking
global $post_count_plus, $self; 
if (empty($self)==true && isset($_GET['tab'])==false && bb_get_location()=="profile-page") {
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('post_count' => array(0, __('Posts'))), array_slice((array) $keys,  1));    
}
return (array) $keys;
}

function post_count_plus_add_css() { global $post_count_plus;  echo '<style type="text/css">'.$post_count_plus['style'].'</style>';} // inject css

function post_count_plus_filter($titlelink) {post_count_plus(0,0,$titlelink); return '';}	// only if automatic inserts are selected

function post_count_plus_initialize() {
	global $bb, $bb_current_user, $post_count_plus, $post_count_plus_type, $post_count_plus_label;
	if (!isset($post_count_plus)) {$post_count_plus = bb_get_option('post_count_plus');
		if (!$post_count_plus) {
		$post_count_plus['activate']=true;
		$post_count_plus['read_only']=false;
		$post_count_plus['wp_comments']=false;
		$post_count_plus['post_count']=true;
		$post_count_plus['join_date']=true;		
		$post_count_plus['custom_title']=true;
		$post_count_plus['profile_insert']=true;
		$post_count_plus['user_color']=true;
		$post_count_plus['user_link']="Profile";
		$post_count_plus['title_link']="Profile";
		$post_count_plus['join_date_format']="M 'y";
		$post_count_plus['additional_bbpress']="";
		$post_count_plus['additional_wordpress']="";
		$post_count_plus['style']=".post_count_plus {color:SlateGray; line-height:150%; white-space:nowrap;}\n.post_count_plus a {color:DarkCyan;}\n#thread .post li {clear:none;}";
		$post_count_plus['custom_titles']=array(
		"New Title",	"Minimum Posts", "Minimum Days", "Minimum Role", "Color",
		"new member",	"0",		"0",			"",	"SlateBlue",
		"junior member","5","14","","Navy",
		"member","10","30","","",
		"senior member","50","180","","#0000FF",
		"preferred member","100","365","","SkyBlue",
		"mod","0","0","moderator","Red",
		"admin","0","0","administrator","DarkRed",
		"senior admin","0","0","keymaster","DarkRed");
		}}						
	// if (BB_IS_ADMIN) {		// doesn't exist until 1040 :-(
		$post_count_plus['custom_titles'][0]=__("New Title");	 
		$post_count_plus['custom_titles'][1]=__("Minimum Posts");
		$post_count_plus['custom_titles'][2]=__("Minimum Days");
		$post_count_plus['custom_titles'][3]=__("Minimum Role");
		$post_count_plus['custom_titles'][4]=__("Color");					
		
		$post_count_plus_label['activate']=__("Use features without template editing ?");
		$post_count_plus_label['read_only']=__("Read Only - display but don't update ?");
		$post_count_plus_label['wp_comments']=__("Include WordPress comment counts in post counts ?");
		$post_count_plus_label['post_count']=__("Show post counts for users in topic pages ?");
		$post_count_plus_label['join_date']=__("Show joined date for users in topic pages ?");
		$post_count_plus_label['custom_title']=__("Show custom titles based on posts & membership ?");
		$post_count_plus_label['profile_insert']=__("Insert post count for users into profile?");
		$post_count_plus_label['user_color']=__("Match USERNAME color to TITLE color?");
		$post_count_plus_label['user_link']=__("Where should their USERNAME link to?");		
		$post_count_plus_label['title_link']=__("Where should their TITLE link to?");
		$post_count_plus_label['join_date_format']=__("Custom <a target=_blank href='http://php.net/date#function.date'>format</a> for user joined date:");
		$post_count_plus_label['additional_bbpress']=__("Include additional bbPress post tables:");
		$post_count_plus_label['additional_wordpress']=__("Include additional WordPress comment tables:");
		$post_count_plus_label['style']=__("Custom style for post author info:");
		$post_count_plus_label['custom_titles']=__("<h2>Custom Titles</h2>Enter any special titles given based upon number of posts, days of membership, and/or role.<br>Each field is optional, but at least one minimum is required.<br />");

		$post_count_plus_type['activate']="binary";		
		$post_count_plus_type['read_only']="binary";
		$post_count_plus_type['wp_comments']="binary";
		$post_count_plus_type['post_count']="binary";
		$post_count_plus_type['join_date']="binary";				
		$post_count_plus_type['custom_title']="binary";						
		$post_count_plus_type['profile_insert']="binary";
		$post_count_plus_type['user_color']="binary";
		$post_count_plus_type['user_link']="Profile,Author URL,Nothing";
		$post_count_plus_type['title_link']="Profile,Author URL,Nothing";
		$post_count_plus_type['join_date_format']="input";
		$post_count_plus_type['additional_bbpress']="input";
		$post_count_plus_type['additional_wordpress']="input";
		$post_count_plus_type['style']="textarea";
		$post_count_plus_type['custom_titles']="array,5,10";		
	// }
	if (empty($post_count_plus['read_only'])) {add_action('bb_new_post', 'post_count_plus_update',200);}
	if ($post_count_plus['profile_insert']) {add_filter( 'get_profile_info_keys','post_count_plus_profile_key',200);}
	if ($post_count_plus['activate']) {add_filter( 'post_author_title', 'post_count_plus_filter'); add_filter( 'post_author_title_link', 'post_count_plus_filter');}
	if ($post_count_plus['style']) {add_action('bb_head', 'post_count_plus_add_css');}	
	if ($post_count_plus['user_color']) {
		add_filter( 'get_post_author','post_count_plus_user_color',200,2);
	            	// add_filter( 'get_user_name','post_count_plus_user_color', 200,2);	            	
		add_filter( 'get_topic_last_poster', 'post_count_plus_user_color',200,2 ); 
		add_filter( 'get_topic_author', 'post_count_plus_user_color',200,2 ); 
		add_filter( 'post_delete_link', 'post_count_plus_delete_link_cleanup',10,3);
		add_action('bb_head', 'post_count_plus_user_cache');
	}
	if (bb_get_location()!="profile-page") {add_filter( 'get_user_link','post_count_plus_user_link',200,2);}
}	

function post_count_plus_user_cache() {
	global $post_count_plus, $topics, $stickies, $super_stickies,$forums;
	if (in_array(bb_get_location(),array('front-page','forum-page', 'tag-page','search-page','favorites-page','profile-page','view-page'))) {	
		if ( !empty($super_stickies) ) {foreach ( $super_stickies as $topic ) { $ids[$topic->topic_last_poster]=$topic->topic_last_poster; }}
		if ( !empty($stickies) ) {foreach ( $stickies as $topic ) { $ids[$topic->topic_last_poster]=$topic->topic_last_poster; }}
		if ( !empty($topics) ) {foreach ( $topics as $topic ) { $ids[$topic->topic_last_poster]=$topic->topic_last_poster; }} 
		if ( !empty($forums) && function_exists('forum_last_topic_id')) {
			foreach ($forums as $forum) { 
				$topic=get_topic(forum_last_topic_id($forum->forum_id)); 
				$ids[$topic->topic_last_poster]=$topic->topic_last_poster; 	
			}
		}		
		if (isset($ids)) {bb_cache_users($ids);}
	}
}
?>