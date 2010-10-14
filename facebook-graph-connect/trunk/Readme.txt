===Facebook Graph Connect ===
Tags:  facebook connect, connect, fbconnect, fb-connect, graph facebook, saran
Contributors: Saran
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: facebook connect

Users can Register or Login using facebook connect button.

== Description ==
Adds facebook connect features to bbpress, so users may Register or Login with facebook connect features.

1. Plugin Should request basic & extended permissions on fbConnect Button Click.
2. Plugin should create new user or Link existing member of bbpress.
3. Plugin should retrive firstname, lastname, email address from facebook.
4. Plugin should send a random password to new user email.
5. Plugin should auto login a linked facebook user.

DEMO WEBSITE :  http://www.aboutconsumer.com
 
== Installation ==

* This plugin requires CURL to be installed. Check your PHPINFO for a CURL section.

a) COPY "facebook_graph_connect" folder to "root/bb-plugins" directory.

b) COPY "fb_connect.php" file from template directory to "root/bb-templates/YOUR-TEMPLATE".

c) COPY "bb-fb-connect.php" to root directory "root/".

d) EDIT copy & paste this code : <?php fb_get_login_button();?> in templates, Where you want fb-button to apprear. 

e) ACTIVATE plugin

f) IN admin->settings, Click "Facebook connect" and enter facebook App id and App secret. Save Changes!


* --You may want to show facebook profile images as avatars. But Steps Below are totally Optional.--

a) in "bb-template" folder, inside "your template" folder, edit post.php, paste code below or replace existing avatar code.

	<?php get_fb_avatar(get_post_author_id()); ?>

b) In same "bb-template" sub folder, edit profile.php, paste code below or replace existing code.
	<?php get_fb_avatar($user->ID,'large'); ?>

c) That's it, your facebook users should able to choose not to use facebook image in profile edit page.


== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Changelog ==

* 0.0.1	first public beta release for review
* 0.0.2	public beta release for re-review, as readme text wasn't neat, needed some writing in plugin description.
* 0.0.3	Added Facebook profile Image as bbpress avatar for your facebook users.

== To Do ==

* check for file duplicates before saving

