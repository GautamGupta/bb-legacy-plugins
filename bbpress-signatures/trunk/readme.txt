=== bbPress Signatures ===
Tags: signature, signatures
Contributors: 
Requires at least: 0.8.2
Tested up to: trunk
Stable tag: trunk

== Description ==

This plugin allows users to add signatures to their forum posts.
It extends their edit profile options automatically.
There are several powerful options for administrators.

== Installation ==

Add the `bb-signatures.php` file to bbPress' `my-plugins/` directory.
Activate and check under "Site Management" admin submenu for "Signatures".

If you would like the optional toggle on new/edit posts to allow the member to disable signatures  on a per-post basis, 
you must edit  the  edit-form.php  & post-form.php   templates and place at or near the bottom:  
<?  bb_signatures_checkbox(); ?>
(you can optionally wrap that in a DIV and float it to the left, right style anyway you'd like)

== Version History ==

Version 0.11 (2007-07-31)

* first public release
