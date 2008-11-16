<?php
/*
Plugin Name: Related Topics
Plugin URI: http://bbpress.org/plugins/topic/related-topics
Description: Displays a list of related topics based on tags and keywords. No template edits required.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.3

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

$related_topics['automatic_in_topicmeta']=true;  // true/false, use false for manual placement
$related_topics['topicmeta_position']=250; 	 // position in topicmeta items, high number = lower

$related_topics['use_tags']=true;			 // use tags to find related?  true/false
$related_topics['minimum_tag_match']=1;	 // how many tags must match to be related
$related_topics['tag_weight']=5;			 // higher number means higher importance

$related_topics['use_titles']=true;			 // use titles to find related?  true/false
$related_topics['minimum_title_match']=1;	 // how many title words must match to be related
$related_topics['title_weight']=1;			 // higher number means higher importance - keep this lower than the tag weight
$related_topics['title_word_length']=5; 		 // how long must a word in the title be for comparison

$related_topics['maximum_items']=5;		 // display how many related items at most
$related_topics['label']=__("Related Topics:");
$related_topics['no_related']="<small>(<em>".__("no related topics found")."</em>)</small>";	  // message to display when no matches found (or blank)

$related_topics['exclude_tags']="";	// comma separated list of id numbers, via admin menu coming later
$related_topics['exclude_forums']="";	// comma separated list of id numbers, via admin menu coming later 
$related_topics['exclude_topics']="";	// comma separated list of id numbers, via admin menu coming later 

/*  stop editing here  */

$related_topics['debug']=false;		// debug mode

if (empty($related_topics['exclude_tags'])) {$related_topics['exclude_tags']="0";}
if (empty($related_topics['exclude_forums'])) {$related_topics['exclude_forums']="0";}
if (empty($related_topics['exclude_topics'])) {$related_topics['exclude_topics']="0";}

add_action('related_topic','related_topics');
if ($related_topics['automatic_in_topicmeta']) {add_action('topicmeta','related_topics',$related_topics['topicmeta_position']);}

