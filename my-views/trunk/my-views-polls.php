<?php
/*
Plugin Name: My Views module - Polls
Description: This plugin is part of the My Views plugin. It adds Topics with Polls to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/my-views
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.3
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = array('append_meta'=>false,'sticky'=>false);	// attempt to short-circuit bb_query 
    	bb_register_view("polls","Topics with polls",$query);
    	bb_register_view("polls","Topics with polls",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_polls_filter($passthrough) {
	global $views;
	$views["polls"] = "Topics with polls";
	$views["polls"] = "Topics with polls";
	return $passthrough;
}
add_filter('bb_views', 'my_views_polls_filter');

}

add_action( 'bb_custom_view', 'my_views_polls' );

function my_views_polls( $view ) {
global $bbdb, $topics, $view_count, $page;
if ($view=='polls')  {
	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 ");
	if (defined('BACKPRESS_PATH')) {		
		$query = " FROM $bbdb->topics AS t1 LEFT JOIN $bbdb->meta as t2 ON t1.topic_id=t2.object_id 
			$where AND object_type='bb_topic'  AND meta_key='poll_options' ";	
	} else {		
		$query = " FROM $bbdb->topics AS t1 LEFT JOIN $bbdb->topicmeta as t2 ON t1.topic_id=t2.topic_id 
			$where AND meta_key='poll_options' ";
	}
	$restrict = " ORDER BY topic_time DESC LIMIT $limit OFFSET $offset";

	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	$topics = bb_append_meta( $topics, 'topic' );	
}
}
?>