=== Cross Cookie ===
Tags: login, cookie, cookies, domains, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Login users across multiple domain names with bbPress 0.9 and/or WordPress installs that share the same user database table. Requires WP 2.5 cookie method (use Ann's plugin for WP 2.7+)

== Description ==

Now your forums / blogs can have simultaneous logins and share the user across multiple domain names, not just sub-domains.

Sets login cookie across multiple domain names for bbPress 0.9 and/or WordPress installs that share the same user database table. 

This plugin does NOT currently work with bbPress 1.0, only 0.9

If using WordPress 2.6, 2.7 or 2.8, requires WP 2.5 cookie method (use Ann's plugin)

Plugin works in both bbPress and WordPress. 

== Installation ==

* Setup your bbPress and/or WordPress installs as if they were fully integrated, they must be sharing the same user database table.

* Make sure your cookies on all installs are set to the webroot of your site ( / ) and they are using dotted domains  ( .example.com )

* Edit near the top of `cross-cookie.php` and change the url to the OTHER bbpress/wordpress installation

* Add the `cross-cookie.php` file to each install's plugin directory and activate

* Make sure you tell WP-Cache to exclude any url with  ?cc 

== Frequently Asked Questions ==

* I'm sure there will be some soon
 
== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2009-05-10) =

* first public release

== To Do ==

* admin menu ?

* bbPress 1.0 & WP 2.7-2.8 compatibility
