<?php
/*
Plugin Name: Post Edit Look Behind
Description: When editing a post, this allows you to see some of the previous posts in the topic. No template edits required.
Plugin URI: http://bbpress.org/plugins/topic/post-edit-look-behind
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

add_action('post_edit_form','post_edit_look_behind',999);

function post_edit_look_behind() {
global $post_id, $bb_post, $topic, $bbdb; if (empty($post_id) || empty($topic)) {return;}
if ($posts=$bbdb->get_results("SELECT * FROM $bbdb->posts WHERE topic_id=$topic->topic_id AND post_id<$post_id ".apply_filters('get_thread_where', 'AND post_status = 0')." ORDER BY post_time DESC LIMIT 3")) {
	echo '<div id="topics"><ol id="thread">'; 
	foreach ($posts as $bb_post) {echo '<li id="post-'.$bb_post->post_id.'"'.get_alt_class('post').'>'; bb_post_template(); echo '</li>';} 
	echo '</ol></div>';
}
}
?>