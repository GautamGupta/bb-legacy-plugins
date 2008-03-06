=== bbPress Theme Switcher  ===
Tags: theme, templates, change, switch, _ck_
Contributors: _ck_
Requires at least: 0.8
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Allow your forum visitors to switch between any themes you have installed. 
Automatically (optionally) inserts dropdown in bottom right of all themes. 
Optional timer to return to default theme.

== Description ==

Inspired by Ryan Boren's WordPress theme switcher which was adapted from Alex King's WordPress style switcher http://www.alexking.org/software/wordpress/

== Installation ==

* Install, activate. Look in the bottom right hand corner to see the drop-down switcher.  Read FAQ for customization abilities.

== Frequently Asked Questions ==

* Optionally add the following to your sidebar menu for manually placement of the switcher (or use the automatically dropdown in the bottom right).

  `<li>Themes:
	<?php bb_theme_switcher(); ?>
  </li>`

This will create a list of themes for your readers to select.

* If you would like a dropdown box rather than a list, add this:

  `<li>Themes:
	<?php bb_theme_switcher('dropdown'); ?>
  </li>`

* Theme timeout is set to 3 minutes (180 seconds) by default.  Look around line 31 to change this to however long you'd like, ie. 999999 = virtually forever, 30 = half-minute.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 1.05 (2007-08-06) =

*   bb-theme-switcher is born

= Version 1.06 (2007-10-05) =

*   update for first public release

= Version 1.08 (2007-1-25) =

*   update for alpha 0.8.4 builds >981 (backward compatible)

= Version 1.10 (2008-2-09) =

* enhanced to return to original location after theme switch instead of front page, also small bug fixes/tweaks

= Version 1.14 (2008-3-06) =

* update to match theme method in 0.8.4 while remaining backward compatible with 0.8.3
