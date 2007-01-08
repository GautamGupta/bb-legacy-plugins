<?
/*
Plugin Name: Private Forums
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-private-forums/
Description: Regulate Access to forums in bbPress
Author: Aditya Naik
Version: 2.1
Author URI: http://www.adityanaik.com/

Version History:
1.0 	: Initial Release
1.1 	: bug fix for empty private forums
		: Added failsafe for installation.
2.0 	: Added choice to hide private forums or show them with private prefix
		: Added selectable prefix text
		: Removed redundant forum_access_update_option
2.1		: Created Common Submit for all options
*/

add_action( 'bb_admin_menu_generator', 'forum_access_add_admin_page' );
add_action( 'bb_init', 'forum_access_check_private_forum');
add_action( 'init', 'forum_access_check_private_forum');

function forum_access_add_admin_page() {
	global $bb_submenu;

	$forum_func = (forum_access_check_requirements()) ? 'forum_access_admin_page' : 'forum_access_admin_page_requirement_failed';

	$bb_submenu['site.php'][] = array(__('Private Forums'), 'use_keys', $forum_func);
}

function forum_access_check_requirements() {
	return (function_exists('is_serialized')) ? true :false;
}

function forum_access_admin_page_requirement_failed() {
	?>
	<h2>Private Forums</h2>
	<p>The bbPress installation you are running does not meet requirements for Private Forums. You can do the following to fix this:</p>
	<ol>
		<li>Upgrade the installation of bbPress to the <a href="http://trac.bbpress.org/changeset/trunk?old_path=%2F&format=zip" title="latest version of code from the bbPress Development Site">latest version</a> from the code repository</li>
	</ol>
	<?php
}

function forum_access_get_option($option) {
	return (bb_get_option($option)) ? bb_get_option($option) : array() ;
}

function forum_access_admin_page() {
	$forums = get_forums();
	$priv_forums = forum_access_get_option('forum_access_private_forums');
	?>
		<h2>Private Forums</h2>
		<form method="post">
			<?php
			foreach($forums as $forum) {
				?>
					<p><input type="checkbox" name="priv_forum[<?php echo $forum->forum_id; ?>]" <?php echo 'value="' . $forum->forum_id . '" ' . ((array_key_exists($forum->forum_id,($priv_forums) ? $priv_forums : array())) ? ' checked ' : '') ; ?> > <?php echo $forum->forum_name; ?> </p>
				<?php
			}
			?>
			<p><input type="hidden" name="action" value="priv_forum_update">
			<input type="submit" name="submit" value="Submit"></p>
		<h2>Privacy Options</h2>
		<?php
			$checked = "checked='checked'";
			if ( 'SHOW_PRIVATE' == bb_get_option('forum_access_private_forums_option')) {
				$sp = $checked;
		 	} else {
		 		$h = $checked;
		 	}
		?>
			<p>
				<input type="radio" name="priv_forum_option" value="HIDE" <?php echo $h; ?>/> Hide Private Forums and Posts
			</p>
			<p>
				<input type="radio" name="priv_forum_option" value="SHOW_PRIVATE" <?php echo $sp; ?>/>
				Mark Private Forums and Posts with
				<input type="text" onfocus="document.forms[1].priv_forum_option[1].checked=true"
				name="priv_forum_private_text"
				value="<?php $text = bb_get_option('forum_access_private_text');
				if (empty($text) && 'SHOW_PRIVATE' == bb_get_option('forum_access_private_forums_option')) {
					echo  'private';
				} else {
					echo bb_get_option('forum_access_private_text');
				} ?>"/>
			</p>
			<p><input type="submit" name="submit" value="Submit"></p>
		<h2>Error Options</h2>

			<p>When a user tries to access a private forum without having access
			he will be redirected to a error page with the following text.</p>
			<p><strong>Usage:</strong><br/>
				<ol>
					<li>Use '&lt;br/&gt;' for line breaks.</li>
					<li>Use '%s' for the context of the error. If the error occurs when the user tries to access a private forum, '%s' will be replaces by 'forum'.</li>
					<li>Word 'login' will be replaced by a link to the login page.</li>
				</ol>
			</p>


			<textarea name="failure_msg" id="failure_msg" style="width: 98%;" rows="3" cols="50"><?php echo bb_get_option('forum_access_failure_msg'); ?></textarea>

			<p><input type="submit" name="submit" value="Submit"></p>
		</form>
		<?php
}

