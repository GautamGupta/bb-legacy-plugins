<?php
/*
Plugin Name: bbPress Recent Posts
Plugin URI: http://bbpress.org/plugins/
Description:  shows most recent posts with (extremely limited) moderation options
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.01
*/

function recent_posts_admin_page() {
	if ( !bb_current_user_can('browse_deleted') ) {die(__("Now how'd you get here?  And what did you think you'd being doing?"));}
	global $bbdb, $bb_posts, $bb_post, $page;
	$bb_posts=get_latest_posts(0,$page);
	$total = bb_count_last_query();
?>
<h2>Recent Posts</h2>
<ol id="the-list">
<? bb_admin_list_posts();  ?>
</ol>
<?php echo get_page_number_links( $page, $total );
}

function recent_posts_admin_menu() {
	global $bb_submenu;
	$bb_submenu['content.php'][] = array(__('Recent Posts'), 'use_keys', 'recent_posts_admin_page');
}
add_action( 'bb_admin_menu_generator', 'recent_posts_admin_menu' );
?>
