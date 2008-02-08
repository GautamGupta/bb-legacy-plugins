<?php
/*
Plugin Name: My Views module - Most/Least Views
Description: This plugin is part of the My Views plugin. It adds Most/Least Views to the list of views and forum view counts to the list of forums.
		To make the forum view count available, you must edit your front-page.php and forum.php templates.
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.09
*/ 

//  if (function_exists('get_view_count')) :       // requires  bb-topic-views plugin   // this needs to check the db meta directly as the other plugin may not have loaded

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = ''; 
    	bb_register_view("most-views","Topics with the most views",$query);
    	bb_register_view("least-views","Topics with the least views",$query);

} else {		// Build 214-875	(0.8.2.1)

function most_views_filter($passthrough) {
	global $views;
	$views["most-views"] = "Topics with the most views";
	$views["least-views"] = "Topics with the least views";
	return $passthrough;
}
add_filter('bb_views', 'most_views_filter');
}

function most_views( $view ) {
global $bbdb, $topics, $view_count, $page;
if ($view=='most-views')  {$sort="DESC";}
if ($view=='least-views')  {$sort="ASC";}
if ($view=='least-views' || $view=='most-views')  {
	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;
	$where = apply_filters('get_latest_topics_where','');
	// limit *9 is a lazy work around to avoid a join, as topic_static=0 in next query filters out deleted
	$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) $sort LIMIT ".$limit*9);
	foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} 
	unset($most_views); 	 // huge query, release memory
	$ids = join(',', array_keys($trans));			// this eventually needs to be enhanced to filter/split the array for pagination - could get HUGE
	
	$query ="FROM $bbdb->topics WHERE topic_status=0 AND topic_id IN ($ids) $where ";
	$restrict="ORDER BY FIELD(topic_id, $ids) LIMIT $limit OFFSET $offset";
	
	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	 //  bb_count_last_query();  // count($topics);		
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	$topics = bb_append_meta( $topics, 'topic' );	
}
// else {do_action( 'bb_custom_view', $view );}
}
add_action( 'bb_custom_view', 'most_views' );

function forums_views_append($forums) {
if (is_front() || is_forum()) {
global $bbdb, $forums_views; $sum_meta_value="SUM(meta_value)";
if (!isset($forums_views)) {
$forums_views = $bbdb->get_results(" SELECT $sum_meta_value,forum_id FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views'  GROUP BY $bbdb->topics.forum_id");
} foreach ($forums_views as $forum_views) {
// echo " <!-- ".$forum_views->forum_id." - ".$sum_meta_value." -->";  
if ($forum_views->forum_id) {$forums[$forum_views->forum_id]->views=$forum_views->$sum_meta_value;} 
}
}
return $forums;
}
add_filter('get_forums','forums_views_append');

// endif;
?>