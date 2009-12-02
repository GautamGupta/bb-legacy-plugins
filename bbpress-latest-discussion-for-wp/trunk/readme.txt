=== Bbpress Latest Discussion ===
Donate link: http://www.atsutane.net/
Tags: bbpress, wordpress, latest, discussion, bbld, widget, plugin, sidebar, post, page, atsutane, shirane, atsutane.net
Contributors: Atsutane
Requires at least: 2.0.5
Tested up to: 2.9
Stable Tag: 1.6

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress page.

== Description ==

This plugin will generates Latest Discussion list from your bbpress forum into your wordpress. It has the ability to generate latest discussion on sidebar also.

The administrator can also set the behavior for this plugin. Even if your bbpress is not intergrated with your wordpress. U still can use this plugin with a little change on the option page.

Bbpress Latest Discussion has been around since almost 2 years ago at Bbpress.org

Currently support bbPress `0.9.0.5`, `1.0-RC-3` And BuddyPress `1.0.1`

If you like my work, Please give a link back.

For more support and help, please go <a href="http://forums.atsutane.net/forum/bbpress-latest-discussion">here</a>

== Installation ==

Simply download the Zip-Archive and extract all folder into your wp-content/plugins/ directory. Then go into your WordPress administration page, click on Plugins and activate it. Go to BbLD Option page, to setup this plugin.

make sure the path become like this `wp-content/plugins/bbpress-latest-discussion/`

It is to make sure that wordpress find the plugin correctly so it can check for new update.

I’ve created two template tags you can use in your pages:

1. `wp_bb_get_discuss()` : Show Bbpress latest discussion on wp static page.
2. `wp_bb_get_discuss_sidebar()` : Show Bbpress latest discussion on wp sidebar.
3. `bbld_getforum()` : Show Bbpress forum list on wp static page.
4. `wp_bb_get_forum_sidebar()` : Show Bbpress forum list on wp sidebar.page.

Examples of use:

`<?php wp_bb_get_discuss(); ?>`

`<?php wp_bb_get_discuss_sidebar(); ?>`

`<?php bbld_getforum(); ?>`

`<?php wp_bb_get_forum_sidebar(); ?>`

Optional Usage:

`<?php wp_bb_get_discuss(1,2,3,4,5); ?>` Insert Forum ID Number That Need To Be Show, Will overide existing filter option. Seperate by comma.

Extra Stuff:

Filter Hook: `bbld_page`, `bbld_sidebar`, `bbld_forum`

For more support and help, please go <a href="http://forums.atsutane.net/forum/bbpress-latest-discussion">here</a>

== Frequently Asked Questions ==

How to use different database?

1. Make sure you check "External DB" option and input the data for your external database.

For more support and help, please go <a href="http://forums.atsutane.net/forum/bbpress-latest-discussion">here</a>

== Screenshots ==

1. BbLD New Admin Page
2. BbLD Template system
3. Sample shot how BbLD do the job
4. Sample shot how BbLD do the job
5. Sample shot with Gravatar support

== Changelog ==

= 1.6.4 (2009-11-26) =

* Add wp nonce into both BBld forms.

= 1.6.3 (2009-11-14) =

* Fix typo error on admin page.

= 1.6.2 (2009-11-09) =

* Fix BuddyPress permalink to support custom 'groups' slugs. Props Ashley L.

= 1.6.1 (2009-10-16) =

* Add Support For BuddyPress 1.1
* Try to fix several problem with title display.
* Fix paging number problem.

= 1.5.2 (2009-07-12) =

* Add No Encoding Option.
* Fix permalink function. Report By Fab.

= 1.5 (2009-06-22) =

* Add support for `BuddyPress 1.0.1`.
* Add 3 filter for display support. Current filter: `bbld_page`, `bbld_sidebar` and `bbld_forum`.
* Share `$user_data` globally.
* Add `user_id` inside `$user_data` variable.

= 1.4.0.8 (2009-06-16) =

* Fix wrong query code for exclude forum list.

= 1.4.0.6 (2009-06-16) =

* Add exclude option for forum list, Share same exclude option for latest list.

= 1.4.0.4 (2009-06-14) =

* Fix display name problem.
* Fix external bbpress cant show data while share with external wordpress.
* Fix option template page for forum list
* Add back user data function to support 0.9.0.*

= 1.4.0.2 (2009-06-13) =

