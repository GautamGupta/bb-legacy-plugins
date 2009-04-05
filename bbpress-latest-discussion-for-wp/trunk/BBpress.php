<?php
/*
Plugin Name: BBpress Latest Discussions
Plugin URI: http://www.atsutane.net/2006/11/bbpress-latest-discussion-for-wordpress/
Description: Put bbpress Latest Discussions on your wp page.
Author: Atsutane Shirane
Version: 1.0.2
Author URI: http://www.atsutane.net/
*/

$plugin_dir = basename(dirname(__FILE__));

### BBpress Latest Discussions Version Number
$BbLD_version = '1.0.2';


if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

### Create Text Domain For Translations
add_action('init', 'bbld_textdomain');
function bbld_textdomain() {
	load_plugin_textdomain('bbpress-latest-discussion', false, 'bbpress-latest-discussion');
}

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
		$head_data = '<div id=\"discussions\"><h2>%BBLD_TITLE%</h2><table id=\"latest\"><tr><th>%BBLD_TOPIC%</th><th>%BBLD_POST%</th><th>%BBLD_LPOSTER%</th></tr>';
		update_option('wpbb_pagepost_header', $head_data);
		$body_data = '<tr class="%BBLD_CLASS%"><td><a href="%BBLD_URL%">%BBLD_TOPIC%</a></td><td class="num">%BBLD_POST%</td><td class="num">%BBLD_LPOSTER%</td></tr>';
		update_option('wpbb_pagepost_body', $body_data);
		update_option('wpbb_pagepost_footer', '</table></div>');
		update_option('wpbb_sidebar_title', '<h2>Forum Last %BBLD_LIMIT% Discussions</h2>');
		update_option("wpbb_widget_title", 'Forum Last %BBLD_LIMIT% Discussions');
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
function wpbb_permalink($type,$topicid) {
	global $wpdb,$BbLD_version;
	if (get_option('wpbb_permalink')) {
		$perma_type = $wpdb->get_row("SELECT * FROM `".get_option('wpbb_bbprefix')."topicmeta` WHERE `meta_key` LIKE 'mod_rewrite' LIMIT 1");
		$metakey = $perma_type->meta_value;
		if ($metakey == 1) {
			$permalink = get_option('wpbb_path') . '/'. $type . '/' . $topicid;
		}
		else {
			if ($type == 'topic') {
				$get_title = $wpdb->get_row("SELECT * FROM `".get_option('wpbb_bbprefix')."topics` WHERE `topic_id` LIKE '$topicid' LIMIT 1");
				$permalink = get_option('wpbb_path') . '/topic/' . $get_title->topic_slug;
			}
			else {
				$get_title = $wpdb->get_row("SELECT * FROM `".get_option('wpbb_bbprefix')."forums` WHERE `forum_id` LIKE '$topicid' LIMIT 1");
				$permalink = get_option('wpbb_path') . '/forum/' . $get_title->forum_slug;
			}			
		}
	}
	else {
		$permalink = get_option('wpbb_path') . '/'. $type . '.php?id=' . $topicid;
	}
	return $permalink;
}

### Function: Filter forum to exclude some forum
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

### Function: Get forum data
function bbld_getdata($type,$forum_slimit) {
	global $wpdb;
	if ($type == 'topic') {
		$filter = bbld_filter_forums();
		if (get_option('wpbb_exdb')) {
			$exbbdb = new wpdb(get_option('wpbb_dbuser'), get_option('wpbb_dbpass'), get_option('wpbb_dbname'), get_option('wpbb_dbhost'));
			$bbtopic = $exbbdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
		}
		else {
			$bbtopic = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."topics ".$filter." ORDER BY topic_time DESC LIMIT $forum_slimit");
		}
	}
	else {
		if (get_option('wpbb_exdb')) {
			$exbbdb = new wpdb(get_option('wpbb_dbuser'), get_option('wpbb_dbpass'), get_option('wpbb_dbname'), get_option('wpbb_dbhost'));
			$bbtopic = $exbbdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$forum_slimit'");
		}
		else {
			$bbtopic = $wpdb->get_row("SELECT * FROM ".get_option('wpbb_bbprefix')."forums WHERE forum_id = '$forum_slimit'");
		}
	}
	return $bbtopic;
}

