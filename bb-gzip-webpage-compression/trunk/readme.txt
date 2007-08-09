=== bb Gzip  ===
Tags: compress, bandwidth, performance, speed, mod_gzip, mod_deflate
Contributors: _ck_
Requires at least: 0.8
Tested up to: trunk
Stable tag: trunk

Makes bbPress output smaller and faster pages for all modern web browsers. Only use if you do not have compression already available on your host.

== Description ==

This plugin is only for bbPress users on servers which have no other compression methods installed (mod_gzip, mod_deflate, etc.)
Please check to make sure your pages are NOT already compressed, ie. via http://www.pipeboost.com/report.asp or http://whatsmyip.org/mod_gzip_test/

bbPress Web Compress gzips output to all modern web browsers that support it and leaves others alone.
A 30k page can be reduced to as little as 5k which can be appreciated even on broadband and reduce your hosting bill if you are near your limit.
Your static files, css, javascript & images will not be compressed.

(some coders think this is as easy as setting ob_start("ob_gzhandler") but there are often missed checks and performance options)

== Instructions ==

Check to make sure web pages aren't already compressed, install, activate, analyze webpages again to see size/speed improvements.

== License ==

CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Version History ==

Version 0.01 (2007-08-06)

*   bb-gzip is born

Version 0.02 (2007-08-09)

*   a couple extra checks for special conditions added, chunked output if possible
