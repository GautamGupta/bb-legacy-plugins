<?php
/*
Plugin Name: Random Topic
Description:  Selects a topic at random (within specified settings) so users can discover popular topics they've missed previously.
Plugin URI:  http://bbpress.org/plugins/topic/random-topic
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1
*/

$random_topic['include']=""; 			// forums to ONLY include, leave blank to include all but excluded forums
$random_topic['exclude']="524,595,951";		// forums to exclude from selection (hidden forums are automatically included)
$random_topic['max_age']=365;			// maximum age in days a topic should be to be included (use 0 to disable)
$random_topic['last_reply']=45;			// maximum age of the last reply in days
$random_topic['minimum_posts']=1;		// minimum number of posts required for topic to be included
$random_topic['top']=1000;				// pool size for random selection, top 1000 topics by number of posts
$random_topic['view']=true;			// add to view list  true/false

/* 	 stop editing here 	  */

if (isset($_REQUEST['random-topic']) || isset($_REQUEST['random_topic'])) {add_action('bb_send_headers','random_topic',1);}
if ($random_topic['view']) {
	add_action( 'bb_custom_view', 'random_topics_view',301);
	bb_register_view("random-topics","Random Topics",array('started' => '>0','append_meta'=>false,'sticky'=>false,'topic_status'=>'all','order_by'=>1,'per_page'=>1));	
}

function random_topic() {
	$random=random_topics(1);
	if (empty($random) || !is_array($random))  {$uri=bb_get_option('uri');} 
	else {$uri=get_topic_link(reset($random));}
	wp_redirect($uri); 
}

function random_topics_view($view) {
	if ($view!='random-topics')  {return;}
	global $bbdb, $topics, $view_count, $page;
	$limit = bb_get_option('page_topics');
	$random = random_topics($limit);	
	$view_count = count($random);
	shuffle($random);
	$random=implode(',',$random);
	$topics = $bbdb->get_results("SELECT * from $bbdb->topics WHERE topic_id IN ($random) ORDER BY find_in_set(topic_id, '$random')");
	$topics = bb_append_meta( $topics, 'topic' );		
}

function random_topics($limit=1) {
global $random_topic, $bbdb; $join="";
$user=bb_get_current_user();
$where="WHERE topics.topic_status=0 AND topics.topic_posts>".$random_topic['minimum_posts']." ";
$where = apply_filters('get_latest_topics_where',$where);
if (!empty($random_topic['include'])) {
	if (!is_array($random_topic['include'])) {(array) $random_topic['include']=explode(',',$random_topic['include']);}
	$where.=" AND topics.forum_id IN (".implode(',',$random_topic['include']).") ";
}
if (!empty($random_topic['exclude'])) {
	if (!is_array($random_topic['exclude'])) {(array) $random_topic['exclude']=explode(',',$random_topic['exclude']);}
	$where.=" AND topics.forum_id NOT IN (".implode(',',$random_topic['exclude']).") ";
}
if (!empty($random_topic['max_age'])) {
	$where.=" AND topics.topic_start_time>'".gmdate('Y-m-d H:i:s',time()-$random_topic['max_age']*60*60*24)."' ";
}
if (!empty($random_topic['last_reply'])) {
	$where.=" AND topics.topic_time>'".gmdate('Y-m-d H:i:s',time()-$random_topic['last_reply']*60*60*24)."' ";
}
if (!empty($user->ID)) {
	$join=" LEFT JOIN $bbdb->posts as posts ON posts.topic_id=topics.topic_id ";
	$where.=" AND posts.poster_id != $user->ID ";
	if (!empty($user->favorites)) {$favorites=trim($user->favorites,", "); if (!empty($favorites)) {$where.=" AND  topics.topic_id NOT IN ($favorites) ";}}
	if (!empty($user->up_read_topics)) {$favorites=trim($user->up_read_topics,", "); if (!empty($up_read_topics)) {$where.=" AND  topics.topic_id NOT IN ($up_read_topics) ";}}
}
$query="SELECT topic_id FROM (SELECT DISTINCT topics.topic_id FROM $bbdb->topics as topics $join $where ORDER BY topics.topic_posts DESC LIMIT ".$random_topic['top'].") as random ORDER BY RAND() LIMIT $limit";
$results=$bbdb->get_col($query); 
return $results;
}
?>