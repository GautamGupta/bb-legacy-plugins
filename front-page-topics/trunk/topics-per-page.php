<?php
/*
Plugin Name: Topics Per Page
Plugin URI: http://bbpress.org/plugins/topic/topics-per-page
Description:  Set custom topic or post count limits for nearly every kind of bbPress page while still calculating direct post links correctly.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.4

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

add_filter( 'bb_get_option_page_topics', 'topics_per_page',200);
add_filter( 'get_post_link','topics_per_page_fix_link',10, 2);
add_filter( 'get_topic_page_links_per_page', 'topics_per_page_fix_topic_links');
add_filter( 'bb_get_option_front_page_topics', 'front_page_topics_fetch');	 // backward-compatibility fix for old front page topic plugin
add_filter('get_latest_topics_limit','front_page_pagination',999);

function front_page_topics_fetch($limit) {global $topics_per_page;  return $topics_per_page;}

function topics_per_page($limit) {	 	// set custom topics per page limits
global $topics_per_page, $topics_per_page_fix_link, $topics, $topic, $topic_id; 
if ($topics_per_page_fix_link) {$location="topic-page";} else {$location=bb_get_location();} 
if (isset($topics_per_page[$location])) {return $topics_per_page[$location];}
return $limit;
}

function topics_per_page_fix_topic_links($limit) {	// er, thanks Sam, I think - 1.0a2 fix
global $topics_per_page;  return isset($topics_per_page['topic-page']) ? $topics_per_page['topic-page'] : $limit;
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

function front_page_pagination($limit="") {
global $page; 
if (is_front() && $page>1) {$where.=" OFFSET ".($page-1)*bb_get_option('page_topics');}
return $limit;
}

function front_page_pages() {
global $page, $bbdb; 
echo get_page_number_links( $page, $bbdb->get_var("SELECT SUM(topics) FROM $bbdb->forums")); 
}

?>