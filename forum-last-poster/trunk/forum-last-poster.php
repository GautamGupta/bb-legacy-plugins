<?php
/*
Plugin Name: Forum Last Poster
Description:  Adds `forum_last_poster()`, `forum_time()`, `forum_last_post_link()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.
Plugin URI:  http://bbpress.org/plugins/topic/forum-last-poster
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.4
*/

function forum_last_poster($id=0) {topic_last_poster(forum_last_topic_id($id));}

function get_forum_last_poster($id=0) {return get_topic_last_poster(forum_last_topic_id($id));}

function forum_time($id=0) {topic_time(forum_last_topic_id($id));}

function get_forum_time($id=0) {return get_topic_time(forum_last_topic_id($id));}

function forum_last_post_link($id=0) {topic_last_post_link(forum_last_topic_id($id));}

function get_forum_last_post_link($id=0) {return get_topic_last_post_link(forum_last_topic_id($id));}

add_action( 'bb_new_post', 'forum_last_poster_update');
add_action( 'bb_delete_post','forum_last_poster_update');

function forum_last_poster_update($post_id=0) {
	global $bbdb;
	$query="SELECT forum_id, topic_id FROM $bbdb->topics 
		WHERE topic_last_post_id IN (SELECT MAX(topic_last_post_id) FROM $bbdb->topics WHERE topic_status=0 GROUP BY forum_id)";
	$last_topics = $bbdb->get_results($query); 
	foreach ($last_topics as $last_topic) {$forum_last_poster[$last_topic->forum_id]=$last_topic->topic_id;}
	bb_update_option('forum_last_poster',$forum_last_poster);
	return $forum_last_poster;
}

function forum_last_topic_id($id = 0) {
global $forums_last_topic_id, $forum, $bb_topic_cache, $wp_object_cache; 
if (!$id) {$id=$forum->forum_id;}
if (isset($forums_last_topic_id)) {return $forums_last_topic_id[$id];}
$last_topics=bb_get_option('forum_last_poster');
if (empty($last_topics)) {$last_topics=forum_last_poster_update();}
foreach ($last_topics as $temp=>$id) {
	$forums_last_topic_id[$temp]=$id;
	if (!isset($bb_topic_cache[$id]) && !isset($wp_object_cache->cache['bb_topic'][$id])) {$add_cache[]->topic_id=$id;}
} 
if (!empty($add_cache)) {bb_cache_post_topics($add_cache);}	// cache topics not already in cache
return $forums_last_topic_id[$id];
}

?>