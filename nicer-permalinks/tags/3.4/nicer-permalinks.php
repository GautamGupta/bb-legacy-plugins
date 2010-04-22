<?php
/*
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Description: Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes hierarchy. Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.
Author: mr_pelle
Author URI:
Version: 3.4
Requires at least: 1.0.2
Tested up to: 1.0.2
*/


/**
 * Update `.htaccess`
 **/
// files paths
$htaccess = BB_PATH.".htaccess";
$nicer_htaccess = bb_get_plugin_directory(bb_plugin_basename(__FILE__))."nicer-htaccess";

// first of all, check if `.htaccess` has already been updated

// look for control sequence
if (file_get_contents($htaccess, NULL, NULL, 0, 2) != '##') // try to update `.htaccess`
{
	// check files permissions
	if (!is_writable($htaccess) || !is_writable($nicer_htaccess))
	{
		bb_die("Files not writable. Please see ".bb_get_plugin_directory(bb_plugin_basename(__FILE__))."readme.txt");
		exit;
	}
	else // update `.htaccess`
	{
		// load files content
		$content = file_get_contents($htaccess);
		$nicer_content = file_get_contents($nicer_htaccess);

		// swap files content
		file_put_contents($htaccess, $nicer_content);
		file_put_contents($nicer_htaccess, $content);
	}
}


/**
 * Add bbPress filters
 *
 * Note: following code is executed whether `.htaccess` was updated manually or automatically
 **/
add_filter('get_forum_link', 'nicer_get_forum_link_filter');
add_filter('bb_get_forum_bread_crumb', 'nicer_bb_get_forum_bread_crumb_filter');
add_filter('get_topic_link', 'nicer_get_topic_link_filter');
add_filter('get_post_link', 'nicer_get_post_link_filter');
add_filter('bb_slug_sanitize', 'nicer_bb_slug_sanitize_filter');


/**
 * Nicer get_forum_link filter
 **/
function nicer_get_forum_link_filter ($link, $forum_id = 0) {
	// remove redundant "forum" word from URI.
	// str_replace looks for bb_get_option('path')."forum/" instead of just "forum" to avoid misreplacements
	$link = str_replace(bb_get_option('path')."forum/", bb_get_option('path'), $link);

	// append `/` to forum URI. Mandatory! Props: Mohta
	return $link."/";
}


/**
 * Nicer bb_get_forum_bread_crumb filter
 **/
function nicer_bb_get_forum_bread_crumb_filter ($trail = '', $forum_id = 0) {
	// remove redundant "forum" word from each forum URI.
	// str_replace looks for bb_get_option('path')."forum/" instead of just "forum/" to avoid misreplacements
	$trail = str_replace(bb_get_option('path')."forum/", bb_get_option('path'), $trail);

	// append `/` to each forum URI, if missing. Mandatory! Props: Mohta
	return preg_replace('/([^\/])(">)/', '$1/$2', $trail);
}


/**
 * Nicer get_topic_link filter
 **/
function nicer_get_topic_link_filter ($link, $id = 0) {
	// request coming from main forum, from an admin page or from a view?
	// The first passes a topic id, the second a post id and the third a view id.
	if ($topic_id = get_topic_id($id)) // request coming from main forum
		$topic = get_topic($topic_id); // retrieve topic object
	elseif ($post_id = get_post_id($id)) // request coming from an admin page
	{
		$bb_post = bb_get_post($post_id); // retrieve post object

		$topic = get_topic(get_topic_id($bb_post->topic_id)); // retrieve topic object that contains post
	}
	elseif (get_view_name($id) != '') // request coming from a view
		$topic = bb_get_topic_from_uri($link); // retrieve topic object from its URI

	$forum = get_forum(get_forum_id($topic->forum_id)); // retrieve forum object that contains topic

	// replace "topic" with "$forum->forum_slug" to emphasize hierarchy.
	// str_replace looks for bb_get_option('path')."topic/" instead of just "topic/" to avoid misreplacements
	return str_replace(bb_get_option('path')."topic/", bb_get_option('path')."$forum->forum_slug/", $link);
}


/**
 * Nicer get_post_link filter
 **/
function nicer_get_post_link_filter ($link, $post_id = 0, $topic_id = 0) {
	// get_post_link or get_topic_last_post_link request?
	// The former uses `$post_id`, the latter both `$post_id` and `$topic_id`.
	if ($id = get_topic_id($topic_id)) // get_topic_last_post_link request
	{
		$topic = get_topic($id); // retrieve topic object

		$post_id = $topic->topic_last_post_id; // retrieve topic last post id from topic object
	}
	elseif ($id = get_post_id($post_id)) // get_post_link request
	{
		$bb_post = bb_get_post($id); // retrieve post object

		$topic = get_topic(get_topic_id($bb_post->topic_id)); // retrieve topic object that contains post
	}
	$forum = get_forum(get_forum_id($topic->forum_id)); // retrieve forum object that contains topic

	$post_page = bb_get_page_number(get_post_position($post_id)); // retrieve page where post is located

	// append page to topic URI, if needed
	if ($post_page>1) $page = "/page/$post_page";
	else $page = '';

	// build nicer post URI emphasizing hierarchy
	return bb_get_option('uri')."$forum->forum_slug/$topic->topic_slug$page#post-".get_post_id($post_id);
}


/**
 * Nicer bb_blug_sanitize filter
 **/
function nicer_bb_slug_sanitize_filter ($text_slug, $text_original = '', $length = 0) {
	// prepend "r-" if string begins with "bb-" or is a reserved word.
	// "view" word is changed only if not preceded by `-`. Mandatory to preserve some views by "My Views" plugin!
	return preg_replace('/(bb-.*|rss|tags|[^-]+view|admin|profiles)/', 'r-$1', $text_slug);
}


/**
 * Restore `.htaccess`
 **/
function restore_htaccess() {
	// files paths
	$htaccess = BB_PATH.".htaccess";
	$nicer_htaccess = bb_get_plugin_directory(bb_plugin_basename(__FILE__))."nicer-htaccess";

	// first of all, check if `.htaccess` has already been restored.

	// look for control sequence
	if (file_get_contents($htaccess, NULL, NULL, 0, 2) != '##') return; // no need to do anything more
	else // try to restore `.htaccess`
	{
		// check files permissions
		if (!is_writable($htaccess) || !is_writable($nicer_htaccess))
		{
			bb_die("Files not writable. Please see ".bb_get_plugin_directory(bb_plugin_basename(__FILE__))."readme.txt", "Nicer Permalinks plugin deactivation error");
			exit;
		}
		else // restore `.htaccess`
		{
			// load files content
			$content = file_get_contents($htaccess);
			$nicer_content = file_get_contents($nicer_htaccess);

			// swap files content
			file_put_contents($htaccess, $nicer_content);
			file_put_contents($nicer_htaccess, $content);
		}
	}
}


/**
 * Grab bbPress plugin deactivated hook
 **/
bb_register_plugin_deactivation_hook(__FILE__, 'restore_htaccess');