=== Skip Akismet ===
Tags: akismet, spam, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Defines a list of roles (ie. moderator) that should never be checked against the Akismet spam filter to prevent false positives. Works in both bbPress and WordPress. 

== Description ==

Defines a list of roles (ie. administrator, moderator) that should never be checked against the Akismet spam filter to prevent false positives. 

You can also use the profile checkbox for ignoring "throttle limit" to specifiy skipping for individual users.

Plugin works in both bbPress and WordPress. 

== Installation ==

* Add the `skip-akismet.php` file to bbPress' `my-plugins/` directory and activate.

== Frequently Asked Questions ==

 = How do I add other roles?  =

* edit the first line near the top of the plugin

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-05-04) =

* first public release

= Version 0.0.2 (2009-05-06) =

* complete rewrite for clearer functionality and deep integration compatibility

= Version 0.0.3 (2009-05-22) =

* yet another rewrite for better performance

= Version 0.0.4 (2009-05-27) =

* also skip WordPress moderation checks (for bad keywords, etc.) if role is on override list

== To Do ==

* admin menu ?
