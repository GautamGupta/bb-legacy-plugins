=== Bbpress Latest Discussion ===
Donate link: http://www.atsutane.net/
Tags: bbpress, wordpress, latest, discussion, bbld, widget, plugin, sidebar, post, page
Contributors: Atsutane
Requires at least: 2.0.5
Tested up to: 2.7.1
Stable Tag: 1.0

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress page.

== Description ==

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress. It has the ability to generate latest discussion on sidebar also.

The administrator can also set the behavior for this plugin. Even if your bbpress is not intergrated with your wordpress. U still can use this plugin with a little change on the option page.

Bbpress Latest Discussion has been around since almost 2 years ago at Bbpress.org

Currently support bbPress `0.9.0.4` and `1.0-Alpha-6`

If you like my work, Please give a link back.

== Installation ==

Simply download the Zip-Archive and extract all folder into your wp-content/plugins/ directory. Then go into your WordPress administration page, click on Plugins and activate it. Go to BbLD Option page, to setup this plugin.

make sure the path become like this `wp-content/plugins/bbpress-latest-discussion/`

It is to make sure that wordpress find the plugin correctly so it can check for new update.

I�ve created two template tags you can use in your pages:

1. `wp_bb_get_discuss()` : Show Bbpress latest discussion on wp static page.
2. `wp_bb_get_discuss_sidebar()` : Show Bbpress latest discussion on wp sidebar.

Examples of use:

`<?php wp_bb_get_discuss(); ?>`

`<?php wp_bb_get_discuss_sidebar(); ?>`

== Frequently Asked Questions ==

How to use different database?

1. Make sure you check "External DB" option and input the data for your external database.

== Screenshots ==

1. BbLD New Admin Page
2. BbLD Template system
3. Sample shot how BbLD do the job
4. Sample shot how BbLD do the job

== Version History ==

Version 1.2 (2009-04-16):

* Major Clean Up Code
* Add Donate link option

Version 1.1.2 (2009-04-12):

* Add Bbpress.js file.
* Fix external db connection.

Version 1.1.1 (2009-04-11):

* Fix permalink not working. Report by guyom.

Version 1.1 (2009-04-08):

* Fix permalink structure to match BBPress 1.0 Alpha-6. Report by irina57.
* Change how BbLD read permalink data
* Change how BbLD read exclude data
* Fix several external db option

Version 1.0.4 (2009-04-07):

* Fix wrong id number use on forum url. Report by matiaspunx.

Version 1.0.3 (2009-04-06):

* Remove permalink option. Now BbLD will auto detect.
* Remove last poster & inside option. Using template for sidebar display.
* Remove Wordpress/Bbpress Integration option. Now BbLD will auto use display name if exist.
* Add templates system for sidebar display.
* Add support for multi active plugin

Version 1.0.2 (2009-04-05):

* Fix wrong text inside option page.

Version 1.0.1 (2009-04-05):

* Fix a work-around for display name.

Version 1.0 (2009-04-04):

* New Admin Option Page.
* New templates system.
* Add support for widget control.
* Major clean up code.

Version 0.9.2 (2009-04-02):

* Clean up some code
* Add pot file support

Version 0.9.1 (2009-04-02):

* Add function to check what permalink type Bbpress use.
* Add more class option to style the table.

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
* Change option page name. From �Bbpress Option� into �BbLD Option�.

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
* Enable to set the �number of post�

Version 0.2 (2006-11-11):

* Fix post number bug. (It seem i use the wrong sql table XD)

Version 0.1 (2006-11-05):

* Initial Release.
