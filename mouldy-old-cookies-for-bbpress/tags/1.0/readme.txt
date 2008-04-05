=== Mouldy old cookies for bbPress ===
Contributors: sambauers
Tags: cookies, old-skool
Requires at least: 0.9.0.1
Tested up to: 0.9.0.1
Stable tag: 1.0

Reverts bbPress to use old style authentication cookies

== Description ==

This plugin reverts the style of authentication cookie used to the pre-0.9
version.

It is only really useful if you plan to integrate your bbPress installation
with a version of WordPress older than 2.5

Keep in mind that the best possible path is to upgrade your WordPress
installation to 2.5 where possible, as this older style of cookie has proven
to be insecure.

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

2. Upload the file into /my-plugins/ directory

3. It is probably best to autoload the plugin by prepending an underscore "_"
   to the filename. i.e. rename file to "_mouldy-old-coookies-for-bbpress.php"

== License ==

Mouldy old cookies for bbPress 1.0
Copyright (C) 2008 Sam Bauers

Mouldy old cookies for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 1.0 : 
  <br/>Initial Release