=== zaerl Visibility ===
Contributors: zaerl
Donate link: http://bit.ly/cJKknC
Tags: visibility, hidden forum, hidden topic, private forum, private topic, zaerl
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 0.2.1

Hide/Lock of forums/topics.

== Description ==

zaerl Visibility adds the possibility to hide/lock forums/topics/profiles. Forums or single topics can be hidden/locked for specific roles and/or for a list of users. Profiles pages can be hidden for specific roles and/or for a list of users.

An hidden forum/post doesn't show on pages and RSS.

A locked forum is closed to new topics.

A locked topic is closed to new replies which is different from the built-in "closed" status that deny the possibility of replying to all users.

An hidden profile page can't be accessed.

You can specify super users and super roles that aren't affected by all other rules.

== Installation ==

Extract the zip file and just drop the contents in the my-plugins/ directory of your bbPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

= How do I modify the visibility of a topic? =

Using the apposite link in the admin area in the footer of the topic.

= How do I modify the visibility of a profile page? =

Using the apposite link in the profile page (/profile/profile_name).

= zaerl Visibility doesn't allow me to hide all forums. =

Unfortunately you can't specify a particular mix of rules that hide all forums. This is due to the fact that the bbPress engine assumes that there is a least one forum and otherwise it spawns an error.

= How do I create a forum that is visible only to registered users? =

Select "Inactive" and "Blocked" roles hide rules.

= What are the template functions exported? =

zaerl Visibility exports one function:

`za_page_is_hidden($role)`

That returns `true` if the current page is hidden for that particular role. Example:

`if(za_page_is_hidden('inactive')) echo 'Only registered members can see me';`

The function return `null` if an error has occurred. Please notice that if you use such functions in you template and you deactivate the plugin a PHP error will be spawn cause the PHP parser doesn't find a function with that name. This is the best use of zeal Visibility functions:

`if(defined('ZA_VI_ID'))
{
   // here you can call za_page_is_hidden
}`

== Changelog ==

= 0.2 =
* Fixed some typos

= 0.2 =
* Profile pages hiding
* Template functions
* Roles displayed by name
* Added clarification text for the inactive role
* Fixed several bugs regarding incorrect handling of exceptions lists
* Tag replacement message has been removed

= 0.1.4 =
* Corrected tag replacement message
* Corrected replacement messages loading

= 0.1.2 =
* Initial release

== Upgrade Notice ==

= 0.1.4 =
This version fixes two important bugs.  Upgrade immediately.  If you have problems loading the plugin delete the "za_visibility" option from the meta database table.
