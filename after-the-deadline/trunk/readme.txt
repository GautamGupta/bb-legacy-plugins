=== After the Deadline ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/AtD/
Tags: after the deadline, writing, spell, spelling, spellchecker, grammar, style, plugin, edit, proofreading, Gautam
Requires at least: 0.9
Tested up to: 1.0.2
Stable tag: 1.3

After the Deadline plugin checks for spelling, style, and grammatical errors in your bbPress forum posts.

== Description ==

After the Deadline helps you to write better by spending less time editing.

When you activate the plugin and click the proofread link above a textbox, the plugin will check the content for spelling, style, and grammatical errors.

There is also an option for the administrator to enter an [API key](http://www.afterthedeadline.com/profile.slp). Please see [FAQ](http://bbpress.org/plugins/topic/after-the-deadline/faq/) for more information.

== Other Notes ==

= Thanks/Credits =
* [After the Deadline](http://www.afterthedeadline.com) service by Mudge, Raphael S. [Automattic](http://open.afterthedeadline.com)
* [jQuery Alert Dialogs Plugin](http://abeautifulsite.net/2008/12/jquery-alert-dialogs/) by [A Beautiful Site](http://abeautifulsite.net/)

= Translations =
You can contribute by translating this plugin. Please refer to [this post](http://gaut.am/) to know how to translate.

= Notes =
* Only English language is supported for the time being.

= To Do =
* Add ignore always option
* Add the option to let the user select ignore types
* AutoProofread if the user has forgotten to run the spellcheck

= Not on bbPress? =
You can get AtD elsewhere too! Here are the current platforms:

* [Bookmarklet](http://www.afterthedeadline.com/download.slp?platform=Bookmarklet) - A utility to AtD to any web-page with one click
* [Intense Debate](http://www.afterthedeadline.com/download.slp?platform=IntenseDebate) - Distributed Comment System for Blogs and Websites
* [PHP List](http://www.afterthedeadline.com/download.slp?platform=PHPList) - Open Source Newsletter Manager
* [RoundCube Webmail](http://www.afterthedeadline.com/download.slp?platform=RoundCube) - Browser-based IMAP Client
* [WordPress](http://www.afterthedeadline.com/download.slp?platform=Wordpress) - Blog Tool and Publishing Platform

Please check [here](http://www.afterthedeadline.com/download.slp) for an updated list.

= Donate =
* You may donate by going [here](http://gaut.am/donate/AtD/).

== Installation ==

1. Upload the extracted `after-the-deadline` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Optional (but recommended) - Enter an API key by going to `Settings -> After the Deadline`
4. Enjoy and always write correct English!

== Frequently Asked Questions ==

= 1. Do I need an API key to run the plugin? =
The plugin allows the administrator to enter an API key. It is suggested that you enter one, though it is optional.

= 2. Where do I get an API key? =
You can get one by logging in/signing up [here](http://www.afterthedeadline.com/profile.slp).

= 3. What is the disadvantage of not entering an API key? =
AtD allows one call at a time/key. This means if a lot of people are using the same (default) key then their & your performance will degrade as more people use it.

= 4. The DIV is not looking like the textbox! =
If you are using a kakumei based theme, then open the style.css file of the theme and follow these steps:

1. Find `.postform textarea {`
2. Replace it with `#post_content {`
3. Save the file and upload it to the server

= 5. Things aren't going as they should! Some `fsockopen` error is coming! =
This means that `fsockopen` function is not enabled on your webserver. Please use the [1.3 version](http://bbpress.org/plugins/topic/after-the-deadline/after-the-deadline.1.3.zip) and follow these steps to fix this error:

1. Open `scripts/atd.js` which is located in the plugin folder.
2. Find `AtD.checkTextArea('post_content', 'checkLink', 'Edit Text');` (6th line)
3. Replace it with `AtD.checkTextAreaCrossAJAX('post_content', 'checkLink', 'Edit Text');`
4. Save the file and upload it to the server.

If you do this, you cannot add an API key, and the text limit (2000 for Internet Explorer & 7000 for others) will be imposed.

= 6. The directory of the plugin is not being matched properly! =
If the directory of the plugin could not be matched for some reason, then define `ATD_PLUGPATH` in `bb-config.php` file which is the full URL path to the plugin directory
It should be set to something like `http://www.example-domain.tld/forums/my-plugins/after-the-deadline/`

= 7. The dialog boxes aren't looking as they should! =
Please make sure that the plugin directory is chmodded to 755.

*Please see [this FAQ](http://www.afterthedeadline.com/questions.slp) for more questions*

== Screenshots ==

1. After the Deadline Plugin in Action
2. A Screenshot of the Settings Page

== Changelog ==

= 1.4 (17-01-10) =
* Addded localization support! Please refer to [this post](http://gaut.am/) to know how to contribute!
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
Upgraded AtD/jQuery - [Changelog](http://www.polishmywriting.com/atd_jquery/changelog.html):

* Updated edit selection ability to keep phrase highlighted if no change was made
* Fixed a character escaping issue
* AtD now restores missing accents to English words borrowed from other languages

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

= 1.4 (17-01-10) =
Upgraded AtD/jQuery, added some more features and fixed bugs.