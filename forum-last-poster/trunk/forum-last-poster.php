<?php
/*
Plugin Name: Forum Last Poster
Description:  Adds `forum_last_poster()`, `forum_time()`, `forum_last_post_link()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.
Plugin URI:  http://bbpress.org/plugins/topic/123
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.3
*/

function forum_last_poster($id=0) {topic_last_poster(forum_last_topic_id($id));}

function get_forum_last_poster($id=0) {return get_topic_last_poster(forum_last_topic_id($id));}

function forum_time($id=0) {topic_time(forum_last_topic_id($id));}

function get_forum_time($id=0) {return get_topic_time(forum_last_topic_id($id));}

function forum_last_post_link($id=0) {topic_last_post_link(forum_last_topic_id($id));}

function get_forum_last_post_link($id=0) {return get_topic_last_post_link(forum_last_topic_id($id));}

function forum_last_topic_id($id = 0) {
global $forums_last_topic_id, $forum, $bbdb, $bb_topic_cache; 
if (!$id) {$id=$forum->forum_id;}
if (!isset($forums_last_topic_id)) {	// unfortunately the join method does not handle deleted topics correctly and returns no results for that forum :-(
// $last_topics = $bbdb->get_results("SELECT t1.forum_id as forum_id, t1.topic_id as topic_id FROM $bbdb->topics AS t1 LEFT JOIN $bbdb->topics AS t2 ON t1.forum_id=t2.forum_id AND t1.topic_time < t2.topic_time WHERE t1.topic_status=0 AND t2.forum_id IS NULL;");
$last_topics = $bbdb->get_results("SELECT forum_id, topic_id FROM $bbdb->topics WHERE topic_last_post_id IN (SELECT MAX(topic_last_post_id) FROM $bbdb->topics WHERE topic_status=0 GROUP BY forum_id)"); 
foreach ($last_topics as $forum_last_topic_id) {
	if ($temp=$forum_last_topic_id->forum_id) {
		$forums_last_topic_id[$temp]=$forum_last_topic_id->topic_id;
		if (!isset($bb_topic_cache[$forum_last_topic_id->topic_id])) {$add_cache[]->topic_id=$forum_last_topic_id->topic_id;}
	}
} if (!empty($add_cache)) {bb_cache_post_topics($add_cache);}	// cache topics not already in cache
} return $forums_last_topic_id[$id];
}
?>