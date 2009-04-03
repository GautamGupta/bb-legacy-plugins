<?php
/*
Plugin Name: BBpress Latest Discussions
Plugin URI: http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/
Description: Put bbpress Latest Discussions on your wp page.
Author: Atsutane Shirane
Version: 0.9.1
Author URI: http://www.atsutane.net/
*/

### BBpress Latest Discussions Version Number
$BbLD_version = '0.9.1';

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

### Function: Install BbLD Configuration
$install = (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' && isset($_GET['activate']));;
if ($install) {
	bbld_install();
}

function bbld_install() {
	if (get_option('wpbb_status') == FALSE) {
		$bbpath = '/bbpress'; // Adjust the path to suit your bbpress location. Example: '/forums'
		$wpbburl = get_settings('home') . $bbpath;
		update_option('wpbb_path', $wpbburl);
		$forum_slimit = '10'; // Adjust the limit to show
		update_option('wpbb_limit', $forum_slimit);
		$bbdb_prefix = 'bb_'; // Set Bbpress Prefix
		update_option('wpbb_bbprefix', $bbdb_prefix);
		$limit = '100';
		update_option('wpbb_slimit', $limit);
		update_option('wpbb_permalink', false);
		update_option('wpbb_intergrated', false);
		update_option('wpbb_exdb', false);
		update_option('wpbb_dbuser', DB_USER);
		update_option('wpbb_dbpass', DB_PASSWORD);
		update_option('wpbb_dbname', DB_NAME);
		update_option('wpbb_dbhost', DB_HOST);
		$install_status = 'install';
		update_option('wpbb_status', $install_status);
		update_option('wpbb_lastposter', true);
		update_option('wpbb_inside', true);
	}
}

### Function: Add Option Page
add_action('admin_menu', 'wpbb_add_pages');
function wpbb_add_pages() {
	add_options_page(__("BBpress Latest Discussions Option"), __('BbLD Option'), 8, __FILE__, 'wp_bb_option');
}

### Function: Trim some text
function wpbb_trim($paragraph, $limit) {
	$original = strlen($paragraph);
	if ($original > $limit) {
		$text = substr($paragraph, 0, $limit) . " [...]";
	}
	else {
		$text = $paragraph;
	}
	return $text;
}

### Function: Permalink Data
function wpbb_permalink($type,$topicid) {
	global $wpdb,$BbLD_version;
	if (get_option('wpbb_permalink')) {
		$perma_type = $wpdb->get_row("SELECT * FROM `bb_topicmeta` WHERE `meta_key` LIKE 'mod_rewrite' LIMIT 1");
		$metakey = $perma_type->meta_value;
		if ($metakey == 1) {
			$permalink = get_option('wpbb_path') . '/'. $type . '/' . $topicid;
		}
		else {
			if ($type == 'topic') {
				$get_title = $wpdb->get_row("SELECT * FROM `bb_topics` WHERE `topic_id` LIKE '$topicid' LIMIT 1");
				$permalink = get_option('wpbb_path') . '/topic/' . $get_title->topic_slug;
			}
			else {
				$get_title = $wpdb->get_row("SELECT * FROM `bb_forums` WHERE `forum_id` LIKE '$topicid' LIMIT 1");
				$permalink = get_option('wpbb_path') . '/forum/' . $get_title->forum_slug;
			}			
		}
	}
	else {
		$permalink = get_option('wpbb_path') . '/'. $type . '.php?id=' . $topicid;
	}
	return $permalink;
}

### Function: BBpress Latest Discussions Option
function wp_bb_option() {
	global $wpdb,$BbLD_version;
	$ori_url = $_SERVER['REQUEST_URI'];
	if ($_POST['wpbb_save']){
		$test = $_POST['bburl'];
		update_option('wpbb_path', $test);
		update_option('wpbb_slimit', $_POST['bbslimit']);
		update_option('wpbb_limit', $_POST['bblimit']);
		update_option('wpbb_permalink', $_POST['wpbb_permalink']);
		update_option('wpbb_intergrated', $_POST['wpbb_intergrated']);
		update_option('wpbb_bbprefix', $_POST['bbprefix']);
		update_option('wpbb_exdb', $_POST['use_outdb']);
		update_option('wpbb_dbuser', $_POST['bbuser']);
		update_option('wpbb_dbpass', $_POST['bbpass']);
		update_option('wpbb_dbname', $_POST['bbname']);
		update_option('wpbb_dbhost', $_POST['bbhost']);
		update_option('wpbb_lastposter', $_POST['wpbb_lastposter']);
		update_option('wpbb_inside', $_POST['wpbb_inside']);
		update_option('wpbb_exclude', $_POST['wpbb_exclude']);
		$update_msg = "<div id='message' class='updated fade'><p>BBpress Latest Discussions options saved successfully.</p></div>";
	}
?>
<div class="wrap">
	<h2><?php _e('BBpress Latest Discussions'); ?></h2>
	<?php if ($update_msg) { _e("$update_msg"); } ?>
	<p><strong><?php _e('Plugin Name:'); ?></strong> <?php _e('BBpress Latest Discussions'); ?><br />
	<strong><?php _e('Plugin URI:'); ?></strong> <a href="http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/">http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/</a><br />
	<strong><?php _e('Author:'); ?></strong> <a href="http://www.atsutane.net/">Atsutane Shirane</a><br />
	<strong><?php _e('Version:'); ?></strong> <?php echo $BbLD_version; ?></p>
	<p><strong><?php _e('ToDo List:'); ?></strong></p>
	<ul>
		<li>Clean up the code</li>
	</ul>
	<p><?php _e('If you have any suggestion or feedback. Feel free to post it'); ?> <a href="http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/"><?php _e('here'); ?></a>.</p>
	<h2><?php _e('BBpress Option'); ?></h2>
	<form method="post" action="<?php echo $ori_url; ?>">
		<p class="submit">
			<input type="submit" name="wpbb_save" id="wpbb_save" value="<?php _e('Update Option &raquo;'); ?>">
		</p>
		<table class="optiontable"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress URL:'); ?></th> 
				<td><input name="bburl" type="text" id="bburl" value="<?php echo get_option('wpbb_path'); ?>" size="40" /></td> 
			</tr> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Post Limit:'); ?></th> 
				<td><input name="bblimit" type="text" id="bblimit" value="<?php echo get_option('wpbb_limit'); ?>" size="3" /> <?php _e('posts'); ?></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Text Limit:'); ?></th> 
				<td><input name="bbslimit" type="text" id="bbslimit" value="<?php echo get_option('wpbb_slimit'); ?>" size="3" /> <?php _e('chars'); ?><br /><?php _e("Set the length of text you want to show"); ?></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Exclude Forums:'); ?></th>
				<td><?php _e('Use this option if you want to exclude forum from being display on wordpress.'); ?></td>
			</tr>
			<?php
				$request = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."forums ORDER BY forum_order ASC");
				$exclude_chk = get_option('wpbb_exclude');
				foreach ($request as $request) {
					if (isset($exclude_chk[$request->forum_id])) {
						$allowed_forum = $request->forum_id;
						$exclude_option = "checked=\"checked\"";
					}
					echo "
						<tr valign=\"top\">
							<th scope=\"row\"></th>
							<td><label for=\"wpbb_exclude[$request->forum_id]\"><input name=\"wpbb_exclude[$request->forum_id]\" type=\"checkbox\" id=\"wpbb_exclude[$request->forum_id]\" value=\"wpbb_exclude[$request->forum_id]\"
					";
					if ($allowed_forum == $request->forum_id) { echo "$exclude_option"; }
					echo "
							/> $request->forum_name</label></td>
						</tr>
					";
				}
			?>
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress DB Prefix:'); ?></th> 
				<td><input name="bbprefix" type="text" id="bbprefix" value="<?php echo get_option('wpbb_bbprefix'); ?>" size="3" /> <?php _e('Bbpress table prefix'); ?><br /><?php _e("Enter the table prefix for your bbPress installation above. The table prefix is found in your bbPress installation's config.php file."); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Bbpress Permalink:'); ?></th>
				<td><label for="wpbb_permalink"><input name="wpbb_permalink" type="checkbox" id="wpbb_permalink" value="wpbb_permalink" <?php if (get_option('wpbb_permalink')) { echo('checked="checked"'); } ?> /> <?php _e('Use Bbpress Permalink'); ?></label><br /><?php _e('Only use this if you already enable permalink inside Bbpress config.php'); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Bbpress Last Poster:'); ?></th>
				<td><label for="wpbb_lastposter"><input name="wpbb_lastposter" type="checkbox" id="wpbb_lastposter" value="wpbb_lastposter" <?php if (get_option('wpbb_lastposter')) { echo('checked="checked"'); } ?> /> <?php _e('Display last poster'); ?></label><br /><?php _e('Check this option if you want to display the username of the last poster'); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Bbpress Inside:'); ?></th>
				<td><label for="wpbb_inside"><input name="wpbb_inside" type="checkbox" id="wpbb_inside" value="wpbb_inside" <?php if (get_option('wpbb_inside')) { echo('checked="checked"'); } ?> /> <?php _e('Display "Inside: Forum Name"'); ?></label><br /><?php _e('Check this option if you want the "inside" link to appear.'); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Wordpress/Bbpress Integration:'); ?></th>
				<td><label for="wpbb_intergrated"><input name="wpbb_intergrated" type="checkbox" id="wpbb_intergrated" value="wpbb_intergrated" <?php if (get_option('wpbb_intergrated')) { echo('checked="checked"'); } ?> /> <?php _e('Intergrated with Wordpress'); ?></label><br /><?php _e('Check this option if you intergrated your wordpress installation with bbpress installation.'); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('External DB:'); ?></th>
				<td><label for="use_outdb"><input name="use_outdb" type="checkbox" id="use_outdb" value="use_outdb" <?php if (get_option('wpbb_exdb')) { echo('checked="checked"'); } ?> /> <?php _e('Use Different DB'); ?></label><br /><?php _e('Only enable this option if your Bbpress database is not the same as Wordpress database.'); ?></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress DB User:'); ?></th> 
				<td><input name="bbuser" type="text" id="bbuser" value="<?php echo get_option('wpbb_dbuser'); ?>" size="40" />
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress DB Pass:'); ?></th> 
				<td><input name="bbpass" type="text" id="bbpass" value="<?php echo get_option('wpbb_dbpass'); ?>" size="40" />
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress DB Name:'); ?></th> 
				<td><input name="bbname" type="text" id="bbname" value="<?php echo get_option('wpbb_dbname'); ?>" size="40" />
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Bbpress DB Host:'); ?></th> 
				<td><input name="bbhost" type="text" id="bbhost" value="<?php echo get_option('wpbb_dbhost'); ?>" size="40" />
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="wpbb_save" id="wpbb_save" value="<?php _e('Update Option &raquo;'); ?>">
		</p>
	</form>
</div>
<?php
}

