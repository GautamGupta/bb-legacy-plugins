=== zaerl Post Permalink ===
Contributors: zaerl 
Tags: post, permalink, post permalink, post link
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 0.2

Permalinks to posts.

== Description ==

With zeal Post Permalink you can specify direct links to posts of the type domain.com/post/number or domain.com/?post=number. The plugin expose one template functions:

`za_post_permalink($id = 0)`

The accepts a post ID and return the unique post permalink if the post exists, `false` otherwise. If `0` or nothing is passed than the function takes the current post (if any) and generate the permalink for that particular post.

== Installation ==

Extract the zip file and just drop the contents in the my-plugins/ directory of your bbPress installation and then activate the Plugin from Plugins page.

== Changelog ==

= 0.2 =
* Now the permalinks generate a 301 redirect
* Italian localization

= 0.1 =
* Initial release