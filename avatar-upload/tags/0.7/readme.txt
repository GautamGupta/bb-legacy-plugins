=== Avatar Upload ===
Tags: avatars, avatar, uploads, profile
Contributors: LouiseDade
Requires at least: 0.8.2
Tested up to: 0.8.2.1
Stable Tag: 0.7

Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress. Admins can configure maximum allowed file size and image dimensions. Includes fel64's code enabling 'Identicons' - default avatars made of abstract patterns unique to each user.

== Description ==

Allows users to upload an avatar (gif, jpeg/jpg or png) image to bbPress, and provides template functions to display the uploaded image.

Plugin URI: http://bbpress.org/plugins/topic/46

Author URI: http://www.classical-webdesigns.co.uk/

= Features =

* Bozos can not upload avatars.

* Admins can configure maximum allowed file size (bytes) and dimensions (pixels) of images from within the admin page (credit: Sam Bauers).

  - Images that exceed maximum dimensions are automatically resized (and sharpened if truecolor images).

* Anybody with the 'moderate' capability can upload another user's avatar

  - this to ensures that inappropriate images can be removed.

  - there is no "delete avatar" function at this time, but an inappropriate image can be removed by uploading a 'safe' image (e.g. a blank 1x1 pixel image) to replace it (you could them manually set that user as a bozo to stop them re-uploading inappropriate images). Alternatively, you could set their avatar to an identicon (see below).

* Option to display a default avatar for users who do not upload their own.

* fel64's "Identicons" plugin gives users the option of displaying an identicon instead of uploading an image (becomes their 'default' avatar). http://bbpress.org/forums/topic/1027?replies=25#post-6759

Credit to fel64 for providing the bbPress interface for Identicons and Scott Sherrill-Mix for writing the Identicon code at http://scott.sherrillmix.com/blog/blogger/wp_identicon/ 

== Installation ==

UPGRADING?  If you are using a version older than 0.3 of this plugin you NEED to follow these instructions fully because the template functions are incompatible with older versions.  If you are upgrading from version 0.3 or later then of this plugin you can ignore the template instructions (steps 2 to 4), but do check the rest.

1. After activating the plugin go to the "Avatar upload" admin page and configure the options displayed there. At least make sure you have set the avatar upload directory to the location of your choice.

2. The avatar upload page should appear as a tab ("Avatar") on the user's Profile menu.  If you'd prefer the link to be elsewhere, insert the following "Upload Avatar" link wherever you wish:

    `<a href="<?php profile_tab_link($user->ID, 'avatar'); ?>"><?php _e("Upload Avatar"); ?></a>`

   Use the available `$user->ID` for the page you place the link on.

3. To display an uploaded avatar, insert the following template function.

   a) On the user's profile page (`profile.php` template).
      
      `<?php avatarupload_display($user->ID); ?>`

      This grabs the avatar info file directly from the current user's profile information.

   b) On each user's forum posts (`post.php` template)

      `<?php avatarupload_display(get_post_author_id()); ?>`

   You can include the avatar anywhere else you like, just be sure to have the user's ID available.

  c) If you just want the URI of the avatar (for your own plugins for example):

     `<?php avatarupload_get_avatar(ID); ?>`

     Where ID is a user ID. Returns false if no avatar exists for that user.

4. OPTIONAL: open up `my-templates/avatar.php` and edit the template if you wish, but be sure not to mess with the upload form.

5. Upload the files in the "additional-files" directory to the following locations.

   `avatars/`                    - directory to the location specified on the admin page, rename if neccesary

   `avatars/default.png`         - default avatar image into the directory created above.

   `avatar-upload.php`           - bbPress root directory.

   `my-templates/avatar.php`     - your `my-templates/my-template-name/` (or bb-templates/kakumei/) directory.

6. Upload the plugin files.

   `bb-avatar-upload.php`        - your `my-plugins/` directory (and activate it).

   `identicon.php`               - your `my-plugins/` directory (it is automatically included).

   `unsharpmask.php`             - your `my-plugins/` directory (it is automatically included).

That's it, the 'Avatar Upload' plugin should now be working.

== Configuration ==

The following options are configured on the 'Avatar Upload Settings' admin page.

* Avatar upload directory

* Max. allowed width

* Max. allowed height

* Max. allowed filesize

* Allow upload of GIFs, JPGs and/or PNGs

* Use default avatar or auto generated identicon

* Height/width of identicons (if used)

See 'Installation' instructions and admin page for more details.

== Frequently Asked Questions ==

= Are there security risks when allowing users to upload images to my server? =

The plugin checks the images upon upload to ensure that only gifs, jpegs/jpgs and pngs are allowed. It checks both the file extension (e.g. `.gif`) AND the mime-type (e.g. `image/gif`).

However, one can never 100% sure and there is always some security risks when allowing users to upload to your server. USE THIS PLUGIN AT YOUR OWN RISK!

= I get the following error (or similar): move_uploaded_file(/path/to/bbpress/avatars/user.jpg) [function.move-uploaded-file]: failed to open stream: Permission denied in /path/to/bbpress/avatar-upload.php on line XXX =

You need to set the file permissions (chmod) of the `avatars` folder to `666` to allow the plugin to write to the folder.  You can do this using SHH or alternatively (and more easily) many FTP applications allow permissions setting.  Please refer to your web host for their advice if you do not know how to do this.

= When I upload some JPEGs they either look rubbish or are blocks of plain colour =

[N.b. this can also apply to some PNG images, but I'm only going to talk about JPEGS.]

This is most likely a result of your web host providing a version of PHP that can not create 'truecolor' images. JPEGS are usually photographic images and therefore contain millions of colours, hence the need for truecolor. The plugin detects if truecolor is not an option and instead uses the same function as GIF (palette based) images, which can make some JPEGS look awful.

To get the best out of a JPEG image you really need <strong>PHP version >= 4.0.6 or PHP 5 and GD library GD 2.0.1 or later</strong>.

Alternatively, because this only happens when an image needs to be resized, you could inform your uses that they <strong>must</strong> make sure they avatar conforms to the maximum allowed dimensions <em>before</em> they upload it.

== Change Log ==

2007-07-23 Ver. 0.7   Added admin page for configuration, added an unsharp mask function, changed 
                      additional file layout to be more friendly to the Plugin Browser plugin.

2007-07-19 Ver. 0.6.2 Bug-fix. 'Upload Avatar' page wasn't displaying new avatar after upload.

2007-07-17 Ver. 0.6.1 Bug-fix. Reset generic avatar as default option and stopped identicon.php
                      from being loaded with plugin *every* time.

2007-07-16 Ver. 0.6   Integrated Identicons into the core plugin. Added Unix timestamp to filename
                      in DB (updated when user updates avatar) to combat browser caching problems.

2007-07-15 Ver. 0.5   added image resizing function, better mime-type checking and a couple of
                      performance improvements.

2007-06-10 Ver. 0.4.1 minor bug fixes (and made readme more readable in the plugin browser).

2007-06-02 Ver. 0.4   made config vars into a class, totally overhauled upload script (streamlined), 
                      amended readme instructions and fixed problem with pretty permalinks.

2007-05-02 Ver. 0.3   rewritten, config vars moved to plugin script, enabled default avatar, 
                      added profile tab and made it possible to use plugin with other plugins.

2007-04-17 Ver. 0.2   reduced DB calls, added filename checks (to stop things like 
                      "myavatar.exe.jpg").

2007-04-07 Ver. 0.1   released.