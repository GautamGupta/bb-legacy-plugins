=== Support Forum ===
Contributors: so1o, mdawaffe, SamBauers
Tags: support, forums
Requires at least: 0.80 build 701
Tested up to: 1.0 alpha build 1060
Stable tag: 2.3.5

Adds the ability to set a support status on topics in selected forums.

== Description ==

This plugin creates an option to convert forums within a bbPress installation
into support forums where the users can mark the topics as resolved, not
resolved or not a support question.

The administrator can also set the default status of the topics, and which
statuses have special views.

You can select if the original poster has the ability to set the status on
topic creation and whether they can change the status later.

Three additional options add a visual marker to each topic to indicate it's
support, locked or sticky status.

* Not a support question = empty slot
* Unresolved = red ball (alternative orange ball also provided)
* Resolved = green ball
* Locked = padlock
* Sticky = star

Please Note: this plugin contains functionality that was standard before
bbPress 0.75, for details please look at http://trac.bbpress.org/ticket/496

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the file into /my-plugins/ directory

3. Also copy the PNG images into the my-plugins directory. There is an
   alternative icon for the "no" status which is orange instead of red, rename
   as required.

4. If you are migrating from version 1.0 of the plugin, go to the
   administration area and update the topics to the new format

Configuration can be carried out within the admin area of bbPress, you
will need to be logged in as a keymaster.

== License ==

Support forum version 2.3.5
Copyright (C) 2007 Aditya Naik (so1oonnet@gmail.com)
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

Support forum comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0 : 
  <br/>Initial Release
* 1.1 : 
  <br/>Use topic_resolved meta key
  <br/>By default the support forums are switched on
* 1.2 :
  <br/>Integrated visual-support-forum plugin features as options in admin
  <br/>Added admin action to upgrade database instead of running on plugin
       load
  <br/>When default status is "unresolved" topics with no status set now show
       in the "unresolved" view
  <br/>Sticky topics that are unresolved now show in the "unresolved" view
* 1.2.1 :
  <br/>Added support for new admin menu structure introduced in build 740
  <br/>Text based labels in topic lists now show again when icons not used
* 2.0 :
  <br/>Object-orientation
  <br/>Made admin page more serious
  <br/>Added visual feedback when changing a topic's status
  <br/>Limited javascript addLoadEvent call to topic pages only
  <br/>Admin page feedback now uses bb_admin_notice()
  <br/>Added GPLv2 license details
  <br/>Added support for bb_admin_add_submenu()
* 2.0.1 :
  <br/>Also remove topic_title filter through bb_topic_title function
* 2.0.2 :
  <br/>Added some whitespace to clean up display of icons in topics
* 2.0.3 :
  <br/>Make PHP4 compatible
* 2.1 :
  <br/>Compatibility with new bb_register_views/BB_Query methods introduced in
       build 876 for "unresolved" view
  <br/>Selection of individual forums as support forums rather than the whole
       site
* 2.1.1 :
  <br/>Add missing gettext calls to admin page to allow full
       internationalisation
* 2.2 :
  <br/>Added the option to add a view for each individual status
* 2.3 :
  <br/>Added the option for the topic creator to set the status on creation
  <br/>Added the option to allow the topic creator to change the status of the
       topic
  <br/>Tightened up the permissions so that only those who can change others
       topics can generally change the status
  <br/>Slightly better formatting of the admin page
* 2.3.1 :
  <br/>Fixed a problem where a warning was produced if there were no settings
       in the database
* 2.3.2 :
  <br/>Pass $support_forum object by reference for latest WP add_filter() and
       add_action() methods
* 2.3.3 :
  <br/>Make compatible with new bb_topic_labels filter introduced in build 968
  <br/>Add option to label sticky topics with an icon
* 2.3.4 :
  <br/>Squash javascript error in admin pages that aren't the plugin options
       page
* 2.3.5 :
  <br/>Remove some leftover debug code from 2.3.4
  <br/>Add option to hard code the icon URI in a constant
  <br/>Squash JavaScript bug on front page
  <br/>Add title attribute to icons