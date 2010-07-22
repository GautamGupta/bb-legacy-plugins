=== Freshly Baked Cookies for bbPress ===
Tags: integration, wordpress, cookies, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows bbPress 0.9 to use WordPress 2.6, 2.7 or 2.8, 2.9, 3.0 cookies during stand-alone (simple) integration.

== Description ==

Allows bbPress 0.9 to use WordPress 2.6, 2.7 or 2.8, 2.9, 3.0 cookies during stand-alone (simple) integration.

This plugin  only been tested on a very basic setup, however it should be safe to try as it doesn't change any data and others report success when using it.

It can only be used on stand-alone (simple) integration, not "deep" or "full" integration.

NOTE: you cannot downgrade from bbPress to 1.0 to 0.9 without converting database, this plugin does NOT do that for you.

== Installation ==

* First configure all your integration settings properly in both bbPress and WordPress.

* Then adjust the first two "define" lines at the top of this plugin as required.

* Add the `_freshly-baked-cookies.php` file to bbPress' `my-plugins/` directory. 

* There *must* be a leading underscore on the plugin filename so it's automatically activated.

* Log out if necessary and log in. Check to make sure that both bbPress and WordPress sees you as logged in (or out) at the same time.

* If you have any trouble, just delete the plugin and the previous cookie method will be used instead.

* REMEMBER: you must set the path and domain of your cookies to be inclusive in both WP and bbPress (ie. root / )

* NOTE: you cannot downgrade from bbPress to 1.0 to 0.9 without converting the database, this plugin does NOT do that for you.

== Frequently Asked Questions ==

*  coming soon

* REMEMBER: you must set the path and domain of your cookies to be inclusive in both WP and bbPress (ie. root / )
ie. If WordPress is under  "/blog/"    and   bbPress is under "/forums/"  the default cookie paths will not work for you.
However if bbPress is "underneath" WordPress, you may not have to adjust anything.
See my general integration guide here:
http://bbpress.org/forums/topic/wordpress-and-bbpress-integration-101

* Alternately you can try this other method of downgrading the cookies in WordPress 2.7 to the old 2.5 method which will work with bbPress 0.9 (and might work with deep integration) http://superann.com/2009/02/26/wordpress-26-27-bbpress-09-cookie-integration-plugin/

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== To Do ==

* admin menu

== Changelog ==

= Version 0.0.1 (2009-02-26) =

* first public alpha release

= Version 0.0.3 (2009-03-12) =

* more complete WP cookie emulation with auth + logged_in cookies - props Txanny

= Version 0.0.4 (2009-07-14) =

* updated to support additional cookie changes in WordPress 2.8

