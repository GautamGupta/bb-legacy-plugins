<?php
/*
Plugin Name: Social It
Plugin URI: http://www.gaut.am/bbpress/plugins/social-it
Description: Social It adds a (X)HTML compliant list of social bookmarking icons to topics, front page, tags, etc. See <a href="admin-base.php?plugin=socialit_settings_page">configuration panel</a> for more settings. This plugin is inspired from the <a href="http://sexybookmarks.net/">SexyBookmarks plugin for Wordpress</a>. This plugin is also compatible with <a href="http://bbpress.org/plugins/topic/support-forum/">Support Forum plugin</a>.
Version: 1.2
Author: Gautam
Author URI: http://gaut.am/

	Original Social It bbPress Plugin Copyright 2009 Gautam (email : admin@gaut.am) (website: http://gaut.am)
	Original SexyBookmarks Plugin Copyright 2009 Eight7Teen (email : josh@eight7teen.com), Norman Yung (www.robotwithaheart.com)

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
 Main PHP File for
 Social It plugin (for bbPress) by www.gaut.am
*/

// Create Text Domain For Translations
load_plugin_textdomain('socialit', '/my-plugins/social-it/languages/');

define('SOCIALIT_OPTIONS','Social-It');
define('SOCIALIT_vNum','1.2');
define('SOCIALIT_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/');
define('SI_BB_VER', (int) bb_get_option('version'));

//requires
require_once('functions.php'); //php functions file
require_once('bookmarks-data.php'); //bookmarks data file
require_once('mobile.php'); //mobile/bot check file

//load options
$socialit_plugopts = bb_get_option(SOCIALIT_OPTIONS);
if(!$socialit_plugopts){
	//add defaults to an array
	$socialit_plugopts = array(
		'reloption' => 'nofollow', // 'nofollow', or ''
		'targetopt' => '_blank', // '_blank' or '_self'
		'bgimg-yes' => 'yes', // 'yes' or blank
		'bgimg' => 'caring', // 'sexy', 'caring', 'wealth'
		'shorty' => 'e7t',
		'shortyapi' => '1', //1 or 0
		'topic' => '1',
		'bookmark' => array_keys($socialit_bookmarks_data),
		'xtrastyle' => '',
		'feed' => '0', // 1 or 0
		'expand' => '1',
		'autocenter' => '0',
		'ybuzzcat' => 'science',
		'ybuzzmed' => 'text',
		'twittcat' => 'Internet',
		'default_tags' => '',
		'warn-choice' => '',
		'sfpnonres' => 'yes',
		'sfpres' => 'yes',
		'sfpnonsup' => 'yes',
		'shorturls' => array(),
		'mobile-hide' => 'yes',
	);
	bb_update_option(SOCIALIT_OPTIONS, $socialit_plugopts);
}

//add actions/filters
add_action('bb_admin_menu_generator', 'socialit_menu_link', -998); //link in settings
add_action('bb_admin_head', 'socialit_admin'); //admin css
add_action('bb_head', 'socialit_public'); //public css
add_filter('post_text', 'socialit_insert_in_post', 997); //to insert social it automatically below the first post of every topic
?>