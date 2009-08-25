<?php
/*
Plugin Name: bbPress-WordPress syncronization
Plugin URI: http://bobrik.name/code/wordpress/wordpress-bbpress-syncronization/
Description: Sync your WordPress comments to bbPress forum and back.
Author: Ivan Babrou <ibobrik@gmail.com>
Version: 0.7.8
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
$bbwp_version = 78;
$min_version = 78;

require_once(dirname(__FILE__).'/../../bb-load.php');

// for mode checking
$bbwp_plugin = 0;

function bbwp_add_textdomain()
{
	// setting textdomain for translation
	load_plugin_textdomain('bbwp-sync', dirname(__FILE__).'/');
}

if (substr($_SERVER['PHP_SELF'], -13) != 'bbwp-sync.php')
{
	global $bbwp_plugin;
	$bbwp_plugin = 1;
} else
{
	// listening commands from WordPress part
	bbwp_listener();
}


// action => answer
// for actions that only return data and don't change their arguments
$bbwp_cachable_requests = array(
	'test' => '',
	'keytest' => '',
	'get_wpbb_version' => '',
	'get_categories' => '',
	'check_wp_settings' => ''
);

function bbwp_send_command($pairs = array())
{
	global $bbwp_cachable_requests;
	if (isset($bbwp_cachable_requests[$pairs['action']]) && !empty($bbwp_cachable_requests[$pairs['action']]))
		return $bbwp_cachable_requests[$pairs['action']];
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
	$answer = '';
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
		$answer = $response[1];
	}
	// f*cking windows dirty hacks. hate you, dumb idiots from micro$oft!
	$answer = trim(trim($answer, "\xEF\xBB\xBF"));
	$bbwp_cachable_requests[$pairs['action']] = $answer;
	return $answer;
}

function bbwp_test_pair()
{
	$answer = bbwp_send_command(array('action' => 'test'));
	// return 1 if test passed, 0 otherwise
	// TODO: check configuration!
	$data = unserialize($answer);
	return $data['test'] == 1 ? 1 : 0;
}

function bbwp_secret_key_equal()
{
	$answer = bbwp_send_command(array('action' => 'keytest', 'secret_key' => md5(bb_get_option('bbwp_secret_key'))));
	$data = unserialize($answer);
	return $data['keytest'];
}

function bbwp_compare_keys_local()
{
	return $_POST['secret_key'] == md5(bb_get_option('bbwp_secret_key')) ? 1 : 0;
}

function correct_wpbb_version()
{
	$answer = unserialize(bbwp_send_command(array('action' => 'get_wpbb_version')));
	global $min_version;
	return ($answer['version'] < $min_version) ? 0 : 1;
}

function bbwp_listener()
{
	if (empty($_POST['action']))
		echo 'If you see that, plugin must connect well. WordPress test response (must be a:1:{s:4:"test";i:1;}): '.bbwp_send_command(array('action' => 'test'));;
	// setting authorized user
	if ($_POST['user'] != 0)
	{
		bb_set_current_user($_POST['user']);
	} else
	{
		bb_set_current_user(bb_get_option('bbwp_anonymous_user_id'));
	}
	// error_log("GOT COMMAND for bbPress part: ".$_POST['action']);
	if ($_POST['action'] == 'test')
	{
		echo serialize(array('test' => 1));
		return;
	} elseif ($_POST['action'] == 'keytest')
	{
		echo serialize(array('keytest' => bbwp_compare_keys_local()));
		return;
	}
	// here we need secret key, only if not checking settings
	if (!bbwp_secret_key_equal() && $_POST['action'] != 'check_bb_settings')
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

function bbwp_sync_that_status($id)
{
	if (bbwp_get_real_post_status($id) == 0 || bb_get_option('bbwp_sync_all_posts') == 'enabled')
		return true;
	else
		return false;
}

function bbwp_get_real_post_status($id)
{
	global $bbdb;
	return $bbdb->get_var('SELECT post_status FROM '.$bbdb->prefix.'posts WHERE post_id = '.$id);
}

function create_bb_topic()
{
	$forum_id = bb_get_option('bbwp_forum_id');
	$post_categories = unserialize(stripslashes($_POST['categories']));
	$accordance = unserialize(bb_get_option('bbwp_forum_categories'));
	if (is_array($accordance))
		foreach ($accordance as $category => $forum)
			if (in_array($category, $post_categories))
				$forum_id = $forum;
	$tags = stripslashes($_POST['tags']);
	if (trim(bb_get_option('bbwp_crosspost_tag')) != '')
		$tags .= ', '.bb_get_option('bbwp_crosspost_tag');
	$topic_id = bb_insert_topic(array('topic_title' => stripslashes($_POST['topic']), 'forum_id' => $forum_id, 'tags' => $tags));
	remove_all_filters('pre_post');
	$post_id = bb_insert_post(array('topic_id' => $topic_id, 'post_text' => stripslashes($_POST['post_content'])));
	bb_delete_post($post_id, status_wp2bb($_POST['comment_approved']));
	$result = bbwp_add_table_item($_POST["post_id"], 0, $topic_id, $post_id, '', '', '');
	bb_update_meta($topic_id, 'bbwp_sync', '1', 'topic');
	$data = serialize(array("topic_id" => $topic_id, "post_id" => $post_id, "result" => $result));
	echo $data;
}

function continue_bb_topic()
{
	$row = bbwp_get_table_item('wp_post_id', $_POST["post_id"]);
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
	$result = bbwp_add_table_item($_POST['post_id'], $_POST['comment_id'], $row['bb_topic_id'], $post_id, $_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url']);
	$data = serialize(array("topic_id" => $row['bb_topic_id'], "post_id" => $post_id, "result" => $result));
	echo $data;
}

function edit_bb_post()
{
	if (isset($_POST['get_row_by']) && $_POST['get_row_by'] == 'wp_post')
		$row = bbwp_get_table_item('wp_post_id', $_POST["post_id"]);
	else
		$row = bbwp_get_table_item('wp_comment_id', $_POST["comment_id"]);
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
	bbwp_update_table_item('wp_comment_id', $_POST['comment_id'], $_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url']);
	bb_insert_post(array('post_text' => stripslashes($_POST['post_content']), 'post_id' => $row['bb_post_id'], 'topic_id' => $row['bb_topic_id']));
	bb_delete_post($row['bb_post_id'], status_wp2bb($_POST['comment_approved']));
}

function delete_bb_post()
{
	bb_remove_filter('bb_delete_post', 'bbwp_afteredit');
	$row = bbwp_get_table_item('wp_comment_id', $_POST['comment_id']);
	bb_delete_post('bb_post_id', $row['bb_post_id']);
	global $bbdb;
	// REAL post deletion from database
	// FIXME: may do something a little bit inconsistent
	$bbdb->query('DELETE FROM '.$bbdb->prefix.'posts WHERE post_id = '.$row['bb_post_id']);
	bbwp_delete_table_item('wp_comment_id', $_POST['comment_id']);
}

function open_bb_topic()
{
	bb_open_topic($_POST['topic_id']);
}

function close_bb_topic()
{
	bb_close_topic($_POST['topic_id']);
}

function bbwp_add_table_item($wp_post, $wp_comment, $bb_topic, $bb_post, $wp_anon_user, $wp_anon_email, $wp_anon_url)
{
	global $bbdb;
	return $bbdb->query($bbdb->prepare("INSERT INTO ".$bbdb->prefix."bbwp_ids (wp_post_id, wp_comment_id, bb_topic_id, bb_post_id, wp_comment_author, wp_comment_author_email, wp_comment_author_url)
		VALUES (%d, %d, %d, %d, %s, %s, %s)", $wp_post, $wp_comment, $bb_topic, $bb_post, $wp_anon_user, $wp_anon_email, $wp_anon_url));
}

function bbwp_update_table_item($field, $value, $wp_anon_user, $wp_anon_email, $wp_anon_url)
{
	// for anonymous userinfo updating
	global $bbdb;
	$bbdb->query($bbdb->prepare('UPDATE '.$bbdb->prefix."bbwp_ids SET wp_comment_author = %s, wp_comment_author_email = %s, wp_comment_author_url = %s
		WHERE $field = $value", $wp_anon_user, $wp_anon_email, $wp_anon_url));
}

function bbwp_get_table_item($field, $value)
{
	global $bbdb;
	return $bbdb->get_row('SELECT * FROM '.$bbdb->prefix."bbwp_ids WHERE $field = $value LIMIT 1", ARRAY_A);
}

function bbwp_delete_table_item($field, $value)
{
	global $bbdb;
	$bbdb->query("DELETE FROM ".$bbdb->prefix."bbwp_ids WHERE $field = $value");
}

function bbwp_afteredit($id)
{
	if (!bbwp_do_sync())
		return;
	$post = bb_get_post($id);
	$row = bbwp_get_table_item('bb_post_id', $post->post_id);
	if ($row)
	{
		// have it in database, must sync
		edit_wp_comment($post, $row['wp_comment_id']);
	} else
	{
		if (bbwp_sync_that_status($id))
		{
			if (bb_get_option('bbwp_sync_posts_back') == 'disabled')
				return; // syncing back disabled
			$row = bbwp_get_table_item('bb_topic_id', $post->topic_id);
			if ($row)
				add_wp_comment($post, $row['wp_post_id']);
		}
	}
}

function bbwp_afterpost($id)
{
	if (!bbwp_do_sync())
		return;
	$post = bb_get_post($id);
	$row = bbwp_get_table_item('bb_topic_id', $post->topic_id);
	if ($row)
	{
		if (bb_get_option('bbwp_sync_posts_back') == 'disabled')
			return; // syncing back disabled
		// need to duplicate in WordPress
		if (bbwp_sync_that_status($id))
			add_wp_comment($post, $row['wp_post_id']);
	}
}

function add_wp_comment($post, $wp_post_id)
{
	$request = array(
		'action' => 'add_comment',
		'post_text' => bbwp_correct_links(apply_filters('post_text', $post->post_text)),
		'post_id' => $post->post_id,
		'post_status' => $post->post_status,
		'topic_id' => $post->topic_id,
		'user' => $post->poster_id,
		'wp_post_id' => $wp_post_id
	);
	$answer = bbwp_send_command($request);
	$data = unserialize($answer);
	bbwp_add_table_item($wp_post_id, $data['comment_id'], $post->topic_id, $post->post_id, '', '', '');
}

function edit_wp_comment($post, $comment_id)
{
	// FIXME: get post text original way
	global $bbdb;
	remove_filter('post_text', 'bbwp_anonymous_userinfo');
	$request = array(
		'action' => 'edit_comment',
		'post_text' => bbwp_correct_links(apply_filters('post_text', $bbdb->get_var("SELECT post_text FROM ".$bbdb->prefix."posts WHERE post_id = ".$post->post_id))),
		'post_status' => bbwp_get_real_post_status($post->post_id),
		'user' => $post->poster_id,
		'comment_id' => $comment_id,
	);
	bbwp_send_command($request);
}

function bbwp_afterclosing($id)
{
	if (!bbwp_do_sync())
		return;
	global $bbdb;
	$wp_post_id = $bbdb->get_var("SELECT wp_post_id FROM ".$bbdb->prefix."bbwp_ids WHERE bb_topic_id = ".$id);
	$request = array(
		'action' => 'close_wp_comments',
		'post_id' => $wp_post_id,
	);
	bbwp_send_command($request);
}

function bbwp_afteropening($id)
{
	if (!bbwp_do_sync())
		return;
	global $bbdb;
	$wp_post_id = $bbdb->get_var("SELECT wp_post_id FROM ".$bbdb->prefix."bbwp_ids WHERE bb_topic_id = ".$id);
	$request = array(
		'action' => 'open_wp_comments',
		'post_id' => $wp_post_id,
	);
	bbwp_send_command($request);
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

function bbwp_options_page() {
	bb_admin_add_submenu(__('WordPress syncronization', 'bbwp-sync'), 'moderate', 'bbwp_options', 'options-general.php');
}

function bbwp_options()
{
?>
<h2><?php _e('WordPress syncronization options', 'bbwp-sync'); ?></h2>

<div style="vert-align:center;margin:4px">Please donate if you like this plugin: <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ibobrik%40gmail%2ecom&lc=US&item_name=WordPress%2dbbPress%20syncronization%20plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate!" /></a></div>

<form class="settings" method="post" action="">
	<fieldset>
		<div>
			<label for="wordpress_url">
				<?php _e('WordPress url:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input class="text" id="wordpress_url" name="wordpress_url" value="<?php echo bb_get_option('bbwp_wordpress_url'); ?>" />
				<p><?php
				$err = check_bb_settings(); // only one error at once, let's show other if only previvous was fixed
				if (!bb_get_option('bbwp_wordpress_url'))
				{
					_e('Please submit with last "/"! We will test that after submussion', 'bbwp-sync');
				} else
				{
					if (bbwp_test_pair())
					{
						_e('Everything is ok!', 'bbwp-sync');
					} else
					{
						echo '<b>'.__('URL is incorrect or connection error, please verify it (full variant): ', $textdomain).
							'<a href="'.bb_get_option('bbwp_wordpress_url').'?wpbb-listener">'.
							bb_get_option('bbwp_wordpress_url')."?wpbb-listener</a></b>";
					}
				}
				?></p>
			</div>
		</div>
		<div>
			<label for="secret_key">
				<?php _e('Secret key:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input class="text" id="secret_key" name="secret_key" value="<?php echo bb_get_option('bbwp_secret_key'); ?>" />
				<p><?php
				if ((!bb_get_option('bbwp_secret_key') && bbwp_secret_key_equal()) || ($err != 0 && $err != 2))
				{
					_e('We need it for secure communication between your systems', 'bbwp-sync');
				} else
				{
					if (bbwp_secret_key_equal())
					{
						_e('Everything is ok!', 'bbwp-sync');
					} else
					{
						echo '<b>'.__("Error! Not equal secret keys in WordPress and bbPress", 'bbwp-sync').'</b>';
					}
				}
				?></p>
			</div>
		</div>
		<div>
			<label for="forum_id">
				<?php _e('Forum:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<select name="forum_id" id="forum_id">
					<?php
						$forums = get_forums();
						$forum_id = bb_get_option('bbwp_forum_id');
						echo '<option value="-1">'.__('Select forum', 'bbwp-sync').'</option>';
						foreach ($forums as $forum)
							echo '<option value="'.$forum->forum_id.'"'.
								($forum_id == $forum->forum_id ? ' selected="selected"':'').
								'>'.$forum->forum_name.'</option>';
					?>
				</select>
				<p><?php
					echo ($err == 3 ? '<b>' : '').
						__('You need to set the forum for syncronization.', 'bbwp-sync').
						($err == 3 ? '</b>' : '');
				?></p>
			</div>
		</div>
		<div>
			<label for="forum_categories">
				<?php _e('Category-Forum accordance:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<textarea name="forum_categories" id="forum_categories" rows="10" cols="40"><?php
						if (isset($_POST['submit']) && bbwp_check_categories('check', $_POST['forum_categories']) != 0)
						{
							// editing field
							echo implode("\n", bbwp_check_categories('fix', $_POST['forum_categories']));
						} else
						{
							// gettin' info from db
							if (isset($_POST['submit']))
							{
								$accordance = bbwp_get_cat_accordance($_POST['forum_categories']);
								bb_update_option('bbwp_forum_categories', serialize($accordance));
							} else {
								$accordance = unserialize(bb_get_option('bbwp_forum_categories'));
							}
							$wp_categories = unserialize(bbwp_send_command(array('action' => 'get_categories')));
							if (is_array($accordance))
								foreach ($accordance as $cat_id => $forum_id)
									echo $wp_categories[$cat_id].' => '.$forum_id."\n";
						}
				?></textarea>
				<p><?php
					echo ($err == 3 ? '<b>' : '').
						__('This is optional. Use nex syntax: «Category name => forum_id», one option per line.', 'bbwp-sync').
						($err == 3 ? '</b>' : '');
				?></p>
			</div>
		</div>
		<div>
			<label for="crosspost_tag">
				<?php _e('Crosspost tag:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input class="text" name="crosspost_tag" id="crosspost_tag" value="<?php echo bb_get_option('bbwp_crosspost_tag'); ?>" />
				<p><?php _e('Optional. Crossposted from WordPress topics will have that tag. Leave empty to disable', 'bbwp-sync'); ?></p>
			</div>
		</div>
		<div>
			<label for="anonymous_user">
				<?php _e('Anonymous user:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input class="text" id="anonymous_user" name="anonymous_user" value="<?php echo bb_get_option('bbwp_anonymous_user_id'); ?>" />
				<p><?php
					echo ($err == 4 ? '<b>' : '').
						__('User id for posts on forum from unregistered users in WordPress', 'bbwp-sync').
						($err == 4 ? '</b>' : '');
				?></p>
			</div>
		</div>
		<div>
			<label for="sync_all_posts">
				<?php _e('Sync all posts:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input type="checkbox" id="sync_all_posts" name="sync_all_posts"<?php echo (bb_get_option('bbwp_sync_all_posts') == 'enabled') ? ' checked="checked"' : '';?> />
				<p><?php _e('Sync post even if not approved. Post will have the same status in WordPress', 'bbwp-sync'); ?></p>
			</div>
		</div>
		<div>
			<label for="show_anonymous_info">
				<?php _e('Show anonymous userinfo:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" name="show_anonymous_info"<?php echo (bb_get_option('bbwp_show_anonymous_info') == 'enabled') ? ' checked="checked"' : '';?> /> <?php _e('Show name', 'bbwp-sync'); ?></label>
				<label class="checkboxs"><input type="checkbox" name="show_anonymous_email"<?php echo (bb_get_option('bbwp_show_anonymous_email') == 'enabled') ? ' checked="checked"' : '';?> /> <?php _e('Show email', 'bbwp-sync'); ?></label>
				<label class="checkboxs"><input type="checkbox" name="show_anonymous_url"<?php echo (bb_get_option('bbwp_show_anonymous_url') == 'enabled') ? ' checked="checked"' : '';?> /> <?php _e('Show url', 'bbwp-sync'); ?></label>
			</div>
		</div>
		<div>
			<label for="sync_posts_back">
				<?php _e('Sync posts back:', 'bbwp-sync') ?>
			</label>
			<div class="inputs">
				<input type="checkbox" id="sync_posts_back" name="sync_posts_back"<?php echo (bb_get_option('bbwp_sync_posts_back') == 'enabled') ? ' checked="checked"' : '';?> />
				<p><?php _e('You may disable syncing posts from forum back to WordPress. You need to have a good reason to do that', 'bbwp-sync'); ?></p>
			</div>
		</div>
		<div>
			<label for="plugin_status" style="font-weight:bold">
				<?php _e('Enable plugin', 'bbwp-sync'); ?>
			</label>
			<div class="inputs">
			<?php $check = check_bbwp_settings(); if ($check['code'] != 0) bbwp_set_global_plugin_status('disabled'); ?>
				<input type="checkbox" id="plugin_status" name="plugin_status"<?php echo (bb_get_option('bbwp_plugin_status') == 'enabled') ? ' checked="checked"' : ''; echo ($check['code'] == 0) ? '' : ' disabled="disabled"'; ?> />
				<p><?php echo ($check['code'] == 0) ? __('Allowed by both parts', 'bbwp-sync') : __('Not allowed: ', 'bbwp-sync').$check['message'] ?></p>
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<input type="hidden" name="action" value="update-bbwp-configuration" />
		<input class="submit" type="submit" name="submit" id="submit" value="<?php _e('Update Configuration &raquo;', 'bbwp-sync') ?>" />
	</fieldset>
</form>
<?php
}

function bbwp_process_options()
{
	if ($_POST['action'] == 'update-bbwp-configuration')
	{
		bb_update_option('bbwp_forum_id', (int) $_POST['forum_id']);
		bb_update_option('bbwp_wordpress_url', $_POST['wordpress_url']);
		bb_update_option('bbwp_crosspost_tag', $_POST['crosspost_tag']);
		bb_update_option('bbwp_secret_key', $_POST['secret_key']);
		bb_update_option('bbwp_anonymous_user_id', (int) $_POST['anonymous_user']);
		bbwp_set_global_plugin_status($_POST['plugin_status'] == 'on' ? 'enabled' : 'disabled');
		bb_update_option('bbwp_sync_all_posts', $_POST['sync_all_posts'] == 'on' ? 'enabled' : 'disabled');
		bb_update_option('bbwp_show_anonymous_info', $_POST['show_anonymous_info'] == 'on' ? 'enabled' : 'disabled');
		bb_update_option('bbwp_show_anonymous_email', $_POST['show_anonymous_email'] == 'on' ? 'enabled' : 'disabled');
		bb_update_option('bbwp_show_anonymous_url', $_POST['show_anonymous_url'] == 'on' ? 'enabled' : 'disabled');
		bb_update_option('bbwp_sync_posts_back', $_POST['sync_posts_back'] == 'on' ? 'enabled' : 'disabled');
		bb_admin_notice( __('Configuration saved.', 'bbwp-sync') );
	}
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
	if (bb_get_option('bbwp_sync_all_posts'))
	{
		bb_update_option('bbwp_sync_all_posts', 'disabled');
		bb_update_option('bbwp_show_anonymous_info', 'enabled');
		bb_update_option('bbwp_show_anonymous_email', 'disabled');
		bb_update_option('bbwp_show_anonymous_url', 'disabled');
	}
	if (!bb_get_option('bbwp_sync_posts_back'))
		bb_update_option('bbwp_sync_posts_back', 'enabled');
	// next options must be cheched by another conditions!
}

function deactivate_bbwp()
{
	// deactivate on disabling
	bbwp_set_global_plugin_status('disabled');
}

function check_bbwp_settings()
{
	$wp_settings = check_wp_settings();
	$bb_code = check_bb_settings();
	$bb_message = bb_status_error($bb_code);
	# it's better to check bbPress ability to connect first
	if ($wp_settings['code'] == 1)
	{
		$data['code'] = 1;
		$data['message'] = '[WordPress part] '.$wp_settings['message'];
	} elseif ($bb_code != 0)
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
		$data['message'] = __('Everything is ok!', 'bbwp-sync');
	}
	return $data;
}

function bbwp_set_global_plugin_status($status)
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
	if (!bbwp_test_pair())
	{
		return 1; // cannot establish connection to wp
	}
	if (!bbwp_secret_key_equal())
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
	// slashes must be identical. may be strange in 0.0000000000000000001% of situations
	if (strpos(str_replace('\\', '/', $active_plugins), str_replace(bb_plugin_basename(__FILE__), '\\', '/')) === false)
		return 5; // bbpress part not activated
	if (!correct_wpbb_version())
		return 6;
	return 0; // everything is ok
}

function bbwp_check_categories($target, $data)
{
	// checking text, not serialized data from db
	// target may be 'check' or 'fix'
	// if 'fix', returns corrected text
	global $bbdb;
	$wp_categories = unserialize(bbwp_send_command(array('action' => 'get_categories')));
	$new_lines = array();
	foreach (explode("\n", $data) as $acc)
	{
		$correct_line = true;
		if (trim($acc) == '')
			continue;
		$pos = strpos($acc, ' => ');
		if ($pos === false)
		{
			if ($target == 'check')
			{
				return 1;
			} else
			{
				$correct_line = false;
				$new_lines[] = '['.__('Invalid line', 'bbwp-sync').'] '.$acc;
			}
		}
		$cat = substr($acc, 0, $pos);
		if ($correct_line && (!is_array($wp_categories) || !in_array($cat, $wp_categories)))
		{
			if ($target == 'check')
			{
				return 2;
			} else
			{
				$correct_line = false;
				$new_lines[] = '['.__('Invalid category name', 'bbwp-sync').'] '.$acc;
			}
		}
		$forum_id = $bbdb->get_row('SELECT * FROM '.$bbdb->prefix.'forums WHERE forum_id = '.((int) substr($acc, $pos+4)), ARRAY_A);
		if ($correct_line && !$forum_id)
		{
			if ($target == 'check')
			{
				return 3;
			} else
			{
				$correct_line = false;
				$new_lines[] = '['.__('Invalid forum id', 'bbwp-sync').'] '.$acc;
			}
		}
		if ($correct_line)
			$new_lines[] = $acc;
	}
	if ($target == 'check')
		return 0;
	else
		return $new_lines;
}

function bbwp_get_cat_accordance($data)
{
	$wp_categories = unserialize(bbwp_send_command(array('action' => 'get_categories')));
	$accordance = array();
	foreach (explode("\n", $data) as $acc)
	{
		$pos = strpos($acc, ' => ');
		$cat = substr($acc, 0, $pos);
		foreach ($wp_categories as $id => $name)
			if ($name == $cat)
			{
				$accordance[$id] = (int) substr($acc, $pos+4);
				break;
			}
	}
	return $accordance;
}

function bb_status_error($code)
{
	if ($code == 0)
		return __('Everything is ok!', 'bbwp-sync');
	if ($code == 1)
		return __('Cannot establish connection to WordPress part', 'bbwp-sync');
	elseif ($code == 2)
		return __('Invalid secret key', 'bbwp-sync');
	elseif ($code == 3)
		return __('Invalid forum id', 'bbwp-sync');
	elseif ($code == 4)
		return __('Invalid anonymous user id', 'bbwp-sync');
	elseif ($code == 5)
		return __('bbPress part is not activated', 'bbwp-sync');
	elseif ($code == 6)
		return __('Too old WordPress part plugin version', 'bbwp-sync');
}

function set_wp_plugin_status($status)
{
	// when enabling in bbPress
	$request = array(
		'action' => 'set_wp_plugin_status',
		'status' => $status,
	);
	$answer = bbwp_send_command($request);
	$data = unserialize($answer);
	return $data;
}

function check_wp_settings()
{
	$answer = bbwp_send_command(array('action' => 'check_wp_settings'));
	$data = unserialize($answer);
	return $data;
}

function bbwp_aftertagedit($id)
{
	if (!bbwp_do_sync())
		return;
	global $topic;
	$row = bbwp_get_table_item('bb_topic_id', $topic->topic_id);
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
	bbwp_send_command($request);
}

function edit_bb_tags()
{
	bb_remove_topic_tags($_POST['topic']);
	$tags = stripslashes($_POST['tags']);
	if (trim(bb_get_option('bbwp_crosspost_tag')))
		$tags .= ', '.bb_get_option('bbwp_crosspost_tag');
	bb_add_topic_tags($_POST['topic'], $tags);
}

function bbwp_pretagremove($id)
{
	if (!bbwp_do_sync())
		return;
	global $topic;
	$row = bbwp_get_table_item('bb_topic_id', $topic->topic_id);
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

function bbwp_anonymous_userinfo($post)
{
	$text = '';
	if (bb_get_option('bbwp_anonymous_user_id') == get_post_author_id())
	{
		if (bb_get_option('bbwp_show_anonymous_info') != 'enabled')
			return $text;
		// write extra information about anonymous user
		$row = bbwp_get_table_item('bb_post_id', get_post_id());
		$text .= '<div class="wpbb_anonymous_userinfo">'.__('Posted by unregistered user: ', 'bbwp-sync').'<span class="wpbb_anonymous_userinfo_name">'.$row['wp_comment_author'].'</span>';
		if (bb_get_option('bbwp_show_anonymous_email') == 'enabled')
		{
			$text .= ' (<span class="wpbb_anonymous_userinfo_mail"><a href="mailto:'.$row['wp_comment_author_email'].'">'.__('E-mail', 'bbwp-sync').'</a><span>';
			if (bb_get_option('bbwp_show_anonymous_url') != 'enabled' || $row['wp_comment_author_url'] == '')
				$text .= ')';
		}
		if (bb_get_option('bbwp_show_anonymous_url') == 'enabled' && $row['wp_comment_author_url'] != '')
		{
			if (bb_get_option('bbwp_show_anonymous_email') != 'enabled')
				$text .= ' (';
			else
				$text .= ', ';
			$text .= '<span class="wpbb_anonymous_userinfo_url"><a href="'.$row['wp_comment_author_url'].'" rel="nofollow">'.__('URL', 'bbwp-sync').'</a><span>)';
		}
		$text .= '</div>';
	}
	return $text.$post;
}

function bbwp_topic_last_poster($poster)
{
	global $topic;
	if (bb_get_option('bbwp_anonymous_user_id') == $topic->topic_last_poster && bb_get_option('bbwp_show_anonymous_info') == 'enabled')
	{
		$row = bbwp_get_table_item('bb_post_id', $topic->topic_last_post_id);
		if ($row['wp_comment_author'])
			return $row['wp_comment_author'];
	}
	return $poster;
}

function bbwp_topic_anonymous_user($poster)
{
	if (bb_get_option('bbwp_anonymous_user_id') == get_post_author_id())
	{
		$row = bbwp_get_table_item('bb_post_id', get_post_id());
		if ($row['wp_comment_author'])
			return $row['wp_comment_author'];
	}
	return $poster;
}

function bbwp_correct_links($text)
{
	$siteurl = preg_replace('|(://[^/]+/)(.*)|', '${1}', bb_get_option('uri'));
	if (substr($siteurl, -1) != '/')
		$siteurl .= '/';
	$current_url = substr($siteurl, 0, -1).preg_replace('|(.*/)[^/]*|', '${1}', $_SERVER['REQUEST_URI']);
	if (substr($current_url, -1) != '/')
		$current_url .= '/';
	// ':' is for protocol handling, must be replaced by '(://)', but doesn't work :-(
	// for absolute links with starting '/'
	$text = preg_replace('/(href|src)=(["\'])\/([^"\':]+)\2/', '${1}=${2}'.$siteurl.'${3}${2}', $text);
	// for links not starting with '/'
	return preg_replace('/(href|src)=(["\'])([^"\':]+)\2/', '${1}=${2}'.$current_url.'${3}${2}', $text);
}

