=== LDAP authentication for bbPress ===
Contributors: SamBauers
Tags: ldap authentication user management users
Requires at least: 0.75
Tested up to: 1.0 alpha build 805
Stable tag: 2.0

Allows authentication via an LDAP service

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

Configuration can be carried out within the admin area of bbPress, you
will need to be logged in as a keymaster.

This plugin requires at least PHP 5

== Upgrading Plugin from 1.x to 2.x ==

If you plan on upgrading from 1.x versions of this plugin to 2.x
versions, then you should be aware that your current settings will not
be available to the 2.x version after upgrading. You will need to
manually re-enter your LDAP server settings and other options.

== License ==

LDAP authentication for bbPress version 2.0
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

LDAP authentication for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

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
* 2.0 :
  <br/>Moved most functions into a class
  <br/>Amalgamated options into new serialized options
  <br/>Fixed issues with enabling/disabling features when using permalinks
  <br/>Added support for bb_admin_add_submenu()