### Function: BBpress display name
function bbld_intergrated($name) {
	global $wpdb,$table_prefix;
	if (get_option('wpbb_intergrated')) {
		$wpuid = $wpdb->get_row("SELECT * FROM ".$table_prefix."users WHERE user_login = '$name'");
		if ($wpuid) {
			$user_forum_data = get_userdata($wpuid->ID);
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
	}
	else {
		$euser = $name;
	}
	return $euser;
}

### Function: BBpress Extra Post Info
function bbld_extra_info($poster_name, $forum_name, $forum_url) {
	$lastposter = get_option('wpbb_lastposter');
	$inside = get_option('wpbb_inside');
	if ($lastposter || $inside) {
		echo '<small>';
		if ($lastposter) {
			echo __('Last Post By: ', 'bbpress-latest-discussion') . $poster_name;
			if ($inside)
				echo '<br/>';
		}
		if ($inside) {
			echo __('Inside: ', 'bbpress-latest-discussion') . '<a href="'.$forum_url.'">' . __("$forum_name", 'bbpress-latest-discussion') . '</a>';
		}
		echo '</small>';
	}
}

### Function: BBpress Latest Discussions Page Display
function wp_bb_get_discuss() {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	$bbtopic = bbld_getdata('topic',$forum_slimit);
	if ($bbtopic) {
		$template_data_head = stripslashes(get_option('wpbb_pagepost_header'));
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
			$title_text = wpbb_trim($bbtopic->topic_title, get_option('wpbb_slimit'));
			$last_poster = bbld_intergrated($bbtopic->topic_last_poster_name);
			$template_data_body = stripslashes(get_option('wpbb_pagepost_body'));
			$template_data_body = str_replace("%BBLD_CLASS%", $tr_class, $template_data_body);
			$template_data_body = str_replace("%BBLD_URL%", __(wpbb_permalink('topic',$bbtopic->topic_id), 'bbpress-latest-discussion'), $template_data_body);
			$template_data_body = str_replace("%BBLD_TOPIC%", $title_text, $template_data_body);
			$template_data_body = str_replace("%BBLD_POST%", $bbtopic->topic_posts, $template_data_body);
			$template_data_body = str_replace("%BBLD_LPOSTER%", $last_poster, $template_data_body);
			echo $template_data_body;
		}
		echo stripslashes(get_option('wpbb_pagepost_footer'));
	}
}

### Function: BBpress Latest Discussions Sidebar code
function bbld_getside() {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	$bbtopic = bbld_getdata('topic',$forum_slimit);
	if ($bbtopic) {
		foreach ( $bbtopic as $bbtopic ) {
			$title_text = wpbb_trim($bbtopic->topic_title, get_option('wpbb_slimit'));
			$bbforum = bbld_getdata('forum',$bbtopic->forum_id);
			$forum_url = wpbb_permalink('forum',$bbtopic->topic_id);
			$last_poster = bbld_intergrated($bbtopic->topic_last_poster_name);
			echo '<li><a href="' . wpbb_permalink('topic',$bbtopic->topic_id) .'">' . __("$title_text", 'bbpress-latest-discussion') . '</a><br />';
			bbld_extra_info($last_poster,$bbforum->forum_name, $forum_url);
			echo "</li>";
		}
	}
}

### Function: BBpress Latest Discussions Sidebar Display
function wp_bb_get_discuss_sidebar() {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	$template_sidebar = stripslashes(get_option('wpbb_sidebar_title'));
	$template_sidebar = str_replace("%BBLD_LIMIT%", $forum_slimit, $template_sidebar);
	echo '<h2>'.$template_sidebar.'</h2>';
	echo '<ul>';
	bbld_getside();
	echo "</ul>";
}

### Function: BBpress Latest Discussions Sidebar Widget
function bbld_widget($args) {
	global $table_prefix,$wpdb;
	$forum_slimit = get_option('wpbb_limit');
	$template_sidebar = get_option('wpbb_widget_title');
	$template_sidebar = str_replace("%BBLD_LIMIT%", $forum_slimit, $template_sidebar);
	extract($args);
	echo $before_widget;
	echo $before_title . $template_sidebar . $after_title;
	echo '<ul>';
	bbld_getside();
	echo "</ul>";
	echo $after_widget;
}

