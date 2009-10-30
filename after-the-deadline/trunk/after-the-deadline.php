<?php
/*
Plugin Name: After the Deadline
Plugin URI: http://www.gaut.am/bbpress/plugins/after-the-deadline/
Description: After the Deadline Plugin checks spelling, style, and grammar in your bbPress forum posts.
Version: 1.1
Author: Gautam Gupta
Author URI: http://gaut.am/

	Original After the Deadline - Spell Checker bbPress Plugin Copyright 2009 Gautam (email: admin@gaut.am) (website: http://gaut.am)

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
 After the Deadline - Spell Checker Plugin
 (for bbPress) by www.gaut.am
*/

// Create Text Domain For Translations
//load_plugin_textdomain('after-the-deadline', '/my-plugins/after-the-deadline/languages/');

//defines
define('ATD_VER', '1.1.1-beta'); //version number of the plugin
define('ATD_PLUGPATH', bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/'); //full url path to the plugin

function atd_button(){ //inserts AtD spell check button when called
	if(bb_is_user_logged_in()){ //only when the user is logged in
		echo '<span class="atd_container"><img src="'.ATD_PLUGPATH.'images/atdbuttontr.gif"><a href="#postform" id="checkLink">Check Spelling</a></span>';
	}
}

function atd_css_js(){ //enqueues & prints script and style
	echo "\n\n".'<!-- Start Of Code Generated By After the Deadline Plugin By www.gaut.am -->'."\n";
	if(bb_is_user_logged_in()){ //only when the user is logged in
		if(function_exists('wp_register_script') && function_exists('wp_print_scripts') && function_exists('wp_register_style') && function_exists('wp_print_styles')){ //bb 1.0+
			wp_register_script('after-the-deadline-js', ATD_PLUGPATH."scripts/atd.js",  array('jquery'), ATD_VER);
			wp_print_scripts('after-the-deadline-js');
			wp_register_style('after-the-deadline', ATD_PLUGPATH.'css/atd.css', false, ATD_VER, 'all');
			wp_print_styles('after-the-deadline');
		}elseif(function_exists('bb_register_script') && function_exists('bb_print_scripts') && function_exists('bb_deregister_script')){ //bb below 1.0
			bb_deregister_script('jquery');
			bb_register_script('jquery', ATD_PLUGPATH.'scripts/jquery.js', false, '1.3.2');
			bb_register_script('after-the-deadline-js', ATD_PLUGPATH."scripts/atd.js",  array('jquery'), ATD_VER);
			bb_print_scripts('after-the-deadline-js');
			echo "<link rel='stylesheet' id='after-the-deadline-css' href='".ATD_PLUGPATH."css/atd.css' type='text/css' media='all' />";
		}else{ //we cant do anything, rather than echoing the text
			echo "<script type='text/javascript' src='".ATD_PLUGPATH."scripts/jquery.js'></script>
			<script type='text/javascript' src='".ATD_PLUGPATH."scripts/atd.js'></script>
			<link rel='stylesheet' id='after-the-deadline-css' href='".ATD_PLUGPATH."css/atd.css' type='text/css' media='all' />";
		}
	}
	echo '<!-- End Of Code Generated By After the Deadline Plugin By www.gaut.am -->'."\n\n";
}

//actions/filters/hooks
add_action('post_form_pre_post', 'atd_button', 9999999); //to insert AtD automatically above the post textbox, should be after anything
add_action('bb_head', 'atd_css_js', 2); //enqueues & prints scripts and styles, only when the user is logged in
?>