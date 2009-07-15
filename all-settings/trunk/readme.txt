=== All Settings ===
Tags: _ck_, administration, admin, settings, options, configure, bb-admin, phpmyadmin, integration, keymaster
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows all settings, including hidden options, to be edited in the admin control panel. Works just like the (little-known) WordPress feature. 

== Description ==

Allows all settings, including hidden options, to be edited in the admin control panel. Works just like the (little-known) WordPress feature. 

It can sometimes be an easier alternative to using phpmyadmin for changing basic options that are sometimes unaccessable otherwise.

Please be aware that misuse of this plugin can break your bbPress install entirely, permanently. It's meant more for safely OBSERVING your settings to have an expert recommended changes if necessary.

== Installation ==

* Add the `all-settings/` folder to bbPress' `my-plugins/` directory.

* Activate and check under "settings" admin submenu for "All Settings".

* Note you don't have to keep this plugin active all the time as you don't need it most of the time.

== Frequently Asked Questions ==

= I changed something and now bbPress won't start OR I can't get into the admin menu ! =

* re-read the warning and restore your bbPress backup  (you DID make a backup, right?) 

* It may be possible to get bbPress started again by overriding your changes temporarily in `bb-config.php`  Ask (patiently) on bbpress.org for help.

= I can't edit some settings? =

* serialized and boolean data (true/false) cannot be edited at this time for safety reasons - that may eventually change

= What does the (bb-config.php) mean?  =

* When this appears next to an input field, that means the value seems to be from bb-config.php, or calculated internally by bbPress from other options, and may not be possible to change only via the database only. You may have to edit bb-config.php directly.

= This feature is in WordPress ? =

* Yes, it's hidden. Go to the the regular settings page in WP  (options-general.php) and take away the "-general" part in the URL to make it "options.php"

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2008-08-09) =

* first public release

= Version 0.0.2 (2008-08-10) =

* array merge error fix, hardcoded bb_get_options displayed, 1.0 alpha compatibility fix for new bb_meta 

== To Do ==

* display CONSTANTS currently in use (may require PHP > 5.0 )
