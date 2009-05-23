<?php
/*
Plugin Name: bbPress-WordPress syncronization
Plugin URI: http://bobrik.name
Description: Sync your WordPress comments to bbPress forum and back.
Author: Ivan Babrou <ibobrik@gmail.com>
Version: 0.5.0
Author URI: http://bobrik.name

Copyright 2008 Ivan Babroŭ (email : ibobrik@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the license, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; see the file COPYING.  If not, write to
the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA 02111-1307, USA.
*/


// for version checking
$bbwp_version = 0.50;
$min_version = 0.50;

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
	preg_match('@https?://([\-_\w\.]+)+(:(\d+))?/(.*)@', $url, $matches);
	if (!$matches)
		return;
	if (!isset($pairs['user']))
	{
		$user = bb_get_current_user();
		if ($user->ID)
		{
			$pairs['user'] = $user->ID;
			$pairs['username'] = $user->user_login;
		} else
		{
			// anonymous user
			$pairs['user'] = 0;
		}
	}
	if (substr($url, 0, 5) == 'https')
	{
		// must use php-curl to work with https
		// FIXME: really works? :)
		$ch = curl_init($url);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $pairs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$answer = curl_exec($ch);
		curl_close($ch);
		return $answer;
	} else
	{
		$port = $matches[3] ? $matches[3] : 80;
		
		$request = '';
		foreach ($pairs as $key => $data)
			$request .= $key.'='.urlencode(stripslashes($data)).'&';

		$http_request  = "POST /$matches[4] HTTP/1.0\r\n";
		$http_request .= "Host: $matches[1]\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . bb_get_option('charset') . "\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: WordPress/".bb_get_option('version')." | WordPress-bbPress	syncronization\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;

		$response = '';
		if( false != ( $fs = @fsockopen($matches[1], $port, $errno, $errstr, 10) ) ) {
			fwrite($fs, $http_request);

			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
		return $response[1];
	}
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

function correct_wpbb_version()
{
	$answer = unserialize(send_command(array('action' => 'get_wpbb_version')));
	global $min_version;
	return ($answer['version'] < $min_version) ? 0 : 1;
}

function bbwp_listener()
{
	// setting authorized user
	if ($_POST['user'] != 0)
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
	} elseif ($_POST['action'] == 'get_bbwp_version')
	{
		global $bbwp_version;
		echo serialize(array('version' => $bbwp_version));
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
	} elseif ($_POST['action'] == 'delete_post')
	{
		delete_bb_post();
	} elseif ($_POST['action'] == 'get_topic_link')
	{
		echo serialize(array('link' => get_topic_link($_POST['topic_id'])));
	}
}

function bbwp_do_sync()
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		return false;
	global $bbwp_plugin;
	if (!$bbwp_plugin)
		return false;
	return true;
}

function sync_that_status($id)
{
	if (get_real_post_status($id) == 0 || bb_get_option('bbwp_sync_all_posts') == 'enabled')
		return true;
	else
		return false;
}

function get_real_post_status($id)
{
	global $bbdb;
	return $bbdb->get_var('SELECT post_status FROM '.$bbdb->prefix.'posts WHERE post_id = '.$id);
}

function create_bb_topic()
{
	$topic_id = bb_insert_topic(array('topic_title' => stripslashes($_POST['topic']), 'forum_id' => bb_get_option('bbwp_forum_id'), 'tags' => stripslashes($_POST['tags'])));
	remove_all_filters('pre_post');
	$post_id = bb_insert_post(array('topic_id' => $topic_id, 'post_text' => stripslashes($_POST['post_content'])));
	bb_delete_post($post_id, status_wp2bb($_POST['comment_approved']));
	$result = add_table_item($_POST["post_id"], 0, $topic_id, $post_id, '', '', '');
	$data = serialize(array("topic_id" => $topic_id, "post_id" => $post_id, "result" => $result));
	echo $data;
}

