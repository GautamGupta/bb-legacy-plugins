<?php
/*
Plugin Name: Topic Voices
Plugin URI: http://bbpress.org/plugins/topic/topic-voices
Description: Displays how many unique users have posted in a topic (aka "voices"). Gives bbPress 0.9 the same ability as 1.0
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1
*/

add_action( 'topic_voices', 'bb_topic_voices');
add_action( 'bb_new_post', 'bb_topic_voices_update_post' );
add_action( 'bb_delete_post', 'bb_topic_voices_update_post' );
add_action( 'bb_recount_list', 'bb_topic_voices_recount_list');

function bb_topic_voices( $topic_id = 0 ) {
	echo apply_filters( 'bb_topic_voices', bb_get_topic_voices( $topic_id ), get_topic_id( $topic_id ) );
}

function bb_get_topic_voices( $topic_id = 0 ) {	
	$topic = get_topic( get_topic_id( $topic_id ) );	
	$topic_id = intval($topic->topic_id);
	if ( empty( $topic->voices_count ) ) { $voices = bb_topic_voices_update($topic_id); } else { $voices = $topic->voices_count; }
	return apply_filters( 'bb_get_topic_voices', $voices, $topic_id );
}

function bb_topic_voices_update( $topic_id = 0 ) {
	$topic_id = intval($topic_id);
	global $bbdb;	
	if ($topic_id && $voices = $bbdb->get_var("SELECT count(DISTINCT poster_id) FROM $bbdb->posts WHERE topic_id = '$topic_id' AND post_status = '0'") ) {		
		bb_update_topicmeta( $topic_id, 'voices_count', $voices );
		return $voices;
	}
	return 0;	
}

function bb_topic_voices_update_post($post_id=0) {
	$post = bb_get_post( $post_id );
	if (!empty($post->topic_id)) {bb_topic_voices_update($post->topic_id);}
}

function bb_topic_voices_recount_list() {
	global $recount_list; 
	$recount_list[6] = array('topic-voices', __('Count voices of every topic'),'bb_topic_voices_recount');
}	

function bb_topic_voices_recount() {
	if ( isset($_POST['topic-voices']) && 1 == $_POST['topic-voices'] && bb_current_user_can('administrate')) {		
		echo "\t<li>\n";
		echo "\t\t" . __('Counting voices...');
		global $bbdb;		
		$query="SELECT 'voices_count', topic_id, count(DISTINCT poster_id) as meta_value  FROM $bbdb->posts WHERE post_status = '0' GROUP BY topic_id";
		$status1=$bbdb->query("DELETE FROM $bbdb->topicmeta  WHERE meta_key = 'voices_count'");  	 // empty db table first
		$status2=$bbdb->query("INSERT INTO $bbdb->topicmeta  (meta_key, topic_id, meta_value) $query");  	 // performed 100% inside of mysql at maximum speed
		echo "\t\t ($status1:$status2)<br />\n";
		echo "\t\t" . __('Done counting voices.');		
		echo "\n\t</li>\n";		
	}
}

?>