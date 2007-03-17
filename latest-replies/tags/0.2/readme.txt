=== Latest Replies ===
Tags: replies, titles, latest-discussion
Contributors: louisedade
Requires at least: 0.8
Tested up to: 0.8.1
Stable Tag: 0.2

Displays the latest replies (by title) to discussions on the front page and forum user's latest replies on their profile page.

== Description ==

Plugin Name: Latest Replies
Plugin URI: http://www.classical-webdesigns.co.uk/articles/38_bbpress-plugin-latest-replies.html
Description: Displays the latest replies (by title) to discussions on the front page and forum user's latest replies on their profile page.
Author: Louise Dade
Author URI: http://www.classical-webdesigns.co.uk/
Version: 0.2

REQUIRED: the 'reply-titles' plugin is required to use this plugin.
http://www.classical-webdesigns.co.uk/articles/36_bbpress-plugin-add-titles-to-replies.html

== Installation ==

NB: All examples are based on the kakumei theme.
	
1.  Open up your 'front-page.php' template file and add the following code in a location you like (I replaced the "Latest Discussions" section, but you could place it before or after it):

	<?php if ( $ld_latest_posts = ld_get_latest_posts(20) ) : ?>

	<h2><?php _e('Latest Replies'); ?></h2>

	<table id="latest">
	<tr>
		<th><?php _e('Reply'); ?></th>
		<th><?php _e('Last Poster'); ?></th>
		<th><?php _e('Freshness'); ?></th>
	</tr>

	<?php foreach ( $ld_latest_posts as $ldp ) : ?>
	<tr<?php topic_class(); ?>>
		<td><a href="<?php ld_reply_link($ldp->topic_id, $ldp->post_id); ?>"><?php echo $ldp->post_title; ?></a>
		<br /><small>&raquo; Topic: <a href="topic.php?id=<?php echo $ldp->topic_id;?>"><?php echo get_topic_title($ldp->topic_id); ?></a>.</small></td>
		<td class="num"><?php echo get_user_name( $ldp->poster_id ); ?></td>
		<td class="num"><?php echo ld_post_time($ldp->post_time, 'since' ); ?></td>
	</tr>
	<?php endforeach; ?>
	</table>

	<?php endif; ?>

You can specify how many relies to display by changing the number in the template function:

	<?php if ( $ld_latest_posts = ld_get_latest_posts(20) ) : ?>

Just change the number 20 for your own preference (the default is 30).


2. Open up your 'profile.php' template and add the following code to it wherever you want the user's latest replies to appear (I replace the existing "Recent Replies" section which actually only shows topic titles and does not link to the reply itself).

	<?php if ( $ld_latest_posts = ld_get_latest_posts(5, $user->ID) ) : ?>

	<ol>
	<?php foreach ( $ld_latest_posts as $ldp ) : ?>
		<li><a href="<?php ld_reply_link($ldp->topic_id, $ldp->post_id); ?>"><?php echo $ldp->post_title; ?></a> 
			<?php printf(__('%s ago.'), ld_post_time($ldp->post_time, 'since' )); ?></li>
	<?php endforeach; ?>
	</ol>

	<?php else : ?>
		<p><?php _e('No replies yet.') ?></p>
	<?php endif; ?>

The above code prints the last 5 replies made by the user.  As you can see the second argument in the 'ld_get_latest_posts' function specifies the user id.


3.  Upload the edited templates ('front-page.php' and 'profile.php') to the appropriate templates folder, and upload the 'latest-replies.php' plugin to your 'my-plugins' folder.


That's it!

== Configuration ==

None.

== Frequently Asked Questions ==

None (yet).

== Screenshots ==

Not applicable


CHANGE LOG

2007-03-15  ver. 0.2 can now display individual user's latest replies in their profile
2007-03-14	ver. 0.1 relased