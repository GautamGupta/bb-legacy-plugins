<?php
/*
Plugin Name: My Views module - Statistics
Description: This plugin is part of the My Views plugin. It adds forum statistics to list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.2
*/ 

//  if (function_exists('get_view_count')) :       // requires  bb-topic-views plugin   // this needs to check the db meta directly as the other plugin may not have loaded

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = array('append_meta'=>false,'sticky'=>false);	// attempt to short-circuit bb_query     	
    	bb_register_view("statistics","Forum Statistics",$query);

} else {		// Build 214-875	(0.8.2.1)

function my_views_statistics_filter($passthrough ) {
	global $views;
	$views["statistics"] = "Forum Statistics";
	return $passthrough;
}
add_filter('bb_views', 'my_views_statistics_filter');
}

function my_views_statistics_view($view) {
	if ($view=="stats" || $view=="statistics") :	
		bb_send_headers();
		bb_get_header();
		my_views_header(1);
		my_views_statistics();
		my_views_footer();
		bb_get_footer();
		exit();
	endif;
} 
add_action( 'bb_custom_view', 'my_views_statistics_view' );

function my_views_statistics() {
global $bbdb;

$topiclimit=15;  // top topics 10,15,20 etc.
$userlimit=10;  // top users 10,15,20 etc.

// SELECT count(*) as posts, count(distinct topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` WHERE post_status=0
// SELECT count(*) as posts, count(distinct t1.topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` as t1  LEFT JOIN `bb_topics` as t2 ON t1.topic_id=t2.topic_id WHERE post_status=0 AND topic_status=0

$results=$bbdb->get_results("SELECT COUNT(*) as forums, SUM(posts)  as posts, SUM(topics) as topics FROM $bbdb->forums");
$my_views_statistics["total_forums"]=$results[0]->forums;
$my_views_statistics["total_posts"]=$results[0]->posts;
$my_views_statistics["total_topics"]=$results[0]->topics;

$my_views_statistics["total_members"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->users");
$my_views_statistics["total_days_old"]=round((time()-strtotime($bbdb->get_var("SELECT topic_start_time FROM $bbdb->topics ORDER BY topic_start_time LIMIT 1"))) / (3600 * 24),2);

if (bb_get_option('bb_db_version')>1600) { // bbPress 1.0 vs 0.9
$my_views_statistics["total_topic_views"]= $bbdb->get_var("SELECT SUM(meta_value) FROM $bbdb->meta LEFT JOIN $bbdb->topics ON $bbdb->meta.object_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->meta.meta_key='views' AND $bbdb->meta.object_type='bb_topic' ");
$my_views_statistics["total_tags"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->terms");
} else {	 // 0.9
$my_views_statistics["total_topic_views"]= $bbdb->get_var("SELECT SUM(meta_value) FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views' ");
$my_views_statistics["total_tags"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->tags");
}

$my_views_statistics["average_registrations"]=$my_views_statistics["total_members"]/$my_views_statistics["total_days_old"];
$my_views_statistics["average_posts"]=$my_views_statistics["total_posts"]/$my_views_statistics["total_days_old"];
$my_views_statistics["average_topics"]=$my_views_statistics["total_topics"]/$my_views_statistics["total_days_old"];
$my_views_statistics["average_topic_views"]=$my_views_statistics["total_topic_views"]/$my_views_statistics["total_days_old"];

$my_views_statistics["oldest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users ORDER BY user_registered ASC LIMIT $userlimit");
$my_views_statistics["newest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users ORDER BY user_registered DESC LIMIT $userlimit");

$my_views_statistics["top_posters"] = $bbdb->get_results("SELECT DISTINCT poster_id, COUNT(poster_id) AS post_count FROM $bbdb->posts WHERE post_status != 1 GROUP BY poster_id ORDER BY post_count DESC LIMIT $userlimit");
$my_views_statistics["top_topic_starters"]=$bbdb->get_results("SELECT DISTINCT topic_poster, topic_poster_name, COUNT(topic_id) AS post_count FROM $bbdb->topics WHERE topic_status=0 GROUP BY topic_poster_name ORDER BY post_count DESC LIMIT $userlimit");

$topics=$bbdb->get_results("SELECT * FROM $bbdb->topics WHERE topic_status=0 ORDER BY topic_posts DESC LIMIT $topiclimit"); 	// topic_id,topic_title,topic_posts
$my_views_statistics["top_topics_by_posts"] = bb_append_meta( $topics, 'topic' );
	
if ($my_views_statistics["total_topic_views"]) {
	if (bb_get_option('bb_db_version')>1600) { // bbPress 1.0 vs 0.9
	$most_views = $bbdb->get_results("SELECT object_id as topic_id FROM $bbdb->meta WHERE meta_key='views' AND object_type='bb_topic' ORDER BY cast(meta_value as UNSIGNED) DESC LIMIT $topiclimit");
	} else {
	$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC LIMIT $topiclimit");
	}
	foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} $ids = join(',', array_keys($trans));
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 AND topic_id IN ($ids) ");
	$topics ="SELECT * FROM $bbdb->topics $where ORDER BY FIELD(topic_id, $ids)"; // topic_id,topic_title
	$topics = $bbdb->get_results($topics);
$my_views_statistics["top_topics_by_views"] = bb_append_meta( $topics, 'topic' );
} else {$my_views_statistics["top_topics_by_views"] = array();}

?>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Totals</th><th>#</th></tr>
</thead>
<tbody>
<?php $totals=array("members","posts","topics","topic views","tags","forums","days old");
foreach ($totals as $total) {
$item=$my_views_statistics["total_".str_replace(" ","_",$total)];
echo  "<tr".get_alt_class('plugin', $class)."><td> Total ".ucwords($total).":</td>
	<td class=num>".bb_number_format_i18n($item,((int) $item.""==$item."" ? 0 : 2))."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
<tr><th>Averages</th><th>#</th></tr>
</thead>
<tbody>
<?php $averages=array("registrations","posts","topics","topic views");
foreach ($averages as $average) {
$item=$my_views_statistics["average_".str_replace(" ","_",$average)];
echo  "<tr".get_alt_class('plugin', $class)."><td> Average ".ucwords($average)." per day:</td>
	<td class=num>".bb_number_format_i18n($item,((int) $item.""==$item."" ? 0 : 2))."</td></tr>";
} ?>
<!-- 
<tr><td>Latest Member:</td><td> </td></tr>
<tr><td>Users Online:</td><td> </td></tr>
<tr><td>Most Online:</td><td> </td></tr>
<tr><td>Online Today:</td><td> </td></tr>
-->
</tbody>
</table>

<br clear=both />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Top Topics (by posts)</th><th>#</th></tr>
</thead>
<tbody>
<?php
foreach ($my_views_statistics["top_topics_by_posts"] as $top_topic) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic->topic_posts)."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
	<tr><th>Top Topics (by views)</th><th>#</th></tr>
</thead>
<tbody>
<?php
foreach ($my_views_statistics["top_topics_by_views"] as $top_topic) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic->views)."</td></tr>";
} ?>
</tbody>
</table>

<br clear=both />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Oldest Members</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($my_views_statistics["oldest_members"] as $member) {
		$ids[$member->ID]=$member->ID;} 
bb_cache_users($ids); 
foreach ($my_views_statistics["oldest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
	<td class='num'><span class='timetitle' title='". bb_datetime_format_i18n(bb_gmtstrtotime( $member->user_registered ), 'date')."'>".sprintf(__('%s'),bb_since(bb_gmtstrtotime( $member->user_registered )))."</span></td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
<tr><th>Newest  Members</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($my_views_statistics["newest_members"] as $member) {
		$ids[$member->ID]=$member->ID;} 
bb_cache_users($ids); 
foreach ($my_views_statistics["newest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
	<td class='num'><span class='timetitle' title='". bb_datetime_format_i18n(bb_gmtstrtotime( $member->user_registered ), 'date')."'>".sprintf(__('%s'),bb_since(bb_gmtstrtotime( $member->user_registered )))."</span></td></tr>";
} ?>
</tbody>
</table>

<br clear=both />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Top Posters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($my_views_statistics["top_posters"] as $top_poster) {
		$ids[$top_poster->poster_id]=$top_poster->poster_id;} 
bb_cache_users($ids); 
foreach ($my_views_statistics["top_posters"] as $top_poster) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $top_poster->poster_id ) ) . "'>" .get_user_name($top_poster->poster_id)."</a></td>
	<td class=num>".bb_number_format_i18n($top_poster->post_count)."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
<tr><th>Top Topic Starters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($my_views_statistics["top_topic_starters"] as $top_topic_starter) {
		$ids[$top_topic_starter->topic_poster]=$top_topic_starter->topic_poster;} 
bb_cache_users($ids); 
foreach ($my_views_statistics["top_topic_starters"] as $top_topic_starter) {
echo  "<tr".get_alt_class('plugin', $class)."><td>  <a href='" . attribute_escape( get_user_profile_link( $top_topic_starter->topic_poster ) ) . "'>" .$top_topic_starter->topic_poster_name."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic_starter->post_count)."</td></tr>";
} ?>
</tbody>
</table>

<br clear=both />

<?php
}

// endif;
?>