=== Edit History ===
Tags:  history, revisions, rollback, undo, change, changes, changeset, wiki,_ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Allows you to see a detailed history of exactly what has been changed in any post and optionally rollback (undo) to a previous edit. Uses a word based difference algorithm to minimize storage requirements instead of saving the entire previous post on each edit (ie. changing one word only uses a few bytes).

== Description ==

Allows you to see a detailed history of exactly what has been changed in any post and optionally rollback (undo) to a previous edit. Uses a word based difference algorithm to minimize storage requirements instead of saving the entire previous post on each edit (ie. changing one word only uses a few bytes).

== Installation ==

* This is a public beta release not yet intended for use on active forums. Please report bugs and other feedback.

* Until an admin menu is created, edit `edit-history.php` and change settings near the top as desired

* Add the `edit-history.php` file to bbPress' `my-plugins/` directory and activate

== Frequently Asked Questions ==

= Why does the "History" link disappear? =

* The History link is tied to the Edit link (for now) as there's no other easy way to tie into the post action buttons. But anyone who is qualified to see the Edit link (ie. moderators/admin) will see History. Regular members see it only for an hour (or whatever you have set to for edit timeout).

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-04-21) =

* first public release for beta test

== To Do ==

* undo/rollback post feature

* admin menu

