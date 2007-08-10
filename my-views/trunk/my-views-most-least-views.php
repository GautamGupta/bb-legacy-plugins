<?php
/*
Plugin Name: My Views module - Most/Least Views
Description: This plugin is part of the My Views plugin. It adds Most/Least Views to the list of views and forum view counts to the list of forums.
		To make the forum view count available, you must edit your front-page.php and forum.php templates.
Plugin URI:  http://CKon.wordpress.com
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.05
*/ 

//  if (function_exists('get_view_count')) :       // requires  bb-topic-views plugin   // this needs to check the db meta directly as the other plugin may not have loaded

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = ''; 
    	bb_register_view("most-views","Topics with the most views",$query);
    	bb_register_view("least-views","Topics with the least views",$query);

} else {		// Build 214-875	(0.8.2.1)

function most_views_filter($views ) {
	global $views;
	$views["most-views"] = "Topics with the most views";
	$views["least-views"] = "Topics with the least views";
	return $views;
}
add_filter('bb_views', 'most_views_filter');
}

function most_views( $view ) {
global $bbdb, $topics, $view_count;
if ($view=='most-views')  {$sort="DESC";}
if ($view=='least-views')  {$sort="ASC";}
if ($view=='least-views' || $view=='most-views')  {
$limit = bb_get_option('page_topics');
$where = apply_filters('get_latest_topics_where','');
$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) $sort LIMIT $limit");
foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} $ids = join(',', array_keys($trans));
$topics ="SELECT * FROM $bbdb->topics WHERE topic_status=0 AND topic_id IN ($ids) $where ORDER BY FIELD(topic_id, $ids)";
$topics = $bbdb->get_results($topics);
$view_count  = count($topics);
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