<?php
/*
Plugin Name: My Views module - Started/Participated Topics
Description: This plugin is part of the My Views plugin. It adds Started/Participated Topic Views to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.091
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk

function my_views_add_started_participated_topics() {
	$query = '';
	bb_register_view("latest-discussions","Latest Discussions",$query);
	if (bb_is_user_logged_in()) {
    		bb_register_view("my-topics","Topics I've Started",$query);
    		bb_register_view("my-posts","Topics I've Participated In",$query);
    	}
}    	
add_action('bb_init', 'my_views_add_started_participated_topics');

} else {		// Build 214-875	(0.8.2.1)

function my_views_filter( $passthrough ) {
	global $views;
	$views['latest-discussions'] = "Latest Discussions";
	if (bb_is_user_logged_in()) {
		$views['my-topics'] = "Topics I've Started";
		$views['my-posts'] = "Topics I've Participated In";		
	}
	return $passthrough;
}
add_filter('bb_views', 'my_views_filter');
}

function my_views_action( $view ) {
global $bbdb, $topics, $view_count, $page; 
$user_id=bb_get_current_user_info( 'id' );

if ($view=='latest-discussions' || ($user_id && ($view=='my-topics' || $view=='my-posts')))  {
	// $topics=get_recent_user_threads($user_id);  $view_count  = count($topics);
	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;
	$sort=" DESC ";
	$where = apply_filters('get_latest_topics_where','');
	if ($user_id) {
		if ($view=='my-topics') {
		$where = $where." AND topic_poster=$user_id ";		
		}
		elseif ($view=='my-posts') {
		
		// limit *9 is a lazy work around to avoid a join, as topic_static=0 in next query filters out deleted
		$my_posts = $bbdb->get_results("SELECT topic_id FROM $bbdb->posts WHERE post_status=0 AND poster_id=$user_id ORDER BY cast(post_id as UNSIGNED) DESC LIMIT ".$limit*9);
		foreach (array_keys($my_posts) as $i) {$trans[$my_posts[$i]->topic_id] =& $my_posts[$i];} 
		unset($my_posts); 	 // huge query, release memory
		$ids = join(',', array_keys($trans));			// this eventually needs to be enhanced to filter/split the array for pagination - could get HUGE
		$where = $where." AND topic_id IN ($ids) ";			
		}
	}
	$query = " FROM $bbdb->topics WHERE topic_status=0  $where ";
	$restrict = " ORDER BY cast(topic_last_post_id as UNSIGNED) $sort LIMIT $limit OFFSET $offset";	// topic_last_post_id is lazy/faster way to sort by newest

	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	 //  bb_count_last_query();  // count($topics);			
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	$topics = bb_append_meta( $topics, 'topic' );	
}

}
add_action( 'bb_custom_view', 'my_views_action' );

?>