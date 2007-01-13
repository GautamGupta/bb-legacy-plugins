<?php
/**
 * Plugin Name: Online List
 * Plugin Description: Displays a simple List of current Online users.
 * Author: Thomas Klaiber
 * Author URI: http://www.la-school.com
 * Plugin URI: http://www.la-school.com/2006/bbpress-onlinelist/
 * Version: 1.3-fix
 */
 
/**
 * Use MySQL 4.1
 */
$mysql41 = true;
/**
 * Disable if it doesn't work properly
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
 
function online_update() {	
	global $bbdb, $bb_current_user, $bb_table_prefix;
	
	if ( bb_is_user_logged_in() ) : // logged in?
		if ( mysql_version() >= "4.1" ) :
			// thanks to: chester copperpot
			$bbdb->query("INSERT INTO ".$bb_table_prefix."online (user_id, activity) VALUES ('".$bb_current_user->ID."', '".time()."') ON DUPLICATE KEY UPDATE activity=VALUES(activity)");
		else :
			if ($bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE user_id = ".$bb_current_user->ID." LIMIT 1")) :
				$bbdb->query("UPDATE ".$bb_table_prefix."online SET activity = ".time()." WHERE user_id = ".$bb_current_user->ID."");
			else :
				$bbdb->query("INSERT INTO ".$bb_table_prefix."online (user_id, activity) VALUES ('".$bb_current_user->ID."', '".time()."')");
			endif;		
		endif;				
	endif;	
}
add_action('bb_init', 'online_update');

function online_logout() {
	global $bbdb, $bb_current_user, $bb_table_prefix;
	
	$now = time() - 300; // 5 minutes ago
	if ($bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE user_id = ".$bb_current_user->ID." LIMIT 1")) :
		$bbdb->query("UPDATE ".$bb_table_prefix."online SET activity = ".$now." WHERE user_id = ".$bb_current_user->ID."");
	endif;
}
add_action('bb_user_logout', 'online_logout');

function setup_check() {
	global $bbdb, $bb_table_prefix;
	
	$bbdb->hide_errors();
	$installed = $bbdb->get_results("SELECT user_id FROM ".$bb_table_prefix."online LIMIT 1");
	
	if ( !$installed ) :
		$bbdb->query(" CREATE TABLE IF NOT EXISTS `".$bb_table_prefix."online` (
`user_id` INT( 11 ) NOT NULL ,
`activity` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `user_id` )
)");

		online_update(); // insert first data
	endif;
	$bbdb->show_errors();
}

function show_online_users() {
	global $bbdb, $bb_table_prefix;
	
	// checks if table is created, does if not
	setup_check();
	
	$now = time() - 300; // 5 minutes ago
	if ($online = $bbdb->get_results("SELECT * FROM ".$bb_table_prefix."online WHERE activity > ".$now." ORDER BY activity ASC")) :
		
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
			echo "<a href=\"".get_user_profile_link($on->user_id)."\" title=\"\">".$user->user_login."</a>$komma";
		endforeach;
	else :
		echo __('No Members around.');
	endif;	
}

function total_online_users() {
	global $bbdb, $bb_table_prefix;

	$now = time() - 300; // 5 minutes ago
	$total_online_users = $bbdb->get_var("SELECT COUNT(*) FROM ".$bb_table_prefix."online WHERE activity > ".$now."");
	
	echo $total_online_users;
}

function profile_last_online() {
	global $bbdb, $bb_table_prefix, $user_id;

	$now = time() - 300; // 5 minutes ago

	echo "<dl id='userinfo'>\n";
	echo "\t<dt>" . __('Last Online') . "</dt>\n";
	
	if ($last_online = $bbdb->get_var("SELECT activity FROM ".$bb_table_prefix."online WHERE user_id=$user_id LIMIT 1")) :
		if ($last_online > $now) :
			$last_online_since = $last_online;
		else :
			$last_online_since = $last_online + 300;
		endif;
		echo "\t<dd>" . gmdate(__('F j, Y H:i:s'), $last_online + 3600) . " (" . bb_since($last_online_since) . " ago)</dd>\n";
	else :
		echo "\t<dd>" . __('Never') . "</dd>\n";	
	endif;
	echo "</dl>\n";
}
?>