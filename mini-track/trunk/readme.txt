=== Mini Track  ===
Tags: statistics, tracking, online,_ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

A simple way to count and track both members and non-members as they move around your forum.

== Description ==

A simple way to count and track both members and non-members as they move around your forum.
To use this, put   `<?php mini_track(1); ?>`  in your template where you want the output to display. 
If you only want it on the front page you can do it like this: `<?php if (is_front() ) {mini_track(1);} ?>`
If you also want a list of the member names, use `<?php mini_track(2); ?>`
You can see a list of users and locations by going to  `your-forum-url.com/?mini_track_display`

This plugin was written in 15 minutes and demonstrates how easy it is to write plugins for bbPress.
It is meant as a temporary substitute until the full "User Track" plugin is released much later this year.

== Installation ==

* Install `mini-track.php` to  `my-plugins/` and activate

* Edit your `footer.php` template to add  the info like so:
`
	<div id="footer">
		<?php mini_track(2); ?>
		<p><?php printf(__('%1$s is proudly powered by <a href="%2$s">bbPress</a>.'), bb_option('name'), "http://bbpress.org") ?></p>
	</div>

`
== Frequently Asked Questions ==

= Does this slow down my website? =

* I highly recommend you put this line in your `bb-config.php`
`$bb->load_options = true;`
That will make all options load at once when bbPress starts.

* It is NOT recommended to use this on forums with more than a few dozen visitors at a time

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-08-05) =

* first public alpha release

== To Do ==

* internationalization 
