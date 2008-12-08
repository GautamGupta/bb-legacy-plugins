<?php		/* Super Search for bbPress  */ 

// error_reporting(E_ALL);
// global $SSdebug; $SSdebug=true;
 
if(isset($_GET['SuperSearchUsers'])) {super_search_users();}
else {
add_action('bb_head', 'super_search_add_header');
super_search_request();
bb_send_headers();
bb_get_header(); 
super_search_form();
super_search_results();
bb_get_footer(); 
exit;
}

function super_search_request() {
global $bbdb,$SuperSearch,$SSrequest,$SSdebug;

if (!bb_current_user_can('administrate')) {$SSdebug=false;}

$_REQUEST = super_search_safety($_REQUEST);

if (isset($_REQUEST['q']) && !isset($_REQUEST['search'])) {$_REQUEST['search']=$_REQUEST['q'];}

$SSrequest['advanced']=isset($_REQUEST['advanced']) ? intval($_REQUEST['advanced']) : 0;

$submitted=isset($_REQUEST['advanced']) ? true : false;  

// [fieldname] = label / type / extra attributes / value default / values allowed
$SuperSearch['search']=array('Find','text','size="50" style="font-size: small; width: 50%;"','','*');
$SuperSearch['submit']=array('','submit','class="buttonLarge"','Search','Search');
$SuperSearch['reset']=array('','reset','class="buttonLarge" onclick="document.location.replace('."'search.php'".');"','Reset','Reset');
$SuperSearch['highlight']=array('Highlight Results','checkbox','class="bigcheck"','1','1|1');
$SuperSearch['posts']=array('Show Posts','checkbox','class="bigcheck"','1','1|1');
$SuperSearch['exact']=array('Exact Words','checkbox','class="bigcheck"','1','1|1');
$SuperSearch['case']=array('Match Case','checkbox','class="bigcheck"','','1|1');
$SuperSearch['regex']=array('Use RegEx','checkbox','class="bigcheck"','','1|1');
$SuperSearch['advanced']=array('','hidden','',$SSrequest['advanced'],'1');
// $SuperSearch['where']=array('Look in','radio','class="bigcheck"','2','0|Forums','1|Blogs','2|Both');
$SuperSearch['forums']=array('','select',' id="SSforums" style="width:150px" ','0','0|- Search All Forums -');
$SuperSearch['users']=array('By user','select',' id="SSusers" onMousedown="SuperSearch_loadusers()" style="width:150px" ','0','0|- Any -');
$SuperSearch['located']=array('Located','select','','0','0|anywhere in topic','1|started topic','2|replied to topic','3|only in titles','4|only in posts');
$SuperSearch['age']=array('From','select','','999999','999999|anytime','1|yesterday','7|a week ago','14|2 weeks ago','31|one month ago','62|2 months ago','93|3 months ago','186|6 months ago','366|one year ago');
$SuperSearch['direction']=array('','select','','0','0|and newer ','1|and older');
$SuperSearch['maxcount']=array('Results','select','','9999999','9999999|all','5|5','10|10','25|25','50|50','100|100');
$SuperSearch['sort']=array('Sort By','select','','date','date|Date','freshness|Freshness','posts|Posts','title|Title','user|User');
			if (function_exists('get_view_count')) {$SuperSearch['sort'][]='views|Views';}
$SuperSearch['order']=array('','select','','0','0|Descending','1|Ascending');

foreach ($SuperSearch as $formvar=>$value) {		// sanitize form values to allowed values and save request
if (!empty($_REQUEST[$formvar])) {
	$fieldcount=count($SuperSearch[$formvar])-5;	
	if ($formvar=='forums') {
		$forums = $_REQUEST[$formvar];
		if (is_array($forums)) {
			array_walk($forums,intval);		
			$SSrequest[$formvar]=trim(implode(",",$forums),", ");
		} else {$SSrequest[$formvar]=intval($forums);}
	}
	elseif ($formvar=='users') {
		$SSrequest[$formvar] = $user_id = intval($_REQUEST[$formvar]);
		$user_name=$bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID=$user_id LIMIT 1");
		$SuperSearch['users'][]="$user_id|$user_name";	
	} elseif ($fieldcount>0 || strpos($SuperSearch[$formvar][4],'|')!==false) {
		for ($i=0; $i<=$fieldcount; $i++) {$field1=explode("|",$SuperSearch[$formvar][4+$i]); $fields[$field1[0]]=$field[1];}
 		if (!array_key_exists($_REQUEST[$formvar],$fields)) {
 			$SSrequest[$formvar] =  $SuperSearch[$formvar][3];
 		} else {
 			$SSrequest[$formvar] = $_REQUEST[$formvar];
 		}
	} elseif ($SuperSearch[$formvar][4]!="*"  && $SuperSearch[$formvar][4]!=$_REQUEST[$formvar]) {
		$SSrequest[$formvar] =  $SuperSearch[$formvar][3];
	} else {
		$SSrequest[$formvar] = $_REQUEST[$formvar]; 		
	}	
} else { 
	if ($submitted && $SuperSearch[$formvar][1]=="checkbox") { 
		$SSrequest[$formvar] = "";
	} else {
		$SSrequest[$formvar] =  $SuperSearch[$formvar][3];
	}
}
// echo $formvar." - ".$$formvar."<br>";
}
}

