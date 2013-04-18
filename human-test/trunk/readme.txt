=== Human Test for bbPress  ===
Tags: bots, captcha, challenge, register, registration, _ck_
Contributors: _ck_
Requires at least: 0.8
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Uses various methods to exclude bots from registering (and eventually posting) on bbPress, including math, captcha and "negative fields".

== Description ==

Automated bot registration is becoming a problem on bbPress. 
This is an easy way to stop virtually all of them.

This plugin adds a new field at the bottom of your registration page with two random numbers to add together. 
Uses a few tricks like javascript and entity encoding to slow down the smarter bots.

Now supports bb-anonymous-posting plugin with a challenge question automatically placed above posts/topics.

No template edits or core modifications required, 100% plugin based,

Eventually will add image based (captcha style) writing as well as "negative fields".

== Installation ==

* Install, activate, and test your registration page.  No edits required.

== Frequently Asked Questions ==

= How can I force all users, even when logged in, to be challenged with a question before posting? =

* edit the top of the plugin and set  

$human_test['on_for_members']=true;

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.01 (2008-01-27) =

*   Human-Test is born

= Version 0.05 (2008-01-29) =

*   Random numbers and sessions added

= Version 0.06 (2008-01-29) =

*   minor logic bug fix to prevent multiple attempts against same answer
*   additional text localization

= Version 0.07 (2008-02-13) =

* bug fix for some session issues
* bug fix for dealing with bad registration info

= Version 0.7.1 (2008-03-14) =

* more attempts to fix session support on different server configs

= Version 0.7.2 (2008-08-28) =

* don't start session if already started to behave with other plugins that use sessions

= Version 0.7.3 (2008-11-09) =

* use themed error page instead of ugly bb_die

= Version 0.8.1 (2008-11-22) =

* now supports bb-anonymous-posting plugin with a challenge question automatically placed above posts/topics

* optional toggle to force challenge for all members, even when logged in  (edit top of plugin)

= Version 0.9.0 (2009-02-02) =

* change things slightly to confuse spam bots, more to come

= Version 0.9.1 (2009-04-07) =

* missed a change that caused anon posts to always fail, sorry

= Version 0.9.2 (2009-06-15) =

* support annonymous posting on tags.php page

= Version 0.9.3 (2011-01-28) =

* subtle changes to keep bots guessing wrong

= Version 0.9.5 (2012-03-22) =

* now tries to use StopForumSpam.com to check IP and email during registration and sets user inactive if blacklisted, with email alert

= Version 0.9.6 (2013-04-18) =

* now will prevent all additional new user processes from executing if StopForumSpam flags as bad registration


== To Do ==

* optionally write questions in captcha-like graphics (tricks spammers to enter graphic as code instead of answer)

* optionally notify admin of failed registration