* Add new function: Forum list.
* Proper error notice.
* Fix how BBLD fetch data for topic and user.
* Support gravatar for non share user database.

= 1.3.9.2 (2009-06-1) =

* Fix Divide By Zero Problem.

= 1.3.9 (2009-05-27) =

* Fix paging problem. Credit to thekmen, Pr1me, dragunoff
* Fix wrong table name for 9.0.4. Credit to douglsmith

= 1.3.6 (2009-05-27) =

* Fix something that suppose to be fix on 1.3.4
* Change how the query read meta table.

= 1.3.5 (2009-05-27) =

* Fix compatibility issue with 1.3.4, Add new option for backward compatibility

= 1.3.4 (2009-05-26) =

* Fix forum page problem.

= 1.3.3 (2009-05-26) =

* Change url to show latest post for the topic.
* Fix problem in 1.3.2.

= 1.3.2 (2009-05-24) =

* Add option to select what forum to be show on page.

= 1.3.1 (2009-05-07) =

* Add gravatar template tag for sidebar display.
* Add post count tag for sidebar display

= 1.3 (2009-04-24) =

* Fix how BbLD show topic title and forum name. Fix encoding issue. Report by Justin01

= 1.2.1 (2009-04-17) =

* Fix up query code. Now only use 1 query for 1 function. Report by dragunoof
* Add back `permalink type` option.
* Add back `share userdata` option

= 1.2 (2009-04-16) =

* Major Clean Up Code
* Add Donate link option

= 1.1.2 (2009-04-12) =

* Add Bbpress.js file.
* Fix external db connection.

= 1.1.1 (2009-04-11) =

* Fix permalink not working. Report by guyom.

= 1.1 (2009-04-08) =

* Fix permalink structure to match BBPress 1.0 Alpha-6. Report by irina57.
* Change how BbLD read permalink data
* Change how BbLD read exclude data
* Fix several external db option

= 1.0.4 (2009-04-07) =

* Fix wrong id number use on forum url. Report by matiaspunx.

= 1.0.3 (2009-04-06) =

* Remove permalink option. Now BbLD will auto detect.
* Remove last poster & inside option. Using template for sidebar display.
* Remove Wordpress/Bbpress Integration option. Now BbLD will auto use display name if exist.
* Add templates system for sidebar display.
* Add support for multi active plugin

= 1.0.2 (2009-04-05) =

* Fix wrong text inside option page.

= 1.0.1 (2009-04-05) =

* Fix a work-around for display name.

= 1.0 (2009-04-04) =

* New Admin Option Page.
* New templates system.
* Add support for widget control.
* Major clean up code.

= 0.9.2 (2009-04-02) =

* Clean up some code
* Add pot file support

= 0.9.1 (2009-04-02) =

* Add function to check what permalink type Bbpress use.
* Add more class option to style the table.

= 0.9 (2007-04-25) =

* Add option to display last poster and forum category. Props James Zapico.
* Add option to exclude forum from being display.

= 0.8.2 (2007-04-06) =

* Update on active plugin setup.

= 0.8 (2007-03-20) =

* Add option to trim text length.

= 0.7.4 (2007-03-15) =

* Fix typo in line 226. Props wittmania.

= 0.7.3 (2007-03-14) =

* Add Widget Support  :).

= 0.7.2 (2007-03-12) =

* Add Category link in sidebar list.
* Optimize the code a little.

= 0.7.1 (2007-03-11) =

* Add Multi Lang Support.

= 0.7 (2007-03-09) =

* Fix custom wp table prefix problem.
* Remove bbpress favorite function.

= 0.6 (2007-03-08) =

* Add option to set Bbpress table prefix.
* Change option page name. From “Bbpress Option” into “BbLD Option”.

= 0.5 (2007-03-04) =

* Fix display name for bbpress that not integrated with wordpress.
* Add option to set if your wordpress is integrated with bbpress or not.
* Add use Bbpress permalink option

= 0.4 (2007-03-04) =

* Bbpress Latest Discussion Option Page.
* Ability to set Bbpress url
* Ability to use different database

= 0.3 (2007-01-17) =

* Change the table to use display name instead of using username.
* Enable to set the “number of post”

= 0.2 (2006-11-11) =

* Fix post number bug. (It seem i use the wrong sql table XD)

= 0.1 (2006-11-05) =

* Initial Release.
