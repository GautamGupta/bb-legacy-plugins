=== Bbpress Latest Discussion ===
Tags: bbpress, wordpress, latest, discussion
Contributors: Atsutane Shirane
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: 0.8

== License and Warranty ==

This plugin is licensed under the GPL. Because the plugin is licensed free of charge, I do not accept any responsibility for any damages, direct or indirect, that may arise from using the plugin. This software is provided “AS IS” without warranty of any kind. Please refer to the full version of the GPL for more details.

== Description ==

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress page.

== Requirements ==

This plugin requires Wordpress 2.0.5 or better. If you are using an older version, I strongly recommend you to upgrade to the latest version. You can get it at the WordPress Download Page.

== Installation ==

Simply download the Zip-Archive and extract all files into your wp-content/plugins/ directory. Open the `BBpress.php` file using editor program and adjust the path to suit your bbpress location. Then go into your WordPress administration page, click on Plugins and activate it.

== Update ==

Simply download the Zip-Archive, deactivate Bbpress Latest Discussion, extract all files into your wp-content/plugins/ directory and reactivate Bbpress Latest Discussion. That’s all

== Configuration ==

I’ve created three template tags you can use in your pages:

1. `wp_bb_get_discuss()` : Show Bbpress latest discussion on wp static page.
2. `wp_bb_get_discuss_sidebar()` : Show Bbpress latest discussion on wp sidebar.

Examples of use:

`<?php wp_bb_get_discuss(); ?>`

`<?php wp_bb_get_discuss_sidebar(); ?>`

== Changelog ==

Version 0.7 (2007-03-09):

* Fix custom wp table prefix problem.
* Remove bbpress favorite function.

Version 0.6 (2007-03-08):

* Add option to set Bbpress table prefix.
* Change option page name. From “Bbpress Option” into “BbLD Option”.

Version 0.5 (2007-03-04):

* Fix display name for bbpress that not integrated with wordpress.
* Add option to set if your wordpress is integrated with bbpress or not.
* Add use Bbpress permalink option

Version 0.4 (2007-03-04):

* Bbpress Latest Discussion Option Page.
* Ability to set Bbpress url
* Ability to use different database

Version 0.3 (2007-01-17):

* Change the table to use display name instead of using username.
* Enable to set the “number of post”

Version 0.2 (2006-11-11):

* Fix post number bug. (It seem i use the wrong sql table XD)

Version 0.1 (2006-11-05):

* Initial Release.

== Frequently Asked Questions ==
