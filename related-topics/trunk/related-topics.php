<?php
/*
Plugin Name: Related Topics
Plugin URI: http://bbpress.org/plugins/topic/related-topics
Description: Displays a list of related topics based on tags and keywords. No template edits required.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

$related_topics['automatic_in_topicmeta']=true;  // true/false, use false for manual placement
$related_topics['topicmeta_position']=250; 	 // position in topicmeta items, high number = lower
$related_topics['show_on_new']=true;		// show instant display of related topics during new topic post

$related_topics['maximum_items']=5;		 // display how many related items at most
$related_topics['cache_time']=600;		 // in seconds - refreshes immediately when tags added/deleted - cache obeys hidden forums
$related_topics['label']=__("Related Topics:");
$related_topics['no_related']="<small>(<em>".__("no related topics found")."</em>)</small>";	  // message to display when no matches found (or blank)

$related_topics['use_tags']=true;			 // use tags to find related?  true/false
$related_topics['minimum_tag_match']=1;	 // how many tags must match to be related
$related_topics['tag_weight']=5;			 // higher number means higher importance

$related_topics['use_titles']=true;			 // use titles to find related?  true/false
$related_topics['minimum_title_match']=1;	 // how many title words must match to be related
$related_topics['title_weight']=1;			 // higher number means higher importance - keep this lower than the tag weight
$related_topics['title_word_length']=4; 		 // how long must a word in the title be for comparison

$related_topics['exclude_tags']="";	// comma separated list of id numbers, via admin menu coming later
$related_topics['exclude_forums']="";	// comma separated list of id numbers, via admin menu coming later 
$related_topics['exclude_topics']="";	// comma separated list of id numbers, via admin menu coming later 

$related_topics['stop_words']= 		// stop words are removed when scanning titles (don't bother with words below 4 letters)
						// find some non-english words here: http://www.ranks.nl/resources/stopwords.html						 
"before these being they only between this both those other through came over into under come said could same about very"
."just after want should like does since make also each many else some well still might another from were such more what take"
."most when than much where that must which while their never have them because will then been here with without there would your";

/*  stop editing here  */

$related_topics['debug']=false;		// debug mode

if (empty($related_topics['exclude_tags'])) {$related_topics['exclude_tags']="0";}
if (empty($related_topics['exclude_forums'])) {$related_topics['exclude_forums']="0";}
if (empty($related_topics['exclude_topics'])) {$related_topics['exclude_topics']="0";}

if ($related_topics['automatic_in_topicmeta']) {add_action('topicmeta','related_topics',$related_topics['topicmeta_position']);}
add_action('post_form','related_topics_head');
add_action('related_topic','related_topics');
add_action('bb_tag_added', 'related_topics_tag_change',10,3);
add_action('bb_pre_tag_removed', 'related_topics_tag_change',10,3);

if (isset($_GET['related-topics'])) {		// let's try bypassing all of bbpress and do this at load time (but will show hidden-forums!)
	header("Content-Type: application/x-javascript");		// process "ajax-ish" request	
	echo 'RTbox.innerHTML="';
	related_topics(0,bb_sanitize_with_dashes(substr($_GET['related-topics'],0,128)),'','');
	echo '";';
	exit;	
}

