===Facebook Graph Connect ===
Tags:  facebook connect, connect, fbconnect, fb-connect, graph facebook, saran
Contributors: Saran
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: facebook connect

Users can Register or Login using facebook connect button.

== Description ==
Adds facebook connect features to bbpress, so users may Register or Login with facebook connect features.

1. Requests basic & extended permissions on fbConnect Button Click.
2. Creates new user or Link existing member of bbpress.
3. Retrives firstname, lastname, email address from facebook.
4. Sends a random password to new user email.
5. Auto log-in a linked facebook user.
6. Shows facebook profile picture as avatar in bbpress posts and in profile page.

DEMO WEBSITE :  http://www.aboutconsumer.com
 
== Installation ==

READ CAREFULLY * If you face any problems, please visit http://bbpress.org/plugins/topic/facebook-graph-connect/ and let me know. It will help me update the plugin.

a) This plugin requires CURL to be installed. Check your web host has php curl extension enabled.

*  1) COPY "facebook_graph_connect" folder to "root/bb-plugins" directory. ("root" folder is where your bbpress is located)

*  2) COPY "fb_connect.php" file from template directory to "root/bb-templates/YOUR-TEMPLATE".

* COPY "bb-fb-connect.php" to root directory "root/".

* IMPORTANT : Copy-paste code below in your templates, WHERE YOU WANT  facebook connect button to apprear,
		<?php fb_get_login_button(); ?>
  If you are using kakumei template, Edit "login-form.php" and paste code just before <input name="remember"

* In bb-template, edit "header.php" find "<html " and add line manually : xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml" 

* ACTIVATE plugin

* IN admin->settings, Click "Facebook connect" and enter facebook App id and App secret. Save Changes!

* Read "To Do" section below for additional notes.



b) OPTIONAL --You may want to show facebook profile images as avatars. But Steps Below are totally Optional.--

* In "bb-template" folder, inside "your template" folder, edit post.php, paste code below or replace existing avatar code.

	<?php get_fb_avatar(get_post_author_id()); ?>

* In same "bb-template" sub folder, edit profile.php, paste code below or replace existing code.
	<?php get_fb_avatar($user->ID,'large'); ?>

* That's it, your facebook users should able to choose not to use facebook image in profile edit page.

== To Do ==
* check for file duplicates before saving
* On activation, plugin creates a table called " bb_fbuser" in your database if table doesn't exist. import "mysql.sql" file in your phpmyadmin.
* Check In some custom templates, <?php do_action('bb_foot'); ?> code may be missing in footer.php template file. 
* If you face any problems, please visit http://bbpress.org/plugins/topic/facebook-graph-connect/ and let me know. It will help me update the plugin.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Change Log ==

* 0.0.1	first public beta release for review
* 0.0.2	public beta release for re-review, as readme text wasn't neat, needed some writing in plugin description.
* 0.0.3	Added Facebook profile Image as bbpress avatar for your facebook users.
* 0.0.4 	Added kakumei template edit instruction regarding fb-button in Readme.txt, Fixed footer credit alignment.
* 0.0.5 	Changed redirect locations to absolute paths, was redirecting users to unknown locations. Added a mysql file for those it didn't create "fbuser" table in database.
* 0.0.6    Fixed logout url. Removed Base64 footer text for better performance.
* 0.0.7	Altered table "fb_userid" to "bigint(20) unsigned" in db, this INT table column wasnt accepting new long fb user ids, causing plugin to fail. 