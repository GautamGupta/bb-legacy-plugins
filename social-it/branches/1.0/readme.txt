=== Social It ===
Contributors: Gautam Gupta
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6590760
Tags: socialit,social bookmarking,bookmarks menu,digg,delicious,furl,myspace,twitter,facebook,technorati,reddit,yahoo,gautam
Requires at least: 1.0
Tested up to: 1.0
Stable tag: 1.0

== Description ==

Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, feeds etc. See configuration panel for more settings.
This plugin is inspired from the SexyBookmarks plugin for Wordpress (http://sexybookmarks.net/) made by Josh & Norman.
It is also compatible with Support Forum plugin (http://bbpress.org/plugins/topic/support-forum/) Made by Aditya Naik & Sam Bauers.

== Screenshots ==

1. A quick preview of the final outcome

== Installation ==

1. Upload the extracted archive to 'my-plugins/'
2. Activate the plugin through the 'Plugins' menu
3. Open the plugin settings page Settings -> Social It
4. Adjust settings to your liking
4. Enjoy!

= Manual Usage =
**A menu can be inserted once anywhere within your site (even outside the loop) and it will still pull the appropriate data for the dynamic links**

If you would like to insert the menu manually, then place the following code into your theme files where you want the menu to appear:

<?php if(function_exists('selfserv_socialit')) { selfserv_socialit(); } ?>

You can still configure the other options available when inserting manually and they will be passed to the function. This is for those of you who have requested to be able to place the menu anywhere you choose... Enjoy!


== Frequently Asked Questions ==

= I've uploaded the plugin and activated, but it's not showing up or it's broken… =

This is normally due to styles in your bbPress theme overriding the styles of the plugin. Check your theme's stylesheet for rules like !important; as these may be overriding the styles defined by the plugin.

= My favorite bookmarking site isn't listed! =

You can contact me with the name of the site and the URL, and I will work on releasing it with a future update.

= I'm a bbPress theme developer, and I'd like to bundle your plugin with my themes. Is this okay? =

Absolutely, yes!

= I've found a bug not covered here, where do I report it? =

Please report all bugs via the comment form below for quickest response and notation time. Otherwise, you can choose to email me via the [contact form](http://gaut.am/contact) located on my site


== Changelog ==

* 1.0	Initial release