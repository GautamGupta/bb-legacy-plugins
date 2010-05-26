<?php
/*
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Description: Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes hierarchy. Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.
Version: 3.6.1
Author: mr_pelle
Author URI: mailto:francesco.pelle@gmail.com
*/


/**
 * PHP 4 backward compatibility
 *
 * Note: this custom functions are NOT solid, they are intented to be used in this plugin ONLY!
 * Please do NOT re-use this functions anywhere else!
 */
if ( !function_exists( 'file_put_contents' ) ) {
	function file_put_contents( $filename, $contents ) {
		$stream = fopen( $filename, 'w' );
		fwrite( $stream, $contents );
		fclose( $stream );
	}
}

if ( !function_exists( 'file_get_contents' ) ) {
	function file_get_contents( $filename, $flags = 0, $context, $offset = 0, $maxlen ) {
		$stream = fopen( $filename, 'r' );

		if ( !$maxlen )
			$maxlen = filesize( $filename );

		$contents = fread( $stream, $maxlen );
		fclose( $stream );

		return $contents;
	}
}


/**
 * Update .htaccess
 *
 * Note: the following code won't mess up files if manual update was performed
 */
$htaccess = BB_PATH .'.htaccess';
$nicer_htaccess = bb_get_plugin_directory( bb_plugin_basename( __FILE__ ) ) .'nicer-htaccess';

// first of all, check if file has already been updated

// look for control sequence
if ( '##' != file_get_contents( $htaccess, NULL, NULL, 0, 2 ) ) {
	// check files permissions
	if ( !is_writable( $htaccess ) || !is_writable( $nicer_htaccess ) ) { // cannot update automatically
		bb_die( sprintf( __('Files not writable. Please see %s'), bb_get_plugin_directory( bb_plugin_basename( __FILE__ ) ) .'readme.txt' ) );
		exit();
	} else { // update files
		// load files content
		$content =       file_get_contents( $htaccess );
		$nicer_content = file_get_contents( $nicer_htaccess );

		// swap files content
		file_put_contents( $htaccess, $nicer_content );
		file_put_contents( $nicer_htaccess, $content );
	}
} else ; // files already updated


/**
 * Add bbPress filters
 *
 * Note: the following code is executed whether manual or automatic update was performed
 */
add_filter( 'get_forum_link', 'nicer_get_forum_link_filter', 10, 2 );
add_filter( 'bb_get_forum_bread_crumb', 'nicer_bb_get_forum_bread_crumb_filter', 10, 2 );
add_filter( 'get_topic_link', 'nicer_get_topic_link_filter', 10, 2 );
add_filter( 'get_post_link', 'nicer_get_post_link_filter', 10, 3 );


/**
 * Nicer get_forum_link filter
 */
function nicer_get_forum_link_filter( $link, $forum_id = 0 ) {
	// remove redundant "forum" word from URI
	$link = str_replace( bb_get_option('uri') .'forum/', bb_get_option('uri'), $link );

	// append '/' to forum URI. Mandatory! Props: Mohta
	return $link .'/';
}


/**
 * Nicer bb_get_forum_bread_crumb filter
 */
function nicer_bb_get_forum_bread_crumb_filter( $trail = '', $forum_id = 0 ) {
	// remove redundant "forum" word from each forum URI
	$trail = str_replace( bb_get_option('uri') .'forum/', bb_get_option('uri'), $trail );

	// append '/' to each forum URI, if missing. Mandatory! Props: Mohta
	return preg_replace( '/([^\/])(">)/', '$1/$2', $trail );
}


/**
 * Nicer get_topic_link filter
 */
function nicer_get_topic_link_filter( $link, $id = 0 ) {
	// request coming from main forum, from an admin page or from a view?
	// The first passes a topic id, the second a post id and the third a view id
	if ( $topic_id = get_topic_id( $id ) ) // request coming from main forum
		$topic = get_topic( $topic_id ); // retrieve topic object
	elseif ( $post_id = get_post_id( $id ) ) { // request coming from an admin page
		$bb_post = bb_get_post( $post_id ); // retrieve post object

		$topic = get_topic( get_topic_id( $bb_post->topic_id ) ); // retrieve topic object that contains post
	} else /* if ( get_view_name($id)!='' ) // request coming from a view */
		// I found out this branch is used by other kind of requests too,
		// so I removed the "get_view_name" check.
		$topic = bb_get_topic_from_uri( $link ); // retrieve topic object from its URI

	if ( !$topic ) // deleted topic redirection link request
		return bb_get_option('uri'); // redirect to main page because no topic info can be retrieved

	$forum = get_forum( get_forum_id( $topic->forum_id ) ); // retrieve forum object that contains topic

	// replace "topic" word with container forum slug to emphasize hierarchy
	return str_replace( bb_get_option('uri') .'topic/', bb_get_option('uri') ."$forum->forum_slug/", $link );
}


/**
 * Nicer get_post_link filter
 */
function nicer_get_post_link_filter( $link, $post_id = 0, $topic_id = 0 ) {
	// get_post_link or get_topic_last_post_link request?
	// The former uses $post_id, the latter both $post_id and $topic_id
	if ( get_topic_id( $topic_id ) ) // get_topic_last_post_link request
		return $link;
	elseif ( $id = get_post_id( $post_id ) ) { // get_post_link request
		$bb_post = bb_get_post( $id ); // retrieve post object

		$topic = get_topic( get_topic_id( $bb_post->topic_id ) ); // retrieve topic object that contains post
	} else // other "anchor-like" post request, already filtered by nicer_get_topic_link_filter
		return $link;

	$forum = get_forum( get_forum_id( $topic->forum_id ) ); // retrieve forum object that contains topic

	$post_page = bb_get_page_number( get_post_position( $post_id ) ); // retrieve page where post is located

	// append page number to URI, if needed
	$page = ( 1 < $post_page ) ? "/page/$post_page" : '';

	// build nicer post URI emphasizing hierarchy
	return bb_get_option('uri') ."$forum->forum_slug/$topic->topic_slug$page#post-". get_post_id($post_id);
}


/**
 * Restore .htaccess
 */
function restore_htaccess() {
	$htaccess = BB_PATH .'.htaccess';
	$nicer_htaccess = bb_get_plugin_directory( bb_plugin_basename( __FILE__ ) ) .'nicer-htaccess';

	// first of all, check if file has already been restored.

	// look for control sequence
	if ( '##' == file_get_contents( $htaccess, NULL, NULL, 0, 2 ) ) {
		// check files permissions
		if ( !is_writable( $htaccess ) || !is_writable( $nicer_htaccess ) ) {
			bb_die( sprintf( __('Files not writable. Please see %s'), bb_get_plugin_directory( bb_plugin_basename( __FILE__ ) ) .'readme.txt' ) );
			exit();
		} else { // restore files
			// load files content
			$content =       file_get_contents( $htaccess );
			$nicer_content = file_get_contents( $nicer_htaccess );

			// swap files content
			file_put_contents( $htaccess, $nicer_content );
			file_put_contents( $nicer_htaccess, $content );
		}
	} else ; // files already restored
}


/**
 * Grab bbPress plugin deactivated hook
 */
bb_register_plugin_deactivation_hook( __FILE__, 'restore_htaccess' );