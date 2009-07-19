<?php 
/*
Plugin Name: Leaderboard
Plugin URI: http://bbpress.org/plugins/topic/leaderboard
Description: Shows the most active users across bbPress and WordPress within different time periods. Customizable templates. 
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.3
*/

$leaderboard['role']="read";				 // level needed to see list, ie. read, participate, moderate, administrate
$leaderboard['add_view']=true;				 // add to list of views

$leaderboard['per_page']=20;				 // how many users listed per page
$leaderboard['maximum']['default']=100;			 // maximum number of users to be listed overall
$leaderboard['maximum']['sidebar']=15;			 // maximum number of users to be listed for specific templates

$leaderboard['additional_bbpress']="";			 // comma seperated list of additional bbpress post tables to include,   ie.  bb_posts
$leaderboard['additional_wordpress']="";	 	// comma seperated list of additional wordpress post tables to include,   ie.  wp_comments

$leaderboard['cache']=false;					 // caching for active websites, set true to turn on, false for off
$leaderboard['cache_dir']="/home/example/leaderboard/";	 // make this above the web-root and chmod 777
$leaderboard['cache_time']=300;					 // in seconds, default 5 minutes

$leaderboard['css']="
	ul.leaderboard {list-style: none; list-style-position: outside; padding: 0; margin: 0; text-indent: 0;}
	.leaderboard li {display:inline; vertical-align:middle; margin-right: 3px;}
	table.leaderboard td a img {border:0; text-decoration:none; vertical-align:middle; margin-right:10px;}
";

 /*       stop editing here         */

add_action('bb_init','leaderboard_init',999);

function leaderboard_init() {
global $leaderboard;
if (isset($_REQUEST['leaderboard'])) {leaderboard();}
elseif ($leaderboard['add_view']) {
	if ($leaderboard['role'] && $leaderboard['role']!="read" && !bb_current_user_can($leaderboard['role'])) {return;}
	add_action( 'bb_custom_view', 'leaderboard_view',300);
	bb_register_view("leaderboard","Leaderboard",array('started' => '>0','append_meta'=>false,'sticky'=>false,'topic_status'=>'all','order_by'=>1,'per_page'=>1));	
}
}

function leaderboard_view($view="") {	
	if (!empty($view) && $view!='leaderboard')  {return;}
	global $leaderboard,$page;	
	if ($leaderboard['role'] && $leaderboard['role']!="read" && !bb_current_user_can($leaderboard['role'])) {return;}
	$days=isset($_REQUEST['days']) ? intval($_REQUEST['days']) : 0; if ($days<0) {$days=0;} elseif ($days>9999) {$days=9999;}
	$forums=isset($_REQUEST['forums']) ? $_REQUEST['forums'] : 0; if (is_array($forums)) {foreach ($forums as $key=>$value) {$forums[$key]=intval($value);}} else {$forums=intval($forums);}
	add_action('bb_head','leaderboard_head');
	bb_send_headers();
	bb_get_header(); 
	leaderboard("view",$days,$forums,$page);	
	bb_get_footer(); 
	exit;
}

function leaderboard_head() {global $leaderboard; echo '<style type="text/css">'.$leaderboard['css'].'</style>';
}

