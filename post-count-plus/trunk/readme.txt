=== Post Count Plus - Dynamic Titles, Colors & More! ===
Tags: posts, count, post count, titles, custom titles, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9.0.2
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

== Description ==

Not just yet-another plugin to display the total post count for each user next to their posts, 
but has many customization options for colors and format. Also caches all counts in meta 
for the  fastest performance possible with the fewest database queries.

== Frequently Asked Questions ==

* No template edits required of any kind.

* Optionally  shows join date (member since)  next to posts, as well as 
custom titles based on either post count or membership length & role.

* A demo can be seen at any topic on http://bbShowcase.org/forums/
good example: http://bbshowcase.org/forums/topic/new-bbpress-theme-futurekind

= Why does the user's info repeat twice in their posts? =

* This is a bug in an early version of bbPress 1.0 alpha. Please upgrade your bbPress to the newest 1.0

== Installation ==

Add the `post-count-plus.php` file to bbPress `my-plugins/` directory and activate. 
You do not need to edit any template (theme) pages for any of the features.

Admin menu is under `Site Management` -> `Post Count Plus`
and offers many, many options to control how the user info appears next to posts.

For advanced users, you can get post count and other info displayed in 
any template by using direct calls, ie. `<?php post_count_plus($user->ID); ?>`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 1.00 (2008-02-10) =

* first public release

= Version 1.03 (2008-02-16) =

* bug fix to deal with user->bb_capabilities vs user->capabilities in trunk vs 0.8.x

= Version 1.06 (2008-02-20) =

* workaround for 0.8.2 backwards compatibility

= Version 1.07 (2008-02-23) =

* a few tweaks, minor bug fixes to admin side
* tries to enforce proper role name entry (no spaces & lowercase) - todo: selection box instead

= Version 1.1.2 (2008-04-27) =

* don't activate if inside a feed 

= Version 1.1.6 (2008-08-31) =

* admin function externalized to reduce code size for most users
* bug fix for automatic post count inserted into profile
* now compatible with 1.0

= Version 1.1.7 (2008-12-22) =

* bug fixes for when detecting user id on non-topic pages 

= Version 1.1.8 (2008-12-31) =

* optionally include WordPress comment counts in total post counts (does not update automatically from WordPress side yet)

== To Do ==

* Better instructions for all the options, code cleanup
