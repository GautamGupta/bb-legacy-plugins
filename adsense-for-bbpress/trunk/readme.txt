=== AdSense For bbPress ===
Tags: adsense,ads,google,adwords
Contributors: Seans0n
Requires at least: 0.9.0.4
Tested up to: 0.9.0.4
Stable Tag: trunk,

Displays your AdSense ads dynamically as post X in each topic instead of just a flat ad in your theme file.

== Description ==

AdSense for bbPress allows you to input up to 10 different AdSense ads and have them displayed as posts in your forums. You can chose to display different ads at different posts (i.e. Leaderboard ad at post 1, half banner at ad 3, etc) or as a random post within any topic. You can even include an avatar and name so your ads look more like genuine forum posts.

== Installation ==

1. Unpack the zip to your `/my-plugins/` directory.
2. Copy and paste this: `<?php bbad_ad_block(); ?>` as the first line of post.php (before `<div class="threadauthor">`) in whichever theme you are using. I.e. yoursite.com/forums/bb-templates/kakumei/post.php.
3. Activate AdSense for bbPress from the Plugins tab of the bbPress admin page.
4. A new bbAdSense Configuration menu will appear in the Plugins tab. Browse to it and insert your AdSense code and chose a post number.

That's it! Browse your forum to see AdSense for bbPress in action.

== Screenshots ==

1. A standard AdSense image half-banner as a post.