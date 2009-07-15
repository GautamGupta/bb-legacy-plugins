=== Report Post ===
Tags: alert moderator, moderation, alert, report, report post, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows members to easily report a post that needs to be reviewed by a moderator.

== Description ==

This plugin adds a "Report" link to each post on a topic page  
so members can easily alert a moderator to review a questionable post.
Email is currently used to notify the administrator but more options will come eventually.

== Installation ==

1. install, activate 
2. put  `<?php report_post_link(); ?>` in your `post.php` template where you want the link to be seen - 
recommended to the right of `<?php post_edit_link(); ?>`  in 0.9 or `<?php bb_post_admin(); ?>` in 1.0
3. optionally edit your style.css stylesheet to make it stand out, add: `a.report_post {color:red;}`  
4. note that you will NOT see a report link on YOUR OWN posts - to test, make a post as another member.

== Frequently Asked Questions ==

= I'm not getting the emails!? =

* check if your server has proper reverse dns (rDNS) and a SPF record 
* aol and hotmail accounts block the most servers and are troublesome
* gmail (google) email accounts tend to accept almost any (bad) configuration - recommended!

== To Do ==

1. don't let them report more than once on a post - or more than too many times per minute/hour
2. auto-delete post if more than x reports from different members
3. auto-post report into a specified moderator's forum #
4. maybe ajax xmlhttp call instead of real form post so there's no page movement
5. it's technically possible to alert a browing mod with a popup directing to the reported post, no email needed
6. don't allow reports on moderators
7. don't allow reports from members less than x days old

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.10 (2007-07-31) =

* first public release

= Version 0.1.3 (2008-04-20) =

* fix for bbPress change from admin_email to from_email
