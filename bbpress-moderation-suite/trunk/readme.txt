=== bbPress Moderation Suite ===
Contributors: Nightgunner5
Tags: moderation, banning, ban, report post, report, warn user, warning, moderation log, log, logging
Requires at least: 0.8
Tested up to: trunk
Stable tag: 0.1-alpha6

A set of tools to help moderate your forums.

== Description ==

bbPress Moderation Suite is a set of tools to help moderate your forums. There are multiple parts, each able to function separately from the others. You can activate or deactivate each part separately.  It even includes an uninstaller so if you don't want to use a part anymore, you can remove all of its database usage!

*Remember to tell me (either on my blog or on this plugin page) if you find anything that isn't working at "100% awesome".*

= Report =

Report allows users on your forum to report posts to the moderators on your forum. Believe me, if your moderators go on your forums regularly, they'll see the reports very quickly.

= Ban Plus =

Ban Plus allows temporary bans with notes on why the ban occurred. There are safety features to prevent Moderators from banning Keymasters.

= Warning =

Allows moderators to warn users about rule breaking.

= Mod Log =

Keeps track of important moderator actions.

So far, it logs the following actions:

* Plugins
	* Activation
	* Deactivation
* Forums
	* Deletion
* Users
	* Bozo (on, off)
	* Deletion
* Posts
	* Status change (delete, spam, undelete, unspam)
	* Editing (by other users)

== Installation ==

1. If you do not have a `my-plugins` folder, make one now in the same place as the folder `bb-admin`.
1. Upload the entire `bbpress-moderation-suite` folder into your `my-plugins` folder.
1. Activate the plugin through the 'Plugins' menu in bbPress
1. Activate the parts of bbPress Moderation Suite that you would like to use and leave the others alone.

== Frequently Asked Questions ==

= The Report plugin isn't putting a report link anywhere =

Your theme is using old functions.  Find `<?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?>` or the like in your current theme's `post.php` file and change it to `<?php bb_post_admin(); ?>`.  If there's an if statement before it, delete that too.

= How do I solve issue X? =

If you can't figure out an issue with the bbPress Moderation Suite by yourself, post a comment on this page or my blog.

== Screenshots ==

1. Can you spot what happened because there was a report? Don't worry, there's a setting to make this a bit less obtrusive.
2. The Warning administration screen

== Changelog ==

* 0.1-beta1
	* Ban Plus now has hooks.
	* Probation discontinued - use the Bozo plugin that came with your forum instead.
* 0.1-alpha6
	* Major code cleanup
	* This plugin now works with bbPress 0.9 and 0.8. If anything gives you an error, give me the error message and I'll be sure to fix it.
	* Fixed an error in the Report admin panel
	* Mod Log now logs topic deletion
* 0.1-alpha5
	* Report "obtrusive mode" made optional. (See [this post](http://bbpress.org/plugins/topic/bbpress-moderation-suite/#post-2845))
	* Moderation Log added.
* 0.1-alpha4
	* Warning **actually works** now.
	* Options are now cached, so each individual mod helper will not ask the database for its options multiple times per pageload.
* 0.1-alpha3
	* Warning added. ***Early version, lots of bugs***
	* Report only uses one bb_options entry. The downside: You need to de- and re-activate this Moderation Helper, and it **will** forget your settings.
	* A few "idiot checks" have been added. Don't worry if you trigger one. It doesn't *necessarily* mean you're an idiot.
* 0.1-alpha2
	* Ban Plus added.
	* Errors and messages look less weird.
	* Links to administration panels for each sub-plugin added.
* 0.1-alpha1
	* First public release