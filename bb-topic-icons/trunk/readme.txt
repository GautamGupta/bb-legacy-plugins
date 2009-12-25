=== BBPress Topic Icons ===
Contributors: paulhawke
Tags: sticky, closed, busy, topics
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 1.0.2

== Description ==

This plugin changes the default behavior of bbPress - takes away the words "sticky" and "closed" next to topics and replaces them with small icons - in addition, busy topics and normal topics gain an icon.

== Screenshots ==

== Installation ==

The `topic-icons` directory needs to go into your `my-plugins` directory. If you dont have one, you can create it so that it lives alongside your `bb-plugins` directory. Alternatively, `topic-icons` can be dropped directly in to your `bb-plugins` directory.

Oh, and don’t forget to Activate the plugin!

== Frequently Asked Questions ==

= I still see the word "sticky" next to topics =

Firstly, have you installed and activated the plugin?  See the installation instructions for details.

Secondly, if the plugin is being reported as active, the template itself might be misbehaving, you will need to look for where the `frontpage.php` file is looking at topics - there will be a hard-coded 'sticky' text there.  Swap that text for a call to the built-in bbPress function: `<?php bb_topic_labels(); ?>` that handles topic labelling, and things should work.

= Can I use different icons =

Sure you can.  In the `topic-icons` directory, there's a `icon-sets` subdirectory.  In future versions of the plugin, different icon sets will be installable by copying them to this directory.  For now, take a look in the default icon-set - the four icon files live in that directory.  Upload your own files, and in the `topic-icons.php` file, change the filename constants and icon sizes if you need to.

== Change Log ==

= Version 0.1 =

Initial release with minimal documentation
