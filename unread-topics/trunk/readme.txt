=== Unread Topics ===
Contributors: henrybb
Tags: topics, unread
Requires at least: 0.8.2
Tested up to: 0.8.3
Stable tag: 0.4

This plugin indicates new and unread posts.

== Description ==

This plugin in short, marks unread topics by putting `<strong></strong>` tags around them, in the vein of fel64's Indicate New Posts.Further, it updates the links to point to the next unread post in the topic.It works by having a new table called utplugin_log which accounts for which posts have been read by which user in what topic.While working nicely, this also means that the plugin won't scale well for huge boards, since the amount of records in that table have a potential for reaching <em>number of users</em> * <em>number of topics</em>.In a perfect world, I wouldn't have to do that. But I honestly can't think of another way, except implementing some accounting for this stuff that will use meta data to put milestones on each forum and then purge records from the log. But I don't like that solution either.So I have this here plugin, that is a complete drop-in solution for indicating new posts. Requires no modification of templates or other. Just put it in your plugin directory and activate it from the admin panel.

Currently there is no feature for marking every post as read or unread. Mainly because this is intended as a feature of indicating actual browsing of the forum rather than being a personal accountant for the user.

I must give props to fel64 for `Indicate New Posts`. This plugin isn't designed to be a replacement for that plugin, but is rather a more sophisticated and (regrettably) more resource intensive choice.

== Installation ==

1. Upload `bb-unread-topics.php` to your `my-plugins` directory.
2. Go to the admin interface and activate the plugin.

