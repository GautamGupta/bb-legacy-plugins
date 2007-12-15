=== bbSync ===
Contributors: fel64
Donate link: 
Tags: integration, wordpress
Requires at least: 0.8.3
Tested up to: latest
Stable tag: 1

This WORDPRESS PLUGIN makes new bb topics for wp posts, and integrates comments too.

== Description ==

When you make a new blog post, this post will be turned into a new topic in your forums. Replies can be made to the topic. Comments made through the wp comments form will also be shown as replies to the corresponding thread. All comments/replies will show up automagically in wordpress, too.

This plugin will disable bbPress Post. It can clean up bbPress Post, use the data itself and get rid of the old tables. This plugin does *not* add tables.

There are a variety of options, like links back and so on. It's pretty rad!

Since it does not increment wordpress' comment count, you can add something like this to your template:
`
<?php if($topic_id = felwptobb( $id ) ) { echo '<a href="' . bbreplylink( $topic_id ) . '">Reply!</a>'; } else { comments_popup_link('&nbsp;comments', '1 comment', '% comments','',''); } ?>
`

== Installation ==

1. Upload `bbsync.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Help I get a fatal error when I try to post =

Please go to the plugin page and tell me exactly what the fatal error was! That is: an exact report of the error message and what you tried to do.

= What about foo bar? =

Answers to the foo bar dilemma are found by asking.

= Can it do this cool feature? =

Maybe. If not and it would fit in with the function's purpose, I'll try!