function continue_bb_topic()
{
	$row = get_table_item('wp_post_id', $_POST["post_id"]);
	remove_all_filters('pre_post');
	$post_id = bb_insert_post(array("topic_id" => $row['bb_topic_id'], "post_text" => stripslashes($_POST["post_content"])));
	bb_delete_post($post_id, status_wp2bb($_POST['comment_approved']));
	// empty user info if not anonymous user
	$user = bb_get_current_user();
	if (bb_get_option('bbwp_anonymous_user_id') != $user->ID)
	{
		$_POST['comment_author'] = '';
		$_POST['comment_author_email'] = '';
		$_POST['comment_author_url'] = '';
	}
	$result = add_table_item($_POST['post_id'], $_POST['comment_id'], $row['bb_topic_id'], $post_id, $_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url']);
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
	// remove filters to save formatting
	remove_all_filters('pre_post');
	$user = bb_get_current_user();
	if (bb_get_option('bbwp_anonymous_user_id') != $user->ID)
	{
		$_POST['comment_author'] = '';
		$_POST['comment_author_email'] = '';
		$_POST['comment_author_url'] = '';
	}
	update_table_item('wp_comment_id', $_POST['comment_id'], $_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url']);
	bb_insert_post(array('post_text' => stripslashes($_POST['post_content']), 'post_id' => $row['bb_post_id'], 'topic_id' => $row['bb_topic_id']));
	bb_delete_post($row['bb_post_id'], status_wp2bb($_POST['comment_approved']));
}

function delete_bb_post()
{
	bb_remove_filter('bb_delete_post', 'afteredit');
	$row = get_table_item('wp_comment_id', $_POST['comment_id']);
	bb_delete_post('bb_post_id', $row['bb_post_id']);
	global $bbdb;
	// REAL post deletion from database
	// FIXME: may do something a little bit inconsistent
	$bbdb->query('DELETE FROM '.$bbdb->prefix.'posts WHERE post_id = '.$row['bb_post_id']);
	delete_table_item('wp_comment_id', $_POST['comment_id']);
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
	$bbwp_sync_db_version = 0.6;
	$table = $bbdb->prefix."bbwp_ids";
	$sql = "CREATE TABLE $table (
		`wp_comment_id` INT UNSIGNED NOT NULL,
		`wp_post_id` INT UNSIGNED NOT NULL,
		`wp_comment_author` tinytext character set utf8 NOT NULL default '',
		`wp_comment_author_email` varchar(100) NOT NULL default '',
		`wp_comment_author_url` varchar(200) NOT NULL default '',
		`bb_topic_id` INT UNSIGNED NOT NULL,
		`bb_post_id` INT UNSIGNED NOT NULL,
	);";
	if ($bbdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name || backpress_get_option('bbwp_sync_db_version') != $bbwp_sync_db_version) 
	{
		require_once(BB_PATH.'bb-admin/includes/functions.bb-upgrade.php');
		bb_sql_delta($sql);
		backpress_add_option('bbwp_sync_db_version', $bbwp_sync_db_version);
	}
}

function add_table_item($wp_post, $wp_comment, $bb_topic, $bb_post, $wp_anon_user, $wp_anon_email, $wp_anon_url)
{
	global $bbdb;
	return $bbdb->query($bbdb->prepare("INSERT INTO ".$bbdb->prefix."bbwp_ids (wp_post_id, wp_comment_id, bb_topic_id, bb_post_id, wp_comment_author, wp_comment_author_email, wp_comment_author_url)
		VALUES (%d, %d, %d, %d, %s, %s, %s)", $wp_post, $wp_comment, $bb_topic, $bb_post, $wp_anon_user, $wp_anon_email, $wp_anon_url));
}

function update_table_item($field, $value, $wp_anon_user, $wp_anon_email, $wp_anon_url)
{
	// for anonymous userinfo updating
	global $bbdb;
	$bbdb->query($bbdb->prepare('UPDATE '.$bbdb->prefix."bbwp_ids SET wp_comment_author = %s, wp_comment_author_email = %s, wp_comment_author_url = %s
		WHERE $field = $value", $wp_anon_user, $wp_anon_email, $wp_anon_url));
}

function get_table_item($field, $value)
{
	global $bbdb;
	return $bbdb->get_row('SELECT * FROM '.$bbdb->prefix."bbwp_ids WHERE $field = $value LIMIT 1", ARRAY_A);
}

function delete_table_item($field, $value)
{
	global $bbdb;
	$bbdb->query("DELETE FROM ".$bbdb->prefix."bbwp_ids WHERE $field = $value");
}

function afteredit($id)
{
	if (!bbwp_do_sync())
		return;
	$post = bb_get_post($id);
	$row = get_table_item('bb_post_id', $post->post_id);
	if ($row)
	{
		// have it in database, must sync
		edit_wp_comment($post, $row['wp_comment_id']);
	} else
	{
		if (sync_that_status($id))
		{
			$row = get_table_item('bb_topic_id', $post->topic_id);
			if ($row)
				add_wp_comment($post, $row['wp_post_id']);
		}
	}
}

function afterpost($id)
{
	if (!bbwp_do_sync())
		return;
	$post = bb_get_post($id);
	$row = get_table_item('bb_topic_id', $post->topic_id);
	if ($row)
	{
		// need to duplicate in WordPress
		if (sync_that_status($id))
			add_wp_comment($post, $row['wp_post_id']);
	}
}

function add_wp_comment($post, $wp_post_id)
{
	$request = array(
		'action' => 'add_comment',
		'post_text' => apply_filters('post_text', $post->post_text),
		'post_id' => $post->post_id,
		'post_status' => $post->post_status,
		'topic_id' => $post->topic_id,
		'user' => $post->poster_id,
		'wp_post_id' => $wp_post_id
	);
	$answer = send_command($request);
	$data = unserialize($answer);
	add_table_item($wp_post_id, $data['comment_id'], $post->topic_id, $post->post_id, '', '', '');
}

function edit_wp_comment($post, $comment_id)
{
	// FIXME: get post text original way
	global $bbdb;
	remove_filter('post_text', 'bbwp_anonymous_userinfo');
	$request = array(
		'action' => 'edit_comment',
		'post_text' => apply_filters('post_text', $bbdb->get_var("SELECT post_text FROM ".$bbdb->prefix."posts WHERE post_id = ".$post->post_id)),
		'post_status' => get_real_post_status($post->post_id),
		'user' => $post->poster_id,
		'comment_id' => $comment_id,
	);
	send_command($request);
}

function afterclosing($id)
{
	if (!bbwp_do_sync())
		return;
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
	if (!bbwp_do_sync())
		return;
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
		<label for="sync_all_posts">
			<?php _e('Sync all posts:') ?>
		</label>
		<div>
			<input type="checkbox" name="sync_all_posts"<?php echo (bb_get_option('bbwp_sync_all_posts') == 'enabled') ? ' checked="checked"' : '';?> />
			<?php _e('Sync post even if not approved. Post will have the same status in WordPress', $textdomain); ?>
		</div>
		</div>
		<div>
		<label for="show_anonymous_info">
			<?php _e('Show anonymous userinfo:') ?>
		</label>
		<div>
			<input type="checkbox" name="show_anonymous_info"<?php echo (bb_get_option('bbwp_show_anonymous_info') == 'enabled') ? ' checked="checked"' : '';?> />&nbsp;Show name <input type="checkbox" name="show_anonymous_email"<?php echo (bb_get_option('bbwp_show_anonymous_email') == 'enabled') ? ' checked="checked"' : '';?> />&nbsp;Show email <input type="checkbox" name="show_anonymous_url"<?php echo (bb_get_option('bbwp_show_anonymous_url') == 'enabled') ? ' checked="checked"' : '';?> />&nbsp;Show url
		</div>
		</div>
		<div>
		<label for="enable_plugin">
			<?php _e('Enable plugin'); ?>
		</label>
		<div>
		<?php $check = check_bbwp_settings(); if ($check['code'] != 0) set_global_plugin_status('disabled'); ?>
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
		$_POST['sync_all_posts'] == 'on' ? bb_update_option('bbwp_sync_all_posts', 'enabled') : bb_update_option('bbwp_sync_all_posts', 'disabled');
		$_POST['show_anonymous_info'] == 'on' ? bb_update_option('bbwp_show_anonymous_info', 'enabled') : bb_update_option('bbwp_show_anonymous_info', 'disabled');
		$_POST['show_anonymous_email'] == 'on' ? bb_update_option('bbwp_show_anonymous_email', 'enabled') : bb_update_option('bbwp_show_anonymous_email', 'disabled');
		$_POST['show_anonymous_url'] == 'on' ? bb_update_option('bbwp_show_anonymous_url', 'enabled') : bb_update_option('bbwp_show_anonymous_url', 'disabled');
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
		return 1; // cannot establish connection to wp
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
	$active_plugins = $bbdb->get_var('SELECT meta_value FROM '.$bbdb->prefix.'meta WHERE object_type = "bb_option" AND meta_key = "active_plugins"');
	if (strpos($active_plugins, 'wordpress-bbpress-syncronization/bbwp-sync.php') === false)
		return 5; // bbpress part not activated
	if (!correct_wpbb_version())
		return 6;
	return 0; // everything is ok
}

function bb_status_error($code)
{
	if ($code == 0)
		return __('Everything is ok!');
	if ($code == 1)
		return __('Cannot establish connection to WordPress part');
	elseif ($code == 2)
		return __('Invalid secret key');
	elseif ($code == 3)
		return __('Invalid forum id');
	elseif ($code == 4)
		return __('Invalid anonymous user id');
	elseif ($code == 5)
		return __('bbPress part not activated');
	elseif ($code == 6)
		return __('Too old WordPress part plugin version');
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
	if (!bbwp_do_sync())
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
	if (!bbwp_do_sync())
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

function bbwp_anonymous_userinfo($text)
{
	if (bb_get_option('bbwp_anonymous_user_id') == get_post_author_id())
	{
		if (bb_get_option('bbwp_show_anonymous_info') != 'enabled')
			return $text;
		// write extra information about anonymous user
		$row = get_table_item('bb_post_id', get_post_id());
		$text .= '<div class="wpbb_anonymous_userinfo">'.__('User information').'<ul>'
			.'<li><span class="wpbb_anonymous_userinfo_key">Author</span>: <span>'.$row['wp_comment_author'].'</span></li>';
		if (bb_get_option('bbwp_show_anonymous_email') == 'enabled')
			$text .= '<li><span class="wpbb_anonymous_userinfo_key">E-mail</span>: <span>'.$row['wp_comment_author_email'].'</span></li>';
		if (bb_get_option('bbwp_show_anonymous_url') == 'enabled' && $row['wp_comment_author_url'] != '')
			$text .= '<li><span class="wpbb_anonymous_userinfo_key">URL</span>: <span>'.$row['wp_comment_author_url'].'</span></li>';
		$text .= '</ul></div>';
	}
	return $text;
}


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
add_filter('post_text', 'bbwp_anonymous_userinfo');

?>
