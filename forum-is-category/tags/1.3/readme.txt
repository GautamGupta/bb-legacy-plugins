=== Forum is category ===
Contributors: Nightgunner5
Tags: forums, category, posting
Requires at least: 0.7.2
Tested up to: 0.9.0.2
Stable tag: 1.3

This plugin is only needed on bbPress lower than 1.0, which has been depricated. Please upgrade your bbPress installation to the latest bbPress version.

Turn a forum into a "category" which cannot be posted to and does not have post/topic counts.

== Description ==

The forums that you specify in the `forum-is-category.php` file are turned into no-posts-allowed categories.

If a specified forum has a parent, the parent will also be turned into a no-posts-allowed category, whether specified or not.

Do not use this with bbPress 1.0 or higher, categories are in the core on those versions.

== Installation ==

1. Upload `forum-is-category.php` to the `/my-plugins/` directory.  If you do not already have one, make one.
1. Activate the plugin through the 'Plugins' menu in bbPress admin.
1. Change the settings on the menu that appears.

== Frequently Asked Questions ==

= What will happen to topics already in the specified forums? =

Nothing.  All this plugin does is prevent new topics from being posted into "categories".  Pre-existing topics will remain open for discussion if they were open before the installation of this plugin.  A good idea is to remove all topics from forums that are "categories".