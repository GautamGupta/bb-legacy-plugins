=== Avatar ===
Contributors: Josh Hutchins
Requires at least: .73 ?
Tested up to: .74
Stable tag: .73a

Allows users to specify an external address to an avatar that will be shown when they post

== Description ==

Plugin Name: Avatar
Plugin URI: http://faq.rayd.org/bbpress_avatar/
Description: Allows users to specify an external address to an avatar that will be shown when they post
Change Log: .73a - Adjusts implementation so that core files no longer have to be changed.
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .73a

== Installation ==

1. Put the file bb-avatar.php into your my-plugins directory (in the bbpress root directory)
2. Add "post_avatar();"  (between the php tags) in post.php or wherever you want it if using your own template.

NOTE: If using the default template, you MUST change style.css so that avatars can fit in the author info area.

I made these changes in style.css to allow for a 150x150px avatar

.post {
	min-height: 200px;
}

.threadauthor {
	margin-left: -165px;
	overflow: hidden;
	position: absolute;
	max-height: 215px;
	width: 150px;
}


NOTE: .post did not exist previously, but the div's did exist.  .threadauthor did exist, and I made a few changes to it.
