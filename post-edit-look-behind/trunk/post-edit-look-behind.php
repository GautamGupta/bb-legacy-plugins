<?php
/*
Plugin Name: Post Edit Look Behind
Description: When editing a post, this allows you to see some of the previous posts in the topic. No template edits required.
Plugin URI: http://bbpress.org/plugins/topic/post-edit-look-behind
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

add_action('post_edit_form','post_edit_look_behind',999);

function post_edit_look_behind() { 
$limit=3;  // number of posts to show
global $posts,$post_id, $bb_post, $topic, $bbdb; if (empty($post_id) || empty($topic)) {return;} 
if ($posts=$bbdb->get_results("SELECT * FROM $bbdb->posts WHERE topic_id=$topic->topic_id AND post_id<$post_id ".apply_filters('get_thread_where', 'AND post_status = 0')." ORDER BY post_time DESC LIMIT $limit")) {	
	if (defined('BACKPRESS_PATH')) {$bb10=true;} else {global $bb_post_cache; $bb10=false;}
	foreach ($posts as $bb_post) {
		$ids[$bb_post->poster_id]=$bb_post->poster_id; 	
		if ($bb10) {wp_cache_set($bb_post->post_id, $bb_post, 'bb_post' );} 
		else {$bb_post_cache[$bb_post->post_id]=$bb_post;}
	}
	bb_cache_users($ids);
	echo '<div id="topics"><ol id="thread">'; 
	foreach ($posts as $bb_post) {echo '<li id="post-'.$bb_post->post_id.'"'.get_alt_class('post').'>'; bb_post_template(); echo '</li>';} 
	echo '</ol></div>';
}
}
?>