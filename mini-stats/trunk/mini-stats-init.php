<?php

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || strpos($_SERVER['REQUEST_URI'],"/bb-admin/")!==false) {
if (empty($_GET['format'])) {
add_action( 'bb_admin_head','mini_stats_header',100); 
add_action('bb_admin_head','mini_stats_graph_header',100); 
}
function mini_stats_admin() { 
if (!bb_current_user_can('administrate'))  {die();}
global $mini_stats;
mini_stats_graphs();
if (empty($_GET[$mini_stats['trigger']]) && empty($_GET['format']))  {mini_stats_statistics();}
}

} else {
if (empty($_GET['format'])) {
add_action('bb_head','mini_stats_graph_header',100); 
bb_send_headers();
bb_get_header(); 
echo '<h3 class="bbcrumb"><a href="'.bb_get_option('uri').'">'.bb_get_option('name').'</a> &raquo; '.__('Statistics').'</h3>';
mini_stats_graphs();
if (empty($_GET[$mini_stats['trigger']]))  {mini_stats_statistics();}
echo '<br clear="both" />';
bb_get_footer(); 
} else {mini_stats_graphs();}	// format==CSV
exit;
}

function mini_stats_graph_header() {global $mini_stats;  echo '<style type="text/css">'.$mini_stats['style_graph'].'</style>';}

