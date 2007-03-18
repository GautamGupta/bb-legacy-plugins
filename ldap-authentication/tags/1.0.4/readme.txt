=== LDAP authentication for bbPress ===
Contributors: SamBauers
Tags: ldap authentication user management users
Requires at least: 0.75
Tested up to: 1.0 alpha build 782
Stable tag: 1.0.4

Allows authentication via an LDAP service

== License ==

LDAP authentication for bbPress version 1.0.4
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

LDAP users login using their unique id, IE the value in their records
"uid" attribute.

The email that is optionally retrieved will be the first email address
listed in their "mail" attribute.

== Installation ==

Unzip the file and copy the ldap-authentication.php file into the
my-plugins directory.

If the my-plugins directory is not present you need to create it under
the root directory of your forum.

== Version History ==

* 1.0 :
  <br/>Initial Release
* 1.0.1 :
  <br/>Small non-critical fixes to ldap_remove_password_capability()
* 1.0.2 :
  <br/>Cookie hacking vulnerability fixed
  <br/>Disabled password reseting function for LDAP users
  <br/>Added option to disable automatic registration of LDAP users
* 1.0.3 :
  <br/>Added option to retrieve LDAP users email address on registration
* 1.0.4 :
  <br/>Added support for new admin menu structure introduced in build 740