### Function: BBpress Latest Discussions Page Display
function wp_bb_get_discuss() {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	if (get_option('wpbb_exdb')) {
		$exbbdb = new wpdb(get_option('wpbb_dbuser'), get_option('wpbb_dbpass'), get_option('wpbb_dbname'), get_option('wpbb_dbhost'));
		$bbtopic = $exbbdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	else {
		$bbtopic = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	if ($bbtopic) {
		echo '
			<div id="discussions">
			<h2>' . __("Discussion Forum") . '</h2>
			<table id="latest">
				<tr>
					<th>' . __("Topic") . '</th>
					<th>' . __("Posts") . '</th>
					<th>' . __("Last Poster") . '</th>
				</tr>
		';
		$misc_no = 0;
		foreach ( $bbtopic as $bbtopic ) {
			if ($misc_no == 0) {
				$misc_no = $misc_no + 1;
				$tr_class = 'alt';
			}
			else {
				$misc_no = $misc_no - 1;
				$tr_class = 'alt1';
			}
			$title_text = wpbb_trim($bbtopic->topic_title, get_option('wpbb_slimit'));
			echo '
				<tr class="'.$tr_class.'">
				<td><a href="' . wpbb_permalink('topic',$bbtopic->topic_id) .'">' . __("$title_text") . '</a></td>
				<td class="num">' . __("$bbtopic->topic_posts") . '</td>
			';
			if (get_option('wpbb_intergrated')) {
				$wpuid = $wpdb->get_row("SELECT * FROM ".$table_prefix."users WHERE user_login = '$bbtopic->topic_last_poster_name'");
				if ($wpuid) {
					$user_forum_data = "$bbtopic->topic_last_poster_name";
					$user_forum_data = get_userdata($wpuid->ID);
					echo '<td class="num">' . __("$user_forum_data->display_name") . '</td>';
				}
				else {
					echo '<td class="num">' . __("$bbtopic->topic_last_poster_name") . '</td>';
				}
			}
			else {
				echo '<td class="num">' . __("$bbtopic->topic_last_poster_name") . '</td>';
			}
			echo "
				</tr>
			";
		}
		echo "</table></div>";
	}
}

### Function: BBpress Extra Post Info
function bbld_extra_info($poster_name, $forum_name, $forum_url) {
	$lastposter = get_option('wpbb_lastposter');
	$inside = get_option('wpbb_inside');
	if ($lastposter || $inside) {
		echo '<small>';
		if ($lastposter) {
			echo __('Last Post By: ') . $poster_name;
			if ($inside)
				echo '<br/>';
		}
		if ($inside) {
			echo __('Inside: ') . '<a href="'.$forum_url.'">' . __("$forum_name") . '</a>';
		}
		echo '</small>';
	}
}

function bbld_filter_forums() {
	global $wpdb;
	$request = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."forums ORDER BY forum_order ASC");
	$exclude_chk = get_option('wpbb_exclude');
	if ($request) {
		foreach($request as $request) {
			if (isset($exclude_chk[$request->forum_id])) {
			}
			else {
				$forum_ids .= $request->forum_id.'\',\'';
			}
		}
		$forum_ids = rtrim($forum_ids, ',\'\' ');
		$where = "WHERE topic_status = '0' AND forum_id IN ('$forum_ids')";
		return $where;
	}
}

### Function: BBpress Latest Discussions Sidebar Display
function wp_bb_get_discuss_sidebar() {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	$filter = bbld_filter_forums();
	if (get_option('wpbb_exdb')) {
		$exbbdb = new wpdb(get_option('wpbb_dbuser'), get_option('wpbb_dbpass'), get_option('wpbb_dbname'), get_option('wpbb_dbhost'));
		$bbtopic = $exbbdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	else {
		$bbtopic = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	if ($bbtopic) {
		echo '
			<h2>' . __("Forum Last $forum_slimit Discussions") . '</h2>
			<ul>
		';
		foreach ( $bbtopic as $bbtopic ) {
			$title_text = wpbb_trim($bbtopic->topic_title, get_option('wpbb_slimit'));
			if (get_option('wpbb_exdb')) {
				$bbforum = $exbbdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$bbtopic->forum_id'");
			}
			else {
				$bbforum = $wpdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$bbtopic->forum_id'");
			}
			echo '<li><a href="' . wpbb_permalink('topic',$bbtopic->topic_id) .'">' . __("$title_text") . '</a><br />';
			$forum_url = wpbb_permalink('forum',$bbtopic->topic_id);
			if (get_option('wpbb_intergrated')) {
				$wpuid = $wpdb->get_row("SELECT * FROM ".$table_prefix."users WHERE user_login = '$bbtopic->topic_last_poster_name'");
				if ($wpuid) {
					$user_forum_data = "$bbtopic->topic_last_poster_name";
					$user_forum_data = get_userdata($wpuid->ID);
					bbld_extra_info($user_forum_data->display_name,$bbforum->forum_name, $forum_url);
					echo "</li>";
				}
				else {
					bbld_extra_info($bbtopic->topic_last_poster_name,$bbforum->forum_name, $forum_url);
					echo "</li>";
				}
			}
			else {	
				bbld_extra_info($bbtopic->topic_last_poster_name,$bbforum->forum_name, $forum_url);
				echo "</li>";
			}
		}
		echo "</ul>";
	}
}

### Function: BBpress Latest Discussions Sidebar Widget
function bbld_widget($args) {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	if (get_option('wpbb_exdb')) {
		$exbbdb = new wpdb(get_option('wpbb_dbuser'), get_option('wpbb_dbpass'), get_option('wpbb_dbname'), get_option('wpbb_dbhost'));
		$bbtopic = $exbbdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	else {
		$bbtopic = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT $forum_slimit");
	}
	if ($bbtopic) {
		extract($args);
		echo $before_widget;
		echo $before_title . __("Forum Last $forum_slimit Discussions") . $after_title;
		echo '<ul>';
		foreach ( $bbtopic as $bbtopic ) {
			$title_text = wpbb_trim($bbtopic->topic_title, get_option('wpbb_slimit'));
			if (get_option('wpbb_exdb')) {
				$bbforum = $exbbdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$bbtopic->forum_id'");
			}
			else {
				$bbforum = $wpdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$bbtopic->forum_id'");
			}
			echo '<li><a href="' . wpbb_permalink('topic',$bbtopic->topic_id) .'">' . __("$title_text") . '</a><br />';
			$forum_url = wpbb_permalink('forum',$bbtopic->topic_id);
			if (get_option('wpbb_intergrated')) {
				$wpuid = $wpdb->get_row("SELECT * FROM ".$table_prefix."users WHERE user_login = '$bbtopic->topic_last_poster_name'");
				if ($wpuid) {
					$user_forum_data = "$bbtopic->topic_last_poster_name";
					$user_forum_data = get_userdata($wpuid->ID);
					bbld_extra_info($user_forum_data->display_name,$bbforum->forum_name, $forum_url);
					echo "</li>";
				}
				else {
					bbld_extra_info($bbtopic->topic_last_poster_name,$bbforum->forum_name, $forum_url);
					echo "</li>";
				}
			}
			else {	
				bbld_extra_info($bbtopic->topic_last_poster_name,$bbforum->forum_name, $forum_url);
				echo "</li>";
			}
		}
		echo "</ul>";
		echo $after_widget;
	}
}

### Function: Register BbLD Widget
function bbld_add_widget() {
	if (function_exists('register_sidebar_widget')) {
		register_sidebar_widget('BbLD Widget','bbld_widget');
	}
}

### Function: Add BbLD Widget
add_action('init', 'bbld_add_widget');

?>
