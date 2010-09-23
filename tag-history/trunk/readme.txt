=== Tag History ===
Tags: _ck_, administration, moderation, tags, tag
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Helps administrators figure out who tagged what, when, by exploring tagging history.

== Description ==

With this plugin administrators may explore the complete tag history of all topics by all users.

This can be helpful to make sure spammers have not snuck in any tags or find misbehaving new users.

Currently only works with bbPress 0.9

== Installation ==

Add the entire `tag-history/` directory to the bbPress `my-plugins/` directory.

Activate and check under the "Manage" menu for the new Tags submenu.

== Frequently Asked Questions ==

= Why doesn't it work in bbPress 1.x =

* bbPress 1.x completely changes the tag structure internally and no longer tracks the tagging date.

They literally downgraded the feature and removed capabilities  to match WordPress's clunky design. 

In theory I could eventually make it work if your database tag table has not been optimized or sorted.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.2 (2010-09-23) =

* first public release

== To Do ==

* maybe allow direct mass deletion of tags/history
