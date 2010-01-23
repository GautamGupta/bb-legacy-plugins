<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.4
Author: Gautam Gupta
Author URI: http://gaut.am/

	Original After the Deadline Plugin Copyright 2010 Gautam (email: admin@gaut.am) (website: http://gaut.am)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/*
 * Main PHP File for
 * After the Deadline Plugin
 * (for bbPress) by www.gaut.am
 */

/* Create Text Domain For Translations */
load_plugin_textdomain( 'after-the-deadline', dirname(__FILE__) . '/languages' );

/*
 * Defines
 */

/* IF statement introduced in v1.3
 * If you have problems (the directory of the plugin could not be matched), then define ATD_PLUGPATH in bb-config.php file to the full URL path to the plugin directory
 * Eg. - http://www.example-domain.tld/forums/my-plugins/after-the-deadline/
 */
if( !defined( 'ATD_PLUGPATH' ) ){
	/* Define ATD_PLUGPATH if value is not set - Full URL path to the plugin */
	define( 'ATD_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/' );
}
/* Version */
define( 'ATD_VER', '1.4.1-dev' );
/* AtD Option Name */
define( 'ATD_OPTIONS','After-the-Deadline' );

/* Set the Options if they are not set */
$atd_plugopts = bb_get_option(ATD_OPTIONS);
if( !is_array( $atd_plugopts ) ){
	/* Add defaults to an array */
	$atd_plugopts = array(
		'key' => ''
	);
	/* Update the options */
	bb_update_option( ATD_OPTIONS, $atd_plugopts );
}

/* Load admin.php file if it is the admin area */
if( bb_is_admin() ){
	require_once('includes/admin.php');
}else{ /* Else load public.php file if it is the public area */
	require_once('includes/public.php');
}
