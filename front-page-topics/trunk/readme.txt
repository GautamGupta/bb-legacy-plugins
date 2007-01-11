=== Front Page Topics ===
Tags: page_topics, pagination
Contributors: mdawaffe
Stable Tag: 0.7.2

Front Page Topics allows you to change the number of topics shown on the front
page without effecting other pages in bbPress.

== Installation ==

Add the front-page-topics.php file to bbPress' my-plugins/ directory.

== Configuration ==

Where front-page-topics.php reads

    $bb->page_topics = 2;

change the `2` to however many topics you'd like to display on the front page.

== Frequently Asked Questions ==

= Where is the number of topics per page set globally? =

In bbPress' config.php file.  `$bb->page_topics` sets how many topics should be
shown per page. This plugin lets you specify how many topics to show on the
front page and use that global value for everything else.
