=== Use Display Name ===
Tags: wordpress, integration, display_name, username, user_login
Contributors: mdawaffe

This plugin uses a moderator's display name (as set in WordPress) as the name of
the moderator rather than the moderator's login name.  This means that moderators
on your forum will have the same name displayed in bbPress as they do on your
WordPress blog.

The plugin can be easily modified to work for *all* users, not just moderators.

== Installation ==

Add the `display-name.php` file to bbPress' `my-plugins/` directory.

== Configuration ==

No configuration is necessary if you only want to use display name for moderators.
If you want to use display names for *all* users, delete all of the lines in
`display-names.php` that end with `// mod`.

== Frequently Asked Questions ==

= Does this *completely* replace the username? =
For all display purposes, yes.  Users must still log in with their user logins.
