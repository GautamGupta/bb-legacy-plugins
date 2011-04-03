=== Simple Facebook Connect ===
Tags: simple, facebook, connect
Contributors: moogie1
Tested up to: 1.0.3
Requires at least: 1.0

Adds a single-click seamless Facebook Connect -based registration/login to your bbPress.

== Description ==

Adds a single-click seamless Facebook Connect -based registration/login to your bbPress. The user is not bothered with anything extra, and is automatically logged in right after accepting the Facebook authorization popup.

DEMO SITE: http://moogie.idle.fi/bbpress-demo/

Very simple installation, at minimum only two changes required on your blog:

* edit the theme header.php, and add the facebook xmlns param to the `<html>` tag
* edit the theme, and place `<?php fb_login_button(); ?>` where you want the login button to appear

A Facebook Application ID and Secret is obviously required, as the Facebook Connect requires these.

Other features:

* Facebook Avatars are displayed automatically (if avatars are enabled in your bbPress settings)
* No unnecessary registration email spam is sent to the user. 
* Disables password edit for facebook connected users; the random password assigned to the account is never used
* Select how users Display Name is set from Facebook (Full Name, First Name or Last Name)
* (optional) Disables profile edit for facebook connected users
* (optional) Request users email address from facebook. If disabled, a dummy email is set for the user.
* (optional) Hide "You must login" from post form, which leads to the traditional login form. This would confuse Facebook Connected users, as they cannot login traditionally.
* Select how to initialize Facebook Javascript SDK - automatically (when needed), always or never. Can solve conflicts with other plugins/themes using the Facebook SDK.

== Installation ==

= Prerequirements =

1. PHP 5 (tested on 5.2 and 5.3)
2. PHP CURL and JSON extensions (required by Facebook's PHP connector)

= Installation =

1. Unzip plugin zip to plugin folder. Make sure that the whole simple_facebook_connect folder is there.
2. Activate plugin in bb-admin
3. Configure plugin (bb-admin -> Settings -> Facebook Connect)
4. Edit your theme's header.php:
4.1. look for `<html>` tag at the top, and add `xmlns:fb="http://www.facebook.com/2008/fbml"` inside the tag
4.2. the result should look something like: `<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml"<?php bb_language_attributes( '1.1' ); ?>>`
5. Edit your theme, and place the facebook login button on a suitable place. Use the function fb_login_button() to add it:
5.1. `<?php if ( function_exists ( 'fb_login_button' ) ) { fb_login_button(); } ?>`
6. DONE

== Other Notes ==

= Why are the avatars not showing up? =
Have you enabled avatars in 'bb-admin -> Settings -> Discussion' ?

= Why is the avatar of such poor quality? I want a big good quality image! =
The only square image Facebook provides is 50px in height and width. All the larges images sizes are not squared, and would thus likely display distorted.

= I get a strange error from Facebook in the login popup. What's up? =
1. Check that your Application ID and Secret are correct
2. In Facebook Application Settings, check that OAuth 2.0 is enabled (Advanced-tab)
3. In Facebook Application Settings, add your sites domain name (domain.com if your site is www.domain.com) to 'Site Domain' field (Web Site-tab)

= What if I have other plugins using the Facebook as well, will these conflict? =
If the plugins are using the Facebook Javascript SDK, then yes, very likely.

This plugin has an option to disable the initialization of the Javascript SDK. This should be the first thing to try if you have a conflict situation. The same Application ID/Secret needs to be used by all Facebook-enabled plugins/themes.

== Changelog ==

= Version 1.0 (2011-03-21) =

* First public release

= Version 1.0.1 (2011-03-26) =

* Fixed facebook.php include conflict
* Fixed compatibility with wordpress integration

= Version 1.0.2 (2011-03-28) =

* Users with a Facebook ID >= 2147483647 would not work properly due to PHP limitation in intval() function. Fixed.

= Version 1.0.3 (2011-04-03) =

* Improved user login name sanitization
* Fixed feature for hiding "You must login" in the post form
