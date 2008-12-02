=== BBcode Buttons  ===
Tags: bbcode, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Automatically adds an easy access button toolbar above the post textarea to allow quick tags in BBcode. This is an enhanced replacement for the Comment Quicktags plugin. No template editing required.

== Description ==

Automatically adds an easy access button toolbar above the post textarea to allow quick tags in BBcode. This is an enhanced replacement for the Comment Quicktags plugin. No template editing required.

Requires the BBcode-Lite plugin (or some other kind of BBcode support).

Some buttons will only appear if the tag is supported  (ie. img, center)

Smilie support coming eventually.

* example:
http://bbshowcase.org/forums/topic/new-bbpress-plugin-bbcode-lite

== Installation ==

* Add entire folder `bbcode-buttons` to bbPress' `my-plugins/` directory and activate.

== Frequently Asked Questions ==

= Why is this better than Comment Quicktags? =

* The Comment Quicktags plugin does something extreme awkward - it captures the entire PHP page output and then parses it to insert the toolbar. 
That can be incompatible with some PHP configurations. BBcode Buttons instead uses javascript to insert itself. BBcode is also more familiar to forum users than bbPress's default html tags.

= The toolbar is working but the codes don't do anything? =

* BBcode Buttons requires the BBcode-Lite plugin installed (or some other kind of BBcode support).

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-06-19) =

* early beta release based upon a request

= Version 0.0.7 (2008-12-02) =

* weird bug fix from php parsing comments inside javascript

* compatibility with anonymous posting via more natural post-form action 

== To Do ==

* lots, this is an early beta

