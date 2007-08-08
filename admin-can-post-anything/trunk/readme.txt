=== Admin Can Post Anything ===
Tags: admin, administration, images, flash, youtube
Contributors: 
Requires at least: 0.8
Tested up to: 0.8.2.1
Stable tag: trunk

Allows keymaster/administrators to post any content regardless of tag restrictions, including javascript and flash video embed.

== Description ==

With this plugin, keymaster/administrators may include any markup in their posts.  
All checks are removed for content - autop still runs after post for formatting.
For example, you could post youtube embed code without bbPress stripping it.

Version 0.04+ includes untested code to allow compatibility with the "allow images" plugin.
It's safe to remove "allow image" filters because this only happens if admin are trying to post.

== Installation ==

Add the `admin-can-post-anything.php` file to bbPress' `my-plugins/` directory. Activate.

== Version History ==

Version 0.04 (2007-07-15)

* attempt at co-existance with "allow images" plugin

Version 0.05 (2007-08-08)

* improved pre-filter before autop to preserve newlines between script/embed tags instead of <br />
* stripslashes added to fix improper slashes added by autop



