<?php
/*
Plugin Name: Forum is category
Description: Turn a forum into a "category" which cannot be posted to and does not have post/topic counts.
Plugin URI: about:blank
Author: Nightgunner5
Author URI: http://llamaslayers.net/daily-llama/
Version: 1.0
*/

// All of the forum IDs that are categories.
// You can find this out by editing a forum and looking at the ID GET parameter (bb-admin/content-forums.php?action=edit&id=21 would be 21.)
// For example, array(21, 3, 12); would do forum number 21, 3, and 12.
// Forums that are parents of categories must also be categories and will be made so if they are not already.
$forums_that_are_categories = array();
				################
				# STOP EDITING #
				################
reset($forums_that_are_categories);
while (list(, $forumcat) = each($forums_that_are_categories)) {
	if (!in_array(get_forum_parent($forumcat), $forums_that_are_categories)) {
		$forums_that_are_categories[] = get_forum_parent($forumcat);
		reset($forums_that_are_categories);
	}
}

function restrict_category_posting($retvalue, $capability, $args) {
	if ($capability == "write_topic" && in_array($args[1], $forums_that_are_categories)) {
		return false;
	} elseif ($capability == "move_topic" && in_array($args[2], $forums_that_are_categories)) {
		return false;
	}
	return $retvalue;
}
add_filter('bb_current_user_can', 'restrict_category_posting', 10, 3);

function restrict_category_postview($posts, $id) {
	if (in_array($id, $forums_that_are_categories)) {
		return false;
	}
	return $posts;
}
add_filter('get_forum_posts', 'restrict_category_postview', 10, 2);
add_filter('get_forum_topics', 'restrict_category_postview', 10, 2);

?>