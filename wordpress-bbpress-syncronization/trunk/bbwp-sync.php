<?php
/*
Plugin Name: bbPress-WordPress syncronization
Plugin URI: http://bobrik.name
Description: Sync your WordPress comments to bbPress forum and back.
Author: Ivan Babrou <ibobrik@gmail.com>
Version: 0.3
Author URI: http://bobrik.name
*/

// FIXME: change path!
require_once(dirname(__FILE__).'/../../bb-load.php');

// for mode checking
$bbwp_plugin = 0;

if (substr($_SERVER['PHP_SELF'], -13) != 'bbwp-sync.php')
{
	global $bbwp_plugin;
	$bbwp_plugin = 1;
	//print_r(bb_get_post(12));
	// working as plugin
	//echo "Plugin ".substr($_SERVER['PHP_SELF'], -14);
} else
{
	// listening commands from WordPress part
	bbwp_listener();
}


function send_command($pairs = array())
{
	$url = bb_get_option('bbwp_wordpress_url')."?wpbb-listener";
	$user = bb_get_current_user();
	// FIXME: anonymous user workaroud (anonymous for bbPress?)
	$pairs['user'] = $user->ID;
	$pairs['username'] = $user->user_login;
	$ch = curl_init($url);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $pairs);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$answer = curl_exec($ch);
	curl_close($ch);
	return $answer;
}

function test_pair()
{
	$answer = send_command(array('action' => 'test'));
	// return 1 if test passed, 0 otherwise
	// TODO: check configuration!
	$data = unserialize($answer);
	return $data['test'] == 1 ? 1 : 0;
}

function secret_key_equal()
{
	$answer = send_command(array('action' => 'keytest', 'secret_key' => bb_get_option('bbwp_secret_key')));
	$data = unserialize($answer);
	return $data['keytest'];
}

function comare_keys_local()
{
	return $_POST['secret_key'] == bb_get_option('bbwp_secret_key') ? 1 : 0;
}

function bbwp_listener()
{
	// setting authorized user
	if ($_POST['user'] != -1)
	{
		bb_set_current_user($_POST['user']);
	} else
	{
		bb_set_current_user(bb_get_option('bbwp_anonymous_user_id'));
	}
	//error_log("GOT COMMAND for bbPress part: ".$_POST['action']);
	if ($_POST['action'] == 'test')
	{
		echo serialize(array('test' => 1));
		return;
	} elseif ($_POST['action'] == 'keytest')
	{
		echo serialize(array('keytest' => comare_keys_local()));
		return;
	}
	// here we need secret key, only if not checking settings
	if (!secret_key_equal() && $_POST['action'] != 'check_bb_settings')
	{
		// go away, damn cheater!
		return;
	}
	if ($_POST['action'] == 'check_bb_settings')
	{
		$code = check_bb_settings();
		echo serialize(array('code' => $code, 'message' => bb_status_error($code)));
	} elseif ($_POST['action'] == 'set_bb_plugin_status')
	{
		set_bb_plugin_status();
	}
	// we need enabled plugins for next actions
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
	{
		// stop sync
		return;
	}
	if ($_POST['action'] == 'create_topic')
	{
		create_bb_topic();
	} elseif ($_POST['action'] == 'continue_topic')
	{
		continue_bb_topic();
	} elseif ($_POST['action'] == 'edit_post')
	{
		edit_bb_post();
	} elseif ($_POST['action'] == 'edit_post_status')
	{
		edit_bb_post_status();
	} elseif ($_POST['action'] == 'open_bb_topic')
	{
		open_bb_topic();
	} elseif ($_POST['action'] == 'close_bb_topic')
	{
		close_bb_topic();
	} elseif ($_POST['action'] == 'edit_bb_tags')
	{
		edit_bb_tags();
	}
}

