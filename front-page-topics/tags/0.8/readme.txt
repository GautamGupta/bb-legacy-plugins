=== Front Page Topics ===
Tags: page_topics, pagination
Contributors: mdawaffe
Requires at least: 0.8
Stable Tag: 0.8

Front Page Topics allows you to change the number of topics or posts shown on 
most of your bbPress pages.

== Installation ==

Add the front-page-topics.php file to bbPress' my-plugins/ directory.

== Configuration ==

1. Log in as the key master.
2. In your admin panels, go to Site Management -> Front Page Topics
3. Enter the number of topics (or posts) you want displayed on each page.
   If you enter a `0` for any item, the default number (as defined by
   `$bb->page_topics` in your `config.php` file) will be used.


== Frequently Asked Questions ==

= It says I can adjust the number of items in my feeds, but I can't.  Is it lying? =

A little bit, yes.  You can adjust the number of topics or posts in your all of your
feeds except the main feed.  That feed shows the 35 most recent topics from any forum
and cannot be customized with this plugin.
