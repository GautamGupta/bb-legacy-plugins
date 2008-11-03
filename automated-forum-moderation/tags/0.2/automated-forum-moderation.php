<?php
/*
Plugin Name: Automated Forum Moderation
Description: Blocks common (and sometimes accidental) human-generated spam automatically.
Plugin URI: http://llamaslayers.net/daily-llama/tag/automated-forum-moderation/
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Version: 0.2
*/

$automated_forum_moderation_data = array(
	'max_days' => 30, // Maximum time since the last post in a topic to allow posting
	'allow_double_post' => false, // Allow posting twice (false, true, or an array of forum IDs to allow it in)
	'allow_double_post_after' => 60, //the number of minutes since the previous post by that user (if the user is the last poster on a topic) that a new post can be made.  false if not applicable
	'min_words' => 2, // Minimum words in a post
	'min_chars' => 5 // Minimum characters in a post
);

########################################
# Most users should stop editing here. #
########################################

if (!$automated_forum_moderation_data['allow_double_post_after'])
	$automated_forum_moderation_data['allow_double_post_after'] = 2147483647; // Huge number (about 4000 years) used to prevent extra code bloat.

function automated_forum_moderation_initial_blocking($retvalue, $capability, $args) {
	global $bb_post, $automated_forum_moderation_data;
	if ($capability == "write_post") {
		if ((!$automated_forum_moderation_data['allow_double_post'] && $bb_post->poster_id === bb_get_current_user_info('id') && ((bb_current_time('timestamp') - bb_gmtstrtotime($bb_post->post_time))/60) < $automated_forum_moderation_data['allow_double_post_after']) || // Double posting is not allowed
			(is_array($automated_forum_moderation_data['allow_double_post']) && (!in_array($bb_post->forum_id, $automated_forum_moderation_data['allow_double_post']) || ((bb_current_time('timestamp') - bb_gmtstrtotime($bb_post->post_time))/60) < $automated_forum_moderation_data['allow_double_post_after']) && $bb_post->poster_id === bb_get_current_user_info('id')) || // Double posting is allowed in certain forums
			intval((bb_current_time('timestamp') - bb_gmtstrtotime($bb_post->post_time))/86400) > $automated_forum_moderation_data['max_days']) // Topic is old
				return false;
	}
	return $retvalue;
}

function automated_forum_moderation_message() {
	global $bb_post, $automated_forum_moderation_data;
	if ($bb_post->poster_id === bb_get_current_user_info('id')) { ?>
	<p>The last post on this topic is your own. Please wait until someone else replies to post again.</p>
<?php } elseif (intval((bb_current_time('timestamp') - bb_gmtstrtotime($bb_post->post_time))/86400) > 30) { ?>
	<p>This topic is old. It has been automatically closed to new replies.</p>
<?php }
}

function automated_forum_moderation_jit_blocking($post_text, $post_id, $topic_id) { // JIT = Just In Time
	global $bbdb, $automated_forum_moderation_data;
	$last_post_in_topic = $bbdb->get_row($bbdb->prepare('SELECT `post_id`, `poster_id`, `post_time`, `forum_id` FROM `'.$bbdb->posts.'` WHERE `topic_id` = %s AND `post_status` = \'0\' ORDER BY `post_time` DESC, `post_position` ASC LIMIT 1', $topic_id)); // Only get what we need
	if ($last_post_in_topic !== null) {
		$is_new_topic = false;
		$last_post_time = $last_post_in_topic->post_time;
		$last_post_is_current_user = ($last_post_in_topic->poster_id == bb_get_current_user_info('ID'));
		$forum_id = $last_post_in_topic->forum_id;
		if ($last_post_is_current_user && $automated_forum_moderation_data['allow_double_post'] !== true && $automated_forum_moderation_data['allow_double_post_after'] > ((bb_current_time('timestamp') - bb_gmtstrtotime($last_post_time))/60)) {
			if (!$automated_forum_moderation_data['allow_double_post']) {
				bb_die('The last post on this topic is your own. Please wait until someone else replies to post on this topic again.');
			} elseif (!in_array($forum_id, $automated_forum_moderation_data['allow_double_post'])) {
				bb_die('The last post on this topic is your own. Please wait until someone else replies to post on this topic again.');
			}
		}
		if (intval((bb_current_time('timestamp') - bb_gmtstrtotime($last_post_time))/86400) > $automated_forum_moderation_data['max_days']) {
			bb_die('This topic is old. It has been automatically closed to new replies.');
		}
		if (count(explode(' ', $post_text)) < $automated_forum_moderation_data['min_words'] || strlen($post_text) < $automated_forum_moderation_data['min_chars']) {
			bb_die(__('You need to actually submit some content!'));
		}
	} elseif (count(explode(' ', $post_text)) < $automated_forum_moderation_data['min_words'] || strlen($post_text) < $automated_forum_moderation_data['min_chars']) {
		/*error_reporting(E_ERROR); // Hack to remove php error caused by no posts being in the topic yet.
		bb_delete_topic($topic_id, 1);*/ // We want ro really delete the topic since it has no reason to be in the database (it has no posts and none can be added)
		$bbdb->query($bbdb->prepare('DELETE FROM `'.$bbdb->topics.'` WHERE `topic_id` = %s LIMIT 1', $topic_id));
		bb_die(__('You need to actually submit some content!'));
	}
	return $post_text;
}

if (is_topic()) {
	add_filter('bb_current_user_can', 'automated_forum_moderation_initial_blocking', 10, 3);
	add_action('pre_post_form', 'automated_forum_moderation_message');
}
add_filter('pre_post', 'automated_forum_moderation_jit_blocking', 10, 3);

?>