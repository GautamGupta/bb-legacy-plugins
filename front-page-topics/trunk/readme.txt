=== Topics Per Page  ===
Tags: _ck_, topics, posts, custom, limit, front-page, discussions
Contributors: _ck_, mdawaffe
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Set custom topic or post count limits for nearly every kind of bbPress page while still calculating direct post links correctly. (now 1.0 compatible)

== Description ==

Set custom topic or post count limits for nearly every kind of bbPress page while still calculating direct post links correctly. (now 1.0 compatible).

Now adds the ability to do pagination on the front page with the latest discussions.

== Installation ==

* Edit the top of the `topics-per-page.php` file to change the number of topics or posts you want on each kind of page

* Add the `topics-per-page.php` file to bbPress' `my-plugins/` directory

* Activate plugin

* If you'd like to add pagination (page numbers) on the front page, simply add to your `front-page.php` template `<div class="nav"><?php front_page_pages(); ?></div>` AFTER `<?php endforeach; endif; // $topics ?>
</table>`

* If you use rewrite slugs you MUST add the following rule to your `.htaccess` file 
`RewriteRule ^page/([0-9]+)/?$ /forums/?page=$1 [L,QSA]` 
anywhere before `</IfModule>` where `/forums/` is the path to your bbpress install.


== Frequently Asked Questions ==

= Where's the admin menu? =

* Since the settings in this plugin will likely be edited only once and then left forever, an admin menu would only slow bbPress down

* mdawaffe has instructed me to completely replace front-page-topics with this plugin

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-08-26) =

* first public release after several informal versions posted around the bbPress.org forums

= Version 0.0.2 (2008-10-27) =

* first attempt at workaround for bbPress 1.0a2 topic_page_links() 

= Version 0.0.4 {2008-11-04) =

* now adds the ability to do pagination on the front page with the latest discussions

== To Do ==

* optionally calculate number of stickies on a page and enforce topic count limit including stickies (overriding bbPress's default behaviour)
