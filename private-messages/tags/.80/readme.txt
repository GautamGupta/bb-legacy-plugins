=== Private Messaging ===
Contributors: Ardentfrost
Requires at least: .80
Tested up to: .80
Stable tag: .80

Integrates Private Messages into BBPress

== Description ==

Plugin Name: BBPress Private Messaging
Plugin URI: http://faq.rayd.org/bbpress_private_message/
Description: Integrates Private Messages into BBPress
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: 0.80

== Installation ==

Unzip the files into your forums root directory and navigate to your forums url /pm.php

NOTE: You will have to move the files in "template folder" to whatever template you are using

********************************************************************************************
Usage:

In order to get new message notification on a page, put the following code where you want it:

	<?php if (bb_current_user_can('write_posts')) : ?>
		<?php pm_fp_link(); ?>
	<?php endif; ?>

I have mine between <li> tags under the "Views" section on front-page.php

********************************************************************************************

In order to link directly to a form to message a specific user, use the following code:

	`<?php pm_user_link(get_post_author_id()); ?>`

I have mine in post.php at the end of line 8 or 10 depending on your plugins (this causes 
each post in the forums to have a link to PM that user).

********************************************************************************************

If you have either Post Count or Avatar plugins installed (made by Josh Hutchins), then you 
can go to /my-templates/postmsg.php and uncomment either or both of the lines pertaining to 
those plugins.

********************************************************************************************

If you have emoticons plugin by Hiromasa, you must open the file containing his plugin and 
add the following line at the very end (before the ?> )

bb_add_filter('pm_text', array(&$bbemoticons, 'convert_smilies'));

That will enable smilies in private messages.  A filter like this will need to be added to 
any filter (in or out of plugins) that affects post text in order for that filter to be 
applied to pm text.

********************************************************************************************
List of files and where they go:

pm-post.php - forums root
pm.php - forums root
message.php - forums root
bb-privatemessage.php - /my-plugins
message-form.php - /bb-templates/*your template folder*
pm-form.php - /bb-templates/*your template folder*
pm-user-form.php - /bb-templates/*your template folder*
postmsg.php - /bb-templates/*your template folder*
privatemessages.php - /bb-templates/*your template folder*
newmail.png - /bb-templates/*your template folder*

********************************************************************************************

For reports of bugs or suggestions, please visit http://www.rayd.org/forums/