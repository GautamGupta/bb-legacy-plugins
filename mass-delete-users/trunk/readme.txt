=== Mass Delete Users ===
Tags: _ck_, administration, moderation, users, members, mass delete, bulk, bulk delete, delete
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows administrators to physically delete multiple users at once from the bbPress / WordPress database based on a variety of search attributes.

== Description ==

Allows administrators to find and delete many users in bulk at once.
Users are physically deleted from the bbPress / WordPress database based on a variety of attributes.
Backup and undelete feature coming eventually.

USERS ARE DELETED PERMANENTLY - BE CAREFUL, BACKUP DATABASE FIRST

== Installation ==

* Add the `mass-delete-users` directory to bbPress' `my-plugins/` directory.
* Activate and check under "Users" admin submenu for "Mass Delete".

== Frequently Asked Questions ==

* Keep in mind there is no backup/restore method yet. Users are deleted permanently.

* Also note that a deleted user allows someone else to register with the same username. 
Set user to INACTIVE instead to prevent re-use of the username.

* This plugin does NOT delete posts / comments from the user you are deleting. Delete such items beforehand via the Mass Edit plugin.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-02-25) =

* first pre-alpha release

= Version 0.0.2 (2009-03-7) =

* alpha release for public testing

= Version 0.0.3 (2009-03-25) =

* now can filter by users with no forum posts or WP comments

= Version 0.0.4 (2009-04-03) =

* bug fix for wrong option on api delete (accidentally still worked on bbPress 0.9 but fatal in 1.0)

== To Do ==

* backup option with undelete ability

* search via number of posts / comments
