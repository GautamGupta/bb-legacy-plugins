<?php
/*
Plugin Name: Spoiler Tags
Plugin URI: http://bbpress.org/
Description: Allows <spoiler></spoiler> tags to be posted in your forums.
Author: Mika A. Epstein
Author URI: http://ipstenu.org/
Version: 0.1
*/

// You can add more tags here
function add_spoiler_tags( $tags ) {
        $tags['spoiler'] = array();
        return $tags;
}

function spoiler_embed_callback( $content )
{
        return preg_replace_callback("|<spoiler>(.*?)</spoiler>|", 'bb_spoiler', $content);
}


function bb_spoiler( $matches )
{
        $text = $matches[0];
        $text = str_replace("<spoiler>", "", $text);
        $text = str_replace("</spoiler>", "", $text);
        return "<span class=\"spoiler\">$text</span>";
}

add_filter('post_text', 'spoiler_embed_callback', 1); // This replaces <spoiler> with <span> stuff
add_filter( 'bb_allowed_tags', 'add_spoiler_tags' ); // This adds spoilers to Allowed markup list


?>