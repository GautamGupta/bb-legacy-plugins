<?php
/*
Plugin Name: Subscribe to Topic
Plugin URI: http://bbpress.org/plugins/topic/subscribe-to-topic
Description: Allows members to track and/or receive email notifications (instant, daily, weekly) for new posts on topics.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

if (strpos($self,"subscribe-to-topic.php")===false) :	

$subscribe_to_topic['labels']['subscribe']  =__("Subscribe To Topic");
$subscribe_to_topic['labels']['subscribed']=__("Members Subscribed To Topic");
$subscribe_to_topic['labels']['tab']  =__("Subscribed Topics");
$subscribe_to_topic['labels']['no more']=__('No more Subscribed Topics');
$subscribe_to_topic['labels']['none']=__('No new Subscribed Topics yet');
$subscribe_to_topic['labels']['all']=__('Show All Subscribed Topics');

$subscribe_to_topic['tab']="Subscribed";	 // editable but don't translate here
$subscribe_to_topic['public']=false;	 // false = only user can see their own subscribed topics, true = everyone can
$subscribe_to_topic['view']=true;		// add Subscribed Topics to view list

$subscribe_to_topic['levels']=array(
0=>__("Don't Subscribe"),
1=>__("Subscribe Without Email"),
2=>__("Instant Email Notification"),
);
// 3=>__("Daily Updates By Email"),
// 4=>__("Weekly Updates By Email")
// );

/*  stop editing here  */

add_action('bb_init','stt_init',150);
add_action('bb_new_post', 'stt_notify');
add_action('topicmeta','stt_topic_meta',140);
add_action( 'bb_profile_menu', 'stt_add_profile_tab');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'stt_install');
add_action( 'bb_custom_view', 'stt_view');

$tab = isset($_GET['tab']) ? $_GET['tab'] : get_path(2); 
if ($subscribe_to_topic['view'] || (bb_get_location()=="profile-page" && $tab=="subscribed")) {

if (is_callable('bb_register_view')) {	// Build 876+   alpha trunk
function stt_view_09() {			
	if (bb_is_user_logged_in()) {
		$query = array('append_meta'=>false,'sticky'=>false);	// attempt to short-circuit bb_query	
		bb_register_view("subscribed-topics","Subscribed Topics",$query);
	}
} add_action('bb_init', 'stt_view_09');

} else {		// Build 214-875	(0.8.2.1)

function stt_view_08( $passthrough ) {		
	if (bb_is_user_logged_in()) {
		global $views;
		$views['subscribed-topics'] = "Subscribed Topics";
	}
	return $passthrough;
} add_filter('bb_views', 'stt_view_08');
}
}

function stt_last_post_link($link)  {			// props kaviaar - makes title links jump to last unread post
global $topic;	
if (!empty($topic->post)) {$link = get_post_link($topic->post);}
return $link;
}

function stt_new_post_title($title)  {
global $topic;		
if (!empty($topic->post) && $topic->topic_last_post_id>$topic->post) {$title="<strong>$title</strong>";}
return $title;
}

function stt_view_pages($item) {
global $view_count,$topics,$page,$subscribe_to_topic;
$count=count($topics);
if ($count) {
$limit = bb_get_option('page_topics');
$offset = ($page-1)*$limit;	
// if (!isset($_GET['all'])) {$all=" <a href='".add_query_arg('all','')."'>".$subscribe_to_topic['labels']['all']."</a> ";} else {$all="";}
$item=__('Showing')." <strong>".($offset+1)."</strong> - <strong>".($offset+$count)."</strong> ".__('of')." <strong>$view_count</strong> ".	$item; // .$all;	
}
return $item;
}

function stt_footer($template='',$file='') {
global $topics,$subscribe_to_topic;
if ($file=="footer.php"){
	$all=""; if (empty($topics)) {$all.=$subscribe_to_topic['labels']['none']."<br />";}	
	if (!isset($_GET['all'])) {$all.="<br /><a href='".add_query_arg('all','')."'>".$subscribe_to_topic['labels']['all']."</a><br />";}	
	echo "<div style='margin:1em;text-align:center;clear:both;'>".$all."</div>";
}
return $template;	
}

function stt_unsubscribe_link($text) {
global $topic, $subscribe_to_topic;
$link=add_query_arg('unsubscribe',$topic->topic_id);
$text=" <span title='".addslashes($subscribe_to_topic['levels'][$topic->type])."'>[<a href='$link'>x</a>]</span> ".$text;
return $text;
}

