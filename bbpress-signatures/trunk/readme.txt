=== bbPress Signatures ===
Tags: signature, signatures, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

== Description ==

This plugin allows users to add signatures to their forum posts.
It extends their edit profile options automatically.
There are several powerful options for administrators.

== Installation ==

Add the `bb-signatures.php` file to bbPress' `my-plugins/` directory.
Activate and check under "Site Management" admin submenu for "Signatures".

If you would like the optional toggle on new/edit posts to allow the member to disable signatures  on a per-post basis, 
you must edit  the  edit-form.php  & post-form.php   templates and place at or near the bottom:  
`<?  bb_signatures_checkbox(); ?>`
(you can optionally wrap that in a DIV and float it to the left, right style anyway you'd like)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.11 (2007-07-31) =

* first public release

= Version 0.15 (2008-01-30) =

*	signatures removed from rss feeds
*	bug fix to maintain setting when posts are saved after edit 

= Version 0.1.7 (2008-03-31) =

*	bug workaround for is_topic() failure to maintain signature toggle during edits
