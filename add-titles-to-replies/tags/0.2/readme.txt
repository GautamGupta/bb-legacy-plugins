=== Add Titles to Replies ===
Tags: replies, titles
Contributors: louisedade
Requires at least: 0.8
Tested up to: 0.8.1
Stable Tag: 0.2

Allows users to add a title to their replies to topics.

== Description ==

Plugin Name: Add Titles to Replies
Plugin URI: http://www.classical-webdesigns.co.uk/articles/36_bbpress-plugin-add-titles-to-replies.html
Description: Allows users to add a title to their replies to topics.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
Version: 0.2

== Installation ==

NB: All examples are based on the kakumei theme.
	
1.  Open up your 'post-form.php' template file and find the following section (it's at the top):

	<?php if ( !is_topic() ) : ?>
	<p>
		<label for="topic"><?php _e('Topic title: (be brief and descriptive)'); ?>
			<input name="topic" type="text" id="topic" size="50" maxlength="80" tabindex="1" />
		</label>
	</p>
	<?php endif; do_action( 'post_form_pre_post' ); ?>

Replace it with the following:

	<?php if ( !is_topic() ) { ?>

	<p>
		<label for="topic"><?php _e('Topic title: (be brief and descriptive)'); ?><br />
			<input name="topic" type="text" id="topic" size="60" maxlength="80" tabindex="1" />
		</label>
	</p>

	<?php } else { ?>

	<p>
		<label for="replytitle"><?php _e('Message Title: (be brief and descriptive)'); ?><br />
			<input name="msgtitle" type="text" id="replytitle" size="60" maxlength="80" tabindex="1" value="Re: <?php topic_title(); ?>" />
		</label>
	</p>

	<?php } do_action( 'post_form_pre_post' ); ?>


2.  Open up your 'edit-form.php' template file and find the following section (it's at the top):

	<?php if ( $topic_title ) : ?>
	<p>
		<label><?php _e('Topic:'); ?><br />
			<input name="topic" type="text" id="topic" size="50" maxlength="80"  value="<?php echo attribute_escape( get_topic_title() ); ?>" />
		</label>
	</p>
	<?php endif; ?>

Replace it with the following:

	<?php if ( $topic_title ) { ?>
	<p>
		<label><?php _e('Topic:'); ?><br />
			<input name="topic" type="text" id="topic" size="50" maxlength="80"  value="<?php echo wp_specialchars(get_topic_title(), 1); ?>" />
		</label>
	</p>
	<?php } else { ?>
	<p>
		<label for="replytitle"><?php _e('Message Title:'); ?><br />
			<input name="msgtitle" type="text" id="replytitle" size="80" maxlength="80" tabindex="1" value="<?php echo wp_specialchars(ld_get_post_title(), 1); ?>" />
		</label>
	</p>
	<?php } ?>


3.  Open up your 'post.php' template file and add the following code to display the post title (wherever it suits you best):

	<?php ld_post_title(); ?>

For example, in the kakumei theme you might add it like this:

	<div class="threadpost">
		<h3 class="post_title"><?php ld_post_title(); ?></h3>
		<div class="post"><?php post_text(); ?></div>
		<div class="poststuff"><?php printf( __('Posted %s ago'), bb_get_post_time() ); ?> <a href="<?php post_anchor_link(); ?>">#</a> <?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?></div>
	</div>


4.  Now we need to add a field to the database. If you are comfortable with MySQL, just login to your database admin and add the following field to the 'posts' table:  

		post_title  VARCHAR(100)  NOT NULL

Alternatively, we do this by adding a temporary line of the code to your forum templates*. Add the following code to the bottom of your 'footer.php' template.

	<?php ld_install_title_field(); ?>

Upload it to your working/test forum and load the forum in your browser.  This should add the new field to the database.


5.  Upload the edited templates ('post-form.php' and 'post.php') to the appropriate templates folder, and upload the 'reply-titles.php' plugin to your 'my-plugins' folder.


6. Test the forum by adding a message with a title to check that the installation function worked. If it did you'll see the reply title when you view topic replies.  You can then reopen your 'footer.php' file and REMOVE the following line (remembering to re-upload the file afterwards):
		
	<?php ld_install_title_field(); ?>


That's it, the Add Titles to Replies plugin is now installed and working.

* I made this a add, run, remove function because I really don't like adding unnecessary overhead to the code - once installed, there is no need to keep checking the database!

=== Configuration ===

None.

== Frequently Asked Questions ==

None (yet).


== Screenshots ==

Not applicable

CHANGE LOG

2007-03-11	Ver. 0.2 enabled the title to be edited in "Edit Post" + template changes.
2007-03-10	ver. 0.1 relased