=== Forum Restriction ===
Tags: forum restrict
Contributors: David Bessler
Requires at least: 0.73
Tested up to: 0.75
Stable Tag: 1.1

Allows you to restrict access to forums to certain individuals

== Description ==
Allows you to restrict access to forums to certain individuals.

== Installation ==
Add `forum-restriction.php`  to your `/my-plugins/` directory.

== Configuration ==
This plugin will add an admin/Site Management/Forum Restriction submenu.
Go there and simply add the user names (or display names: see below) to each forum listed.
Leave the field blank to allow everyone.

If you have display-name.php by Michael D Adams which can be found here:
http://trac.bbpress.org/ticket/430
you will have to use the display name instead of the user name in the configuration.

== Screenshots ==


== Version History ==
Version 1.1

* Added admin user interface
* Using MYSQL database rather than flat file in plugin
* Added comments so you people start helping make this thing work
* Tested with "private-forums.php"

Version 1.0

* Really rough.  My first attempt at a plugin.  No admin user interface.

== Known Issues and Wishlist ==
1.	PROBLEM:  Does not hide names or listing of restricted forums from those not authorized to see them.
instead, it adds an [x] before the forum name, and removes the link to the forum.  Same goes for topic listings under "recent discussions."

	SOLUTION:  I need someone to explain to me how to hijack the entire row of the forum listing in front-page.php

2.  	PROBLEM:  In admin user interface, you have to press submit twice to get the input fields to reflect the actual changes you made.  
The database changes go through the first time, but the input fields don't update.

	SOLUTION:  Again, need someone to help me figure this out.

3.  	PROBLEM:  Would like a more efficient way to choose and enter allowed users.  
This is really a layout problem.  
I don't think a dropdown list allowing multiple selects is the answer, because of the ease of which one can accidentally unselect one or all!! the chosen users.
I'm not so keen on the idea of a huge page with all the users listed with checkboxes.

	SOLUTION:  Either stick with the way I have it now, or rework the plugin so that the array works as follows:
"username" => "CSV list of allowed forums", and then have one huge member list where you can select forums in which they are allowed.
I chose my way because the way I intend to use the plugin is to have a large number of forums with only a few members allowed in each.
That is also why it is so important to me to HIDE the restricted forums on the front-page.

== Frequently Asked Questions ==

None.  It's early.  I'm sure there will be many suggestions.