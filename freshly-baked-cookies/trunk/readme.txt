=== Freshly Baked Cookies for bbPress ===
Tags: integration, wordpress, cookies, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9.0.4
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows bbPress 0.9 to use WordPress 2.7 or 2.8 cookies during stand-alone (simple) integration.

== Description ==

Allows bbPress 0.9 to use WordPress 2.7 or 2.8 cookies during stand-alone (simple) integration.

This plugin is very alpha and has only been tested on a very basic setup. It should be safe to use as it doesn't change any data.

It can only be used on stand-alone (simple) integration, not "deep" or "full" integration.

== Installation ==

* First configure all your integration settings properly in both bbPress and WordPress.

* Then adjust the first two "define" lines at the top of this plugin as required.

* Add the `_freshly-baked-cookies.php` file to bbPress' `my-plugins/` directory. 

* Activation is automatic on plugins with leading underscores.

* Log out if necessary and log in. Check to make sure that both bbPress and WordPress sees you as logged in (or out) at the same time.

* If you have any trouble, just delete the plugin and the previous cookie method will be used instead.

* REMEMBER: you must set the path and domain of your cookies to be inclusive in both WP and bbPress (ie. root / )

== Frequently Asked Questions ==

*  coming soon

* REMEMBER: you must set the path and domain of your cookies to be inclusive in both WP and bbPress (ie. root / )
ie. If WordPress is under  "/blog/"    and   bbPress is under "/forums/"  the default cookie paths will not work for you.
However if bbPress is "underneath" WordPress, you may not have to adjust anything.
See my general integration guide here:
http://bbpress.org/forums/topic/wordpress-and-bbpress-integration-101

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2009-02-26) =

* first public alpha release

== To Do ==

* admin menu
