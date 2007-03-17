<?php
/*
Plugin Name: Add Titles to Replies
Plugin URI: http://www.classical-webdesigns.co.uk/articles/36_bbpress-plugin-add-titles-to-replies.html
Version: 0.2
Description: Allows users to add a title to their replies to topics.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
*/

// Add post_title field to database (add this function to footer.php template,
// run once, and then remove from template)
function ld_install_title_field()
{
	global $bbdb, $bb_table_prefix;

	$installed = false;

	// Get fieldnames from posts table
	$fields = $bbdb->get_results("SHOW COLUMNS FROM ".$bb_table_prefix."posts");

	// Check that 'post_title' doesn't already exist
	foreach ($fields as $field) {
		if ($field->Field == "post_title") {
			$installed = true;
		}
	}

	// If 'post_title' does not exist, add it to the table
	if (!$installed) {
		$bbdb->query("ALTER TABLE `".$bb_table_prefix."posts` ADD `post_title` VARCHAR(100) NOT NULL");
	}

	$bbdb->show_errors();
}

// Get the post title and filter it to spit out to template
function ld_post_title() {
	echo apply_filters( 'ld_post_title', ld_get_post_title() );
}
function ld_get_post_title() {
	global $bb_post;
	return $bb_post->post_title;
}


// Extend the bb_new_post() function by adding a title to replies
// when new post is added via bb_new_topic(), it takes the topic title as the message title.
// also used when the post is edited.
function ld_add_reply_title ( $post_id )
{
	global $bbdb;

	if ( !trim( $_POST['msgtitle'] ) && !trim( $_POST['topic'] ) )
	{
		$bb_ttl = "(No Title)";
	}
	else if ( !trim( $_POST['msgtitle'] ) && trim( $_POST['topic'] ) )
	{
		$bb_ttl  = apply_filters('pre_topic_title', trim( $_POST['topic'] ), false);
	}
	else
	{
		$bb_ttl  = apply_filters('pre_topic_title', trim( $_POST['msgtitle'] ), false);
	}

	if ( $bb_ttl && $post_id ) {
		$bbdb->query("UPDATE $bbdb->posts SET post_title='$bb_ttl' WHERE post_id='$post_id'");
		return $post_id;
	} else {
		return false;
	}
}

add_action( 'bb_new_post', 'ld_add_reply_title', 1, 1 ); // add new
add_action( 'bb_update_post', 'ld_add_reply_title', 1, 1 ); // edit

?>