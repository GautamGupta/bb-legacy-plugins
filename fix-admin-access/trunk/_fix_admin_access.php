<?php
/*
Plugin Name: Fix Admin Access
Description:  restores keymaster access to control panel on bbPress and/or WordPress integration. This is an auto-load plugin and must have a leading underscore "_". Put into your plugin directory, access bbPress and/or WordPress once and then delete it from the directory.
Plugin URI:  http://bbpress.org/plugins/topic/86
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.0.2

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   This is an auto-load plugin and must have a leading underscore "_". Put into your plugin directory, access bbPress and/or WordPress once and then delete it from the directory.

Warning: This plugin assumes your keymaster is user id #1. It is rare but possible you are no longer user #1 if you have deleted your original keymaster. In which case this won't work.

*/

function fix_admin_access() {
global $bb,$bbdb,$wpdb; if (isset($bbdb)) {$db=$bbdb;} else {$db=$wpdb;}
if (empty($bbdb->prefix)) {$prefix="bb_";} else {$prefix=$bbdb->prefix;}
if (empty($bb->wp_table_prefix)) {$wp_prefix="wp_";} else {$wp_prefix=$bb->wp_table_prefix;}
$meta=array(
$prefix.'capabilities' => 'a:1:{s:9:"keymaster";b:1;}',
$wp_prefix.'capabilities' => 'a:1:{s:13:"administrator";b:1;}',
$wp_prefix.'user_level' =>  '10'
); 
if ($db) {
foreach ($meta as $key => $value) :
	if ($db->get_var("SELECT meta_value FROM $db->usermeta WHERE meta_key = '$key' AND user_id = '1' LIMIT 1"))
	            {$db->query("UPDATE $db->usermeta SET meta_value = '$value' WHERE user_id = '1' AND meta_key = '$key' LIMIT 1");}
     	   else {$db->query("INSERT INTO $db->usermeta  (user_id, meta_key, meta_value)  VALUES ('1', '$key', '$value')");}
endforeach;
} 
}
add_action('bb_init','fix_admin_access',200);
add_action('init','fix_admin_access',200);

?>