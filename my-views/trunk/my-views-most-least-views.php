<?php
/*
Plugin Name: My Views module - Most/Least Views
Description: This plugin is part of the My Views plugin. It adds Most/Least Views to the list of views and forum view counts to the list of forums. To make the forum view count available, you must edit your front-page.php and forum.php templates. REQUIRES bb-Topic-Views plugin.
Plugin URI:  http://bbpress.org/plugins/topic/my-views
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.4
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
	$where="WHERE topic_status=0 ";
	if (isset($_REQUEST['days'])) {$field="topic_start_time"; $days=$_REQUEST['days'];}
	elseif (isset($_REQUEST['days_started'])) {$field="topic_start_time"; $days=$_REQUEST['days_started'];}
	elseif (isset($_REQUEST['days_replied'])) {$field="topic_time"; $days=$_REQUEST['days_replied'];}
	if (!empty($field)) {$time=gmdate('Y-m-d H:00:00',time()-intval($days)*86400); $where.=" AND $field>'$time' ";}
	$where = apply_filters('get_latest_topics_where',$where);
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
	if (!is_front() && !is_forum()) {return $forums;}
	global $bbdb, $forums_views; 
	if (empty($forums_views)) {$forums_views=bb_get_option('forums_views');}
	if (empty($forums_views) || floor($forums_views[0]/300)!=floor(time()/300)) {
		if (empty($forums_views)) {$old_sum=0;} else {$old_sum=array_sum($forums_views);}
		if (defined('BACKPRESS_PATH')) {		
			$query="SELECT forum_id, SUM(meta_value) AS sum_meta_value 
			FROM $bbdb->meta LEFT JOIN $bbdb->topics ON $bbdb->meta.object_id = $bbdb->topics.topic_id  
			WHERE $bbdb->topics.topic_status=0 AND $bbdb->meta.meta_key='views'  AND $bbdb->meta.object_type='bb_topic'
			GROUP BY $bbdb->topics.forum_id";
		} else {
			$query="SELECT forum_id, SUM(meta_value) AS sum_meta_value 
			FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  
			WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views'  
			GROUP BY $bbdb->topics.forum_id";
		}
		$results = $bbdb->get_results($query);	 
		foreach ($results as $result) {$forums_views[$result->forum_id]=$result->sum_meta_value;}
		$new_sum=array_sum($forums_views);
		// if ($old_sum!=$new_sum) {	
			$forums_views[0]=time(); 
			if ($old_sum==0) {bb_update_option('forums_views',$forums_views);}
			else {
				$value=addslashes(serialize($forums_views)); 
				if (defined('BACKPRESS_PATH')) {						
					$query="UPDATE $bbdb->meta SET meta_value='$value' WHERE object_id='0' AND object_type='bb_option' AND meta_key='forums_views'  LIMIT 1";
				} else {
					$query="UPDATE $bbdb->topicmeta SET meta_value='$value' WHERE topic_id=0 AND meta_key='forums_views'  LIMIT 1";
				}
				@$bbdb->query($query);			
			}
		// }
	}	
	foreach ($forums as $id=>$forum) {if (!empty($forums_views[$id])) {$forums[$id]->views=$forums_views[$id];} else {$forums[$id]->views=0;}}
return $forums;
}
?>