function bbwp_post_exists()
{
	if (get_option('bbwp_plugin_status') != 'enabled')
		return false; // plugin disabled
	global $topic;
	$row = bbwp_get_table_item('bb_topic_id', $topic->topic_id);
	if ($row)
		return true;
	else
		return false;
}

function bbwp_post_url()
{
	global $topic;
	$row = bbwp_get_table_item('bb_topic_id', $post->ID);
	$answer = unserialize(bbwp_send_command(array('action' => 'get_post_link', 'post_id' => $row['wp_post_id'])));
	return $answer['link'];
}

function bbwp_warning()
{
	if (bb_get_option('bbwp_plugin_status') != 'enabled')
		echo '<div class="updated" id="message"><p><strong>'.__('Synchronization with WordPress is not enabled.').'</strong> '.sprintf(__('You must <a href="%1$s">check options and enable plugin</a> to make it work.'), 'admin-base.php?plugin=bbwp_options').'</p></div>';
	$request = array(
		'action' => 'get_categories'
	);
	$answer = bbwp_send_command($request);
}


add_action('bb_init', 'bbwp_add_textdomain');
bb_register_plugin_activation_hook(__FILE__, 'bbwp_install');
bb_register_plugin_deactivation_hook(__FILE__, 'deactivate_bbwp');
add_action('bb_tag_added', 'bbwp_aftertagedit');
add_action('bb_pre_tag_removed', 'bbwp_pretagremove');
add_action('bb_update_post', 'bbwp_afteredit');
add_action('bb_new_post', 'bbwp_afterpost');
add_action('bb_delete_post', 'bbwp_afteredit');
add_action('open_topic', 'bbwp_afteropening');
add_action('close_topic', 'bbwp_afterclosing');
add_action('bb_admin_menu_generator', 'bbwp_options_page');
add_action('bb_admin-header.php', 'bbwp_process_options');
add_filter('post_text', 'bbwp_anonymous_userinfo');
add_filter('get_topic_last_poster', 'bbwp_topic_last_poster');
add_filter('get_post_author','bbwp_topic_anonymous_user');
add_action('bb_admin_notices', 'bbwp_warning');

?>
