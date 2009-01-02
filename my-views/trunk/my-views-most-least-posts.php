<?php
/*
Plugin Name: My Views module - Most/Least Posts
Description: This plugin is part of the My Views plugin. It adds Most/Least Posts to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.3
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = array('append_meta'=>false,'sticky'=>false);	// attempt to short-circuit bb_query 
    	bb_register_view("most-posts","Topics with the most posts",$query);
    	bb_register_view("least-posts","Topics with the least posts",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_most_posts_filter($passthrough) {
	global $views;
	$views["most-posts"] = "Topics with the most posts";
	$views["least-posts"] = "Topics with the least posts";
	return $passthrough;
}
add_filter('bb_views', 'my_views_most_posts_filter');
}

function most_posts( $view ) {
global $bbdb, $topics, $view_count, $page;
if ($view=='most-posts')  {$sort="DESC";}
if ($view=='least-posts')  {$sort="ASC";}
if ($view=='least-posts' || $view=='most-posts')  {
	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 ");
	$query = " FROM $bbdb->topics $where ";
	$restrict = " ORDER BY cast(topic_posts as UNSIGNED) $sort LIMIT $limit OFFSET $offset";

	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	 //  bb_count_last_query();  // count($topics);		
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	$topics = bb_append_meta( $topics, 'topic' );	
}
// else {do_action( 'bb_custom_view', $view );}
}
add_action( 'bb_custom_view', 'most_posts' );

?>