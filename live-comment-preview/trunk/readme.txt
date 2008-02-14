=== BBPress:Live Comment ===
Tags: comment, preview, ajax, post
Donate link: http://klr20mg.com/
Contributors: Enrique Chavez aka Tmeister
Requires at least: 0.8.3.1
Tested up to: 0.8.3.1
Stable Tag: 0.8.3.1

Add a preview button.

== Description ==
	
Provide users with a live comment preview before submit. show it in the same page using Ajax

== Installation ==

-Add the `live_comment_preview` folder to bbPress' `my-plugins/` directory.
-Activate the plugin in your admin area
-Add the code:

	<?php add_live_comment_preview("View Preview"); ?>

	to your topic.php file, where you want to show the preview's area.
	
	ie. before the post form something like that
	
	<?php if ( topic_is_open( $bb_post->topic_id ) ) : ?>
		<?php add_live_comment_preview("View Preview"); ?>
		<?php post_form(); ?>
	<?php else : ?>

-Also, you can edit the CSS file (style.css) to adapt the design to your template.

