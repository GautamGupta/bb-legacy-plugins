=== Forum Restriction ===
Tags: forum restrict
Contributors: David Bessler
Requires at least: 0.73
Tested up to: 0.75
Stable Tag: 1.5

Allows you to restrict access to forums to certain individuals and hide them and their topics from all others.  It also allows you to send notification to members of those restricted forums when a new topic is started.

== Description ==

Allows you to restrict access to forums to certain individuals and hide them and their topics from all others.  It also allows you to send notification to members of those restricted forums when a new topic is started.

== Installation ==
Add `forum-restriction.php`  to your `/my-plugins/` directory.

== Configuration ==
This plugin will add an admin/Site Management/Forum Restriction submenu.
Go there and simply add the user names (or display names: see below) to each forum listed.
Leave the field blank to allow everyone.

This plugin MUST be used along with display-name.php by Michael D Adams which can be found here:
http://trac.bbpress.org/ticket/430

Check/uncheck the option to "Email members for new topic."

== Screenshots ==


== Version History ==
version 1.4

* Major reorganization of admin page generation code
* Finally fixed bug where you would have to reload the page to see current settings (yay!).
* Added error message if invalid user name is entered (must be used with display-names plugin).
* Display-name.php now required.  The reason is that I've decided that the most efficient way to enter allowed users is by typing in their names, rather than some elaborate select box or checkbox system.  I also opted against requiring people to enter standardized user id numbers which would be a pain in the arse.  So, rather than have to create some sort of system that checks all the entered names to see whether or not they ar display names or logins, I just required the display names plugin which I always use anyway.

version 1.3

* Added yellow hilighted listing of authorized users in forum description.
* Added option to email members of forums when new topic is started.

version 1.2

* Finally figure out how to hide non-accessible forums and topics

Version 1.1

* Added admin user interface
* Using MYSQL database rather than flat file in plugin
* Added comments so you people start helping make this thing work
* Tested with "private-forums.php"

Version 1.0

* Really rough.  My first attempt at a plugin.  No admin user interface.

== Known Issues and Wishlist ==
1.  	PROBLEM:  Would like a more efficient way to choose and enter allowed users.  
This is really a layout problem.  
I don't think a dropdown list allowing multiple selects is the answer, because of the ease of which one can accidentally unselect one or all!! the chosen users.
I'm not so keen on the idea of a huge page with all the users listed with checkboxes.

	SOLUTION:  Either stick with the way I have it now, or rework the plugin so that the array works as follows:
"username" => "CSV list of allowed forums", and then have one huge member list where you can select forums in which they are allowed.
I chose my way because the way I intend to use the plugin is to have a large number of forums with only a few members allowed in each.
That is also why it is so important to me to HIDE the restricted forums on the front-page.

== Frequently Asked Questions ==

None.  It's early.  I'm sure there will be many suggestions.