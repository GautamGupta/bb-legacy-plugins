=== Plugin Name ===
Contributors: Eric Barnes
Donate link: http://68kb.com/
Tags: gravatar, avatar
Requires at least: 0.8.3
Tested up to: 0.8.3
Stable tag: 1

This plugin allows you to integrate gravatar's into the post template.

== Description ==

This plugin allows you to integrate gravatar's into the post template.

== Installation ==

1. Upload `68-gravatar.php` to the `/my-plugins/` directory
1. Activate the plugin through the 'Site Management' menu in bbPress
1. Place `<img src="<?php sixtyeight_gravatar(); ?>" />` in your post template.

This plugin has the following syntax: 

`<?php sixtyeight_gravatar({rating{, size{, default{, border}}}}) ?>`

All of the parameters are optional. For example, the following will create a gravatar URL that allows all rating levels, is 80×80 pixels, uses no default image, and has no border:

<?php sixtyeight_gravatar(); ?>

If you wish to restrict your gravatars to R rated and below, you'd do this:

`<?php sixtyeight_gravatar("R"); ?>`

If you want the size of the image changed as well, supply the pixel dimension as the second argument (defaults to 80):

`<?php sixtyeight_gravatar("R", 40); ?>`

If you want to use your own "Heat Vision and Jack" image as a default graphic (shows up when either no gravatar exists for a given user, or the given user's gravatar exceeds the specified rating), you'd do this:

`<?php sixtyeight_gravatar("R", 40, "http://www.somewhere.com/heatvision.jpg"); ?>`

You can also add a 1px border of any color you choose with the fourth parameter:

`<?php sixtyeight_gravatar("R", 40, "http://www.somewhere.com/heatvision.jpg", "FF0000"); ?>`

If you wish to leave a parameter at its default while supplying other parameters, simply pass an empty string as the argument.

Remember that this only generates the URL, so you have to place the gravatar tag inside the src attribute of an img tag like so:

`<img src="<?php sixtyeight_gravatar() ?>" alt="" />`


== Frequently Asked Questions ==
