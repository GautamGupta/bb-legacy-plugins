<?php
/*
Plugin Name: bbPress Recent Replies
Plugin URI: http://bbpress.org/plugins/topic/bbpress-recent-replies/
Version: 0.1b
Description: Shows recent replies just like we have recent comments plugin on WordPress
Author: Ashfame
Author URI: http://www.ashfame.com/
Donate: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8357028
Possible Improvements : 1) Better MySQL queries 2) Options
*/

/*******************************/
/* Options - you can edit them */
/*******************************/
$css="#recent-replies li{ margin-bottom:3px; }"; // change this to alter the default styling of the DIV
$css_post_id_prefix="post-"; // change this to the DIV you are using for posts in yoe theme 'post-X'

/******************/
/* Attached hooks */
/******************/
// add_action('post_post_form','ashfame_recent_bbpress_replies'); // this is not working as of now - gotta fix it
add_action('bb_head','ashfame_recent_bbpress_replies_style'); // comment this line to remove the default styling and use your own in your theme's stylesheet

/************************/
/* Function Definitions */
/************************/

function ashfame_recent_bbpress_replies_style() // this hooks up the styling to bb_head
{
	global $css;
	echo "\n<style type=\"text/css\">$css</style>\n";
}

function ashfame_recent_bbpress_replies($limit=5,$heading_opening_tag="<h2>",$heading_closing_tag="</h2>") // You can call the following function anywhere in your template 
{
	global $bbdb, $bb_table_prefix, $query_recent_replies,$css_post_id_prefix;
	$where = " WHERE post_status = 0 AND post_position <> 1 ";
	$query_recent_replies = "SELECT * from ".$bb_table_prefix."topics JOIN ".$bb_table_prefix."posts ON ".$bb_table_prefix."topics.topic_id = ".$bb_table_prefix."posts.topic_id ".$where." ORDER BY post_time DESC LIMIT ".$limit;
	$recent_replies = $bbdb->get_results($query_recent_replies);
	/* echo $query_recent_replies; */
		
	if (empty($recent_replies))
		echo "err";
	echo "\n<!-- Start of code - (Ashfame's Recent bbPress Replies Plugin) -->\n";
	echo $heading_opening_tag."Recent Replies".$heading_closing_tag;
	echo "\n<div id=\"recent-replies\">\n";
	echo "<ul>";
	
	$permalink_type = bb_get_option_from_db('mod_rewrite');
	
	foreach($recent_replies as $recent_reply)
	{
		echo "\n<li><a href=\"".get_user_profile_link ($recent_reply->poster_id)."\">".get_user_display_name ($recent_reply->poster_id)."</a> on <a href=\"".get_topic_link($recent_reply->topic_id);
		$page = (int)$recent_reply->post_position / bb_get_option_from_db('page_topics') ;
		
		if ($page > 1)
		{
			if(!$permalink_type)
				echo "&page=".(int) ++$page;
			else
				echo "/page/".(int) ++$page;
		}
		echo "#".$css_post_id_prefix.$recent_reply->post_id."\">".$recent_reply->topic_title."</a></li>";
	}
	
	echo "\n</ul>\n";
	echo "</div>\n<!-- End of code - (Ashfame's Recent bbPress Replies Plugin) -->\n\n";
}

?>