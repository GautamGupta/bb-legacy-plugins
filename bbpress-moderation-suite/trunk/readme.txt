=== bbPress Moderation Suite ===
Contributors: Nightgunner5
Tags: moderation, banning, ban, report post, report, warn user, warning, moderation log, log, logging, move, merge, split
Requires at least: 1.0
Tested up to: trunk
Stable tag: 0.1-rc2

A set of tools to help moderate your forums.

== Description ==

bbPress Moderation Suite is a set of tools to help moderate your forums. There are multiple parts, each able to function separately from the others. You can activate or deactivate each part separately.  It even includes an uninstaller so if you don't want to use a part anymore, you can remove all of its database usage!

*Remember to tell me (either on my blog or on this plugin page) if you find anything that isn't working at "100% awesome".*

**IMPORTANT: Move! is an alpha (probably very buggy) plugin. Please keep this in mind before activating it on a live forum.**

= Report =

Report allows users on your forum to report posts to the moderators on your forum. Believe me, if your moderators go on your forums regularly, they'll see the reports very quickly.

= Ban Plus =

Ban Plus allows temporary bans with notes on why the ban occurred. There are safety features to prevent Moderators from banning Keymasters.

[The "you've been banned" page can be customized.](http://bbpress.org/plugins/topic/bbpress-moderation-suite/other_notes/)

= Warning =

Allows moderators to warn users about rule breaking.

= Mod Log =

Keeps track of important moderator actions.

It logs the following actions:

* Plugins
	* Activation
	* Deactivation
* Forums
	* Deletion
* Users
	* Bozo (on, off)
	* Deletion
	* Banning (via Ban Plus)
* Posts
	* Status change (delete, spam, undelete, unspam)
	* Editing (by other users)
	* Moving
* Topics
	* Splitting
	* Merging

[There is a plugin API to allow more logged actions to be added.](http://bbpress.org/plugins/topic/bbpress-moderation-suite/other_notes/)

= Move! =

Move! allows moderators to move, split, and merge topics and posts.

== Installation ==

1. If you do not have a `my-plugins` folder, make one now in the same place as the folder `bb-admin`.
1. Upload the entire `bbpress-moderation-suite` folder into your `my-plugins` folder.
1. Activate the plugin through the 'Plugins' menu in bbPress
1. Activate the parts of bbPress Moderation Suite that you would like to use and leave the others alone.

== Frequently Asked Questions ==

= The Report plugin isn't putting a report link anywhere =

Your theme is using old functions.  Find `<?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?>` or the like in your current theme's `post.php` file and change it to `<?php bb_post_admin(); ?>`.  If there's an if statement before it, delete that too.

= What is a CIDR range? =

Look it up on Wikipedia. It's simple to use, but too complicated to explain here.

= How do I solve issue X? =

If you can't figure out an issue with the bbPress Moderation Suite by yourself, post a comment on this page.

== Screenshots ==

1. The moderation helper (de)activation system.

== Ban Plus API ==
If a `ban-plus.php` file is found in the template directory, it will be loaded with a global variable `$ban` that includes:

* `type` - So far this is always 'temp'
* `length` - The duration of the ban in seconds
* `on` - The unix timestamp of the ban starting
* `until` - The unix timestamp of the ban ending
* `banned_by` - The user ID of the person who started the ban
* `notes` - Any notes left by the person who started the ban

Here's an example of what a `ban-plus.php` template might look like:

`<?php bb_get_header(); ?>

<div class="notice error" id="message"><p>You have been blocked from the forum until 
<?php echo bb_datetime_format_i18n( $ban['until'] ); ?>, starting on <?php echo bb_datetime_format_i18n( $ban['on'] ); ?>. This ban will last <?php echo bb_since( time() - $ban['length'], true ); ?> in total. Your ban will end in <?php echo bb_since( time() * 2 - $ban['until'], true ); ?>. The reason for the ban was:</p><?php echo $ban['notes']; ?></div>

<?php bb_get_footer(); ?>`

For IP bans, `ban-plus-ip.php` will be used if possible. The only difference is an additional global variable, `$ban_ip` which is the IP address or CIDR range that was banned.

== Mod Log API ==
To log other actions - for example, those caused by an unrelated plugin - use `bbmodsuite_modlog_log( $content, $type )`

In order to define a new type, you will have to add a filter to `bbmodsuite_modlog_get_type_description` and return the user friendly version if the non-user friendly version is given. For example, you could return `Topic deletion` if the argument to the function is `topic_delete`, and otherwise just return the argument.

== Changelog ==
= 0.1-rc3 =
* Backslashes should no longer appear in messages.

= 0.1-rc2 =
* Moderators can now view the main Moderation page with a list of active moderation helpers.
* IP ban message order fixed.
* `bbmodsuite_banplus_unban` changed to `bbmodsuite_banplus_automated_unban` for expired bans.
* Report submission page no longer has "error" in its title.
* Default report reasons and resolve types added for new installs.
* Some minor UI fixes
* Move! moderation helper added.
* Ban Plus IP banning fixed.

= 0.1-rc1 =
* Mod Log now tracks unbanning as well as banning
* IP addresses and CIDR ranges (from /16 to /32) can now be blocked
* Backslashes will no longer appear before apostrophes (new reports/bans/warnings only, old ones will still have them)
* Plugins can ban users by using the `$override` argument in `bbmodsuite_banplus_set_ban`
* Report backend rewrite, removing report/resolve types will no longer cause errors.
* Warnings can now be sent via bbPM instead of email. If the bbPM option is chosen, the warnings will be sent by the recipient to help the moderators stay anonymous.

= 0.1-beta1 =
* [The Ban Plus "you've been banned" page can now be edited](http://bbpress.org/plugins/topic/bbpress-moderation-suite/other_notes/)
* Admin interface now matches bbPress 1.0
* Mod Log categorization fixed.
* New top level navigation menu for easy access
* Ban Plus now has hooks
* Probation discontinued - use the Bozo plugin that came with your forum instead
* Mod Log can now filter different types of messages. Unfortunately, you will need to uninstall and reinstall the Mod Log Moderation Helper before you can use this feature
* Mod Log now hides duplicate messages by default.
* Mod Log now has pagination
* The Ban Plus "unban" button now works properly
* Ban Plus now has autocomplete for the username box

= 0.1-alpha6 =
* Major code cleanup
* This plugin now works with bbPress 0.9 and 0.8. If anything gives you an error, give me the error message and I'll be sure to fix it.
* Fixed an error in the Report admin panel
* Mod Log now logs topic deletion

= 0.1-alpha5 =
* Report "obtrusive mode" made optional (See [this post](http://bbpress.org/plugins/topic/bbpress-moderation-suite/#post-2845))
* Moderation Log added

= 0.1-alpha4 =
* Warning **actually works** now
* Options are now cached, so each individual mod helper will not ask the database for its options multiple times per pageload

= 0.1-alpha3 =
* Warning added. ***Early version, lots of bugs***
* Report only uses one bb_options entry. The downside: You need to de- and re-activate this Moderation Helper, and it **will** forget your settings
* A few "idiot checks" have been added. Don't worry if you trigger one. It doesn't *necessarily* mean you're an idiot

= 0.1-alpha2 =
* Ban Plus added
* Errors and messages look less weird
* Links to administration panels for each sub-plugin added

= 0.1-alpha1 =
* First public release

== Upgrade Notice ==
= 0.1 =
UI and minor backend fixes only. See Changelog for details.