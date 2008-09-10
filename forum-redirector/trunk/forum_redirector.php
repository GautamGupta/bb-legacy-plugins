<?php
/*
Plugin Name: Private Forums
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-private-forums/
Description: Regulate Access to forums in bbPress
Author: Aditya Naik
Version: 5.0
Author URI: http://www.adityanaik.com/
*/

	//HOOK
	add_action( 'bb_forum.php_pre_db', 'check_forum_redirect' );
	
function check_forum_redirect($forum_id) {
	
	$redirection_forum = 2;
	if($forum_id == $redirection_forum) {
		wp_redirect('http://localhost/old/bbpress/topic.php?id=6');
		var_dump($redirection_forum);
	}
}

?>