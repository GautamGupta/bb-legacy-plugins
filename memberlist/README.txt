Plugin Name: Memberlist
Plugin URI: http://faq.rayd.org/memberlist/
Description: Displays all active members
Author: Joshua Hutchins
Change Log: .73a - Added Active User Count on memberlist page
		 - Added support for multiple pages (default is 10 per page)
		 - Added better support for WPMU integration
  	    .73b - Added optional functionality to sort by post count
	 	 	- Requires Post Count plugin and uncommented post count code on memberlist.php
			- Get Post Count from here: http://faq.rayd.org/bbpress_postcount
		 - Fixed some strange behavior with page changing combined with ordering
		 - Added ability to change the number of users per page
	    .73c - Small change for servers that need rewrite rules in .htaccess
Author URI: http://ardentfrost.rayd.org/
Version: .73c


Included are 3 files: bb-memberlist.php which goes into the my-plugins directory, mlist.php which goes into the forums root directory, and memberlist.php which goes into my-templates (it MUST go into my-templates and not bb-templates)

Once those three files are in place, simply put the following wherever you want the link: 

<a href="<?php bb_memberlist_link(); ?>">Member List</a>

I put mine under the "Views" section

If you need rewrite rules in .htaccess, add the following line (if you don't know what that means, don't worry about it)

RewriteRule ^mlist/ /forums/mlist.php? [L,QSA]
