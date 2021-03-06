=== Swirl Unknowns ===
Contributors: mr_pelle
Tags: redirect, hidden, private, forum, login
Plugin Name: Swirl Unknowns
Plugin URI: http://bbpress.org/plugins/topic/swirl-unknowns/
Version: 1.1
Requires at least: 1.0
Tested up to: 1.1-alpha

Redirects non-logged-in users to a page of your choice.

== Description ==

Redirects non-logged-in users to a page of your choice.

Based on <a href="http://blogwaffe.com/">Michael D. Adams</a>' <a href="http://bbpress.org/forums/topic/117">Force Login</a> plugin plus the <a href="http://bbpress.org/forums/topic/force-login-for-bbpress-101">*voodoo code from Trent Adams and Sam Bauers*</a>.

== Installation ==

* Copy plugin folder into `my-plugins` folder.

* Activate the plugin and go to plugin configuration page at Plugins > Swirl Unknowns.

* Enter the swirl page.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

= Version 1.1 (2010-07-12) =

* wrapper class for plugin settings created

* configuration page and data processing finally do follow bbPress standards

* redirection is now done via `wp_redirect()`, which supports external URLs too

= Version 1.0.2 (2010-07-05) =

* plugin configuration page processing typo fixed

* better plugin activated check

= Version 1.0.1 (2010-06-30) =

* `rss/` added to swirl-immune pages

= Version 1.0 (2010-06-25) =

* plugin files structure revised

* configuration page CSS now mirrors admin pages'

* notices and errors are now displayed via `bb_admin_notice()`

* functions are now more documented

= Version 0.7.1 (2010-06-10) =

* CSS hook added

* minor changes

= Version 0.7 (2010-06-05) =

* percent-substitution tags added

* minor changes

= Version 0.6 (2010-05-10) =

* uninstall function added

* allowed page removed

* source code cleaned

= Version 0.5 (2010-05-03) =

* new version released