=== Move It ===

Tags: move, post, merge, split, moderate
Author: Matteo Crippa (gh3) & _ck_
Contributors:  _ck_
Requires at least: 0.8.2
Tested up to: 0.8.2.1
Stable Tag: trunk

This plugin allows you to move, merge & split posts and topics. 
Major re-writes by _ck_ for versions 0.14+

== Description ==

This plugin allows you to move, merge & split posts and topics. 

== Installation ==

1. delete any old "moveit.php" due to file rename to "move-it.php" for update
2. put "move-it.php" into my-plugins
3. put "move-it-helper.php" into the bbpress root (where config.php is)
4. edit your "topic.php" template and near the end put <?php move_it_topic_form(); ?> after <?php topic_move_dropdown(); ?>
5. edit your "edit-post.php" template and near the end put  <? move_it_post_form(); ?>  after <?php edit_form(); ?>
6. moderators will see the new options at the end of every topic or when when editing any user's post

== Version History ==

Version 0.14 (2007-07-19)

* plugin renamed from moveit.php to move-it.php for naming consistency
* code restructuring and functions rename 
* dynamic dropdown list, only loads topics on demand, saves bandwidth and server load
* post postion table updates added

Version 0.13 (2007-07-16)

* dropdown list for topics, other bug fixes by _ck_

Version 0.12 (2007-05-07)

* Now topics can be merged
* Problem with url reload on button pressing that doesn't hide the moved post

Version 0.11 (2007-05-02)

* Both origin topic and destination topic info are now upgraded
* Problem with url reload on button pressing that doesn't hide the moved post

Version 0.1 (2007-05-01)

* First release