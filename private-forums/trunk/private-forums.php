<?php
/*
Plugin Name: Private Forums
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-private-forums/
Description: Regulate Access to forums in bbPress
Author: Aditya Naik
Version: 5.0
Author URI: http://www.adityanaik.com/

Version History:
1.0		: Initial Release
1.1		: bug fix for empty private forums
			: Added failsafe for installation.
2.0		: Added choice to hide private forums or show them with private prefix
			: Added selectable prefix text
			: Removed redundant forum_access_update_option
2.1		: Created Common Submit for all options
3.0		: Fixed the submenu generation
			: Fixed Forum Filter
3.1		: Fixed <?
4.0		: Added Restriction by User Role
			: Renamed Functions to be prefixed by private_forums instead of forum_access
			: Changed where the options are stored.
			: Added Upgrade Function
			: Options can now be set by role administrator
5.0		: Access Roles can be set for each forums now
			: Fixed the results of search
			: Fixed RSS 
*/

private_forums_upgrade();
add_action( 'bb_admin_menu_generator', 'private_forums_add_admin_page' );
add_action( 'bb_init', 'private_forums_initialize_filters');
add_action( 'init', 'private_forums_initialize_filters');

function private_forums_add_admin_page() {
	bb_admin_add_submenu(__('Private Forums'), 'administrate', 'private_forums_admin_page');

}

function private_forums_custom_get_options($option) {
	global $private_forums_options;
	if(!isset($private_forums_options)) {
		$private_forums_options = bb_get_option('private_forums_options');
		$private_forums = $private_forums_options['private_forums'];
		$private_forums_for_user = array();
		foreach($private_forums as $forum => $role) {
			if(!private_forums_check_user_access_to_forum($role))
				$private_forums_for_user[$forum] = $role;
		}
		$private_forums_options['private_forums'] = $private_forums_for_user;
	}
	return $private_forums_options[$option];
}

function private_forums_upgrade() {
	$private_forums_options = bb_get_option('private_forums_options');
	if(!isset($private_forums_options['version']) || $private_forums_options['version'] < 40) {
		private_forums_upgrade_4_0();		
		$private_forums_options = bb_get_option('private_forums_options');
	}
	if($private_forums_options['version'] < 41) {
		private_forums_upgrade_4_1();
	}
}

function private_forums_upgrade_4_0() {
	$private_forums_options = array();
	
	$forum_access_private_forums = bb_get_option('forum_access_private_forums');
	$private_forums_options['private_forums'] = ($forum_access_private_forums) ? $forum_access_private_forums : array() ;
	$private_forums_options['failure_msg'] = bb_get_option('forum_access_failure_msg');
	$private_forums_options['hide_show_flag'] = bb_get_option('forum_access_private_forums_option');
	$private_forums_options['user_role'] = "MEMBER";
	$private_forums_options['private_text'] = bb_get_option('forum_access_private_text');
	$private_forums_options['version'] = 40;
	
	bb_delete_option('forum_access_private_forums');
	bb_delete_option('forum_access_failure_msg');
	bb_delete_option('forum_access_private_forums_option');
	bb_delete_option('forum_access_private_text');
	bb_update_option('private_forums_options', $private_forums_options);
}

function private_forums_upgrade_4_1() {
	$private_forums_options = bb_get_option('private_forums_options');
	$private_forums = $private_forums_options['private_forums'];
	$user_role = $private_forums_options['user_role'];
	if('ADMINSTRATOR' == $user_role) $user_role = 'ADMINISTRATOR';
	foreach($private_forums as $forums => $role) {
		$private_forums[$forums] = $user_role;
	}
	$private_forums_options['private_forums'] = $private_forums;
	$private_forums_options['version'] = 41;
	unset($private_forums_options['user_role']);
	bb_update_option('private_forums_options', $private_forums_options);
}

function private_forums_display_role_dropdown($name, $index, $role) {
	?>
	<p>
		<select name="<?php echo $name . '[' . $index . ']'; ?>" id="<?php echo $name . '_' . $index ; ?>">
			<option value="OPEN" <?php echo ($role == 'OPEN') ? 'selected' : '' ; ?>>Open to all</option>
			<option value="MEMBER" <?php echo ($role == 'MEMBER') ? 'selected' : '' ; ?>>Registered Members</option>
			<option value="MODERATOR" <?php echo ($role == 'MODERATOR') ? 'selected' : '' ; ?>>Moderators</option>
			<option value="ADMINISTRATOR" <?php echo ($role == 'ADMINISTRATOR') ? 'selected' : '' ; ?>>Administrators</option>
		</select>
	</p>
	<?php
}