function mini_stats_graphs() { 
global $bbdb, $mini_stats;
if (bb_current_user_can('administrate')) {
	$gmt_offset=bb_get_option("gmt_offset")*3600;
	$time=time();

	$endtime=$_GET[$mini_stats['trigger']]; 
	if (empty($endtime)) {$endtime=$time;} 
	else {$endtime=strtotime($endtime." 23:59:59 GMT"); if (empty($endtime) || $endtime==-1) {$endtime=$time;}}

	if (isset($_GET['format']) && $_GET['format']=="CSV") {$format="CSV";} else {$format="";}
} else {$endtime=$time; $format="";}

$endmidnight=strtotime(gmdate('Y-m-d',$endtime)." GMT"); 
$limit=31; $starttime=($endmidnight)-(($limit-1)*24*3600);
$time=$time+$gmt_offset;

$gmt_starttime=$starttime;
$gmt_endtime=$endtime;
$starttime=$starttime+$gmt_offset;
$endtime=$endtime+$gmt_offset;

if ($format=="CSV") {		
	$endtime=$time;
	$limit=ceil(($endtime-$starttime)/(24*3600));
	$label[1]=__("topics"); 
	$label[2]=__("posts");
	$label[3]=__("registrations"); 
} else {
	$maxheight=50;
	$today=gmdate("n/j",$time);
	$label[1]=__("Daily Total Topics");
	$label[2]=__("Daily Total Posts");
	$label[3]=__("Daily Total Registrations");
}

$count=0;  $day=$starttime;
do {				
$date=gmdate('Y-m-d',$day);
$empty[$date]->time=$day;
$empty[$date]->topics=0;
$empty[$date]->posts=0;
$empty[$date]->views=0;
$empty[$date]->users=0;
$day=$day+24*3600;
$count++;
} while ($limit>=$count); 	// ($day>$monthago);
// $empty=array_reverse($empty);

if (empty($fomat) && bb_current_user_can('administrate')) {
echo '<div style="text-align:right; font-size:13px; margin:0 0 -9px 0;">'.(empty($_GET[$mini_stats['trigger']]) ? '' : gmdate('M j, Y | ',$endtime))
// .'[<a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime-31*24*3600)).'">'.__('previous month').'</a>] [<a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)-1).'-'.gmdate('n-j',$endtime)).'">'.__('previous year').'</a>] '
 .'<a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)-1).'-'.gmdate('n-j',$endtime)).'"> <b><<</b> '.__('year').'</a> <a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime-31*24*3600)).'"> <b><</b> '.__('month').'</a> ' 
.' < '.(empty($_GET[$mini_stats['trigger']]) ? __('current') : '<a href="'.add_query_arg($mini_stats['trigger'],"").'">'.__('current').'</a>').' > '
 .'<a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime+31*24*3600)).'">'.__('month').' <b>></b></a> <a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)+1).'-'.gmdate('n-j',$endtime)).'">'.__('year').' <b>>></b> </a> '
.' | <a target="_blank" href="'.add_query_arg('format',"CSV").'">'.__('CSV').'</a></div>';
}

for ($loop=1; $loop<=3; $loop++) {

if ($loop==1) {
// topics per day
$query="SELECT UNIX_TIMESTAMP(topic_start_time) as time FROM $bbdb->topics WHERE topic_status=0  AND UNIX_TIMESTAMP(topic_start_time)>=$gmt_starttime AND UNIX_TIMESTAMP(topic_start_time)<=$gmt_endtime ORDER BY topic_start_time ASC";
$variable="topics";
}
elseif ($loop==2) {
// posts per day
$query="SELECT UNIX_TIMESTAMP(post_time) as time FROM $bbdb->posts WHERE post_status=0  AND UNIX_TIMESTAMP(post_time)>=$gmt_starttime AND UNIX_TIMESTAMP(post_time)<=$gmt_endtime ORDER BY post_time ASC";
$variable="posts";
}
elseif ($loop==3) {
// registrations per day
$query="SELECT UNIX_TIMESTAMP(user_registered) as time FROM $bbdb->users WHERE user_status=0  AND UNIX_TIMESTAMP(user_registered)>=$gmt_starttime AND UNIX_TIMESTAMP(user_registered)<=$gmt_$endtime ORDER BY user_registered ASC";
$variable="users";
}

@$results=$bbdb->get_results($query);  // print "<pre>"; foreach ($results as $result) {print $result->time." - ".."<br>";} exit;

// make missing days have zero results
// unset($fill); foreach ($results as $result) {$fill[$result->time]=$result;} $results=array_merge($empty,$fill);

$fill=$empty; foreach ($results as $result) {$fill[gmdate("Y-m-d",$result->time+$gmt_offset)]->$variable++;} $results=$fill;

if ($format!="CSV") {

    $count=0; unset($values); unset($labels); unset($colors);
    foreach ($results as $date=>$result) {    	    	
    	        if ($loop==1) {$values[$count]=$result->topics;}    		
    	elseif ($loop==2) {$values[$count]=$result->posts;}
    	elseif ($loop==3) {$values[$count]=$result->users;}	

    	$labels[$count]=intval(substr($date,5,2))."/".intval(substr($date,8,2));	// gmdate("n/j",strtotime($date)." GMT");    //  gmdate("n/j",$result->time+$gmt_offset); 
    	if ($labels[$count]==$today) {$colors[$count]="#bbb";}     // #7799aa
    	$count++; if ($count>$limit) {break;}
    }    
    $peak=$values; arsort($peak); $peak=array_keys($peak);
    $colors[$peak[0]]="#cc0000";
    $colors[$peak[1]]="#0000cc";
    $colors[$peak[2]]="#00cc00";
    $multiply=round($maxheight/(0.0001+$values[$peak[0]]),2);
    $width=round(100/($count+0.0001),2); if ($width<1) {$width=1;} elseif ($width>100) {$width=50;}
    
$output= "<br clear='both' /><h3 align='center'>$label[$loop]</h3>\n<table class='mini_stats_graph'>";
$output.="<tr valign='bottom'>";
foreach (array_keys($values) as $count)  {
	$output.="<td width='$width%'>";	
	// if ($values[$count]>$values[$peak[20]]) {$output.="$values[$count]<div";} else {$output.="<div title='$values[$count]'";}
	if ($values[$count]) {$output.="$values[$count]";}
	$output.="<div style='height:".(1+$multiply*$values[$count])."px;"; // 
	if (isset($colors[$count])) {$output.="background:$colors[$count];";}
	$output.="'> </div></td>";
}
$output.="</tr>";

$output.="<tr class='alt'>";
foreach (array_keys($values) as $count)  {
// if ($values[$count]>$values[$peak[20]]) {$output.="<td>$labels[$count]</td>";} else {$output.="<td title='$labels[$count]'> </td>";}
$output.="<td>$labels[$count]</td>";
}
$output.="</tr>";

$output.= "</table><br clear='both'>\n";

echo $output;

} else {  // CSV check
	foreach ($results as $date=>$result) {    	    	
    	        if ($loop==1) {$value=$result->topics;}    		
    	elseif ($loop==2) {$value=$result->posts;}
    	elseif ($loop==3) {$value=$result->users;}	
	$CSV[$date][$loop]=$value; 	//  gmdate("Y-m-d",strtotime($date)+$gmt_offset)  // gmdate("Y-m-d",$result->time+$gmt_offset)
	}

}	 // CSV check
}	//  end graph loops

if ($format=="CSV") {
	$output=__('date').",".implode(",",$label)."\r\n";
	foreach ($CSV as $date=>$line) {$output.= $date.",".implode(",",$line)."\r\n";} reset($CSV);
//	header("Content-Type: text/plain");
	header ("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
	header("Pragma: hack");
	header("Content-Type: application/octet-stream");
	header("Content-Length: ".strlen($output));
	header('Content-Disposition: attachment; filename="'.key($CSV).'.csv"');
	header("Content-Transfer-Encoding: binary");              
	ob_clean();
  	flush();  
	print $output;
	exit;
}

}

function mini_stats_statistics() {
global $bbdb;

echo  "<br clear='both' /><h3 align='center'>".__('Statistics')."</h3>\n<br clear='both' />";

$topiclimit=10;  // top topics 10,15,20 etc.
$userlimit=10;  // top users 10,15,20 etc.

// SELECT count(*) as posts, count(distinct topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` WHERE post_status=0
// SELECT count(*) as posts, count(distinct t1.topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` as t1  LEFT JOIN `bb_topics` as t2 ON t1.topic_id=t2.topic_id WHERE post_status=0 AND topic_status=0

$results=$bbdb->get_results("SELECT COUNT(*) as forums, SUM(posts)  as posts, SUM(topics) as topics FROM $bbdb->forums");
$statistics["total_forums"]=$results[0]->forums;
$statistics["total_posts"]=$results[0]->posts;
$statistics["total_topics"]=$results[0]->topics;

$statistics["total_members"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->users");
$statistics["total_days_old"]=round((time()-strtotime($bbdb->get_var("SELECT topic_start_time FROM $bbdb->topics ORDER BY topic_start_time LIMIT 1"))) / (3600 * 24),2);

if (bb_get_option('bb_db_version')>1600) { // bbPress 1.0 vs 0.9
$statistics["total_topic_views"]= $bbdb->get_var("SELECT SUM(meta_value) FROM $bbdb->meta LEFT JOIN $bbdb->topics ON $bbdb->meta.object_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->meta.meta_key='views' AND $bbdb->meta.object_type='bb_topic' ");
$statistics["total_tags"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->terms");
} else {	 // 0.9
$statistics["total_topic_views"]= $bbdb->get_var("SELECT SUM(meta_value) FROM $bbdb->topicmeta LEFT JOIN $bbdb->topics ON $bbdb->topicmeta.topic_id = $bbdb->topics.topic_id  WHERE $bbdb->topics.topic_status=0 AND $bbdb->topicmeta.meta_key='views' ");
$statistics["total_tags"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->tags");
}

$statistics["average_registrations"]=$statistics["total_members"]/$statistics["total_days_old"];
$statistics["average_posts"]=$statistics["total_posts"]/$statistics["total_days_old"];
$statistics["average_topics"]=$statistics["total_topics"]/$statistics["total_days_old"];

$statistics["oldest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users ORDER BY user_registered ASC LIMIT $userlimit");
$statistics["newest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users ORDER BY user_registered DESC LIMIT $userlimit");

$statistics["top_posters"] = $bbdb->get_results("SELECT DISTINCT poster_id, COUNT(poster_id) AS post_count FROM $bbdb->posts WHERE post_status != 1 GROUP BY poster_id ORDER BY post_count DESC LIMIT $userlimit");
$statistics["top_topic_starters"]=$bbdb->get_results("SELECT DISTINCT topic_poster, topic_poster_name, COUNT(topic_id) AS post_count FROM $bbdb->topics WHERE topic_status=0 GROUP BY topic_poster_name ORDER BY post_count DESC LIMIT $userlimit");

$topics=$bbdb->get_results("SELECT * FROM $bbdb->topics WHERE topic_status=0 ORDER BY topic_posts DESC LIMIT $topiclimit"); 	// topic_id,topic_title,topic_posts
$statistics["top_topics_by_posts"] = bb_append_meta( $topics, 'topic' );
	
if (!empty($statistics["total_topic_views"])) {
	$statistics["average_topic_views"]=$statistics["total_topic_views"]/$statistics["total_days_old"];

	if (bb_get_option('bb_db_version')>1600) { // bbPress 1.0 vs 0.9
	$most_views = $bbdb->get_results("SELECT object_id as topic_id FROM $bbdb->meta WHERE meta_key='views' AND object_type='bb_topic' ORDER BY cast(meta_value as UNSIGNED) DESC LIMIT $topiclimit");
	} else {
	$most_views = $bbdb->get_results("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key='views' ORDER BY cast(meta_value as UNSIGNED) DESC LIMIT $topiclimit");
	}
	foreach (array_keys($most_views) as $i) {$trans[$most_views[$i]->topic_id] =& $most_views[$i];} $ids = join(',', array_keys($trans));
	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 AND topic_id IN ($ids) ");
	$topics ="SELECT * FROM $bbdb->topics $where ORDER BY FIELD(topic_id, $ids)"; // topic_id,topic_title
	$topics = $bbdb->get_results($topics);
$statistics["top_topics_by_views"] = bb_append_meta( $topics, 'topic' );
} else {
	$statistics["top_topics_by_views"] = array(); 
	$statistics["average_topic_views"]="";
}

?>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest" class="widefat">
<thead>
<tr><th>Totals</th><th>#</th></tr>
</thead>
<tbody>
<?php $totals=array("members","posts","topics","topic views","tags","forums","days old");
foreach ($totals as $total) {
$item=$statistics["total_".str_replace(" ","_",$total)];
echo  "<tr".get_alt_class('plugin', $class)."><td> Total ".ucwords($total).":</td>
	<td class=num>".bb_number_format_i18n($item,((int) $item.""==$item."" ? 0 : 2))."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest" class="widefat">
<thead>
<tr><th>Averages</th><th>#</th></tr>
</thead>
<tbody>
<?php $averages=array("registrations","posts","topics","topic views");
foreach ($averages as $average) {
$item=$statistics["average_".str_replace(" ","_",$average)];
echo  "<tr".get_alt_class('plugin', $class)."><td> Average ".ucwords($average)." per day:</td>
	<td class=num>".bb_number_format_i18n($item,((int) $item.""==$item."" ? 0 : 2))."</td></tr>";
} ?>
</tbody>
</table>

<br clear="both" />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest" class="widefat">
<thead>
<tr><th>Top Topics (by posts)</th><th>#</th></tr>
</thead>
<tbody>
<?php
foreach ($statistics["top_topics_by_posts"] as $top_topic) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic->topic_posts)."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest" class="widefat">
<thead>
	<tr><th>Top Topics (by views)</th><th>#</th></tr>
</thead>
<tbody>
<?php
foreach ($statistics["top_topics_by_views"] as $top_topic) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic->views)."</td></tr>";
} ?>
</tbody>
</table>

<br clear="both" />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest" class="widefat">
<thead>
<tr><th>Oldest Members</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($statistics["oldest_members"] as $member) {
		$ids[$member->ID]=$member->ID;} 
bb_cache_users($ids); 
foreach ($statistics["oldest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
	<td class='num'><span class='timetitle' title='". bb_datetime_format_i18n(bb_gmtstrtotime( $member->user_registered ), 'date')."'>".sprintf(__('%s'),bb_since(bb_gmtstrtotime( $member->user_registered )))."</span></td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest" class="widefat">
<thead>
<tr><th>Newest  Members</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($statistics["newest_members"] as $member) {
		$ids[$member->ID]=$member->ID;} 
bb_cache_users($ids); 
foreach ($statistics["newest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
	<td class='num'><span class='timetitle' title='". bb_datetime_format_i18n(bb_gmtstrtotime( $member->user_registered ), 'date')."'>".sprintf(__('%s'),bb_since(bb_gmtstrtotime( $member->user_registered )))."</span></td></tr>";
} ?>
</tbody>
</table>

<br clear="both" />

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest" class="widefat">
<thead>
<tr><th>Top Posters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($statistics["top_posters"] as $top_poster) {
		$ids[$top_poster->poster_id]=$top_poster->poster_id;} 
bb_cache_users($ids); 
foreach ($statistics["top_posters"] as $top_poster) {
echo  "<tr".get_alt_class('plugin', $class)."><td> <a href='" . attribute_escape( get_user_profile_link( $top_poster->poster_id ) ) . "'>" .get_user_name($top_poster->poster_id)."</a></td>
	<td class=num>".bb_number_format_i18n($top_poster->post_count)."</td></tr>";
} ?>
</tbody>
</table>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:right;" id="latest" class="widefat">
<thead>
<tr><th>Top Topic Starters</th><th>#</th></tr>
</thead>
<tbody>
<?php 
unset($ids); foreach ($statistics["top_topic_starters"] as $top_topic_starter) {
		$ids[$top_topic_starter->topic_poster]=$top_topic_starter->topic_poster;} 
bb_cache_users($ids); 
foreach ($statistics["top_topic_starters"] as $top_topic_starter) {
echo  "<tr".get_alt_class('plugin', $class)."><td>  <a href='" . attribute_escape( get_user_profile_link( $top_topic_starter->topic_poster ) ) . "'>" .$top_topic_starter->topic_poster_name."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic_starter->post_count)."</td></tr>";
} ?>
</tbody>
</table>

<?php
}

?>