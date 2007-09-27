<?php
/*
Plugin Name: Unread Topics
Description: Indicates unread topics based on read posts, not login time.
Author: Henry Baldursson
Author URI: http://andrymi.com/community/
Version: 0.4
*/

$utplugin_db_version = "1.0";
bb_register_activation_hook("bb-unread-topics.php", "utplugin_install");

if (is_front() || is_forum() || is_tags()) {
	add_filter('topic_title', 'utplugin_show_unread');
	add_filter('topic_link', 'utplugin_link_latest');
}
add_filter('post_text', 'utplugin_update_log'); // Don't modify it, just use this hook to update our utplugin_log table.


function utplugin_install() {
	global $bbdb, $bb_table_prefix;
	$table_name = $bb_table_prefix . "utplugin_log";
	if ($bbdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			readtopic_id BIGINT (20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT (20) UNSIGNED NOT NULL,
			topic_id BIGINT (20) NOT NULL,
			post_read BIGINT (20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (readtopic_id)
			);";
		require_once(BBPATH . 'bb-admin/upgrade-functions.php');
		bb_dbDelta($sql);

		bb_update_option("utplugin_db_version", $utplugin_db_version);
	}
}
function utplugin_is_topic_unread($topic, $last_read_in_topic) {

	$last_post_in_topic = $topic->topic_last_post_id;
	if (!$last_read_in_topic) {
		return false; // This could be true when we support marking all threads as read. :-)
	}
	
	if ($last_post_in_topic > $last_read_in_topic) {
		return true;
	}
	return false;
}
function utplugin_show_unread($title)
{
	global $topic, $bb_current_user, $bbdb, $bb_table_prefix;
	if (bb_is_user_logged_in() && $topic)
	{
		$user = bb_get_user($bb_current_user->ID);
		$last_read_in_topic = $bbdb->get_var("SELECT post_read FROM ".$bb_table_prefix."utplugin_log WHERE user_id = $user->ID AND topic_id = $topic->topic_id");
		if ( utplugin_is_topic_unread($topic, $last_read_in_topic) || !$last_read_in_topic )
		{
			$title = '<strong>' . $title . '</strong>';
		}
	}
	return($title);
}
function utplugin_link_latest($link, $id = 0)
{
	global $bbdb, $bb_current_user, $bb_table_prefix;

	if (bb_is_user_logged_in())
	{
		$user = bb_get_user($bb_current_user->ID);
		$id = get_topic_id($id);
		$topic = get_topic($id);
		$last_read_in_topic = $bbdb->get_var("SELECT post_read FROM ".$bb_table_prefix."utplugin_log WHERE user_id = $user->ID AND topic_id = $topic->topic_id");
		if ( utplugin_is_topic_unread($topic, $last_read_in_topic) )
		{
			$lastpost = $bbdb->get_var("SELECT post_read FROM ".$bb_table_prefix."utplugin_log WHERE user_id = $user->ID AND topic_id = $topic->topic_id");
			// find $nextpost, that is the next post made in topic after $lastpost.
			$nextpost = $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE topic_id = $topic->topic_id AND post_id > $lastpost ORDER BY post_id ASC LIMIT 1");
			$link = get_post_link( $nextpost );
		}
	}
	return $link;
}
function utplugin_update_log($post_text, $post_id = 0)
{
	global $bbdb, $bb_current_user, $bb_table_prefix;
	if (bb_is_user_logged_in())
	{
		$user = bb_get_user($bb_current_user->ID);
		$post_id = get_post_id($post_id);
		$bb_post = bb_get_post($post_id);

		$last_read = $bbdb->get_var("SELECT post_read FROM ".$bb_table_prefix."utplugin_log WHERE user_id = $user->ID AND topic_id = $bb_post->topic_id");
		if (! $last_read)
		{
	                $bbdb->query("INSERT INTO ".$bb_table_prefix."utplugin_log (user_id, topic_id, post_read) VALUES ( $user->ID, $bb_post->topic_id, $post_id )");
		}

		if ($last_read && $post_id && $last_read >= $post_id)
		{
			return $post_text;
		} else if ($last_read < $post_id) {
			$bbdb->query("UPDATE ".$bb_table_prefix."utplugin_log SET post_read = $post_id WHERE user_id = $user->ID AND topic_id = $bb_post->topic_id");
		}
	}

	return $post_text;
}

?>
