=== Campaign Monitor Sync ===
Contributors: Maria Cheung
Tags: user, registration, export, campaign monitor, sync
Requires at least: 1.0.2
Tested up to: 1.0.2
Stable tag: 1.0

== Description ==
When a new user registers, they are given the option to join your Campaign Monitor mailing list. The user can subscribe and unsubscribe to the mailing list on their Edit Profile page.  

= Administration =
A new Campaign Monitor Sync admin page, under the Users section allows the admin to provide the Campaign Monitor API key for their Campaign Monitor account. They must also select the mailing list that they want new users to be added to. The admin can also turn new user subscriptions on/off.

= Option =
The user can subscribe or unsubscribe from the mailing list on their Edit Profile page.

== Installation ==

1. Upload the file into /my-plugins/ directory 
1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

== Usage ==
Once the plugin is activated, you can use it in your other plugins or theme functions.php file. 

To subscribe a person to your mailing list:
`<?php if (function_exists('campaign_monitor_sync_bb_add_user_to_list')) {
	campaign_monitor_sync_bb_add_user_to_list($user_email, $user_name); 	//$user_name is optional
} ?>`
To unsubscribe a person from your mailing list:
`<?php if (function_exists('campaign_monitor_sync_bb_remove_user_from_list')) {
	campaign_monitor_sync_bb_remove_user_from_list($user_email);
} ?>`

== Frequently Asked Questions ==