function stt_view($view) {
if ($view!="subscribed-topics") {return;}
	add_filter('topic_link', 'stt_last_post_link',20);	
	add_filter('topic_title', 'stt_new_post_title',20);
	add_filter( 'view_pages', 'stt_view_pages',20);
	remove_filter( 'view_pages', 'my_views_view_pages_label',200 );
	add_filter('bb_template','stt_footer',50,2);
	add_filter( 'bb_topic_labels', 'stt_unsubscribe_link', 5);

	global $bbdb,$topics,$page,$user, $view_count, $bb_current_user;
	if (!empty($user->ID)) {$user_id=$user->ID;} elseif (!empty($bb_current_user->ID)) {$user_id=$bb_current_user->ID;} else {return;}

	$limit = bb_get_option('page_topics');
	$offset = ($page-1)*$limit;	

	$where = apply_filters('get_latest_topics_where',"WHERE topic_status=0 AND user=$user_id ");
	if (!isset($_GET['all'])) {$where.=" AND topic_last_post_id>post ";}
	$query =" FROM $bbdb->topics LEFT JOIN subscribe_to_topic ON topic_id=topic $where ";
	$restrict="ORDER BY topic_last_post_id  DESC LIMIT $limit OFFSET $offset";
	
	$view_count  = $bbdb->get_var("SELECT count(*) ".$query);	
	$topics = $bbdb->get_results("SELECT * ".$query.$restrict);
	if (!empty($topics)) {$topics = bb_append_meta( $topics, 'topic' );}
}

function stt_init() {
global $bbdb,$topic, $bb_current_user,$user,$subscribe_to_topic;
if (isset($_GET['subscribe_to_topic'])) {
	$level=intval($_GET['subscribe_to_topic']);
	$topic_id=0;  if (isset($_GET['topic_id'])) {$topic_id=intval($_GET['topic_id']);}
 	if (!empty($bb_current_user->ID) &&! empty($topic_id) && bb_current_user_can('edit_user', $bb_current_user->ID)) {stt_change_level($bb_current_user->ID,$topic_id,$level);}
 	bb_safe_redirect(remove_query_arg(array('subscribe_to_topic','topic_id'))); exit;
}
elseif (!empty($_GET['unsubscribe'])) {
	$topic_id=intval($_GET['unsubscribe']);
	$user_id=0; if (!empty($user->ID)) {$user_id=$user->ID;} elseif (!empty($bb_current_user->ID)) {$user_id=$bb_current_user->ID;}
	if (!empty($user_id) && !empty($topic_id) && bb_current_user_can('edit_user', $user_id)) {stt_change_level($user_id,$topic_id,0);}
	bb_safe_redirect(remove_query_arg(array('subscribe_to_topic','topic_id','unsubscribe'))); exit;	
}
}

function stt_notify($post_id=0) {
remove_action('bb_new_post', 'stt_notify');	// bbpress bug, fires multiple times?
global $bbdb, $topic_id, $bb_current_user, $subscribe_to_topic; $time=time()-60;
$topic=get_topic($topic_id); if (empty($post_id) || empty($bb_current_user->ID) || ($topic->topic_posts<2)) {return;}
$query="SELECT DISTINCT user_id,user_email FROM $bbdb->users as t1 	     
	     LEFT JOIN $bbdb->usermeta as t2 on t1.ID=t2.user_id 
	     LEFT JOIN subscribe_to_topic as t3 on t1.ID=t3.user
	     WHERE user!=$bb_current_user->ID AND user_status=0 
	     AND topic=$topic_id AND type=2 AND time<$time and post>last
	     AND (meta_key='bb_capabilities' AND NOT (meta_value LIKE '%inactive%' OR meta_value LIKE '%blocked%'))";
$emails=$bbdb->get_results($query); if (empty($emails)) {return;}

$topic_link=get_post_link($post_id);
$unsubscribe=add_query_arg( 'unsubscribe', $topic_id, $topic_link);
$from=bb_get_option('from_email'); if (empty($from)) {$from=bb_get_option('admin_email');}
$from_email='From: ' .$from;	
$user_name=get_user_name($bb_current_user->ID);
$subject="[".bb_get_option('name') . ']' ." ". __('new post notification');
$message = __("There is a new post on [%1\$s] \n\nReply by: %2\$s \n%3\$s \n\n\nUnsubscribe: \n%4\$s \n");

foreach ($emails as $email){
	mail($email->user_email,$subject,sprintf($message,$topic->topic_title,$user_name,$topic_link,$unsubscribe),$from_email,"-odb");	  // odq = queue only
	$ids[$email->user_id]=$email->user_id;
	print "$email->user_email <br>\n";	
}
if (!empty($ids)) {
	$ids=implode(',',$ids); $time=time();
	$bbdb->query("UPDATE subscribe_to_topic SET last=$post_id,time=$time WHERE topic=$topic_id AND user IN ($ids)");
}
}

