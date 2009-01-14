<?php
/*
Plugin Name: Hot Topic
Plugin URI: http://bbpress.org/plugins/topic/hot-topic
Description: Shows icon next to topic title to indicate hotness or popularity based on number of posts and topic age.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

//  I intend to make this more sophisticated eventually and actually look at number of recent posts in the past hours/days by number of users

$hot_topic['days']=2;			// number of days after which topic is no longer hot if no more replies

$hot_topic['posts']=10;		// mininum number of posts for a topic to be considered hot

$hot_topic['position']="right";	// left = left side,  right = right side

$hot_topic['style']=".hot_topic {width:16px; height:16px; text-decoration:none; border:0; padding:1px; vertical-align: text-top; background:none;}\n";

$hot_topic['url']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/'; 

$hot_topic['indicator']="<img class='hot_topic' src='".$hot_topic['url']."hot-topic.png' />";

/*	stop editing here	*/

if ($hot_topic['style']) {add_action('bb_head', 'hot_topic_add_css');}	
if (!is_bb_feed()  && !is_topic()) {add_filter( 'topic_title', 'hot_topic',200,2);}

function hot_topic_add_css() { global $hot_topic;  echo '<style type="text/css">'.$hot_topic['style'].'</style>';} // inject css

function hot_topic($title,$id) {
global $hot_topic,$topic; $is_hot=false;

if (intval($topic->topic_posts)>=$hot_topic['posts']) {
$days=floor((time()-strtotime($topic->topic_time. ' +0000'))/86400);
if ($hot_topic['days']>=$days) {
	$is_hot=true;
}
}

if ($is_hot) {
	if ($hot_topic['position']=="left") {$title=$hot_topic['indicator']." ".$title;}
	if ($hot_topic['position']=="right") {$title=$title." ".$hot_topic['indicator'];}
}
				
return $title;
}

?>