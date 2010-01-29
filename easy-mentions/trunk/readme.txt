=== Easy Mentions ===
Contributors: Gautam Gupta
Donate link: http://gaut.am/donate/EM/
Tags: easy, mention, username, link, reply, twitter, Gautam
Requires at least: 1.0
Tested up to: 1.1
Stable tag: 0.1.1

Easy Mentions allows the users to link to other users' profiles in posts by using @username (like Twitter).

== Description ==

Easy Mentions allows the users to link to other users' profiles in posts by using `@username` (like Twitter).

Just make a new post, write the content with a `@username` (can be any username) in the text. When you submit the post, the plugin will automatically link the usernames (which exist) to their profile. Note that the plugin doesn't link the usernames with spaces.

The plugin can also add a Reply link below each post and when clicked, adds `@username` with the *post link* in the post textbox. This can be enabled via the settings page, but before that please see #1 question in the [FAQ](http://bbpress.org/plugins/topic/easy-mentions/faq/).

== Other Notes ==

= Translations =
* Hindi Translation by [Gautam](http://gaut.am/) (me)

You can contribute by translating this plugin. Please refer to [this post](http://gaut.am/translating-wordpress-or-bbpress-plugins/) to know how to translate.

= To Do =
* Add the option to link `#tag` to a tag.

= License =
GNU General Public License, Version 3: http://www.gnu.org/licenses/gpl-3.0.txt

= Donate =
* You may donate by going [here](http://gaut.am/donate/EM/).

== Installation ==

1. Upload the extracted `easy-mentions` folder to the `/my-plugins/` directory
2. Activate the plugin through the 'Plugins' menu in bbPress
3. Optional - Change the plugin's settings by going to `Settings` -> `Easy Mentions`
4. Enjoy!

== Frequently Asked Questions ==

= 1. How do I show the reply form on each page of topic for the reply feature? =
* Open `topic.php` file of your theme.
* Search for `post_form();` (Tip: Press `Ctrl+F` and then search)
* Replace it with `post_form( array( 'last_page_only' => false ) );`
* The resulting line should look like this - `<?php post_form( array( 'last_page_only' => false ) ); ?>`
* Save and Upload!

Also note that the reply feature might not work on all themes (depends on how much the theme's `post.php` differs from the default kakumei theme)

== Screenshots ==

1. Easy Mentions Plugin in Action
2. A Screenshot of the Settings Page

== Changelog ==

= 0.2 (xx-0x-10) =
* Better Javascript

= 0.1.1 (28-01-10) =
* Important bug fix for Reply feature

= 0.1 (28-01-10) =
* Initial Release

== Upgrade Notice ==

= 0.1.1 (28-01-10) =
Has important bug fix for Reply feature, please upgrade!

= 0.1 (28-01-10) =
Initial Release