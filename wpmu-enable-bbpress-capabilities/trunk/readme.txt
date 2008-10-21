=== WPMU Enable bbPress Capabilities ===

Tags: wpmu enable capabilities member
Contributors: eagano
Requires at least: 1.0 alpha
Tested up to: 1.0-alpha-2
Stable tag: 1.0-alpha-2

== Description ==
	
This is a WPMU plugin (probably works on standalone) that will create usermeta data for bb_press
when a new user is created in WPMU. The default level for bbPress is 'member'. This is useful
if you are integrating bbPress with a WordPress install, and you are only allowing user registration
on the WordPress site. When a user clicks on the activation link in the user regsitration email,
user meta data is added to wp_usermeta to enable the 'member' role in bbPress.
	
== Installation ==

Upload the wpmu-enable-bbpress-capabilities.php file into the wp-content/mu-plugins directory of your
Wordpress install.

== License ==

CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== History ==

0.1     Initial release

== To Do ==
- logic to determine bbPress role based on WordPress role


