=== Social It ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/
Tags: SocialIt, Social Bookmarking, Social, Bookmarks Menu, Twitter, Digg, Delicious, StumpleUpon, Reddit, Diigo, YahooBuzz, Technorati, Mixx, Facebook, Myspace, DesignFloat, GoogleBookmarks, Blinklist, Script & Style, LinkedIn, Newsvine, Devmarks, Mister Wrong, Izeby, Tipd, Friendfeed, BlogMarks, Twittley, Fwisp, DesignMoo, Short URL, Gautam
Requires at least: 0.9.0.6
Tested up to: 1.0.2
Stable tag: 1.2

Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, feeds etc. See configuration panel for more settings.

== Description ==

Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, feeds etc. See configuration panel for more settings.

It can add the following social bookmarks: Twitter, Digg, Delicious, StumpleUpon, Reddit, Diigo, YahooBuzz, Technorati, Mixx, Facebook, Myspace, DesignFloat, GoogleBookmarks, Blinklist, Script & Style, LinkedIn, Newsvine, Devmarks, Mister Wrong, Izeby, Tipd, Friendfeed, BlogMarks, Twittley, Fwisp, DesignMoo, and some Russian Websites. If you do not find your favourite bookmarking site here, please contact me.

It can also display the feed URL of the current page you are on (If no feed URL is available, it uses the main feed URL).

Social It can also shorten the Permalinks and offers a list of Short URL Services. You can choose any of those, and it will be used to shorten the URLs.

It automatically fetches description, title, content, etc. Still, on all pages it is not possible. So please do not forget to check the Settings page of the Plugin, and configure it correctly. Also do not leave the description of your forum blank.

It has a variety of options which you can choose from, and displays the list of Social Bookmarks in Style!

This plugin is inspired from the <a href="http://sexybookmarks.net/">SexyBookmarks plugin</a> for Wordpress made by Josh & Norman.
It is also compatible with <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum plugin</a> Made by Aditya Naik & Sam Bauers.

== Installation ==

1. Upload the extracted `social-it` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Open the plugin settings page Settings -> Social It
4. Adjust settings to your liking.
5. Enjoy!

== Frequently Asked Questions ==

= I've uploaded the plugin and activated, but it's not showing up or it's broken =

This is normally due to styles in your bbPress theme overriding the styles of the plugin. Check your theme's stylesheet for rules like !important; as these may be overriding the styles defined by the plugin.

= My favorite bookmarking site isn't listed! =

You can contact me with the name of the site and the URL, and I will work on releasing it with a future update.

= I'm a bbPress theme developer, and I'd like to bundle your plugin with my themes. Is this okay? =

Absolutely, yes!

= I've found a bug not covered here, where do I report it? =

Please report all bugs via the post form below for quickest response and notation time. Otherwise, you can choose to email me via the [contact form](http://gaut.am/contact) located on my site

== Screenshots ==

1. A quick preview of the final outcome - screenshot-1.jpg.
2. Screenshot of the settings page - screenshot-2.png.

== Changelog ==

= 1.2 =
* Added i18n / l10n support
* Now compatible with bbPress 0.9.0.6
* Added new "Share and Enjoy" image
* Added Fwisp, DesignMoo and some other Russian Websites to the list
* Twitter character encoding bug totally fixed
* Advanced Short URL Management added (where you can add your username and/or API Key/Password of the short URL website)
* bit.ly added and ri.ms & short.to removed from Short URL List
* Import & Export Options
* Option to show or not to show menu to Mobiles and Bots
* Compatibility with bbAttachments Plugin

= 1.1 =
* Some bug fixes
* Bookmarks are also shown in the feed, but in simple list, not in style
* Auto Updater

= 1.0 =
* Initial Release

== Arbitrary section ==

= Manual Usage =
**A menu can be inserted once anywhere within your site (even outside the loop) and it will still pull the appropriate data for the dynamic links**

If you would like to insert the menu manually, then place the following code into your theme files where you want the menu to appear:

`<?php if(function_exists('selfserv_socialit')) { selfserv_socialit(); } ?>`

You can still configure the other options available when inserting manually and they will be passed to the function. This is for those of you who have requested to be able to place the menu anywhere you choose... Enjoy!

You can also get a short URL of a web page (whereever you call this function on your site). Note that the plugin must be activated:
`<?php if(function_exists('socialit_get_fetch_url')) { socialit_get_fetch_url(); } ?>`