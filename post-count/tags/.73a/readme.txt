=== Post Count ===
Contributors: Josh Hutchins
Requires at least: .73 ?
Tested up to: .74
Stable tag: .73a

Allows post count to be easily displayed, just add 

== Description ==

Plugin Name: Show Post Count
Plugin URI: http://faq.rayd.org/bbpress_postcount/
Description: Allows post count to be easily displayed, just add post_count() wherever you want it
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .73a

== Installation ==

1. Put bb-post-count.php in bbpress/my-plugins

2. for default template, insert "<?php post_count() ?>" in post.php in the templates directory (or if you have your own template, put it wherever you want the post count displayed).

3. If you want a different look, surround "<?php post_count() ?>" with div tags and adjust your .css file accordingly.