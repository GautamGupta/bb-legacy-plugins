<?php

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || strpos($_SERVER['REQUEST_URI'],"/bb-admin/")!==false) {
if (empty($_GET['format'])) {
add_action( 'bb_admin_head','mini_stats_header',100); 
add_action('bb_admin_head','mini_stats_graph_header',100); 
}
function mini_stats_admin() { 
global $mini_stats;
if (!bb_current_user_can($mini_stats['level']))  {die();}
if ($_GET[$mini_stats['trigger']]=="statistics" && empty($_GET['format'])) {mini_stats_statistics();}
else {mini_stats_graphs();}
}

} else {
if (empty($_GET['format'])) {
add_action('bb_head','mini_stats_graph_header',100); 
bb_send_headers();
bb_get_header(); 
echo '<h3 class="bbcrumb"><a href="'.bb_get_option('uri').'">'.bb_get_option('name').'</a> &raquo; '.__('Statistics').'</h3>';
if ($_GET[$mini_stats['trigger']]=="statistics")  {mini_stats_statistics();}
else {mini_stats_graphs();}
echo '<br clear="both" />';
bb_get_footer(); 
} else {mini_stats_graphs();}	// format==CSV
exit;
}

function mini_stats_graph_header() {global $mini_stats;  echo '<style type="text/css">'.$mini_stats['style_graph'].'</style>';}

