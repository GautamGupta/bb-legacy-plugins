<?php

add_action('bb_admin_head','tag_history_header'); 
function tag_history_header() {echo '<style type="text/css">table.widefat tr th, table.widefat tr td{text-align:left;} .count{font-weight:normal;font-size:12px;} .timetitle{cursor:help;} .page-numbers{margin:0 2px;}</style>';}

add_action('bb_get_option_page_topics','tag_history_per_page',250);
function tag_history_per_page($per_page) {return 100;}

function tag_history() {
if (!bb_current_user_can('administrate')) {die;}

echo '<h2>Tag History</h2>';
global $bbdb, $tag, $topic, $page;  // $bbdb->show_errors();  error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
$bb1=defined('BACKPRESS_PATH');
$where=$reset=$for=''; $user_id=$tag_id=$topic_id=0;
$per_page=100; $offset=(intval($page) -1)*$per_page;
$base=bb_get_option('path').'bb-admin/'.bb_get_admin_tab_link('tag_history');

if (isset($_GET['user_id'])) {$user_id=intval($_GET['user_id']); $where=" user_id=$user_id ";}
elseif (isset($_GET['tag_id'])) {$tag_id=intval($_GET['tag_id']); $where=$bb1 ? " term_taxonomy_id=$tag_id " : " tag_id=$tag_id ";}
elseif (isset($_GET['topic_id'])) {$topic_id=intval($_GET['topic_id']); $where=$bb1 ? " object_id=$topic_id " : " topic_id=$topic_id ";}
if ($where) {$where="WHERE $where"; $reset=' &nbsp; [<a href="'.$base.'">RESET</a>]';}

$query=$bb1?"SELECT @rn:=@rn+1 as rownum,user_id,object_id as topic_id,term_taxonomy_id as tag_id,0 as tagged_on 
FROM $bbdb->term_relationships,(SELECT @rn:=0) as rinit $where ORDER BY rownum DESC LIMIT $offset,$per_page"  // tagged_id tag_id user_id topic_id tagged_on
:"SELECT * FROM $bbdb->tagged $where ORDER BY tagged_on DESC LIMIT $offset,$per_page";  // tagged_id tag_id user_id topic_id tagged_on
$history=$bbdb->get_results($query);
if (empty($history)) {echo '<p>No tags found?</p>'; return;} 
$total=$bbdb->get_var($bb1?"SELECT count(*) FROM $bbdb->term_relationships $where":"SELECT count(*) FROM $bbdb->tagged $where");

foreach ($history as $item) {$users[$item->user_id]=$item->user_id; $tags[$item->tag_id]=$item->tag_id; $topics[$item->topic_id]->topic_id=$item->topic_id;} 
bb_cache_users($users);
$query=$bb1?"SELECT user_id,count(*) as tag_count FROM $bbdb->term_relationships WHERE user_id IN (".implode(',',$users).") GROUP BY user_id"
:"SELECT user_id,count(*) as tag_count FROM $bbdb->tagged WHERE user_id IN (".implode(',',$users).") GROUP BY user_id";
$results=$bbdb->get_results($query);
foreach ($results as $item) {$user_count[$item->user_id]=$item->tag_count;}
bb_cache_post_topics($topics);
$query=$bb1?"SELECT t1.term_id as tag_id, name as raw_tag, count as tag_count FROM $bbdb->terms as t1 LEFT JOIN $bbdb->term_taxonomy as t2 ON t1.term_id=t2.term_id WHERE t1.term_id IN (".implode(',',$tags).")"
:"SELECT * FROM $bbdb->tags WHERE tag_id IN (".implode(',',$tags).")";   // tag_id tag raw_tag tag_count
$results=$bbdb->get_results($query); 
unset($tags); foreach ($results as $item) {$tags[$item->tag_id]=$item;}		
$count=$bb1?$bbdb->get_row("SELECT count(DISTINCT term_taxonomy_id) as tags,count(DISTINCT user_id) as users,count(DISTINCT object_id) as topics FROM $bbdb->term_relationships $where")
:$bbdb->get_row("SELECT count(DISTINCT tag_id) as tags,count(DISTINCT user_id) as users,count(DISTINCT topic_id) as topics FROM $bbdb->tagged $where");

if ($user_id) {$for=' for user <span class="alt"><i>'.get_user_name($user_id).'</i></span> ';}
elseif ($tag_id) {$for=' for tag <span class="alt"><i>'.$tags[$tag_id]->raw_tag.'</i></span> ';}
elseif ($topic_id) {$for=' for topic <span class="alt"><i>'.get_topic_title($topic_id).'</i></span> ';}

echo $pagelinks="<p style='clear:left'>[ ".(($total>$per_page) ? "showing ".(($page-1)*$per_page+1)." - ".(($total<$page*$per_page) ? $total : $page*$per_page)." of " : "")."$total tagging history $for] ".'<span style="padding-left:1em">'.get_page_number_links( $page, $total )."</span>$reset</p>";
echo "<table class='widefat'><thead><tr><th>Tag <span class='count'>($count->tags)</span></th><th>By <span class='count'>($count->users)</span></th><th width='50%'>Topic <span class='count'>($count->topics)</span></th>".($bb1?'':'<th>Date</th>')."</tr></thead>";

foreach ($history as $key=>$item) {
$tag=$tags[$item->tag_id]; $topic=get_topic($item->topic_id);
echo '<tr'.($key%2 ? ' class="alt"' : '').'>';
echo '<td nowrap><a href="'.bb_get_tag_link().'">'.$tag->raw_tag.'</a> (<a href="'.add_query_arg('tag_id',$tag->tag_id,$base).'">'.$tag->tag_count.'</a>)</td>';
echo '<td nowrap><a href="'.attribute_escape(get_user_profile_link( $item->user_id) ).'">'.get_user_name( $item->user_id ).'</a> (<a href="'.add_query_arg('user_id',$item->user_id,$base).'">'.$user_count[$item->user_id].'</a>)</td>';
echo '<td '; topic_class(); echo '>'; bb_topic_labels(); ?> <a href="<?php topic_link(); ?>"><?php topic_title();  echo '</a> (<a href="'.add_query_arg('topic_id',$topic->topic_id,$base).'">'.$topic->tag_count.'</a>)</td>';
if (!$bb1) {echo '<td nowrap><span class="timetitle" title="'.date('r',strtotime($item->tagged_on)).'">'.sprintf( __('%s ago'),bb_since($item->tagged_on)).'</span></td>';}
echo '</tr>';
}

echo '</table>'.(($total>15) ? $pagelinks : '<br />').'<br />';
}

?>