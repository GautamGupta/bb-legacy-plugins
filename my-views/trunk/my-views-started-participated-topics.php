<?php
/*
Plugin Name: My Views module - Started/Participated Topics
Description: This plugin is part of the My Views plugin. It adds Started/Participated Topic Views to the list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.08
*/ 

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk

function my_views_add_started_participated_topics() {
	if (bb_is_user_logged_in()) {
		$query = '';
    		bb_register_view("my-topics","Topics I've Started",$query);
    		bb_register_view("my-posts","Topics I've Participated In",$query);
    		bb_register_view("latest-discussions","Latest Discussions",$query);
    	}
}    	
add_action('bb_init', 'my_views_add_started_participated_topics');

} else {		// Build 214-875	(0.8.2.1)

function my_views_filter( $passthrough ) {
	if (bb_is_user_logged_in()) {
		global $views;
		$views['my-topics'] = "Topics I've Started";
		$views['my-posts'] = "Topics I've Participated In";
		$views['latest-discussions'] = "Latest Discussions";
	}
	return $passthrough;
}
add_filter('bb_views', 'my_views_filter');
}

function my_views_action( $view ) {
global $bbdb, $topics, $view_count; $user_id=bb_get_current_user_info( 'id' );
if ($user_id) {
if ($view=='my-topics')  {$topics=get_recent_user_threads($user_id); $view_count  = count($topics);}
if ($view=='my-posts')   {
$posts=get_recent_user_replies($user_id);   $topics=""; 
foreach ($posts as $post) {$topics[]=get_topic($post->topic_id );}
$topics=bb_append_meta( $topics, 'topic' );
$view_count  = count($topics);}	
}
if ($view=='latest-discussions')  {$topics= get_latest_topics(); $view_count  = count($topics);}
}
add_action( 'bb_custom_view', 'my_views_action' );

?>