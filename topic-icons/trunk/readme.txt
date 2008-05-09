=== Topic Icons ===
Tags: topics, icons, graphics, support, forums, _ck_
Contributors: _ck_
Requires at least: 0.8.3
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.

== Description ==

Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.

Demo: http://bbshowcase.org/forums/

== Installation ==

* Add the `topic-icons.php` file to bbPress' `my-plugins/` directory and activate.

== Frequently Asked Questions ==

= How can I put the icons into their own column (like on bbshowcase.org) =

* edit the front-page.php, topic.php, tag-single.php, view.php templates

* then for super-stickies, stickies and topics add a new column ie. `<td width="1"><?php topic_icon(); ?></td>`

* then for the forums  add a new column ie. `<td width="1"><?php forum_icon(); ?></td>`

* make the table header span two columns via `colspan="2"` ie. `<th colspan="2"><?php _e('Topic'); ?> &#8212; <?php new_topic(); ?></th>`  and `<th colspan="2"><?php _e('Main Theme'); ?></th>`

= How can I add more icons and keywords/triggers? =

* That requires editing of the icon and rule arrays at the top of the plugin (until an admin menu can be made). More instructions soon.

= How can I stop it from adding icons all over, other than their own column? =

* in the plugin change the top to `$topic_icons['automatic']=false;` (from true). Note that making your own column manually will turn off automatic insertion anyway.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-05-09) =

* first public release

== To Do ==

* admin menu

* handle non-english languages / keywords
