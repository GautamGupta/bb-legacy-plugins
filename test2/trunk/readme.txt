=== Use Display Name ===
Tags: wordpress, integration, display_name, username, user_login
Contributors: mdawaffe
Requires at least: 0.72
Tested up to: 0.73
Stable Tag: trunk

Use a moderator's display name as the name of the moderator rather than the moderator's login name.

== Description ==

This means that moderators on your forum will have the same name displayed in bbPress as they do on your WordPress blog.

The plugin can be easily modified to work for *all* users, not just moderators.

== Installation ==

Add the `display-name.php` file to bbPress' `my-plugins/` directory.

== Configuration ==

No configuration is necessary if you only want to use display name for moderators.
If you want to use display names for *all* users, delete all of the lines in
`display-names.php` that end with `// mod`.

== Frequently Asked Questions ==

= Where do I set a user's display name? =
In Wordpress' Users admin panel.

= Does this *completely* replace the username? =
For all display purposes, yes.  Users must still log in with their user logins.
