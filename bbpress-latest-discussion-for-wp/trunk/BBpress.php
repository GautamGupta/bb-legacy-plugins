<?php

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

/*
Plugin Name: BBpress Latest Discussions
Plugin URI: http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/
Description: This plugin will generates Latest Discussion list from your bbpress forum into your wordpress. It has the ability to generate latest discussion on sidebar also. The administrator can also set the behavior for this plugin. Even if your bbpress is not intergrated with your wordpress. U still can use this plugin with a little change on the option page. Bbpress Latest Discussion has been around since almost 2 years ago at Bbpress.org.
Author: Atsutane Shirane
Version: 1.3
Author URI: http://www.atsutane.net/

	This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
*/

$plugin_dir = basename(dirname(__FILE__));

### BBpress Latest Discussions Version Number
$BbLD_version = '1.3';

### BBpress Latest Discussions Advertisment
add_action('wp_head', 'bbld');
function bbld() {
	global $BbLD_version;
	echo '<!-- BBpress Latest Discussions v'.$BbLD_version.': http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/ -->';
}

### Create Text Domain For Translations
add_action('init', 'bbld_textdomain');
function bbld_textdomain() {
	load_plugin_textdomain('bbpress-latest-discussion', false, 'bbpress-latest-discussion');
}

### Function: Install BbLD Configuration
$install = (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' && (isset($_GET['activate']) || isset($_GET['activate-multi'])));
if ($install) {
	bbld_install();
}

function bbld_preinstall() {
	global $BbLD_version;
	if ($BbLD_version <= '1.1.3') {
		$BbLD_ver = get_option('wpbb_version');
		$BbLD_status = get_option('wpbb_status');
		if (($BbLD_ver <= '1.0.3') && ($BbLD_status == 'install')) {
			// Use for remove some old data
			delete_option('wpbb_permalink');
			delete_option('wpbb_intergrated');
			delete_option('wpbb_lastposter');
			delete_option('wpbb_inside');
			update_option('wpbb_version', $BbLD_version);
		}
		if (($BbLD_ver <= '1.1.3') && ($BbLD_ver > '1.0.3') && ($BbLD_status == 'install')) {
			// Export New Data Format
			$bbld_option['version'] = $BbLD_version;
			$bbld_option['url'] = get_option('wpbb_path');
			$bbld_option['limit'] = get_option('wpbb_limit');
			$bbld_option['prefix'] = get_option('wpbb_bbprefix');
			$bbld_option['trim'] = get_option('wpbb_slimit');
			$bbld_option['exdb'] = get_option('wpbb_exdb');
			$bbld_option['dbuser'] = get_option('wpbb_dbuser');
			$bbld_option['dbpass'] = get_option('wpbb_dbpass');
			$bbld_option['dbname'] = get_option('wpbb_dbname');
			$bbld_option['dbhost'] = get_option('wpbb_dbhost');
			$bbld_option['status'] = get_option('wpbb_status');
			$bbld_option['exclude'] = get_option('wpbb_exclude');
			$bbld_option['donate'] = get_option('wpbb_donate');
			$bbld_template['header'] = get_option('wpbb_pagepost_header');
			$bbld_template['body'] = get_option('wpbb_pagepost_body');
			$bbld_template['footer'] = get_option('wpbb_pagepost_footer');
			$bbld_template['widget'] = get_option('wpbb_sidebar_title');
			$bbld_template['sidebar'] = get_option('wpbb_widget_title');
			$bbld_template['sidedisplay'] = get_option('wpbb_sidebar_display');
			update_option('bbld_option', $bbld_option);
			update_option('bbld_template', $bbld_template);
			// Delete Old Data
			delete_option('wpbb_version');
			delete_option('wpbb_path');
			delete_option('wpbb_limit');
			delete_option('wpbb_slimit');
			delete_option('wpbb_exdb');
			delete_option('wpbb_dbuser');
			delete_option('wpbb_dbpass');
			delete_option('wpbb_dbname');
			delete_option('wpbb_dbhost');
			delete_option('wpbb_status');
			delete_option('wpbb_pagepost_header');
			delete_option('wpbb_pagepost_body');
			delete_option('wpbb_pagepost_footer');
			delete_option('wpbb_sidebar_title');
			delete_option('wpbb_widget_title');
			delete_option('wpbb_sidebar_display');
			delete_option('wpbb_bbprefix');
			delete_option('wpbb_exclude');
		}
	}
	else {
	}
}

function bbld_install() {
	global $BbLD_version;
	bbld_preinstall(); // For remove and add new data
	$bbld_option = get_option('bbld_option');
	if ($bbld_option['status'] == FALSE) {
		$bbld_option['version'] = $BbLD_version;
		$bbld_option['url'] = get_settings('home') . '/bbpress';
		$bbld_option['limit'] = 10;
		$bbld_option['prefix'] = 'bb_';
		$bbld_option['trim'] = 100;
		$bbld_option['exdb'] = false;
		$bbld_option['share'] = false;
		$bbld_option['dbuser'] = DB_USER;
		$bbld_option['dbpass'] = DB_PASSWORD;
		$bbld_option['dbname'] = DB_NAME;
		$bbld_option['dbhost'] = DB_HOST;
		$bbld_option['status'] = 'install';
		$bbld_option['donate'] = false;
		$bbld_option['slug'] = 'no';
		$bbld_option['exclude'] = '';	
		$bbld_option['utf8'] = 'UTF-8';
		$bbld_template['header'] = '<div id=\"discussions\"><h2>%BBLD_TITLE%</h2><table id=\"latest\"><tr><th>%BBLD_TOPIC%</th><th>%BBLD_POST%</th><th>%BBLD_LPOSTER%</th></tr>';
		$bbld_template['body'] = '<tr class="%BBLD_CLASS%"><td><a href="%BBLD_URL%">%BBLD_TOPIC%</a></td><td class="num">%BBLD_POST%</td><td class="num">%BBLD_LPOSTER%</td></tr>';
		$bbld_template['footer'] = '</table></div>';
		$bbld_template['widget'] = 'Forum Last %BBLD_LIMIT% Discussions';
		$bbld_template['sidebar'] = '<h2>Forum Last %BBLD_LIMIT% Discussions</h2>';
		$bbld_template['sidedisplay'] = '<li><a href="%BBLD_URL%">%BBLD_TOPIC%</a><br /><small>Last Post By: %BBLD_LPOSTER%<br/>Inside: <a href="%BBLD_FURL%">%BBLD_FORUM%</a></small></li>';
		update_option('bbld_option', $bbld_option);
		update_option('bbld_template', $bbld_template);
	}
}

### Function: Add Option Page
add_action('admin_menu', 'wpbb_add_pages');
function wpbb_add_pages() {
	add_options_page(__("BBpress Latest Discussions Option", 'bbpress-latest-discussion'), __('BbLD Option', 'bbpress-latest-discussion'), 8, __FILE__, 'wp_bb_option');
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
function wpbb_permalink($type,$topicid, $slug = '') {
	global $wpdb,$BbLD_version;
	$bbld_option = get_option('bbld_option');
	if ($bbld_option['slug'] == 1) {
		$permalink = $bbld_option['url'] . '/'. $type . '/' . $topicid;
	}
	elseif ($bbld_option['slug'] == 'slug') {
		if ($type == 'topic') {
			$permalink = $bbld_option['url'] . '/topic/' . $slug;
		}
		else {
			$permalink = $bbld_option['url'] . '/forum/' . $slug;
		}			
	}
	else {
		$permalink = $bbld_option['url'] . '/'. $type . '.php?id=' . $topicid;
	}
	return $permalink;
}

### Function: Filter forum to exclude some forum
function bbld_filter_forums() {
	global $wpdb;
	$bbld_option = get_option('bbld_option');
	$exclude_chk = $bbld_option['exclude'];
	if ($exclude_chk) {
		foreach($exclude_chk as $request) {
				$forum_ids .= $request.'\',\'';
		}
		$forum_ids = rtrim($forum_ids, ',\'\' ');
		$where = "AND ".$bbld_option['prefix']."topics.forum_id NOT IN ('$forum_ids')";
		return $where;
	}
}

### Function: Get forum data
function bbld_getdata($type,$forum_slimit = 0) {
	global $wpdb,$exbbdb;
	$bbld_option = get_option('bbld_option');
	if ($bbld_option['exdb']) {
		$exbbdb = new wpdb($bbld_option['dbuser'], $bbld_option['dbpass'], $bbld_option['dbname'], $bbld_option['dbhost']);
	}
	if ($type == 'topic') {
		$filter = bbld_filter_forums();
		if ($bbld_option['exdb']) {
			$bbtopic = $exbbdb->get_results("SELECT * FROM ".$bbld_option['prefix']."topics JOIN ".$bbld_option['prefix']."forums ON ".$bbld_option['prefix']."topics.topic_status = '0' AND ".$bbld_option['prefix']."topics.forum_id = ".$bbld_option['prefix']."forums.forum_id ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
		}
		else {
			$bbtopic = $wpdb->get_results("SELECT * FROM ".$bbld_option['prefix']."topics JOIN ".$bbld_option['prefix']."forums ON ".$bbld_option['prefix']."topics.topic_status = '0' AND ".$bbld_option['prefix']."topics.forum_id = ".$bbld_option['prefix']."forums.forum_id ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
		}
	}
	elseif ($type == 'utf8') {
		if ($bbld_option['exdb']) {
			$bbld_var = $exbbdb->get_var("SELECT count(*) FROM ".$bbld_option['prefix']."topics");
			$bbld_random = rand(1,$bbld_var);
			$bbtopic = $exbbdb->get_row("SELECT * FROM ".$bbld_option['prefix']."topics WHERE topic_id = ".$bbld_random." LIMIT 1");
		}
		else {
			$bbld_var = $wpdb->get_var("SELECT count(*) FROM ".$bbld_option['prefix']."topics");
			$bbld_random = rand(1,$bbld_var);
			$bbtopic = $wpdb->get_row("SELECT * FROM ".$bbld_option['prefix']."topics WHERE topic_id = ".$bbld_random." LIMIT 1");
		}
	}
	else {
		if ($bbld_option['exdb']) {
			$bbtopic = $exbbdb->get_results("SELECT * FROM ".$bbld_option['prefix']."forums ORDER BY forum_order ASC");
		}
		else {
			$bbtopic = $wpdb->get_results("SELECT * FROM ".$bbld_option['prefix']."forums ORDER BY forum_order ASC");
		}
	}
	return $bbtopic;
}

### Function: BBpress display name
function bbld_intergrated($id,$name) {
	global $wpdb,$table_prefix;
	$bbld_option = get_option('bbld_option');
	if ($bbld_option['share']) {
		$user_forum_data = get_userdata($id);
		if ($user_forum_data->display_name) {
			$euser = $user_forum_data->display_name;
		}
		else {
			$euser = $name;
		}
	}
	else {
		$euser = $name;
	}
	return $euser;
}

### Function: Donate a link back.
function bbld_donate() {
		echo '<p style="font-size: 75%; float: right">Powered By <a href="http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/">BbLD</a></p>';
}

### Function: BBpress Latest Discussions Page Display
function wp_bb_get_discuss() {
	global $table_prefix,$wpdb;
	$bbld_option = get_option('bbld_option');
	$bbtopic = bbld_getdata('topic',$bbld_option['limit']);
	$bbld_template = get_option('bbld_template');
	if ($bbtopic) {
		$template_data_head = stripslashes($bbld_template['header']);
		$template_data_head = str_replace("%BBLD_TITLE%", __("Discussion Forum", 'bbpress-latest-discussion'), $template_data_head);
		$template_data_head = str_replace("%BBLD_TOPIC%", __("Topic", 'bbpress-latest-discussion'), $template_data_head);
		$template_data_head = str_replace("%BBLD_POST%", __("Posts", 'bbpress-latest-discussion'), $template_data_head);
		$template_data_head = str_replace("%BBLD_LPOSTER%", __("Last Poster", 'bbpress-latest-discussion'), $template_data_head);
		echo $template_data_head;
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
			$title_text = bbld_utf8($bbtopic->topic_title, $bbld_option['utf8']);
			$title_text = wpbb_trim($title_text, $bbld_option['trim']);
			$last_poster = bbld_intergrated($bbtopic->topic_last_poster,$bbtopic->topic_last_poster_name);
			$template_data_body = stripslashes($bbld_template['body']);
			$template_data_body = str_replace("%BBLD_CLASS%", $tr_class, $template_data_body);
			$template_data_body = str_replace("%BBLD_URL%", wpbb_permalink('topic',$bbtopic->topic_id,$bbtopic->topic_slug), $template_data_body);
			$template_data_body = str_replace("%BBLD_TOPIC%", $title_text, $template_data_body);
			$template_data_body = str_replace("%BBLD_POST%", $bbtopic->topic_posts, $template_data_body);
			$template_data_body = str_replace("%BBLD_LPOSTER%", $last_poster, $template_data_body);
			echo $template_data_body;
		}
		echo stripslashes($bbld_template['footer']);
		if ($bbld_option['donate']) {
			bbld_donate();
		}
	}
}

### Function: Convert Data Into UTF-8
function bbld_utf8($data, $charset = 'UTF-8') {
	if (mb_check_encoding($data,"UTF-8") == FALSE) {
		$data = utf8_encode($data);
		$string = iconv("UTF-8", $charset."//TRANSLIT", $data);
	}
	else {
		$string = iconv("UTF-8", $charset."//TRANSLIT", $data);
	}
	if (!$string) { $string = $data; }
	return $string;
}

### Function: BBpress Latest Discussions Sidebar code
function bbld_getside() {
	global $table_prefix,$wpdb;
	$bbld_option = get_option('bbld_option');
	$bbtopic = bbld_getdata('topic',$bbld_option['limit']);
	$bbld_template = get_option('bbld_template');
	if ($bbtopic) {
		foreach ( $bbtopic as $bbtopic ) {
			$title_text = bbld_utf8($bbtopic->topic_title, $bbld_option['utf8']);
			$title_text = wpbb_trim($title_text, $bbld_option['trim']);
			$forum_url = wpbb_permalink('forum',$bbtopic->forum_id,$bbtopic->forum_slug);
			$last_poster = bbld_intergrated($bbtopic->topic_last_poster,$bbtopic->topic_last_poster_name);
			$template_data_sidebar = stripslashes($bbld_template['sidedisplay']);
			$template_data_sidebar = str_replace("%BBLD_URL%", wpbb_permalink('topic',$bbtopic->topic_id,$bbtopic->topic_slug), $template_data_sidebar);
			$template_data_sidebar = str_replace("%BBLD_TOPIC%", $title_text, $template_data_sidebar);
			$template_data_sidebar = str_replace("%BBLD_FURL%", $forum_url, $template_data_sidebar);
			$forum_name = bbld_utf8($bbtopic->forum_name, $bbld_option['utf8']);
			$template_data_sidebar = str_replace("%BBLD_FORUM%", $forum_name, $template_data_sidebar);
			$template_data_sidebar = str_replace("%BBLD_LPOSTER%", $last_poster, $template_data_sidebar);
			echo $template_data_sidebar;
		}
	}
}

### Function: BBpress Latest Discussions Sidebar Display
function wp_bb_get_discuss_sidebar() {
	global $table_prefix,$wpdb;
	$bbld_template = get_option('bbld_template');
	$bbld_option = get_option('bbld_option');
	$template_sidebar = stripslashes($bbld_template['sidebar']);
	$template_sidebar = str_replace("%BBLD_LIMIT%", $bbld_option['limit'], $template_sidebar);
	echo '<h2>'.$template_sidebar.'</h2>';
	echo '<ul>';
	bbld_getside();
	echo "</ul>";
	if ($bbld_option['donate']) {
		bbld_donate();
	}
}

### Function: BBpress Latest Discussions Sidebar Widget
function bbld_widget($args) {
	global $table_prefix,$wpdb;
	$bbld_template = get_option('bbld_template');
	$bbld_option = get_option('bbld_option');
	$template_sidebar = str_replace("%BBLD_LIMIT%", $bbld_option['limit'], $bbld_template['widget']);
	extract($args);
	echo $before_widget;
	echo $before_title . $template_sidebar . $after_title;
	echo '<ul>';
	bbld_getside();
	echo "</ul>";
	echo $after_widget;
	if ($bbld_option['donate']) {
		bbld_donate();
	}
}

### Function: BBpress Latest Discussions Sidebar Widget Control
function bbld_widget_control() {
	$bbld_template = get_option('bbld_template');
	if ($_POST['bbld_widget_submit']) {
		$bbld_template['widget'] = $_POST['bbld_widget_title'];
		update_option('bbld_template', $bbld_template);
	}
	echo '
		<label for="bbld_widget_title">'. __('BbLD Widget Title:', 'bbpress-latest-discussion') . '</label>
		<input name="bbld_widget_title" type="text" id="bbld_widget_title" value="'.$bbld_template['widget'].'" class="regular-text code" /><br />
		<span class="setting-description">Allowed Variables: %BBLD_LIMIT%</span>
		<input type="hidden" id="bbld_widget_submit" name="bbld_widget_submit" value="1" />
	';
}

### Function: Register BbLD Widget
function bbld_add_widget() {
	if (function_exists('register_sidebar_widget')) {
		register_sidebar_widget('BbLD Widget','bbld_widget');
		register_widget_control('BbLD Widget', 'bbld_widget_control', 400, 300);
	}
}

### Function: Add BbLD Widget
add_action('init', 'bbld_add_widget');

### Function: Add icon for option page
add_action('admin_head', 'bbld_css');
function bbld_css() {
	global $plugin_dir;
	echo '
		<link rel="stylesheet" href="' . get_option('siteurl') . '/wp-content/plugins/'.$plugin_dir.'/style.css" type="text/css" media="screen" />
		<script type="text/javascript" src="' . get_option('siteurl') . '/wp-content/plugins/'.$plugin_dir.'/Bbpress.js"></script>
	';
}

### Function: BBpress Latest Discussions Option
function wp_bb_option() {
	global $wpdb,$BbLD_version,$plugin_dir;
	$ori_url = $_SERVER['REQUEST_URI'];
	$bbld_template = get_option('bbld_template');
	$bbld_option = get_option('bbld_option');
	if ($_POST['wpbb_save']){
		$bbld_option['url'] = $_POST['bburl'];
		$bbld_option['limit'] = $_POST['bblimit'];
		$bbld_option['prefix'] = $_POST['bbprefix'];
		$bbld_option['trim'] = $_POST['bbslimit'];
		$bbld_option['exdb'] = $_POST['use_outdb'];
		$bbld_option['dbuser'] = $_POST['bbuser'];
		$bbld_option['dbpass'] = $_POST['bbpass'];
		$bbld_option['dbname'] = $_POST['bbname'];
		$bbld_option['dbhost'] = $_POST['bbhost'];
		$bbld_option['exclude'] = $_POST['wpbb_exclude'];
		$bbld_option['donate'] = $_POST['bbld_donate'];
		$bbld_option['share'] = $_POST['bbld_share'];
		$bbld_option['slug'] = $_POST['bbld_permalink'];
		$bbld_option['utf8'] = $_POST['bbld_utf8'];
		update_option('bbld_option', $bbld_option);
		$update_msg = "<div id='message' class='updated fade'><p>BBpress Latest Discussions options saved successfully.</p></div>";
	}
	if ($_POST['wpbb_save_template']){
		$bbld_template['header'] = $_POST['bbld_postpage_header'];
		$bbld_template['body'] = $_POST['bbld_postpage_body'];
		$bbld_template['footer'] = $_POST['bbld_postpage_footer'];
		$bbld_template['sidebar'] = $_POST['bbld_sidebar_title'];
		$bbld_template['sidedisplay'] = $_POST['bbld_sidebar_display'];
		update_option('bbld_template', $bbld_template);
		$update_msg = "<div id='message' class='updated fade'><p>BBpress Latest Discussions templates saved successfully.</p></div>";
	}
?>
<div class="wrap">
	<p class="bbldright"><a href="http://www.atsutane.net" title="Atsutane Dot Net" alt="Atsutane Dot Net" ><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/<?php echo $plugin_dir; ?>/images/atsutane.gif" alt="Atsutane Dot Net" /></a></p>
	<div id="icon-bbld" class="icon32"><br /></div>
<h2><?php _e('BBpress Latest Discussions', 'bbpress-latest-discussion'); echo ' ('.$BbLD_version.')'; ?></h2>
<?php if (!$bbld_option['donate']) { echo "<div id='message' class='updated fade'><p>If you like my work. Please donate a link back.</p></div>"; } ?>
<?php if ($update_msg) { _e("$update_msg", 'bbpress-latest-discussion'); } ?>
<form method="post" action="<?php echo $ori_url; ?>">
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="bburl"><?php _e('Bbpress URL:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bburl" type="text" id="bburl" value="<?php echo $bbld_option['url']; ?>" class="regular-text code" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bblimit"><?php _e('Post Limit:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bblimit" type="text" id="bblimit"  value="<?php echo $bbld_option['limit']; ?>" class="small-text" />
<span class="setting-description"><?php _e("Set the number of topic you want to show.", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbslimit"><?php _e('Text Limit:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbslimit" type="text" id="bbslimit"  value="<?php echo $bbld_option['trim']; ?>" class="small-text" />
<span class="setting-description"><?php _e("Set the length of text you want to show", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbprefix"><?php _e('Bbpress DB Prefix:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbprefix" type="text" id="bbprefix"  value="<?php echo $bbld_option['prefix']; ?>" class="small-text" />
<span class="setting-description"><?php _e("Enter the table prefix for your bbPress installation. The table prefix is found in your bbPress installation's config.php file.", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Share UserData:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">bbld_share</legend><label for="bbld_share">
<input name="bbld_share" type="checkbox" id="bbld_share" value="bbld_share" <?php if ($bbld_option['share']) { echo('checked="checked"'); } ?> />
<?php _e('Check this option if you are share Wordpress/Bbpress userdata.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr>
<th scope="row"><?php _e('Permalink Format:', 'bbpress-latest-discussion'); ?></th>
<td>
	<fieldset><legend class="hidden">Date Format</legend>
	<label><input type='radio' name='bbld_permalink' value='no' <?php if ($bbld_option['slug'] == 'no') { echo 'checked="checked"'; } ?> /> None ... /forum.php?id=1</label><br />
	<label><input type='radio' name='bbld_permalink' value='1' <?php if ($bbld_option['slug'] == 1) { echo 'checked="checked"'; } ?> /> Numeric .../forum/1</label><br />
	<label><input type='radio' name='bbld_permalink' value='slug' <?php if ($bbld_option['slug'] == 'slug') { echo 'checked="checked"'; } ?> /> Name based .../forum/first-forum</label><br />
	<p>Choose Bbpress permalink type.</p>
	</fieldset>
</td>
</tr>

<tr>
<th scope="row"><?php _e('Exclude Forums:', 'bbpress-latest-discussion'); ?></th>
<td>
	<fieldset><legend class="hidden">Date Format</legend>
		<?php
			$request = bbld_getdata('exclude');
			$exclude_chk = $bbld_option['exclude'];
			if ($request) {
				foreach ($request as $request) {
					if (isset($exclude_chk[$request->forum_id])) {
						$allowed_forum = $request->forum_id;
						$exclude_option = "checked=\"checked\"";
					}
					echo "
							<label for=\"wpbb_exclude[$request->forum_id]\"><input name=\"wpbb_exclude[$request->forum_id]\" type=\"checkbox\" id=\"wpbb_exclude[$request->forum_id]\" value=\"$request->forum_id\"
					";
					if ($allowed_forum == $request->forum_id) { echo "$exclude_option"; }
					echo "
							/> $request->forum_name</label><br />
					";
				}
				echo '<p>'.__("Use this option if you want to exclude forum from being display on wordpress.", 'bbpress-latest-discussion').'</p>';
			}
			else { echo '<p>'.__("There is no forum to be display.", 'bbpress-latest-discussion').'</p>'; }
		?>
	</fieldset>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Donate Link:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">bbld_donate</legend><label for="bbld_donate">
<input name="bbld_donate" type="checkbox" id="bbld_donate" value="bbld_donate" <?php if ($bbld_option['donate']) { echo('checked="checked"'); } ?> />
<?php _e('If you like my work. Please donate a link back using this option.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>

<?php
	$utf8_sample = bbld_getdata('utf8');
?>
<th scope="row"><?php _e('Encoding Format:', 'bbpress-latest-discussion'); ?></th>
<td>
	<fieldset><legend class="hidden">Encoding Format</legend>
	<label><input type='radio' name='bbld_utf8' value='UTF-8' <?php if ($bbld_option['utf8'] == 'UTF-8') { echo 'checked="checked"'; } ?> /> <strong>UTF-8</strong>, Example: <?php echo bbld_utf8($utf8_sample->topic_title); ?></label><br />
	<label><input type='radio' name='bbld_utf8' value='ISO-8859-1' <?php if ($bbld_option['utf8'] == 'ISO-8859-1') { echo 'checked="checked"'; } ?> /> <strong>ISO-8859-1</strong>, Example: <?php echo bbld_utf8($utf8_sample->topic_title, 'ISO-8859-1'); ?></label><br />
	<label><input type='radio' name='bbld_utf8' value='ASCII' <?php if ($bbld_option['utf8'] == 'ASCII') { echo 'checked="checked"'; } ?> /> <strong>ASCII</strong>, Example: <?php echo bbld_utf8($utf8_sample->topic_title, 'ASCII'); ?></label><br />
	<p><?php _e('It will show how the plugin display the title depend on each encoding. By default it is UTF-8. Each refresh will show different title.', 'bbpress-latest-discussion'); ?></p>
	</fieldset>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('External DB:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">use_outdb</legend><label for="use_outdb">
<input name="use_outdb" type="checkbox" id="use_outdb" value="use_outdb" <?php if ($bbld_option['exdb']) { echo('checked="checked"'); } ?> />
<?php _e('Only enable this option if your Bbpress database is not the same as Wordpress database.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbuser"><?php _e('Bbpress DB User:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbuser" type="text" id="bbuser" value="<?php echo $bbld_option['dbuser']; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbpass"><?php _e('Bbpress DB Pass:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbpass" type="text" id="bbpass" value="<?php echo $bbld_option['dbpass']; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbname"><?php _e('Bbpress DB Name:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbname" type="text" id="bbname" value="<?php echo $bbld_option['dbname']; ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbhost"><?php _e('Bbpress DB Host:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbhost" type="text" id="bbhost" value="<?php echo $bbld_option['dbhost']; ?>" class="regular-text" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="wpbb_save" id="wpbb_save" class="button-primary" value="<?php _e('Update Option &raquo;', 'bbpress-latest-discussion'); ?>" />
</p>
</form>

<div id="icon-bbld" class="icon32"><br /></div>
<h2><?php _e('BbLD Templates System', 'bbpress-latest-discussion'); ?></h2>

<ul id="Tabs">
	<li id="PostPageTab" class="SelectedTab"><a href="#PostPage" onclick="index(); return false;">Post/Page</a></li>
	<li id="SidebarTab" class="Tab"><a href="#Sidebar" onclick="sidebar(); return false;">Sidebar/Widget</a></li>
</ul>

<form method="post" action="<?php echo $ori_url; ?>">
<div id="Content">
<div id="PostPage">
<table class="form-table">
<tr valign="top">
<th scope="row">
	<p><strong>BbLD Post/Page Header:</strong></p>
	<p>Allowed Variables:</p>
	<p style="margin: 2px 0">- %BBLD_TITLE%</p>
	<p style="margin: 2px 0">- %BBLD_TOPIC%</p>
	<p style="margin: 2px 0">- %BBLD_POST%</p>
	<p style="margin: 2px 0">- %BBLD_LPOSTER%</p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('postpage_header');" class="button" /></p>
</th>
<td><textarea name="bbld_postpage_header" rows="10" cols="50" id="bbld_postpage_header" class="large-text code"><?php echo htmlspecialchars(stripslashes($bbld_template['header'])); ?></textarea></td>
</tr>

<tr valign="top">
<th scope="row">
	<p><strong>BbLD Post/Page Body:</strong></p>
	<p>Allowed Variables:</p>
	<p style="margin: 2px 0">- %BBLD_CLASS%</p>
	<p style="margin: 2px 0">- %BBLD_URL%</p>
	<p style="margin: 2px 0">- %BBLD_TOPIC%</p>
	<p style="margin: 2px 0">- %BBLD_POST%</p>
	<p style="margin: 2px 0">- %BBLD_LPOSTER%</p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('postpage_body');" class="button" /></p>
</th>
<td><textarea name="bbld_postpage_body" rows="10" cols="50" id="bbld_postpage_body" class="large-text code"><?php echo htmlspecialchars(stripslashes($bbld_template['body'])); ?></textarea></td>
</tr>

<tr valign="top">
<th scope="row">
	<p><strong>BbLD Post/Page Footer:</strong></p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('postpage_footer');" class="button" /></p>
</th>
<td><textarea name="bbld_postpage_footer" rows="10" cols="50" id="bbld_postpage_footer" class="large-text code"><?php echo htmlspecialchars(stripslashes($bbld_template['footer'])); ?></textarea></td>
</tr>
</table>
</div>

<div id="Sidebar" style="display: none;">
<table class="form-table">
<tr valign="top">
<th scope="row">
	<p><strong>BbLD Sidebar Title:</strong></p>
	<p>Allowed Variables:</p>
	<p style="margin: 2px 0">- %BBLD_LIMIT%</p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('sidebar_title');" class="button" /></p>
</th>
<td><textarea name="bbld_sidebar_title" rows="10" cols="50" id="bbld_sidebar_title" class="large-text code"><?php echo htmlspecialchars(stripslashes($bbld_template['sidebar'])); ?></textarea></td>
</tr>

<tr valign="top">
<th scope="row">
	<p><strong>BbLD Sidebar Display:</strong></p>
	<p>Allowed Variables:</p>
	<p style="margin: 2px 0">- %BBLD_URL%</p>
	<p style="margin: 2px 0">- %BBLD_TOPIC%</p>
	<p style="margin: 2px 0">- %BBLD_FURL%</p>
	<p style="margin: 2px 0">- %BBLD_FORUM%</p>
	<p style="margin: 2px 0">- %BBLD_LPOSTER%</p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('sidebar_display');" class="button" /></p>
</th>
<td><textarea name="bbld_sidebar_display" rows="10" cols="50" id="bbld_sidebar_display" class="large-text code"><?php echo htmlspecialchars(stripslashes($bbld_template['sidedisplay'])); ?></textarea></td>
</tr>
</table>
</div>
</div>
<p class="submit">
<input type="submit" name="wpbb_save_template" id="wpbb_save_template" class="button-primary" value="<?php _e('Save Templates Change &raquo;', 'bbpress-latest-discussion'); ?>" />
</p>
</form>
</div>
<?php
}

?>
