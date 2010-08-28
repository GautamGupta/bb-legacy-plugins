=== Nicer Permalinks ===
Contributors: mr_pelle
Tags: permalinks, mod_rewrite, htaccess, slug, forum, topic
Plugin Name: Nicer Permalinks
Plugin URI: http://bbpress.org/plugins/topic/nicer-permalinks/
Version: 5.0.3
Requires at least: 1.0
Tested up to: 1.1-alpha

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

== Description ==

Rewrites every bbPress URI removing the words "forum" and "topic" and emphasizes forum hierarchy.

Based on <a href="http://www.technospot.net/blogs/">Ashish Mohta</a> and <a href="http://markroberthenderson.com/">Mark R. Henderson</a>'s <a href="http://blog.markroberthenderson.com/getting-rid-of-forums-and-topic-from-bbpress-permalinks-updated-plugin/">Remove Forum Topic</a> plugin.

<strong>Requires PHP 5 or higher.</strong>

== Installation ==

* If your bbPress version is <strong>1.0.2 or lower</strong>, open `bb-post.php` and switch lines 46 and 48. They must look like this:

`$topic = get_topic( $topic_id, false );

$link = get_post_link( $post_id );`

* Copy plugin folder into `my-plugins` folder.

* Activate the plugin and go to plugin configuration page at Plugins > Nicer Permalinks.

* Check prerequisites and enable the plugin.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 5.0.3 (2010-08-28) =

* additional `$topic` type check added (fixes a bug with <a href="http://bbpress.org/plugins/topic/bbpress-sitemap-generator/">bbPress 
Sitemap Generator plugin</a>)

= Version 5.0.2 (2010-08-12) =

* `bb-post.php` editing restored bceause mandatory on bbPress 1.0.2

= Version 5.0.1 (2010-07-30) =

* plugin current status check added to plugin configuration page

= Version 5.0 (2010-07-15) =

* plugin filters completely revised

* no need to hack bbPress core anymore

* performance greatly enhanced: most of the data is now got using global vars instead of bbPress functions

= Version 4.2 (2010-07-12) =

* non-bbPress rules do not get removed anymore by `.htaccess` update

* configuration page and data processing finally do follow bbPress standards

= Version 4.1 (2010-07-05) =

* load of typos fixed

* support for PHP 4 quitted

* PHP version check added

= Version 4.0.1 (2010-06-30) =

* bug with name based permalinks status check fixed

* plugin configuration page now looks for requested status to decide if activate or deactivate the plugin

= Version 4.0 (2010-06-25) =

* plugin files structure revised

* configuration page added

* load of new control functions added

* functions are now more documented

= Version 3.6.3 (2010-06-11) =

* minor changes

= Version 3.6.2 (2010-06-05) =

* minor changes

= Version 3.6.1 (2010-05-26) =

* redirection link when deleting a topic from its own page fixed (bug fixed in bbPress 1.1-alpha)

= Version 3.6 (2010-05-13) =

* nicer_get_post_link_filter enhanced

* bug with <a href="http://bbpress.org/plugins/topic/ajaxed-quote/">Ajaxed Quote</a> plugin fixed

= Version 3.5 (2010-05-10) =

* nicer_bb_slug_sanitize_filter removed

* nicer-htaccess code revisited

* source code cleaned

= Version 3.4.1 beta (2010-05-04) =

* PHP 4 compatibility added