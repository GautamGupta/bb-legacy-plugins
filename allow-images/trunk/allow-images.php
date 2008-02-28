<?php
/*
Plugin Name: Allow Images
Plugin URI: http://bbpress.org/#
Description: Allows <img /> tags to be posted in your forums.  The image must be a png, gif or jpeg.
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.8
*/

// You can add more tags here
function allow_images_allowed_tags( $tags ) {
	$tags['img'] = array('src' => array(), 'title' => array(), 'alt' => array());
	return $tags;
}

function allow_images( $text ) {
	if ( preg_match_all('/<img(.+?)src=("|\')(.+?)\\2(.*?)>/i', $text, $matches, PREG_SET_ORDER ) )
		foreach( $matches as $match )
			if (
				preg_match('/src=/i', $match[4]) // multiple src = someone's trying to cheat
			   ||
				!in_array(substr($match[3], -4), array('.png', '.jpg', '.gif'))  // only match .jpg, .gif, .png
			   &&
				'.jpeg' != substr($match[3], -5) // and .jpeg
			)
				$text = str_replace($match[0], '', $text);
	return $text;
}

add_filter( 'pre_post', 'allow_images', 52 );
add_filter( 'bb_allowed_tags', 'allow_images_allowed_tags' );

?>
