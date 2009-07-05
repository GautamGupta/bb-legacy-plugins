=== Wiki Post ===
Tags:  wiki, wiki post, group post, shared post, collective, collaborative, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

== Description ==

Allows any member to edit a Wiki-like post in any topic for a collaborative entry or FAQ. 

This is a beta release without an admin menu (coming soon). 
For now you must edit the wiki-post.php directly to change default options.

== Installation ==

* Edit `wiki-post.php` and change any default settings as desired near the top (until admin menu is available).
* Add the "wiki-post.php" file to bbPress "my-plugins/" directory and activate. 
* Optionally edit the new "Wiki Post" member that has been created for you, ie. add avatar icon or change email for gravatar use
* If you edit `wiki-post.php` after install, you must reset the plugin via `your-forum.com?reset_wiki_post` (until there is an admin menu)

== Frequently Asked Questions ==

* demo: http://bbshowcase.org/forums/topic/new-bbpress-plugin-wiki-post

* If you edit `wiki-post.php` after install, you must reset the plugin via `your-forum.com?reset_wiki_post` (until there is an admin menu)

* There is a separate level control to create the Wiki and then another be able to edit it - so if you want, you can allow only mods+admin to create the Wiki and then any member to edit it.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

* 0.0.1 	wiki-post is born 
* 0.1.0	first public beta release
* 0.1.1 a few minor tweaks
* 0.1.5	compatibility fix for bbPress 1.0 (use  ?reset_wiki_post)
	
== To Do ==

* history of edits
* rollback of edits
* optional different background color
* admin menu