function create_bb_topic()
{
	$topic_id = bb_insert_topic(array('topic_title' => stripslashes($_POST['topic']), 'forum_id' => bb_get_option('bbwp_forum_id'), 'tags' => stripslashes($_POST['tags'])));
	remove_all_filters('pre_post');
	$post_id = bb_insert_post(array('topic_id' => $topic_id, 'post_text' => str_replace('\"', '"', $_POST['post_content'])));
	bb_delete_post($post_id, status_wp2bb($_POST['comment_approved']));
	$result = add_table_item($_POST["post_id"], 0, $topic_id, $post_id);
	$data = serialize(array("topic_id" => $topic_id, "post_id" => $post_id, "result" => $result));
	echo $data;
}

function continue_bb_topic()
{
	$row = get_table_item('wp_post_id', $_POST["post_id"]);
	$post_id = bb_insert_post(array("topic_id" => $row['bb_topic_id'], "post_text" => stripslashes($_POST["post_content"])));
	bb_delete_post($post_id, status_wp2bb($_POST['comment_approved']));
	$result = add_table_item($_POST['post_id'], $_POST['comment_id'], $row['bb_topic_id'], $post_id);
	$data = serialize(array("topic_id" => $row['bb_topic_id'], "post_id" => $post_id, "result" => $result));
	echo $data;
}

function edit_bb_post()
{
	if (isset($_POST['get_row_by']) && $_POST['get_row_by'] == 'wp_post')
		$row = get_table_item('wp_post_id', $_POST["post_id"]);
	else
		$row = get_table_item('wp_comment_id', $_POST["comment_id"]);
	// updating topic title
	if (isset($_POST['topic_title']))
		bb_insert_topic(array('topic_title' => $_POST['topic_title'], 'topic_id' => $row['bb_topic_id']));
	// FIXME: delete <p> from beginning and </p> from the end
	$_POST['post_content'] = str_replace(array('<p>', '</p>'), '', $_POST['post_content']);
	// remove filters to save formatting
	remove_all_filters('pre_post');
	bb_insert_post(array('post_text' => str_replace('\"', '"', $_POST['post_content']), 'post_id' => $row['bb_post_id'], 'topic_id' => $row['bb_topic_id']));
	bb_delete_post($row['bb_post_id'], status_wp2bb($_POST['comment_approved']));
}

function open_bb_topic()
{
	bb_open_topic($_POST['topic_id']);
}

function close_bb_topic()
{
	bb_close_topic($_POST['topic_id']);
}

function bbwp_install()
{
	// create table at first install
	global $bbdb;
	$bbwp_sync_db_version = 0.2;
	$table = $bbdb->prefix."bbwp_ids";
	if ($bbdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE $table (
			`wp_comment_id` INT UNSIGNED NOT NULL,
			`wp_post_id` INT UNSIGNED NOT NULL,
			`bb_topic_id` INT UNSIGNED NOT NULL,
			`bb_post_id` INT UNSIGNED NOT NULL
		);";
		require_once(BB_PATH.'bb-admin/includes/functions.bb-upgrade.php');
		bb_sql_delta($sql);
		
		backpress_add_option('bbwp_sync_db_version', $bbwp_sync_db_version);
	}
	
	$installed_version = backpress_get_option('bbwp_sync_db_version');
	// upgrade table if necessary
	if ($installed_version != $bbwp_sync_db_version)
	{
		$sql = "CREATE TABLE $table (
			`wp_comment_id` INT UNSIGNED NOT NULL,
			`wp_post_id` INT UNSIGNED NOT NULL,
			`bb_topic_id` INT UNSIGNED NOT NULL,
			`bb_post_id` INT UNSIGNED NOT NULL
		);";
		require_once(BB_PATH.'bb-admin/includes/functions.bb-upgrade.php');
		bb_sql_delta($sql);
		backpress_update_option('bbwp_sync_db_version', $bbwp_sync_db_version);
	}
}

