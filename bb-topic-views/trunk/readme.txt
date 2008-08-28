=== bb-Topic-Views ===
Plugin Name: bb-Topic-Views
Plugin URI: http://bbpress.org/plugins/topic/bb-topic-views/
Tags: views, count, topic views, _ck_
Author: wittmania
Author URI: http://blog.wittmania.com
Contributors:  _ck_
Stable Tag: 1.6
Requires at least: 0.8
Tested up to: 1.0

bb-Topic-Views counts and displays the number of times each topic has been viewed.

== Description ==

bb-Topic-Views keeps track of how many times each topic has been viewed, and then displays
the count alongside the title of the topic on the front page, on forums pages, and on tags
pages.

The plugin is written in such a way that it does not double-count views when a visitor
browses to a different page in the same topic.  If no view count record exists for a specific
topic, the plugin will create a record for it.  Rather than setting the initial view count
to zero, the plugin sets it to the number of posts in the topic, because it has obviously
been viewed at least as many times as people have posted in it!  This is especially nice
for adding the plugin to existing bbpress forums so the view count isn't zero for every
single topic.

== Installation ==

1.  Upload bb-topic-views.php to your /my-plugins/ directory.
2.   If you use the Page Links plugin, you may want to change the way this plugin handles the page links.
The default is to insert a break at the end of the title, so the pages (if they exist) show up on a new line
below the rest of the title.

== Configuration ==

Function calls:

most_viewed_list (parameters) - 
	Parameters accepted:
	$list_length (default is 10), $before_list (default is "<ul>"), $after_list (default is "</ul>"), $before_item (default
	is "<li>"), $after_item = "</li>")

	Use this function anywhere you want to show a list of your most viewed topics.

most_viewed_table (parameters) - 
	Parameters accepted:
	$list_length (default is 10)

	Use this function on the front/forum/tags pages to insert a table of your most-viewed posts.  Again, if you are not
	using the default theme you may have to make some changes to the styling of the table.

show_view_count () - 
	If used in a "topic loop," this function will display the number of times a topic has been viewed.

To add a view count column to front page, forum, and/or tags page tables, make the following modifications.
Note: these apply to the default Kakumei theme.  If you are using a different theme, or a highly modified version
of the default, your modifications may be slightly different.

1.  Open bb-topic-views.php and change $append_to_title to zero.  This will prevent the view count from
being appended to the end of the topic title.
2.  Open front-page.php.  Find <table id="latest"> (around line 21).  Replace everything between this opening
tag and </table> (around line 46) with the following:

<table id="latest">
<tr>
	<th><?php _e('Topic'); ?> &#8212; <?php new_topic(); ?></th>
	<th><?php _e('Views'); ?></th>
	<th><?php _e('Posts'); ?></th>
	<th><?php _e('Last Poster'); ?></th>
	<th><?php _e('Freshness'); ?></th>
</tr>

<?php if ( $super_stickies ) : foreach ( $super_stickies as $topic ) : ?>
<tr<?php topic_class(); ?>>
	<td><?php _e('Sticky:'); ?> <big><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></big></td>
	<td class="num"><?php show_view_count(); ?></td>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><small><?php topic_time(); ?></small></td>
</tr>
<?php endforeach; endif; ?>

<?php if ( $topics ) : foreach ( $topics as $topic ) : ?>
<tr<?php topic_class(); ?>>
	<td><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></td>
	<td class="num"><?php show_view_count(); ?></td>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><small><?php topic_time(); ?></small></td>
</tr>
<?php endforeach; endif; ?>
</table>

3.  Open forum.php and make the same changes.
4.  Open tag-single.php.  Find the opening and closing <table> tags (around line 11 and line 27) and replace
everything between them with the following:

<table id="latest">
<tr>
	<th><?php _e('Topic'); ?> &#8212; <?php new_topic(); ?></th>
	<th><?php _e('Posts'); ?></th>
	<th><?php _e('Views'); ?></th>
	<th><?php _e('Last Poster'); ?></th>
	<th><?php _e('Freshness'); ?></th>
</tr>

<?php foreach ( $topics as $topic ) : ?>
<tr<?php topic_class(); ?>>
	<td><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></td>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php show_view_count(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><small><?php topic_time(); ?></small></td>
</tr>
<?php endforeach; ?>
</table>

5.  You can rearrange the order of the columns if you'd like, just make sure you get them in the right order for 
each section.
	
== Example ==

You can see this plugin in action at http://blog.wittmania.com/bbpress or http://bbshowcase.org/forums/