function mini_stats_graphs() { 
global $bbdb, $bb, $mini_stats;
// putenv("TZ=PST");	 // debug

if (!empty($bb->wp_table_prefix)) {$wp_table_prefix=$bb->wp_table_prefix; $loop_max=5;} else {$loop_max=3;}
if (function_exists('pm_install_check')) {$privatemessages=$bbdb->get_var("SHOW TABLES LIKE '%_privatemessages'"); if (!empty($privatemessages)) {$loop_max=6;}}

$gmt_offset=intval(bb_get_option("gmt_offset"))*3600;
$time=strtotime(gmdate('Y-m-d',time()+$gmt_offset)." 23:59:59 +0000")-$gmt_offset;   // midnight of today's date in GMT (before offset)

// print gmdate("r",$time)."<br>".date("r",$time)."<br>".gmdate("r",$time+$gmt_offset)."<br>".date("r",$time+$gmt_offset)."<br>"; // debug

if (bb_current_user_can('moderate')) {	
	if (!empty($_GET['days']) && ($days=intval($_GET['days']))>31) {$limit=$days;} else {$limit=31;}
	if (empty($_GET[$mini_stats['trigger']])) {$endtime=$time;} 
	else {   $endtime=$_GET[$mini_stats['trigger']]; 
		if (intval($endtime)) {$endtime=strtotime(gmdate('Y-m-d',strtotime($endtime." 23:59:59 +0000")+$gmt_offset)." 23:59:59 +0000");} 
		else {$endtime=0;}
		if (empty($endtime) || $endtime==-1) {$endtime=$time;} else {$endtime=$endtime-$gmt_offset;}
	}
	if (empty($_GET['format'])) {$format=""; $showmenu=true;} else {$format=strtoupper(trim($_GET['format']));}
} else {$endtime=$time; $format=""; $limit=31;}

$starttime=1+$endtime-($limit*24*3600); 	// 31 days backwards plus one second to make it midnight next day

if (!empty($format)) {		
	$endtime=$time;	
	$limit=1+floor(($endtime-$starttime)/(24*3600));	// calculate new number of days in date range
	$label[0]=__("date"); 
	$label[1]=__("topics"); 
	$label[2]=__("posts");
	$label[3]=__("registrations"); 
	$label[4]=__("wp posts"); 
	$label[5]=__("wp comments"); 
	$label[6]=__("private messages");
} else {		
	$maxheight=($limit>31) ? 75 : 50;
	$today=gmdate("Y-m-d",$time+$gmt_offset);
	$label[1]=__("Daily Total Topics");
	$label[2]=__("Daily Total Posts");
	$label[3]=__("Daily Total Registrations");
	$label[4]=__("Daily WP Posts");
	$label[5]=__("Daily WP Comments");
	$label[6]=__("Daily Private Messages");
}

$mysql_starttime=gmdate("Y-m-d H:i:s",$starttime); 	 // store mysql search range
$mysql_endtime=gmdate("Y-m-d H:i:s",$endtime);
$time=$time+$gmt_offset;					 // now shift everything into local time
$endtime=$endtime+$gmt_offset;
$starttime=$starttime+$gmt_offset;

$count=0;  $day=$starttime;  $legend=""; $makelegend=(!$format && $limit==31) ? true : false;
do {					// build empty date list, zero filled for database data merge
$date=gmdate('Y-m-d',$day);
$empty[$date]->time=$day;
$empty[$date]->topics=0;
$empty[$date]->posts=0;
$empty[$date]->views=0;
$empty[$date]->users=0;
$empty[$date]->wp_posts=0;
$empty[$date]->wp_comments=0;
if ($makelegend) {$legend.="<td>".intval(substr($date,5,2))."/".intval(substr($date,8,2))."</td>";} else {$months[$count]=substr($date,5,2);}
$day=$day+24*3600;
$count++;
} while ($limit>$count); 	

if (empty($format)) {
echo '<div style="text-align:right; font-size:13px; margin:0.5em 0;">';
if (!empty($showmenu)) {
echo (!empty($_GET[$mini_stats['trigger']]) || $limit>31  ? gmdate('M j',$starttime)." - ".gmdate('M j, Y',$endtime)." | " : "");
// .'[<a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime-31*24*3600)).'">'.__('previous month').'</a>] [<a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)-1).'-'.gmdate('n-j',$endtime)).'">'.__('previous year').'</a>] '
echo '<form style="display:inline;" method="get"><label for="days"><a style="border:0;" name="view">'.__('view').'</a>:</label>';
echo '<select style="padding:0;margin:0;border:0;background:transparent;width:6em;" name="days" onchange="this.form.submit();" >
<option value="31"'.($limit==31 ? ' selected="selected"' : '').'>month</option>
<option value="93"'.($limit==93 ? ' selected="selected"' : '').'>quarter</option>
<option value="186"'.($limit==186 ? ' selected="selected"' : '').'>&#189; year</option>
<option value="366"'.($limit==366 ? ' selected="selected"' : '').'>year</option>
</select>';
foreach ($_GET as $key=>$value) {if ($key!='days') {echo '<input type="hidden" name="'.$key.'" value="'.$value.'">';}} 
echo '</form>';
echo 
' | <a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)-1).'-'.gmdate('n-j',$endtime)).'"> <b><<</b> '.__('year').'</a> <a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime-31*24*3600)).'"> <b><</b> '.__('month').'</a> ' 
.' | '.(empty($_GET[$mini_stats['trigger']]) ? __('current') : '<a href="'.add_query_arg($mini_stats['trigger'],"").'">'.__('current').'</a>').' | '
 .'<a href="'.add_query_arg($mini_stats['trigger'],gmdate('Y-n-j',$endtime+31*24*3600)).'">'.__('month').' <b>></b></a> <a href="'.add_query_arg($mini_stats['trigger'],(gmdate('Y',$endtime)+1).'-'.gmdate('n-j',$endtime)).'">'.__('year').' <b>>></b> </a> '
.' | <a target="_blank" href="'.add_query_arg('format',"CSV").'">'.__('CSV').'</a> | ';
}
echo __('graphs').' | <a href="'.add_query_arg($mini_stats['trigger'],'statistics',remove_query_arg($mini_stats['trigger'])).'">'.__('statistics').'</a></div>';
}

for ($loop=1; $loop<=$loop_max; $loop++) {

if ($loop==1) {   // topics per day
$variable="topics";
$query="SELECT DATE(DATE_ADD(topic_start_time,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $bbdb->topics WHERE topic_status=0  AND topic_start_time>='$mysql_starttime' AND topic_start_time<='$mysql_endtime' GROUP BY time ORDER BY topic_start_time ASC";
}
elseif ($loop==2) {  // posts per day
$variable="posts";
$query="SELECT DATE(DATE_ADD(post_time,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $bbdb->posts WHERE post_status=0  AND post_time>='$mysql_starttime' AND post_time<='$mysql_endtime' GROUP BY time ORDER BY post_time ASC";
}
elseif ($loop==3) { // registrations per day
$variable="users";
$query="SELECT DATE(DATE_ADD(user_registered,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $bbdb->users WHERE user_status=0  AND user_registered>='$mysql_starttime' AND user_registered<='$mysql_endtime' GROUP BY time ORDER BY user_registered ASC";
}
elseif ($loop==4 && !empty($wp_table_prefix)) { // WP posts per day
$variable="wp_posts";
$query="SELECT DATE(DATE_ADD(post_date_gmt,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $wp_table_prefix"."posts WHERE post_status='publish'  AND post_date_gmt>='$mysql_starttime' AND post_date_gmt<='$mysql_endtime'  GROUP BY time ORDER BY post_date_gmt ASC";
}
elseif ($loop==5 && !empty($wp_table_prefix)) { // WP comments per day
$variable="wp_comments";
$query="SELECT DATE(DATE_ADD(comment_date_gmt,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $wp_table_prefix"."comments WHERE comment_approved='1'  AND comment_date_gmt>='$mysql_starttime' AND comment_date_gmt<='$mysql_endtime' GROUP BY time ORDER BY comment_date_gmt ASC";
}
elseif ($loop==6 && !empty($privatemessages)) { // PM's per day
$variable="private_messages";
 $query="SELECT DATE(DATE_ADD(created_on,INTERVAL $gmt_offset SECOND)) as time,count(*) as $variable FROM $privatemessages  WHERE created_on>='$mysql_starttime' AND created_on<='$mysql_endtime' GROUP BY time ORDER BY created_on ASC";
}

$results=$bbdb->get_results($query);     // print "<pre>"; foreach ($results as $result) {print $result->time." - ".."<br>";} exit;    // debug

if (empty($results)) {if (empty($format)) {continue;} $results=$empty;}
// else {$fill=$empty; foreach ($results as $result) {$fill[gmdate("Y-m-d",strtotime($result." +0000")+$gmt_offset)]->$variable++;} $results=$fill; unset($fill);}
else {$fill=$empty; foreach ($results as $result) {$fill[$result->time]->$variable=$result->$variable;} $results=$fill; unset($fill);}

if (empty($format)) {

    $count=0; unset($values); unset($colors); 
    foreach ($results as $date=>$result) {    	    	
	$values[$count]=$result->$variable; 			
    	if ($date==$today) {$colors[$count]="#bbb";}     // #7799aa
    	$count++; if ($count>$limit) {break;}
    }    
    $peak=$values; arsort($peak); $peak=array_keys($peak);
    $colors[$peak[0]]="#cc0000";
    $colors[$peak[1]]="#0000cc";
    $colors[$peak[2]]="#00cc00";
    $multiply=round($maxheight/(0.0001+$values[$peak[0]]),2);
    $width=round(100/($limit+0.0001),2); if ($width==0) {$width=0.01;} elseif ($width>100) {$width=50;}
    
$output= "<br clear='both' /><h3 align='center'>$label[$loop]</h3>\n<div style='position:relative;'><table style='' class='mini_stats_graph".($limit>31 ? " mini_stats_graph_large" : "")."'>";
$output.="<tr valign='bottom'>";
if ($limit>31) {
$height=(1+$multiply*$values[$peak[0]]);
$height2=$height/2; $value2=intval($height2/$multiply); $height2=(1+$multiply*$value2);
$height=$height-$height2;
$output.="<td style='width:1%;padding:0 1em 0 0;'><div  class='mini_stats_grid' style='margin-top:-$height"."px;'>".$values[$peak[0]]."</div>";
if ($value2>0) {
$output.="<div class='mini_stats_grid'>".$value2."</div><div style='border:0;width:0px;background:none;height:".$height2."px;'></div>";
}
$output.="</div></td>";
}
if ($limit<180) {$w=" width='$width%'";} else {$w="";}	// save html size by eliminating width control when lines get narrow
foreach ($values as $count=>$total)  {
	if (!$makelegend) {if ($count!=0 && $months[$count]!=$months[$count-1]) {$style=" style='border-left:1px dashed #ccc;'";} else {$style="";}}
	$output.="<td$w$style>";	
	// if ($values[$count]>$values[$peak[20]]) {$output.="$values[$count]<div";} else {$output.="<div title='$values[$count]'";}
	if ($limit==31 && $values[$count]) {$output.="$values[$count]";}
	$output.="<div style='height:".(1+$multiply*$values[$count])."px;"; // 
	if (isset($colors[$count])) {$output.="background:$colors[$count];";}
	$output.="'> </div></td>";
}
$output.="</tr>".($legend ? "<tr class='alt'>".$legend."</tr>" : "")."</table></div><br clear='both'>\n";

echo $output;

} else {  // CSV
	if (!empty($results)) {foreach ($results as $date=>$result) {$CSV[$date][$loop]=$result->$variable;} unset($results);}

}	 // CSV check
}	//  end graph loops

if (!empty($format)) {
	$keys=(array) $label[0] + reset($CSV); $filename=key($CSV).'.csv';		
	unset($labels); foreach ($keys as $key=>$value) {$labels[$key]=$label[$key];}
	$output=implode(",",$labels)."\r\n";
	foreach ($CSV as $date=>$line) {$output.= $date.",".implode(",",$line)."\r\n";} 	
	 unset($CSV); $size=strlen($output);	
	header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
if ($format=="CSV") {
	header("Pragma: hack");
	header("Content-Type: application/octet-stream");
	header("Content-Length: $size");
	header("Content-Disposition: attachment; filename=".'"'.$filename.'"');
	header("Content-Transfer-Encoding: binary"); 
} else {
	header("Content-Type: text/plain");
}	             
	ob_clean();
  	flush();  
	print $output;
	exit;
}

}

