=== HTTP authentication for bbPress ===
Contributors: SamBauers
Tags: http_auth authentication user management users
Requires at least: 0.80
Tested up to: 0.80
Stable tag: NA

Allows authentication via basic or digest HTTP authentication

== License ==

HTTP authentication for bbPress version 0.1.2
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

HTTP authentication for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Description ==

This plugin allows the administrator to specify the use of HTTP
authentication for login.

Optionally, when the user successfully authenticates using HTTP
authentication for the first time, a local record is created for them
to hold their settings.

From then on the user is exactly the same as a local user, except that
they still authenticate against HTTP authentication, i.e. they have no
local password. However, HTTP users can still be blocked if desired,
just like normal users. Optionally, local user registration can be
disabled.

HTTP users cannot edit their passwords in the profile area.

== Installation ==

Unzip the file and copy the http-authentication.php file into the
my-plugins directory.

If the my-plugins directory is not present you need to create it under
the root directory of your forum.

== Version History ==

* 0.1 :
  <br/>Initial Release - based on LDAP Authentication plugin
* 0.1.1 :
  <br/>Removed need to login twice
* 0.1.2	:
  <br/>Added function to call bb_login with null arguments