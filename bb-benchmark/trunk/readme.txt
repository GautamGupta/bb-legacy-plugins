=== bbPress Benchmark ===
Tags: performance, speed, benchmark, mysql, queries, cache, caching
Contributors: _ck_
Requires at least: 0.8
Tested up to: 1.0 alpha build 892
Stable tag: trunk

Prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators. Based on Jerome Lavigne's Query Diagnostics for WordPress.

== Description ==

Now you can find out a bit more detail than just "11 Queries, 0.500 seconds" at the bottom of your bbPress pages.
bb-Benchmark prints simple benchmarks and mysql diagnostics, hidden in page footers for administrators.
Simply do a "view source" on any bbpress page to see hidden results at bottom (visible to administrators only).
Output includes current server load, page render vs mysql query time, slowest query and a list of all queries used.

== Installation ==

1. put  define('SAVEQUERIES', true);   into your config.php
2. install plugin  (leading underscore in filename means it auto-activates, auto-loads in bbPress)
3. do a "view source" on any bbpress page to see hidden results at bottom (visible to administrators only)

== License ==

CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Version History ==

Version 0.10 (2007-07-15)

* first public release

Version 0.11 (2007-08-08)

* improved load detection for php in safe mode or without shell access if using PHP5

Version 0.12 (2007-08-09)

* visual output cleanup & reminder to add "define('SAVEQUERIES', true);" 

Version 0.13 (2007-08-10)

* display breakdown by template pre-load

Version 0.14 (2007-08-11)

* additional sections timed (plugins loaded)

Version 0.14 (2007-08-11)

* switched to auto-load plugin (leading underscore) to better time main plugins loading
