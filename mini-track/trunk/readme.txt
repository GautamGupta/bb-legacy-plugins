=== Mini Track  ===
Tags: statistics, track, tracking, online, onlinelist, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

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
If you want CUSTOM placement, edit your `footer.php` template (or other template) to add  the info like so:
`
	<div id="footer">
		<?php mini_track(1); ?>
		<p><?php printf(__('%1$s is proudly powered by <a href="%2$s">bbPress</a>.'), bb_option('name'), "http://bbpress.org") ?></p>
	</div>

`
* With non-automatic placement, if you also want a list of the member names, use `<?php mini_track(2); ?>`
or if you only want it on the front page you can do it like this: `<?php if (is_front() ) {mini_track(1);} ?>` or you can put it anywhere in `front-page.php` that you'd like.

== Frequently Asked Questions ==

= There are duplicates of users or dates are wrong ? =

* If you upgrade from an old version you need to use the RESET link in the mini-track display panel.

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

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-08-05) =

* first public alpha release

= Version 0.0.5 (2008-08-06) =

* bunch of new features including online status in posts, last online time in profile, bot tracking

== To Do ==

* internationalization 
