<?php
/*
Plugin Name:  Post Count Plus for WordPress
Plugin URI:  http://bbpress.org/plugins/topic/83
Description: WordPress helper plugin for bbPress's Post Count Plus
Author: _ck_
Author URI: http://bbShowcase.org
Version: 1.1.10
*/

$bb_table_prefix="bb_"; 	// change this if you use another prefix for bbPress tables

/*  stop editing here  */

add_action('comment_post', 'wp_post_count_plus_update',200);

function wp_post_count_plus_update() {
global $current_user; 
if (!empty($current_user->ID)) {
	$user_id=$current_user->ID; $user=get_userdata($user_id); 
	if (empty($user->post_count)) {$posts=wp_post_count_plus_get_count($user_id);} else {$posts=intval($user->post_count);}
	update_usermeta($user_id, "post_count", $posts+1);
}
} 

function wp_post_count_plus_get_count($user_id=0) {
if (empty($user_id)) {global $comment; if (!empty($comment->user_id)) {$user_id=$comment->user_id;}}
if (!empty($user_id)) {
	$user=get_userdata($user_id); 
	if (!empty($user->post_count)) {$posts=intval($user->post_count);}
	else {
		global $bb_table_prefix,$wpdb; 		
		/*   // is this query failing when there are no forum posts?
		$query="SELECT COALESCE(post_count,0)+COALESCE(comment_count,0) as meta_value 
		FROM (SELECT count(post_status) as post_count FROM $bb_table_prefix"."posts WHERE post_status=0 AND poster_id=1 GROUP BY poster_id) as t1 
		JOIN (SELECT count(comment_approved) as comment_count 
		FROM  $wpdb->comments WHERE comment_approved=1 AND user_id=1 GROUP BY user_id) as t2";		
		*/			
		$query="SELECT COUNT(post_status) FROM $bb_table_prefix"."posts WHERE post_status=0 AND poster_id=$user_id";
		$posts=intval($wpdb->get_var($query));		
		$query="SELECT COUNT( comment_approved ) FROM $wpdb->comments WHERE comment_approved=1 AND user_id=$user_id";
		$comments=intval($wpdb->get_var($query));
		$posts+=$comments;
		
		update_usermeta( $user_id, "post_count", $posts);
	}
}	
if (empty($posts)) {return 0;} else {return  $posts;}
}

function wp_post_count_plus_custom_title($user_id=0,$posts=0,$days=0,$role='') {
global $post_count_plus; $title=""; 
$found=wp_post_count_plus_find_title($user_id,$posts,$days,$role);
if (!empty($found)) { 
	$title=$post_count_plus['custom_titles'][$found];
	$color=$post_count_plus['custom_titles'][$found+4];
	if ($color) {$title="<span style='color:$color;'>$title</span>";}
}
return $title;
}

function wp_post_count_plus_find_title($user_id=0,$posts=0,$days=0,$role='') {	
global $post_count_plus, $post_count_plus_title_cache;
if (empty($user_id)) {global $comment; if (!empty($comment->user_id)) {$user_id=$comment->user_id;}}
if (!empty($user_id)) {
	if (isset($post_count_plus_title_cache[$user_id])) {return $post_count_plus_title_cache[$user_id];}		
	if (!$posts) {$posts=wp_post_count_plus_get_count($user_id);}
	$user = get_userdata($user_id);
	if (!$days) {$days=intval((time() - strtotime($user->user_registered.' GMT'))/86400);}						
	$capabilities=(isset($user->bb_capabilities)) ? $user->bb_capabilities : $user->capabilities;  // makes compatibile for 0.8.x - 1.0a
	if (!$role && !empty($capabilities)) {		
		$role=reset(array_keys($capabilities)); 
	}
}
if (empty($post_count_plus)) {$post_count_plus=get_option('post_count_plus');}
$found=0; $width=5; $rows=floor(count($post_count_plus['custom_titles'])/$width);
for ($i=1; $i<$rows; $i++) {
if ($post_count_plus['custom_titles'][$i*$width]) {
	$posts0=$post_count_plus['custom_titles'][$i*$width+1];
	$days0=$post_count_plus['custom_titles'][$i*$width+2];
	$role0=$post_count_plus['custom_titles'][$i*$width+3];		
	if ((!$posts0 || $posts>=$posts0) && (!$days0 || $days>=$days0) && (!$role0 || $role==$role0)) {$found=$i*$width;}
}	
} // we don't break out of the loop because we need to see if there is a higher value more appropriate - array needs to be in sorted order though

if (!empty($user_id)) {$post_count_plus_title_cache[$user_id]=$found;}	// cache for same page queries
return $found;
}

?>