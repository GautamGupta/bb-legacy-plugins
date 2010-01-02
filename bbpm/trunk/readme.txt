=== bbPM ===
Contributors: Nightgunner5
Tags: private messaging, messages, pm
Requires at least: 1.0
Tested up to: trunk
Stable tag: 1.0-beta1

bbPM - The new way to private message.

== Description ==

Users can send private messages to each other. Replies are threaded, and more than two users can participate in a single conversation.

[Developer documentation can be found here.](http://nightgunner5.is-a-geek.net:1337/phpdoc/bbpm/)

== Installation ==

1. Upload the entire `bbpm` plugin to the `my-plugins` directory of your bbPress installation. If you don't have one, create it!
2. Activate the plugin in your administration panel.

== Screenshots ==

1. Members can send messages to each other as simply as typing in a username, title, and message.
2. Replies are threaded, and the reply form is loaded using ajax.

== Changelog ==

= 1.0 =
* Danish translation from Daniel Juhl
* The last read message in each thread will remain read, and all the messages before it will also be marked read.
* "Don't send me emails" button now works as planned.

= 1.0-beta1 =
* bbPM no longer thinks that messages are new to the person who sent them
* Conversion from the deprecated Private Messaging plugin now works as planned
* Conversion from the deprecated Private Messaging plugin now turns messages with the same title and to/from pair into threads

= 0.1-alpha7 =
* Recount option to remove deleted users from bbPM threads
* Adding users to a thread now redirects properly without pretty permalinks
* Pagination now works as expected
* Emails sent when new messages are created can be disabled
* Forums in the root directory (/ instead of /forums/) will now be able to use bbPM without the RewriteRule.
* Ajax user search when typing in a user's name to add to a thread or write a new thread to.

= 0.1-alpha6b =
* Subscribe to Topic will no longer prevent users from unsubscribing from PM threads.

= 0.1-alpha6 =
* Notices for incorrect my-plugins permissions and location.
* Backend rewrite, now messages can have multiple recipients.
* Admin styling fixed.
* Database queries drastically optimized (6 queries on most pages instead of 25+).
* 0.9 compatibility removed, upgrade your bbPress installation!
* Throttle (like the one used on posting) added

= 0.1-alpha5 =
* Various tweaks and fixes
* Non-logged in users can no longer see the link to private message users.
* The header link is now able to be repositioned.

= 0.1-alpha4b =
* bbPress 0.9 compatibility fix.

= 0.1-alpha4 =
* PM threads are now numbered, using a lot less database queries.
* Unread Topics will now work in harmony with bbPM.
* A strange profile filter error has been resolved.

= 0.1-alpha3 =
* PM this user links have been added to profiles and post authors.
* Inbox sizes are configurable in the administration panel. (Settings -> bbPM)

= 0.1-alpha2 =
* Pretty permalinks on bbPress 0.9 now work as planned.

= 0.1-alpha1 =
* First public release.