=== Report Post ===
Tags: alert moderator, moderation, alert, report, report post
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk

== Description ==

This plugin adds a "Report" link to each post on a topic page 
so members can alert a moderator to review a questionable post.
Currently just an email is sent the administrator but more features will come eventually.

== Installation ==

1. install, activate 
2. put  <? report_post_link(); ?> in your post.php template where you want the link to be seen - recommended next to <?php post_edit_link(); ?> 
3. optionally edit your style.css stylesheet to make it stand out, add:  a.report_post {color:red;}  

== To Do ==

1. don't let them report more than once on a post - or more than too many times per minute/hour
2. auto-delete post if more than x reports from different members
3. auto-post report into a specified moderator's forum #
4. maybe ajax xmlhttp call instead of real form post so there's no page movement
5. it's technically possible to alert a browing mod with a popup directing to the reported post, no email needed
6. don't allow reports on moderators
7. don't allow reports from members less than x days old

== Version History ==

Version 0.10 (2007-07-31)

* first public release
