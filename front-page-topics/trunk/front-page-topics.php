<?php
/*
Plugin Name: Front Page Topics
Plugin URI: http://bbpress.org/forums/topic/65#post-333
Description: Changes the number of topics displafed on the front page only.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.7.2

Requires at least: 0.72
Tested up to: 0.73
*/

function front_page_topics() {
	global $bb;
	$bb->page_topics = 3;
}

// This is what changes it only on the front page.
add_action( 'bb_index.php_pre_db', 'front_page_topics' );
?>
