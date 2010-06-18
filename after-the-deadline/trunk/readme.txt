=== After the Deadline ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/bb/atd/
Tags: after the deadline, atd, writing, spell, spelling, spellchecker, grammar, style, edit, proofread, Gautam
Requires at least: 1.0
Tested up to: 1.1
Stable tag: 1.7

After the Deadline plugin checks for spelling, style, and grammatical errors in your bbPress forum posts.

== Description ==

After the Deadline helps you to write better by spending less time editing.

When you activate the plugin and click the proofread link above a textbox, the plugin will check the content for spelling, style, and grammatical errors.

The proofreader supports English, French, German, Portuguese and Spanish. If your `BB_LANG` is one of these, then it becomes the default proofreading language. It can be configured via the settings page.

== Other Notes ==

= Thanks/Credits =
* [After the Deadline](http://www.afterthedeadline.com) service by Mudge, Raphael S. [Automattic](http://open.afterthedeadline.com)
* [jQuery Alert Dialogs Plugin](http://abeautifulsite.net/2008/12/jquery-alert-dialogs/) by [A Beautiful Site](http://abeautifulsite.net/)

= Translations =
* Hindi Translation by [Gautam](http://gaut.am/) (me)

You can contribute by translating this plugin. Please refer to [this post](http://gaut.am/translating-wordpress-or-bbpress-plugins/) to know how to translate.

= Donate =
You may donate by going [here](http://gaut.am/donate/bb/atd/).

= Todo =
Nothing for now

= License =
GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html

= Not on bbPress? =
You can get AtD elsewhere too! Please check [here](http://www.afterthedeadline.com/download.slp) for the list.

== Installation ==

1. Upload the extracted `after-the-deadline` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Enjoy!

== Frequently Asked Questions ==

= 1. The dialog boxes aren't looking as they should! =
Please make sure that the plugin directory (and the sub-folders and files in it) are chmodded to 755.

= 2. This doesn't work with bbPress 0.9! =
Please use AtD v1.4 or upgrade your bbPress install

*Please see [this FAQ](http://www.afterthedeadline.com/questions.slp) for more questions*

== Screenshots ==

1. The Plugin in Action
2. The Settings Page
3. The Settings on User's Profile Page (When AutoProofread, Ignore Types & Ignore Always Options are Checked)

== Changelog ==

= 1.8 (xx-xx-10) =
* Added an option to send the data over SSL
* Updated screenshots

= 1.7 (13-06-10) =
* Fixed a bug in which the API key sent to the server should be different for each user - [Check here](http://www.afterthedeadline.com/api.slp)
* The proxy script now makes use of `WP_Http` which is much more reliable
* Added an option for Accepting All Suggestions

= 1.6.1 (26-03-10) =
* Updated AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd_jquery/changelog.html)
* Fixed some upgrade notice issues

= 1.6 (23-03-10) =
* The following options have been added for the user (can also be mass-disabled from the admin page):
 * AutoProofread if the user has forgotten to run the spellcheck
 * Ignore always option
 * Ignore types
* Added an AJAX loader image
* Added screenshot of the profile edit page and updated admin page screenshot
* Updated hindi translation

= 1.5 (12-02-10) =
* The proofreader now supports English, French, German, Portuguese and Spanish
* Removed API key requirement
* Updated AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd_jquery/changelog.html)
* Removed compatibility with bbPress 0.9
* Improved coding efficiency
* Updated translations & screenshots

= 1.4 (17-01-10) =
* Addded localization support! Please refer to [this post](http://gaut.am/translating-wordpress-or-bbpress-plugins/) to know how to contribute!
* Updated AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd-jquery/changelog.html)
* Compatibility with anonymous posting feature, which will be released in bbPress 1.1
* Compressed Javascript
* Added Other Notes & Update Notice sections in ReadMe
* Now the plugin attaches itself to every textbox on every page of your forums (but only for logged in users, or if the anonymous posting feature is turned on which will be introduced in bbPress 1.1)

= 1.3 (22-11-09) =
* Added an option to enter the API key, please see FAQ for more information
* Now there is no limit of characters entered in the textbox (under normal conditions)
* The new DIV tries to mimic the the textbox, please see FAQ for more information
* Plugin notifies you when update is available, but only when you visit the settings page
* Doesn't check for spelling if no text has been entered
* Improvements in the coding and highly commented the PHP files
* Added a screenshot of the settings page
* Added FAQ in readme

= 1.2.1 (19-11-09) =
Upgraded AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd_jquery/changelog.html)

= 1.2 (5-11-09) =
* Compatibility with bbPress 0.9 version
* Upgraded AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd_jquery/changelog.html)
* Added Stylish Alert Dialogs - [Credits](http://abeautifulsite.net/2008/12/jquery-alert-dialogs/)
* Updated Screenshot

= 1.1 (29-10-09) =
* The button is now only shown to logged in users
* The button is now also shown when creating a new topic
* Merged three javascript files into one
* Improvement in the core code
* Updates to readme

= 1.0 (27-10-09) =
* Initial Release

== Upgrade Notice ==

= 1.8 =
Added some new features and fixed bugs.

= 1.5 =
Upgraded AtD/jQuery, added some more features and fixed bugs. Now no need of API key. Do not upgrade if you are using bbPress 0.9.