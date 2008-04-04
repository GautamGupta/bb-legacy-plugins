=== MD5 insecurity for bbPress ===
Contributors: sambauers
Tags: login, password, md5, old-skool, insecurity
Requires at least: 0.9.0.1
Tested up to: 0.9.0.1
Stable tag: 1.2

Changes the password hashing in bbPress to use old-skool MD5

== Description ==

This plugin reverts storage of user passwords back to using MD5 hashing
instead of the newer phpass hashing introduced in build 974.

It is useful for people who need to maintain MD5 as the hashing type due to
sharing of the bbPress user tables with other applications which require it.

If some of your passwords are already converted to phpass, do not fear. The
next time those users log in to bbPress their passwords will be converted back
to MD5.

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the file into /my-plugins/ directory

3. It is probably best to autoload the plugin by prepending an underscore "_"
   to the filename. i.e. rename the file to "_md5-insecurity-for-bbpress.php"

== License ==

MD5 insecurity for bbPress 1.2
Copyright (C) 2007 Sam Bauers
Copyright (C) 2008 Ryan Boren

MD5 insecurity for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0 : 
  <br/>Initial Release
* 1.1 :
  <br />Update for compatibility with new auth cookies
* 1.2 :
  <br />Port directly from Ryan Boren's "MD5 Password Hashes"