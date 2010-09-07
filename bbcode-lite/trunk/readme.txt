=== BBcode Lite for bbPress ===
Tags: bbcode, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

A lightweight alternative to allow BBcode on your forums without slowdowns.

== Description ==

Most bbPress and WordPress plugins for BBcode  rely on the Pear PHP class which is HUGE and SLOW.
The big problem with that is it has to execute for every single post, every single time a post is displayed,
because the post is stored natively in bbPress with the BBcode intact. An active forum can get overloaded.

This version is less than one tenth the size and executes much faster. 
It also takes into account that bbpress does the rest of the parsing.
Users may mix bbcode and html in the same post.

If "Allow Images" is installed, the `[img]` BBcode will also work.

* example:
http://bbshowcase.org/forums/topic/new-bbpress-plugin-bbcode-lite

== Installation ==

* Add the `bbcode-lite.php` file to bbPress' `my-plugins/` directory and activate.

== Frequently Asked Questions ==

 = What BBcodes are supported? =

* see here for examples, though all BBcode is not supported as there's no single standard:
http://www.vbulletin.com/forum/misc.php?do=bbcode#basic
http://bbshowcase.org/forums/topic/new-bbpress-plugin-bbcode-lite

* [list] [list=1] [list=a]   [/list] (unordered and ordered lists both numeric and alphabetical)   use  [*] for items
* [img][/img]
* [url][/url]  or  [url=http://bbpress.org]bbpress[/url]
* [size=4]  [size=+2]  [size=-1]   [/size]
* [color=red] [color=#FF0000] [/color]
* [center] [/center]
* [u] [/u]
* [b] [/b]
* [s] [/s] or  [strike] [/strike]
* [code] [/code]
* there may be others, periodically updated based upon request

= What if I want to show BBcode examples and not have them evaluated? =

* BBcode inbetween backticks or `code`  is ignored and will not be processed

= some BBcode is not working? =

* There are still some weird special circumstances where the BBcode may not work properly,
be sure to let me know if you find some and I will address them as time permits. 

= It's fast but my forum is huge and it's still too slow? =

* If you have an extremely active forum or overloaded server you can change 
`post_text` to `pre_post` within the plugin filter to permanently save bbcode as html.
(however this makes it a bit harder for users to re-edit a post as the bbcode will then appear as html)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 1.00 (2008-02-26) =

* first public release

= Version 1.0.3 (2008-10-15) =

* speed up by moving definitions to init instead of each post pass 

* security issue fix where IMG tag supported even when "allow images" plugin disabled - can be turned back on, see first few lines of plugin

* pre tag added

= Version 1.0.5 (2010-09-07) =

* emergency release for security issue - ALL USERS SHOULD IMMEDIATELY UPGRADE ASAP

== To Do ==

* enforce limits for [SIZE] and [COLOR] BBcodes
