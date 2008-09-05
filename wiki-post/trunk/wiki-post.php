<?php
/*
Plugin Name: Wiki Post
Description:  Allows any member to edit a Wiki-like post in any topic for a collaborative entry or FAQ.
Plugin URI:  http://bbpress.org/plugins/topic/101
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.1.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   tinker with settings below, install, activate
*/

function wiki_post_initialize() {
	global $wiki_post;	
	if (!isset($wiki_post)) {$wiki_post = bb_get_option('wiki_post');}
	if (!$wiki_post) {
	
	// you can edit these
	
	$wiki_post['start_question']="Add a Wiki Post to this topic ?";
	$wiki_post['start_level']="participate";	// participate/moderate/administrate
	$wiki_post['edit_level']="participate";	// participate/moderate/administrate
	$wiki_post['post_instructions']="<hr /><em>This is a <strong>Wiki Post</strong> that anyone can edit to collaboratively summarize this topic or make a FAQ</em>";
	$wiki_post['post_position']="2";		// you can make the wiki post the 1st or 2nd post (2 is recommended)
	$wiki_post['automatic_insert']=false;	// not implimented yet - automatic wiki creation for every topic
	$wiki_post['name']="Wiki Post";		// formal wiki name
	$wiki_post['title']="<span style='font:bold 1.2em serif;color:navy;'>W</span>"; // text, html or image to show on topic titles if Wiki is present	
	
	// stop editing
	
	$wiki_post['user']=wiki_post_create_user();	// if you don't make a user with the above 'name' one will be made for you	
	bb_update_option('wiki_post',$wiki_post);	
	}
	
	if (isset($_GET['add_wiki_post']) && intval($_GET['add_wiki_post'])) { 					
		if ($post_id=wiki_post_create_post()) {wp_redirect( get_post_link( $post_id ));}
		else {wp_redirect(remove_query_arg(array('add_wiki_post')));}
	}
	
	if (isset($_GET['reset_wiki_post']) && bb_current_user_can('administrate')) {
		bb_delete_option('wiki_post');
		wp_redirect(remove_query_arg(array('reset_wiki_post')));
	}		
					
	if (is_topic()) {					
		add_action('topicmeta','wiki_post_create_link',200);
		add_filter('post_text','wiki_post_footer',5);
	} else {
		add_filter('topic_title', 'wiki_post_title',200);
	}	
	add_filter('bb_current_user_can', 'wiki_post_allow_edit',200,3);		
} 
add_action('bb_init','wiki_post_initialize');
add_action( 'bb_update_post', 'wiki_post_update_post');

function wiki_post_allow_edit($retvalue, $capability, $args) {
	global $wiki_post, $bb_post;		 			
	if ($args[0]=="edit_post" && $bb_post->post_id==$args[1] && $bb_post->poster_id==$wiki_post['user'] && bb_current_user_can($wiki_post['edit_level']))  
	{return true;}	
return $retvalue;
}

function wiki_post_create_link($x) {
	global $wiki_post;		 	
	$topic_id=get_topic_id(); $topic = get_topic($topic_id);  
	if ($topic_id && $topic && !isset($topic->wiki_post) && bb_current_user_can($wiki_post['start_level'])) {
		echo "<li><a href='".add_query_arg( 'add_wiki_post',$topic_id)."'>".$wiki_post['start_question']."</a></li>";
	}
}

function wiki_post_create_user() {
	global $wiki_post, $bbdb;
	$user_id=$bbdb->get_var("SELECT ID FROM $bbdb->users WHERE user_login = '".$wiki_post['name']."' LIMIT 1");

		@require_once( BB_PATH . BB_INC . 'registration-functions.php');		
		// if ( $user_id = bb_new_user( $wiki_post['name'], $email, bb_get_option('uri'), 0 ) ) {
		// 	   bb_new_user( $user_login, $user_email, $user_url, $user_status = 1 )	
		// screw it, 1.0 breaks everything, we'll just do it ourselves
		
		$user_login = sanitize_user($wiki_post['name'], true );	
		$display_name = $user_login;
		$user_nicename = bb_user_nicename_sanitize( $user_login );		
		// while ( is_numeric($user_nicename) || $existing_user = bb_get_user_by_nicename( $user_nicename ) )
		//	$user_nicename = bb_slug_increment($_user_nicename, $existing_user->user_nicename, 50);
	
		$user_email=bb_get_option('from_email'); if (empty($user_email)) {$user_email=bb_get_option('admin_email');}
		$user_url = ""; 	// bb_fix_link(bb_get_option('uri'));
		$user_registered = bb_current_time('mysql');
		$password = wp_generate_password();
		$user_pass = wp_hash_password( $password );
		$user_status=0;
		$compact=compact( 'user_login', 'user_pass', 'user_nicename', 'display_name', 'user_email', 'user_url', 'user_registered', 'user_status' );

		if (empty($user_id)) {@$bbdb->insert( $bbdb->users, $compact); $user_id = $bbdb->insert_id;}
		else {@$bbdb->update( $bbdb->users, $compact,  array( 'ID' => $user_id));}		 

		bb_update_usermeta( $user_id, $bbdb->prefix . 'capabilities', array('throttle'=>true, 'member' => true) );
		bb_update_usermeta( $user_id, $bbdb->prefix . 'title', $wiki_post['name']);			

return $user_id;	
}

