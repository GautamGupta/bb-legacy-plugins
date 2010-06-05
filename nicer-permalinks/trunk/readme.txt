=== Nicer Permalinks ===
Contributors: mr_pelle
Tags: permalinks, mod_rewrite, htaccess, slug, forum, topic
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Version: 3.6.2
Requires at least: 1.0
Tested up to: 1.1-alpha

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

== Description ==

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.

Automatically updates and backups `.htaccess` and restores it when deactivating (if files permissions are correctly set).

== Installation ==

* Copy plugin folder into `my-plugins` folder.

* Open `nicer-htaccess` and change the forum path used by <em>RewriteBase</em> into your forum path, if different.

* If name based permalinks aren't activated yet, turn them on (check under "Settings" admin submenu for "Permalinks").

* Open `bb-post.php` and switch lines 46 and 48 (bug fixed in bbPress 1.1-alpha). They must look like this:

<blockquote>
`$topic = get_topic( $topic_id, false );

$link = get_post_link($post_id);`
</blockquote>

* Choose one of the following:

= Option #1: automatic update =

* Change `.htaccess` permissions to read+write.

* Change `nicer-htaccess` permissions the same way.

* Activate plugin.

* Restore `.htaccess` original permissions (usually read only). Remember to set them back to read+write before deactivating the plugin!

= Option #2: manual update =

* Backup your `.htaccess`.

* Replace `.htaccess` content with `nicer-htaccess`'s.

* Activate plugin.

== Frequently Asked Questions ==

= Error on deactivation =

* If you performed manual update: first restore your original `.htaccess` and then deactivate the plugin.

* If you performed automatic update: did you set both `.htaccess` and `nicer-htaccess` permissions to read+write before plugin deactivation?

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 3.4.1 beta (2010-05-04) =

* PHP 4 compatibility added

= Version 3.5 (2010-05-10) =

* nicer_bb_slug_sanitize_filter removed

* nicer-htaccess code revisited

* source code cleaned

= Version 3.6 (2010-05-13) =

* nicer_get_post_link_filter enhanced

* bug with <a href="http://bbpress.org/plugins/topic/ajaxed-quote/">Ajaxed Quote</a> plugin fixed

= Version 3.6.1 (2010-05-26) =

* redirection link when deleting a topic from its own page fixed (bug fixed in bbPress 1.1-alpha)

= Version 3.6.2 (2010-06-05) =

* minor changes