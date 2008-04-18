=== Read-Only Forums ===
Tags: restrict,read,private,forum,forums,reply, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Prevent all or certain members from starting topics or just replying in certain forums while allowing posting in others. Moderators and administrators can always post. Note that this does not hide forums, just prevents posting.

== Description ==

Prevent all or certain members from starting topics or just replying in certain forums while allowing posting in others. Moderators and administrators can always post. Note that this does not hide forums, just prevents posting.

== Installation ==

* Until an admin menu is created, edit `read-only-forums.php` and change settings near the top as desired

* Add the `read-only-forums.php` file to bbPress' `my-plugins/` directory and activate.

== Frequently Asked Questions ==

= How do I know the forum number / user number ? =

* administrators can do  forum.com/forums/?forumlist to get a list of forums by number (when the plugin is installed)

* user numbers can be found under forum.com/forums/bb-admin/users.php

= How can I remove the "Add a reply" message when members can't post? =

* in `topic.php` template replace the `<?php post_form(); ?>`  with `<?php if (function_exists('read_only_post_form')) {read_only_post_form();}  else {post_form();} ?>`

* in `forum.php` template replace the `<?php post_form(); ?>`  with `<?php if (function_exists('read_only_post_form')) {read_only_post_form();}  else {post_form();} ?>`

* if ALL your forums are going to be Read-Only, replace in `topic.php` `forum.php` `front-page.php`  `tag-single.php` `<?php post_form(); ?>` with `<?php if (function_exists('read_only_post_form')) {if (bb_current_user_can('moderate')) {read_only_post_form();}}  else {post_form();} ?>`

= Configuration Examples =

* stop ALL members from starting topics in ALL forums 
`$read_only_forums['deny_all_start_topic']=true;`

* stop ALL members from replying to topics in ALL forums
`$read_only_forums['deny_all_reply']=false;`

* which forums should ALL members not be able to start topics
`$read_only_forums['deny_forums_start_topic']=array(9,15,22);`

* which forums should ALL members not be able to reply to posts
`$read_only_forums['deny_forums_reply']=array(9,15,22);`

* allow start topic override for this member=>forums
`$read_only_forums['allow_members_start_topic']=array(1=>array(1,2,3,4,5,6,7), 2=>array(9,10,11));`  

* allow reply override for this member=>forums
`$read_only_forums['allow_members_reply']=array(1=>array(1,2,3,4,5,6,7), 2=>array(9,10,11));` 	

* deny start topic for this specific member=>forums
`$read_only_forums['deny_members_start_topic']=array(54321=>array(1,2,3,4,5,6,7), 34567=>array(1,2,3));`

* deny reply for this specific member=>forums
`$read_only_forums['deny_members_reply']=array(54321=>array(1,2,3,4,5,6,7), 34567=>array(1,2,3));`

* these types of users can always start/reply
`$read_only_forums['allow_roles_always']=array('moderator','administrator','keymaster');`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-04-17) =

* first public release

== To Do ==

* admin menu

