<?php
/*
Plugin Name: Topic Icons
Plugin URI: http://bbpress.org/plugins/topic/110
Description: Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$topic_icons['automatic']=true;

$topic_icons['rules']=array(			// triggers seperated by vertical pipe |, plurals ending in 's' are also matched	
	"idea" => "idea|tip|trick",		// if you don't want this feature, just remove these entries or comment them out with /*    */
	"theme" => "theme|template",
	"plugin" => "plugin|extension",
	"alert" => "test|testing|attention|warning|alert|error",
	"wordpress" => "wordpress",
	"bbpress" => "bbpress"
);

$topic_icons['forums']=array(	// keyword group to show based on forum number as fallback if none of the rules above are matched
	"1" => "bbpress",		// to get a list of your forums by number when this plugin is active: add   ?forumlist to your url
	"2" => "plugin",		// if you don't want this feature, just remove these entries or comment them out with /*    */
	"3" => "plugin",	
 	"5" => "theme",		// example where order doesn't matter
 	"4" => "wordpress"
);					

$topic_icons['graphics']=array(		// graphics to assign to keyword groups (now you must specify extension)
	"alert" => "exclamation.png",
	"idea" => "lightbulb.png",
	"theme" => "layout_content.png",
	"plugin" => "plugin.png",
	"wordpress" => "wordpress.png",
	"bbpress" => "bbpress.png",
	"sticky" => "star.png",
	"closed" => "lock.png",
	"poll" => "chart_bar.png",
	"resolved" => "flag_green.png",
	"unresolved" => "flag_red.png"
);

$topic_icons['style']=".topic_icons {width:16px; height:16px; text-decoration:none; border: 0; padding: 1px; vertical-align: text-top; background:none;}";

/*	stop editing here	*/

$topic_icons['url']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/icons/'; 

add_action('bb_init','topic_icons_init');

if ($topic_icons['style']) {add_action('bb_head', 'topic_icons_add_css');}	

if ($topic_icons['automatic']) { // in_array(bb_get_location(),array('front-page','forum-page', 'tag-page','search-page','favorites-page','profile-page','view-page'))) {
	$bb_location=bb_get_location();
	if ($bb_location!='feed-page') {
		add_filter( 'forum_name', 'forum_icon_automatic',9,2);
		if ($bb_location!='topic-page') {add_filter( 'topic_title', 'topic_icon_automatic',9,2);}
	}
}

function topic_icons_init() { 
global $bb_current_user;
	if ((isset($_GET['listforums']) || isset($_GET['forumlist'])) && 'keymaster'==@reset($bb_current_user->roles)) {echo "<h2>Forum List</h2>"; foreach (get_forums() as $forum) {echo "$forum->forum_id -> $forum->forum_name <br><br>";} exit();}
}

function topic_icon_automatic($title,$id) {if (topic_icon(true,$id)) {echo " ";} return $title;}
function forum_icon_automatic($name,$id) {if (forum_icon(true,$id)) {echo " ";} return $name;}

function topic_icon($automatic=false,$id=0) {
global $topic_icons,$topic;		// print " <!-- "; print_r($topic); print " --> ";	// diagnostic
	if ($id && (!isset($topic->topic_id) || $topic->topic_id!=$id) ) {$topic=get_topic($id);}	// handles searches albeit with too many queries - needs fix
	elseif (!$id && isset($topic->topic_id)) {$id=$topic->topic_id;}
	if ($automatic==false && $topic_icons['automatic']) {
		remove_filter( 'forum_name', 'forum_icon_automatic',9); 
		remove_filter( 'topic_title', 'topic_icon_automatic',9);
	}
	if ($topic->topic_sticky) {echo topic_icons_graphic("sticky");}
	elseif (!$topic->topic_open) {echo topic_icons_graphic("closed");}
	elseif (isset($topic->poll_options)) {echo topic_icons_graphic("poll");}
	elseif ($temp=topic_icons_keyword($topic->topic_title)) {echo $temp;}
	elseif (isset($topic->topic_resolved) && $topic->topic_resolved!="mu") {
		if ($topic->topic_resolved=="yes") {echo topic_icons_graphic("resolved");}
		if ($topic->topic_resolved=="no") {echo topic_icons_graphic("unresolved");}
	}
	else {	// no topic icon found, check for forum icon fallback
		if ($topic->topic_id==$id) {$forum_id=$topic->forum_id;} else {$temp=get_topic($id); $forum_id=$temp->forum_id;}
		if ($temp=topic_icons_forum($forum_id))  {echo $temp;} 
		else {return false;} // nope, nothing found
	}
				
return true;	
}

function forum_icon($automatic=false,$id=0) {
global $topic_icons,$forum;
	if (!$id && isset($forum->forum_id)) {$id=$forum->forum_id;}
	if ($automatic==false && $topic_icons['automatic']) {
		remove_filter( 'forum_name', 'forum_icon_automatic',9); 
		remove_filter( 'topic_title', 'topic_icon_automatic',9);
	}
	if ($temp=topic_icons_forum($id))  {echo $temp;}
	elseif ($temp=topic_icons_keyword($forum->forum_name)) {echo $temp;}	
	else {return false;}	// nope, nothing found
return true;	
}	

function topic_icons_keyword($item) {
global $topic_icons; $temp="";
	if (isset($topic_icons['rules']) && is_array($topic_icons['rules'])) {
		foreach ($topic_icons['rules'] as $keyword=>$rule) {
			if (eregi("(^|[^\w])(".$rule.")(s?)($|[^\w])",$item)) {$temp=topic_icons_graphic($keyword); break;}
		}
	}
return $temp;	
}

function topic_icons_forum($item) {
global $topic_icons; $temp="";
	if (isset($topic_icons['forums'][$item])) {$temp=topic_icons_graphic($topic_icons['forums'][$item]);}
return $temp;	
}

function topic_icons_graphic($keyword) {
global $topic_icons;
return "<img class='topic_icons' title='$keyword' alt='$keyword' src='".$topic_icons['url'].$topic_icons['graphics'][$keyword]."' />";
}

function topic_icons_add_css() { global $topic_icons;  echo '<style type="text/css">'.$topic_icons['style'].'</style>';} // inject css
?>