=== Move It ===

Tags: move, post
Contributors: 
Requires at least: 0.8
Tested up to: 0.8.1
Stable Tag: 0.12

This plugin allows you to manage posts and topic.

== Description ==

This plugin allows you to move single post to another topic and to merge topics.


== Installation ==

Simply download the file, and upload to /my-plugins dir.

Then edit you template post.php file adding this:


<?
global $bb_current_user;
			
if(bb_current_user_can( 'moderate' )) moveIt(get_post_id(),get_topic_id()); ?>

Open topic.php and add this @ line 55:

<?php mergeIt(get_topic_id()); ?>

== Version History ==

Version 0.12 (2007-05-07)

* Now topics can be merged
* Problem with url reload on button pressing that doesn't hide the moved post

Version 0.11 (2007-05-02)

* Both origin topic and destination topic info are now upgraded
* Problem with url reload on button pressing that doesn't hide the moved post

Version 0.1 (2007-05-01)

* First release