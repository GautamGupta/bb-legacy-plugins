=== Bbpress Latest Discussion ===
Tags: bbpress, wordpress, latest, discussion
Contributors: Atsutane
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: 0.9

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress page.

== Description ==

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress. It has the ability to generate latest discussion on sidebar also.

The administrator can also set the behavior for this plugin. Even if your bbpress is not intergrated with your wordpress. U still can use this plugin with a little change on the option page.

== Installation ==

Simply download the Zip-Archive and extract all files into your wp-content/plugins/ directory. Then go into your WordPress administration page, click on Plugins and activate it. Go to BbLD Option page, to setup this plugin.

== Configuration ==

I’ve created three template tags you can use in your pages:

1. `wp_bb_get_discuss()` : Show Bbpress latest discussion on wp static page.
2. `wp_bb_get_discuss_sidebar()` : Show Bbpress latest discussion on wp sidebar.

Examples of use:

`<?php wp_bb_get_discuss(); ?>`

`<?php wp_bb_get_discuss_sidebar(); ?>`

== Version History ==

Version 0.9 (2007-04-25):

* Add option to display last poster and forum category. Props James Zapico.
* Add option to exclude forum from being display.

Version 0.8.2 (2007-04-06):

* Update on active plugin setup.

Version 0.8 (2007-03-20):

* Add option to trim text length.

Version 0.7.4 (2007-03-15):

* Fix typo in line 226. Props wittmania.

Version 0.7.3 (2007-03-14):

* Add Widget Support :).

Version 0.7.2 (2007-03-12):

* Add Category link in sidebar list.
* Optimize the code a little.

Version 0.7.1 (2007-03-11):

* Add Multi Lang Support.

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