function add_table_item($wp_post, $wp_comment, $bb_topic, $bb_post)
{
	global $bbdb;
	return $bbdb->query("INSERT INTO ".$bbdb->prefix."bbwp_ids (wp_post_id, wp_comment_id, bb_topic_id, bb_post_id)
		VALUES ($wp_post, $wp_comment, $bb_topic, $bb_post)");
}

function get_table_item($field, $value)
{
	global $bbdb;
	return $bbdb->get_row("SELECT * FROM ".$bbdb->prefix."bbwp_ids WHERE $field = $value LIMIT 1", ARRAY_A);
}

function detele_talbe_item($field, $value)
{
	global $bbdb;
	return $bddb->query("DELETE FROM ".$bbdb->prefix."bbwp_ids WHERE $field = $value");
}

function afteredit($id)
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	// error_log("bbpress: afteredit");
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	$post = bb_get_post($id);
	$row = get_table_item('bb_post_id', $post->post_id);
	if ($row)
	{
		// have it in database, must sync
		edit_wp_comment($post, $row['wp_comment_id']);
	}
}

function afterpost($id)
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	// error_log("bbpress: afterpost");
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	$post = bb_get_post($id);
	$row = get_table_item('bb_topic_id', $post->topic_id);
	if ($row)
	{
		// need to duplicate in WordPress
		add_wp_comment($post, $row['wp_post_id']);
	}
}

function add_wp_comment($post, $wp_post_id)
{
	$request = array(
		'action' => 'add_comment',
		'post_text' => $post->post_text,
		'post_id' => $post->post_id,
		'post_status' => $post->post_status,
		'topic_id' => $post->topic_id,
		'wp_post_id' => $wp_post_id
	);
	$answer = send_command($request);
	$data = unserialize($answer);
	add_table_item($wp_post_id, $data['comment_id'], $post->topic_id, $post->post_id);
}

function edit_wp_comment($post, $comment_id)
{
	// FIXME: get post status and post text original way
	global $bbdb;
	$request = array(
		'action' => 'edit_comment',
		'post_text' => $bbdb->get_var("SELECT post_text FROM ".$bbdb->prefix."posts WHERE post_id = ".$post->post_id),
		'post_status' => $bbdb->get_var("SELECT post_status FROM ".$bbdb->prefix."posts WHERE post_id = ".$post->post_id),
		'comment_id' => $comment_id,
	);
	send_command($request);
}

function afterclosing($id)
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	// error_log("bbpress: afterclosing");
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	global $bbdb;
	$wp_post_id = $bbdb->get_var("SELECT wp_post_id FROM ".$bbdb->prefix."bbwp_ids WHERE bb_topic_id = ".$id);
	$request = array(
		'action' => 'close_wp_comments',
		'post_id' => $wp_post_id,
	);
	send_command($request);
}

function afteropening($id)
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	// error_log("bbpress: afteropening");
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	global $bbdb;
	$wp_post_id = $bbdb->get_var("SELECT wp_post_id FROM ".$bbdb->prefix."bbwp_ids WHERE bb_topic_id = ".$id);
	$request = array(
		'action' => 'open_wp_comments',
		'post_id' => $wp_post_id,
	);
	send_command($request);
}

function status_wp2bb($status)
{
	// return bbPress post status equal to WordPres comment status
	if ($status == 1)
		return 0; // hold
	if ($status == 0)
		return 1; // approved
	if ($status == 'spam')
		return 2; // spam
}

function options_page() {
	bb_admin_add_submenu(__('WordPress syncronization'), 'moderate', 'bbwp_options', 'options-general.php');
}

