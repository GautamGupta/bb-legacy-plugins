=== bbPM ===
Contributors: Nightgunner5
Tags: private messaging, messages, pm
Requires at least: 0.9
Tested up to: trunk
Stable tag: 0.1-alpha2

bbPM - The new way to private message.

== Description ==

Users can send private messages to each other. There is threading of replies and individual messages can be deleted.

== Installation ==

1. Upload the entire `bbpm` plugin to the `my-plugins` directory of your bbPress installation. If you don't have one, create it!
1. Activate the plugin in your administration panel.

== Frequently Asked Questions ==

= I use bbPress 0.9 and I get a 404 error when I click on Private Messages =
You will need to add the following code to your .htaccess file. If your bbPress installation is not at /forums, change the code accordingly.

`
RewriteRule ^pm/?(.*)$ /forums/my-plugins/bbpm/index.php?$1 [L]
`

== Screenshots ==

1. Members can send messages to each other as simply as typing in a username, title, and message.
2. Replies are threaded, and the reply form is loaded using ajax.

== Changelog ==

* 0.1-alpha3
	* PM this user links have been added to profiles and post authors
	* Inbox sizes are configurable in the administration panel (Settings -> bbPM)
* 0.1-alpha2
	* Pretty permalinks on bbPress 0.9 now work as planned.
* 0.1-alpha1
	* First public release