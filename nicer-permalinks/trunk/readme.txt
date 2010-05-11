=== Nicer Permalinks ===
Contributors: mr_pelle
Tags: permalinks, mod_rewrite, htaccess, slug, forum, topic
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Version: 3.5
Requires at least: 1.0.2
Tested up to: 1.0.2

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

== Description ==

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.

Automatically updates and backups `.htaccess` on activation and restores it on deactivation (if files permissions are correctly set).

== Installation ==

* Copy plugin folder into `my-plugins` folder.

* Open `nicer-htaccess` and change the forum path used by <em>RewriteBase</em> into your forum path, if different.

* If name based permalinks aren't activated yet, turn them on (check under "Settings" admin submenu for "Permalinks").

* Open `bb-post.php` and switch lines 46 and 48 (<a href="http://trac.bbpress.org/browser/trunk/bb-post.php">bbPress 1.0.3 will fix this</a>). They must look like this:

<blockquote>`$topic = get_topic( $topic_id, false );
$link = get_post_link($post_id);`
</blockquote>

* Choose one of the following:

= Option #1: automatic update =

* Open terminal, go into bbPress root folder and change `.htaccess` permissions: `sudo chmod -v 666 .htaccess`

* Go to plugin folder and change `nicer-htaccess` permissions: `sudo chmod -v 666 nicer-htaccess`

* Activate plugin.

* Restore original files permissions (usually "644"). Remember to set them back to "666" before deactivating the plugin, though.

= Option #2: manual update =

* Open `.htaccess`, replace all its content with `nicer-htaccess` content and save.

* Activate plugin.

== Frequently Asked Questions ==

= Search links are broken. =

* I know; <a href="http://trac.bbpress.org/ticket/1212">bbPress 1.0.3 will fix this</a>, meanwhile you may edit lines 942-943 of `/bb-includes/class.bb-query.php` changing every `forum-id` in `forum_id` <a href="http://bbpress.org/forums/topic/broken-forum-search-forum-id-vs-forum_id#post-61715">as described here</a>.

= "Relevant posts" search links are still broken. =

* I know; <a href="http://trac.bbpress.org/ticket/1222">bbPress 1.0.3 will fix even this</a>, meanwhile you may remove related code from your custom `search.php` since it's not vital.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 3.4.1 beta (2010-05-04) =

* PHP 4 compatibility added

= Version 3.5 (2010-05-10) =

* nicer_bb_slug_sanitize_filter removed

* nicer-htaccess code revisited

* source code cleaned