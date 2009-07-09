=== Edit Post Attributes ===
Tags: best answer, vote, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows administrators to change hidden post settings including the author and timestamp. Use at your own risk.

== Description ==

Allows administrators to change hidden post settings including the author and timestamp. Use at your own risk.

Be careful as there currently is no validation on the changes you make - if you set the date out of bounds you may cause the topic to disappear.

Some plugins track the author of a post independently, which might cause problems, for example if you change the user and a post has attachments, the new author may not be able to delete the old attachments.

== Installation ==

* Add the `edit-post-attributes.php` file to bbPress' `my-plugins/` directory and activate.

* A new set of options will appear below posts when you are editing them.

* There is currently no validation on the changes you make, if you set the date out of bounds you may cause the topic to disappear.

* The plugin *will* properly correct the last poster for the topic (or first poster) as necessary if you change a post author.

* enter User Login,  not their Display Name when changing authors, it won't work if the plugin can't find a match for the username.

== Frequently Asked Questions ==

 = Can I change something else ?  =

* let me know and I'll see what I can do

= Post Position doesn't work  ? =

* bbPress currently uses the date to sort post order, the purpose of post position is unclear, except position 1 which is sometimes used

= A topic completely disappeared ? =

* you probably set the date too far in the future or too far in the past, try going back to the old edit post url for the post and change it back, if that fails, you need to use phpmyadmin

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2009-07-09) =

* first public release

== To Do ==

* admin menu ?
