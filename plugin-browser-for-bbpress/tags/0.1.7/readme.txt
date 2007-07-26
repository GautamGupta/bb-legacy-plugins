=== Plugin browser for bbPress ===
Contributors: SamBauers
Tags: plugin management
Requires at least: 0.8.2.1
Tested up to: 1.0 alpha build 892
Stable tag: NA

Adds one-click installation and upgrade of plugins from the bbPress plugin
repository.

== Description ==

This plugin provides an interface in the bbPress admin area where a keymaster
can install, upgrade and uninstall plugins that are featured in the bbPress
plugin repository here http://bbpress.org/plugins/

== Installation ==

1. If you don't have a /my-plugins/ directory in your bbpress installaltion,
   create it on the same level as config.php.

2. Upload the file into /my-plugins/ directory.

3. The /my-plugins/ directory needs to be readable and writable by the web
   server for installation and upgrading or browsers to work.

The plugin browser interface is within the admin area of bbPress, you will
need to be logged in as a keymaster.

Further information is on-screen in the browser interface.

== Usage note ==

Plugins installed via the browser interface are contained in specially named
directories within the plugins directory that are prefixed with 'pb--'.

For the browser to work correctly these folder must not be renamed. The main
plugin file within the folder also must not be renamed. The special file
'pb--revision.php' within each folder must also be left untouched.

Plugins still need to be activated using the standard plugin page after
installation.

The plugin browser will not detect your existing plugins, it manages plugins
using a specific naming convention that must not be interrupted.

== License ==

Plugin browser for bbPress version 0.1.7
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

Plugin browser for bbPress comes with ABSOLUTELY NO WARRANTY
This is free software, and you are welcome to redistribute
it under certain conditions.

See accompanying license.txt file for details.

== Version History ==

* 0.1 :
  <br />Initial Beta release
* 0.1.1 :
  <br />Trim whitespace from items in Plugin_Browser::getRemoteList()
* 0.1.2 :
  <br />Using CURL libraries as preference, then falling back to fopen
        wrappers
* 0.1.3 :
  <br />Removed stray fclose() call
* 0.1.4 :
  <br />Stop the truncating of files using \r\n line breaks being retrieved
        via CURL
  <br />Added _wpnonce to check action validity
* 0.1.5 :
  <br />Support for plugins with sub-directories
* 0.1.6 :
  <br />Added link to view available readme.txt files on installed plugins
* 0.1.7 :
  <br />Stopped fopen() from redirecting and returning redirected content,
        fixes support for plugins with sub-directories
  <br />Now only retrieving headers where desirable
  <br />Replaced call to bb_get_plugin_data() with custom call that does not
        use file()