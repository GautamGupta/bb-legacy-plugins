<?php 
/*
Plugin Name: bbFreshness
Plugin URI: http://devt.caffeinatedbliss.com/bbpress/freshness
Description: Filters the topic lists to show only the freshest topics
Author: Paul Hawke
Author URI: http://paul.caffeinatedbliss.com/
Version: 0.1
*/

/******************************************************************
 *
 * FRESHNESS here is measured in DAYS.  Topics untouched for longer
 * than this amount of time will not be displayed as long as the
 * bbFreshness plugin is active.  For example, to define topic 
 * freshness as "touched within the last 90 days" set the value to
 * 90.
 *
 *****************************************************************/

define(FRESHNESS, 90);

/*****************************************************************
 *
 * No edits needed beyond this point.
 *
 *****************************************************************/

function freshness_init( ) {
	add_action('bb_front-page.php', 'freshness_topic_filter');
	add_action('bb_forum.php', 'freshness_topic_filter');
	add_action('bb_topic.php', 'freshness_topic_filter');
}

function freshness_topic_filter( $args ) {
	global $topics;
	
	$now = time();
	$filter = 60 * 60 * 24 * FRESHNESS;
	$mytopics = Array();
	for ($i=0; $i < count($topics); $i++) {
		$topic_timestamp = strtotime($topics[$i]->topic_time);
		if ($now - $topic_timestamp < $filter) {
			$mytopics[] = $topics[$i];
		}
	}
	
	$topics = $mytopics;
}

freshness_init();

?>