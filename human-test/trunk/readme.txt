=== Human Test for bbPress  ===
Tags: bots, captcha, register, registration
Contributors: _ck_
Requires at least: 0.8
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Uses various methods to exclude bots from registering (and eventually posting) on bbPress, including math, captcha and "negative fields".

== Description ==

Bot registration is becoming a problem on bbPress. 
This will slow them down if not stop them entirely.

Simply adds a new field at the bottom of your registration page with two random numbers to add together. 
Uses a few tricks like javascript and entity encoding to slow down the smarter bots.

(no core modifications required, 100% plugin based)

Optional image based (captcha style) writing coming soon as well as "negative fields".

== Installation ==

* Install, activate, and test your registration page. 

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

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

