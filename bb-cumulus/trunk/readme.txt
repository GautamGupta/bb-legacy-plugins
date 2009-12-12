=== bb Cumulus ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/bbC/
Tags: tag cloud, flash, sphere, categories, widget, tags, 3D, cloud, bb, cumulus, Gautam
Requires at least: 1.0
Tested up to: 1.0.2
Stable tag: 1.0-beta

bb-Cumulus displays your forum's tags in 3D by placing them on a rotating sphere.

== Description ==

bb-Cumulus allows you to display your forum's tags using a Flash movie that rotates them in 3D.

It works just like a regular tags cloud, but is more visually exciting.
Clicking the tags can be a little hard (depending on your speed setting) but does take you to the appropriate page :).

The plugin automatically replaces the generated tag heat map with the 3D cloud, but you can place it at a custom location too. Please see Other notes for more information.
 
This plugin is based on the [WP Cumulus](http://wordpress.org/extend/plugins/wp-cumulus/) plugin by Roy Tanck and Luke Morton.

== Installation ==
1. Upload the extracted `bb-cumulus` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Open the plugin settings page `Settings` -> `bb-Cumulus`
4. Adjust settings to your liking.
5. Please see Other Notes & FAQ sections for more information.
6. Enjoy!

== Frequently Asked Questions ==

= My theme/site appears not to like this plugin. It's not displaying correctly. =
There are a number of things that may prevent bb-Cumulus from displaying or cause it to display a short message about how it needs the Flash plugin.

* In 99% of all cases where this happens the issue is caused by markup errors in the page where the plugin is used. Please validate your blog using [validator.w3.org](http://validator.w3.org) and fix any errors you may encounter.
* Older versions had issues with PHP 5.2 (or better). This has been fixed, so please upgrade to the latest version.
* The plugin requires Flash Player 9 or better and javascript. Please make sure you have both.

= Hey, but what about SEO? =
I'm not sure how beneficial tag clouds are when it comes to SEO, but just in case bb-Cumulus outputs the regular tag cloud for non-flash users. This means that search engines will see the same links.

= I'd like to change something in the Flash movie, will you release the .fla? =
The source code is available from wordpress.org under the GPL license. Visit [here](http://wordpress.org/extend/plugins/wp-cumulus/download/) and download the developer version.

= Some of my tags occasionally hit the sides of the movie and are cropped =
If this happens you should change the aspect for the movie to make it wider. This can be done by increasing the width, but also by decreasing the height. Both will make the movie 'more landscape' giving long tags more room.

= Some characters are not showing up =
Because of the way Flash handles text, only Latin characters are supported in the current version. This is due to a limitation where in order to be able to animate text fields smoothly the glyphs need to be embedded in the movie. The Flash movie's source code is available for download through Subversion. Doing so will allow you to create a version for your language. There's a text field in the root of the movie that you can use to embed more characters. If you change to another font, you'll need to edit the Tag class as well. More info [here](http://www.roytanck.com/2008/08/04/how-to-add-more-characters-to-wp-cumulus/).

= When I click on tags, nothing happens. =
This is usually caused by a Flash security feature that affects movies served from another domain as the surrounding page. If your forum is http://yourforum.com, but you have http://www.yourforum.com listed as the 'bbPress address' under Settings -> General this issue can occur. In this case you should adjust this setting to match your forum's actual URL. If you haven't already, I recommend you decide on a single URL for your blog and redirect visitors using other options. This will increase your search engine ranking and in the process help solve this issue :).

= I'm not using bbPress... =
* [WordPress Plugin](http://wordpress.org/extend/plugins/wp-cumulus/) by Roy Tanck and Luke Morton
* Steve Springett has ported this to Movable Type. More info over on [his site](http://www.6000rpms.com/blog/2008/04/04/flash-tag-cloud-for-mt-4.html).
* Michael Robinson has ported WP-Cumulus to RapidWeaver, see his tutorial [here](http://pagesofinterest.net/mikes/blog_of_interest_files/tag_cloud.php).
* Amanda Fazani managed to get Cumulus working on Blogger. More info on Blogumus [here](http://www.bloggerbuster.com/2008/08/blogumus-flash-animated-label-cloud-for.html).
* Yannick Lejeune has done a [TypePad version](http://www.yannicklejeune.com/2008/09/tumulus-wp-cumu.html) based in part on Steve's work.
* Christian Philipp's created a [TYPO3 version](http://typo3.org/extensions/repository/view/t3m_cumulus_tagcloud/current/).
* Rob Antonishen did a [Serendipity version](http://spartacus.s9y.org/index.php?mode=bygroups_event_en) (search for serendipity\_event\_freetag).
* Big Bear maintains the [Joomla version](http://joomlabear.com/Joomulus/).
* Pratul Kalia and Bjorn Jacob have ported it to [Drupal](http://drupal.org/project/cumulus).
* Ryan Tomlinson has a [BlogEngine.NET version](http://www.99atoms.com/post/BlogCumulusNET-A-flash-based-tag-cloud.aspx).
* Colin Seymour has created a [Habari version](http://www.lildude.co.uk/projects/hb-cumulus/).
* Andreas Scherer uses [DasBlog](http://www.scherer.as/blog/).
* Jean-Yves Zinsou did an [eZ version](http://ez.no/developer/contribs/applications/ezcumulus).
* [Simple Tags](http://utilitees.silenz.org/index.php/notes/page/simple-tags-1.6.3/), an Expression Engien addon can now display tags using Cumulus. Thanks Oliver Heine.
* Catchpen ported Cumulus to [Social Web CMS](http://forums.socialwebcms.com/index.php?topic=672.0).
* Domi create a [PHP-Fusion](http://www.venue.nu/forum/viewthread.php?thread_id=672) port.
* [Bysoft](http://www.bysoft.fr/) did a [Magento](http://www.magentocommerce.com/extension/925/3d-advanced-tags-clouds-based-on-wp-cumulus--admin-manager-by-bysoft) version.
* Benjamin Anseaume created a [Sweetcron](http://www.anseaume.com/items/site/anseaume.com) version.
* I wrote [this post](http://www.roytanck.com/2008/05/19/how-to-repurpose-my-tag-cloud-flash-movie/) on how to use the flash movie in other contexts.

== Screenshots ==

1. The tag sphere. You can set colors that match your theme on the plugin's options page.
2. The options panel.

== Other Notes ==

= In order to actually display the tag cloud, you have two options =
1. The Tag Heat Map is automatically replaced by the bb Cumulus Tag Cloud
2. You can also add the following code anywhere in your theme to display the cloud. `<?php if (function_exists('bb_cumulus_insert')) bb_cumulus_insert(); ?>` This can be used to add bb-Cumulus to your sidebar, although it may not actually be wide enough in many cases to keep the tags readable.

= Options =

The options page allows you to change the Flash movie's dimensions, change the text color as well as the background.

*Width of the Flash tag cloud*
The movie will scale itself to fit inside whatever dimensions you decide to give it. If you make it really small, chances are people will not be able to read less-used tags that are further away. Anything up from 200 will work fine in most cases.

*Height of the Flash tag cloud*
Ideally, the height should be something like 3/4 of the width. This will make the rotating cloud fit nicely, while the extra width allows for the tags to be displayed without cropping. Western text is horizontal by nature, which is why the ideal aspect is slightly landscape even though the cloud is circular.

*Color of the tags*
Type the hexadecimal color value you'd like to use for the tags, but not the '#' that usually precedes those in HTML. Black (000000) will obviously work well with light backgrounds, white (ffffff) is recommended for use on dark backgrounds. Optionally, you can use the second input box to specify a different color. When two colors are available, each tag's color will be from a gradient between the two. This allows you to create a multi-colored tag cloud. The third input box lets you specify a mouseover highlight color.

*Background color*
The hex value for the background color you'd like to use. This options has no effect when 'Use transparent mode' is selected.

*Use transparent mode*
Turn on/off background transparency. Enabling this might cause issues with some (mostly older) browsers.

*Rotation speed*
Allows you to change the speed of the sphere. Options between 25 and 500 work best.

*Distribute tags evenly on sphere*
When enabled, the movie will attempt to distribute the tags evenly over the surface of the sphere.

== Changelog ==

= 1.0-beta (12-12-09) =
* Initial release

== Upgrade Notice ==

= 1.0-beta (12-12-09) =
Initial release, just install