function super_search_safety($string) {
$keys=array_keys($string);
foreach ($keys as $key) { 
	if (is_array($string[$key])) {super_search_safety($string[$key]);}
	else {$string[$key]=substr(stripslashes($string[$key]),0,64);}
}
return $string;
}

function super_search_form() {require(rtrim(dirname(__FILE__),' /\\')."/form.php");}

function super_search_results() {
global $bbdb,$SuperSeach, $SSrequest,$SSdebug,$topics,$topic,$stickies,$page,$total,$bb_post;

if (empty($SSrequest['search'])) {require(rtrim(dirname(__FILE__),' /\\')."/instructions.php"); return;}

 $ss=trim($SSrequest['search']); 

remove_filter('bb_template','bb_ts_add_dropdown',100,2);  
if (!$page && isset($_REQUEST['page'])) {$page=intval($_REQUEST['page']);}
$topic_views=function_exists('get_view_count') ? true : false;
$limit=bb_get_option('page_topics'); 

super_search_find($ss); 

 if (empty($topics) && empty($stickies)) {  	// query submitted but no results,  give instructions, suggestions 
	echo '<div class="indent"><font color="red">'.__('No results found.').'</font></div>
		<div class="SSbreak"></div>';
	require(rtrim(dirname(__FILE__),' /\\')."/instructions.php");
return;
}
 ?>
<div class="indent"><font color="green"><?php echo $total." ";  _e('results found') ?>
, showing <?php echo 1+($page-1)*$limit." - "; echo ($page*$limit>$total) ? $total : $page*$limit; ?></font></div>

<table id="latest">
<tr>
	<th width="50%"><?php _e('Topic'); ?></th>
<?php if ($topic_views) { ?>	<th><?php _e('Views'); ?></th><?php } ?>
	<th><?php _e('Posts'); ?></th>
	<th nowrap><?php _e('Last Poster'); ?></th>
	<th><?php _e('Freshness'); ?></th>
</tr>

<?php 
$last_topic_id=0;
$args = _bb_parse_time_function_args( $args );
remove_filter('post_text','add_signature_to_post',5);	// we need real post_text filter but somethings should not execute
add_filter('topic_title','super_search_highlight',999);

if ( $topics ) : foreach ( $topics as $topic ) : 				// start loop
if (!$SSrequest['posts'] || $topic->topic_id!=$last_topic_id) {
?>
<tr <?php if ($SSrequest['posts'] && !empty($topic->post_id)) {echo "class=alt";} else {topic_class();} ?>>	
	<td><?php if (function_exists("bb_topic_labels")) {bb_topic_labels();} elseif ($topic->topic_sticky) {if (function_exists("bb_fancy_titles")) {echo bb_fancy_titles(__('Sticky:'));} else {_e('Sticky:');}} ?> 
	<?php
	if ($SSrequest['posts']) {$link=get_topic_link();} else {$link=get_post_link($topic->post_id);}
	// $title=super_search_highlight(get_topic_title());	// apply_filters( 'get_topic_title', $topic->topic_title, $topic->topic_id)
	echo '<a href="'.$link.'">'; topic_title(); echo '</a></td>';
	if ($topic_views) { ?><td class="num"><?php echo $topic->views; ?></td><?php } 
	?>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php  topic_last_poster();  /* else { $user = bb_get_user($topic->poster_id); echo $user->user_login;} */ ?></td>
	<td class="num"><small>
	<?php // if ($SSrequest['posts'] || empty($topic->post_id)) { ?>
	<span class=timetitle title="<?php echo date("r",strtotime($topic->topic_time)); ?>"><?php topic_time(); ?></span>
	<?php /* } else {  ?>
	<span class=timetitle title="<?php echo date("r",strtotime($topic->post_time)); ?>"><?php echo _bb_time_function_return(apply_filters( 'bb_get_post_time', $topic->post_time, $args ), $args ); ?></span>
	<?php } */ ?>	
	</small></td>
</tr>
<?php } if ($SSrequest['posts'] && !empty($topic->post_id)) {  $bb_post=$topic; // cache post for plugins ?>
<tr><td colspan=5> 
<?php
$poster='<a href="' . attribute_escape( get_user_profile_link( $topic->poster_id ) ) . '"><strong>'. get_post_author($topic->post_id) .'</strong></a>';
echo "<small><fieldset><legend>$poster :: post # $topic->post_position</legend>";
$post_text=super_search_highlight(super_search_show_context(apply_filters('post_text', $topic->post_text, $topic->post_id)));	//  I really should just strip bbcode tags here, this is a lazy/slow workaround
echo '<a style="display:inline;text-decoration:none;border:0;" href="'.get_post_link($topic->post_id).'">'.$post_text.'</a>'; ?></fieldset></small>
</td></tr>
<?php 
}
$last_topic_id=$topic->topic_id;
endforeach; endif; 
?>
</table>

<div class=nav>
<?php  
	echo paginate_links( array(	
	'base' => preg_replace('![&?]page=[0-9]+!', '',$_SERVER['REQUEST_URI'].'%_%'),
	'format' => '&page=%#%','total' => ceil($total/bb_get_option('page_topics')),'current' => $page,'add_args' => array() ));
?>
</div>

<?php 
if ($SSdebug) {echo "$bbdb->num_queries queries";}
}

