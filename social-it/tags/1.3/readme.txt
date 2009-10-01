=== Social It ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/
Tags: Social It, Social Bookmarking, Social, Bookmarks Menu, Twitter, Digg, Delicious, StumpleUpon, Reddit, Diigo, YahooBuzz, Technorati, Mixx, Facebook, Myspace, DesignFloat, GoogleBookmarks, Blinklist, Script & Style, LinkedIn, Newsvine, Devmarks, Mister Wrong, Izeby, Tipd, Friendfeed, BlogMarks, Twittley, Fwisp, DesignMoo, BobrDobr, Yandex, Memory.ru, 100 bookmarks, MyPlace, Short URL, Gautam
Requires at least: 1.0
Tested up to: 1.0.2
Stable tag: 1.3

Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, feeds etc. See configuration panel for more settings.

== Description ==

Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, feeds etc. See configuration panel for more settings.

It can add the following social bookmarks: Twitter, Digg, Delicious, StumpleUpon, Reddit, Diigo, YahooBuzz, Technorati, Mixx, Facebook, Myspace, DesignFloat, GoogleBookmarks, Blinklist, Script & Style, LinkedIn, Newsvine, Devmarks, Mister Wrong, Izeby, Tipd, Friendfeed, BlogMarks, Twittley, Fwisp, DesignMoo, and some Russian Websites like BobrDobr, Yandex, Memory, 100 bookmarks, MyPlace. If you do not find your favourite bookmarking site here, please contact me.

It can also display the feed URL of the current page you are on (If no feed URL is available, it uses the main feed URL).

Social It can also shorten the Permalinks and offers a list of Short URL Services. You can choose any of those, and it will be used to shorten the URLs.

It automatically fetches description, title, content, etc. Still, on all pages it is not possible. So please do not forget to check the Settings page of the Plugin, and configure it correctly.

It has a variety of options which you can choose from, and displays the list of Social Bookmarks in Style!

This plugin is inspired from the <a href="http://sexybookmarks.net/">SexyBookmarks plugin</a> for Wordpress made by Josh & Norman.

== Installation ==

1. Upload the extracted `social-it` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Open the plugin settings page `Settings` -> `Social It`
4. Adjust settings to your liking.
5. Please see Usage & FAQ sections for more information.
6. Enjoy!

== Frequently Asked Questions ==

= The menu shows up as a regular list with no styling and no images! =
Unfortunately, this is becoming a more prevalent problem recently and it's due to your WordPress theme not having the function reference `bb_head()` in the **header.php** file as it should. Social It uses this function to hook the associated stylesheet and javascript files into the `<head>` of your document. So if it doesn't exist, then the stylesheet and/or javascript files won't be included on your site.

= I see blank spaces where icons used to be! =
This means that whatever service was previously in that space has been removed from the plugin either permanently or temporarily as we work out bugs or incorporate upgraded functionality. To remove the blank space, simply follow the detailed instructions found on the actual [FAQ Page](http://sexybookmarks.net/documentation/faq#17).

= My jQuery slider/fader doesn't work anymore! =
Please disable both of the jQuery dependent options (auto-center and animate-expand) in the plugin options area. We are working on a solution to make the plugin FULLY compatible with ALL themes, but have not reached that point yet... Sorry.

= Your plugin broke my site and there's a ton of stuff from another site being displayed!!! =
This isn't as critical as it may look... Simply choose another URL shortening service and select the "Clear all short URLs" option. Now save the changes and [report which URL shortening service you were using](http://gaut.am/contact/) that broke your site so I can look into it.

= I've uploaded the plugin and activated, but it's not showing up or it's broken… =
This is normally due to styles in your Wordpress theme overriding the styles of the plugin. Check your theme's stylesheet for rules like `!important;` as these may be overriding the styles defined by the plugin.

= My favorite bookmarking site isn't listed! =
You can contact me with the name of the site and the URL, and I will work on releasing it with a future update.

= I'm a Wordpress theme developer, and I'd like to bundle your plugin with my themes. Is this okay? =
Absolutely, yes!

= I've found a bug not covered here, where do I report it? =
Please report all bugs via the [Bug Report Form](http://gaut.am/contact/) for quickest response and notation time.

== Screenshots ==

1. A quick preview of the final outcome
2. Screenshot of the settings page
3. Feature - Hide/Show Bookmarks on topic by topic basis

== Changelog ==

= 1.3 (1-10-09) =
* Fixed a compatibility issue with WordPress-bbPress syncronization Plugin - Thanks to [Torsten Kruger](http://xtcmodified.org/) for the fix
* Changed e7t.us short url to b2l.me
* Fixed admin settings page fatal error
* Changed Twitter message from RT @username to (via @username)
* Fixed bug causing Twittley default category not to hold it's value
* Added ability to turn Social It on/off on a topic by topic basis
* Updated Readme file
* Some CSS Fixes
* Back-end coding enhancements & bug fixes

= 1.2 (5-9-09) =
* Added i18n / l10n support
* Added new "Share and Enjoy" image
* Added Fwisp, DesignMoo and some other Russian Websites to the list
* Twitter character encoding bug totally fixed
* Advanced Short URL Management added (where you can add your username and/or API Key/Password of the short URL website)
* bit.ly added and ri.ms & short.to removed from Short URL List
* Import & Export Options
* Option to show or not to show menu to Mobiles and Bots
* Compatibility with bbAttachments Plugin

= 1.1 (9-8-09) =
* Some bug fixes
* Bookmarks are also shown in the feed, but in simple list, not in style
* Auto Updater
* CSS fix to remove the top border from the menu list

= 1.0 (27-7-09) =
* Initial Release

== Usage ==

**A menu can be inserted once anywhere within your site (even on non-topic pages) and it will still pull the appropriate data for the dynamic links**

If you would like to insert the menu manually, then place the following code into your theme files where you want the menu to appear:
`<?php if(function_exists('selfserv_socialit')) { selfserv_socialit(); } ?>`

You can still configure the other options available when inserting manually and they will be passed to the function.

**There are some other things that plugin can do, like:-**

You can also get a short URL of a web page by this function:
`<?php if(function_exists('socialit_get_fetch_url')) { socialit_get_fetch_url(); } ?>`

You can get the feed link of any page you are on (in bbPress) by this function:
`<?php if(function_exists('socialit_get_current_rss_link')) { socialit_get_current_rss_link(); } ?>`
Note - It returns only posts feed links.

Note that the plugin must be activated to use the above functions.