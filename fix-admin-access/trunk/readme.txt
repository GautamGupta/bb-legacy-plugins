=== Fix Admin Access ===
Tags: integration, admin, keymaster, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

== Description ==

Restores keymaster access to control panel on bbPress and/or WordPress integration. 
This is an auto-load plugin and must have a leading underscore "_". 
Put into your plugin directory, access bbPress and/or WordPress once and then delete it from the directory.

== Installation ==

* This is an auto-load plugin and must have a leading underscore "_". Put into your plugin directory, access bbPress and/or WordPress once and then delete it from the plugin directory.

== Frequently Asked Questions ==

* This plugin assumes your keymaster is user id #1. It is rare but possible you are no longer user #1 if you have deleted your original keymaster. In which case this won't work. Future versions may be able to find other admin users to fix.
 
* Do not leave this plugin installed. Once it fixes the problem it should be deleted from the server.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.01 (2008-02-15) =

* first public release
	
== To Do ==

* search for other admin users to fix based on various possible settings