function SSinput($name,$default="",$offset=0) {
global $SuperSearch, $SSrequest;

if ($name=='forums') {echo ' &nbsp; '.super_search_forum_list(); return;}

if ($name=='simple') {
echo ' <input name="advanced" type="hidden" value="'.($SSrequest['advanced'] ? 1: 0).'"> 
<a  id="SS'.$name.'" onfocus="this.blur()" href="javascript:SSadvanced();">'.(($SSrequest['advanced']) ? "Simple Search" : "<b>Advanced Search</b>").'</a>';
return;
}

if (!isset($SuperSearch[$name])) {return;}

$output=""; // get ready to build resulting field
$options=count($SuperSearch[$name])-5;	// are there multiple options?
$array=($options && ($SuperSearch[$name][1]=="checkbox" || strpos($SuperSearch[$name][2],"multiple"))) ? '[]' : '';

// label before item, if any, except checkboxes
if ($offset==0 && $SuperSearch[$name][0] && $SuperSearch[$name][1]!="checkbox") {$output="<b>".__($SuperSearch[$name][0]).":</b>&nbsp;";}

if (in_array($SuperSearch[$name][1],array("text","submit","reset","checkbox","radio","hidden"))) {
	$output.="<span class=nowrap>";
	$output.= '<input type="'.$SuperSearch[$name][1].'" ';		//  style types  test / submit / checkbox / radio 
	$output.= ' id="SS'.$name.'" name="'.$name.$array.'" '.$SuperSearch[$name][2];	 // add name and  id and any extra attributes
}
elseif ($SuperSearch[$name][1]=="select") {
	if ($offset==0) {$output.='<select name="'.$name.'" '.$SuperSearch[$name][2].'>';}
	$output.='<option ';
}

// check for passed options, if not check for default 			- todo: check against allowed values
if (in_array($SuperSearch[$name][1],array("checkbox","radio","select"))) {
		list($value,$label) = explode("|",$SuperSearch[$name][4+$offset]);		
		$output.=' value="'.addslashes($value).'" ';
}

if (isset($SSrequest[$name])) {	
	if (in_array($SuperSearch[$name][1],array("checkbox","radio","select"))) { 
		if ((is_array($SSrequest[$name]) && $value && in_array($value,$SSrequest[$name]))  || (!is_array($SSrequest[$name]) &&  $value==$SSrequest[$name])) {
			$output.= ($SuperSearch[$name][1]=="select") ? 'selected' : ' checked ';
		} 
	} else {$output.=' value="'.htmlentities(stripslashes($SSrequest[$name]), ENT_QUOTES).'" ';}		//search bar 		// todo: verifiy allowed values	
} else  {
	if ($value) {$output.=' value="'.addslashes($value).'" ';}
	else {$output.=' value="'.addslashes($SuperSearch[$name][3]).'" ';}

	if (!isset($_REQUEST['submit']) && $value && in_array($value,explode("|",$SuperSearch[$name][3]))) {$output.= ($SuperSearch[$name][1]=="select") ? 'selected' : ' checked ';}	
}

// print on success
if ($output) {
	if ($SuperSearch[$name][1]=="select") { 		
		$output.=">&nbsp;".$label."</option>";
		 if ($offset>=$options) {$output.="</select>&nbsp;&nbsp;";} // write option labels and close select if last one		
	} else {
		if ($label && $label!=$value) {$output.=">".$label;}
		else {$output.=">";}
	
	// label after item, if any, only for checkboxes
	if ($offset==0 && $SuperSearch[$name][0] && $SuperSearch[$name][1]=="checkbox") {$output.="<b>".__($SuperSearch[$name][0])."</b>&nbsp;&nbsp;";}
		
	$output.="&nbsp;</span>";
	}
echo $output;
}

// recursive for other radio/checkbox options
if ($offset==0 && $label && $options>0) {
	for ($i=1; $i<=$options; $i++) {
		SSinput($name,"",$i);
	} 	
}
}

