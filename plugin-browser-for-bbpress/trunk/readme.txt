=== Plugin Browser for bbPress ===
Contributors: SamBauers, GautamGupta
Tags: plugin management, install, upgrade, uninstall, plugins, Gautam
Requires at least: 1.0
Tested up to: 1.1
Stable tag: 0.2

Adds one-click installation, uninstallation, and upgrade functions for new and existing plugins.

== Description ==

This plugin provides an interface in the bbPress admin area where a keymaster can install, uninstall, activate, deactivate and upgrade plugins that are featured in the [bbPress plugin repository](http://bbpress.org/plugins/).

== Installation ==

1. If you don't have a `/my-plugins/` directory in your bbpress installaltion, create it on the same level as `bb-config.php`.
2. Upload the extracted `plugin-browser-for-bbpress` folder into `/my-plugins/` directory.
3. The `/my-plugins/` directory needs to be readable and writable by the web server for installation and upgrading or browsers to work.

The plugin browser interface is within the admin area of bbPress, you will need to be logged in as a keymaster. Further information is on-screen in the browser interface.

== Other Notes ==

* If you were using an older version than v0.2, then it is recommened that you remove `pb--` from the plugins' directory name in the `my-plugins` folder.
* If the plugin list is not updating for some reason, then go to the plugin page, and in the URL add this - `&force_update=1` and press enter. The plugin should then make a new list of the plugins.

= Translations =
You can contribute by translating this plugin. Please refer to [this post](http://gaut.am/translating-wordpress-or-bbpress-plugins/) to know how to translate.

= To Do =
* Add a daily auto-check for plugins and notify if any plugin could be upgraded

= License =
GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html
Copyright (C) 2010 Sam Bauers (sam@viveka.net.au), Gautam (www.gaut.am)

= Donate =
You may donate by going [here](http://gaut.am/donate/PBfB/).

== Changelog ==

= 0.1 =
* Initial Beta release

= 0.1.1 =
* Trim whitespace from items in Plugin_Browser::getRemoteList()

= 0.1.2 =
* Using CURL libraries as preference, then falling back to fopen wrappers

= 0.1.3 =
* Removed stray fclose() call

= 0.1.4 =
* Stop the truncating of files using \r\n line breaks being retrieved via CURL
* Added _wpnonce to check action validity

= 0.1.5 =
* Support for plugins with sub-directories

= 0.1.6 =
* Added link to view available readme.txt files on installed plugins

= 0.1.7 =
* Stopped fopen() from redirecting and returning redirected content, fixes support for plugins with sub-directories
* Now only retrieving headers where desirable
* Replaced call to bb_get_plugin_data() with custom call that does not use file()

= 0.1.8 =
* Cleaned up sloppy function call

= 0.1.9 =
* Pass the contents index of plugin_file array instead of the whole array

= 0.1.10 =
* 0.1.9 was all talk and no action, this time for sure

= 0.1.11 =
* Clear readme.txt link properly on each loop through the plugin list items

= 0.1.12 =
* Reinstate author link when there is no author uri

= 0.2 =
* Compatibility with bbPress 1.0. If you are using an older version of bbPress, then please do not upgrade
* Usage of WP_Http instead of cURL or fopen
* Automatically gets the new repository list if available (when you go to the plugin browser page)
* The directory folder of a plugin now need not have pb-- in its name, nor a pb--revision.php (which means that now the plugins are checked with their version numbers)
* Added i18n support
* Coding cleanups (including WordPress style coding), optimizations and improvements