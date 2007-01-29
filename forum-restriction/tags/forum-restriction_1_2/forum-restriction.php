<?php
/*
Plugin Name: Forum Restriction
Plugin URI:  http://bbpress.org/forums/topic/591
Description: This is intended to restrict access to any forum to specifically listed individuals.
Author: David Bessler
Author URI: http://davidbessler.com
Version: 1.2
*/

$forum_restriction_allowed_in_forum = bb_get_option('forum_restriction_db');


//  ADMIN GUI
add_action( 'bb_admin_menu_generator', 'forum_restriction_add_admin_page' );

function forum_restriction_add_admin_page() {
	global $bb_submenu;
	$forum_func = (forum_restriction_check_requirements()) ? 'forum_restriction_admin_page' : 'forum_restriction_admin_page_requirement_failed';

	$bb_submenu['site.php'][] = array(__('Forum Restriction'), 'use_keys', $forum_func);
}

function forum_restriction_check_requirements() {
	return (function_exists('is_serialized')) ? true :false;
}

if (forum_restriction_check_requirements()) {
	add_action( 'bb_admin-header.php','forum_restriction_process_post');
	$login_page = $bb->path . 'bb-login.php';
}

function forum_restricition_admin_page_requirement_failed() {
	?>
	<h2>Forum Restriction</h2>
	<p>The bbPress installation you are running does not meet requirements for Forum Restriction. You can do the following to fix this:</p>
	<ol>
		<li>Upgrade the installation of bbPress to the <a href="http://trac.bbpress.org/changeset/trunk?old_path=%2F&format=zip" title="latest version of code from the bbPress Development Site">latest version</a> from the code repository</li>
	</ol>
	<?php
}

function forum_restriction_admin_page() {
	$forums = get_forums();
	global $forum_restriction_allowed_in_forum;
	?>
		<h2>Forum Restriction</h2>
		<form method="post">
			<input type="hidden" name="action" value="forum_restriction_update">
			<h3>Current Settings</h3>
			<?php
			echo "<table><tr> <th>Forum<br/>ID</th> <th>Forum Name</th> <th>Allowed Users</th> </tr>";
			foreach($forums as $forum) {
				echo "<tr><td style=\"text-align: center\">$forum->forum_id</td><td>$forum->forum_name</td><td><input type=\"text\" name=\"forum_restriction_for_forum[$forum->forum_id]\" value=\"{$forum_restriction_allowed_in_forum[$forum->forum_id]}\" size=\"75\"></td></tr>";
			}
			echo "</table>";
			?>
		<p><input type="submit" name="submit" value="Submit"></p>
		</form>
	<?php
}

// Process the info posted from the admin page
function forum_restriction_process_post() {
	if(isset($_POST['submit'])) {
		if ('forum_restriction_update' == $_POST['action']) {
			$forum_restriction_to_add_to_db = (isset($_POST['forum_restriction_for_forum'])) ? $_POST['forum_restriction_for_forum'] : array ();
			bb_update_option('forum_restriction_db',$forum_restriction_to_add_to_db);
		}
	}
}

//  ENTIRE FORUM HIJACKING

function forum_restriction_alter_front_page_forum_list( $forums ) {
	global $bb_current_user,$forum,$forum_restriction_allowed_in_forum;
	$new_forums = array();
		if($forums) {
			foreach($forums as $forum) {
				if ($bb_current_user){
					$pos = strpos($forum_restriction_allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
				} else {
					$pos = FALSE;
				}
				if ($pos === FALSE && !empty($forum_restriction_allowed_in_forum[$forum->forum_id])) {
						//$new_forums[] = $forum;
				} else {
						$new_forums[] = $forum;
				}
			}
			return $new_forums ;
		}
		return $forums;
}

add_filter('get_forums', 'forum_restriction_alter_front_page_forum_list');

//  FOR TOPICS ON FRONT PAGE

function break_up_forum_array( $forums ) {
	global $forums;
	forum_restriction_alter_front_page_forum_list( $forums );
	foreach ( $forums as $forum ) {
		$forum_ids .= $forum->forum_id.'\',\'';
	}
	$forum_ids = rtrim($forum_ids, ',\'\' ');
	return $forum_ids;
}

function forum_restriction_get_topics_where_plugin( $where ) {
	if ( is_front() ) {
		$list_of_allowed_forums = break_up_forum_array( $forums ) ;
		$where .= " AND forum_id IN ('$list_of_allowed_forums') ";
		return $where;
	} else {
		return $where;
	}
}

 add_filter ( 'get_latest_topics_where', 'forum_restriction_get_topics_where_plugin' );


//  PAGE HIJACKINGS
//  FOR FORUM.PHP
function forum_restriction_hijack_forum_page() {
		global $bb_current_user,$forum,$forum_restriction_allowed_in_forum;
	if (is_forum()){
		if ($bb_current_user){
			$pos = strpos($forum_restriction_allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($forum_restriction_allowed_in_forum[$forum->forum_id])) {
			$wheretogo = bb_nonce_url( bb_get_option('uri'));
			header( 'Location:'.$wheretogo );
		} else {
		}
	}
}

add_action('bb_forum.php_pre_db', 'forum_restriction_hijack_forum_page');

//  FOR TOPIC.PHP
function forum_restriction_hijack_topic_page() {
		global $bb_current_user,$topic,$forum_restriction_allowed_in_forum;
	if (is_topic()){
		if ($bb_current_user){
			$pos = strpos($forum_restriction_allowed_in_forum[$topic->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($forum_restriction_allowed_in_forum[$topic->forum_id])) {
			$wheretogo = bb_nonce_url( bb_get_option('uri'));
			header( 'Location:'.$wheretogo );
		} else {
		}
	}
}

add_action('bb_topic.php_pre_db', 'forum_restriction_hijack_topic_page');