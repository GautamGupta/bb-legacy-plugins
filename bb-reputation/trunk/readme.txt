=== bb Reputation ===
Tags: reputation, karma, points, cash, currency, bank, _ck_
Contributors: _ck_
Requires at least: 0.8.3
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

This is a very early release for the adventurous. Allows members to award Reputation (or "Karma") points to other members for their posts. Can optionally be used as a pseudo currency (ie. "forum dollars").

== Description ==

This is a very early release for the adventurous. Allows members to award Reputation (or "Karma") points to other members for their posts. Can optionally be used as a pseudo currency (ie. "forum dollars").

== Installation ==

* edit settings near the top of `bb-reputation.php` as desired (until admin menu is created)

* install, activate plugin 

* please note that you cannot add reputation to yourself, you'll have to test with another member.

== Frequently Asked Questions ==

* template editing not required - if you want manual placement in posts change `$bb_reputation['automatic']` to `false`

* please note that you cannot add reputation to yourself, you'll have to test with another member

* there's probably going to be a bunch more questions eventually

== To Do ==

* convert transaction history to real db table and not use user_meta which will overload on active forums

* admin review of all transactions 

* admin menu

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-05-05) =

* first public release for alpha testing - warning: history transactions will not be converted to next version, only total points per member
