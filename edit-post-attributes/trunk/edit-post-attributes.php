<?php
/*
Plugin Name: Edit Post Attributes
Plugin URI: http://bbpress.org/plugins/topic/edit-post-attributes
Description: Allows administrators to change hidden post settings including author and timestamp. Use at your own risk.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.1
*/

$edit_post_attributes['role']="administrate";	//  administrate, moderate, etc.

/*     stop editing here    */

add_action( 'bb_update_post', 'edit_post_attributes_save',8);
add_action('edit_form','edit_post_attributes_form',8);

function edit_post_attributes_save($post_id=0) {
global $edit_post_attributes;
if (!bb_current_user_can($edit_post_attributes['role'])) {return;}
	global $bbdb, $bb_cache; $fields=array(); $topic_fields=array();			
	$bb_post=bb_get_post($post_id);
	if (!empty($_POST['poster_login'])) {
		$poster_login=sanitize_user($_POST['poster_login']);
		$poster_id = intval($bbdb->get_var( $bbdb->prepare("SELECT ID FROM $bbdb->users WHERE user_login = %s", $poster_login ) ) );
		if (!empty($poster_id) && $poster_id!=$bb_post->poster_id) {$fields[]="poster_id";}
	}
	if (!empty($_POST['post_time']) && $_POST['post_time']!=$bb_post->post_time) {$post_time=trim($_POST['post_time']); $fields[]="post_time";}
	if (!empty($_POST['post_position']) && $_POST['post_position']!=$bb_post->post_position) {$post_position=intval($_POST['post_position']); $fields[]="post_position";}
	if (!empty($fields)) {		
		$bbdb->update( $bbdb->posts, compact( $fields ), compact( 'post_id' ) );
		$topic_id=$bb_post->topic_id;	
		$fields=array_flip($fields);	// faster to check this way		
	
		$first_post = $bbdb->get_row("SELECT *  FROM $bbdb->posts WHERE topic_id = $topic_id AND post_status = 0 ORDER BY post_time ASC LIMIT 1");		
		$first_user = $bbdb->get_var( $bbdb->prepare( "SELECT user_login FROM $bbdb->users WHERE ID = %d", $first_post->poster_id ) );
		$last_post = $bbdb->get_row("SELECT *  FROM $bbdb->posts WHERE topic_id = $topic_id AND post_status = 0 ORDER BY post_time DESC LIMIT 1");
		$last_user = $bbdb->get_var( $bbdb->prepare( "SELECT user_login FROM $bbdb->users WHERE ID = %d", $last_post->poster_id ) );
				
		$topic_poster=$first_post->poster_id; $topic_fields[]="topic_poster";
		$topic_poster_name=$first_user; $topic_fields[]="topic_poster_name";
		$topic_start_time=$first_post->post_time; $topic_fields[]="topic_start_time";
		
		$topic_last_poster=$last_post->poster_id; $topic_fields[]="topic_last_poster";
		$topic_last_poster_name=$last_user; $topic_fields[]="topic_last_poster_name";
		$topic_time=$last_post->post_time; $topic_fields[]="topic_time";
		
		if (!empty($topic_fields)) {
			$bbdb->update( $bbdb->topics, compact( $topic_fields ), compact( 'topic_id' ) );
			$bb_cache->flush_one( 'topic', $topic_id );
			do_action( 'bb_update_topic', $topic_id );
		}
		}
}

function edit_post_attributes_form() {
global $edit_post_attributes;
if (!bb_current_user_can($edit_post_attributes['role'])) {return;}
	global $bb_post;
	$user=bb_get_user($bb_post->poster_id);
print <<<EPAF
	<fieldset style='border:1px solid #ccc;padding:0 1em 1em 1em;margin-bottom:1em;'><legend>Edit Post Attributes</legend>
	<div  style='float:left;'><label>Username:</label> <input name="poster_login" id="poster_login" size="30" maxlength="30" type="text" value="$user->user_login"></div>
	<div style='float:left;margin-left:1em;'><label>Date:</label> <input name="post_time" id="post_time" size="30" maxlength="30" type="text" value="$bb_post->post_time"></div>
	<div style='float:left;margin-left:1em;'><label>Position:</label> <input name="post_position" id="post_position" size="3" maxlength="3" type="text" value="$bb_post->post_position"></div>
	</fieldset>
EPAF;
}
?>