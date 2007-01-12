<?php
/*
Plugin Name: Show Post Count
Plugin URI: http://faq.rayd.org/bbpress_postcount/
Description: Allows post count to be easily displayed, just add post_count() wherever you want it
Change Log: .73a - No longer counts posts that have been deleted or marked as spam
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .73a
*/

function post_count() {
	if (  get_post_author_id() ) 
		echo 'Post Count: ' . get_post_count( get_post_author_id() );
	else
		echo 'Error';
}

function get_post_count ( $id ) {
	global $bbdb;

	return $bbdb->query("SELECT * FROM $bbdb->posts WHERE poster_id = $id AND post_status = 0");

}

?>