function related_topics_head() {
global $related_topics, $bb_current_user; 
if (empty($related_topics['show_on_new']) || empty($bb_current_user->ID) || is_topic()) {return;}
$minimum=$related_topics['minimum_title_match'];
print  <<<RTHEAD
<script type='text/javascript' defer='defer'>
if (window.attachEvent) {window.attachEvent('onload', related_topics_init);} 
else if (window.addEventListener) {window.addEventListener('load', related_topics_init, false);} 
else {document.addEventListener('load', related_topics_init, false);}

function related_topics_init() {
	if (!document.forms || !document.forms['postform'] || !document.forms['postform'].topic) {return;}
	RTscript_src=location.pathname+(location.search ? location.search+"&" : "?")+"related-topics=";
	RTbox=document.createElement("div");
	RTbox.style.margin="0 0 4px 2px";
	RTbox.id="related_topics"; RTbox.last=""; RTbox.search="";
	RTtitle=document.getElementById('topic');  // document.forms['postform'].topic ?? stopped working ??
	RTtitle.setAttribute("autocomplete","off");	
	RTtitle.onfocus=related_topics;
	RTtitle.onkeyup=related_topics_key;
	RTtitle.onblur=related_topics_off;	
    	var RTparent=RTtitle; while (RTparent.parentNode.tagName=="LABEL" || RTparent.parentNode.tagName=="P") {RTparent=RTparent.parentNode;}
    	RTparent.parentNode.insertBefore(RTbox,RTparent.nextSibling);			 
}
function related_topics() {
	try {clearTimeout(RTbox.timer);} catch(e) {}
	RTbox.timer=setTimeout("related_topics();",8000);	
	if (RTbox.last==RTtitle.value) {return;} RTbox.last=RTtitle.value;	
	var i,j=0,search,terms=[], words=RTtitle.value.toLowerCase().split(" ");
	for (i=0;i<words.length;i++) {if (words[i].length>3) {terms[j]=words[i]; j++;}}	
	if (terms.length<$minimum) {return;}	// note exteral string 
	search=terms.join("-");
	if (RTbox.search==search) {return;} RTbox.search=search;	
	if (typeof RTscript != "undefined") {document.body.removeChild(RTscript);}
	RTscript = document.createElement("script");
	RTscript.src = RTscript_src+search;	// +Math.floor(Math.random()*99999999)+"&related-topics="
	RTscript.type = "text/javascript";
	RTscript.charset = "utf-8";
	// RTbox.innerHTML="searching...";   //  more distracting than helpful
	document.body.appendChild(RTscript);
}
function related_topics_key(e) {			
	if ((window.event ? window.event.keyCode : e.which)==32) {related_topics();}
}
function related_topics_off() {
	related_topics();	// one last check on blur
	try {clearTimeout(RTbox.timer);} catch(e) {}
}
</script>
RTHEAD;

}