function super_search_forum_list( $args = '' ) {
global $forum_id, $forum, $SuperSearch, $SSrequest;
	$old_global = $forum;			
	$defaults = array(
		'size'=>5, 
		'callback' => false, 
		'callback_args' => false, 
		name=> 'forums', 
		'id' => 'SSforums', 
		'tab' => 5, 
		'hierarchical' => 1, 
		'depth' => 0, 
		'child_of' => 0, 
		'disable_categories' => 1 
	);
	if ( $args && is_string($args) && false === strpos($args, '=') ) {$args = array( 'callback' => $args );}
	if ( 1 < func_num_args() ) {$args['callback_args'] = func_get_arg(1);}
	$args = wp_parse_args( $args, $defaults ); extract($args, EXTR_SKIP);  if ( !bb_forums( $args ) ) {return;}
	
	// $SuperSearch['forums']=array('','select',' id="SSforums" style="width:150px" ','0','0|- Search All Forums -');
	
	$selected=array_flip((array) explode(",",$SSrequest['forums']));  
	$option_selected = isset($selected[0]) ? ' selected="selected"' : '';
	$r = '<select name="' . $name . '[]" id="' . $id . '" tabindex="' . intval($tab). '" size="'.$size.'" multiple="multiple">' . "\n";	
	$r .= "\n" . '<option value="0" '.$option_selected.'>&nbsp;' . __('- Search All Forums -').'&nbsp;</option>' . "\n"; 
	$options = array();
	while ( $depth = bb_forum() ) :
		global $forum; // Globals + References = Pain
		if ( $disable_categories && isset($forum->forum_is_category) && $forum->forum_is_category ) {
			$options[] = array(
				'value' => 0,
				'display' => str_repeat( '&nbsp;&nbsp;&nbsp;', $depth - 1 ) . $forum->forum_name,
				'disabled' => true,
				'selected' => false
			);
			continue;
		}
		$_selected = false;
		if (isset($selected[$forum->forum_id])) {
			$_selected = true;			
		}
		$options[] = array(
			'value' => $forum->forum_id,
			'display' => str_repeat( '&nbsp;&nbsp;&nbsp;', $depth - 1 ) . $forum->forum_name,
			'disabled' => false,
			'selected' => $_selected
		);
	endwhile;
	
	foreach ($options as $option_index => $option_value) {		
		$option_disabled = $option_value['disabled'] ? ' disabled="disabled"' : '';
		$option_selected = $option_value['selected'] ? ' selected="selected"' : '';
		$r .= "\n" . '<option value="' . $option_value['value'] . '"' . $option_disabled . $option_selected . '>&nbsp;' . $option_value['display'] . '</option>' . "\n";
	}
	
	$forum = $old_global;
	$r .= '</select>' . "\n";
	return $r;
}