function leaderboard($template="sidebar",$days=0,$forums=0,$tpage=0) {
	global $leaderboard,$leaders,$bb,$bbdb,$page,$total,$view_count;
	
	if (!empty($forums)) {
		if (is_array($forums)) {foreach ($forums as $key=>$value) {$forums[$key]=intval($value); if ($value==0) {unset($forums[$key]);}}} 
		else {$forums=intval($forums);}
	}		
	
	if ($leaderboard['cache']) {	
		$filename=$leaderboard['cache_dir']."$template-$days-$forums-$tpage.html"; 
		@$filemtime=filemtime($filename);
		if ($filemtime && intval(time()/$leaderboard['cache_time'])==intval($filemtime/$leaderboard['cache_time'])) {readfile($filename); return;}   		
	}
		
	if (isset($leaderboard['maximum'][$template])) {$maximum=$total=$view_count=$limit=$leaderboard['maximum'][$template]; } 
	else {$maximum=$leaderboard['maximum']['default']; $total=$view_count=$limit=$leaderboard['per_page'];}
	$maxpage=intval($maximum/$limit); if ($maxpage<1) {$maxpage=1;}	
	$page = intval($page); if ($page<1) {$page=1;} elseif ($page>$maxpage) {$page=$maxpage;} 
	$offset = ($page -1) * $limit;	 	
	$post_count_plus=($days==0 && empty($forums) && function_exists('post_count_plus')); 
	$bbRestrict=$wpRestrict="";
	
	if ($days) {
	$gmt_offset=intval(bb_get_option("gmt_offset"))*3600;
	$time=strtotime(gmdate('Y-m-d',time()+$gmt_offset)." 23:59:59 +0000")-$gmt_offset;   // midnight of today's date in GMT (before offset)
	$endtime=$time; 
	$starttime=1+$endtime-($days*24*3600); 		 //  days backwards plus one second to make it midnight next day

	$mysql_starttime=gmdate("Y-m-d H:i:s",$starttime); 	
	$mysql_endtime=gmdate("Y-m-d H:i:s",$endtime);
	$bbRestrict.=" AND post_time>='$mysql_starttime' ";		 	// AND post_time<='$mysql_endtime' ";
	$wpRestrict.=" AND comment_date_gmt>='$mysql_starttime' ";	 // AND comment_date_gmt<='$mysql_endtime' ";		
	} elseif ($post_count_plus) {
		$query="SELECT user_id FROM $bbdb->usermeta WHERE meta_key='post_count' ORDER BY (meta_value+0) DESC,user_id ASC LIMIT $offset,$limit";
		$ids=$bbdb->get_col($query);
		if (empty($ids)) {$ids="0";} 	// short-circuit on purpose
		$ids=implode($ids,",");
		$bbRestrict.=" AND poster_id IN ($ids) ";
		$wpRestrict.=" AND user_id IN ($ids) ";
	}
		
	if (!empty($forums)) {$bbRestrict.=" AND forum_id IN (".implode(",",(array) $forums).") ";}	
			
	if (!empty($forums) || (empty($bb->wp_table_prefix) && empty($leaderboard['additional_wordpress']) && empty($leaderboard['additional_bbpress'])) ) {	
		$select="SELECT poster_id as ID,count(post_status) as post_count,0 as comment_count,count(post_status) as total_count ";
		$query="FROM $bbdb->posts WHERE post_status = 0 $bbRestrict GROUP BY poster_id";
	} else {	
		if (!empty($leaderboard['additional_bbpress'])) {
			(array) $add_bbpress=explode(",",trim($leaderboard['additional_bbpress'],", "));
			array_walk($add_bbpress,"trim");				
		}
		if (!empty($leaderboard['additional_wordpress'])) {
			(array) $add_wordpress=explode(",",trim($leaderboard['additional_wordpress'],", "));
			array_walk($add_wordpress,"trim");			
	
		}				
		$add_bbpress[]=$bbdb->posts;  $add_bbpress=array_flip($add_bbpress); $add_bbpress=array_keys($add_bbpress);
		$add_wordpress[]=$bb->wp_table_prefix."comments"; $add_wordpress=array_flip($add_wordpress); $add_wordpress=array_keys($add_wordpress);
						
		$user_id=""; $meta_value=""; $from=""; $tcount=""; $comments=""; $posts="";
		foreach ($add_bbpress as $key=>$bbpress) {
			$user_id.="bb$key.poster_id,"; 
			$posts.="COALESCE(bb$key.pcount,0)+";
			if ($key>0) {$from.=" LEFT JOIN ";} 
			$from.=" (SELECT poster_id,  count(post_status) as pcount FROM $bbpress WHERE post_status=0 $bbRestrict GROUP BY poster_id) as bb$key ";
			if ($key>0) {$from.=" ON bb0.poster_id=bb$key.poster_id ";}
		}
		$tcount=$posts; 
		$meta_value=trim($posts,"+ ")." AS post_count, ";
		
		foreach ($add_wordpress as $key=>$wordpress) {
			$user_id.="wp$key.user_id,"; 
			$comments.="COALESCE(wp$key.ccount,0)+";
			$from.=" LEFT JOIN "; 
			$from.=" (SELECT user_id,count(comment_approved) as ccount FROM $wordpress WHERE comment_approved=1 $wpRestrict GROUP BY user_id) as wp$key ";
			$from.=" ON bb0.poster_id=wp$key.user_id "; 			
		}
		$tcount.=trim($comments,"+ ");
		$meta_value.=trim($comments,"+ ")." AS comment_count";
		
		$user_id=trim($user_id,", "); 
		$select="SELECT COALESCE($user_id) as ID,$meta_value, $tcount as total_count ";
		$query="FROM $from";	
	}

	$leaders=array();	
	if ($template!="view" || $offset+$leaderboard['per_page']<=$maximum) {
		
		if ($post_count_plus) {$toffset=0;} else {$toffset=$offset;}
		$restrict=" ORDER BY total_count DESC,ID ASC LIMIT $toffset,$limit";	
		$leaders=$bbdb->get_results($select.$query.$restrict);	
		$count=count($leaders);	
		if ($count<$leaderboard['per_page']) {$total=$view_count=$offset+$count;} else {$total=$view_count=$maximum;}
	
		/*
		if ($page>1 || $offset>0 || ($page==1 && $count>=$leaderboard['per_page'] && $maximum>$leaderboard['per_page'])) {
		 	$total=$view_count=intval($bbdb->get_var("SELECT COUNT(*) ".$query));  	  // this is killing performance for obvious reasons 
		} else {$total=$view_count=$count;}
		*/
			
		if (!empty($leaders)) {
			$ids=array(); foreach ($leaders as $leader) {$ids[$leader->ID]=$leader->ID;}		
			if (isset($ids)) {bb_cache_users($ids);}	// return; ?
			$ids=implode(",",$ids);
			
			if (empty($leaderboard['additional_bbpress'])) {
				$select="SELECT poster_id as ID,count(post_status) as topic_count ";
				$query="FROM $bbdb->posts WHERE post_status = 0 $bbRestrict AND post_position=1 AND poster_id IN ($ids) GROUP BY poster_id";
			} else {			
				$user_id=""; $from=""; $posts="";
				foreach ($add_bbpress as $key=>$bbpress) {
					$user_id.="bb$key.poster_id,"; 
					$posts.="COALESCE(bb$key.pcount,0)+";
					if ($key>0) {$from.=" LEFT JOIN ";} 
					$from.=" (SELECT poster_id,  count(post_status) as pcount FROM $bbpress WHERE post_status=0 $bbRestrict AND post_position=1 AND poster_id IN ($ids) GROUP BY poster_id) as bb$key ";
					if ($key>0) {$from.=" ON bb0.poster_id=bb$key.poster_id ";}
				}				
				$meta_value=trim($posts,"+ ")." AS topic_count ";
				$user_id=trim($user_id,", "); 
				$select="SELECT COALESCE($user_id) as ID,$meta_value ";
				$query="FROM $from";	
			}
			
			$results=$bbdb->get_results($select.$query);		
				foreach ($leaders as $key=>$leader) {
				if (!empty($results)) {
					foreach ($results as $result) {				
						if ($leader->ID==$result->ID) {$leaders[$key]->topic_count=$result->topic_count; $leaders[$key]->post_count-=$leaders[$key]->topic_count; break;}
					} 
				} if (!isset($leaders[$key]->topic_count)) {$leaders[$key]->topic_count=0;}			
			}								
		}
		
	}
@require(rtrim(dirname(__FILE__),' /\\')."/$template.php");
}

function leaderboard_cache($template="sidebar",$days=0,$forums=0,& $output,$page=0) {
global $leaderboard;
	if (!$leaderboard['cache']) {return;}
	if (is_array($forums)) {foreach ($forums as $key=>$value) {$forums[$key]=intval($value);}} else {$forums=intval($forums);}
	$forums=implode("-",(array) $forums);
	$filename=$leaderboard['cache_dir']."$template-$days-$forums-$page.html"; 
	$current=get_current_user();  if (!($current && !in_array($current,array("nobody","httpd","apache","root")) && strpos(__FILE__,$current))) {$current="";}
	$x=posix_getuid (); if (0 == $x && $current) {$org_uid = posix_get_uid(); $pw_info = posix_getpwnam ($current); $uid = $pw_info["uid"];  posix_setuid ($uid);}
	$fh=@fopen($filename,"wb"); if ($fh) {@fwrite($fh,$output); fclose($fh);}
	if ($org_uid) {posix_setuid($org_uid);}
}

?>