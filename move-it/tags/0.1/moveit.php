<?php
	
/*
 * Plugin Name: Move It
 * Plugin Description: This plugin allow you to move single posts in your topics
 * Author: Matteo Crippa
 * Author URI: http://www.ellequadro.net
 * Plugin URI: http://www.ellequadro.net/download/
 * Version: 0.1
*/	

function move()
{
	global $bbdb, $bb_table_prefix;
		

	if(isset($_POST['pid'])&&isset($_POST['toid']))
	{
		$pid = $_POST['pid'];
		$toid = $_POST['toid'];
		$fid = $_POST['fid'];
	
		$bbdb->query("UPDATE $bbdb->posts SET topic_id = '$toid' WHERE post_id = '$pid'");
		
		$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$fid'");
		$t--;
		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t' WHERE topic_id = '$fid'");
		
		$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$toid'");
		$t++;
		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t' WHERE topic_id = '$toid'");

	}
}

add_filter('bb_head', 'move');


function moveIt($pid,$fid)
{
	if(is_topic()):

		echo "<form action='' method='post'>move to tid:<input type='text' name='toid' size='4'/><input type='hidden' name='pid' value='".$pid."'/><input type='hidden' name='fid' value='".$fid."'/><input type='submit' value='move'></form>";

	endif;
}
?>

