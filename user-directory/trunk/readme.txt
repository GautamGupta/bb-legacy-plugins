=== BBPress User Directory ===
Contributors: paulhawke
Tags: user, directory, members, list, messaging
Requires at least: 0.9.0.3
Tested up to: 0.1-alpha4
Stable tag: 0.1-alpha4

== Description ==

This plugin generates a clean user-facing user directory (ie. no admin functionality).  The plugin detects the presence of the excellent Private Messaging plugin and if the facility is available, it adds a link to send a private message to any given user in the directory. Similarly the plugin detects if Wordpress integration is enabled, and if Wordpress profile editing has been used to set a given user’s ‘display name’.

== Screenshots ==

1. Basic user directory
2. User directory when "private messaging" is active

== Installation ==

There are three files in the download that need to be installed, the rest are there for documentation. Installing them is simple, feel free to rename the `members.php` file to whatever you want, and copy it to your main bbPress installation directory. You will want to add a link to this file in your template. 

The `bb-user-directory.php` file needs to go into your `my-plugins` directory.  If you dont have one, you can create it so that it lives alongside your `bb-plugins` directory.  Alternatively, `bb-user-directory.php` can be dropped directly in to your `bb-plugins` directory.

Lastly the `userdirectory.php` file needs to be dropped into your active theme directory. 

The files are packaged in the correct directories, so you may be able to unzip and upload directly to your bbPress installation, assuming you rename the *your-theme* directory appropriately. Oh, and don’t forget to Activate the plugin!

== Frequently Asked Questions ==

= I cant access the user directory =

Firstly, have you installed and activated the plugin?  See the installation instructions for details.

Secondly, if the plugin is being reported as active, did you add a link to the `members.php` file?  The link needs to be in your active theme - under the "Views" section would be a good location.

= Can I change the pagination links? =

Yes and no.  In version 0.4 all you can do is change the styling of the <div> that the links live in.  Look for a <div> with an ID of `pagination`.  In a later version there will be more options in the admin area to control pagination.

== Change Log ==

= Version 0.4 =

* Added an automatic "nofollow" onto links in the user directory (Thanks 'Malice' for the suggestion)
* Added pagination (initially hard-coded to 20 per-page, config to come later - thanks to 'johnjamesjacoby' for the suggestion)

= Version 0.3 =

Bumped the version number (0.2 --> 0.3) to indicate passing a milestone: the readme.txt now passes validation!

= Version 0.2 =

Improved documentation, no actual code changes

= Version 0.1 =

Initial release with minimal documentation