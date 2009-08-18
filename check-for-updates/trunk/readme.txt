=== Check for Updates  ===
Tags: _ck_, update, upgrade, version, revision, plugins
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Shows which bbPress plugins may be out of date. Avoid plugin security issues and bugs by checking periodically.

== Description ==

Shows which bbPress plugins may be out of date. 

Avoid plugin security issues and bugs by checking periodically.

== Installation ==

* copy `check-for-updates/` directory to  `my-plugins/`

* activate and click on the new sub-menu item "Check for Updates"

== Frequently Asked Questions ==

* doesn't work or lots of errors? your server must allow fsockopen (CURL support coming soon)

* revision is old? I don't have automatic updates for the master list done yet so it may run behind from time to time

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-04-02) =

*   first public alpha release

= Version 0.0.2 (2009-04-09) =

*   first try CURL before fallback to fsockopen, also check BB_PLUGIN_DIR as well as BBPLUGINDIR before fallback to bb-plugins

= Version 0.0.3 (2009-08-18) =

*   switch to API for admin menu insert (required for 1.0.3 compatibility)
