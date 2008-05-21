<?php
/*
Plugin Name: BB Anonymous Posting
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-anonymous-posting/
Description: Allows anonymous users to add post
Author: Aditya Naik
Version: 2.0
Author URI: http://www.adityanaik.com/

*/

// before a form is shown spoof the user
add_action('pre_post_form', 'bb_anon_spoof_user');
// after displaying the form unspoof the user so it 
// does not affect any of the other functionality 
add_action('post_post_form', 'bb_anon_unspoof_user');
// initialization
add_action('bb_init', 'bb_anon_init');

// add a location for the bb-post page
add_filter('bb_get_location', 'bb_anon_get_bb_post', 10, 2);
function bb_anon_get_bb_post($loc = '', $file = '') {
	if (bb_find_filename($file) == 'bb-post.php')
		return 'bb-post';
}

// initialization
// add anonymous role 
// if the location is bb-post page spoof user
function bb_anon_init() {
	global $bb_roles;
	$perm_array = array (
		'write_posts' => true,
		'read' => true
	);
	
	if (bb_get_option('bb_anon_write_topics') == 'Y')
		$perm_array['write_topics'] = true;
	
	$bb_roles->add_role('anonymous', $perm_array, 'Anonymous');
	if ('bb-post' == bb_get_location())
		bb_anon_spoof_user();
}

// remove auth function for the bb-post page
// !function_exists is used only during plugin activation
if (!function_exists('bb_auth') && 'bb-post' == bb_get_location()) {
	function bb_auth() {}
}

// meat of the plugin
function bb_anon_spoof_user() {
	global $bb_current_user;
	if (!$bb_current_user) {
		$anon_id = bb_get_option('bb_anon_user_id');
		$bb_current_user = new BB_User($anon_id);
	}
}

// remove meat of the plugin
function bb_anon_unspoof_user() {
	global $bb_current_user, $bb_roles;
	if ($bb_current_user->has_cap('anonymous')) {
		$bb_current_user = false;
	}
	
	if (!bb_current_user_can('write_topics')) {
		echo '<p>';
		printf(__('You must <a href="%s">log in</a> to post.'), attribute_escape( bb_get_option('uri') . 'bb-login.php' ));
		echo '</p>';
	}		
}

// activate plugin 
// create a anonymous user
//
// if the option for the anonmous user id is set, set the user id to that value 
// this is to keep the user id consitent if you deactivate and activate it a couple of times
//
// add anonymous roles
add_action('bb_activate_plugin_' . bb_plugin_basename(__FILE__), 'bb_anon_activate_plugin');
function bb_anon_activate_plugin() {
	global $bbdb;

	$bbdb->query("INSERT INTO $bbdb->users (user_login,user_nicename, user_registered) VALUES ('anonymous','Anonymous', '" . bb_current_time('mysql') . "')");

	if ($anon_id = bb_get_option('bb_anon_user_id')) {
		$bbdb->query("UPDATE $bbdb->users SET ID = $anon_id where ID = " . $bbdb->insert_id);
	} else {
		$anon_id = $bbdb->insert_id;
		bb_update_option('bb_anon_user_id', $anon_id);
	}

	$user = new BB_User($anon_id);

	$user->add_role('anonymous');
	$user->remove_role('member');
	$user->add_cap('anonymous');
	$user->remove_cap('member');
}

// remove user and meta data on deactivation
// do note remove the option in case you activate the plugin again later
add_action('bb_deactivate_plugin_' . bb_plugin_basename(__FILE__), 'bb_anon_deactivate_plugin');
function bb_anon_deactivate_plugin() {
	global $bbdb;
	$anon_id = bb_get_option('bb_anon_user_id');
	$bbdb->query("DELETE FROM $bbdb->users WHERE ID = '$anon_id'");
	$bbdb->query("DELETE FROM $bbdb->usermeta WHERE user_id = '$anon_id'");
}

// fixes the frontpage link to add new post
add_filter('new_topic_url', 'bb_anon_filter_new_topic_url');
function bb_anon_filter_new_topic_url($url) {
	if (is_front() && bb_get_option('bb_anon_write_topics') == "Y")
		$url = add_query_arg('new', '1', bb_get_option('uri'));
	return $url;
}

// this fixes the display name and title for the anonymous user on the topic page
add_filter('get_post_author_id', 'bb_anon_filter_poster_id');
function bb_anon_filter_poster_id($poster_id) {
	if ($poster_id == bb_get_option('bb_anon_user_id'))
		return 0;
	else
		return $poster_id;
}

// this fixes the display names for topic last poster or topic author
add_filter('topic_last_poster','bb_anon_filter_poster',10,2);
add_filter('get_topic_author','bb_anon_filter_poster',10,2);
function bb_anon_filter_poster($last_poster, $poster_id) {
	if ($poster_id == bb_get_option('bb_anon_user_id'))
		return __('Anonymous');
	else
		return $last_poster;
}

// admin functionality
if (!BB_IS_ADMIN) 
	return;


add_action( 'bb_admin_menu_generator', 'bb_anon_add_admin_page' );
function bb_anon_add_admin_page() {
	bb_admin_add_submenu(__('Anonymous Posting'), 'use_keys', 'bb_anon_settings_page', 'options-general.php');
}

function bb_anon_settings_page() {
	
	$write_topics = bb_get_option('bb_anon_write_topics');
	?>
	<h2>Registration Settings</h2>
	<form class="options" method="post">
		<fieldset>
			<label for="">
				Let Anonymous Users create new Topics 
			</label>
			<div>
				Yes <input <?php if($write_topics == 'Y') echo ' checked="checked" ' ?> type="radio" name="bb_anon_write_topics" id="bb_anon_write_topics_y" value="Y"/>
				No <input <?php if($write_topics != 'Y') echo ' checked="checked" ' ?> type="radio" name="bb_anon_write_topics" id="bb_anon_write_topics_n" value="N"/>
				<p>By default if the plugin is enabled the users can create new posts. Selecting yes above will let users create new topics also.</p>
			</div>
	       	<p class="submit">
	          <input type="submit" name="bb_anon_submit_options" value="Update Options" />
	        </p>
	        </fieldset>
	</form>	
	<?php
}

add_action('bb_anon_settings_page_pre_head','bb_anon_settings_page_process');
function bb_anon_settings_page_process() {
	if(isset($_POST['bb_anon_submit_options'])) {
		$anon_id = bb_get_option('bb_anon_user_id');
		$user = new BB_User($anon_id);

		if($_POST['bb_anon_write_topics'] == Y) {
			bb_update_option('bb_anon_write_topics',"Y"); 
			//$user->add_cap('write_topics');
		} else {
			bb_update_option('bb_anon_write_topics',"N");
			//$user->remove_cap('write_topics');
		}

		$goback = add_query_arg('bb-anon-options-updated', 'true', wp_get_referer());
		bb_safe_redirect($goback);
	}
	
	if (isset($_GET['bb-anon-options-updated'])) {
		bb_admin_notice( __('Options Updated.') );
	}
}

add_action('bb_admin-header.php','bb_anon_user_filter');
function bb_anon_user_filter() {
	global $bb_user_search;
	
	if($bb_user_search)
	foreach ( $bb_user_search->results as $key => $user_id ) {
		if ($user_id == bb_get_option('bb_anon_user_id')) {
			unset($bb_user_search->results[$key]);
			$bb_user_search->total_users_for_query = $bb_user_search->total_users_for_query -1; 
			break;
		}
	}
}
?>
