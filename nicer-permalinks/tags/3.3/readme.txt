=== Nicer Permalinks ===
Contributors: mr_pelle, Ashish Mohta, Mark Robert Henderson
Tags: permalinks, mod_rewrite, htaccess, slug, topic, forum, Mohta, Henderson
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Version: 3.3
Requires at least: 1.0.2
Tested up to: 1.0.2

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

== Description ==

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark Robert Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Topic Forum</a> plugin.

Automatically updates and backups `.htaccess` on activation and restores it on deactivation (if files permissions are correctly set).

Note: you <strong>must</strong> turn name based permalinks on <strong>before</strong> activating the plugin.

== Installation ==

* First of all, if name based permalinks aren't activated yet, turn them on (check under "Settings" admin submenu for "Permalinks").

* Copy plugin folder into `my-plugins` folder.

* Choose one of the following:

= Option #1: automatic update =

* Open terminal, go into bbPress root folder and change `.htaccess` permissions: `sudo chmod -v 666 .htaccess`

* Go to plugin folder and change `nicer-htaccess` permissions: `sudo chmod -v 666 nicer-htaccess`

= Option #2: manual update =

* Open `.htaccess`, replace all its content with `nicer-htaccess` content and save.

= Then =

* Activate plugin.

* Optional: restore original files permissions. Remember to set them back to "666" before deactivating the plugin, though.

== Frequently Asked Questions ==

= Can I manually edit `.htaccess` and then activate the plugin? =

* As of version 3.1, yes. Make sure to do a backup copy before saving! ;)

= Search links are broken. =

* I know; <a href="http://trac.bbpress.org/ticket/1212">bbPress 1.0.3 will fix this</a>, meanwhile you may edit lines 942-943 of `/bb-includes/class.bb-query.php` changing every `forum-id` in `forum_id` <a href="http://bbpress.org/forums/topic/broken-forum-search-forum-id-vs-forum_id#post-61715">as described here</a>.

= "Relevant posts" search links are still broken. =

* I know; <a href="http://trac.bbpress.org/ticket/1222">bbPress 1.0.3 will fix even this</a>, meanwhile you may remove related code from your custom `search.php` since it's not vital.

== To Do ==

* check if "Relevant posts" search links will work <a href="http://trac.bbpress.org/milestone/1.0.3">under bbPress 1.0.3</a>.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 1.1 (2009-09-19) =

* <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">original Ashish Mohta and Mark Robert Henderson's version</a>

= Version 2.0 (2010-04-12) =

* created `readme.txt`

* added update/restore `.htaccess` functions

= Version 2.1 (2010-04-13) =

* modified `nicer-htaccess` to correct many issues

= Version 3.0 (2010-04-14) =

* added nicer `get_post_link` filter which also handles `get_topic_last_post_link` requests, which are slightly different

* added nicer `bb_get_forum_bread_crumb` filter

= Version 3.1 (2010-04-15) =

* simplified and enhanced update/restore `.htaccess` functions: now manually editing `.htaccess` and then activating the plugin is possible

* added FAQ section

= Version 3.2 (2010-04-16) =

* enhanced nicer `get_topic_link` filter: now it also handles requests coming from admin pages, which are slightly different

* simplified all filters removing redundant code

* added official bbPress Plugin Browser URI

= Version 3.3 (2010-04-16) =

* enhanced nicer `get_topic_link` filter: now it also handles requests coming from views

* slightly changed nicer `bb_get_forum_bread_crumb` filter because was messing up some views by <a href="http://bbpress.org/plugins/topic/my-views">"My Views" plugin</a>