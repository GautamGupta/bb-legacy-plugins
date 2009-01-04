=== bbPress Moderation Suite ===
Contributors: Nightgunner5
Tags: moderation
Requires at least: 0.9
Tested up to: trunk
Stable tag: 0.1-alpha1

A set of tools to help moderate your forums.

== Description ==

bbPress Moderation Suite is a set of tools to help moderate your forums.  There are multiple parts, each able to function separately from the others.  You can activate or deactivate each part separately.  It even includes an uninstaller so if you don't want to use a part anymore, you can remove all of its database usage!

= Report =

Report allows users on your forum to report posts to the moderators on your forum. Believe me, if your moderators go on your forums regularly, they'll see the reports very quickly.

= Ban Plus =

Being programmed.

= Mod Log =

Being programmed.

= Warning =

Being programmed.

= Probation =

Being programmed.

== Installation ==

1. If you do not have a `my-plugins` folder, make one now in the same place as the folder `bb-admin`.
1. Upload the entire `bbpress-moderation-suite` folder into your `my-plugins` folder.
1. Activate the plugin through the 'Plugins' menu in bbPress
1. Activate the parts of bbPress Moderation Suite that you would like to use and leave the others alone.

== Frequently Asked Questions ==

= The Report plugin isn't putting a report link anywhere =

Your theme is using old functions.  Find `<?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?>` or the like in your current theme's `post.php` file and change it to `<?php bb_post_admin(); ?>`.  If there's an if statement before it, delete that too.

== Screenshots ==

1. Can you spot what happened because there was a report?

== Changelog ==

* 0.1-alpha1
	* First public release