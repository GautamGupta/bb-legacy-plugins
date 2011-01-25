=== Forum Last Poster  ===
Tags: last post, last poster, freshness, forums, forum, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Adds `forum_last_poster()`, `forum_time()`, `forum_last_post_link()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.

== Description ==

Adds `forum_last_poster()`, `forum_time()`, `forum_last_post_link()` and other functions to bbPress to mimic the topic tables' FRESHNESS column. Requires simple template edits.

High performance: uses only a single extra query regardless of the number of forums listed (when topics are on same page).

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
	<td class="num"><?php bb_forum_pad( '<div class="nest">' ); ?><a href="<?php forum_link(); ?>"><?php forum_name(); ?></a><small><?php forum_description(); ?></small><?php bb_forum_pad( '</div>' ); ?></td>
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

* forum_last_poster() - echos last poster's name (enhanced by Post Count Plus)

* get_forum_last_poster() - returns last poster's name

* forum_time() - echos last topic time of forum

* get_forum_time()  - returns last topic time of forum

* forum_last_post_link() - echos link to most recent post of most recent topic in forum

* get_forum_last_post_link() - returns link to most recent post of most recent topic in forum

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2008-07-24) =

* first public alpha release

= Version 0.0.3 (2008-08-16) =

* unfortunately the join query does not handle deleted topics correctly and returns no results for that forum, so have to use subquery :-(

= Version 0.0.4 (2008-01-04) =

* cache non-indexed query
* compatibility with bbPress 1.0a5+

= Version 0.0.5 (2009-06-20) =

* slightly faster query by only updating for forum affected by new/deleted post

= Version 0.0.6 (2011-01-25) =

* bugfix: updates when topic is deleted (which was bypassing bbpress delete post api)
* feature: manual update available under admin recount menu

== To Do ==

* nothing yet!