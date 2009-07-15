=== Email Login ===
Tags: _ck_,  login, email, e-mail
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows users to login via their email address in addition to username. Works with 0.9.x and 1.0a

== Description ==

* Allows users to login via their email address in addition to username. Works with 0.9.x and 1.0a
(bbPress 1.0 already has email login built-in, though it is not enabled by default)

== Installation ==

* This is an auto-load plugin (and must have a leading underscore `_`). Put into your plugin directory and you're done, no activation required.

* You may also want to install this mini-plugin to prevent duplicate email addresses in bbPress 0.9
http://bbpress.org/forums/topic/registering-with-another-users-email#post-17362

== Frequently Asked Questions ==

* This plugin name must retain the leading underscore for auto-load  ie. `_email-login.php` (don't rename it)

* This plugin won't work with any other plugin that also replaces function bb_login

* Note that email addresses in bbPress 0.9 (and early versions of 1.0a) are fundamentally flawed. 
Two users can have the same email address, and a user can change their email address later to the same as an existing user,
 because there are no checks in the API (fixed eventually in WordPress 2.5 but still not bbPress 0.9)
 This plugin only checks the first email address that matches, which in theory can prevent the login from working.
 
 * I'm not sure why anyone would want to do this but in theory you could force ONLY email addresses for user login (no usernames allowed) 
 by disabling the first real line in the plugin  ie. ` // $user = bb_check_login( $login, $password );`

* It's interesting to note that this plugin can be done in WordPress via two lines of code as a real plugin 
(and not replacing functions in pluggable) by using wp_authenticate - not even bbPress 1.0 has such filters yet

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-03-26) =

* first public release
	
== To Do ==

* maybe fix bbPress's duplicate email problem but that really should be done in the core, not via plugin