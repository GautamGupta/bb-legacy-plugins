=== Admin Can Post Anything ===
Tags: admin, administration, images, flash, youtube, javascript, embed, _ck_
Contributors: _ck_
Requires at least: 0.8
Tested up to: 0.8.2.1
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows administrators to post any content regardless of tag restrictions, including javascript and flash video embedding.

== Description ==

With this plugin, keymaster/administrators may include any markup in their posts.  
All checks are removed for content while some light formatting is still applied.
For example, you could post youtube embed code without bbPress stripping it.

== Installation ==

Add the `admin-can-post-anything.php` file to the bbPress `my-plugins/` directory and activate.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

Version 0.04 (2007-07-15)

* attempt at co-existance with "allow images" plugin

Version 0.05 (2007-08-08)

* improved pre-filter before autop to preserve newlines between script/embed tags instead of <br />
* stripslashes added to fix improper slashes added by autop

Version 0.1.0 (2008-12-12)

* re-write of general logic to try to handle more conditions
* feature is optional per-post and can be toggled on by admin for any specific post
* if tags are detected in a post your are editing that are not allowed by default, feature automatically turns on
* note this method disables automatic paragraphs and uses line breaks instead
* this whole plugin is much harder than one might think!
