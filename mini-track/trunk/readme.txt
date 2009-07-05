=== Mini Track  ===
Tags: statistics, track, tracking, online, onlinelist, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

A simple way to count and track both members and non-members as they move around your forum.

== Description ==

A simple way to count and track both members and non-members as they move around your forum.
Now automatically shows online status in posts and last online time in profiles and can detect most bots.

This plugin now inserts itself into the footer automatically, no template edits required unless you want custom placement.
You can see the real-time tracking at  `your-forum-url.com/?mini_track_display`

If you upgrade from an old version you need to use the RESET link in the tracking display.

== Installation ==

* Install `mini-track.php` to  `my-plugins/` and activate

* Edit options (true / false) at the top of the plugin until an admin menu is made

*  This plugin now inserts itself into the footer automatically, no template edits required unless you want custom placement.

* ONLY if you want CUSTOM placement, edit your `footer.php` template (or other template) to add  the info like so:
`
	<div id="footer">
		<?php mini_track(1);  // who's online now ?>
		<?php mini_track_today(1);  // who's been online today ?>
		<?php mini_track_statistics(2);  // general statistics ?>
		<p><?php printf(__('%1$s is proudly powered by <a href="%2$s">bbPress</a>.'), bb_option('name'), "http://bbpress.org") ?></p>
	</div>

`
In the above example the (1) indicates don't show extended info, ie. usernames.  (2) would also show usernames.

* With non-automatic placement, if you also want a list of the member names, use `<?php mini_track(2); ?>`
or if you only want it on the front page you can do it like this: `<?php if (is_front() ) {mini_track(1);} ?>` or you can put it anywhere in `front-page.php` that you'd like.

* Mini-Track can now show some basic statistics in your footer, or with manual placement anywhere on the page via `<?php mini_track_statistics(); ?>`

* it is highly recommend you put this line in your `bb-config.php`  which will help with database performance on active forums by loading all  bbPress options at once instead of piecemeal
`
$bb->load_options = true;
`

== Frequently Asked Questions ==

= How do I enable GeoIP (country codes) and flags? =

* While the mysql lookup for geoip is much faster, it is not available yet to the public, instead you can use the IP2C method which although slower, is much easier to install

* create a new sub-directory in the same folder as mini-track.php called  "ip2c"  ie.  `/my-plugins/mini-track/ip2c/`

* download these two files and put them in there 
http://firestats.cc/browser/trunk/ip2c/ip-to-country.bin?format=raw
http://firestats.cc/browser/trunk/ip2c/php/ip2c.php?format=raw

* find the option  `$mini_track_options['geoip'] = ` near the top of `mini-track.php` and set it to "ip2c" ie.
`$mini_track_options['geoip'] = "ip2c";`

* if you also want country flags shown instead of country codes, you need to install the 80 or so country flags in png format
one free source of the flags is the famfamfam site:
http://www.famfamfam.com/lab/icons/flags/famfamfam_flag_icons.zip
you need to extract the png folder in the zip to your own image sub-directory,  ie. `/images/flags/`

* find the option  `$mini_track_options['flags'] = ` near the top of `mini-track.php` and set it to the path of your flags images ie.
`$mini_track_options['flags'] = "/images/flags/";`

= There are duplicates of users or dates are wrong ? =

* If you upgrade from an old version you need to use the RESET link in the mini-track display panel.

* If you see repeats of the same IP's in the real-time display, that means the user is going through a proxy. Some bots like yahoo's slurp will connect to your site many times in a row from the same external IP, but a different computer in their network: Mini-Track can detect and follow this.

= Does this slow down my website? =

* I highly recommend you put this line in your `bb-config.php`
`
$bb->load_options = true;
`
That will make all options load at once when bbPress starts.

* It is NOT recommended to use this on forums with more than a few dozen visitors at a time (because of the complex string serialization/deserialization)

= I know there are more people online than Mini Track is showing ? =

* Note the (old) method this plugin uses, indexing by plain IP only, has some limitations. If you get more than one person from one IP, only one person will be tracked. If you run bbPress on an intranet or multiple people often visit from the same proxy like a school, this makes it useless.

If it's a problem for anyone, you could take the md5 of the IP plus the User Agent plus any proxy headers and use that as an index instead. I didn't do that by default because it would be considerably slower and doubles the size of the key (and serialized string).

The md5 technique is now used by default. You can disable it if you want and use the old IP method at the expense of missing multiple NAT/proxy visitors.

= It's missing this or that feature ... =

* The core of this plugin was written in 15 minutes to demonstrate how easy it is to write plugins for bbPress.
(It is meant as a temporary substitute until the full "User Track" plugin is released much later this year.)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-08-05) =

* first public alpha release

= Version 0.0.5 (2008-08-06) =

* bunch of new features including online status in posts, last online time in profile, bot tracking

= Version 0.0.9 (2008-08-09) =

* bug fixes for last online, IP tracked/displayed + IP lookup, bots recorded/displayed by agent

= Version 0.1.0 (2008-08-11) =

* more bots detected, including spoofing/steath bots that use regular user agents

* remote IP lookup at RIPE (click IP in tracking display)

* referer from remote site tracked upon entry

* statistics of topics/posts and members

= Version 0.1.5 (2008-08-15) =

* major additions including geoip via ip2c (or mysql but dataset not available to public)

* admin functions moved to external php for inclusion only by admin user

* more bot tracking

* tracking display can be sorted

* temporary ban ability (with automatic/manual action)

= Version 0.1.6 (2008-08-16) =

* bot verification via rdns to allow excessive page counts

* can replace the view count function of bb-topic-views without using sessions 

== To Do ==

* total online today

* online today by name

* store time online per member in profile ++(first seen - last seen)

* internationalization 

* convert to real db table instead of meta
