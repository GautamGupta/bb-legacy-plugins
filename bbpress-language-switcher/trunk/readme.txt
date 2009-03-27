=== bbPress Language Switcher  ===
Tags: _ck_, language, i18n, i10n, internationalization, translate, translation, mo, po, poedit
Contributors: _ck_
Requires at least: 0.9
Tested up to: 1.0 alpha 5
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows any user (guest or member) to select a different bbPress language for templates.

== Description ==

Allows any user (guest or member) to select a different bbPress language for templates.

== Installation ==

* Place dropdown anywhere you'd like in templates via:  `<?php do_action('bb_language_switcher',''); ?>`

* If you do not already have an alternate language set,
You MUST change in your bb-config.php :  `define('BBLANG', ' ');` 
note the space between the quotes

* Put any .mo language files into  `bb-includes/languages/`

* Install, activate plugin

* To rebuild the list of languages in the dropdown you must deactivate/reactivate the plugin

== Frequently Asked Questions ==

* Users must have cookies enabled for language switch to work

* You can define your own custom path to .mo files with:  `define('BB_LANG_DIR', '/your-custom-path/');`  

* To rebuild the list of languages in the dropdown you must deactivate/reactivate the plugin

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2009-03-27) =

*   first public alpha release

