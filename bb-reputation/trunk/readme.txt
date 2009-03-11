=== Reputation (Karma) for bbPress ===
Tags: reputation, karma, points, cash, currency, bank, _ck_
Contributors: _ck_
Requires at least: 0.8.3
Tested up to: 0.9.x
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

This is a very early release for the adventurous. Allows members to award Reputation (or "Karma") points to other members for their posts. Can optionally be used as a pseudo currency (ie. "forum dollars").

== Description ==

This is a very early release for the adventurous. Allows members to award Reputation (or "Karma") points to other members for their posts. Can optionally be used as a pseudo currency (ie. "forum dollars").

== Installation ==

* edit settings near the top of `bb-reputation.php` as desired (until admin menu is created)

* install, activate plugin 

* please note that you cannot add reputation to yourself, you'll have to test with another member.

== Frequently Asked Questions ==

* template editing not required - if for some reason you want manual placement in posts change `$bb_reputation['automatic']` to `false` and put `<?php bb_reputation(); ?>` somewhere in your `post.php` template

* please note that you cannot add reputation to yourself, you'll have to test with another member

* there's probably going to be a bunch more questions eventually

== To Do ==

* convert transaction history to real db table and not use user_meta which will overload on active forums

* admin review of all transactions 

* admin menu

* ajax-ish behaviours

* dhtml prompts instead of javascript popups

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-05-05) =

* first public release for alpha testing - warning: history transactions will not be converted to next version, only total points per member

= Version 0.0.2 (2008-05-10) =

* admin (or you can also allow mods) can apply negative points for deductions

*  bug fix for incorrect name displayed in reputation history

= Version 0.0.3 (2008-09-06) =

* compatibility fix for 1.0

= Version 0.0.6 (2009-03-10) =

* serious XSS fix - please update immediately - props mciarlo
