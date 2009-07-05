=== Topic Icons ===
Tags: topics, icons, graphics, support, forums, _ck_
Contributors: _ck_
Requires at least: 0.8.3
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.

== Description ==

Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.

Demo: http://bbshowcase.org/forums/

== Installation ==

* Add the `topic-icons.php` file to bbPress' `my-plugins/` directory and activate.

= Custom Icons =

* First go down to the $topic_icons['graphics']=array(`
section and add the icons you want to the list, selecting a keyword to point to the graphic.
ie.
`'keyword'=>'newgraphic.png'`

* Then go to $topic_icons['rules']=array(	`
and for each keyword, add the words that will trigger the icon.
ie.
`'keyword'=>'word1|word2|word3'`

* Optionally for each forum that you want a "fallback" icon edit `$topic_icons['forums']=array(`
and add the forum number mapped to the keyword
ie.
`'6'=>'keyword'`

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

= How do I know what forum number to use for forum icons? =

* put `?forumlist` at the end of your url (must be logged in as keymaster)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-05-09) =

* first public release

= Version 0.0.3 (2008-05-12) =

* now can specify icons for forums as a fallback (or primary by removing other rules)

= Version 0.0.4 (2008-07-24) =

* IIS bug fix

== To Do ==

* admin menu

* handle non-english languages / keywords
