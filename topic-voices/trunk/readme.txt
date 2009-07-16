=== Topic Voices ===
Tags: topics, posts, users, voices, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Displays how many unique users have posted in a topic (aka "voices"). Gives bbPress 0.9 the same ability as 1.0

== Description ==

Displays how many unique users have posted in a topic (aka "voices"). Gives bbPress 0.9 the same ability as 1.0

You can install this now as a plugin and when you upgrade to 1.0, simply de-activate it and the data will remain compatible.

Since it uses the same function names as 1.0, template edits will also remain compatible.

== Installation ==

* Add the `topic-voices.php` file to bbPress' `my-plugins/` directory and activate.

* It is recommended but not required to go to the Manage->Recount admin menu and recount voices.

* Edit your templates as desired to display voices in your topic lists (front-page.php forum.php view.php tag.php) and on the topic.php template itself.

* The two added functions are `bb_get_topic_voices();`  and `bb_topic_voices();`

* For example, you can add a column like so on your front-page.php
`
	<th><?php _e('Posts'); ?></th>
	<th><?php _e('Voices'); ?></th>
	<th><?php _e('Last Poster'); ?></th>
`
`
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php bb_topic_voices(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
`

== Frequently Asked Questions ==

* I'm sure there will be some eventually

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-07-11) =

* first public release

== To Do ==

* it's pretty much done
