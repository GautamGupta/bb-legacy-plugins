=== Thanks ===
Contributors: paulhawke
Tags: thanks, like, voting, ajax
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 1.0.2

== Description ==

The "Thanks" plugin allows logged in users to add a vote of thanks for posts in the forum, and report how many "thank you" votes posts have received.  All text is fully configurable via an admin page (so users can "like" a given post, or the plugin could report the post has "15 sparkling vampires" if you really want it to) and has the ability to fully uninstall its data from the database at the click of a button.

Votes are cast using AJAX, and a given user's vote only counts once for any given post, no matter how many times they click the "vote" link!

== Screenshots ==

== Installation ==

The `thanks` directory needs to go into your `my-plugins` directory. If you dont have one, you can create it so that it lives alongside your `bb-plugins` directory. Alternatively, `thanks` can be dropped directly in to your `bb-plugins` directory.

Oh, and don’t forget to Activate the plugin!

== Frequently Asked Questions ==

= I clicked the "voting" link multiple times but it still only registered one vote of thanks =

Yes, that is how it's meant to work.  The plugin counts the number of people who add a vote of thanks, not the number of times the voting link is clicked, to keep things fair.  This means you can only vote for a given post once.

= I dont like where the plugin puts its text =

Take a look in the admin area of your forum - you can opt to have the plugin output its report of the number of votes of thanks and the voting link either before, or after, each post.  You can also edit the text that is used by the plugin.

= I dont like the margins and styling used by the plugin, can I change them? =

Have at it!  The plugin wraps its output in a `div` with a class of `thanks-output` - if you want to get into the code to really customize the output, look in `thanks.php` at the `thanks_output()` method.

== Change Log ==

= Version 0.5 =

Initial release.
