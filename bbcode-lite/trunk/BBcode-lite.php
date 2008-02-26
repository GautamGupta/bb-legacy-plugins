<?php
/*
Plugin Name: BBcode Lite
Plugin URI: http://bbpress.org/plugins/
Description: A lightweight alternative to allow BBcode on your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 1.01

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

add_filter('post_text', 'bbcode_lite',7);	//  to store bbcode as html permanently,  change 'post_text' to 'pre_post' which is faster but harder for users to edit
add_filter( 'bb_allowed_tags', 'bbcode_lite_extra_tags' );	 // unfortunately to make bbcode fast we need to allow some extra tags like "font"

function bbcode_lite ($text) {
// echo "before";print_r($text);

$counter=0;  // filter out all backtick code first
if (preg_match_all("|\<code\>(.*?)\<\/code\>|sim", $text, $backticks)) {foreach ($backticks[0] as $backtick) {++$counter; $text=str_replace($backtick,"_bbcode_lite_".$counter."_",$text);}}

$text = preg_replace('/\[\*\](.+?)(\[\/\*\]|(?=(\[\*\]|\[\/list\])))/sim','<li>$1</li>',$text);	// * = li, a very special case since they may not be closed

$wrap = array('color' => array('font','color'),'size' => array('font','size'),'url' => array('a','href'), 'list' => array('ol','type'));	
foreach($wrap as $bbcode=>$html){$text = preg_replace('/\['.$bbcode.'=(.+?)\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">$2</'.$html[0].'>',$text);}

$simple = array('b' => 'strong','i' => 'em','u' => 'u','center'=>'center','quote' => 'blockquote','strike' => 'strike','s' => 'strike','list' => 'ul', 'code' => 'code');
foreach($simple as $bbcode=>$html){$text = preg_replace('/\['.$bbcode.'\](.+?)\[\/'.$bbcode.'\]/is','<'.$html.'>$1</'.$html.'>',$text);}

$complex = array('url' => array('a','href'),'img' => array('img','src'));
foreach($complex as $bbcode=>$html){	 
	if($bbcode!='url') {$text = preg_replace('/\['.$bbcode.'\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">',$text);} 
	else {$text = preg_replace('/\['.$bbcode.'\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">$1</'.$html[0].'>',$text);}	
}

if ($counter) {$counter=0; foreach ($backticks[0] as $backtick)  {++$counter; $text=str_replace("_bbcode_lite_".$counter."_",$backtick,$text);}}	// undo backticks

// echo "after";print_r($text);

return $text;
}

function bbcode_lite_extra_tags( $tags ) {
$tags=array_merge(array("BBcode"=>array()),$tags);	 // trick bbPress to show BBcode in the list of allowed tags and give users a clue
$new_tags=array('font','strike','center','u','blockquote','pre','hr'); foreach ($new_tags as $tag) {$tags[$tag]=array();}	// add a few allowed tags for more robust bbCode support
return $tags;
}


?>