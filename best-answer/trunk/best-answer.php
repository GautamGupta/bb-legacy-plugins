<?php
/*
Plugin Name: Best Answer
Plugin URI: http://bbpress.org/plugins/topic/best-answer
Description: Allows the topic starter or moderators to select which reply is a "Best Answer" to the original post.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.4
*/

$best_answer['automatic']=true;	 	 //  set to false if you want to place manually in post.php template via   do_action('best-answer');
$best_answer['forums']="1,2,3,4,5,6,7,8";	 //  comma seperated list of forum id numbers to have best answer  (set blank for all)
$best_answer['max']=1;		 	 //  how many posts per topic can be designated as a "best answer"
$best_answer['display_first']=true;	 //  should Best Answer(s) be moved to the start of the topic? set false to disable
$best_answer['text']="<span>&#8902;</span>".__('Best Answer');
$best_answer['css']="
	.best_answer {text-decoration:none; border:0; white-space:nowrap;}		
	.best_answer span {font-weight:900; font-size:4em; padding:0 1px 0 0; margin:0 0 0 -10px; vertical-align:-20%;
	       font-family:'Lucida Grande','Arial Unicode','Arial Unicode MS','Georgia Ref','Verdana Ref','DejaVu Sans Mono','DejaVu Serif','GNU Unifont','Lucida Sans Unicode';}
	a.best_answer {color:#999;}
	a.best_answer:hover {color:red;}		
	a.best_answer_selected, .best_answer_selected span {color: green;}
	#thread li.best_answer_background { background-color: transparent; }
	#thread li.best_answer_background .threadpost { background-color: #80DD80; }
	#thread li.alt.best_answer_background .threadpost { background-color: #84DB8B; }
";

/*
 note: because of lack of foresight in the bbPress output functions you have to edit the topic.php template to change post colors
 change: <?php foreach ($posts as $bb_post) : $del_class = post_del_class(); ?>
 to:   <?php foreach ($posts as $bb_post) : $del_class = apply_filters('best_answer_class',post_del_class()); ?>
*/

/*  	stop editing here 	 */

if (!empty($best_answer['forums']) && !is_array($best_answer['forums'])) {(array) $best_answer['forums']=explode(',',$best_answer['forums']); $best_answer['forums']=array_flip($best_answer['forums']);}
if ($best_answer['display_first']) {add_filter( 'get_post_link', 'best_answer_post_link', 255, 2 );}
if (is_topic()) {	add_action( 'bb_topic.php', 'best_answer_init' ); }

function best_answer_init() {		
	global $best_answer, $topic, $bb_current_user, $posts, $page; 
	if (!empty($best_answer['forums']) && !isset($best_answer['forums'][$topic->forum_id])) {return;}
	add_action('bb_head','best_answer_head'); 
	add_action('best_answer','best_answer'); 
	add_action('best-answer','best_answer'); 
	add_filter('best_answer_class','best_answer_class');	
	if ($best_answer['automatic']) {
		add_filter( 'post_author_title', 'best_answer_filter',300); 
		add_filter( 'post_author_title_link', 'best_answer_filter',300);
	}
	if ((!empty($bb_current_user->ID) && $bb_current_user->ID==$topic->topic_poster) || bb_current_user_can('moderate')) {$best_answer['can_edit']=true;}
	else {$best_answer['can_edit']=false;}	
	if (empty($topic->best_answer)) {$topic->best_answer=array();} elseif (!is_array($topic->best_answer)) {(array) $topic->best_answer=explode(',',$topic->best_answer);}
	$topic->best_answer=array_flip($topic->best_answer);		// speedup by using post id as key	
	if (!empty($topic->topic_id) && !empty($_GET['best_answer']) && $best_answer['can_edit']) {		
		$value=intval($_GET['best_answer']);
		if (isset($topic->best_answer[$value])) {unset($topic->best_answer[$value]);} 
		else {if ($best_answer['max']==1) {$topic->best_answer=array();} $topic->best_answer[$value]=$value;}
		bb_update_topicmeta($topic->topic_id,'best_answer',implode(',',array_flip($topic->best_answer)));
		wp_redirect(get_post_link($value)); exit;	
	}
	$best_answer[$topic->topic_id]=$topic->best_answer;
	$best_answer['count']=count($topic->best_answer);
	if ($best_answer['display_first'] && !empty($best_answer[$topic->topic_id])) {		// move best answer(s) to top of topic
		if ($page==1) {$question=$posts[0]; unset($posts[0]);}
		foreach ($posts as $key=>$bb_post) {if ($bb_post->post_status==0 && isset($best_answer[$topic->topic_id][$bb_post->post_id])) {unset($posts[$key]);}}
		if ($page==1) {
		foreach ($best_answer[$topic->topic_id] as $post_id=>$ignore) {$best=bb_get_post($post_id); if ($best->post_status==0) {array_unshift($posts,$best);} else {unset($best_answer[$topic->topic_id][$post_id]);}}
		array_unshift($posts,$question);
		}
	}
}	

function best_answer_head() {global $best_answer; echo '<style type="text/css">'.$best_answer['css'].'</style>';} 	 // css style injection + javascript 

function best_answer_filter($titlelink) {echo $titlelink; best_answer(); return '';}	// only if automatic post inserts are selected

function best_answer_class($class) {
	global $best_answer, $topic, $bb_post; 
	if ($bb_post->post_status==0 && isset($best_answer[$topic->topic_id][$bb_post->post_id])) {$class="$class best_answer_background";} 
	return $class;
}

function best_answer() {
	global $best_answer, $topic, $bb_current_user, $bb_post; 	
	if ($bb_post->post_status!=0) {return;}
	if ($topic->topic_posts>1 && $bb_post->post_position>1 && (empty($best_answer['forums']) || isset($best_answer['forums'][$topic->forum_id]))) {	
		if (!$best_answer['can_edit']) { 
			if (isset($best_answer[$topic->topic_id][$bb_post->post_id])) {
				echo "<div class='best_answer'>".$best_answer['text']."</div>";
			}
		} else {
			$url=add_query_arg(array('best_answer'=>$bb_post->post_id)); 	//  ,'r'=>rand(0,9999)))."#post-$bb_post->post_id";
			if (isset($best_answer[$topic->topic_id][$bb_post->post_id])) {
				echo "<a title='click to undo' href='$url' class='best_answer best_answer_selected'>".$best_answer['text']."</span></a>";
			} elseif ($best_answer['max']==1 || $best_answer['count']<$best_answer['max']) {
				echo "<a title='click to select as best answer' class='best_answer' href='$url'>".$best_answer['text']."?</a>";
			} 
		}
	}
}

function best_answer_post_link($link, $post_id ) {	// this needs to be rewritten for better performance somehow
global $best_answer; static $posts_per_page; 
	$post=bb_get_post($post_id); 
	if (empty($posts_per_page)) {$posts_per_page=bb_get_option('page_topics');}	// speedup
	if ($post->post_position>$posts_per_page) {	 // is it beyond page 1 typically?
		$topic=get_topic($post->topic_id);
		if (!empty($topic->best_answer)) {
			if (!is_array($topic->best_answer)) {(array) $topic->best_answer=explode(',',$topic->best_answer); $topic->best_answer=array_flip($topic->best_answer);}
			if (isset($topic->best_answer[$post_id])) {$link=get_topic_link( $bb_post->topic_id, 1) . "#post-$post_id";}    // change link to page 1 for best answers
		}
	}
return $link;
}

?>