function super_search_find($ss) {
global $bbdb, $page, $topics, $topic, $posts, $total, $SSdebug,$SSwords,$SSrequest;

// todo:
// give admin option to limit number of words/phrases to search for
// limit searches per minute  and/or based on system load

$search=""; $SSwords=array();
 
if ($SSrequest['case']) {
$result=$bbdb->get_results("SHOW VARIABLES LIKE 'collation_connection'");
$bin=$result[0]->Value."_";
$bin=substr($bin,0,strpos($bin,'_'))."_bin";
$COLLATE=" COLLATE $bin ";
} else {$COLLATE="";}
 
if ($SSrequest['regex']) {
	$length=strlen(preg_replace('/[^a-zA-Z0-9\']/', '', $ss)); 
	if ($length>3) {
		$search=$plain=$ss;	 $search=addslashes($search);		
		$search=" ((post_text $COLLATE REGEXP '$search')) ";
	}
} else {

$search=" ".stripslashes($ss)." ";

$search=preg_replace('/[^a-zA-Z0-9-\s\*\+\-\'\"]/', '', $search); 		// strip punctuation - todo: check for regex option and leave alone, also: foreign languages?

preg_match_all('/( AND | NOT | OR | \+| \-|)"([^"]+?)"/smU',$search, $phrases); // [0]=full match [1]=prefix [2]=phrases without quotes

$search=str_replace($phrases[0],'',$search);	// 2nd pass for words (1st grabs phrases only)
preg_match_all('/( AND | NOT | OR | \+| \-|)([^ ]+?)/smU',$search, $words);

$search=""; // init build search query

for ($p=0; $p<2; $p++) {	// 1st pass phrases, 2nd pass words (I can't figure out a fast/safe way to merge the two arrays)
if ($p==0)  {$pass=$phrases;} else {$pass=$words;}

// echo "<hr><pre style='font-size:12px'>$p: ";print_r($pass); echo "</pre><hr>";

for ($i=0; $i<count($pass[2]); $i++) { 
$operator=trim($pass[1][$i]);
$term=addslashes(trim($pass[2][$i])); 

if (strlen($term)>2 || $operator) {	// exclude words under 3 characters unless an operator is specifically stated to force it
	if (substr($term,-1)=="*") {$term=substr($term,0,-1); $wildcard=true;} else {$wildcard=false;}
//	if ($search)  {$search.=str_replace(array("-","NOT","+","AND","OR"),array("AND NOT","AND NOT","AND","AND","OR"),$operator);}	// there is an inherit problem with OR needing to track the search before it - must fix
	if ($search)  {$search.=($operator=="OR") ? " OR " : " AND ";}	// there is an inherit problem with OR needing to track the search before it - must fix
	$NOT=(in_array($operator,array("NOT","-"))) ? " NOT " : "";	
	$search.=" (post_text $COLLATE$NOT LIKE '%".$term."%'";
	if (!empty($wildcard) || empty($SSrequest['exact'])) {$search.=") ";}
	else {$search.=" AND post_text $NOT REGEXP '[[:<:]]".$term."[[:>:]][^\']') ";}  // two-pass query to speed up eliminating partial words	
	if (empty($NOT)) {$SSwords[]=$term;}
	if ($operator=="OR") {$search="($search)";}
}
} // end loop through terms
} // end loop through passes

// $plain=preg_replace("/[^a-z0-9*\'\" ]+?/i","",preg_replace("/\(?post\_text[ ]+(".$COLLATE."[ ]+)?((NOT[ ]+)?LIKE[ ]+\'\%(.+?)\%\'\))?((AND[ ]+)?(NOT[ ]+)?(REGEXP[ ]+\'.+?\'[ ]+))?/","$3$4",$search));
// $plain=preg_replace("/[^a-z0-9*\'\" ]+?/i","",preg_replace("/\(?post\_text[ ]+(".$COLLATE."[ ]+)?((NOT[ ]+)?LIKE[ ]+\'\%(.+?)\%\'\))?((AND[ ]+)?(NOT[ ]+)?(REGEXP[ ]+\'.+?\'[ ]+))?/","$3$4",$search));
$plain=$search;
if ($COLLATE) {$plain=preg_replace("/$COLLATE/","",$plain);}
$plain=preg_replace("/post\_text[ ]+/","",$plain);
$plain=preg_replace("/LIKE[ ]+\'\%(.+?)\%\'/"," $1 ",$plain);
$plain=preg_replace("/AND[ ](NOT[ ]+)?+REGEXP[ ]+\'.+?\'\)/","",$plain);
$plain=preg_replace("/[^a-z0-9*\'\" ]+?/i","",$plain);
} // end regex check

echo '<h2 class="indent">'.__('Search for').': <font color="blue">'.wp_specialchars($plain).'</font></h2>';

if (!empty($search)) {

	// calculate date range
	if ($SSrequest['age']<9999) {
	$gmt_offset=intval(bb_get_option("gmt_offset"))*3600;
	$time=strtotime(gmdate('Y-m-d',time()+$gmt_offset)." 23:59:59 +0000")-$gmt_offset;   // midnight of today's date in GMT (before offset)
	$time=$time-($SSrequest['age']+2)*3600*24;
	$mysql_date=gmdate("Y-m-d H:i:s",$time); 	 // store mysql search range
	$direction=empty($SSrequest['direction']) ? ">=" : "<=";
	$search="($search) AND post_time$direction'$mysql_date' "; 
	}

// array('Located','select','','0','0|anywhere in topic','1|started topic','2|replied to topic','3|only in titles','4|only in posts');
if (empty($SSrequest['located']) || $SSrequest['located']==3) {$union=str_replace(array("post_text","post_time"),array("topic_title","topic_time"),$search);} 
if ($SSrequest['located']==1) {$search="($search) AND (post_position=1)";}
if ($SSrequest['located']==2) {$search="($search) AND (post_position>1)";}
if ($SSrequest['located']==3) {$search="";}
	
	$where = str_replace(array("forum_id","topic_id"),array("$bbdb->topics.forum_id","$bbdb->topics.topic_id"),apply_filters('get_latest_topics_where',"WHERE topic_status=0 "));
	
	if (!empty($SSrequest['forums'])) {
	$where.=" AND $bbdb->topics.forum_id IN (".$SSrequest['forums'].") ";
	}
	
	$limit = bb_get_option('page_topics'); if ($SSrequest['maxcount'] <$limit) {$limit=$SSrequest['maxcount'];}
	$page = $page ? intval($page) : 1;
	$offset = ($page-1)*$limit;	

	if (!empty($search)) {
	$query="SELECT
		     $bbdb->topics.topic_id as topic_id,topic_title,topic_slug,topic_poster,topic_poster_name,topic_last_poster,topic_last_poster_name,
		     topic_start_time,topic_time,$bbdb->topics.forum_id as forum_id,topic_status,topic_open,topic_last_post_id,topic_sticky,topic_posts,tag_count,			   
	                  post_id,poster_id,post_text,post_time,poster_ip,post_status,post_position	
		    FROM $bbdb->topics ";
	$query.=" LEFT JOIN $bbdb->posts ON $bbdb->topics.topic_id = $bbdb->posts.topic_id  ";
	$query.=" $where ";
	$query.=" AND post_status=0 ";
	if (!empty($SSrequest['users'])) {	
	$query.=" AND poster_id=".$SSrequest['users'];
	}
	$query.=" AND ($search) ";
	if (!empty($union)) {$query.=" UNION ";}
	}
	if (!empty($union)) {
	$query.="SELECT 
		     $bbdb->topics.topic_id as topic_id,topic_title,topic_slug,topic_poster,topic_poster_name,topic_last_poster,topic_last_poster_name,
		     topic_start_time,topic_time,$bbdb->topics.forum_id as forum_id,topic_status,topic_open,topic_last_post_id,topic_sticky,topic_posts,tag_count,			   
	                  NULL as post_id,NULL as poster_id,NULL as post_text,NULL as post_time,NULL as poster_ip,NULL as post_status,NULL as post_position
		     FROM $bbdb->topics ";
	$query.=" $where ";
	if (!empty($SSrequest['users']) && $SSrequest['located']==1) {	
	$query.=" AND topic_poster=".$SSrequest['users'];
	}
	$query.=" AND ($union) ";	
	// if (!empty($search)) {$query.=" AND topic_id NOT IN (t1.$bbdb->topics.topic_id) ";}
	}
		
	/*
	 ,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL 

	,NULL as post_id,NULL as $bbdb->posts.forum_id,NULL as $bbdb->posts.topic_id,NULL as poster_id,
		     NULL as post_text,NULL as post_time,NULL as poster_ip,NULL as post_status,NULL as post_position
	*/		     

	$query="($query) as t3 ";

	if ($SSrequest['sort']=='views' && function_exists('get_view_count')) {
		if (defined('BACKPRESS_PATH')) {
			 $query.=" LEFT JOIN $bbdb->meta ON t3.topic_id=$bbdb->meta.object_id AND $bbdb->meta.object_type='bb_topic' AND $bbdb->meta.meta_key='views' ";
		} else {
			$query.=" LEFT JOIN $bbdb->topicmeta ON t3.topic_id=$bbdb->topicmeta.topic_id AND $bbdb->topicmeta.meta_key='views' ";			
		}
	}
	
	if (!empty($SSrequest['users'])) {	
	$query.=" WHERE poster_id=".$SSrequest['users'];
	}

	// combine topics if show posts turned off		
	if (empty($SSrequest['posts'])) {$query.="GROUP BY t3.topic_id ";}

	// 'Sort By 'date','date|Date','views|Views','posts|Posts','title|Title','user|User'
	$sort=""; $restrict="";	
	if ($SSrequest['sort']=='date') {$sort="cast(t3.topic_id as UNSIGNED)";} // topic_start_time
	elseif ($SSrequest['sort']=='freshness') {$sort="topic_time";}
	elseif ($SSrequest['sort']=='posts') {$sort="topic_posts";}
	elseif ($SSrequest['sort']=='title') {$sort="topic_title";}
	elseif ($SSrequest['sort']=='user') {$sort="poster_id";}
	
	$restrict.=" ORDER BY $sort ";
	// sort direction
	if (empty($SSrequest['order'])) {$restrict.=" DESC ";} else {$restrict.=" ASC ";}
	// result count and page offset
	$restrict.=" LIMIT $limit OFFSET $offset ";

	if ($SSdebug) {echo "query: $query$restrict <br />"; $bbdb->show_errors();}

	$total  = $bbdb->get_results("SELECT count(*) as count FROM ".$query);	 //  bb_count_last_query();  // count($topics);			
	$count=count($total); if ($count==1) {$total=$total[0]->count;} else {$total=$count;}
	
	if (empty($total)) {$total=0; $topics="";}	// nothing found (perhaps search for plural or singular)
	else {
		if ($SSrequest['maxcount'] <$total) {$total=$SSrequest['maxcount'];}
		$topics = $bbdb->get_results("SELECT *  FROM ".$query.$restrict);
		
		if ($SSdebug) {echo "<div style='height:200px;overflow:scroll;'><pre>"; print_r($topics); print "</pre></div>";}
		
		// $topics = bb_append_meta( $topics, 'topic' );		
		// nasty, nasty workaround for multiple same id's in bb_append_meta
			
		$trans = array(); foreach (array_keys($topics) as $i ) {$trans[$topics[$i]->topic_id]->topic_id = $topics[$i]->topic_id;}		
		// $trans = bb_append_meta( $trans, 'topic' );	//  this doesn't work quite right when the same topic_id shows up multiple times in a list, workaround		
		// Fine. here's how to build it.
		$ids = join(',', array_map('intval', array_keys($trans)));
		if (defined('BACKPRESS_PATH')) {
			 $metas = $bbdb->get_results("SELECT object_id as topic_id, meta_key, meta_value FROM $bbdb->meta WHERE object_type='bb_topic' AND object_id IN ($ids)");
		} else {
			 $metas = $bbdb->get_results("SELECT topic_id, meta_key, meta_value FROM $bbdb->topicmeta WHERE topic_id IN ($ids)");
		}
		if ($metas) {
			foreach ( $metas as $meta ) {
				$trans[$meta->topic_id]->{$meta->meta_key} = bb_maybe_unserialize( $meta->meta_value );
				if ( strpos($meta->meta_key, $bbdb->prefix) === 0 )
					$trans[$meta->topic_id]->{substr($meta->meta_key, strlen($bbdb->prefix))} = bb_maybe_unserialize( $meta->meta_value );
			}
		} 
		unset($metas);   // cleanup
		// load up the bbpress caches so template functions don't generate runaway queries
		if (defined('BACKPRESS_PATH')) {$bb10=true;} else {global $bb_topic_cache,$bb_post_cache; $bb10=false;}
		foreach ( array_keys($topics) as $i ) {		
		foreach($trans[$topics[$i]->topic_id] as $key=>$value) {$topics[$i]->$key=$value;}					
			if ($bb10) {wp_cache_set($topics[$i]->topic_id, $topics[$i], 'bb_topic' ); wp_cache_set($topics[$i]->post_id, $topics[$i], 'bb_post' );} 
			else {$bb_topic_cache[$topics[$i]->topic_id]=$topics[$i]; $bb_post_cache[$topics[$i]->post_id]=$topics[$i];}
			
			$user_ids[$topics[$i]->poster_id]=$topics[$i]->poster_id;
			$user_ids[$topics[$i]->topic_poster]=$topics[$i]->topic_poster;
			$user_ids[$topics[$i]->topic_last_poster]=$topics[$i]->topic_last_poster;
		} 		
		unset($trans);   // cleanup			
		bb_cache_users($user_ids);
		// unset($topics); // this is bad, if we can't unset $topics, it's using double the memory for cache+$topics
		// print_r($topics);
	} 	

} // end test for $search
}

