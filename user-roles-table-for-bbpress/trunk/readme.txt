=== User Roles Table for bbPress ===
Contributors: sambauers
Tags: optimisation, speed, administration
Requires at least: 1.0-alpha-5
Tested up to: 1.0-rc-3
Stable tag: 1.3

Stores user roles in a separate roles table for faster reading purposes,
helps speed up some queries based on roles for sites with lots of users.

== Description ==

This plugin creates a user roles table in the database with the specific
intention of speeding up read queries based on user role.

Currently to obtain a list of users based on some role, bbPress must do a
query against the usermeta table using a LIKE query on an un-indexed column.
Queries like this bring any database server to it's knees when there are
millions of users.

Primarily, there is a filter added to the admin function
bb_get_user_ids_by_role(). So any plugin using this function will gain an
instant speed-up by using this plugin as well.

If another plugin wants to check for the existence of this plugin, then it
can detect the BB_URT_INSTALLED and BB_URT_ACTIVE constants. This detection
should be made by hooking into the 'bb_plugins_loaded' action and querying
for those constants.

This plugin requires modifications made to the bbPress codebase between
1.0-alpha-4 and 1.0-alpha-5, so the first supported release is 1.0-alpha-5.

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the folder into your /my-plugins/ directory

3. Activate the plugin in your "Plugins" administration screen.

Configuration can be carried out within the admin area of bbPress, you
will need to be logged in as a keymaster.

== License ==

User Roles Table for bbPress version 1.2<br />
Copyright (C) 2008 Sam Bauers (http://unlettered.org/)

User Roles Table for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0 : 
  <br />Initial Release
* 1.1 :
  <br />Better table population and bbdb setup
  <br />Now kills the recently registered list on the dashboard
* 1.2 :
  <br />Various bug fixes
* 1.3 :
  <br />Make compatible with 1.0-rc-3