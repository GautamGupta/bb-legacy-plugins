=== Show Avatar ===
Contributors: peterwsterling
Tags: avatar, gravatar, gravatar2
Requires at least: 0.8.3
Tested up to: 0.8.3
Stable tag: trunk

A plug-in enables a bbPress forum to use avatars from a WordPress installation.

== Description ==

A plug-in enables a bbPress forum to use global (and local avatars cached by the Gravatars2 WordPress plug-in) from
a WordPress installation.

== Installation ==

* Just put the plug-in into your Ômy-pluginsÕ directory and activate it.
* There are a number of options available Site Management -> Avatar Options menu.  Define these, example are given.
* Then include the following (or similar) code where you want the avatar to show.
	<?php
		if(function_exists('pws_get_avatar')) pws_get_avatar();
	?>
