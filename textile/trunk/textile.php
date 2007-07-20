<?php
/*
Plugin Name: Textile
Version: 0.1
Plugin URI: http://egypt.urnash.com/
Description: This is a quick and dirty wrapper for <a href=http://textile.thresholdstate.com/>Textile</a>.
Author: Egypt Urnash
Author URI: http://egypt.urnash.com/
*/

// You should be able to update to the latest version of Textile by grabbing it from
// http://textile.thresholdstate.com/ and replacing the provided copy of classTextile.php.
// Presently this ships with Textile 2.0.0.

require('textile/classTextile.php');

/* Thoroughly disengage bbpress' existing input filters - all html is just thrown away by Textile. */
// I am not entirely sure these are the Correct filters to toss. Documentation is sparse.

remove_filter('pre_post', 'bb_encode_bad');
//remove_filter('pre_post', 'bb_code_trick');
remove_filter('pre_post', 'balanceTags');
remove_filter('pre_post', 'stripslashes');
remove_filter('pre_post', 'bb_filter_kses');
remove_filter('pre_post', 'addslashes');

/* And drop textile in as the output filter. */
/* Sluggish on initial page load, not so sluggish once cacheing gets going worth a damn... */
// I'm probably missing a few output filters it should be on.

add_filter('post_text','tex_do_textile');
add_filter('get_allowed_markup', 'tex_allowed_markup');

// override bbpress' quick ref string with this.
// need to figure out how to kill the "put code in `backticks`" string.
function tex_allowed_markup() {
	return " <em>_i_</em>  *<b>b</b>*  \"linktext\":http://blah  !imageurl!  *bullet list  and more...";
}

function tex_do_textile($text) {
	$textile = new Textile;

	return $textile->TextileThis($text);  
}
?>
