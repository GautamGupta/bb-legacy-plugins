=== Members Online  ===
Tags: statistics, track, tracking, activity, online, onlinelist, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 1.0 alpha 5
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Shows which members are currently online or visited today. Tracks the total time online and last visit for each member in their profile.

== Description ==

Shows which members are currently online or visited today. Tracks the total time online and last visit for each member in their profile.

This is a lightweight alternative to my overly complex and somewhat buggy Mini-Track (which I don't have the time to maintain right now).

The compromise is it makes no attempt to track guests, at all. Only members are followed. Daily statistics are not (yet) stored.

However the benefit is it's very fast, makes as few queries as possible and should be very easy to install and use. 

== Installation ==

*  Install the `members-online/`  directory to  `my-plugins/` and activate

*  This plugin inserts some results into the front-page footer automatically, no template edits required unless you want custom placement.

*  If you don't like the information in the footer, you can either turn it off entirely or place selected information wherever you'd like.

*  turn off footer entirely by editing near the top of the plugin and change to 
`
$members_online['footer']	= false;   
`
*  get a plain list of member that are online right now via   `<?php do_action('members_online_now',''); ?>`

*  get a plain list of members that visited today via   `<?php do_action('members_online_today',''); ?>`

== Frequently Asked Questions ==

= It still shows me in the footer and topics as logged in even after I  just logged out? =

*  You will disappear after the timeout  (10 minutes by default). 
This is actually to keep performance reasonable and removes an extra mysql query. However in your profile you will properly display as offline.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2009-04-03) =

* first public alpha release

