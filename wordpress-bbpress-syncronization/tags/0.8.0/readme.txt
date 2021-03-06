=== WordPress-bbPress syncronization ===
Contributors: bobrik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ibobrik@gmail.com&lc=US&item_name=WordPress%20-%20bbPress%20sync%20plugin&currency_code=USD&bn=PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest
Tags: wordpress, post, comment, integration, forum, syncronization, sync, synchronization
Requires at least: 1.0alpha6
Tested up to: 1.0.2
Stable tag: 0.8.0

Sync your WordPress comments to bbPress forum and back.

== Description ==

**YOU NEED TO SET UP SAME PLUGIN FOR WORDPRESS TOO!**

**WordPress part may be downloaded [here](http://wordpress.org/extend/plugins/wordpress-bbpress-syncronization/)**

When you post comment to WordPress it automatically mirroring in bbPress and back. See ChangeLog section to know about new hot features.
Please note that plugin permissions must be 755 (rwxr-xr-x) or at lease 750 (rwxr-x---)

REQUIREMENTS:

*   Optional: php-curl module must be installed for https communication
*   WordPress user database must be integrated with bbPress
*   Plugin must be installed and correctly configured in both systems

NOTES:

*   Please install plugin into **my-plugins** directory, **not bb-plugins**

FEATURES:

*   Creating topic for new comments on post and continuing conversation
*   Creating topic for new posts after publishing
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
*   Hiding comment for in WordPress after some comments and pointing to forum
*   Optionally showing only some recent comments in WordPress
*   Optionally disabling syncing posts back to WordPress from bbPress
*   Automatic redirection of relative links in posts/comments
*   Translations, currently: English, Russian, Belarusian, you may do your own
*   WP template functions: wpbb_forum_thread_exists, wpbb_forum_thread_url (returns string)
*   Adding tag for crossposted forum topics to process them on bbPress side
*   Crossposting to specified forum according post category

YOU MAY HELP:

*   Coding
*   Translation (heh, you may help with English version too)
*   Bug reporting

Made for news.vitebsk.cc

== Changelog ==

Version 0.8.0 (20.09.2009)

*  Spelling errors fixes
*  Windows path fix, again and again
*  Minor usability fixes
*  Fixed "Incorrect URL" trouble for (hope) many installations
*  Fixed EB BB BF unicode problem for some installations
*  Fixed incorrect html attributes handling in some cases
*  Options page performance tweaks, twice faster for bbPress now
*  Ability to tag crossposted topics in bbPress
*  Setting additional meta information for synchronizing data
*  New function to get WordPress post URL on forum
*  Fixed potential bug with incorrect post test on bb side due mysql sort
*  Changed function names to resolve collisions with «deep» integration
*  Crossposting to specified forum according post category

Version 0.7.7 (05.08.2009)

*  More accurate path checking, must fix windows path problems
*  Avoiding funtion name collision with other plugins
*  Admin notice if plugin not enabled correctly
*  Donation link. I want to eat too :)

Version 0.7.6 (01.08.2009)

*  Fixed possible bug with another plugin part version checking due floating number precision
*  Some code cleanups
*  Additional debug info for connection checking. See incorrect url links for more info

Version 0.7.5 (28.07.2009)

*  Fixed bug with crap in wordpress part responses. Now many installations will be fixed :)

Version 0.7.4 (26.07.2009)

*  fixed bug with no comments when plugin disabled
*  More checks before giving link to forum if plugin is inactive

Version 0.7.3 (25.07.2009)

*  Now anonymous comments at the forum have original author name (thanks to Ronen Halevy)
*  Reworked anonymous userinfo displaying
*  Now additional post options applying only if sync enabled
*  'bbPress url' instead 'bbPress plugin url' in WP options, now it's named right
*  Fixed stupid typo: sync_h_ronization

Version 0.7.2 (23.07.2009)

*  Fixed bug with incorrect first post summary for <!--more--> tag
*  Updated plugin homepage links

Version 0.7.1 (23.07.2009)

*  Diagnostical messages for plugin connection checking
*  Better error highlighting on options page

Version 0.7.0 (22.07.2009)

*  Additional checks befor some options setting
*  Belarusian translation (thanks to Ilya aka FatCow)
*  Trackback sync options (disable or show URL as username on forum)
*  Optional disabling syncing comments from bbPress back to Wordpress
*  Template function to get forum link. Now you can place link anywhere
*  Ability to use post excerpt and full post text from WP as first topic post

Version 0.6.0 (06.07.2009)

*  Now plugin checks for availability by another part before any other checks
*  Added showing last topic poster as anonymous name from WP if anonymous info enabled
*  Added ability for translation (gettext template included)
*  Added russian translation (thanks to me, hehe)
*  Nicer plugin administration interface, especially for new bbPress versions
*  More accurate and secure secret keys structure

Version 0.5.2 (02.06.2009)

*  Added some checks for option 'Sync comments by default'
*  Fixed some typos
*  Fixed bug with link to forum in posts with no comments

Version 0.5.1 (28.05.2009)

*  Fixed bug: incorrect behavior with '-1' value for 'Max comments with form'
*  Added link to another part in plugin readme (both WordPress & bbPress)
*  Added option initiation on first use
*  Fixed some typos
*  Code cleanups

Version 0.5.0 (24.05.2009)

*  Made php-curl dependence optional. Now you need it only for https
*  Option for showing link to forum discussion in last comment in WordPress
*  Option for setting amount of latest comments to show in WordPress
*  Option for setting maximal amount of comments to show new comment form
*  Second part version checking

Version 0.4.5 (7.05.2009)

*  Added option to create topic in bbPress after post publishing in WordPress

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
