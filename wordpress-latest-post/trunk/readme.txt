=== Wordpress Latest Post ===
Donate link: http://www.atsutane.net/
Tags: bbpress, wordpress, latest, post, bbpress, topic, plugin, sidebar, page
Contributors: Atsutane
Requires at least: 0.9.4
Tested up to: 1.0
Stable Tag: trunk

This plugin will generates Wordpress Latest Post list inside your bbpress forum.

== Description ==

This plugin will generates Wordpress Latest Post list inside your bbpress forum.

If you like my work, Please give a link back.

== Installation ==

Simply download the Zip-Archive and extract all folder into your `my-plugins` directory. Then go into your BbPress administration page, click on Plugins and activate it. Open `wp2bbpress.php` with your favorite text editor to config the plugin.

I’ve created one template tags you can use in your pages:

1. `get_wp_postdata('title')` : Show Bbpress latest discussion on wp static page.

Examples of use:

`<?php if (function_exists('get_wp_postdata')) { get_wp_postdata('title'); } ?>`

More Documentation <a href="http://www.atsutane.net/2009/04/wordpress-inside-bbpress-part-1.html">Here</a>.

== Frequently Asked Questions ==

-

== Version History ==

Version 0.2 (2009-04-28):

* Add permalink support.

Version 0.1 (2009-04-28):

* Initial Release.
