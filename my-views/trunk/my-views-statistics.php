<?php
/*
Plugin Name: My Views module - Statistics
Description: This plugin is part of the My Views plugin. It adds forum statistics to list of views.		
Plugin URI:  http://bbpress.org/plugins/topic/67
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.09
*/ 

//  if (function_exists('get_view_count')) :       // requires  bb-topic-views plugin   // this needs to check the db meta directly as the other plugin may not have loaded

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
	$query = '';     	
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

$my_views_statistics["total_members"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->users");
$my_views_statistics["total_posts"]=$bbdb->get_var("SELECT SUM(posts) FROM $bbdb->forums");
$my_views_statistics["total_topics"]=$bbdb->get_var("SELECT SUM(topics) FROM $bbdb->forums");
$my_views_statistics["total_topic_views"]= $bbdb->get_var("SELECT SUM(meta_value) FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views' ");
$my_views_statistics["total_tags"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->tags");
$my_views_statistics["total_forums"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->forums");
$my_views_statistics["total_days_old"]=round((time()-strtotime($bbdb->get_var("SELECT topic_start_time FROM $bbdb->topics ORDER BY topic_start_time LIMIT 1"))) / (3600 * 24),2);

$my_views_statistics["average_registrations"]=bb_number_format_i18n($my_views_statistics["total_members"]/$my_views_statistics["total_days_old"],2);
$my_views_statistics["average_posts"]=bb_number_format_i18n($my_views_statistics["total_posts"]/$my_views_statistics["total_days_old"],2);
$my_views_statistics["average_topics"]=bb_number_format_i18n($my_views_statistics["total_topics"]/$my_views_statistics["total_days_old"],2);
$my_views_statistics["average_topic_views"]=bb_number_format_i18n($my_views_statistics["total_topic_views"]/$my_views_statistics["total_days_old"],2);

$my_views_statistics["total_days_old"]=bb_number_format_i18n($my_views_statistics["total_days_old"],2);

$my_views_statistics["top_posters"] = $bbdb->get_results("SELECT poster_id, COUNT(poster_id) AS post_count FROM $bbdb->posts WHERE post_status != 1 GROUP BY poster_id ORDER BY post_count DESC LIMIT 10");
$my_views_statistics["top_topic_starters"]=$bbdb->get_results("SELECT topic_poster_name,  COUNT(topic_id) AS post_count FROM $bbdb->topics WHERE topic_status=0 GROUP BY topic_poster_name ORDER BY post_count DESC LIMIT 10");

$my_views_statistics["top_topics_by_posts"] = $bbdb->get_results("SELECT topic_title,topic_posts FROM $bbdb->topics WHERE topic_status=0 ORDER BY topic_posts DESC LIMIT 10");

	$where = apply_filters('get_latest_topics_where','');
	$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC LIMIT 10");
	foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} $ids = join(',', array_keys($trans));
	$topics ="SELECT topic_id,topic_title FROM $bbdb->topics WHERE topic_status=0 AND topic_id IN ($ids) $where ORDER BY FIELD(topic_id, $ids)";
	$topics = $bbdb->get_results($topics);
$my_views_statistics["top_topics_by_views"] = bb_append_meta( $topics, 'topic' );


?>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Totals</th><th>#</th></tr>
</thead>
<tbody>
<?php $totals=array("members","posts","topics","topic views","tags","forums","days old");
foreach ($totals as $total) {
echo "<tr".get_alt_class('plugin', $class)."><td>Total ".ucwords($total).":</td><td class=num>".$my_views_statistics["total_".str_replace(" ","_",$total)]."</td></tr>";
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
echo "<tr".get_alt_class('plugin', $class)."><td>Average ".ucwords($average)." per day:</td><td class=num>".$my_views_statistics["average_".str_replace(" ","_",$average)]."</td></tr>";
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
<tr><th>Top Posters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
foreach ($my_views_statistics["top_posters"] as $top_poster) {
echo "<tr".get_alt_class('plugin', $class)."><td>".get_user_name($top_poster->poster_id)."</td><td class=num>".$top_poster->post_count."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
<tr><th>Top Topic Starters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
foreach ($my_views_statistics["top_topic_starters"] as $top_poster) {
echo "<tr".get_alt_class('plugin', $class)."><td>".$top_poster->topic_poster_name."</td><td class=num>".$top_poster->post_count."</td></tr>";
} ?>
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
echo "<tr".get_alt_class('plugin', $class)."><td>".$top_topic->topic_title."</td><td class=num>".$top_topic->topic_posts."</td></tr>";
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
echo "<tr".get_alt_class('plugin', $class)."><td>".$top_topic->topic_title."</td><td class=num>".$top_topic->views."</td></tr>";
} ?>
</tbody>
</table>

<br clear=both />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest">
<thead>
<tr><th>Top Forums</th><th>#</th></tr>
</thead>
<tbody>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest">
<thead>
<tr><th>Top Time Online</th><th>#</th></tr>
</thead>
<tbody>
</tbody>
</table>

<br clear=both />

<?php
}

function stats_most_views( $view ) {
global $bbdb, $topics, $view_count;
if ($view=='most-views')  {$sort="DESC";}
if ($view=='least-views')  {$sort="ASC";}
if ($view=='least-views' || $view=='most-views')  {
$limit = bb_get_option('page_topics');
$where = apply_filters('get_latest_topics_where','');
$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) $sort LIMIT $limit");
foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} $ids = join(',', array_keys($trans));
$topics ="SELECT * FROM $bbdb->topics WHERE topic_status=0 AND topic_id IN ($ids) $where ORDER BY FIELD(topic_id, $ids)";
$topics = $bbdb->get_results($topics);
$view_count  = count($topics);
$topics = bb_append_meta( $topics, 'topic' );	
}
// else {do_action( 'bb_custom_view', $view );}
}

function stats_forums_views_append($forums) {
if (is_front() || is_forum()) {
global $bbdb, $forums_views; $sum_meta_value="SUM(meta_value)";
if (!isset($forums_views)) {
$forums_views = $bbdb->get_results(" SELECT $sum_meta_value,forum_id FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views'  GROUP BY $bbdb->topics.forum_id");
} foreach ($forums_views as $forum_views) {
// echo " <!-- ".$forum_views->forum_id." - ".$sum_meta_value." -->";  
if ($forum_views->forum_id) {$forums[$forum_views->forum_id]->views=$forum_views->$sum_meta_value;} 
}
}
return $forums;
}

// endif;
?>