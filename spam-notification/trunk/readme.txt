=== Spam Notification Email ===
Tags: spam, akismet, notification, email, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 1.1
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Notifies admin when a post is marked as spam by Akismet.

== Description ==

Notifies admin when a post is marked as spam by Akismet.

Posts MANUALLY marked as spam will not trigger emails. 

(do not attempt to purposely spam yourself to test as Akismet may remember your IP & username, etc. across other sites)

== Installation ==

* Add the `spam-notification.php` file to bbPress' `my-plugins/` directory and activate. Check your email when spam occurs.

* You can change/add additonal email addresses at the top of the plugin, the `from_email` system address is the default.

== Frequently Asked Questions ==

= I'm not receiving any emails? =

* If you use Hotmail or AOL, your server must have rDNS and SPF setup. Otherwise try gmail instead which is more tolerant.

* Note that posts MANUALLY marked as spam will not trigger emails. This is a feature as designed.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2010-07-01) =

* first public release
