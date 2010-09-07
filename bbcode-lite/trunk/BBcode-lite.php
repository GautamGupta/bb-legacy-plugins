<?php
/*
Plugin Name: BBcode Lite
Plugin URI: http://bbpress.org/plugins/topic/93
Description: A lightweight alternative to allow BBcode on your forum.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 1.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

// to force allowing images without extra plugins like "allow images" uncomment the following line:
// $bbcode_lite['complex']['img'] = array('img','src');

add_filter('bb_init','bbcode_lite_init',255);
add_filter('post_text', 'bbcode_lite',7);	//  to store bbcode as html permanently,  change 'post_text' to 'pre_post' which is faster but harder for users to edit
add_filter('pm_text', 'bbcode_lite',7);	// support private message plugin
add_filter( 'bb_allowed_tags', 'bbcode_lite_extra_tags' );	 // unfortunately to make bbcode fast we need to allow some extra tags like "font"

function bbcode_lite_init() {		// speed up by defining variables only once - also allows us to deactivate tags not allowed
global $bbcode_lite;
$tags = bb_allowed_tags();
$bbcode_lite['wrap'] = array('color' => array('font','color'),'size' => array('font','size'),'url' => array('a','href'), 'list' => array('ol','type'));	
$bbcode_lite['simple'] = array('pre'=>'pre','b' => 'strong','i' => 'em','u' => 'u','center'=>'center','quote' => 'blockquote','strike' => 'strike','s' => 'strike','list' => 'ul', 'code' => 'code');
$bbcode_lite['complex']['url'] = array('a','href'); if (isset($tags['img'])) {$bbcode_lite['complex']['img'] = array('img','src');}
$bbcode_lite['complex']['quote'] = array('blockquote','cite','a','href'); 
}

function bbcode_lite_extra_tags( $tags ) {   // add some extra html tags to support - though in theory it bypasses all restrictions when using post_text hook
$tags=array_merge(array("BBcode"=>array()),$tags);	 // trick bbPress to show BBcode in the list of allowed tags and give users a clue
$new_tags=array('font','strike','center','u','blockquote','pre','hr'); foreach ($new_tags as $tag) {$tags[$tag]=array();}	// add a few allowed tags for more robust bbCode support
return $tags;
}

function bbcode_lite ($text) {
global $bbcode_lite;
$counter=0;  // filter out all backtick code first
if (preg_match_all("|\<code\>(.*?)\<\/code\>|sim", $text, $backticks)) {foreach ($backticks[0] as $backtick) {++$counter; $text=str_replace($backtick,"_bbcode_lite_".$counter."_",$text);}}

$text=preg_replace('/(\<br \/\>|[\s])*?\[(\*|li)\](.+?)(\<br \/\>|[\s])*?(\[\/(\*|li)\](\<br \/\>|[\s])*?|(?=(\[(\*|li)\](\<br \/\>|[\s])*?|\[\/list\])))/sim','<li>$3</li>',$text); // * = li, a very special case since they may not be closed
foreach($bbcode_lite['wrap'] as $bbcode=>$html){$text = preg_replace('/\['.$bbcode.'=([^ \'\"]{0,512}?)\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">$2</'.$html[0].'>',$text);}
foreach($bbcode_lite['simple'] as $bbcode=>$html){$text = preg_replace('/\['.$bbcode.'\](.+?)\[\/'.$bbcode.'\]/is','<'.$html.'>$1</'.$html.'>',$text);}
foreach($bbcode_lite['complex'] as $bbcode=>$html){	 	
	if($bbcode=='url') {$text = preg_replace('/\['.$bbcode.'\]([^ \'\"]{0,512}?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">$1</'.$html[0].'>',$text);}
	if($bbcode=='quote') {
	$text = preg_replace('/\['.$bbcode.'=["\']?(.+?)["\']?\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].'><'.$html[1].'>$1&nbsp;&raquo;&nbsp;</'.$html[1].'>$2</'.$html[0].'>',$text);
	// todo: needs callback for post link, post age
	// $text = preg_replace('/\['.$bbcode.'=["\']?(.+?)["\']?[\:\;]([0-9]+?)\](.+?)\[\/'.$bbcode.'\]/is','<'.$html[0].'><'.$html[1].'>$1&nbsp;&raquo;&nbsp;</'.$html[1].'>$2</'.$html[0].'>',$text);
	} else {$text = preg_replace('/\['.$bbcode.'\]([^ \'\"]{0,512}?)\[\/'.$bbcode.'\]/is','<'.$html[0].' '.$html[1].'="$1">',$text);} 
}

if ($counter) {$counter=0; foreach ($backticks[0] as $backtick)  {++$counter; $text=str_replace("_bbcode_lite_".$counter."_",$backtick,$text);}}	// undo backticks

return $text;
}

?>