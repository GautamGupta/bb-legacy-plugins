=== Private Forums ===
Contributors: so1o
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=so1oonnet%40gmail%2ecom&item_name=Aditya%20Naik%20for%20bbPress%20Plugin%20Development&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: private, forums, hide
Requires at least: 0.74
Tested up to: 0.8.2.1
Stable tag: 5.0

Regulate Access to forums in bbPress

== Description ==

This plugin filters private forums from view when the user does not have appropriate 
role. The Access level can individually for each of the forums be set to following:
* Open to all 
* Registered Users
* Moderator 
* Administrator .

The plugin enables the administrator to set forum or forums as private from the 
administration menu. The Administrator can set the role to which the selected forums are
restricted to.

The administrator can choose how the forum handles the private forums. the 
forums can either be completely hidden or shown with a text prefix like 
&quot;[private]&quot;. the prefix is customizable through the options.

The admin can also select the text to be shown in case access is denied. The error message 
to be shown is parsed and the word 'login' is replaced by the link to login page.

The admin menu can accessed by Administrators from 
<br/><strong>Admin > Site Management > Private Forums</strong>

== Installation ==

1. Upload the file into /my-plugins/ directory 
1. If you don't have a /my-plugins/ directory in your bbpress installaltion, 
   create it on the same level as config.php.

== Screenshots ==

1. The main options is the one to choose the access levels to retrict to forums.

2. The error message shown when the user tries to access private resource is also 
customizable using the Error Options.

3. The admin menu is accessed through the Site Management tab

4. The privacy Options lets the administrator control the behavior of the forum 
to private forums.

== Version History ==

* 1.0 : 
  * Initial Release
* 1.1 :
  * bug fix for empty private forums
  * Added failsafe for installation.
* 2.0 : 
  * Added choice to hide private forums or show them with private prefix
  * Added selectable prefix text
  * Removed redundant forum\_access\_update\_option
* 2.1 : 
  * Created Common Submit for all options
* 3.0 : 
  * Fixed the submenu generation
  * Fixed Forum Filter
* 3.1 :
  * Fixed &lt;?
* 4.0 :
  * Added Restriction by User Role
  * Renamed Functions to be prefixed by private\_forums instead of forum\_access
  * Changed where the options are stored.
  * Added Upgrade Function
  * Options can now be set by role administrator
* 5.0 :	
  * Access Roles can be set for each forums now
  * Fixed the results of search
  * Fixed RSS 

== Frequently Asked Questions == 

None

