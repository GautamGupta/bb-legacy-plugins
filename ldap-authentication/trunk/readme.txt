=== LDAP authentication for bbPress ===
Contributors: SamBauers
Tags: ldap authentication user management users
Requires at least: 0.75
Tested up to: 0.80-RC3 build 687
Stable tag: 1.0.2

Allows authentication via an LDAP service

== License ==

LDAP authentication for bbPress version 1.0.2
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

LDAP authentication for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Description ==

This plugin allows the administrator to specify an LDAP service for
authentication.

When a user logs in, the local database is searched first, and then
the LDAP server is searched.

When the user successfully authenticates to the LDAP server for the
first time, a local record is created for them to hold their settings.

From then on the user is exactly the same as a local user, except that
they still authenticate against the LDAP service, i.e. they have no
local password. However, LDAP users can still be blocked if desired,
just like normal users. Optionally, local user registration can be
disabled.

LDAP users cannot edit their passwords in the profile area.

== Installation ==

Unzip the file and copy the ldap-authentication.php file into the
my-plugins directory.

If the my-plugins directory is not present you need to create it under
the root directory of your forum.