function mini_stats_statistics() {
global $bbdb,$mini_stats;

echo '<div style="text-align:right; font-size:13px; margin:0.5em 0;"><a href="'.add_query_arg($mini_stats['trigger'],'',remove_query_arg($mini_stats['trigger'])).'">'.__('graphs').'</a> | '.__('statistics').'</div>';
echo "<h3 align='center'>".__('Statistics')."</h3>\n<br clear='both' />";

$topiclimit=10;  // top topics 10,15,20 etc.
$userlimit=10;  // top users 10,15,20 etc.

// SELECT count(*) as posts, count(distinct topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` WHERE post_status=0
// SELECT count(*) as posts, count(distinct t1.topic_id) as topics, count(distinct poster_id) as members FROM `bb_posts` as t1  LEFT JOIN `bb_topics` as t2 ON t1.topic_id=t2.topic_id WHERE post_status=0 AND topic_status=0

$results=$bbdb->get_results("SELECT COUNT(*) as forums, SUM(posts)  as posts, SUM(topics) as topics FROM $bbdb->forums");
$statistics["total_forums"]=$results[0]->forums;
$statistics["total_posts"]=$results[0]->posts;
$statistics["total_topics"]=$results[0]->topics;

$statistics["total_members"]=$bbdb->get_var("SELECT COUNT(*) FROM $bbdb->users");
$statistics["total_days_old"]=round((time()-strtotime($bbdb->get_var("SELECT topic_start_time FROM $bbdb->topics ORDER BY topic_start_time LIMIT 1")." +0000")) / (3600 * 24),0);

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

$statistics["oldest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users LEFT JOIN $bbdb->usermeta ON $bbdb->users.ID=$bbdb->usermeta.user_id WHERE (meta_key='$bbdb->prefix"."capabilities' and meta_value NOT REGEXP 'inactive|blocked') ORDER BY user_registered ASC LIMIT $userlimit");
$statistics["newest_members"] = $bbdb->get_results("SELECT ID,user_registered FROM $bbdb->users LEFT JOIN $bbdb->usermeta ON $bbdb->users.ID=$bbdb->usermeta.user_id WHERE (meta_key='$bbdb->prefix"."capabilities' and meta_value NOT REGEXP 'inactive|blocked') ORDER BY user_registered DESC LIMIT $userlimit");

$statistics["top_posters"] = $bbdb->get_results("SELECT DISTINCT poster_id, COUNT(poster_id) AS post_count FROM $bbdb->posts WHERE post_status != 1 GROUP BY poster_id ORDER BY post_count DESC LIMIT $userlimit");
$statistics["top_topic_starters"]=$bbdb->get_results("SELECT DISTINCT topic_poster, topic_poster_name, COUNT(topic_id) AS post_count FROM $bbdb->topics WHERE topic_status=0 GROUP BY topic_poster_name ORDER BY post_count DESC LIMIT $userlimit");

$topics=$bbdb->get_results("SELECT * FROM $bbdb->topics WHERE topic_status=0 ORDER BY topic_posts DESC LIMIT $topiclimit"); 	// topic_id,topic_title,topic_posts
$statistics["top_topics_by_posts"] = bb_append_meta( $topics, 'topic' );
	
if (!empty($statistics["total_topic_views"])) {
	$statistics["average_topic_views"]=$statistics["total_topic_views"]/$statistics["total_days_old"];

	if (defined('BACKPRESS_PATH')) {   // bbPress 1.0 vs 0.9
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

unset($ids);
foreach ($statistics["oldest_members"] as $member) {$ids[$member->ID]=$member->ID;} 
foreach ($statistics["newest_members"] as $member) {$ids[$member->ID]=$member->ID;} 
foreach ($statistics["top_posters"] as $member) {$ids[$member->poster_id]=$member->poster_id;} 
foreach ($statistics["top_topic_starters"] as $member) {$ids[$member->topic_poster]=$member->topic_poster;} 
bb_cache_users($ids); 
?>

<table style="width:49%;margin:0 0 1em 0;clear:none;float:left;" id="latest" class="widefat">
<thead>
<tr><th>Totals</th><th>#</th></tr>
</thead>
<tbody>
<?php $totals=array("members","posts","topics","topic views","tags","forums","days old");
foreach ($totals as $total) {
$item=$statistics["total_".str_replace(" ","_",$total)];
echo  "<tr".get_alt_class('plugin', '')."><td> Total ".ucwords($total).":</td>
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
echo  "<tr".get_alt_class('plugin', '')."><td> Average ".ucwords($average)." per day:</td>
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
echo  "<tr".get_alt_class('plugin', '')."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
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
echo  "<tr".get_alt_class('plugin', '')."><td> <a href='".get_topic_link($top_topic->topic_id)."'>".$top_topic->topic_title."</a></td>
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
foreach ($statistics["oldest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', '')."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
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
foreach ($statistics["newest_members"] as $member) {
echo  "<tr".get_alt_class('plugin', '')."><td> <a href='" . attribute_escape( get_user_profile_link( $member->ID ) ) . "'>" .get_user_name($member->ID)."</a></td>
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
foreach ($statistics["top_posters"] as $top_poster) {
echo  "<tr".get_alt_class('plugin', '')."><td> <a href='" . attribute_escape( get_user_profile_link( $top_poster->poster_id ) ) . "'>" .get_user_name($top_poster->poster_id)."</a></td>
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
foreach ($statistics["top_topic_starters"] as $top_topic_starter) {
echo  "<tr".get_alt_class('plugin', '')."><td>  <a href='" . attribute_escape( get_user_profile_link( $top_topic_starter->topic_poster ) ) . "'>" .$top_topic_starter->topic_poster_name."</a></td>
	<td class=num>".bb_number_format_i18n($top_topic_starter->post_count)."</td></tr>";
} ?>
</tbody>
</table>

<?php
}

?>