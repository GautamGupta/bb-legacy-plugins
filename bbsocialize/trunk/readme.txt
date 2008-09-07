=== bbSocialize ===
Tags: twitter, flickr, pownce, facebook, stumbleupon, digg, social media website, delicious
Contributors: F.Thion
Tested up to: 1.0-dev
Donate link: http://astateofmind.eu/about/support/

Allows you to set and display your social media websites link in your profile.

== Description ==

Allows you to set and display your social media profiles in your public forum profile. As administrator, you can select which sites will be supported using simple management panel.

== Installation ==

1. Unpackage all files.
2. Move `bbsocialize` directory o to your `/my-plugins/` directory.
3. Go to your administration panel and activate plugin.
6. Go to tab `Social Profiles` tab under `Plugins` tab and configure settings.
7. Open your `profile.php` file in your theme directory and add `<?php get_socialize(); ?>` where you want your profiles to be displayed.

== Use ==

- Open `profile.php` located in your theme directory and put somewhere `get_socialize();` within PHP tags - now profiles will be displayed;
- You can display profiles within single post, just open `post.php` and put `get_socialize_post( get_post_author_id() );` within PHP tags;

== To Do ==

- Add support for more websites;
- Optimize script to make less db queries;

== Donate ==

Give me coffee! Pease ;)

* http://astateofmind.eu/about/support/

== History ==

0.0.3 - (2008-08-12) 
- Added support for Technorati; 
- Minor fixes to input forms; 
- Minor fixes to management panel; 
- New images for social media websites,
- New function to display links within posts;
- New "reset" button to reset settings;
- Now, empty profiles are not displayed;

0.0.2 - (2008-08-11) First beta release, most popular websites supported;
