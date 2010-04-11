=== Ajax Report Post ===
Tags: alert moderator, moderation, alert, report, report post, bbpress report post, ajax, ajax report post, jomontvm
Contributors: jomontvm
Tested up to: 1.0.2
Stable tag: trunk

Allows members to report a post that needs to be reviewed by the moderator.

== Description ==

This plugin allows members to report on a questionable post. 
Once installed, it adds a "Report" link atuomatically with each post on the topic page.

== Installation ==

1. Unzip and copy "ajax-report-post" folder to bbpress/my-plugins
2. Activate the plugin through the 'Plugins' menu
3. A 'Reports' menu will be automatically generated 
3. Add configuration settings in the Reports menu
4. A 'Report' link will be automatically added with each post on the topic page


== Screenshots ==

1. Report post configuration settings


== License ==

* GPLv3

== Other Notes ==


Notes:

- Only logged in users can report on a post.
- A user has no provision to report on his/her own post.
- A user is allowed to report on the same post only once in a day.

Workflow:

- User clicks on the “Report” link below a post.
- Submit report
- Data and user ip gets stored into database
- Admin gets notified by email with link to the post
- Reported posts are listed in the admin panel with reporter's comment
- Admin can manage reports from backend.