function stt_change_level($user_id=0,$topic_id=0,$level=0) {
global $bbdb,$bb_current_user,$subscribe_to_topic;
if (empty($user_id) || empty($topic_id)) {return;}
if ($level===0) {return $bbdb->query("DELETE FROM subscribe_to_topic WHERE user=$user_id AND topic=$topic_id LIMIT 1");}
$topic=get_topic($topic_id); 
$query="INSERT INTO subscribe_to_topic (`user`,`topic`,`post`,`type`) 
	    VALUES ('$user_id','$topic_id','$topic->topic_last_post_id','$level') 
	    ON DUPLICATE KEY UPDATE `post` = VALUES( `post`), `type` = VALUES( `type`)";
return $bbdb->query($query);	    
}

function stt_topic_meta() {
global $bbdb,$topic, $posts, $bb_current_user,$subscribe_to_topic;
if (empty($topic->topic_id)) {return;}
$query="(SELECT COUNT(user) as type FROM subscribe_to_topic WHERE topic=$topic->topic_id)";
if (!empty($bb_current_user->ID)) {
	$query.=" UNION ALL (SELECT type FROM subscribe_to_topic WHERE topic=$topic->topic_id AND user=$bb_current_user->ID LIMIT 1) 
		      UNION ALL (SELECT post FROM subscribe_to_topic WHERE topic=$topic->topic_id AND user=$bb_current_user->ID LIMIT 1) ";
}
$results=$bbdb->get_col($query); $count=$results[0]; $current=0; $post_id=0; if (isset($results[1])) {$current=intval($results[1]); $post_id=intval($results[2]);}
$output="<li id='subscribe_to_topic'>";
if ($count) {$output.= bb_number_format_i18n($count)." ".$subscribe_to_topic['labels']['subscribed'];} else {$output.=$subscribe_to_topic['labels']['subscribe'];}
if (!empty($bb_current_user->ID)) {
$output.=" <form method='get' style='display:inline;' action='". $_SERVER['REQUEST_URI']."'><input type='hidden' name='topic_id' value='$topic->topic_id' />
(<select style='color:Highlight;background:transparent;border:0;' name='subscribe_to_topic' onchange='this.form.submit();'>\n"; 
foreach ($subscribe_to_topic['levels'] as $key=>$level) {$output.="<option value='$key' ".($current==$key ? "selected='selected'" : "").">&nbsp;$level&nbsp; </option>\n";}
$output.="</select>)";
if (!empty($_GET)) {foreach ($_GET as $key=>$value) {$output.="<input type='hidden' name='$key' value='$value' />";}}
$output.="</form>";
}
echo $output,"</li>\n";
if ($current) {
$high_id=0; foreach ($posts as $post) {if ($post->post_id>$post_id) {$high_id=$post->post_id;}}
if ($high_id) {$bbdb->query("UPDATE subscribe_to_topic SET post=$high_id WHERE topic=$topic->topic_id AND user=$bb_current_user->ID LIMIT 1");}
}
}

function stt_install() {
global $bbdb;
$bbdb->query("CREATE TABLE IF NOT EXISTS `subscribe_to_topic` (
		`user` 	int(10)	 UNSIGNED NOT NULL default '0',
		`topic` 	int(10)	 UNSIGNED NOT NULL default '0',		
		`post`	int(10)	 UNSIGNED NOT NULL default '0',
		`last`	int(10)	 UNSIGNED NOT NULL default '0',
		`time`	int(10)	 UNSIGNED NOT NULL default '0',
		`type`	tinyint	 UNSIGNED NOT NULL default '0',		
		PRIMARY KEY (`user`,`topic`),
		INDEX (`user`),
		INDEX (`topic`)		
		) CHARSET utf8  COLLATE utf8_general_ci");	
}

function stt_add_profile_tab() {
global $self, $subscribe_to_topic;
if (!$self) {	// I have no idea exactly why this is but apparently bb_profile_menu action is called twice? bug?	
	if ($subscribe_to_topic['public']) {$role=""; } else {$role="edit_user";}
	add_profile_tab($subscribe_to_topic['tab'], $role, $role, __FILE__ );		
}		
}

else :	// we're in the profile tab, is it enabled?

bb_send_headers();
global $bbdb,$topics,$page, $view, $user, $subscribe_to_topic;
if (!($subscribe_to_topic['public'] || bb_current_user_can('edit_user', $user->ID))) {exit;}	
$view = bb_slug_sanitize("subscribed-topics");
$sticky_count = $topic_count = 0;
$stickies = $topics = $view_count = false;
do_action( 'bb_custom_view', $view, $page );
do_action( 'bb_view.php', '' );
bb_load_template( 'view.php', array('view_count', 'stickies') );	

endif;	// profile tab check
?>