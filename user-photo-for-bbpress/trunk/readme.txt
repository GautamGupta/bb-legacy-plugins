=== User Photo ===
Contributors: Detective
Tags: users, photos, images, avatar, Detective, profile
Requires at least: 0.9.0.2
Tested up to: 1.0 alpha
Stable tag: 1.1

Allows a user to associate a photo with their account and for this photo to be displayed in their posts and profile.

== Description ==

Allows a user to associate a profile photo with their account. Uploaded images are resized to fit the dimensions specified 
on the options page; a thumbnail image correspondingly is also generated.

The resized photo replaces the built-in avatar. It's optional to display the Gravatar (or your configured avatar in the bbPress options)
in case of a user without an uploaded photo.

This plugin is based on the original User Photo for WordPress by Weston Ruster. It has been ported and simplified by Eduardo Graells.

TODOs:

* Make an options page.
* Improve the upload form.

== Installation ==

Upload the folder `user-photo-for-bbpress` in your `my-plugins` directory. Edit the following defines at the beginning of the file user-photo.php:

`
define('USERPHOTO_PATH', ABSPATH . "my-plugins/user-photo-for-bbpress/avatars/");
define('USERPHOTO_URL', 'my-plugins/user-photo-for-bbpress/avatars/');
`

Edit the paths according to your needs:

* `USERPHOTO_PATH` should be the path to a folder with permissions set to 777.
* `USERPHOTO_URL` should be the url to the previous folder.

If you have bbPress integrated with WordPress, you must set the same paths in both installations if you have User Photo for WP.

Also, edit the following variables: `USERPHOTO_FULL_SIZE`, `USERPHOTO_THUMBNAIL_SIZE` and `USERPHOTO_JPEG_COMPRESSION`. Their names are self-explanatory.

The last option to edit is `USE_GRAVATARS_IF_NO_PHOTO`. The default is `0`, which means that if the user doesn't have a photo, then nothing is displayed.
If you set it to `1`, if the user doesn't have a photo then the selected avatar in the bbPress options will be displayed. Also, you must have enabled avatars in the bbPress options, otherwise no photo will be shown (even if you uploaded one).	

Finally, add the following code in the template `profile-edit.php` (inside the form):

`
<?php 
if (function_exists('userphoto_display_selector_fieldset')) 
	userphoto_display_selector_fieldset($user->ID); 
?>
` 

Display of avatars (resized and smaller photo) is automatic. To display the photo you can use the following code: 

`
<?php 
if (function_exists('bb_get_photo'))
	bb_get_photo($id);
?>
`

Where `$id` is the user ID (in a profile, you can use `$user->ID`).	