function private_forums_admin_page() {
	$forums = get_forums();
	$private_forums_options = bb_get_option('private_forums_options');
	$private_forums = $private_forums_options['private_forums'];
	?>
		<h2>Private Forums</h2>
		<form method="post" name="private_forum_form" id="private_forum_form">
			<table class="widefat">
				<thead>
					<tr>
						<th>Forum</th>
						<th>Restrict to Role</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($forums as $forum) {
						?>
						<tr>
							<td><label for="private_forums_<?php echo $forum->forum_id; ?>"><?php echo $forum->forum_name; ?></label></td>
							<td><?php private_forums_display_role_dropdown('private_forums', $forum->forum_id, $private_forums[$forum->forum_id]); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" value="Submit"></p>
		<h2>Privacy Options</h2>
		<?php
			$checked = "checked='checked'";
			if ( 'SHOW_PRIVATE' == private_forums_custom_get_options('hide_show_flag')) {
				$sp = $checked;
		 	} else {
		 		$h = $checked;
		 	}
		?>
			<p>
				<input type="radio" id="hide_show_flag_1" name="hide_show_flag" value="HIDE" <?php echo $h; ?>/> <label for="hide_show_flag_1">Hide Private Forums and Topics</label>
			</p>
			<p>
				<input type="radio" id="hide_show_flag_2" name="hide_show_flag" value="SHOW_PRIVATE" <?php echo $sp; ?>/>
				<label for="hide_show_flag_2">Mark Private Forums and Topics with</label>
				<input type="text" onfocus="document.private_forum_form.hide_show_flag[1].checked=true"
				name="private_text"
				value="<?php $text = private_forums_custom_get_options('private_text');
				if (empty($text) && 'SHOW_PRIVATE' == private_forums_custom_get_options('hide_show_flag')) {
					echo  'private';
				} else {
					echo private_forums_custom_get_options('private_text');
				} ?>"/>
			</p>
			<p class="submit"><input type="submit" name="submit" value="Submit"></p>
		<h2>Error Options</h2>

			<p>When a user tries to access a private forum without having access
			he will be redirected to a error page with the following text.</p>
			<p><strong>Usage:</strong><br/>
				<ul>
					<li>Use '%s' for the context of the error. If the error occurs when the user tries to access a private forum, '%s' will be replaces by 'forum'.</li>
					<li>Word 'login' will be replaced by a link to the login page.</li>
				</ul>
			</p>


			<textarea name="failure_msg" id="failure_msg" style="width: 98%;" rows="3" cols="50"><?php echo private_forums_custom_get_options('failure_msg'); ?></textarea>

			<p class="submit"><input type="submit" name="submit" value="Submit"></p>
		</form>
		<?php
}

function private_forums_process_post() {
	if(isset($_POST['submit'])) {
		$private_forums_options = bb_get_option('private_forums_options');
		$private_forums_options['private_forums'] = (isset($_POST['private_forums'])) ? $_POST['private_forums'] : array();
		$private_forums_options['failure_msg'] = $_POST['failure_msg'];
		$private_forums_options['hide_show_flag'] = $_POST['hide_show_flag'];
		if ('SHOW_PRIVATE'== $private_forums_options['hide_show_flag']) {
			$private_forums_options['private_text'] = empty($_POST['private_text']) ? 'private' : $_POST['private_text'];
		}
		
		bb_update_option('private_forums_options',$private_forums_options);
		unset($GLOBALS['private_forums_options']);
	}
}

function private_forums_initialize_filters() {
	global $bb,$bb_current_user;

	add_action( 'bb_admin-header.php','private_forums_process_post');
	$login_page = $bb->path . 'bb-login.php';

	if ($_SERVER['PHP_SELF'] != $login_page) {
		$private_forums = private_forums_custom_get_options('private_forums');
		if ( !empty($private_forums)) {
			add_action( 'bb_forum.php_pre_db', 'private_forums_check_private_forum' );
			add_action( 'bb_topic.php_pre_db', 'private_forums_check_private_topic' );
			add_action( 'bb_tag-single.php', 'private_forums_check_private_tag' );
			add_action( 'bb_rss.php', 'private_forums_filter_stuff_in_rss' );

			if (private_forums_custom_get_options('hide_show_flag') == 'SHOW_PRIVATE') {
				add_filter( 'get_forum_name', 'private_forums_add_private_name_to_forums', 10,2);
				add_filter( 'get_topic_title', 'private_forums_add_private_name_to_topics', 10,2);
			} else {
				add_action( 'bb_head', 'private_forums_filter_topics_in_head' );
				add_action( 'do_search', 'private_forums_filter_stuff_in_search_results' );
				add_filter( 'get_forums','private_forums_filter_forums');
			}

		}
	}
}

function private_forums_format_error($err_msg){
	global $bb;
	if(empty($err_msg)) $err_msg = "This %s requires access to private forum. \r\nPlease login to access it.";
	$err_msg = trim($err_msg);
	$err_msg = strip_tags ( $err_msg );
	$order  = array("\r\n", "\n", "\r");
	$replace = '<br />';
	$err_msg = str_replace($order, $replace, $err_msg);
	$replace = '<a href="' . $bb->domain . $bb->path . 'bb-login.php">login</a>';
	$err_msg = str_replace('login', $replace, $err_msg);
	return $err_msg;
}


function private_forums_filter_topics_in_head() {
	global $forums, $topics, $super_stickies, $stickies, $topic, $topic_id;
	$topics = private_forums_filter_topics($topics);
	$stickies = private_forums_filter_topics($stickies);
	$super_stickies = private_forums_filter_topics($super_stickies);
}

function private_forums_filter_stuff_in_search_results() {
	global $titles, $recent, $relevant;
	$titles = private_forums_filter_topics($titles);
	$recent = private_forums_filter_posts($recent);
	$relevant = private_forums_filter_posts($relevant);
}

function private_forums_filter_stuff_in_rss() {
	global $posts, $forum_id, $topic;
	$posts = private_forums_filter_posts($posts);
	$private_forums = private_forums_custom_get_options('private_forums');
	if(array_key_exists($forum_id,$private_forums) || array_key_exists($topic->forum_id,$private_forums)) {
		exit();
	}
}

function private_forums_check_private_forum($forum_id) {

	$private_forums = private_forums_custom_get_options('private_forums');
	if(array_key_exists($forum_id,$private_forums)) {
		bb_die(__(sprintf(private_forums_format_error(private_forums_custom_get_options('failure_msg')), 'forum')));
	}
}

function private_forums_check_private_topic($topic_id) {
	global $topic;

	$private_forums = private_forums_custom_get_options('private_forums');
	if(array_key_exists($topic->forum_id,$private_forums)) {
		bb_die(__(sprintf(private_forums_format_error(private_forums_custom_get_options('failure_msg')), 'topic')));
	}
}

function private_forums_check_private_tag($tag_id) {
	global $topics;

	$topics = private_forums_filter_topics($topics);

}

function private_forums_filter_forums($forums) {

	$new_forums = array();
	if ($forums) {
		$private_forums = private_forums_custom_get_options('private_forums');
		foreach($forums as $forum) {
			if(!array_key_exists($forum->forum_id,$private_forums)) {
				$new_forums[] = $forum;
			}
		}
		return $new_forums ;
	}

	return $forums;

}

function private_forums_filter_topics($topics) {

	$new_topics = array();
	if ($topics) {
		$private_forums = private_forums_custom_get_options('private_forums');
		foreach($topics as $topic) {
			if(!array_key_exists($topic->forum_id,$private_forums)) {
				$new_topics[] = $topic;
			}
		}
		return $new_topics ;
	}

	return $topics;
}

function private_forums_filter_posts($posts) {
	$new_posts = array();
	if ($posts) {
		$private_forums = private_forums_custom_get_options('private_forums');
		foreach($posts as $post) {
			if(!array_key_exists($post->forum_id,$private_forums)) {
				$new_posts[] = $post;
			}
		}
		return $new_posts ;
	}
	return $posts;
}

function private_forums_add_private_name_to_forums($text, $id) {
	$private_forums = private_forums_custom_get_options('private_forums');
	if(array_key_exists($id,$private_forums)) {
		return '[' . private_forums_custom_get_options('private_text') . '] ' . $text;
	} else {
		return $text;
	}
}

function private_forums_add_private_name_to_topics($text, $id) {
	$topic = get_topic( $id );
	$id = $topic->forum_id;
	$private_forums = private_forums_custom_get_options('private_forums');
	if(array_key_exists($id,$private_forums)) {
		return '[' . private_forums_custom_get_options('private_text') . '] ' . $text;
	} else {
		return $text;
	}
}

function private_forums_check_user_access_to_forum($access) {
	switch($access) {
		case 'MODERATOR':
			return bb_current_user_can('moderate');
			break;
		case 'ADMINISTRATOR':
			return bb_current_user_can('administrate');
			break;
		case 'MEMBER':
			return bb_current_user_can('participate');
			break;
		case 'OPEN':
			return true;
			break;
		default:
			return true;
	}
}

?>