function bbwp_options()
{
?>
<h2><?php _e('WordPress syncronization options'); ?></h2>

<form class="settings" method="post" action="">
	<fieldset>
		<div>
		<label for="forum_id">
			<?php _e('Forum:') ?>
		</label>
		<div>
			<select name="forum_id" id="forum_id">
				<?php
					$forums = get_forums();
					$forum_id = bb_get_option('bbwp_forum_id');
					echo '<option value="-1">Select forum</option>';
					foreach ($forums as $forum)
					{
						echo '<option value="'.$forum->forum_id.'"'.($forum_id == $forum->forum_id ? " selected='selected'" : '').'>'.$forum->forum_name.'</option>';
					}
					?>
			</select>
			<?php _e('You need to set the forum for syncronization.'); ?>
		</div>
		</div>
		<div>
		<label for="wordpress_url">
			<?php _e('WordPress url:') ?>
		</label>
		<div>
			<input class="text" name="wordpress_url" value="<?php echo bb_get_option('bbwp_wordpress_url'); ?>" />
			<?php
			if (!bb_get_option('bbwp_wordpress_url'))
			{
				_e('Please submit with last "/"! We will test that after submussion');
			} else
			{
				if (test_pair())
				{
					_e('Everything is ok!');
				} else
				{
					_e('URL is incorrect or connection error, please verify it (full variant): '.bb_get_option('bbwp_wordpress_url')."?wpbb-listener");
				}
			}
			?>
		</div>
		</div>
		<div>
		<label for="secret_key">
			<?php _e('Secret key:') ?>
		</label>
		<div>
			<input class="text" name="secret_key" value="<?php echo bb_get_option('bbwp_secret_key'); ?>" />
			<?php
			if (!bb_get_option('bbwp_secret_key'))
			{
				_e('We need it for secure communication between your systems');
			} else
			{
				if (secret_key_equal())
				{
					_e('Everything is ok!');
				} else
				{
					_e("Error! Not equal secret keys in WordPress and bbPress", $textdomain);
				}
			}
			?>
		</div>
		</div>
		<div>
		<label for="anonymous_user">
			<?php _e('Anonymous user:') ?>
		</label>
		<div>
			<input class="text" name="anonymous_user" value="<?php echo bb_get_option('bbwp_anonymous_user_id'); ?>" />
			<?php _e('User id for posts on forum from unregistered users in WordPress', $textdomain); ?>
		</div>
		</div>
		<div>
		<label for="enable_plugin">
			<?php _e('Enable plugin'); ?>
		</label>
		<div>
		<?php $check = check_bbwp_settings(); ?>
			<input type="checkbox" name="plugin_status"<?php echo (bb_get_option('bbwp_plugin_status') == 'enabled') ? ' checked="checked"' : ''; echo ($check['code'] == 0) ? '' : ' disabled="disabled"'; ?> /> (<?php echo ($check['code'] == 0) ? 'Allowed by both parts' : 'Not allowed: '.$check['message'] ?>)
		</div>
		</div>
		<input type="hidden" name="action" value="update-bbwp-configuration" />
		<div class="spacer">
			<input type="submit" name="submit" id="submit" value="<?php _e('Update Configuration &raquo;') ?>" />
		</div>
	</fieldset>
</form>
<?php
}

function process_options()
{
	if ($_POST['action'] == 'update-bbwp-configuration') {
		bb_update_option('bbwp_forum_id', $_POST['forum_id']);
		bb_update_option('bbwp_wordpress_url', $_POST['wordpress_url']);
		bb_update_option('bbwp_secret_key', $_POST['secret_key']);
		bb_update_option('bbwp_anonymous_user_id', $_POST['anonymous_user']);
		$_POST['plugin_status'] == 'on' ? set_global_plugin_status('enabled') : set_global_plugin_status('disabled');
		bb_admin_notice( __('Configuration saved.') );
	}
}

function check_bbwp_settings()
{
	$wp_settings = check_wp_settings();
	$bb_code = check_bb_settings();
	$bb_message = bb_status_error($bb_code);
	if ($bb_code != 0)
	{
		$data['code'] = $bb_code;
		$data['message'] = '[bbPress part] '.$bb_message;
	} elseif ($wp_settings['code'] != 0)
	{
		$data['code'] = $wp_settings['code'];
		$data['message'] = '[WordPress part] '.$wp_settings['message'];
	} else
	{
		$data['code'] = 0;
		$data['message'] = __('Everything is ok!');
	}
	return $data;
}

function set_global_plugin_status($status)
{
	// FIXME: fix something here.
	$wp_settings = check_wp_settings();
	if ((check_bb_settings() == 0 && $wp_settings['code'] == 0 && $status == 'enabled') || $status == 'disabled')
	{
		$wp_status = set_wp_plugin_status($status);
		if ($wp_status['status'] == $status)
		{
			bb_update_option('bbwp_plugin_status', $status);
			return;
		}
	}
	// disable everything, something wrong
	$status = 'disabled';
	$wp_status = set_wp_plugin_status($status);
	bb_update_option('bbwp_plugin_status', $status);		
}

