=== Trac links for bbPress ===
Contributors: SamBauers
Tags: Trac, development
Requires at least: 0.9
Tested up to: 1.0-alpha-2
Stable tag: 1.0.2

Allows the use of Trac shortcodes like #1234 for tickets and [1234] for
changesets within post text.

== Description ==

This plugin allows forum users to insert quick links to Trac tickets and
changesets inside bbPress posts.

For example #123 will be automatically changed into a link to a ticket in Trac,
[321] will change into a link to a changset.

Multiple Trac installs can be specified and distinguished by appending an
"intertrac" code in the numbers. e.g. [WP576] or #BB902

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the folder into your /my-plugins/ directory

3. Activate the plugin in your "Plugins" administration screen.

Configuration can be carried out by editing the small config section at the top
of the plugin file.

== License ==

Trac links for bbPress version 1.0.2<br />
Copyright (C) 2008 Sam Bauers (http://unlettered.org/)

Trac links for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0   :
  <br />Initial Release
* 1.0.1 :
  <br />Stop numbered HTML entities from being turned into links
* 1.0.2 :
  <br />Stop capturing of character before the hash
  <br />Stop transformation inside code blocks