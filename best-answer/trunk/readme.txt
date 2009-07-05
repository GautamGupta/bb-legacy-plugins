=== Best Answer ===
Tags: best answer, vote, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows the topic starter or moderators to select which reply is a "Best Answer" to the original post. 

== Description ==

Allows the topic starter or moderators to select which reply is a "Best Answer" to the original post. Helpful for support forums, etc.

== Installation ==

* Add the `best-answer.php` file to bbPress' `my-plugins/` directory and activate.

* There are a few options you can edit at the top of the plugin including allowing multiple best answers 
and if the best answer(s) should be displayed first when viewing the topic.

* because of lack of foresight in the bbPress output functions you have to edit the topic.php template to change post colors
 change: 
 `<?php foreach ($posts as $bb_post) : $del_class = post_del_class(); ?>`
 to:  
  `<?php foreach ($posts as $bb_post) : $del_class = apply_filters('best_answer_class',post_del_class()); ?>`

== Frequently Asked Questions ==

 = How can other users vote?  =

* this is a simplified version of "best answer" and doesn't allow voting by other users right now

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.2 (2009-05-23) =

* first public release

== To Do ==

* admin menu ?
