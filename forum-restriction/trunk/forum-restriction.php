<?php
/*
Plugin Name: Forum Restriction
Plugin URI:  http://bbpress.org/forums/topic/591
Description: This is intended to restrict access to any forum to specifically listed individuals.
Author: David Bessler
Author URI: http://davidbessler.com
Version: 1.3
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
			<p>Enter the user names for each user to whom you would like to grant access to a restricted forum.  Separate users in the list with commas and NO SPACES between names.  You may need to use the "display name" for the users if you have the display-names plugin installed.</p>
			<?php
			echo "<table><tr> <th>Forum<br/>ID</th> <th>Forum Name</th> <th>Allowed Users</th> </tr>";
			foreach($forums as $forum) {
				echo "<tr><td style=\"text-align: center\">$forum->forum_id</td><td>$forum->forum_name</td><td><input type=\"text\" name=\"forum_restriction_for_forum[$forum->forum_id]\" value=\"{$forum_restriction_allowed_in_forum[$forum->forum_id]}\" size=\"75\"></td></tr>";
			}
			if (bb_get_option('forum_restriction_notify') == "on") {
				$forum_restriction_notify_or_not = "CHECKED";
			}
			echo "</table>";
			?>

			<p><input type="checkbox" name="forum_restriction_notify" <?php echo $forum_restriction_notify_or_not ?>>  Email members for new topics?</p>
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
			$forum_restriction_notify = $_POST['forum_restriction_notify'];
			bb_update_option('forum_restriction_notify',$forum_restriction_notify);
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

// Add allowed users to description so you know who can see what you're writing

function forum_restriction_alter_front_page_forum_description( $r ) {
	global $bb_current_user,$forum,$forum_restriction_allowed_in_forum;
	if (is_front()){
		if ($bb_current_user){
			$pos = strpos($forum_restriction_allowed_in_forum[$forum->forum_id],get_user_name($bb_current_user->ID));
		} else {
			$pos = FALSE;
		}
		if ($pos === FALSE && !empty($forum_restriction_allowed_in_forum[$forum->forum_id])) {
			return $r;
		} elseif (!empty($forum_restriction_allowed_in_forum[$forum->forum_id])) {
			$r .= '<p style="background-color: yellow;">Users with access to this forum:  '.$forum_restriction_allowed_in_forum[$forum->forum_id].'</p>';
			return $r;
		} else {
			return $r;
		}
	}
}

add_filter('forum_description', 'forum_restriction_alter_front_page_forum_description');

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

// Email forum members

function forum_restriction_send_email ($topic_id){
	if (bb_get_option('forum_restriction_notify') == "on") {
		global $forum_restriction_allowed_in_forum, $bbdb, $bb_current_user;
		$topic = get_topic($topic_id);
		$forum = $topic->forum_id;
		$mailing_list = split(",",$forum_restriction_allowed_in_forum[$forum]);
		$message = __("There is a new topic called: %1\$s.  You can see it here %3\$s.  You got this email because Bessler decided to turn on the option to notify all members of restricted forums when there are new TOPICS started in each FORUM.  However, don't forget to click on \"Add this topic to your favorites\" so that you get an email when someone adds a POST to this TOPIC.  This will keep you involved in that particular discussion.  Kapish?");
		foreach ($mailing_list as $member) {
			$userdata = $bbdb->get_var("SELECT ID FROM $bbdb->users WHERE `display_name` = '$member'");
			$email_address .= $bbdb->get_var("SELECT user_email FROM $bbdb->users WHERE ID='$userdata'").",";
		}
		mail( $email_address, '['.bb_get_option('name') . '] ' . __('New Topic In Private Forum:').get_forum_name($forum),
			sprintf( $message, $topic->topic_title, get_user_name($bb_current_user->ID), get_topic_link($topic_id) ),
			'From: ' . bb_get_option('admin_email')
		);
	}
}

add_action('bb_new_topic', 'forum_restriction_send_email');

/*

*/