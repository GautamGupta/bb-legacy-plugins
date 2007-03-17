<?php
/*
Plugin Name: Latest Replies
Plugin URI: http://www.classical-webdesigns.co.uk/articles/38_bbpress-plugin-latest-replies.html
Description: Displays the latest replies (by title) to discussions on the front page and forum user's latest replies on their profile page.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
Version: 0.2

REQUIRED: the 'reply-titles' plugin is required to use this plugin.
http://www.classical-webdesigns.co.uk/articles/36_bbpress-plugin-add-titles-to-replies.html
*/

function ld_get_latest_posts($limit=30, $poster=0, $page = 1 )
{
	global $bbdb, $bb_last_countable_query;

	$page = (int) $page;

	$where = 'WHERE post_status = 0';

	if ($poster > 0)
		$where .= " AND poster_id = $poster";

	if ( 1 < $page )
		$limit = ($limit * ($page - 1)) . ", $limit";

	$bb_last_countable_query = "SELECT post_id,forum_id,topic_id,poster_id,post_title,post_time FROM $bbdb->posts $where ORDER BY post_time DESC LIMIT $limit";

	if ( $ld_latest_posts = $bbdb->get_results($bb_last_countable_query) )
		return $ld_latest_posts;
	else
		return false;
}

function ld_reply_link( $id = 0, $reply = 0, $page = 1 ) {
	echo apply_filters( 'topic_link', ld_get_reply_link($id, $reply), $id );
}

function ld_get_reply_link( $id = 0, $reply = 0, $page = 1 ) {

	$args = array();

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "topic/$id" . ( 1 < $page ? "/page/$page" : '' );
	else {
		$link = bb_get_option('uri') . 'topic.php';
		$args['id'] = $id;
		$args['page'] = 1 < $page ? $page : '';
	}

	if ( bb_current_user_can('write_posts') )
		$args['replies'] = $reply;
	if ( $args )
		$link = add_query_arg( $args, $link ) . "#post-" . $reply;

	return apply_filters( 'get_topic_link', $link, $id );
}

function ld_post_time($t, $args = '' ) {
	$args = _bb_parse_time_function_args( $args );
	$time = apply_filters( 'bb_post_time', ld_get_post_time($t, array('format' => 'mysql') + $args ), $args );
	return _bb_time_function_return( $time, $args );
}

function ld_get_post_time($t, $args = '' ) {
	$args = _bb_parse_time_function_args( $args );

	$time = apply_filters( 'bb_get_post_time', $t, $args );

	return _bb_time_function_return( $time, $args );
}

?>