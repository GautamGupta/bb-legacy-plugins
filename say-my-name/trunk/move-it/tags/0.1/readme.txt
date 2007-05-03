=== Move It ===

Tags: move, post
Contributors: 
Requires at least: 0.8
Tested up to: 0.8.1
Stable Tag: 0.1

This plugin allows you to move single post to another topic.

== Description ==

This plugin allows you to move single post to another topic.

This is an early stage, infact it permits only to move the post between two topics and then updating the post count in the topic.

== Installation ==

Simply download the file, and upload to /my-plugins dir.

Then edit you template post.php file adding this:


<?
global $bb_current_user;
			
if(bb_current_user_can( 'moderate' )) moveIt(get_post_id(),get_topic_id()); ?>

== Version History ==

Version 0.1 (2007-05-01)

* First release