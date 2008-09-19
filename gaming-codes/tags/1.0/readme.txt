=== Gaming Codes ===
Tags: gaming, gaming codes, gamers, gamer tag, friend code, consoles, ggpo, emulation, Detective
Contributors: Detective
Requires at least: 0.9.0.2
Tested up to: 0.9.0.2
Stable Tag: 1.0

A plugin that allows your users to have Gamer/Friend codes of gaming consoles in your bbPress profiles.

== Description ==

Allows users to enter Gaming Codes into their profiles. Currently the following codes are supported:

* PlayStation Network.
* XBox Live Gamer Tag.
* Nintendo Wii Friend Code (one field, you can input all the codes you want there).
* Nintendo DS Friend Code (the same as above).
* GGPO.net username.
* 2D Fighter username.

== Installation ==

1. Upload the folder `gaming-codes-bbpress` into your `/my-plugins/` directory.
1. Modify your `profile-edit.php` template to include the Gaming Codes form: `<?php if (function_exists('gaming_codes_form')) gaming_codes_form($user->ID); ?>` (put this inside the <form> tags).
1. Modify your `profile.php` template to include the Gaming Codes for the user:  `<?php if (function_exists('the_user_profile_gaming_codes')) the_user_profile_gaming_codes(); ?>`.
1. You're done :)

== Configuration ==

None necessary.