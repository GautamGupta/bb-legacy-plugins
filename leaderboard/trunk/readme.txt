=== Leaderboard ===
Tags: leaderboard, leaders, posters, posts, comments, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Shows the most active users across bbPress and WordPress within different time periods. Customizable templates. 

== Description ==

Shows the most active users across bbPress and WordPress within different time periods.
Features a template system so you can customize how the output looks.

Optional caching system to handle very active websites. 
Uses Post Count Plus data if available to accelerate queries.

Demo:
http://bbshowcase.org/forums/view/leaderboard
http://boards.weddingbee.com/view/leaderboard

== Installation ==

* Add the entire `leaderboard/` directory to bbPress' `my-plugins/` directory.

* Edit the settings at the top of `leaderboard.php` as desired.

* Optionally create caching directory if desired and chmod 777. It should be located above your "web root" if posible for added security.

* Activate and check your list of views for the new leaderboard view.

* Optionally edit your bbpress theme and add 
`
<?php leaderboard("sidebar",1); ?>
`
Where "1" is the number of days to include in the results and "sidebar" is the name of the leaderboard template to use.

* You can create other templates as desired, simply copy the styles and methods found in "sidebar.php" and "view.php" to achieve the desired results.

* You can use most any bbPress function inside the leaderboard template to display other user data.

* If you have additonal bbPress or WordPress installations that are all tied to the same user table, you can include the other table names in the configuration, then
the posts and comments will be included, there is no limit. It is recommended to suppliment with the Post Count Plus plugin which creates a higher performance index.

== Frequently Asked Questions ==

 = I don't have WordPress, why does it show zero comments?  =

* Simply remove the columns for comments out of your templates.

= I made some posts but the sidebar didn't change  ? =

* You are probably using the caching system. Reduce the caching time if desired. The main view is real-time.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2009-07-13) =

* first public release

= Version 0.0.2 (2009-07-16) =

* support for forum specific filter with optional dropdown in view (note leaderboard arguments have changed order)

== To Do ==

* admin menu ?
