=== Simple Onlinelist ===
Tags: onlinelist, online, activity
Contributors: thomasklaiber
Requires at least: 0.74
Tested up to: 0.80-alpha
Stable Tag: 1.4

Shows the current online users.

== Description ==

This plugin displays all users which were online over the past 5 minutes. It can also display when a user was last online on his profile page.

== Installation ==

Add `onlinelist.php` to your `/my-plugins/` directory.

Add `<?php show_online_users(); ?>` wherever you want in your template. I suggest adding it to `my-templates/front-page.php` to the hottags like this:

`<div id="hottags">
<h2><?php _e('Hot Tags'); ?></h2>
<p class="frontpageheatmap"><?php tag_heat_map(); ?></p>
<h2><?php _e('Online'); ?></h2>
<p><?php show_online_users(); ?></p>
</div>`

Add `<?php profile_last_online(); ?>` to `my-templates/profile.php`. I suggest adding it under line 14 like this:
`<?php bb_profile_data(); ?>
<?php profile_last_online(); ?>`.

== Configuration ==

not needed.

== Frequently Asked Questions ==

= I'm using bbpress 0.73? =

Go to line 48 and change `add_action('bb_init', 'online_update');` to `add_action('init', 'online_update');`. Then it should work.

= I'm getting MySQL errors? =

You'll maybe get an error if you browse your forum the first time after you've installed the plugin. This is normal.

If you keep getting errors, try setting `$mysql41` to `false` maybe it works then, otherwise don't hestitate to contact me.

= Where is the online-count for my statistics? =

This feature has been removed at the moment, sorry.