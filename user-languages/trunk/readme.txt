=== User Languages ===
Tags: user, languages, A1ex
Contributors: A1ex
Tested up to: trunk
Stable tag: trunk

Allow users to set their own language. This plugin is based on the "User Timezones" plugin.

== Description ==

Allow users to set their own language. This plugin is based on the "User Timezones" plugin.

== Installation ==

* This plugin requires a litle modification of the bb-settings.php file.

Code
`
// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(BB_PATH . BB_INC . 'locale.php');
$bb_locale = new BB_Locale();

$bb_roles  = new BB_Roles();
do_action('bb_got_roles', '');
`

should be replaced with
`
$bb_roles  = new BB_Roles();
do_action('bb_got_roles', '');

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(BB_PATH . BB_INC . 'locale.php');
$bb_locale = new BB_Locale();
`

* Add the `user-languages.php` file to bbPress' `my-plugins/` directory and activate.

