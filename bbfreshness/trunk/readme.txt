=== Topic Freshness Filter ===
Contributors: paulhawke
Tags: filter, topics, freshness, display
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 1.0.2

== Description ==

This plugin filters the topics lists to only allow the freshest topics to appear.  Topic "freshness" is derived from the date the last post was made to a given topic, not its creation date.  The plugin compares today's date with the topic, and if the topic is too old, it is filtered and wont be displayed - all topics / posts remain safely in the database however.

== Screenshots ==

== Installation ==

Unzip the distribution, the `freshness` directory needs to go into your `my-plugins` directory. If you dont have one, you can create it so that it lives alongside your `bb-plugins` directory. Alternatively, `freshness` can be dropped directly in to your `bb-plugins` directory.

Oh, and don’t forget to Activate the plugin!

== Frequently Asked Questions ==

= Nothing has happened =

Firstly, have you installed and activated the plugin?  See the installation instructions for details.

Secondly, if the plugin is being reported as active, are all of your topics fresher than the configured threshold?  If so, dont worry, they all passed the test.

= I have no topics! =

Deactivate the plugin - everything will return.  Now check the freshness of the posts in your topics.  Its possible that none are passing the "freshness" check.

= How do I configure the freshness threshold =

Take a look in the `freshness` directory, at `freshness.php` - at the top of the file is a constant you can modify.  The plugin works in terms of days - by default filtering anything older than 90 days.  Feel free to change that value.

== Change Log ==

= Version 0.1 =

Initial release with minimal documentation
