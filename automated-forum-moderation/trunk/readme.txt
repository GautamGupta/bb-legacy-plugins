=== Automated Forum Moderation ===
Contributors: Nightgunner5
Tags: spam, posting, words, lol
Requires at least: 0.8
Tested up to: trunk
Stable tag: 0.3

Blocks common (and sometimes accidental) human-generated spam automatically.

== Description ==

Blocks common (and sometimes accidental) human-generated spam automatically and displays a helpful message describing what was wrong.

== Installation ==

1. If you wish to change the default values, edit the `automated-forum-moderation.php` file.
2. Upload the `automated-forum-moderation` folder to the `/my-plugins/` directory at the root of your forums.  If you do not already have one, make one.
3. Activate the plugin through the `Plugins` menu in bbPress admin.

== Screenshots ==

1. A post is blocked because it is too short

== Changelog ==

= 0.3.1 =
* More user-friendly error messages

= 0.3 =
* Old topics can now be re-opened by moderators.

= 0.2 =
* Discovered (and fixed) critical error that caused deletion of topics that shouldn't have been (They can be found in the deleted topics section of the admin panel)
* Automated Forum Moderation now completely deletes blank topics it generates instead of just marking them as deleted

= 0.1.1 =
* Auto-remove empty topics that were generated when this plugin blocked a post (due to length)

= 0.1 =
* Initial release