<?php
/*
 * Plugin Name: Move It
 * Plugin Description: This plugin allow you to move single posts in your topics
 * Author: Matteo Crippa
 * Author URI: http://www.ellequadro.net
 * Plugin URI: http://www.ellequadro.net/download/
 * Version: 0.11
*/
	
function move()
{
	global $bbdb, $bb_table_prefix;
		

	if(isset($_POST['pid'])&&isset($_POST['toid']))
	{
		$pid = $_POST['pid'];
		$toid = $_POST['toid'];
		$fid = $_POST['fid'];
	
		// Topic moved
		
		$bbdb->query("UPDATE $bbdb->posts SET topic_id = '$toid' WHERE post_id = '$pid'");
	
		// Upgrade the infos of the destination topic
		
		$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$toid'");
		$t++;
		
		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t' WHERE topic_id = '$toid'");
		
		$uid = $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE post_id = '$pid' LIMIT 1");
		$name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = '$uid' LIMIT 1");
			
		$time = $bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE post_id = '$pid' LIMIT 1");
		
		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t', topic_last_poster= '$uid', topic_last_poster_name='$name', topic_last_post_id = '$pid', topic_time = '$time' WHERE topic_id='$toid'");
		
		
		// Upgrade the infos of the origin topic
		
		$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$fid'");
		$t--;
		
		$uid = $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE topic_id = '$fid' ORDER BY post_id DESC LIMIT 1");
		$name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = '$uid' LIMIT 1");
		$last_p = $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE topic_id = '$fid' ORDER BY post_id DESC LIMIT 1");
		$time = $bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE post_id = '$last_p' LIMIT 1");

		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t', topic_last_poster= '$uid', topic_last_poster_name='$name', topic_last_post_id = '$last_p', topic_time = '$time' WHERE topic_id='$fid'");

	}
	
	
	
}

add_filter('bb_head', 'move');


function moveIt($pid,$fid)
{
	if(is_topic()):

		echo '<form action="" method="post">move to tid:<input type="text" name="toid" size="4"/><input type="hidden" name="pid" value="'.$pid.'"/><input type="hidden" name="fid" value="'.$fid.'"/><input type="submit" value="move" onClick="alert(\'Post moved!\');document.getElementById(\'post-'.$pid.'\').style.display=\'none\';"></form>';

	endif;
}
?>