function wiki_post_create_post($topic_id=0) {
	global $wiki_post, $bbdb;
	bb_repermalink();
	$topic_id=get_topic_id($topic_id); $topic = get_topic($topic_id);
	if ($topic_id && $topic && !isset($topic->wiki_post) && bb_current_user_can($wiki_post['start_level'])) {

		$time=@$bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE topic_id=$topic_id AND post_status=0 ORDER BY post_time ASC LIMIT 1 OFFSET ".($wiki_post['post_position']-2));
		$time=gmdate('Y-m-d H:i:s',(strtotime($time." GMT")+1));					 

		$defaults = array(
			'post_id' => false,
			'topic_id' => $topic_id,
			'post_text' => '',
			'post_time' => $time,
			'poster_id' => $wiki_post['user'], 
			'poster_ip' => $_SERVER['REMOTE_ADDR'],
			'post_status' => 0, 
			'post_position' => $wiki_post['post_position'],
			'throttle'=> false
		);

		$post_id=bb_insert_post($defaults);
		if ($post_id && $topic_id) {$topic->wiki_post['post_id']=$post_id; bb_update_topicmeta( $topic_id, 'wiki_post', $topic->wiki_post);}
		
	wiki_post_fix_last_poster($topic_id);
	wiki_post_add_edit_history($topic_id);
	return $post_id;
	}
return 0;	
}

function wiki_post_update_post($post_id) {
	global $wiki_post, $bb_post;		
	if ($post_id && $post_id==$bb_post->post_id && $wiki_post['user']==$bb_post->poster_id) {wiki_post_add_edit_history($bb_post->topic_id);}	
}

function wiki_post_fix_last_poster($topic_id) {
	global $wiki_post, $bbdb;	
	// bbpress wants to make wiki_post the last poster, so undo that		
	$old_post = $bbdb->get_row( "SELECT post_id, poster_id, post_time FROM $bbdb->posts WHERE poster_id!= ".$wiki_post['user']." AND topic_id = $topic_id AND post_status = 0 ORDER BY post_time DESC LIMIT 1");
	$old_name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = $old_post->poster_id");
	$bbdb->update( $bbdb->topics, array( 'topic_time' => $old_post->post_time, 'topic_last_poster' => $old_post->poster_id, 'topic_last_poster_name' => $old_name, 'topic_last_post_id' => $old_post->post_id ), compact( 'topic_id' ) );
}

function wiki_post_add_edit_history($topic_id=0) {
	global $wiki_post;		
	$topic_id=get_topic_id($topic_id); $topic = get_topic($topic_id);  
	$user_id=bb_get_current_user_info( 'id' );  $time=time();	
	if ($topic->wiki_post['user_id']) {$topic->wiki_post['user_id'].=",$user_id";} else {$topic->wiki_post['user_id']=$user_id;}
	if ($topic->wiki_post['time']) {$topic->wiki_post['time'].=",$time";} else {$topic->wiki_post['time']=$time;}	
	bb_update_topicmeta( $topic_id, 'wiki_post', $topic->wiki_post); 
}

function wiki_post_title( $title ) {
	global $wiki_post, $topic;
	if ($wiki_post['title'] && isset($topic->wiki_post) && !is_topic())  {return $title." ".$wiki_post['title'];}		
	return $title;
} 

function wiki_post_footer($text) {
	if (!is_bb_feed()) {
		global $wiki_post, $bb_post, $topic;		
		if ($wiki_post['user']==$bb_post->poster_id) {
			$edited=1+intval(substr_count($topic->wiki_post['user_id'],",")); 
			if ($edited>1) {$edited="Edited $edited times."; 			
			$last="Last edit by ".get_full_user_link(intval(substr($topic->wiki_post['user_id'],strrpos($topic->wiki_post['user_id'],",")+1)))." ";
			$last.=bb_since(1+intval(substr($topic->wiki_post['time'],strrpos($topic->wiki_post['time'],",")+1)))." ago.";
			} else {$last=""; $edited=""; $ago="";}			
			$started="Started by ".get_full_user_link(intval($topic->wiki_post['user_id'])).".";
			$text.="<div class='wiki_post'>".$wiki_post['post_instructions']."<br />($started $edited $last)</div>";
		}
	}
return $text;
}


?>