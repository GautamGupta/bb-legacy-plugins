=== Hot Tags Plus ===
Tags: hot tags, heat map, tags, tags, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Creates advanced, colorful hot tag heat maps with time & forum filters and caching for performance.

== Description ==

Creates advanced, colorful hot tag heat maps with time & forum filters and caching for performance.

Now you can put customized Hot Tag Heat Maps on every forum page instead of just the front page, and restrict the tag list to specific forums.

It is also possible to restrict the tag cloud to topics that are not outdated (ie. 1 year or newer instead of all time) and ignore tags that have only been used once.

Demo and full option list: http://bbpress.org/plugins/topic/hot-tags-plus/faq/

== Installation ==

* Add the entire `hot-tags-plus` directory to bbPress' `my-plugins/` directory.

* It is recommended but optional to create a caching directory, chmod 777. It should be located above your "web root" if posible for added security.

* Edit the settings at the top of the plugin with the caching directory, etc.

* Edit your templates as desired to display the new Hot Tags.

* You can replace bbPress's `bb_tag_heat_map()` with `hot_tags_plus()` within the `front-page.php` template and `bb_tag_heat_map()` within `tags.php` template.

* There are many options you can use, here is an example of how it's done:
`
<?php
$options=array( 'smallest' => 13, 'largest' => 34, 'unit' => 'px', 'limit' => 40,  'minimum' => 2, 'maximum'=>0, 'colors' => array('24244C','600000','C00000'));
hot_tags_plus($options);
?>

* Demo and full option list: http://bbpress.org/plugins/topic/hot-tags-plus/faq/

== Frequently Asked Questions ==

= While normally options are sent internally through your PHP, here is a demo made to use URLs: =

default tag cloud:  http://bbshowcase.org/forums/tags/

tags for only the first forum, excluding tags that are only used once:
http://bbshowcase.org/forums/tags/?forums=1&minimum=2
internal command on a forum page  `global $forum_id; hot_tags_plus(array('forums'=>$forum_id,'minimum'=>2));` 

the above tag cloud but sorted by tag count instead of alphabetical
http://bbshowcase.org/forums/tags/?forums=1&minimum=2&sort=numeric
internal command on a forum page  `global $forum_id; hot_tags_plus(array('forums'=>$forum_id,'minimum'=>2,'sort'=>'numeric'));` 

tags used only on topics that have been replied to within the past month:
http://bbshowcase.org/forums/tags/?since=1+month+ago
internal command would be  `hot_tags_plus(array('since'=>'1 month ago'));` 

tags in green (without waypoint colors)
http://bbshowcase.org/forums/tags/?colors[]=003300&colors[]=00ee00
internal command would be `hot_tags_plus(array('colors'=>array('003300','00ee00')));` 

= Here are all the options: =
`
$options = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45, 'format' => 'flat', 
	'minimum' => 0, 'maximum' => 0, 'forums' => 0, 'since' => 0, 'sort' => 0, 'colors' => array('24244C','600000','C00000') );
hot_tags_plus($options);		
`
* smallest  - how small should the font go (default 8)
* largest - how large should the font go (default 22)
* unit - what CSS measure should the font use (default pt, you can also try px and em)
* limit - how many tags should be shown (default 45)
* format - display flat or nested ( default flat)
* minimum - minimum number of uses a tag must have to be shown (default 0, meaning no restriction)
* minimum - maximum number of uses a tag must have to be shown (default 0, meaning no restriction)
* forums - restrict tags from the following forums, can be single or an array (default 0, no restriction)
* since -  time based filter, show only tags from topics that have a reply no older than specified
	can be a unix timestamp, a mysql timestamp or even a plain english phase, ie.  "1 year ago" or "2 months ago"
* sort -  default uses the built in bbPress tag sort, but also can be "alphabetical" or "numeric" / "counts" (sorts by most tag use first)
* colors - an array of at least one start color, one end color, and any color "waypoints" along the way to guide the color range
              ie. default of  array('24244C','600000','C00000')   means start with #24244C  end with #C00000 and guide through #600000
	There can be almost unlimited waypoints. Do NOT use CSS short color codes, ie. 609 is invalid, use 660099 instead.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-07-20) =

* first public release

= Version 0.0.2 (2009-07-22) =

* highlights related tags on mouseover (optional)
* stylesheet control

= Version 0.0.3 (2009-08-05) =

* various performance and visual tweaks

= Version 0.0.4 (2009-08-13) =

* eliminate the use of glob which is restricted on some php hosts

== To Do ==

* admin menu
* cache control for time vs tag changes
