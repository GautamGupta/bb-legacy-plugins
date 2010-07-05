<?php
/**
 * @package Nicer Permalinks
 */

// Load very often used options
$bb_uri = bb_get_option( 'uri' );

/**
 * Functions
 */

/**
 * Return nicer forum link
 *
 * @param string $link     Forum link
 * @param int    $forum_id Forum id
 *
 * @uses $bb_uri
 *
 * @return string
 */
function get_nicer_forum_link_filter( $link, $forum_id = 0 ) {
	global $bb_uri;

	// Remove redundant "forum" word from forum link and append '/'. Mandatory! Props: Mohta
	return str_replace( $bb_uri . 'forum/', $bb_uri, $link ) . '/';
}

/**
 * Return nicer forum bread crumb
 *
 * @param string $trail    Forum bread crumb
 * @param int    $forum_id Forum id
 *
 * @uses $bb_uri
 *
 * @return string
 */
function bb_get_nicer_forum_bread_crumb_filter( $trail, $forum_id = 0 ) {
	global $bb_uri;

	// Remove redundant "forum" word from every forum link
	$trail = str_replace( $bb_uri . 'forum/', $bb_uri, $trail );

	// Append '/' to each forum link, if missing. Mandatory! Props: Mohta
	return preg_replace( '/([^\/])(">)/', '$1/$2', $trail );
}

/**
 * Return nicer topic link
 *
 * @param string $link Topic link
 * @param int    $id   Topic id
 *
 * @uses $bb_uri
 * @uses get_topic_id()
 * @uses get_topic()
 * @uses get_post_id()
 * @uses bb_get_post()
 * @uses bb_get_topic_from_uri()
 * @uses get_forum()
 * @uses get_forum_id()
 *
 * @return string
 */
function get_nicer_topic_link_filter( $link, $id = 0 ) {
	global $bb_uri;

	if ( $topic_id = get_topic_id( $id ) ) { // Request is coming from main forum if $id is a topic id
		$topic = get_topic( $topic_id );
	} elseif ( $post_id = get_post_id( $id ) ) { // Request is coming from admin area if $id is a post id
		$bb_post = bb_get_post( $post_id );
		$topic = get_topic( get_topic_id( $bb_post->topic_id ) );
	} else { // No topic info can be retrieved from $id
		$topic = bb_get_topic_from_uri( $link );
	}

	// The following is automatically skipped on bbPress 1.1-alpha or higher
	if ( !$topic ) // Request is a deleted topic redirection link request, which lacks callback info
		return $bb_uri; // Return forum index link because no topic info can be retrieved

	// Retrieve topic parent forum
	$forum = get_forum( get_forum_id( $topic->forum_id ) );

	// Replace "topic" word with parent forum slug to emphasize hierarchy
	return str_replace( $bb_uri . 'topic/', $bb_uri . "{$forum->forum_slug}/", $link );
}


/**
 * Return nicer post link
 *
 * @param string $link      Post link
 * @param int    $post_id   Post id
 * @param int    $topic_id  Post parent topic id
 *
 * @uses $bb_uri
 * @uses get_topic_id()
 * @uses get_post_id()
 * @uses bb_get_post()
 * @uses get_topic()
 * @uses get_topic_id()
 * @uses get_forum()
 * @uses get_forum_id()
 * @uses bb_get_page_number()
 * @uses get_post_position()
 *
 * @return string
 */
function get_nicer_post_link_filter( $link, $post_id = 0, $topic_id = 0 ) {
	global $bb_uri;

	if ( get_topic_id( $topic_id ) ) { // Request is a get_topic_last_post_link() if $topic_id is used
		return $link; // Return unfiltered link because it was already filtered by get_nicer_topic_link_filter()
	} elseif ( $id = get_post_id( $post_id ) ) { // Request is a get_post_link() if $post_id id used
		$bb_post = bb_get_post( $id );

		// Retrieve post parent topic
		$topic = get_topic( get_topic_id( $bb_post->topic_id ) );
	} else { // Request is an other "anchor-like" post request if it does not match previous cases
		return $link; // Return unfiltered link because it was already filtered by get_nicer_topic_link_filter()
	}

	// Retrieve topic parent forum
	$forum = get_forum( get_forum_id( $topic->forum_id ) );

	// Retrieve post page number
	$post_page_number = bb_get_page_number( get_post_position( $post_id ) );

	// Generate post page trail to be appended to nicer post link, if needed
	$post_page = ( 1 < $post_page_number ) ? "/page/$post_page_number" : '';

	// Generate nicer post link emphasizing hierarchy
	return $bb_uri . "{$forum->forum_slug}/{$topic->topic_slug}$post_page#post-" . get_post_id( $post_id );
}