function set_bb_plugin_status()
{
	// to be call through http request
	$status = $_POST['status'];
	if ((check_bb_settings == 0 && $status == 'enabled') || $status == 'disabled')
	{
		bb_update_option('bbwp_plugin_status', $status);
	} else {
		$status = 'disabled';
		bb_update_option('bbwp_plugin_status', 'disabled');
	}
	$data = serialize(array('status' => $status));
	echo $data;
}

function check_bb_settings()
{
	if (!test_pair())
	{
		return 1; // cannot establish connection to bb
	}
	if (!secret_key_equal())
	{
		return 2; // secret keys are not equal
	}
	global $bbdb;
	$forum_id = $bbdb->get_row('SELECT * FROM '.$bbdb->prefix.'forums WHERE forum_id = '.bb_get_option('bbwp_forum_id'), ARRAY_A);
	if (!$forum_id)
		return 3; // forum id not found;
	if (!bb_get_user(bb_get_option('bbwp_anonymous_user_id')))
		return 4; // anonymous user id not found
	return 0; // everything is ok
}

function bb_status_error($code)
{
	if ($code == 0)
		return __('Everything is ok!');
	if ($code == 1)
		return __('Cannot establish connection to bbPress part');
	elseif ($code == 2)
		return __('Invalid secret key');
	elseif ($code == 3)
		return __('Invalid forum id');
	elseif ($code == 4)
		return __('Invalid anonymous user id');
}

function set_wp_plugin_status($status)
{
	// when enabling in bbPress
	$request = array(
		'action' => 'set_wp_plugin_status',
		'status' => $status,
	);
	$answer = send_command($request);
	$data = unserialize($answer);
	return $data;
}

function check_wp_settings()
{
	$answer = send_command(array('action' => 'check_wp_settings'));
	$data = unserialize($answer);
	return $data;
}

function aftertagedit($id)
{
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	global $topic;
	$row = get_table_item('bb_topic_id', $topic->topic_id);
	if ($row)
	{
		$tags = array();
		foreach (bb_get_topic_tags($topic->topic_id) as $tag)
		{
			$tags[] = $tag->name;
		}
		edit_wp_tags($row['wp_post_id'], $tags);
	}
}

function edit_wp_tags($post, $tags)
{
	$request = array(
		'action' => 'edit_wp_tags',
		'post' => $post,
		'tags' => serialize($tags)
	);
	send_command($request);
}

function edit_bb_tags()
{
	bb_remove_topic_tags($_POST['topic']);
	bb_add_topic_tags($_POST['topic'], $_POST['tags']);
}

function pretagremove($id)
{
	global $bbwp_plugin;
	if (!$bbwp_plugin)
	{
		// we don't need endless loop ;)
		return;
	}
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return;
	global $topic;
	$row = get_table_item('bb_topic_id', $topic->topic_id);
	if ($row)
	{
		$tags = array();
		foreach (bb_get_topic_tags($topic->topic_id) as $tag)
		{
			// exclude deleted tag
			if ($tag->term_id != $id)
			{
				$tags[] = $tag->name;
			}
		}
		edit_wp_tags($row['wp_post_id'], $tags);
	}
}

function deactivate_bbwp()
{
	// deactivate on disabling
	set_global_plugin_status('disabled');
}

// TODO: catch topic deletion
// FIXME: change " to ' where no escaping
// TODO: plugin translation
// FIXME: less requests on settings page

add_action('bb_tag_added', 'aftertagedit');
add_action('bb_pre_tag_removed', 'pretagremove');
add_action('bb_update_post', 'afteredit');
add_action('bb_new_post', 'afterpost');
add_action('bb_delete_post', 'afteredit');
add_action('open_topic', 'afteropening');
add_action('close_topic', 'afterclosing');
bb_register_plugin_activation_hook('user#wordpress-bbpress-syncronization/bbwp-sync.php', 'bbwp_install');
add_action('bb_deactivate_plugin_user#wordpress-bbpress-syncronization/bbwp-sync.php', 'deactivate_bbwp');
add_action('bb_admin_menu_generator', 'options_page');
add_action('bb_admin-header.php', 'process_options');

?>
