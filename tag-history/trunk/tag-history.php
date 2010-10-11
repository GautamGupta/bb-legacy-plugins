<?php
/*
Plugin Name: Tag History
Plugin URI: http://bbpress.org/plugins/topic/tag-history
Description:  Helps administrators figure out who tagged what, when, by exploring tagging history.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.4
*/

if (defined('BB_IS_ADMIN') && BB_IS_ADMIN) { 
	add_action( 'bb_admin_menu_generator', 'tag_history_admin');
	function tag_history_admin() {global $bb_menu, $bb_submenu, $bb_registered_plugin_callbacks; $bb_registered_plugin_callbacks[]='tag_history';
	if (defined('BACKPRESS_PATH') && empty($bb_menu[165]))  {$bb_menu[165] = array( __('Manage'),'moderate','content.php', 'bb-menu-manage' ); $bb_submenu['content.php']=array('');}	
	$bb_submenu['content.php']=array_merge(array_slice($bb_submenu['content.php'],0,2), array('tags'=>array(_('Tags'), 'administrate','tag_history')),array_slice($bb_submenu['content.php'],2));}
	if (isset($_GET['plugin']) && $_GET['plugin']=="tag_history") {require_once('tag-history-admin.php');} 	
}
if (defined('BACKPRESS_PATH') && strpos($_SERVER['REQUEST_URI'],'bb-admin/content.php')) {header('Location: '.str_replace('content.php','',$_SERVER['REQUEST_URI'])); exit;}

?>