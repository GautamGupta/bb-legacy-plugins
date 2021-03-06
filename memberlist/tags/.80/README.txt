=== Memberlist ===
Contributors: Ardentfrost
Requires at least: .80
Tested up to: .80
Stable tag: .80

Displays all active members

== Description ==

Plugin Name: Memberlist
Plugin URI: http://faq.rayd.org/memberlist/
Description: Displays all active members
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .80

Change Log: 

<ul>
<li>.73a
<ul>
<li>Added Active User Count on memberlist page</li>
<li>Added support for multiple pages (default is 10 per page)</li>
<li>Added better support for WPMU integration</li>
</ul>
</li>
<li>.73b
<ul>
<li>Added optional functionality to sort by post count
<ul>
<li>Requires Post Count plugin and uncommented post count code on memberlist.php</li>
<li>Get Post Count from here: http://faq.rayd.org/bbpress_postcount</li>
</ul>
</li>
<li>Fixed some strange behavior with page changing combined with ordering</li>
<li>Added ability to change the number of users per page</li>
</ul>
</li>
<li>.73c
<ul>
<li>Small change for servers that need rewrite rules in .htaccess</li>
</ul>
</li>
</ul>

== Installation ==

Included are 3 files: bb-memberlist.php which goes into the my-plugins directory, mlist.php which goes into the forums root directory, and memberlist.php which goes into the template directory (whichever template you are using)

Once those three files are in place, simply put the following wherever you want the link: 

`<a href="<?php bb_memberlist_link(); ?>">Member List</a>`

I put mine under the "Views" section

If you need rewrite rules in .htaccess, add the following line (if you don't know what that means, don't worry about it)

RewriteRule ^mlist/ /forums/mlist.php? [L,QSA]
