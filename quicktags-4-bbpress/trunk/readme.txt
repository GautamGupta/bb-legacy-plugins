=== Comment Quicktags for bbPress ===
Contributors: Stefano Aglietti
Tags: quicktags, textarea
Requires at least: 0.74
Tested up to: 0.74
Stable tag: 1.0

Inserts a quicktag toolbar on the post topic form. js_quicktags is
slightly modified version of Alex King's newer Quicktag.js
(http://www.alexking.org/blog/2005/07/01/javascript-quicktags-12/)
plugin modified from original found at 
http://www.asymptomatic.net/wp-hacks

== Description ==

This plugin adds a quicktags toolbar over textarea for inserting
or editing posts in the various bbPress pages. This plugin is a
modified and simplyfied version of the WordPress plugin
Comment Quicktags + by Dan Cameron.

The plugin already support the IMG tag to include images inside
a topic. To make this tag works you need to installa another
plugin to support the img tag that you can find here:

http://bbpress.org/plugins/topic/5?replies=14

== Installation ==

Unzip the file and copy the quicktags_4_bbpress folder into
my-plugins directory… That’s all! :)

If the my-plugins directory is not present you need to create it under
the root directory of your forum.

Note: If you use bbPress in the version 0.72 or 0.73 the plugin will be
active only in the page that show a single topic, to malke the toolbar
available in all windows to insert/modify topic and post you should edit
the header.php file in the standard template or in you presonal template.
You have to change the following lines:

`< ?php bb_enqueue_script('topic'); bb_head(); ?>`
`< ?php endif; ?>`

into:

`< ?php bb_enqueue_script('topic'); ?>`
`< ?php endif; ?>`

`< ?php bb_head(); ?>`

No modification needed for version 0.74 or above.

The toolbar CSS id is "#ed_toolbar", so you could add this
to your stylesheet, I use:

#ed_toolbar input
{
	background: #14181B;
	color: white;
	border:2px dashed #323136;
	padding: 0px;
	width: 65px;
}
#ed_toolbar input:hover
{
	background: #323136;
	color: white;
	border:2px dashed #14181B;
	padding: 0px;
	width: 65px;
}
}

== Frequently Asked Questions ==
