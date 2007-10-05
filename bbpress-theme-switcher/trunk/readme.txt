=== bbPress Theme Switcher  ===
Tags: theme, templates, change, switch
Contributors: _ck_
Requires at least: 0.8
Tested up to: trunk
Stable tag: trunk

Allow your forum visitors to switch between any themes you have installed. Automatically/optionally inserts dropdown in bottom right of all themes.

== Description ==

Adapted from Ryan Boren WordPress theme switcher which was adapted from Alex King's WordPress style switcher http://www.alexking.org/software/wordpress/

== Instructions ==

Install, activate. 

Optionally add the following to your sidebar menu for manually placement of the switcher (or use the automatically dropdown in the bottom right).

  <li>Themes:
	<?php bb_theme_switcher(); ?>
  </li>

This will create a list of themes for your readers to select.

If you would like a dropdown box rather than a list, add this:

  <li>Themes:
	<?php bb_theme_switcher('dropdown'); ?>
  </li>

== License ==

CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Version History ==

Version 1.05 (2007-08-06)

*   bb-theme-switcher is born

Version 1.06 (2007-10-05)

*   update for first public release
