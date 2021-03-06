=== WordPress-bbPress syncronization ===
Contributors: bobrik
Tags: bbPress, post, comment, integration, forum
Requires at least: 1.0alpha6
Tested up to: 1.0alpha6
Stable tag: 0.4.3

Sync your WordPress comments to bbPress forum and back.

== Description ==

**THIS IS TWO PARTS PLUGIN! YOU NEED TO SET UP SAME PLUGIN FOR BBPRESS TOO!**

**Please install latest versions of both parts!**

When you post comment to WordPress it automatically mirroring in bbPress and back.
Please note, only beta now. See below how you can help.

REQUIREMENTS:

*   php-curl module must be installed (may be changed in next versions)
*   WordPress user database must be integrated with bbPress
*   Plugin must be installed and correctly configured in both systems

NOTES:

*   Please install plugin into **my-plugins** directory, **not bb-plugins**

DONE:

*   Creating topic for new comments on post and continuing conversation
*   Mirroring comment/forum post changes WordPress to bbPress and back
*   Mirroring comment/forum post status (spam, unapproved, approved)
*   Per-post enable/disable for comment syncronization
*   Anonymous comment mirroring
*   Showing anonymous userinfo in forum
*   Syncronizing conversation status (open or closed)
*   Using secret key authorization between parts
*   Settings page
*   Syncronizing plugin status in WordPress and bbPress
*   bbPress forum selection
*   Showing post excerpt in bbPress topic beginning
*   Optional quoting first post in topic
*   Correct displaying even you use markup plugins

TODO:

*   Catch post deletion in WordPress
*   Catch topic deletion in bbPress

YOU MAY HELP:

*   Coding
*   Translation (heh, you may help with English version too)
*   Bug reporting

Made for news.vitebsk.cc

Changelog

Version 0.4.3 (21.04.2009)

*   Fixed bug with incorrect topic author starter (and some related bugs)
*   Fixed bug with incorrect continuing topic without creating it
*   Added option for creating topic even if comment not approved
*   Added screenshots

Version 0.4.2 (18.04.2009)

*   Fixed bug with WordPress post page markup
*   Added Copyright to bbPress part
*   Code cleanups
*   Showing anonymous user info in bbPress (optionally)
*   Option for syncronization of all/only approved comments/posts
*   Fixed bug with incorrect comment author after editing
*   Performance improvements
*   Handling WordPress comment deletion

Version 0.4.1 (31.03.2009):

*   Added option for default comments syncronization status setting
*   Fixed bug with incorrect sycronization
*   Fixed bug with escaping html codes in syncronization

Version 0.4 (31.04.2009):

*   Updating topic title with post title updating
*   Checking for plugin activity state
*   Applying filters before syncronization, you may widely use plugins
*   New post option, now you may switch comment syncronization on/off for post
*   Variuos bugfixes

Version 0.3 (28.03.2009):

*   Fixed broken links inserting in first topic post

Version 0.2 (27.03.2009):

*   Showing post excerpt in bbPress topic beginning
*   Optional quoting first post in topic

== Screenshots ==

1. WordPress part settings
2. bbPress part settings
