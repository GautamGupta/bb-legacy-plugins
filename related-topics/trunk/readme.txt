=== Related Topics  ===
Tags: related, topics, tags, keywords, search, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: trunk
Stable tag: trunk
Donate link: http://amazon.com/paypage/P2FBORKDEFQIVM

Displays a list of related topics based on tags and keywords. No template edits required.

== Description ==

Displays a list of related topics based on tags and keywords. No template edits required.

View any topic on http://bbShowcase.org to see a demonstration.

== Installation ==

* Install the `related-topics.php`  file  into  `my-plugins/` and activate.

* Change defaults if desired by editing options at the top of the plugin until an admin menu is made. 

* This plugin inserts itself into the topicmeta at the top of each topic automatically, no template edits required unless you want custom placement.

* ONLY if you want CUSTOM placement, edit your `topic.php` template (or other template) to add  the info like so:
`	
<?php do_action('related_topics'); ?>
`
	or
`
<?php related_topics(); ?>
`
The first method is prefered because if you deactivate the plugin, you won't get errors. 
But the second method will allow you to specify options like topic id and alternate before/after tags, ie. `related_topics(97,'<ul>','</ul>');`

* it is highly recommend you put this line in your `bb-config.php`  which will help with database performance on active forums by loading all  bbPress options at once instead of piecemeal
`
$bb->load_options = true;
`
* very large/active forums may want to turn off the title keyword search and just use tags until caching is added, check loads when viewing topics

== Frequently Asked Questions ==

= How does this determine related topics? =

* It scores topics higher based on multiple tag matches, keywords in topic titles and younger topic age.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://amazon.com/paypage/P2FBORKDEFQIVM

== History ==

= Version 0.0.1 (2008-11-15) =

* first public release for proof-of-concept feedback

= Version 0.0.2 (2008-11-15) =

* significant mysql speedup via custom query - now only uses 2 queries regardless of number of tags or results

* optional message if no related topics found

= Version 0.0.3 (2008-11-16) =

* now can also search titles for keywords, especially handy if it's a new topic or no tags yet - this feature is on by default
 - very large forums may want to turn this feature off as there is no fulltext index on titles and it may be too slow until caching is added

== To Do ==

* eventually check posts for keyword matches (this feature may take some time due to performance/technical limitations)

* cache results (current adds a few queries per topic view)

* manual add / manual exclude related topics to list