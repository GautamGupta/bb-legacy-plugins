=== Post Meta for bbPress ===
Tags: posts, anonymous, custom fields, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Create additional custom fields for posts, including 'Name', 'Email' and 'Website' for anonymous users.

== Description ==

Now you can create custom fields for posts like 'Name', 'Email' and 'Website' for anonymous users.

Post Meta suppliments bb-anonymous (_ck_ mod) or can work on it's own to add additional custom fields to your posts.

It has the default code to demonstrate fields like WordPress's 'Name', 'Email' and 'Website' and also a 'Post Title' for admin/mods (which can be disabled).

Post Meta is very complicated as it has to "trick" bbPress in several places while getting along 
with other plugins so please test extensively for compatibility before using on a live site.

Human Test plugin or some kind of Captcha plugin is highly recommended when allowing anonymous posts.

== Installation ==

* NOTICE: you MUST change line 9 of `edit-form.php` template from `<?php endif; ?>` to  `<?php endif; do_action( 'edit_form_pre_post' ); ?>`

* Add the `post-meta.php` file to bbPress `my-plugins/` directory and activate. 

== Frequently Asked Questions ==

= If you use the following plugins you must upgrade to the minimum version listed for proper compatibility =

* BB  Anonymous _ck_mod 1.2.0   (included with this plugin)
* Post Count Plus 1.1.7 
* bbPress Reputation 0.0.6

== Screenshots ==

1. Post Meta working with BB Anonymous Posting and Human Test (also visible: Post Count Plus and Related Topics)

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== History ==

= Version 0.0.1 (2008-12-22) =

* first public alpha release

== To Do ==

* lots
