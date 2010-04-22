<?php
/*
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Description: Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes hierarchy. Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.
Author: mr_pelle
Author URI:
Version: 3.3.2
Requires at least: 1.0.2
Tested up to: 1.0.2
*/


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
 * Update `.htaccess`
 **/
function update_htaccess() {
	// files paths
	$htaccess = BB_PATH.".htaccess";
	$nicer_htaccess = bb_get_plugin_directory(bb_plugin_basename(__FILE__))."nicer-htaccess";

	// first of all, check if `.htaccess` has already been updated
	// File is accessed in read-only mode so even if its permissions have not been set to "666",
	// like when manually editing it, the check can be performed

	$htaccess_stream = fopen($htaccess, 'r'); // open `.htaccess` in read-only mode

	if (fread($htaccess_stream, 2) == '##') // look for control sequence
	{
		fclose($htaccess_stream);

		return; // no need to do anything more
	}
	else // try to update `.htaccess`
	{
		fclose($htaccess_stream);

		if (is_writable($htaccess) && is_writable($nicer_htaccess)) // check files writability
		{
			// open files in read+write mode
			$htaccess_stream = fopen($htaccess, 'r+');
			$nicer_htaccess_stream = fopen($nicer_htaccess, 'r+');

			// store files size (needed to truncate)
			$htaccess_size = filesize($htaccess);
			$nicer_htaccess_size = filesize($nicer_htaccess);

			// load files content
			$content = fread($htaccess_stream, $htaccess_size);
			$nicer_content = fread($nicer_htaccess_stream, $nicer_htaccess_size);

			// return pointers to the top
			rewind($htaccess_stream);
			rewind($nicer_htaccess_stream);

			// update files content
			fwrite($htaccess_stream, $nicer_content);
			fwrite($nicer_htaccess_stream, $content);

			// truncate to fit new content
			ftruncate($htaccess_stream, $nicer_htaccess_size);
			ftruncate($nicer_htaccess_stream, $htaccess_size);

			fclose($htaccess_stream);
			fclose($nicer_htaccess_stream);
		}
		else bb_die("Files not writable. Please see ".bb_get_plugin_directory(bb_plugin_basename(__FILE__))."readme.txt");
	}
}


/**
 * Restore `.htaccess`
 **/
function restore_htaccess() {
	// files paths
	$htaccess = BB_PATH.".htaccess";
	$nicer_htaccess = bb_get_plugin_directory(bb_plugin_basename(__FILE__))."nicer-htaccess";

	// first of all, check if `.htaccess` has already been restored.
	// File is accessed in read-only mode so even if its permissions have not been set to "666",
	// like when manually editing it, the check can be performed

	$htaccess_stream = fopen($htaccess, 'r'); // open `.htaccess` in read-only mode

	if (fread($htaccess_stream, 2) != '##') // look for control sequence
	{
		fclose($htaccess_stream);

		return; // no need to do anything more
	}
	else // try to restore `.htaccess`
	{
		fclose($htaccess_stream);

		if (is_writable($htaccess) && is_writable($nicer_htaccess)) // check files writability
		{
			// open files in read+write mode
			$htaccess_stream = fopen($htaccess, 'r+');
			$nicer_htaccess_stream = fopen($nicer_htaccess, 'r+');

			// store files size (needed to truncate)
			$htaccess_size = filesize($htaccess);
			$nicer_htaccess_size = filesize($nicer_htaccess);

			// load files content
			$content = fread($htaccess_stream, $htaccess_size);
			$nicer_content = fread($nicer_htaccess_stream, $nicer_htaccess_size);

			// return pointers to the top
			rewind($htaccess_stream);
			rewind($nicer_htaccess_stream);

			// restore files content
			fwrite($htaccess_stream, $nicer_content);
			fwrite($nicer_htaccess_stream, $content);

			// truncate to fit new content
			ftruncate($htaccess_stream, $nicer_htaccess_size);
			ftruncate($nicer_htaccess_stream, $htaccess_size);

			fclose($htaccess_stream);
			fclose($nicer_htaccess_stream);
		}
		else bb_die("Files not writable. Please see ".bb_get_plugin_directory(bb_plugin_basename(__FILE__))."readme.txt");
	}
}


/**
 * Grab bbPress plugin activated/deactivated hooks
 **/
bb_register_plugin_activation_hook(__FILE__, 'update_htaccess');
bb_register_plugin_deactivation_hook(__FILE__, 'restore_htaccess');


/**
 * Add bbPress filters
 **/
add_filter('get_forum_link', 'nicer_get_forum_link_filter');
add_filter('bb_get_forum_bread_crumb', 'nicer_bb_get_forum_bread_crumb_filter');
add_filter('get_topic_link', 'nicer_get_topic_link_filter');
add_filter('get_post_link', 'nicer_get_post_link_filter');
add_filter('bb_slug_sanitize', 'nicer_bb_slug_sanitize_filter');