function super_search_show_context($text ) {
global $SSrequest, $SSwords;
	$text = strip_tags($text);
	$term = preg_quote($SSwords[0]);
	if (!empty($SSrequest['exact'])) {$term1="\b$term\b";} else {$term1="$term";} 	
	$offset=intval((320-strlen($term1))/2);
	$text = preg_replace("|.*?(.{0,$offset})($term1)(.{0,$offset}).*|is", "... $1$2$3 ...", $text, 1);
return $text;
}

function super_search_highlight($text ) {
global $SSrequest, $SSwords;	
	if (!empty($SSrequest['highlight'])) {
		foreach ($SSwords as $key=>$word) {
			$word=preg_quote($word);
			if (!empty($SSrequest['exact'])) {$term="\b$word\b";} else {$term=$word;}
			$highlight="<span class='SShighlight$key'>$1</span>";
			$text = preg_replace("|($term)|is", $highlight, $text, 1);
		}
	}	
return $text;
}

function super_search_users() {	// ajax helper
	if  (bb_current_user_can( 'participate' )) {		// only let logged in users see/select specific usernames
	global $bbdb;
		
	$active = $bbdb->get_results("SELECT user_id FROM $bbdb->usermeta WHERE meta_key='bb_capabilities' and meta_value NOT REGEXP 'inactive|blocked' LIMIT 3000");
	foreach (array_keys($active) as $i) {$trans[$active[$i]->user_id] =& $active[$i];} 
	unset($active);		 	 // huge query, release memory
	$ids = join(',', array_keys($trans));	// this eventually needs to be enhanced to filter/split the array for pagination - could get HUGE
	
	$users = $bbdb->get_results("SELECT ID,user_login FROM $bbdb->users WHERE user_status = 0 AND ID IN ($ids) ORDER BY  user_login ASC LIMIT 3000");
	
	foreach ($users as $user) {
		echo  "z($user->ID,'".addslashes(substr($user->user_login,0,15))."');";
	}
	}
exit;
}


