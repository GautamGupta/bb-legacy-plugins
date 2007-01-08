=== Plugin Name ===
Contributors: so1o
Tags: private, forums, hide
Requires at least: 0.74
Tested up to: 0.74
Stable tag: 2.1

Give forum specific moderator privileges 

== Description ==

This plugin filters private forums from view when the user is not logged in. 
To access the private forums the user will have to login.

The plugin enables to user to set forum or forums as private from the 
administration menu. The admin can also select the text to be shown in case 
access is denied.

The administrator can choose how the forum handles the private forums. the 
forums can either be completely hidden or shown with a text prefix like 
‘private’. the prefix is customizable through the options.

The admin menu can accessed by keymaster from 
Admin > User > Forum Moderators

== Installation ==

1. Upload the file into /my-plugins/ directory 
1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

== Frequently Asked Questions ==

== Screenshots ==

1. There are several options provided by the Plugin. The main options is the one 
to choose the forums which you want to make private. The privacy Options lets 
the administrator control the behavior of the forum to private forums.

2. The error message shown when the user tries to access private resource is also 
customizable using the Error Options.

== Version History ==

Version History:
1.0 : Initial Release
1.1 : bug fix for empty private forums
	: Added failsafe for installation.
2.0 : Added choice to hide private forums or show them with private prefix
	: Added selectable prefix text
	: Removed redundant forum_access_update_option
2.1	: Created Common Submit for all options
