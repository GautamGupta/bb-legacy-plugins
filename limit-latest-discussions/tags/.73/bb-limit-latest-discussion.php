<?php

/*
Plugin Name: Limit Latest Discussion
Plugin URI: http://faq.rayd.org/bbpress_limit_recent_activity/
Description: Allows a inactivity limit to be set on "Latest Discussion" (default of 7 days) so that only threads active within that limit are shown.
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: 0.73
*/


function get_where_plugin() {

	$forum = (int) get_forum_id();
	$where = 'WHERE topic_status = 0';
	if ( $forum )
		$where .= " AND forum_id = $forum ";
	if ( !empty( $exclude ) )
		$where .= " AND forum_id NOT IN ('$exclude') ";
	if ( is_front() ) 
		$where .= " AND topic_sticky <> 2 AND SUBDATE(NOW(), INTERVAL 7 DAY) < topic_time ";
	elseif ( is_forum() || is_view() )
		$where .= " AND topic_sticky = 0 ";

	
	return $where; 

}

add_filter ( 'get_latest_topics_where', 'get_where_plugin' );

?>