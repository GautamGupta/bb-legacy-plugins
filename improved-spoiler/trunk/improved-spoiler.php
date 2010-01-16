<?php
/*
Plugin Name: Improved Spoiler
Plugin URI: http://epicallabs.com/bbpress/plugins/spoiler
Description: Turns text between [spoiler][/spoiler] into a real spoiler as well as <spoiler></spoiler>. Original by: ipstenu
Author: Nerieru
Author URI: http://epicallabs.com
Version: 0.1
*/
// You can add more tags here
function add_spoiler_tags( $tags ) {
        $tags['spoiler'] = array();
        return $tags;
}
function spoiler_embed_callback( $content ){
        return preg_replace_callback("|<spoiler(=[>]+)?>(.*?)</spoiler>|", 'bb_spoiler', $content);
}
function bb_spoiler( $matches ){
		$title = $matches[1];
        $text = $matches[2];
        $text = str_replace("<spoiler>", "", $text);
        $text = str_replace("</spoiler>", "", $text);
        $title = substr($title,1);
        return "<span class='spoilertitle'>Spoiler for $title</span>: <span class='spoilersmall'>(hover to see)</span><br /><span class=\"spoiler\">$text</span>";
}
function spoiler_embed_bbcode_callback($content){
      return preg_replace_callback("|\[spoiler(=[^\]]+)?\](.*?)\[/spoiler\]|", 'bbcode_spoiler', $content);
}
function bbcode_spoiler( $matches )
{
	   $title = $matches[1];
       $text = $matches[2];
       $text = str_replace("[spoiler]", "", $text);
       $text = str_replace("[/spoiler]", "", $text);
       $title = substr($title,1);
       return "<span class='spoilertitle'>Spoiler for $title</span>: <span class='spoilersmall'>(hover to see)</span><br /><span class=\"spoiler\">$text</span>";
}
add_filter('post_text', 'spoiler_embed_bbcode_callback', 1); // This replaces [spoiler] with the span
add_filter('post_text', 'spoiler_embed_callback', 1); // This replaces <spoiler> with <span> stuff
add_filter( 'bb_allowed_tags', 'add_spoiler_tags' ); // This adds spoilers to Allowed markup list
?>