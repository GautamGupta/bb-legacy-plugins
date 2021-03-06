<?php
/**
 * @package Nicer Permalinks
 */


// Add plugin filters
add_filter( 'get_forum_link', 'get_forum_nicer_link', 10, 1 );
add_filter( 'get_topic_link', 'get_topic_nicer_link', 10, 1 );
add_filter( 'get_post_link',  'get_post_nicer_link', 10, 2 );


/**
 * Functions
 */

/**
 * Return forum nicer link
 *
 * @param string $link Forum link
 *
 * @global $bb
 *
 * @return string
 */
function get_forum_nicer_link( $link ) {
	global $bb;

	// Remove redundant "forum" word from forum link and append '/'. Mandatory! Props: Mohta
	return str_replace( $bb->uri . 'forum/', $bb->uri, $link ) . '/';
}

/**
 * Return topic nicer link
 *
 * @param string $link Topic link
 *
 * @global $topic
 * @global $bb
 *
 * @uses bb_get_topic_from_uri()
 * @uses wp_get_referer()
 * @uses add_query_arg()
 * @uses get_forum()
 *
 * @return string
 */
function get_topic_nicer_link( $link ) {
	global $topic;

	if ( !$topic ) {
		$topic = bb_get_topic_from_uri( $link );

		if ( !$topic ) // Fix for bbPress 1.0.2 deleted topic redirection link
			return add_query_arg( 'view', 'all', wp_get_referer() );
	}

	global $bb;

	// Replace "topic" word with parent forum slug to emphasize hierarchy
	return str_replace( $bb->uri . 'topic', $bb->uri . get_forum( $topic->forum_id )->forum_slug, $link );
}

/**
 * Return post nicer link
 *
 * @param string $link    Post link
 * @param int    $post_id Post id
 *
 * @global $bb_post
 *
 * @uses bb_get_first_post()
 * @uses get_post_link()
 *
 * @return string
 */
function get_post_nicer_link( $link, $post_id = 0 ) {
	if ( !$post_id ) { // Fix for bbPress 1.0.2 Relevant "posts" links
		global $bb_post; // $bb_post actually is a topic object

		return get_post_link( bb_get_first_post( $bb_post )->post_id );
	}

	return $link; // Yeah, really. There's no need to filter it at all!
}
