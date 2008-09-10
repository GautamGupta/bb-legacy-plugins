<?php
/*
Plugin Name: Forum Redirector
Plugin URI: http://bbpress.org/plugins/topic/forum-redirector/
Description: Use forum redirection to get to another link when clicking on a chosen forum
Author: Benjamin Davison
Version: 0.01
Author URI: http://www.thesaferoom.org
*/

	//HOOK
	add_action( 'bb_forum.php_pre_db', 'check_forum_redirect' );
	
function check_forum_redirect($forum_id) {
	
	$redirection_forum = 2;
	if($forum_id == $redirection_forum) {
		wp_redirect('http://localhost/old/bbpress/topic.php?id=6');
	}
}

?>