<?php
/*
Plugin Name: Topics Per Page
Plugin URI: http://bbpress.org/plugins/topic/topics-per-page
Description:  Set custom topic or post count limits for nearly every kind of bbPress page while still calculating direct post links correctly.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$topics_per_page=array(		// edit the numbers below as desired - no fancy admin page for maximum performance
			'front-page' 	     => 15  ,
			'forum-page'	     => 20  ,	// stickies are added to these numbers, not included, because of bbPress design
			'topic-page' 	     => 10  ,	// this is actually number of posts on a topic page
			'view-page'  	     => 20  ,
			'tag-page' 	     => 20  ,	
			'search-page'     => 20  ,
			'feed-page'  	     => 10  ,	// some versions of bbPress do not obey feed limits, sorry	
			'profile-page'      => 10  ,		
			'favorites-page' => 10  ,	
			'stats-page'  	     => 10 	
);

/*   stop editing here   */

add_action( 'bb_get_option_page_topics', 'topics_per_page',200);
add_filter( 'get_post_link','topics_per_page_fix_link',10, 2);

function topics_per_page($limit) {	 	// set custom topics per page limits
global $topics_per_page, $topics_per_page_fix_link, $topics, $topic; 
if ($topics_per_page_fix_link) {$location="topic-page";} 
else {$location=bb_get_location(); if (($location!="topic-page") && !empty($topics) && isset($topic) && !empty($topic->topic_id) && isset($topics[$topic->topic_id])) {$location="topic-page";}} 
if (isset($topics_per_page[$location])) {return $topics_per_page[$location];}
return $limit;
}

function topics_per_page_fix_link($link,$post_id) { 		// required to calculate correct post/page jumps into topic pages
global $topic, $bb_topic_cache, $wp_object_cache, $topics_per_page_fix_link;
$topic_id=0; $topics_per_page_fix_link=true;
if ($topic && $topic->topic_last_post_id==$post_id) {
	$topic_id=$topic->topic_id;
	$page=get_page_number( $topic->topic_posts );
} elseif ( isset( $bb_topic_cache ) || isset($wp_object_cache->cache['bb_topic']) ) {		
	if (!isset( $bb_topic_cache)) {$cache=& $wp_object_cache->cache['bb_topic'];}	// 1.0 compatibility workaround
	else {$cache=& $bb_topic_cache;}
	foreach ($cache as $test) {
		if ($test->topic_last_post_id==$post_id) {$topic_id=$test->topic_id; $page=get_page_number( $test->topic_posts ); break;}
	}
}
if (!$topic_id) {	// all other cache lookup attempts have failed, do a manual lookup
	$bb_post = bb_get_post( get_post_id( $post_id ) );
	$topic_id=$bb_post->topic_id;
	$page = get_page_number( $bb_post->post_position );
}
$topic_link=get_topic_link( $topic_id, $page )."#post-$post_id";
$topics_per_page_fix_link=false; 
return  $topic_link;
}

?>