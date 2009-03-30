=== WordPress-bbPress syncronization ===
Contributors: bobrik
Tags: bbPress, post, comment, integration, forum
Requires at least: 1.0alpha6
Tested up to: 1.0alpha6
Stable tag: 0.3

Sync your WordPress comments to bbPress forum and back.

== Description ==

THIS IS TWO PARTS PLUGIN! YOU NEED TO SET UP SAME PLUGIN FOR BBPRESS TOO!

When you post comment to WordPress it automatically mirroring in bbPress and back.
Please note, only beta now. See below how you can help.

REQUIREMENTS:

*   php-curl module must be installed (may be changed in next versions)
*   WordPress user database must be integrated with bbPress
*   Plugin must be installed and correctly configured in both systems

DONE:

*   Creating topic for new comments on post and continuing conversation
*   Mirroring comment/forum post changes WordPress to bbPress and back
*   Mirroring comment/forum post status (spam, unapproved, approved)
*   Anonymous comment mirroring
*   Syncronizing conversation status (open or closed)
*   Using secret key authorization between parts
*   Settings page
*   Syncronizing plugin status in WordPress and bbPress
*   bbPress forum selection
*   Showing post excerpt in bbPress topic beginning
*   Optional quoting first post in topic

TODO:

*   Catch post deletion in WordPress
*   Catch topic deletion in bbPress
*   WordPress anonymous user info sycronization

YOU MAY HELP:

*   Coding
*   Translation (heh, you may help with English version too)
*   Bug reporting

Made for news.vitebsk.cc

== Changelog ==

Version 0.4 (dd.mm.yyyy):

*   Updating topic title with post title updating
*   Checking for plugin activity state
*   Applying filters before syncronization, you may widely use plugins
*   New post option, now you may switch comment syncronization on/off for post
*   Variuos bugfixes

Version 0.3 (28.03.2008):

*   Fixed broken links inserting in first topic post

Version 0.2 (27.03.2008):

*   Showing post excerpt in bbPress topic beginning
*   Optional quoting first post in topic
