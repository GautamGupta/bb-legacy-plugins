=== Support Forum ===
Contributors: so1o, mdawaffe, SamBauers
Tags: support, forums
Requires at least: 0.80 build 701
Tested up to: 1.0 alpha build 805
Stable tag: 2.0.1

Adds the ability to set a support status on topics and thus turn the forum into a support forum.

== Description ==

This plugin creates an option to convert a bbPress installation into a
support forum where the users can mark the topics as resolved, not resolved
or not a support question.

The administrator can also set the default status of the topics and some
other options as well.

Two of these options add a visual marker to each topic to indicate it's
support/locked status.

* Not a support question = empty slot
* Unresolved = red ball (alternative orange ball also provided)
* Resolved = green ball
* Locked = padlock

Please Note: this plugin contains functionality that was standard before
bbPress 0.75, for details please look at http://trac.bbpress.org/ticket/496

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the file into /my-plugins/ directory

3. Also copy the PNG images into the my-plugins directory. There is an alternative
   icon for the "no" status which is orange instead of red, rename as required.

4. If you are migrating from version 1.0 of the plugin, go to the
   administration area and update the topics to the new format

Configuration can be carried out within the admin area of bbPress, you
will need to be logged in as a keymaster.

This plugin requires at least PHP 5

== License ==

Support forum version 2.0.1
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
  <br/>Added admin action to upgrade database instead of running on plugin load
  <br/>When default status is "unresolved" topics with no status set now show in the "unresolved" view
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