=== Super Search ===
Tags: search, find, google, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Radically improves the search in bbPress with many advanced options.

== Description ==

Radically improves the search in bbPress.

Adds Google-like search ability to your bbPress forum, with many advanced options to pinpoint results.

== Installation ==

* Put entire plugin contents into `my-plugins/super-search/`

* Install, activate, no template edits required.

* Plugin automatically intercepts search requests from existing search forms on your forum.

== Frequently Asked Questions ==

= Can I change the search form or instructions? ==

* Edit the `form.php` or `instructions.php` as desired.

* You can remove any form element you don't want used, or move ALL of them to the advanced section, etc.

* You can even limit certain fields to specific user levels by wraping them in IF statements,
    ie.  `if (bb_current_user_can('administrate')) {SSinput('regex');}`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2008-02-04) =

*   Super-Search is born.

= Version 0.0.2 (2008-12-07) =

*   First public alpha release for testing and feedback.

= Version 0.0.3 (2009-03-29) =

*   Don't strip all non A-Z to allow non-english languages to work - may need more testing

== To Do ==

* sort by number of views does not work yet

* result cache to reduce load on mysql (can't use indexes)

* user list needs to be replaced by a regular input field with ajax-like results, ala "Google Suggest"

* context routine needs to find groups of words more intelligently and split on words instead of on characters