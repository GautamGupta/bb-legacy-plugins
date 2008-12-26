=== Subscribe To Topic  ===
Tags: email,notify,subscribe, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows members to track and/or receive email notifications (instant, daily, weekly) for new posts on topics.

== Description ==

Now members can subscribe to get new post notifications via email or simply track topics without emails.

Checks are done to make sure members are not emailed more than once per minute for the same topic and not until they have read the topic again.

If you have an active site, it is important to consider that some ISPs may decide to block 
your forum's emails  simply based on the volume of emails when you use a plugin like this.
Make sure your website has proper SPF, Sender ID and DomainKeys to help delay blocking
but if it's a very large site you'll probably have to pay a whitelisting service to stop blocks eventually.

== Installation ==

* Install, activate.

* View some topics and subscribe to them via the topic meta at the top.

* Check profile  page to see it working.  No edits required unless you want to disable features like the additional view.

* No admin menu yet, only "instant" email option available for now - "daily" and "weekly" coming later.

== Frequently Asked Questions ==

= How can I add a graphic icon to the subscribe area in the topic meta? =

* edit your css as desired, the "Subscribe To Topic" line has an id of #subscribe_to_topic

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-12-26) =

* early alpha release for testing, feedback and bug reports

== To Do ==

* bcc research to send only one email notify to many members
* daily, weekly email summaries (cron support?)
* 3rd party emailer support when php mail not available
* templating system to allow custom email layout
* admin menu
