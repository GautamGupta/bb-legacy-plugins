<?php
/*
Plugin Name: Hidden Forums Tag Filter
Description:  experimental optional add-on for Hidden Forums to hide tags that belong to Hidden Forums
Plugin URI:  http://bbpress.org/plugins/topic/105
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.6
*/

add_filter( 'sort_tag_heat_map', 'hidden_forums_filter_tags',1);

function hidden_forums_filter_tags(&$tags) {
global $bbdb,$hidden_forums_list; 
if (empty($hidden_forums_list)) {return;}

if (defined('BACKPRESS_PATH')) { 	// bbpress 1.0
	$query="SELECT name,count FROM $bbdb->terms AS t 
		INNER JOIN $bbdb->term_taxonomy AS tt ON t.term_id = tt.term_id 
		INNER JOIN $bbdb->term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN $bbdb->topics as tp ON object_id=topic_id
		WHERE tt.taxonomy='bb_topic_tag'  AND tt.count > 0 AND forum_id IN($hidden_forums_list)";
} else {  	// bbpress 0.9
	$query="SELECT tag as name,t.tag_count as count FROM $bbdb->tags as t
		INNER JOIN $bbdb->tagged as tt ON t.tag_id=tt.tag_id
		INNER JOIN $bbdb->topics as tp ON tp.topic_id=tt.topic_id
		WHERE t.tag_count> 0 AND forum_id IN($hidden_forums_list)";
}

$results=$bbdb->get_results($query); if (empty($results) || !is_array($results)) {return;}
foreach ($results as $result) {$tags[$result->name]-=$result->count; if ($tags[$result->name]<1) {unset($tags[$result->name]);}}
}


?>