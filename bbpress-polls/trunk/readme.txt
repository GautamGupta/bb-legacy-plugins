=== bbPress Polls ===
Tags: vote, votes, voting, poll, polls, polling
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk

== Description ==

This plugin allows polls to be added to any topic in bbpress.
There are many powerful options for administrators.

This is a beta release without an admin menu (coming soon).
For now you must edit the bb-polls.php directly to change default options.

== Installation ==

Add the "bb-polls.php" file to bbPress "my-plugins/" directory and activate. 

For now you must edit the bb-polls.php directly to change default options.
Options can be found near the top of the file as $bb_polls['option'] etc.
This is a beta release without an admin menu (coming soon).

== Version History ==

0.01	* bb-polls is born - no voting yet, just create a poll for testing
0.10	* first public beta
0.11	* bug fix for polls on page 1 setting
0.12	* poll can now be on first/last/both/all pages & add text to topic titles like [poll]
0.13	* more control over who can add/vote/view/edit polls 
0.14	* colour fixes for default theme
0.15  	* cache performance fixes, extra custom label ability, more css classes, colour tweaks
0.16	* added __() for automatic translations when possible, all text is now in array near top
0.17	* trick bbpress to keep data unserialized until needed for performance (backward compatible)
0.18	* post data fix for refreshed pages (via redirect, nasty but no other way?)
0.19	* first ajax-ish behaviours added for view current voting results and then back to the form - pre-caching forms, but no submit saving ajax yet 
0.20	* more text found & moved to array for translations, float removed from default css for Right-to-Left setups, graph bars limited to min & max
0.21	* many little fixes for IE to work properly, css changes to make IE vs Firefox almost identical 
0.22	* voting is now ajax-ish - only non-ajax-ish form is the one to create a poll, might be awhile - cancel button also added to create poll form
0.23	* javascript fix for internet explorer (has to delay append action a few milliseconds or update won't appear to happen)
0.24	* bug fix for opera trying to cache javascript requests - added alert if they try to vote without selection (todo: need to alert on non-ajax) 
	
== To Do ==
* admin menu (coming soon - edit plugin directly for now, many options)
* administrative editing/deleting of existing polls.	
* display a poll anywhere within bbpress templates
* display all polls on a single page