### Function: BBpress Latest Discussions Sidebar Widget Control
function bbld_widget_control() {
	$widget_title = get_option('wpbb_widget_title');
	if ($_POST['bbld_widget_submit']) {
		update_option("wpbb_widget_title", $_POST['bbld_widget_title']);
	}
	echo '
		<label for="bbld_widget_title">'. __('BbLD Widget Title:', 'bbpress-latest-discussion') . '</label>
		<input name="bbld_widget_title" type="text" id="bbld_widget_title" value="'.get_option('wpbb_widget_title').'" class="regular-text code" /><br />
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
	echo '<link rel="stylesheet" href="' . get_option('siteurl') . '/wp-content/plugins/'.$plugin_dir.'/style.css" type="text/css" media="screen" />';
echo '<script type="text/javascript">
/* <![CDATA[*/
	function bbld_default_templates(template) {
		var default_template;
		switch(template) {
			case "postpage_header":
				default_template = "<div id=\"discussions\"><h2>%BBLD_TITLE%</h2><table id=\"latest\"><tr><th>%BBLD_TOPIC%</th><th>%BBLD_POST%</th><th>%BBLD_LPOSTER%</th></tr>";
				break;
			case "postpage_body":
				default_template = "<tr class=\"%BBLD_CLASS%\"><td><a href=\"%BBLD_URL%\">%BBLD_TOPIC%</a></td><td class=\"num\">%BBLD_POST%</td><td class=\"num\">%BBLD_LPOSTER%</td></tr>";
				break;
			case "postpage_footer":
				default_template = "</table></div>";
				break;
			case "sidebar_title":
				default_template = "<h2>Forum Last %BBLD_LIMIT% Discussions</h2>";
				break;
		}
		document.getElementById("bbld_" + template).value = default_template;
	}
/* ]]> */
</script>';
}

require_once(ABSPATH . WPINC . '/rss.php');

