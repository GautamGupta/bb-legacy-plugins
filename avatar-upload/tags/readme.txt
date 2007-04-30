=== Avatar Upload ===
Tags: avatars, avatar, uploads, profile
Contributors: LouiseDade
Requires at least: 0.8
Tested up to: 0.8.1
Stable Tag: 0.1

Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress. Admins can configure maximum allowed file size and image dimensions.

== Description ==

Plugin URI: http://www.classical-webdesigns.co.uk/articles/43_bbpress-plugin-avatar-upload.html

Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress, and provides template functions to display the uploaded image.

Features:
* Bozos can not upload avatars.
* Admins can configure maximum allowed file size (bytes) and dimensions (pixels) of images.
  - Current done from within the script (no Admin page interface at this time).
* Anybody with the 'moderate' capability can upload another user's avatar
  - this to ensures that inappropriate images can be removed.
  - there is no "delete avatar" function at this time, but an inappropriate image can be removed by uploading a 'safe' image (e.g. a blank 1x1 pixel image) to replace it (you could them manually set that user as a bozo to stop them re-uploading inappropriate images.

== Installation ==

1. Create a folder to store your avatars. A folder called "avatars" in the root directory of your bbPress installation is probably best (and the default).

2. Open up the 'avatar-upload.php' file and configure the "configuration Variables" (if desired). At least make sure the '$avatar_dir' path is correct.  Other configurable variables include the maximum allowed width and height of uploaded images and the maximum file size (in bytes).

3. Open up your 'profile-edit.php' template and insert the following "Upload Avatar" link wherever you wish:

    <a href="avatar-upload.php?id=<?php echo $user->ID; ?>">Upload Avatar</a>

4. To display an uploaded avatar, just insert the following template function:

    <?php display_avatar(ID); ?>

   Where 'ID' is the available user_id.  The following examples show you where to grab the user_id for the user profile and forum posts pages.

   a) On the user's profile page ('profile.php' template).
      
      <?php display_avatar($user->ID); ?>

   b) On each user's forum posts ('post.php' template)

      <?php display_avatar(get_post_author_id()); ?>

5. This is optional, but you can open up 'my-templates/avatar.php' and edit the template if you wish, but be sure not to mess with the upload form.

6. Upload the plugin scripts to the following locations.

   'avatar-upload.php' - into your bbPress root folder.
   'my-templates/avatar.php' - into your 'my-templates/my-template-name/' folder.
   'my-plugins/bb-avatar-upload.php' - into your 'my-plugins/' folder.

That's it, the 'Avatar Upload' plugin should now be working.

== Configuration ==

Some variables can be configured.  See 'Installation' instructions.

== Frequently Asked Questions ==

= Are there security risks when allowing users to upload images to my server? =

The plugin checks the images upon upload to ensure that only gifs, jpegs/jpgs and pngs are allowed. It checks both the file extension (e.g. '.gif') AND the content-type (e.g. 'image/gif'), as well as ensuring the two match.

However, one can never 100% sure and there is always some security risks when allowing users to upload to your server. USE THIS PLUGIN AT YOUR OWN RISK!

== Change Log ==

2007-04-07  Ver. 0.1 released.