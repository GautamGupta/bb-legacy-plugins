<?php
/*
Plugin Name: Mini Stats
Plugin URI: http://bbpress.org/plugins/topic/mini-stats
Description: Some simple forum statistics.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM
*/

// place a link anywhere in your templates you want to link to statistics with the trigger, ie.  <a href="?mini-stats">statistics</a>

$mini_stats['statistics_in_footer']=true;			// true = automatic, no edits,  false =  manual placement via mini_stats();
$mini_stats['statistics_only_on_front_page']=false;	// every page or only front-page
$mini_stats['disable_footer']=false;			// disable footer feature entirely (for extremely active forums)

$mini_stats['show_new_members']=true;		// show names of newest registered members in footer
$mini_stats['new_members']=3;			// how many new members to show if enabled in footer

$mini_stats['trigger']="mini-stats";		// URL line option to show stats,  ie.  ?mini-stats
$mini_stats['level']="read"; 			// read/participate/moderate/administrate  (access level, read = anyone)

$mini_stats['icons']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/icons/';  // web path to icons

$mini_stats['style']="
.mini_stats {text-align:center; line-height:160%;} 		
.mini_stats_num, .mini_stats strong, mini_track a {color: #000080;}
.mini_stats_num {font-family: verdana, arial, sans-serif; font-weight:bold; font-size: 90%;}
.mini_stats_wrap {white-space: nowrap; zoom:1;}	
.mini_stats img {margin:0px 2px -1px 2px; border:0; text-decoration:none;}
.mini_stats_users {padding-left: 20px;  background: url(".$mini_stats['icons']."users.png) 1px 0px no-repeat;}
.mini_stats_member {padding-left: 20px;  background: url(".$mini_stats['icons']."member.png) 1px 0px no-repeat;}
.mini_stats_stats {padding-left: 20px;  background: url(".$mini_stats['icons']."stats.png) 1px 0px no-repeat;}	
.mini_stats_users .mini_stats_num, .mini_stats_star .mini_stats_num {margin-left: -1px;}
";

$mini_stats['style_graph']="
.mini_stats_graph {font-family:arial,san-serif; font-size:11px; border:1px solid #aabbcc; float:left; letter-spacing:-1px; word-spacing:-1px; border: 0; border-collapse: collapse; width:100%; margin:1em 0;}
.mini_stats_graph TD {font-family:arial,san-serif; font-size:11px; white-space:nowrap; overflow:hidden; text-align:center; border:0; border-collapse: collapse;}
.mini_stats_graph TD div {font-size:1px;background:#aabbcc; margin: 0 1px; border-right: 1px solid #000000; border-top: 1px solid #dedede; border-left: 1px solid #dedede;}
.mini_stats_graph TD span {width:100%;white-space:nowrap;}
.mini_stats_graph2 {margin:1em 5px; width:48%; word-spacing:0;}
.mini_stats_graph2 TD {text-align:left; vertical-align: middle; padding: 0 2px; height:20px;}
.mini_stats_graph2 TD div {float: left; height:8px; font-size:1px; line-height:1px;  margin: 2px 5px 0 0; border-bottom: 1px solid #000000;}
.mini_stats_graph .alt TD {border-top:1px solid #444; letter-spacing:-1.6px;}
h3 {font-size:1.3em; position:relative; bottom: -10px; margin-top:-10px;}
#latest th {text-align:center;}
.widefat td {padding:0.5em;}
.widefat td.num {text-align:center;}
.wrap {padding:0 3em 2em 3em;}
";

/*  stop editing here  */

if (isset($_GET[$mini_stats['trigger']])) {add_action('bb_init','mini_stats_init');}

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 	
	if (isset($_GET['plugin']) && $_GET['plugin']=="mini_stats_admin") { // load entire core only when needed
	@require_once("mini-stats-init.php");
	} 
	function mini_stats_admin_page() {global $bb_submenu; $bb_submenu['content.php'][] = array(__('Mini Stats'), 'administrate', 'mini_stats_admin');}
	add_action( 'bb_admin_menu_generator', 'mini_stats_admin_page',200);	// try to be last menu feature		
}

// statistics hooks
if (!$mini_stats['disable_footer']) {
add_action('bb_head','mini_stats_header',100); 
if ($mini_stats['statistics_in_footer']) {add_action('bb_foot','mini_stats',200);}
add_action( 'bb_new_post','mini_stats_update');
add_action( 'bb_delete_post','mini_stats_update');
add_action('register_user','mini_stats_update');
add_action('user_register','mini_stats_update');
// add_action('profile_edited', 'mini_stats_update');	// unfortunately no clean way to hook cap changes if set inactive
}

function mini_stats_header() {global $mini_stats;  echo '<style type="text/css">'.$mini_stats['style'].'</style>';}

function mini_stats_init() {global $mini_stats; if ($mini_stats['level']=='read' || bb_current_user_can($mini_stats['level'])) {@require_once("mini-stats-init.php");}}

function mini_stats($display=0) {
global $mini_stats, $mini_stats_done, $mini_track_statistics_done; 
if (!empty($mini_stats_done) || !empty($mini_track_statistics_done)) {return;} $mini_stats_done=true;   // only run once if manually called
if ($mini_stats['statistics_only_on_front_page'] && !is_front()) {return;}
if (empty($display)) {
	if ($mini_stats['statistics_in_footer']) {$display=1;}
	if ($mini_stats['show_new_members']) {$display=2;}
	if ($display && $mini_stats['statistics_only_on_front_page'] && !is_front()) {$display=0;}
}
if ($display) {
$results=bb_get_option('mini_stats'); if (empty($results)) {$results=mini_stats_update();}
$months=ceil((time()-strtotime($results->days)) / (3600 * 24 * 30));
$output="<div class='mini_stats'>";
$output.=" <a href='".bb_get_option('uri')."?".$mini_stats['trigger']."'><span class='mini_stats_stats'>";
$output.=" <span class='mini_stats_num'>".bb_number_format_i18n($results->posts)."</span></a> ".__('posts in'); 
$output.=" <span class='mini_stats_num'>".bb_number_format_i18n($results->topics)."</span> ".__('topics over'); 
$output.=" <span class='mini_stats_num'>$months</span> ".__('months by'); 
$output.=" <span class='mini_stats_users mini_stats_num'>".bb_number_format_i18n($results->active)."</span> ".__('of')." <span class='mini_stats_num'>".bb_number_format_i18n($results->members)."</span> ".__('members.'); 
$output.=" </span>";
if ($display>1) {
$output.="<span class='mini_stats_wrap mini_stats_member'>";
if (!empty($results->latest)) {$output.=__('Latest:'); $uri=bb_get_option('uri')."profile.php?id="; foreach ($results->latest as $key=>$value) {$output.=" <a href='$uri$key'>$value</a>, ";}}
$output=trim($output,", ")."</span>";
}
$output.="</div> ";
echo $output;
}
}

function mini_stats_update($x="") {
global $bbdb, $mini_stats;
$query="SELECT count(*) as posts, count(distinct topic_id) as topics, count(distinct poster_id) as active, min(post_time) as days FROM $bbdb->posts WHERE post_status=0";
$results=$bbdb->get_results($query);
$usertable=$bbdb->users; $usermeta=$bbdb->usermeta;
$base="FROM $usertable as t1 LEFT JOIN $usermeta as t2 on t1.ID=t2.user_id WHERE user_status=0 AND (meta_key='".$bbdb->prefix.'capabilities'."' AND NOT (meta_value LIKE '%inactive%' OR meta_value LIKE '%blocked%'))";
$query="SELECT user_login,ID $base ORDER BY user_registered DESC LIMIT ".$mini_stats['new_members'];
$results2=$bbdb->get_results($query);
$results2=array_reverse($results2);
foreach ($results2 as $key=>$value) {$results[0]->latest[$value->ID]=$value->user_login;}
$query="SELECT count(*) as members $base";
$results2=$bbdb->get_results($query);
$results[0]->members=$results2[0]->members;
bb_update_option('mini_stats',$results[0]);
return $results[0];
}

?>