### Function: BBpress Latest Discussions Option
function wp_bb_option() {
	global $wpdb,$BbLD_version,$plugin_dir;
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
	if ($_POST['wpbb_save_template']){
		update_option('wpbb_pagepost_header', $_POST['bbld_postpage_header']);
		update_option('wpbb_pagepost_body', $_POST['bbld_postpage_body']);
		update_option('wpbb_pagepost_footer', $_POST['bbld_postpage_footer']);
		update_option('wpbb_sidebar_title', $_POST['bbld_sidebar_title']);
		$update_msg = "<div id='message' class='updated fade'><p>BBpress Latest Discussions templates saved successfully.</p></div>";
	}
?>
<div class="wrap">
	<p class="bbldright"><a href="http://www.atsutane.net" title="Atsutane Dot Net" alt="Atsutane Dot Net" ><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/<?php echo $plugin_dir; ?>/images/atsutane.gif" alt="Atsutane Dot Net" /></a></p>
	<div id="icon-bbld" class="icon32"><br /></div>
<h2><?php _e('BBpress Latest Discussions', 'bbpress-latest-discussion'); echo ' ('.$BbLD_version.')'; ?></h2>
<?php if ($update_msg) { _e("$update_msg", 'bbpress-latest-discussion'); } ?>
<form method="post" action="<?php echo $ori_url; ?>">
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="bburl"><?php _e('Bbpress URL:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bburl" type="text" id="bburl" value="<?php echo get_option('wpbb_path'); ?>" class="regular-text code" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bblimit"><?php _e('Post Limit:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bblimit" type="text" id="bblimit"  value="<?php echo get_option('wpbb_limit'); ?>" class="small-text" />
<span class="setting-description"><?php _e("Set the number of topic you want to show.", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbslimit"><?php _e('Text Limit:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbslimit" type="text" id="bbslimit"  value="<?php echo get_option('wpbb_slimit'); ?>" class="small-text" />
<span class="setting-description"><?php _e("Set the length of text you want to show", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbprefix"><?php _e('Bbpress DB Prefix:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbprefix" type="text" id="bbprefix"  value="<?php echo get_option('wpbb_bbprefix'); ?>" class="small-text" />
<span class="setting-description"><?php _e("Enter the table prefix for your bbPress installation. The table prefix is found in your bbPress installation's config.php file.", 'bbpress-latest-discussion'); ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Bbpress Permalink:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">wpbb_permalink</legend><label for="wpbb_permalink">
<input name="wpbb_permalink" type="checkbox" id="wpbb_permalink" value="wpbb_permalink" <?php if (get_option('wpbb_permalink')) { echo('checked="checked"'); } ?> />
<?php _e('Only use this if you already enable permalink inside Bbpress.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Bbpress Last Poster:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">wpbb_lastposter</legend><label for="wpbb_lastposter">
<input name="wpbb_lastposter" type="checkbox" id="wpbb_lastposter" value="wpbb_lastposter" <?php if (get_option('wpbb_lastposter')) { echo('checked="checked"'); } ?> />
<?php _e('Check this option if you want to display the username of the last poster.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Bbpress Inside:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">wpbb_inside</legend><label for="wpbb_inside">
<input name="wpbb_inside" type="checkbox" id="wpbb_inside" value="wpbb_inside" <?php if (get_option('wpbb_inside')) { echo('checked="checked"'); } ?> />
<?php _e('Check this option if you want the "inside" link to appear.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Wordpress/Bbpress Integration:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">wpbb_intergrated</legend><label for="wpbb_intergrated">
<input name="wpbb_intergrated" type="checkbox" id="wpbb_intergrated" value="wpbb_intergrated" <?php if (get_option('wpbb_intergrated')) { echo('checked="checked"'); } ?> />
<?php _e('Check this option if you intergrated your wordpress installation with bbpress installation.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr>
<th scope="row"><?php _e('Exclude Forums:', 'bbpress-latest-discussion'); ?></th>
<td>
	<fieldset><legend class="hidden">Date Format</legend>
		<?php
			$request = $wpdb->get_results("SELECT * FROM ".get_option('wpbb_bbprefix')."forums ORDER BY forum_order ASC");
			$exclude_chk = get_option('wpbb_exclude');
			if ($request) {
				foreach ($request as $request) {
					if (isset($exclude_chk[$request->forum_id])) {
						$allowed_forum = $request->forum_id;
						$exclude_option = "checked=\"checked\"";
					}
					echo "
							<label for=\"wpbb_exclude[$request->forum_id]\"><input name=\"wpbb_exclude[$request->forum_id]\" type=\"checkbox\" id=\"wpbb_exclude[$request->forum_id]\" value=\"wpbb_exclude[$request->forum_id]\"
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
<th scope="row"><?php _e('External DB:', 'bbpress-latest-discussion'); ?></th>
<td> <fieldset><legend class="hidden">use_outdb</legend><label for="use_outdb">
<input name="use_outdb" type="checkbox" id="use_outdb" value="use_outdb" <?php if (get_option('wpbb_exdb')) { echo('checked="checked"'); } ?> />
<?php _e('Only enable this option if your Bbpress database is not the same as Wordpress database.', 'bbpress-latest-discussion'); ?></label>
</fieldset></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbuser"><?php _e('Bbpress DB User:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbuser" type="text" id="bbuser" value="<?php echo get_option('wpbb_dbuser'); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbpass"><?php _e('Bbpress DB Pass:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbpass" type="text" id="bbpass" value="<?php echo get_option('wpbb_dbpass'); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbname"><?php _e('Bbpress DB Name:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbname" type="text" id="bbname" value="<?php echo get_option('wpbb_dbname'); ?>" class="regular-text" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bbhost"><?php _e('Bbpress DB Host:', 'bbpress-latest-discussion'); ?></label></th>
<td><input name="bbhost" type="text" id="bbhost" value="<?php echo get_option('wpbb_dbhost'); ?>" class="regular-text" /></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="wpbb_save" id="wpbb_save" class="button-primary" value="<?php _e('Update Option &raquo;', 'bbpress-latest-discussion'); ?>" />
</p>
</form>

<div id="icon-bbld" class="icon32"><br /></div>
<h2><?php _e('BbLD Templates System', 'bbpress-latest-discussion'); ?></h2>
<form method="post" action="<?php echo $ori_url; ?>">
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
<td><textarea name="bbld_postpage_header" rows="10" cols="50" id="bbld_postpage_header" class="large-text code"><?php echo htmlspecialchars(stripslashes(get_option('wpbb_pagepost_header'))); ?></textarea></td>
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
<td><textarea name="bbld_postpage_body" rows="10" cols="50" id="bbld_postpage_body" class="large-text code"><?php echo htmlspecialchars(stripslashes(get_option('wpbb_pagepost_body'))); ?></textarea></td>
</tr>

<tr valign="top">
<th scope="row">
	<p><strong>BbLD Post/Page Footer:</strong></p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('postpage_footer');" class="button" /></p>
</th>
<td><textarea name="bbld_postpage_footer" rows="10" cols="50" id="bbld_postpage_footer" class="large-text code"><?php echo htmlspecialchars(stripslashes(get_option('wpbb_pagepost_footer'))); ?></textarea></td>
</tr>

<tr valign="top">
<th scope="row">
	<p><strong>BbLD Sidebar Title:</strong></p>
	<p>Allowed Variables:</p>
	<p style="margin: 2px 0">- %BBLD_LIMIT%</p>
	<p><input class="button-primary" type="button" name="RestoreDefault" value="Restore Default Template" onclick="bbld_default_templates('sidebar_title');" class="button" /></p>
</th>
<td><textarea name="bbld_sidebar_title" rows="10" cols="50" id="bbld_sidebar_title" class="large-text code"><?php echo htmlspecialchars(stripslashes(get_option('wpbb_sidebar_title'))); ?></textarea></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="wpbb_save_template" id="wpbb_save_template" class="button-primary" value="<?php _e('Save Templates Change &raquo;', 'bbpress-latest-discussion'); ?>" />
</p>
</form>
</div>
<?php
}

?>
