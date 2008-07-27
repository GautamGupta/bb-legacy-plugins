=== Forum Last Poster  ===
Tags: last post, last poster, freshness, forums, forum, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Adds `forum_last_poster()` `forum_time()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.

== Description ==

Adds `forum_last_poster()` `forum_time()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.

High performance: requires only a single extra query regardless of the number of forums listed (when topics are on same page).

== Installation ==

* Install `forum-last-posters.php` to  `my-plugins/` and activate

* Edit your `front-page.php` and/or `forum.php` templates to add  FRESHNESS column in the forum list like so:
`<table id="forumlist">

<tr>
	<th><?php _e('Main Theme'); ?></th>
	<th><?php _e('Freshness'); ?></th>
	<th><?php _e('Topics'); ?></th>
	<th><?php _e('Posts'); ?></th>
</tr>

<?php while ( bb_forum() ) : ?>
<tr<?php bb_forum_class(); ?>>
	<td><?php bb_forum_pad( '<div class="nest">' ); ?><a href="<?php forum_link(); ?>"><?php forum_name(); ?></a><small><?php forum_description(); ?></small><?php bb_forum_pad( '</div>' ); ?></td>
	<td class="num"><?php forum_time(); ?></td>
	<td class="num"><?php forum_topics(); ?></td>
	<td class="num"><?php forum_posts(); ?></td>
</tr>
`
(Notice the new Freshness and `forum_time()` lines, compared to the default template)

*  You may also use any of the other topic-like functions in any combination, see the FAQ for the list.

* You may specify a specifc forum by id if using a custom query/template, ie. `forum_time(3)` - otherwise it will automatically figure out the current forum id from the forum loop

== Frequently Asked Questions ==

= What functions are available? =

* forum_last_poster()

* get_forum_last_poster() 

* forum_time()

* get_forum_time()

* forum_last_post_link()

* get_forum_last_post_link() 

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-07-24) =

* first public alpha release

== To Do ==

* instead of "expensive" mysql lookup for every page load, data could be stored upon every new post or deletion - since bbPress 1.0 will have meta for forums (0.9 does not) I will look at this again later