function super_search_add_header() {
global $SSrequest;
echo '<style type="text/css">
.bigcheck 	{vertical-align:middle; margin-right:4px; height:1.4em; width:1.4em; '.(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') ? 'font-size:1.4em;' : '').'}
.bigbutton	{font-size: small;font-weight: bold;	cursor: pointer;cursor: hand;}
.nowrap	{white-space:nowrap;}
.indent	{margin:0 10px;}

#latest,#main	{margin-bottom:1em;}
#latest th {text-align:center;}

.SuperSearch {font-size:1em; line-height:150%;margin:0 1em;}
.SuperSearch option {padding:1px 2px; margin:1px; font-size:1em; vertical-align:middle;}
.SuperSearch input {vertical-align:middle; 1em;}
.SSbreak 	{clear:left; line-height:11px; height:11px; font-size:11px;}
.SShighlight0	{color:#000; background:#FFFFCF;}
.SShighlight1	{color:#000; background:#00FFFF;}
.SShighlight2	{color:#000; background:#7FFF00;}
.SShighlight3	{color:#000; background:#FF8C00;}
.SShighlight4	{color:#000; background:#ADFF2F;}
.SShighlight5	{color:#000; background:#98FB98;}
.SShighlight6	{color:#000; background:#AFEEEE;}
.SShighlight7	{color:#000; background:#FFFF00;}

#SSsubmit, #SSreset {font-size:1em; line-height:150%; padding:1px; line-height:1em;}
#SSsearch	 {padding-left:3px;}
#SSsimple	 {text-decoration:underline;}
#SSadvanced {display:'.($SSrequest['advanced'] ? 'block' : 'none').';}
#SSforums 	 {position:absolute;display:inline; height:8em;}

fieldset {
	margin: 0;	
	background: #eee;
	border: 1px dashed #ccc;
	width:96%;
}

legend { 
	background: #E1E6EB;
	border: 1px solid #ccc;
	color:#283C50;	
	padding:1px 1em 2px 1em;
	line-height:92%;
	margin-left:1em;	
	overflow:hidden;
	font-weight: normal;
}
	
legend .post_count_plus {line-height:92%;}
}
</style>
<!--[if IE]>
<style>
fieldset {position: relative; top: 3px;}
legend {position: absolute; top: -.55em; left: .2em;}
</style>
<![endif]-->

<script language="javascript" type="text/javascript" defer="defer">
var SuperSearch_script = document.createElement("script");
var SuperSearchAdvanced='.(($SSrequest['advanced']) ? 1 : 0).';

function SSadvanced() {
SuperSearchAdvanced=-SuperSearchAdvanced+1;
document.getElementById("SSsimple").innerHTML=  SuperSearchAdvanced ? "'.__('Simple Search').'" : "<strong>'.__('Advanced Search').'</strong>"; 
document.getElementById("SSadvanced").style.display= SuperSearchAdvanced ? "block" : "none";
document.forms["SuperSearch"].advanced.value=SuperSearchAdvanced;
}

function SuperSearch_loadusers() {
	if(!SuperSearch_script.src) {
		SuperSearch_script.src = "'.bb_get_option( 'uri' ).'/?SuperSearchUsers";
		SuperSearch_script.type = "text/javascript";
		SuperSearch_script.charset = "utf-8";
		document.getElementsByTagName("head")[0].appendChild(SuperSearch_script);
	}
}
function z(add_value,add_text) {     	                 
	newOption = document.createElement("option");                
	newOption.text = add_text;
	newOption.value = add_value;
	selectElement=document.getElementById("SSusers");
	try {selectElement.add(newOption,null);}
	catch (e) {selectElement.add(newOption,selectElement.length);}
}
</script>
';
} 
?>