function related_topics($topic_id=0,$before="<li>",$after="</li>") {
global $related_topics, $tags, $topic, $bbdb;
if ($related_topics['debug'] && !bb_current_user_can('administrate')) {return;}	// debug
if (empty($topic_id)) {$topic_id=$topic->topic_id;}
if ($topic_id==$topic->topic_id) {$topic_tags=$tags;} else {$topic_tags=get_topic_tags($topic_id);}   // speedup with global copy

if ($related_topics['use_tags'] && !empty($topic_tags)) {
// foreach ($topic_tags as $tag) {$tag_topics=bb_get_tagged_topic_ids($tag->tag_id); foreach ($tag_topics as $related_topic) {@$match_count[$related_topic]++;}}  // old slow method, cross compatible
foreach ($topic_tags as $tag) {$ids[$tag->tag_id]=$tag->tag_id;} 
if (!empty($ids)) {	
$ids=implode(',',$ids);
$having="HAVING count>=".$related_topics['minimum_tag_match'];
if (defined('BACKPRESS_PATH')) {$query="SELECT object_id as topic_id,COUNT(*) as count FROM $bbdb->term_relationships WHERE term_taxonomy_id IN ($ids) AND term_taxonomy_id NOT IN (".$related_topics['exclude_tags'].") AND object_id NOT IN (".$related_topics['exclude_topics'].") GROUP BY object_id $having ORDER BY count DESC";}
else {$query="SELECT topic_id,COUNT(*) as count FROM $bbdb->tagged WHERE tag_id IN ($ids) AND tag_id NOT IN (".$related_topics['exclude_tags'].") AND topic_id NOT IN (".$related_topics['exclude_topics'].")  GROUP BY topic_id $having ORDER BY count DESC";}
$tag_topics=$bbdb->get_results($query);
}
if (!empty($tag_topics) && reset($tag_topics)>=$related_topics['minimum_tag_match']) {
foreach ($tag_topics as $related_topic) {@$match_count[$related_topic->topic_id]=$match_count[$related_topic->topic_id]+$related_topic->count*$related_topics['tag_weight'];}
}
}

if ($related_topics['use_titles']) {
(array) $words=explode('-',$topic->topic_slug); $word0=""; $union=""; $count=0; 
foreach ($words as $word) {if (strlen($word)>=$related_topics['title_word_length']) {if (++$count>1) {$union.=" UNION SELECT '".$word."' ";} else {$word0=$word;}}}
if ($word0) {
$where="";
$having="HAVING count>=".$related_topics['minimum_title_match'];
$query="SELECT topic_id, COUNT(*) as count FROM $bbdb->topics AS s1 JOIN (SELECT '$word0' AS wd $union) AS v1 ON s1.topic_slug LIKE CONCAT('%',v1.wd,'%') $where GROUP BY s1.topic_id $having ORDER BY count DESC  LIMIT 100";
$word_topics=$bbdb->get_results($query);
}
if (!empty($word_topics) && reset($word_topics)>=$related_topics['minimum_title_match']) {
foreach ($word_topics as $related_topic) {@$match_count[$related_topic->topic_id]=$match_count[$related_topic->topic_id]+$related_topic->count*$related_topics['title_weight'];}
}
}

unset($match_count[$topic_id]);	// remove self
if (!empty($match_count)) {
	 // ksort($match_count,SORT_NUMERIC);	// newest topics first by high id
	 arsort($match_count,SORT_NUMERIC); 	 // highest tag match count first    
    	$vals = array_count_values($match_count);    $i = 0;	// insanity sort because php moves array items with the same value in ksort :-(
    	foreach ($vals AS $val=>$num) {
       	$first=array(); $x=1; if ($i)  {foreach ($match_count as $key=>$value) {$first[$key]=$value; unset($match_count[$key]); if (++$x>$i) {break;}}} // $first = array_splice($match_count,0,$i);
       	$tmp=array(); $x=1; if ($num)  {foreach ($match_count as $key=>$value) {$tmp[$key]=$value; unset($match_count[$key]); if (++$x>$num) {break;}}} //  $tmp = array_splice($match_count,0,$num);
        	krsort($tmp,SORT_NUMERIC);                
        	$match_count=$first+$tmp+$match_count;        	        	
        	$i = $i + $num;
    	}

	foreach ($match_count as $related_topic=>$score) {$final[]=$related_topic;	}
	$links=related_topics_get_links($final);	
	if (!empty($links)) {
	$output=$before.$related_topics['label']."<ol class='related_topics' style='margin:0.15em 0 0 1.5em;'>"; $count=0; $debug="";
	foreach ($final as $related_topic) {
		if ($related_topics['debug']) {$debug=" ($match_count[$related_topic] - $related_topic)";}
		if (!empty($links[$related_topic])) {$output.="<li>$links[$related_topic]$debug</li>";} 
		if (@++$count>$related_topics['maximum_items']) {break;}		
	} 	
	$output.="</ol>".$after;
	echo $output;
	} else {$no_related=true;}	// empty links
} else {$no_related=true;}	// empty tag count 

if (!empty($no_related) && !empty($related_topics['no_related'])) {echo $before.$related_topics['no_related'].$after;}
}

function related_topics_get_links($list) {
global $related_topics, $bbdb;
	
	$list=implode(',',$list); $links=array();
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0");
	$where.= " AND forum_id NOT IN (".$related_topics['exclude_forums'].") AND topic_id NOT IN (".$related_topics['exclude_topics'].")";
	$where.=" AND topic_id IN ($list) ORDER BY FIND_IN_SET(topic_id,'$list')";
	$query="SELECT topic_id,topic_slug,topic_title FROM $bbdb->topics $where LIMIT ".$related_topics['maximum_items'];
	$topics=$bbdb->get_results($query);
	
	if (!empty($topics)) {		
	$rewrite = bb_get_option('mod_rewrite'); if ( $rewrite ) {if ( $rewrite === 'slugs' ) {$column = 'topic_slug';} else {$column = 'topic_id';}}
	
	foreach ($topics as $topic) {
		if ( $rewrite ) {
			$link = bb_get_option('uri') . "topic/" . $topic->$column;
		} else {					
			$link=add_query_arg('id',$topic->topic_id, bb_get_option('uri').'topic.php');
		}	
		$links[$topic->topic_id]="<a href='".attribute_escape($link)."'>$topic->topic_title</a>";
	}
	}
	
return $links;	
}

?>