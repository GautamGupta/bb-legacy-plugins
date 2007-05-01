<?php
/*
 * Plugin Name: Say my name
 * Plugin Description: Sends an Notification email if someone make your name on a post. ( Based on Notify Post by Thomas Klaiber )
 * Author: Matteo Crippa
 * Author URI: http://www.ellequadro.net
 * Plugin URI: http://www.ellequadro.net/download/
 * Version: 0.1
*/
 
function say_my_name ($post_id) {
	
	global $bbdb, $bb_table_prefix, $topic_id;
	
	$post = $bbdb->get_var("SELECT post_text FROM $bbdb->posts WHERE post_id = $post_id LIMIT 1");
					
	$all_users = $bbdb->get_results("SELECT * FROM $bbdb->users WHERE user_status=0");

	$post = trim($post);
	$post = str_replace("\n", " ", $post);
	$post = str_replace("\r", " ", $post); 
		
	$post = strip_tags($post);
	
	$words = explode(" ",$post);
			
	foreach ($all_users as $userdata) :
	
		$notify = 0;
	
		foreach ( $words as $word) :
		
			if($word != "" || $word!=" "):
		
				if ( is_smn( $userdata->ID ) && ($userdata->user_login == $word) ) :
			
					$notify = 1;
			
				endif;
			
			endif;
		
		endforeach;
		
		if($notify==1):
			
			$message = __("Someone called you on: %1\$s \n\n%2\$s ");
			
			$topic = get_topic($topic_id);
			
			mail( $userdata->user_email, bb_get_option('name') . ':' . __('Notification'), sprintf( $message, "$topic->topic_title", get_topic_link($topic_id) ), 'From: ' . bb_get_option('admin_email'));
		endif;
		
	endforeach; 
	
}
add_action('bb_new_post', 'say_my_name',1,1);

function smn_profile() {
	global $user_id, $bb_current_user;
	
	if ( bb_is_user_logged_in() ) :
	
		$checked = "";
		if (is_smn($user_id)) :
			$checked = "checked='checked'";
		endif;
	
		echo "<fieldset>
<legend>Say My Name Notification</legend>
<p> " . __('If you want to get an email when someone call your name in a new post.') . "</p>
<table width=\"100%\">
<tr>
<th width=\"21%\" scope=\"row\">" . __('Activate') . ":</th>
<td width=\"79%\" ><input name=\"smn\" id=\"smn\" type=\"checkbox\" value=\"1\"" . $checked . " /></td>
</tr>
</table>
</fieldset>\n\n";
	endif;
}
add_action('extra_profile_info', 'smn_profile');

function smn_edit() {
	global $user_id;
		
	bb_update_usermeta($user_id, "smn", $_POST['smn']);
}
add_action('profile_edited', 'smn_edit');

function is_smn($user_id) {
	$user = bb_get_user( $user_id );
	if ($user->smn) :
		return true;
	else :
		return false;
	endif;
}
?>