=== bb-myAvatars ===
Tags: myavatars, mybloglog, avatars
Contributors: jcorless
Requires at least: 0.8
Tested up to: 0.8
Stable Tag: 0.1

Allows users to use the MyBlogLog avatars 

== Description ==

This is a direct port from the Wordpress plugin MyAvatars by Napolux http://www.napolux.com/projects/myavatars/ - Thanks.

== Installation ==

Add `bb-myavatars.php`, `bb-myavatars-style.css` to your `/my-plugins/` directory.

I have also included sample versions of `profile.php` and `post.php` based on the Kakumei theme. These should be placed under `my-templates/<theme-name>`. 

In addition I've added it to the private messaging plugin and a sample of that can be found in `postmsg.php`

Borrowed the following idea from the other avatar plugin by Joshua Hutchins http://faq.rayd.org/bbpress_avatar/ - Thanks

NOTE: If using the default template, you MUST change style.css so that avatars can fit in the author info area.

I made these changes in style.css to allow for a 150x150px avatar

.post {
	min-height: 150px;
}

.threadauthor {
	margin-left: -110px;
	overflow: hidden;
	position: absolute;
	max-height: 215px;
	width: 160px;
}


NOTE: .post did not exist previously, but the div's did exist.  .threadauthor did exist,

== Configuration ==

The plugin offers the following template tags for use in your templates.

1. `bb_myavatars($id,$gravatar = false)`: Displays the avatar for the users id. 


== Frequently Asked Questions ==

