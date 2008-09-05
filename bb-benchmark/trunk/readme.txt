=== bbPress Benchmark ===
Tags: _ck_, performance, speed, benchmark, mysql, queries, cache, caching, faster
Contributors: _ck_
Requires at least: 0.8
Tested up to: trunk alpha 1075
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators. Based on Jerome Lavigne's Query Diagnostics for WordPress.

== Description ==

Now you can find out a bit more detail than just "11 Queries, 0.500 seconds" at the bottom of your bbPress pages.
bb-Benchmark prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators.
Simply do a "view source" on any bbpress page to see hidden results at bottom (visible to administrators only).
Output includes current server load, page render vs mysql query time, slowest query and a list of all queries used.

== Installation ==

1.  put  `define('SAVEQUERIES', true);`   into your `bb-config.php` (or `config.php`)
2.  install plugin  (leading underscore in filename means it auto-activates, auto-loads in bbPress)
3. "view source" in browser on any bbPress page to see hidden results at bottom (visible to administrators only)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.10 (2007-07-15) =

* first public release

= Version 0.11 (2007-08-08) =

* improved load detection for php in safe mode or without shell access if using PHP5

= Version 0.12 (2007-08-09) =

* visual output cleanup & reminder to add "define('SAVEQUERIES', true);" 

= Version 0.13 (2007-08-10) =

* display breakdown by template pre-load

= Version 0.14 (2007-08-11) =

* additional sections timed (plugins loaded)

= Version 0.14 (2007-08-11) =

* switched to auto-load plugin (leading underscore) to better time main plugins loading

= Version 0.18 (2008-02-11) =

* 0.15	: switched to auto-load plugin (leading underscore) to better time main plugins loading
* 0.16	: better unnamed hook tracking so benchmark timer can be inserted almost anywhere
* 0.17	: double dashes break html comments and make them visible -- replaced as - -
* 0.18	: added hook to admin panel for plugin admin testing
* 0.19	: important bug fix to hide output again in certain situations

= Version 0.2.0 (2008-04-16) =

* 0.2.0	: bug fix when checking BB_IS_ADMIN

= Version 0.2.1 (2008-08-06) =

* 0.2.1	: set priority to dead last so ALL queries are tracked in bb_foot, another BB_IS_ADMIN bug

= Version 0.2.2 (2008-09-05) =

* 0.2.2	: adds function trace available in bbPress 1.0 - would like to replace with enhanced/deeper trace someday
