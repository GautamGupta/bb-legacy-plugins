<?php
/*
Plugin Name: My Views module - Most/Least Views
Description: This plugin is part of the My Views plugin. It adds Most/Least Views to the list of views and forum view counts to the list of forums. To make the forum view count available, you must edit your front-page.php and forum.php templates. REQUIRES bb-Topic-Views plugin.
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.3
*/ 

// NOTICE: requires  bb-topic-views plugin  

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = array('append_meta'=>false,'sticky'=>false);	// attempt to short-circuit bb_query 
    	bb_register_view("most-views","Topics with the most views",$query);
    	bb_register_view("least-views","Topics with the least views",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_most_views_filter($passthrough) {
	global $views;
	$views["most-views"] = "Topics with the most views";
	$views["least-views"] = "Topics with the least views";
	return $passthrough;
}
add_filter('bb_views', 'my_views_most_views_filter');
}

add_action( 'bb_custom_view', 'most_views' );
add_filter('get_forums','forums_views_append');

function most_views( $view ) {
global $bbdb, $topics, $view_count, $page;
if ($view=='most-views')  {$sort="DESC";}
if ($view=='least-views')  {$sort="ASC";}
if ($view=='least-views' || $view=='most-views')  {
	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 ");
	if (defined('BACKPRESS_PATH')) {		
		$query = " FROM $bbdb->topics AS t1 LEFT JOIN $bbdb->meta as t2 ON t1.topic_id=t2.object_id 
			$where AND object_type='bb_topic'  AND meta_key='views' ";	
	} else {		
		$query = " FROM $bbdb->topics AS t1 LEFT JOIN $bbdb->topicmeta as t2 ON t1.topic_id=t2.topic_id 
			$where AND meta_key='views' ";
	}
	$restrict=" ORDER BY CAST(meta_value as UNSIGNED) $sort LIMIT $limit OFFSET $offset";
	
	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	$topics = bb_append_meta( $topics, 'topic' );	
}
}

function forums_views_append($forums) {
if (is_front() || is_forum()) {
global $bbdb, $forums_views; $sum_meta_value="SUM(meta_value)";
if (!isset($forums_views)) {
$forums_views = $bbdb->get_results("SELECT DISTINCT forum_id, $sum_meta_value FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views'  GROUP BY $bbdb->topics.forum_id");
} foreach ($forums_views as $forum_views) {
if ($forum_views->forum_id) {$forums[$forum_views->forum_id]->views=$forum_views->$sum_meta_value;} 
}
}
return $forums;
}
?>