function related_topics($topic_id=0,$slug="",$before="<li>",$after="</li>") {
global $related_topics, $tags, $topic, $bbdb;
if ($related_topics['debug'] && !bb_current_user_can('administrate')) {return;}	// debug
if (empty($topic_id)) {$topic_id=$topic->topic_id;}
if (empty($slug)) {$slug=$topic->topic_slug;}

if ($topic_id==$topic->topic_id && (empty($topic->related_topics) || empty($topic->related_topics->time) || time()-$topic->related_topics->time>$related_topics['cache_time'])) {

if ($topic_id==$topic->topic_id) {$topic_tags=$tags;} else {$topic_tags=get_topic_tags($topic_id);}   // speedup with global copy

if (!is_array($related_topics['stop_words'])) {$related_topics['stop_words']=array_flip(explode(" ",$related_topics['stop_words']));}

if (!empty($slug) && empty($topic_tags)) {		// turn slug words when no real tag present into tags for tag compare  
	(array) $words=explode('-',$slug); $words="'".implode("','",$words)."'";
	if (defined('BACKPRESS_PATH')) {	 // 1.0 query needs to be revisited because term_id is not quite tag_id,  has to check taxonomy_id
		$query="SELECT term_id AS tag_id FROM $bbdb->terms WHERE slug IN ($words) AND term_id NOT IN (".$related_topics['exclude_tags'].") ";
	} else {
		$query="SELECT tag_id FROM $bbdb->tags WHERE tag IN ($words) AND tag_id NOT IN (".$related_topics['exclude_tags'].") ";
	}
	$topic_tags=$bbdb->get_results($query);		 //  (maybe merge with real tags?)
}

if ($related_topics['use_tags'] && !empty($topic_tags)) {
// foreach ($topic_tags as $tag) {$tag_topics=bb_get_tagged_topic_ids($tag->tag_id); foreach ($tag_topics as $related_topic) {@$match_count[$related_topic]++;}}  // old slow method, cross compatible
foreach ($topic_tags as $tag) {$ids[$tag->tag_id]=$tag->tag_id;}   // 1.0 converts term_id/taxonomy_id into tag_id ?!
if (!empty($ids)) {	
$ids=implode(',',$ids);
$having="HAVING count>=".$related_topics['minimum_tag_match'];
if (defined('BACKPRESS_PATH')) {
	$query="SELECT object_id as topic_id,COUNT(*) as count FROM $bbdb->term_relationships WHERE term_taxonomy_id IN ($ids) AND term_taxonomy_id NOT IN (".$related_topics['exclude_tags'].") AND object_id NOT IN (".$related_topics['exclude_topics'].") GROUP BY object_id $having ORDER BY count DESC";
} else {
	$query="SELECT topic_id,COUNT(*) as count FROM $bbdb->tagged WHERE tag_id IN ($ids) AND tag_id NOT IN (".$related_topics['exclude_tags'].") AND topic_id NOT IN (".$related_topics['exclude_topics'].")  GROUP BY topic_id $having ORDER BY count DESC";
}
$tag_topics=$bbdb->get_results($query);
}
if (!empty($tag_topics) && reset($tag_topics)>=$related_topics['minimum_tag_match']) {
foreach ($tag_topics as $related_topic) {@$match_count[$related_topic->topic_id]=$match_count[$related_topic->topic_id]+$related_topic->count*$related_topics['tag_weight'];}
}
}

if ($related_topics['use_titles']) {

(array) $words=explode('-',$slug); $word0=""; $union=""; $count=0; 
foreach ($words as $word) {if (strlen($word)>=$related_topics['title_word_length'] && !isset($related_topics['stop_words'][$word])) {if (++$count>1) {$union.=" UNION SELECT '".$word."' ";} else {$word0=$word;}}}
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
} else {$match_count=array();}

if ($topic_id==$topic->topic_id) {	// save/update cache
	$topic->related_topics->cache=$match_count;
	$topic->related_topics->time=time();
	bb_update_topicmeta($topic_id,'related_topics',$topic->related_topics);
}

} else { 	// cache check	
	$match_count=$topic->related_topics->cache;
	if ($related_topics['debug']) {echo "<div style='margin:0.15em 0 -1.5em 10em'>(cached)</div>";}
}

if (!empty($match_count)) {
	foreach ($match_count as $related_topic=>$score) {$final[]=$related_topic;	}
	$links=related_topics_get_links($final);	
	if (!empty($links)) {
	$output=$before.$related_topics['label']."<ol class='related_topics' style='list-style-position:inside;'>"; $count=0; $debug="";
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
	$bb_uri = bb_get_option('uri');
	
	foreach ($topics as $topic) {
		if ( $rewrite ) {
			$link = $bb_uri . "topic/" . $topic->$column;
		} else {					
			$link=add_query_arg('id',$topic->topic_id, $bb_uri.'topic.php');
		}	
		$links[$topic->topic_id]="<a href='".attribute_escape($link)."'>".wp_specialchars( $topic->topic_title, ENT_QUOTES)."</a>";
	}
	}
	
return $links;	
}

function related_topics_tag_change($tag_id=0, $user_id=0, $topic_id=0) {	// clears cache when tag changes
if (!empty($topic_id)) {
global $related_topics;
$topic_data=bb_get_topicmeta( $topic_id, 'related_topics');
if (!empty($topic_data) && !empty($topic_data->time)) {
	unset($topic_data->time); 
	bb_update_topicmeta($topic_id,'related_topics',$topic_data);
}
}
}

function related_topics_updatemeta($topic_id,$meta_key='related_topics',$data) {	// trying to skip bbpress's wasteful queries - not in use yet
if (empty($topic_id)) {return;}
global $bbdb;
	$data =addslashes(serialize($data));
	if (defined('BACKPRESS_PATH'))  {	// bbPress 1.0	
	$bbdb->query("INSERT INTO $bbdb->meta (object_id, object_type, meta_key, meta_value) VALUES ('$topic_id', 'bb_topic', '$meta_key', '$data') ON DUPLICATE KEY UPDATE `meta_value` = VALUES(`meta_value`)");
	} else {		// bbPress 0.7 - 0.9	
	echo "INSERT INTO $bbdb->topicmeta (topic_id, meta_key, meta_value) VALUES ('$topic_id', '$meta_key', '$data') ON DUPLICATE KEY UPDATE `meta_value` = VALUES(`meta_value`)";
	$bbdb->query("INSERT INTO $bbdb->topicmeta (topic_id, meta_key, meta_value) VALUES ('$topic_id', '$meta_key', '$data') ON DUPLICATE KEY UPDATE `meta_value` = VALUES(`meta_value`)");	
	} 
}
?>