<?php
/**
 * Plugin Name: Simple Onlinelist
 * Plugin Description: Displays a simple list of current online users.
 * Author: Thomas Klaiber
 * Author URI: http://thomasklaiber.com
 * Plugin URI: http://thomasklaiber.com/bbpress/
 * Version: 1.5
 */
 
/**
 * Use MySQL 4.1
 */
$mysql41 = true;
/**
 * Disable if it doesn't work properly
 */


 
function online_update() {	
	global $bbdb, $bb_current_user, $bb_table_prefix;
	
	// So noone talks about that bad install-error
	$bbdb->hide_errors();
	
	if ( bb_is_user_logged_in() ) : // logged in?
		$now = bb_current_time('mysql');
		if ( mysql_version() >= "4.1" ) :
			// thanks to: chester copperpot
			$bbdb->query("INSERT INTO ".$bb_table_prefix."online (user_id, activity) VALUES ('".$bb_current_user->ID."', '".$now."') ON DUPLICATE KEY UPDATE activity=VALUES(activity)");
		else :
			if ($bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE user_id = ".$bb_current_user->ID." LIMIT 1")) :
				$bbdb->query("UPDATE ".$bb_table_prefix."online SET activity = '".$now."' WHERE user_id = ".$bb_current_user->ID."");
			else :
				$bbdb->query("INSERT INTO ".$bb_table_prefix."online (user_id, activity) VALUES ('".$bb_current_user->ID."', '".$now."')");
			endif;	
		endif;				
	endif;	
	
	$bbdb->show_errors();
}
add_action('bb_init', 'online_update');

function online_logout() {
	global $bbdb, $bb_current_user, $bb_table_prefix;
	
	$now = get_online_timeout_time();
	$now = date('Y-m-d H:i:s', $now);
	
	if ($bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE user_id = ".$bb_current_user->ID." LIMIT 1")) :
		$bbdb->query("UPDATE ".$bb_table_prefix."online SET activity = '".$now."' WHERE user_id = ".$bb_current_user->ID."");
	endif;
}
add_action('bb_user_logout', 'online_logout');

function setup_check() {
	global $bbdb, $bb_table_prefix;
	
	$bbdb->hide_errors();
	
	$installed = $bbdb->get_results("SELECT ghost FROM ".$bb_table_prefix."online LIMIT 1");
	$installed2 = $bbdb->get_results("SELECT user_id FROM ".$bb_table_prefix."online LIMIT 1");
	
	if ( !$installed ) :
		if ( !$installed2 ) : // new install?
			$bbdb->query("CREATE TABLE IF NOT EXISTS `".$bb_table_prefix."online` (
	  `user_id` int(11) NOT NULL default '0',
	  `activity` datetime NOT NULL default '0000-00-00 00:00:00',
	  `ghost` tinyint(1) NOT NULL default '0',
	  PRIMARY KEY  (`user_id`)
	)");
		else : // or only update?
			$bbdb->query("ALTER TABLE `".$bb_table_prefix."online` ADD `ghost` TINYINT(1) DEFAULT '1' NOT NULL");
			$bbdb->query("ALTER TABLE `".$bb_table_prefix."online` CHANGE `activity` `activity` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
		endif;

		online_update(); // insert first data
	endif;
	$bbdb->show_errors();
}

function show_online_users() {
	global $bbdb, $bb_table_prefix;
	
	// checks if table is created, does if not
	setup_check();
	
	$now = get_online_timeout_time();
	
	if ($online = $bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE activity > FROM_UNIXTIME(".$now.") ORDER BY activity ASC")) :
		
		$counter = 0;
		$numrows = $bbdb->num_rows;	
		
		if ($numrows == 1) :
			$isare = __(' Member is');
		else :
			$isare = __(' Members are');
		endif;
		
		printf(__('%1$s %2$s online.'), $numrows, $isare);
		echo "<br />";
		
		foreach ($online as $on) :
			// get commas
			$counter++;
			if ( $numrows != 1 && $numrows > $counter ) :
				$komma = ", ";
			else :
				$komma = " ";
			endif;
			
			$user = bb_get_user($on->user_id);
			echo "<a href=\"".get_user_profile_link($on->user_id)."\" title=\"\">".(empty($user->display_name) ? $user->user_login : $user->display_name)."</a>$komma";
		endforeach;
	else :
		echo __('No Members around.');
	endif;	
}

function profile_last_online() {
	global $bbdb, $bb_table_prefix, $user_id;

	$now = get_online_timeout_time();

	echo "<dl id='userinfo'>\n";
	echo "\t<dt>" . __('Last Online') . "</dt>\n";
	
	if ($last_online = $bbdb->get_var("SELECT activity FROM ".$bb_table_prefix."online WHERE user_id=$user_id LIMIT 1")) :
		if (strtotime($last_online) > $now) :
			$last_online_since = $last_online;
		else :
			$last_online_since = strtotime($last_online) + 300;
			$last_online_since = date('Y-m-d H:i:s', $last_online_since);
		endif;
		echo "\t<dd>" . offset_online_time($last_online_since) . " (" . bb_since($last_online_since) . " ago)</dd>\n";
	else :
		echo "\t<dd>" . __('Never') . "</dd>\n";	
	endif;
	echo "</dl>\n";
}

/**
 * little helper functions
 */

function mysql_version() {
	global $bbdb;
	
	if ($mysql41) :	
		$a = mysql_get_server_info();
		$b = substr($a, 0, strrpos($a, "."));
		return $b;
	else :
		return "4.0";
	endif;
}

function offset_online_time($last_online) {
	return apply_filters( 'offset_online_time', $last_online);
}
add_filter('offset_online_time', 'bb_offset_time');

function get_online_timeout_time( $timeout = '300' ) {
	$now = bb_current_time('mysql');
	$now = strtotime( $now ) - $timeout;
	return $now;
}
?>