<?php
/*
Plugin Name: My Posts module - Most/Least Posts
Description: This plugin is part of the My Posts plugin. It adds Most/Least Posts to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.081
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = ''; 
    	bb_register_view("most-posts","Topics with the most posts",$query);
    	bb_register_view("least-posts","Topics with the least posts",$query);

} else {		// Build 214-875	(0.8.2.1)

function most_posts_filter($passthrough) {
	global $views;
	$views["most-posts"] = "Topics with the most posts";
	$views["least-posts"] = "Topics with the least posts";
	return $passthrough;
}
add_filter('bb_views', 'most_posts_filter');
}

function most_posts( $view ) {
global $bbdb, $topics, $view_count;
if ($view=='most-posts')  {$sort="DESC";}
if ($view=='least-posts')  {$sort="ASC";}
if ($view=='least-posts' || $view=='most-posts')  {
$limit = bb_get_option('page_topics');
$where = apply_filters('get_latest_topics_where','');
$topics ="SELECT * FROM $bbdb->topics WHERE topic_status=0  $where ORDER BY cast(topic_posts as UNSIGNED) $sort LIMIT $limit";
$topics = $bbdb->get_results($topics);
$view_count  = count($topics);
$topics = bb_append_meta( $topics, 'topic' );	
}
// else {do_action( 'bb_custom_view', $view );}
}
add_action( 'bb_custom_view', 'most_posts' );

?>