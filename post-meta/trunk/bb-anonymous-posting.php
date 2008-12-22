<?php
/*
Plugin Name: BB Anonymous Posting
Plugin URI: http://www.adityanaik.com/projects/plugins/bb-anonymous-posting/
Description: Allows anonymous users to add posts and topics
Author: Aditya Naik
Version: 2.2_ck_mod_1.2.0
Author URI: http://www.adityanaik.com/
*/

// initialization
add_action('bb_init', 'bb_anon_init');

// add anonymous role 
// if the location is bb-post page spoof user
function bb_anon_init() {
	if (bb_is_user_logged_in()) {return;}

	// before a form is shown spoof the user
	add_action('pre_post_form', 'bb_anon_spoof_user');
	// after displaying the form unspoof the user so it 
	// does not affect any of the other functionality 
	add_action('post_post_form', 'bb_anon_unspoof_user');

	global $bb_roles, $wp_roles;
	$perm_array = array (
		'write_posts' => true,
		'read' => true
	);
	if (bb_get_option('bb_anon_write_topics') == 'Y')
		$perm_array['write_topics'] = true;
		
	if (!defined( 'BACKPRESS_PATH' ) ) {  	// 1.0 workaround
		$bb_roles->add_role('anonymous', $perm_array, 'Anonymous');
	} else {
		$wp_roles->add_role('anonymous', 'Anonymous', $perm_array);    // note different order	
		$bb_roles =& $wp_roles;
	}	
		
	if ('bb-post' == bb_anon_is_bb_post()) {
		add_filter('bb_current_user_can','bb_anon_add_tag_to',10,3); 
		bb_anon_spoof_user();
	}
}

function bb_anon_is_bb_post() {	// bb_location is cached in 1.0, can't add page types bug
	$resource=array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']);
	foreach ($resource as $name ) {if (false!==strpos($name, '.php')) {return str_replace(".php","",bb_find_filename($name));}} 
	return false;	
}

function bb_anon_add_tag_to($retvalue, $capability, $args) {
global $topic, $topic_id;
if ($args[0]=="add_tag_to" && (!empty($args[1])) && (empty($topic->topic_id)) && empty($topic_id) && bb_get_option('bb_anon_write_topics') == 'Y') {return true;}
return $retvalue;
}

// remove auth function for the bb-post page
// !function_exists is used only during plugin activation
if (!function_exists('bb_auth') && 'bb-post' == bb_anon_is_bb_post()) {
	function bb_auth() {}
}

// meat of the plugin
function bb_anon_spoof_user() {
	global $bb_current_user; 
	if (empty($bb_current_user->ID)) {
		$anon_id = bb_get_option('bb_anon_user_id');
		if (!$anon_id) {
			bb_anon_activate_plugin();
			$anon_id = bb_get_option('bb_anon_user_id');
		}
		$bb_current_user = class_exists('BB_User') ? new BB_User($anon_id) : new WP_User($anon_id);
	}
}

// remove meat of the plugin
function bb_anon_unspoof_user() {
	global $bb_current_user;
	if ($bb_current_user->has_cap('anonymous')) {
		$bb_current_user = false;
	}
	
	if (is_forum() && bb_get_option('bb_anon_write_topics') != "Y") {
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
// add_action('bb_activate_plugin_' . bb_plugin_basename(__FILE__), 'bb_anon_activate_plugin');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'bb_anon_activate_plugin');

function bb_anon_activate_plugin() {
	global $bbdb;

	// _ck_ mod - don't trust bbdb insert id - also make sure not to dupe anonymous user
	$anon_id=$bbdb->get_var("SELECT ID FROM $bbdb->users WHERE user_login='anonymous' AND user_status=0 LIMIT 1");
	if (empty($anon_id)) {
	$bbdb->query("INSERT INTO $bbdb->users (user_login,user_nicename, user_registered) VALUES ('anonymous','Anonymous', '" . bb_current_time('mysql') . "')");
	$anon_id=$bbdb->get_var("SELECT ID FROM $bbdb->users WHERE user_login='anonymous' AND user_status=0 LIMIT 1");
	}
	
	$old_anon_id = bb_get_option('bb_anon_user_id');
	if (!empty($old_anon_id) && $old_anon_id!=$anon_id) {
		$bbdb->query("UPDATE $bbdb->users SET ID = $old_anon_id WHERE ID = $anon_id AND user_status=0");
		$anon_id=$old_anon_id;
	} else {		
		bb_update_option('bb_anon_user_id', $anon_id);
	}
	
	$user = class_exists('BB_User') ? new BB_User($anon_id) : new WP_User($anon_id);	// 1.0 workaround

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
	<form class="options settings" method="post">
		<fieldset>
			<div>
			<label for="bb_anon_write_topics">				
				Let Anonymous Users create&nbsp;new&nbsp;Topics 				
			</label>
			<div>
			<span class="text short">
			 Yes <input <?php if($write_topics == 'Y') echo ' checked="checked" ' ?> type="radio" name="bb_anon_write_topics" id="bb_anon_write_topics_y" value="Y"/> &nbsp;
			 No <input <?php if($write_topics != 'Y') echo ' checked="checked" ' ?> type="radio" name="bb_anon_write_topics" id="bb_anon_write_topics_n" value="N"/>
			</span>
				
			<p>By default if the plugin is enabled the users can create new posts. <br />
			Selecting yes above will let users create new topics also.</p>
			</div>
			</div>
		</fieldset>		
	       
	       
	          <input type="submit" class="submit" name="bb_anon_submit_options" value="Update Options" />
	       
	        
	</form>	
	<?php
}

add_action('bb_anon_settings_page_pre_head','bb_anon_settings_page_process');
function bb_anon_settings_page_process() {
	if(isset($_POST['bb_anon_submit_options'])) {
		$anon_id = bb_get_option('bb_anon_user_id');
		$user = class_exists('BB_User') ? new BB_User($anon_id) : new WP_User($anon_id);

		if($_POST['bb_anon_write_topics'] == Y) {
			bb_update_option('bb_anon_write_topics',"Y"); 
			//$user->add_cap('write_topics');
		} else {
			bb_update_option('bb_anon_write_topics',"N");
			//$user->remove_cap('write_topics');
		}

		$goback = add_query_arg('bb-anon-options-updated', 'true');	// fixed - refer not needed
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