function forum_access_check_private_forum() {
	global $bb,$bb_current_user, $forum_access_failure_msg;

	if (forum_access_check_requirements()) {
		add_action( 'bb_admin-header.php','forum_access_process_post');
		$login_page = $bb->path . 'bb-login.php';

		if ($_SERVER['PHP_SELF'] != $login_page) {
			$priv_forums = forum_access_get_option('forum_access_private_forums');
			if ( !$bb_current_user && !empty($priv_forums)) {
				add_action( 'bb_forum.php_pre_db', 'forum_access_forum_init' );
				add_action( 'bb_topic.php_pre_db', 'forum_access_topic_init' );
				add_action( 'bb_tag-single.php', 'forum_access_tag_single_init' );

				if (bb_get_option('forum_access_private_forums_option') == 'SHOW_PRIVATE') {
					add_filter( 'get_forum_name', 'forum_access_add_private_name_to_forums', 10,2);
					add_filter( 'get_topic_title', 'forum_access_add_private_name_to_topics', 10,2);
				} else {
					add_action( 'bb_head', 'forum_access_head_init' );
				}

		 		$forum_access_failure_msg = forum_access_format_error(bb_get_option('forum_access_failure_msg'));

			}
		}
	}
}

function forum_access_process_post() {
	if(isset($_POST['submit'])) {
		if ('priv_forum_update' == $_POST['action']) {

			//Private Forums Update
			$priv_forums = (isset($_POST['priv_forum'])) ? $_POST['priv_forum'] : array();
			bb_update_option('forum_access_private_forums',$priv_forums);

			//Error Message Update
			$err_msg = $_POST['failure_msg'];
			bb_update_option('forum_access_failure_msg',$err_msg);

			//Privacy Option Update
			bb_update_option('forum_access_private_forums_option',$_POST['priv_forum_option']);
			if ('SHOW_PRIVATE'== bb_get_option('forum_access_private_forums_option')) {
				$text = empty($_POST['priv_forum_private_text']) ? 'private' : $_POST['priv_forum_private_text'];
				bb_update_option('forum_access_private_text',$text);
			}

		}
	}
}

function forum_access_format_error($err_msg){
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


function forum_access_head_init() {
	global $forum_access_failure_msg, $forums, $topics, $super_stickies, $stickies, $topic, $topic_id;
	$forums = forum_access_filter_forums($forums);
	$topics = forum_access_filter_topics($topics);
	$stickies = forum_access_filter_topics($stickies);
	$super_stickies = forum_access_filter_topics($super_stickies);
}

function forum_access_forum_init($forum_id) {
	global $forum_access_failure_msg;

	$priv_forums = forum_access_get_option('forum_access_private_forums');
	if(array_key_exists($forum_id,$priv_forums)) {
		bb_die(__(sprintf($forum_access_failure_msg, 'forum')));
	}
}

function forum_access_topic_init($topic_id) {
	global $topic, $forum_access_failure_msg;

	$priv_forums = forum_access_get_option('forum_access_private_forums');
	if(array_key_exists($topic->forum_id,$priv_forums)) {
		bb_die(__(sprintf($forum_access_failure_msg, 'topic')));
	}
}

function forum_access_tag_single_init($tag_id) {
	global $topics, $forum_access_failure_msg;

	$topics = forum_access_filter_topics($topics);
	if (empty($topics)) {
		bb_die(__(sprintf($forum_access_failure_msg, 'tag')));
	}
}

function forum_access_filter_forums($forums) {

	$new_forums = array();
	if ($forums) {
		$priv_forums = forum_access_get_option('forum_access_private_forums');
		foreach($forums as $forum) {
			if(!array_key_exists($forum->forum_id,$priv_forums)) {
				$new_forums[] = $forum;
			} else {
				//$new_forums[] = $forum;
			}
		}
		return $new_forums ;
	}

	return $forums;

}

function forum_access_add_private_name_to_forums($text, $id) {
	$priv_forums = forum_access_get_option('forum_access_private_forums');
	if(array_key_exists($id,$priv_forums)) {
		return '[' . bb_get_option('forum_access_private_text') . '] ' . $text;
	} else {
		return $text;
	}
}

function forum_access_add_private_name_to_topics($text, $id) {
	$topic = get_topic( $id );
	$id = $topic->forum_id;
	$priv_forums = forum_access_get_option('forum_access_private_forums');
	if(array_key_exists($id,$priv_forums)) {
		return '[' . bb_get_option('forum_access_private_text') . '] ' . $text;
	} else {
		return $text;
	}
}

function forum_access_filter_topics($topics) {

	$new_topics = array();
	if ($topics) {
		$priv_forums = forum_access_get_option('forum_access_private_forums');
		foreach($topics as $topic) {
			if(!array_key_exists($topic->forum_id,$priv_forums)) {
				$new_topics[] = $topic;
			}
		}
		return $new_topics ;
	}

	return $topics;
}

?>