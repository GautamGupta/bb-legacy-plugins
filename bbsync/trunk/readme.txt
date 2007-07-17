=== bbSync ===
Contributors: fel64
Donate link: 
Tags: integration, wordpress
Requires at least: 0.8.2.1
Tested up to: latest
Stable tag: 1

This WORDPRESS PLUGIN makes new bb topics for wp posts, and integrates comments too.

== Description ==

When you make a new blog post, this post will be turned into a new topic in your forums. Replies can be made to the topic, and using `<?php felbbreplies(); ?>` will show all replies. Comments made through the wp comments form will also be shown as replies to the corresponding thread.

You can change the template used for replies in `<?php felbbreplies(); ?>` by putting bbreply.php in your theme folder and editing that. Some default, useful styling is in bbsyncstyles.css - just add it to your theme's css file.

This plugin will disable bbPress Post. It can clean up bbPress Post, use the data itself and get rid of the old tables. This plugin does *not* add tables.

There are a variety of options, like links back and so on. It's pretty rad! 

== Installation ==

1. Upload `bbsync.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php felbbreplies(); ?>` in your template to replace the comments.

== Frequently Asked Questions ==

= Help I get a fatal error when I try to post =

Please go to the plugin page and tell me exactly what the fatal error was!

Then add this to the bbPress config.php: `global $bbdb, $bb_cache, $bb_roles;`

= What about foo bar? =

Answers to the foo bar dilemma are found by askin'.

= Can it do this cool feature? =

Probably. If not yet, request it and I'll try!