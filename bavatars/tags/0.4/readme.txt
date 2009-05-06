=== Bavatars ===
Contributors: Nightgunner5
Tags: avatar, upload
Requires at least: 0.9
Tested up to: trunk
Stable tag: 0.4

Gravatar - Globally recognized + bbPress = Bavatar

== Description ==

**Gravatar - Globally recognized + bbPress = Bavatar**

Users can upload avatars to the forum, which are automatically resized and cached.

Non-images cannot be uploaded.

== Installation ==

1. If you don't want the avatar max filesize to be 50KiB (less than 5% of a megabyte but still plenty), edit the bavatars.php file.
1. Upload the entire `bavatars` plugin to the `my-plugins` directory of your bbPress installation. If you don't have one, create it!
1. Activate the plugin in your administration panel.
1. If you have WordPress simple integration:
	1. Fill out the top of the `bavatars-wp.php` file.
	1. Copy the `bavatars-wp.php` file to your WordPress `wp-content/plugins` folder.

== Changelog ==

* 0.4
	* WordPress simple integration compatibility
* 0.3.1
	* Bavatars can now be deleted
	* Bavatars is compatible with bbPress 0.9 (hopefully)
* 0.2.1
	* Bavatars works with bbPress 0.9
* 0.2
	* Bavatar sizes that are generated in bb-admin will no longer cause errors.
* 0.1
	* First public release