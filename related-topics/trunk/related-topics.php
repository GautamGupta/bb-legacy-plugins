<?php
/*
Plugin Name: Related Topics
Plugin URI: http://bbpress.org/plugins/topic/mini-stats
Description: Displays a list of related topics based on tags (and eventually keywords and manual selection)
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$related_topics['automatic_in_topicmeta']=true;  // true/false, use false for manual placement
$related_topics['topicmeta_position']=250; 	 // position in topicmeta items, high number = lower
$related_topics['minimum_tag_match']=1;	 // how many tags must match to be related
$related_topics['maximum_count']=5;		 // how many related items at most
$related_topics['label']=__("Related Topics:");
$related_topics['no_related']="<small>(".__("no related topics found").")</small>";	  // message to display when no matches found (or blank)

/*  stop editing here  */

add_action('related_topic','related_topics');
if ($related_topics['automatic_in_topicmeta']) {add_action('topicmeta','related_topics',$related_topics['topicmeta_position']);}

function related_topics($topic_id=0,$before="<li>",$after="</li>") {
global $related_topics, $topic;
// if (!bb_current_user_can('administrate')) {return;}	// debug
if (empty($topic_id)) {$topic_id=$topic->topic_id;}
$tags=get_topic_tags($topic_id);
if (empty($tags)) {return;}

foreach ($tags as $tag) {
	$tag_topics=bb_get_tagged_topic_ids($tag->tag_id);
	foreach ($tag_topics as $related_topic) {@$tag_count[$related_topic]++;}
}

unset($tag_count[$topic_id]);	// remove self
if (!empty($tag_count)) {
	ksort($tag_count,SORT_NUMERIC);	// newest topics first by high id
	arsort($tag_count,SORT_NUMERIC); 	 // highest tag match count first
	if (reset($tag_count)<$related_topics['minimum_tag_match']) {return;}

	foreach ($tag_count as $related_topic=>$score) {
		if ($score<$related_topics['minimum_tag_match']) {break;}
		if (@++$count>$related_topics['maximum_count']) {break;}
		$final[]=$related_topic;		
	}

	$output=$before.$related_topics['label']."<ol class='related_topics' style='margin:0.15em 0 0 1.5em;'>"; 
	$links=related_topics_get_links($final);
	foreach ($final as $related_topic) {
		if (!empty($links[$related_topic])) {$output.="<li>$links[$related_topic]</li>";}  //  ($tag_count[$related_topic] - $related_topic)  // debug
	} 	
	$output.="</ol>".$after;
	echo $output;
} elseif ($related_topics['no_related']) {echo $before.$related_topics['no_related'].$after;}

}

function related_topics_get_links($list) {
global $bbdb;	
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 ");
	$topics=$bbdb->get_results("SELECT topic_id,topic_slug,topic_title FROM $bbdb->topics $where AND topic_id IN (".implode(',',$list).")");
	$links=array();
	$rewrite = bb_get_option( 'mod_rewrite' );
	if ( $rewrite ) {if ( $rewrite === 'slugs' ) {$column = 'topic_slug';} else {$column = 'topic_id';}}
	foreach ($topics as $topic) {
		if ( $rewrite ) {
			$link = bb_get_option('uri') . "topic/" . $topic->$column;
		} else {					
			$link=add_query_arg('id',$topic->topic_id, bb_get_option('uri').'topic.php');
		}	
		$links[$topic->topic_id]="<a href='".attribute_escape($link)."'>$topic->topic_title</a>";
	}
return $links;	
}

?>