=== bbPress Signatures ===
Tags: signature, signatures, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

== Description ==

This plugin allows users to add signatures to their forum posts.
It extends their edit profile options automatically.
There are several powerful options for administrators.

== Installation ==

Add the `bb-signatures.php` file to bbPress' `my-plugins/` directory.
Activate and check under "Site Management" admin submenu for "Signatures".

If you would like the optional toggle on new/edit posts to allow the member to disable signatures  on a per-post basis, 
you must edit  the  edit-form.php  & post-form.php   templates and place at or near the bottom:  
`
<?php  bb_signatures_checkbox(); ?>
`
(you can optionally wrap that in a DIV and float it to the left, right style anyway you'd like)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

* 0.05 	: slashes & autop fixed, replaced input with textarea, max_lines now supported in post-processing,  max_length checked in realtime (as well as post processing)
* 0.06 	: internal testing/bugfix
* 0.07 	: per-post signature toggle
* 0.08 	: toggle for allow html and allow images should now work
* 0.10 	: basic functioning admin menu
* 0.11	: more intelligent admin menu
* 0.12	: attempted fix at in_array error for disabling posts 
* 0.13	: warnings cleanup for better code
* 0.14	: signatures removed from rss feeds
* 0.15	: bug fix to maintain setting when posts are saved after edit 
* 0.16	: minor bug fixes

= Version 0.11 (2007-07-31) =

* first public release

= Version 0.15 (2008-01-30) =

*	signatures removed from rss feeds
*	bug fix to maintain setting when posts are saved after edit 

= Version 0.1.7 (2008-03-31) =

*	bug workaround for is_topic() failure to maintain signature toggle during edits

= Version 0.1.8 (2008-04-06) =

* 	boost plugin priority to 5 so signatures are inserted before bbcode-lite runs (allowing bbcode in signatures)

= Version 0.2.0 (2009-01-04) =

*	externalize admin functions, save options on activation to spare extra mysql query