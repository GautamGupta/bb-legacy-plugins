=== Mini Stats  ===
Tags: statistics, members, posts, topics, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Shows some simple forum statistics at the bottom of your pages and links to a full summary display.

== Description ==

Shows some simple forum statistics at the bottom of your pages and links to a full summary display.

I am now separating some functionality from Mini Track into Mini Stats to reduce complexity and make features optional.

Automatically inserts into the footer, no template edits required unless you want custom placement.
You can see the full display at  `your-forum-url.com/?mini-stats` or click on the pie graph icon in the footer.

Example:  
http://bbshowcase.org/forums/?mini-stats

== Installation ==

* Install the complete `mini-stats/`  directory into  `my-plugins/` and activate.

* Change defaults if desired by editing options at the top of the plugin until an admin menu is made.

*  This plugin inserts itself into the footer automatically, no template edits required unless you want custom placement.

* ONLY if you want CUSTOM placement, edit your `footer.php` template (or other template) to add  the info like so:
`
	<div id="footer">
		<?php mini_stats(1);  // general statistics ?>
		<p><?php printf(__('%1$s is proudly powered by <a href="%2$s">bbPress</a>.'), bb_option('name'), "http://bbpress.org") ?></p>
	</div>

`
In the above example the (1) indicates don't show extended info, ie. usernames.  (2) would also show usernames.

* it is highly recommend you put this line in your `bb-config.php`  which will help with database performance on active forums by loading all  bbPress options at once instead of piecemeal
`
$bb->load_options = true;
`

== Frequently Asked Questions ==

= I don't want members to see this, only admin, how can I do that? =

* change the settings at the top of the plugin:

`
$mini_stats['statistics_in_footer']=false;
$mini_stats['level']="administrate"; 
`	

* Then you can see the stats by manually going to your-forum-name.com/?mini-stats or under the admin menu,  Manage -> Mini Stats

= Why doesn't the newly registered names update after I delete a member ? =

* bbPress doesn't have a hook right now for that, instead try adding or deleting a post instead and it will re-sync 

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-11-13) =

* first public release

= Version 0.0.2 (2008-11-14) =

* now available via admin menu, Manage ->  Mini Stats   (looks better in 0.9 than 1.0)

= Version 0.0.3 (2008-11-21) =

* CSV export support, move to your desired start date and it will export all data to current date

* some date range and control cleanup

= Version 0.0.4 (2008-11-23) =

* bug fixes for gmt offset logic and zero fill for empty dates, CSV fixes, topics per day were using end time instead of start time

== To Do ==

* fix buggy width on graphs when dates are too wide
