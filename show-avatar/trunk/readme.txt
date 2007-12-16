=== Show Avatar ===
Contributors: peterwsterling
Tags: avatar, gravatar, avatars, gravatars, gravatar2, wordpress, integration
Requires at least: 0.8.3
Tested up to: 0.8.3
Stable tag: 2.0

A plug-in that enables a bbPress forum to use avatars from a WordPress installation.

== Description ==

A plug-in that enables a bbPress forum to use global (and local avatars cached by the Gravatars2 WordPress plug-in) from
a WordPress installation.

== Installation ==

* Just put the plug-in into your Ômy-pluginsÕ directory and activate it.
* There are a number of options available Site Management -> Avatar Options menu.  Define these, example are given.
* Then include the following (or similar) code where you want the avatar to show.
	<?php
		if(function_exists('pws_get_avatar')) pws_get_avatar();
	?>
