=== My Views  ===
Tags: view, views, most viewed, least viewed, my topics, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to:  trunk alpha 1075
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM


My Views is a powerful addition to the default "views" in bbPress. It will let you customize output and adds several new views.

== Description ==

My Views consists of a main plugin and several optional "module" plugins that can be added if desired for additional new views.

== Installation  ==

1. Install and activate my-views.php plugin
2. Optionally install bb-Topic-Views plugin and Plugin Browser for bbPress if desired
3. Install and activate optional My Views module plugins as desired. 
4. Edit "view.php" template and place `<? my_views_header(); ?>` just after `<h3>..</h3>` breadcrumb section  
5. Adjust display order and/or hide undesired views (currently only by editing my-views.php, admin menu coming soon)
6. Optionally edit "front-page.php" and "forum.php" templates to show `<? echo $forum->views; ?>;` in desired column

== Frequently Asked Questions ==

= My Views adds the following features to bbPress Views: =
    * adds html page titles to Views which are missing by bbPress default
    * adds proper view title and dropdown Views box  if you edit "view.php" template and put <? my_views_header(); ?> after <h3>..</h3> section
    * adds optional dropdown views list  anywhere you put <? my_views_dropdown(); ?> in your templates (themes)
    * adds forum total view count if bb-Topic-Views is installed. Edit "front-page.php" template to show <? echo $forum->views; ?>; in desired column.

= Built-in bbPress Views =
    * Topics with no replies		(no-replies)
    * Topics with no tags		(untagged)

= Appended bbPress Views by My Views =
    * Latest Discussions		(latest-discussions)
    * Topics I've Started		(my-topics)
    * Topics I've Participated In	(my-posts)
    * Topics with the most posts	(most-posts)
    * Topics with the least posts	(least-posts)
    
= Extended bbPress Views by My Views if optional "bb Topic Views" plugin by Mike Wittmann is installed http://bbpress.org/plugins/topic/53  =
    * Topics with the most views	(most-views)
    * Topics with the least views	(least-views)
    * Forum Statistics			(statistics)
    
= Extended bbPress Views by My Views if optional "Plugin Browser for bbPress" plugin by Sam Bauers is installed http://bbpress.org/plugins/topic/57 =
   * Installed bbPress Plugins	(installed-plugins)
   * Available bbPress Plugins	(available-plugins)

= Extended bbPress Views by My Views if optional " bbPress Theme Switcher" plugin by _ck_ is installed =
   * Installed bbPress Themes  	(available-themes)

= Extended bbPress Views not publicly available =
  * Top 100, Top 1000 bbPress Forums

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

Version 0.03 (2007-08-01)

* 	first public release, for 0.8.2.x only, not trunk

Version 0.04 (2007-08-09)

* 	compatibility with both 0.8.x & 1.0 alpha trunk

Version 0.05 (2007-08-10)

* 	breakup of view modules into seperate, optional plugins

Version 0.06 (2007-08-20)

*	available/installed plugins improvements (totals, sorting)

Version 0.06 (2007-08-29)

*	additional modules (statistics, available themes)	  Statistics is not finished yet and Themes requires bbPress Theme Switcher

Version 0.08 (2007-08-30)

*	bug fix for passthrough adding views to 0.8.2.x , optional header & footer for internal views, optional .my_views_header class

Version 0.09 (2008-02-03)

*	pagination (multi-page) support added for versions >0.8.3 & add label "pages: " (or any text) to list of pages

Version 0.091 (2008-02-03)

* 	quick fix for removing duplicated/misplaced stickies & incorrect sorting orders

Version 0.1.1 (2008-04-21)

*	fix for improper WHERE filter use to peacefully co-exist with Hidden Forums plugin


