<?php
/*
Plugin Name: Live Comment Preview
Plugin URI: http://klr20mg.com/bbpresslive-comment-preview-plugin/
Description: Provide users with a live comment preview before submit. show it in the same page using Ajax
Author: Enrique Chavez aka Tmeister
Author URI: http://www.klr20mg.com/
Version: 0.1
*/

/* *
* Plugin path
* */
$live_comment_preview_path = bb_get_option('uri')."my-plugins/live_comment_preview";

/* *
* Header stuff
* */
function live_comment_preview_header()
{
	global $live_comment_preview_path;
	$head  = "<link rel='stylesheet' href='".$live_comment_preview_path."/style.css' type='text/css' media='all' />\n";
	$head .= "<script language='javascript' type='text/javascript' src='".$live_comment_preview_path."/live_comment_preview.js'></script>\n";
	echo $head;
}

/* *
* Main function 
* */
function add_live_comment_preview($label)
{
	global $live_comment_preview_path;
	if( bb_is_user_logged_in() ){
		echo "<div id='live_comment_preview_main_content'></div>
			<div id='live_comment_preview_button_div'>
		  	<input type='submit' name='live_comment_preview_submit' id='live_comment_preview_submit' value='".$label."'  onclick='sendPostToPreview(\"".$live_comment_preview_path."\" )' />
		  </div>";
	  }
}

/* *
* Add header content
* */
add_action('bb_head', 'live_comment_preview_header');
?>