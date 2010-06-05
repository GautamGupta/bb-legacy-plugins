=== Facebook Like ===
Contributors: GautamGupta
Donate link: http://gaut.am/donate/bb/fbl/
Tags: facebook like button, facebook button, like button, social plugin, facebook like, like, share, facebook, like, button, social, bookmark, sharing, bookmarking, widget, open graph, opengraph, protocol, Gautam
Requires at least: 1.0
Tested up to: 1.1
Stable tag: 0.1

The plugin adds a Facebook 'Like' button to your forum topics (under the topic title).

== Description ==
The plugin adds a Facebook 'Like' button to your forum topics (under the topic title) and lets your readers quickly share your content on Facebook with a simple click.

It uses the new Facebook Like button released on Apr. 21st 2010.

You can customize it in the Settings section:

* Access directly your Facebook Pages to manage them
* Send updates to your Fans
* IFRAME or XFBML versions of the button
* Asynchronous or Synchronous loading of the Javascript
* Width/Height
* Layout (standard or button_count)
* Verb to display (Like or Recommend)
* Fonts
* Color Scheme (Light or Dark)
* Show thumbnails of Facebook profile pictures
* Align to the Left or Right of your posts
* Show at the top and/or bottom of posts
* Show/hide the button on pages/posts/home/search/archive.
* Margins (top, bottom, left, right)
* Complete support of the [Open Graph protocol](http://opengraphprotocol.org)

The plugin configures automatically all the Open Graph Meta tags you need in your HTML header:

* og:site_name
* og:title
* og:type
* og:description
* og:url
* og:image (configured in the Settings)
* fb:admins (configured in the Settings)
* fb:app_id (configured in the Settings)
* fb:page_id (configured in the Settings)

All other Open Graph options are available:

* og:latitude
* og:longitude
* og:street-address
* og:locality
* og:region
* og:postal-code
* og:country-name
* og:email
* og:phone_number
* og:fax_number
* og:type

This is a port of the [WordPress Like Plugin](http://wordpress.org/extend/plugins/like/) by [Bottomless](http://blog.bottomlessinc.com/)

== Installation ==

1. Upload the extracted `facebook-like` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Customize the plugin in the Settings -> Facebook Like
4. Enjoy Liking!

== Frequently Asked Questions ==

= The Button doesn't appear in IE / XHTML validation errors =
You would have to edit your theme's `header.php`, and change this code:

`&lt;html xmlns="http://www.w3.org/1999/xhtml"<?php bb_language_attributes( '1.1' ); ?>&gt;`

To:

`&lt;html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"<?php bb_language_attributes( '1.1' ); ?>&gt;`

= Do I need to provide my Facebook ID? =
No. The plugin works out of the box. Adding your Facebook ID allows you to administer your pages so you would get more functionalities if you provide it.

= How to get my Facebook ID? =
You need your NUMERICAL Facebook ID (ex: `68310606562` and not `markzuckerberg`).

Click on your Facebook profile and look at the URL, it should resemble this: http://www.facebook.com/profile.php?id=68310606562 where `68310606562` is your Facebook user ID.

If you have a username (ex: `markzuckerberg`), lookup your user ID with this URL: http://graph.facebook.com/markzuckerberg

Be careful when adding your Facebook ID as it must always be present later on (see the errors question below).

= My Page has many Likes but I don't see the Admin link =
You need to enter your NUMERCIAL Facebook ID in the Settings. You also need to Like your own page.

= I get a red "Error" when clicking the Like Button =

Click on the red Error link and a popup will give you more information.

Here are some common errors reported by Facebook:

_You previously specified `68310606562` as the leading admininstatory in the `fb_admins` meta tag. The `fb_admins` tag now specifies that 666 is the leading administrator. That needs to be changed back._

* You changed your Facebook ID in the Settings of the plugin.
* Make sure you keep the original one as the first one.
* You can optionally add other Facebook IDs by separating them with commas.

_Your page no longer includes any admininstrator IDs, even though you've specified one before. You must include `68310606562` in the `fb_admins meta` tag, and it must be the very first one if there are many._

You simply removed your previously entered Facebook ID in the Settings of the plugin. Put it back and be sure to use the original one.
If specifying several comma-separated Facebook IDs to administer your pages, be sure the original one appears first.

_The application ID specified within the `fb:app_id`. meta tag is not allowed on this domain. You must setup the Connect Base Domains for your application to include this domain._

* You are NOT using XFBML: This error from Facebook is confusing, in fact they relly mean that the fb:admins is incorrect (you probably entered an ID of a Facebook page instead of your own Numeric Facebook user ID)
* You are using XFBML and have a Facebook Application: Chances are you are just missing a slash at the end of your Connect URL. Edit your Facebook Application settings, go to the Connect tab, and add a slash at then end of your domain name in the first field called "Connect URL".

For instance your domain name should read `http://cyberfundu.com/` and not `http://cyberfundu.com`.

_You failed to provide a valid list of administators. You need to supply the administors using either a fb:app_id meta tag, or using a fb:admins meta tag to specify a comma-delimited list of Facebook users._
* You provided your string Facebook ID (ex: `markzuckerberg`) instead of your numerical Facebook ID (ex: `68310606562`).
* Change the Facebook ID field to the numerical one in the Settings of the plugin.

== Screenshots ==

1. The Plugin in Action
2. The Settings Page

== Other Notes ==

= Donate =
You may donate by going [here](http://gaut.am/donate/bb/fbl/).

= Translations =
You can contribute by translating this plugin. Please refer to [this post](http://gaut.am/translating-wordpress-or-bbpress-plugins/) to know how to translate.

= Todo =
Nothing for now

= License =
[GNU General Public License version 3](http://www.opensource.org/licenses/gpl-3.0.html).

== Changelog ==

= 0.1 (June 5, 2010) =
* Initial Release

== Upgrade Notice ==

= 0.1 =
Initial Release