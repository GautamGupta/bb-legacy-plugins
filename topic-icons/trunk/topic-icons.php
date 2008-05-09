<?php
/*
Plugin Name: Topic Icons
Plugin URI: http://bbpress.org/plugins/topic/
Description: Adds icons next to your topic (and forum) titles automatically based on keywords or special topic status such as sticky, support question, has poll, etc.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$topic_icons['automatic']=true;

$topic_icons['rules']=array(						// triggers seperated by vertical pipe |, plurals ending in 's' are also matched
	"alert" => "test|testing|attention|warning|alert|error",
	"idea" => "idea|tip|trick",
	"theme" => "theme|template",
	"plugin" => "plugin|extension",
	"wordpress" => "wordpress",
	"bbpress" => "bbpress"
);

$topic_icons['graphics']=array(
"alert" => "exclamation",
"idea" => "lightbulb",
"theme" => "layout_content",
"plugin" => "plugin",
"wordpress" => "wordpress",
"bbpress" => "bbpress",
"sticky" => "star",
"closed" => "lock",
"poll" => "chart_bar",
"resolved" => "flag_green",
"unresolved" => "flag_red"
);

$topic_icons['style']=".topic_icons {width:16px; height:16px; text-decoration:none; border: 0; padding: 1px; vertical-align: text-bottom;}";

/*	stop editing here	*/

$topic_icons['url']=bb_get_option('uri').trim(str_replace(BBPATH,'',dirname(__FILE__)),' /\\').'/icons/';   ;	// bb_get_active_theme_uri()."images/icons/";

if ($topic_icons['style']) {add_action('bb_head', 'topic_icons_add_css');}	

if ($topic_icons['automatic']) { // in_array(bb_get_location(),array('front-page','forum-page', 'tag-page','search-page','favorites-page','profile-page','view-page'))) {
	$bb_location=bb_get_location();
	if ($bb_location!='feed-page') {
	add_filter( 'forum_name', 'forum_icon_automatic',9);
	if ($bb_location!='topic-page') {add_filter( 'topic_title', 'topic_icon_automatic',9);}
	}
}

function forum_icon_automatic($name) {if (forum_icon(true)) {echo " ";} return $name;}
function topic_icon_automatic($title) {if (topic_icon(true)) {echo " ";} return $title;}

function topic_icon($automatic=false) {
global $topic_icons,$topic;		// print " <!-- "; print_r($topic); print " --> ";	// diagnostic
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
	else {return false;}
return true;	
}

function forum_icon($automatic=false) {
global $topic_icons,$forum;
	if ($automatic==false && $topic_icons['automatic']) {
		remove_filter( 'forum_name', 'forum_icon_automatic',9); 
		remove_filter( 'topic_title', 'topic_icon_automatic',9);
	}
	if ($temp=topic_icons_keyword($forum->forum_name)) {echo $temp;}	
	else {return false;}
return true;	
}	

function topic_icons_keyword($item) {
global $topic_icons; $temp="";
	foreach ($topic_icons['rules'] as $keyword=>$rule) {
		if (eregi("(^|[^\w])(".$rule.")(s?)($|[^\w])",$item)) {$temp=topic_icons_graphic($keyword); break;}
	}
return $temp;	
}

function topic_icons_graphic($keyword) {
global $topic_icons;
return "<img class='topic_icons' title='$keyword' alt='$keyword' src='".$topic_icons['url'].$topic_icons['graphics'][$keyword].".png'>";
}

function topic_icons_add_css() { global $topic_icons;  echo '<style type="text/css">'.$topic_icons['style'].'</style>';} // inject css
?>