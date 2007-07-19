<?php
/*
 * Plugin Name: Move It
 * Plugin Description: This plugin allows you to move, merge & split posts and topics in bbPress. Major re-writes by _ck_
 * Author: Matteo Crippa (gh3) & _ck_
 * Author URI: http://www.ellequadro.net
 * Plugin URI: http://bbpress.org/plugins/topic/48
 * Version: 0.14
*/

/*
instructions:
	1. delete any old "moveit.php" due to file rename for update
	2. put move-it.php into my-plugins
	3. put move-it-helper.php into the bbpress root (where config.php is)
	4. edit your topic.php template and near the end put <?php move_it_topic_form(); ?> after <?php topic_move_dropdown(); ?>
	5. edit your edit-post.php template and near the end put  <? move_it_post_form(); ?>  after <?php edit_form(); ?>
	6. moderators will see the new options at the end of every topic or when when editing any user's post
*/

//  to do:	topic split routine, optional placeholders for old moved posts, old merged topic, old split topics
//  to do:	move alerts, notices to AFTER move/merge/spilt is actually done
//  notes:	favorites must be updated on topic split
	
function move_it() {		
	if ($_POST['maction']=="Move"):	// move single post to end of other existing topic
		if (isset($_POST['pid'])&&isset($_POST['toid'])) {
			$pid = $_POST['pid'];   	// post id
			$toid = $_POST['toid'];  	// destination topic id
			$fid = $_POST['fid'];  		// source topic id  - not actually needed, redundant, stored in post's data
		move_it_single_post($pid,$toid);	
				
		// wp_redirect(get_post_link($pid));    // is the bb cache preventing this from working?	
		wp_redirect(get_topic_last_post_link($fid));
		}				
	endif;
	
	if($_POST['maction']=="Merge"):	// move entire topic to end of other existing topic
		if (isset($_POST['toid'])&&isset($_POST['fid'])) {
			$toid = $_POST['toid'];	// destination topic id
			$fid = $_POST['fid'];		// source topic id
		move_it_topic_merge($fid,$toid);		
		
		wp_redirect(get_topic_last_post_link($toid));		
		}
	endif;		
		
	if($_POST['maction']=="Split"):	// move single post OR entire remaining portion of topic to NEW topic in specified forum
		// not implimented yet
		move_it_topic_split($pid,$toforumid);
	endif;	
}
add_filter('bb_head', 'move_it');

function move_it_post_form() {
if(bb_current_user_can( 'moderate' )) : 
	$pid=get_post_id(); 	$fid=get_topic_id();
	move_it_add_javascript();
	echo '<form action="" method="post">Move this post to the selected topic: 
	<select style="width:220px" name="toid" id="toid"  onMousedown="moveit_loadtopics()"><option value="0">None</option></select>
	<input type="hidden" name="pid" value="'.$pid.'"/>
	<input type="hidden" name="fid" value="'.$fid.'"/>
	<input type="hidden" name="maction" value="Move"/><input type="submit" value="Move" onClick="alert(\'Post moved!\');document.getElementById(\'post-'.$pid.'\').style.display=\'none\';">
	</form>';	
	/* echo '<form action="" method="post">split since here to tid:<input type="text" name="toid" size="4"/><input type="hidden" name="pid" value="'.$pid.'"/><input type="hidden" name="fid" value="'.$fid.'"/><input type="hidden" name="a" value="Split"/><input type="submit" value="Split" onClick="alert(\'Topic splitted!\');"></form>'; */
endif;
}

function move_it_topic_form() {
if(bb_current_user_can( 'moderate' )) : 
	$fid=get_topic_id();
	move_it_add_javascript();
	echo '<form action="" method="post">Merge this topic to selected topic: 
	<select style="width:220px" name="toid" id="toid"  onMousedown="moveit_loadtopics()"><option value="0">None</option></select>
	<input type="hidden" name="fid" value="'.$fid.'"/>
	<input type="hidden" name="maction" value="Merge"/>
	<input type="submit" value="Merge" onClick="alert(\'Topic merged!\');"></form>';
endif;
}

function move_it_add_javascript() {
echo '<scr'.'ipt type="text/javascript">
var moveit_script = document.createElement("script");
function moveit_loadtopics() {
if(!moveit_script.src) {
moveit_script.src = "'.bb_get_option( 'uri' ).'/move-it-helper.php";
moveit_script.type = "text/javascript";
moveit_script.charset = "utf-8";
document.getElementsByTagName("head")[0].appendChild(moveit_script);
}
}
function moveit_add(add_value,add_text) {     	                 
newOption = document.createElement("option");                
newOption.text = add_text;
newOption.value = add_value;
selectElement=document.getElementById("toid");
try {selectElement.add(newOption,null);}
catch (e) {selectElement.add(newOption,selectElement.length);}
}
</scr'.'ipt>';
}


function move_it_single_post($pid,$toid) {		
	global $bbdb, $bb_table_prefix;

	// pre-move updates
	
	// grab source post's topic id
	$fid = $bbdb->get_var("SELECT topic_id FROM $bbdb->posts WHERE post_id = '$pid'");
	if ($fid==$toid) {return;}  //  source and destination topic is the same, pointless - but probably should alert moderator
		
	// update post position in topic to last post +1 in destination topic
	$t = $bbdb->get_var("SELECT MAX(post_position) FROM $bbdb->posts WHERE topic_id = '$toid'");
	$t++;			
	$bbdb->query("UPDATE $bbdb->posts SET post_position = '$t' WHERE post_id = '$pid'");

	// move post to new topic
	$bbdb->query("UPDATE $bbdb->posts SET topic_id = '$toid' WHERE post_id = '$pid'");

	// post-move updates

	//  update topic post count
	$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$toid'");
	$t++;			
	$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t' WHERE topic_id = '$toid'");			
			
	// fetch new last poster and date
	$uid = $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE post_id = '$pid' LIMIT 1");
	$name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = '$uid' LIMIT 1");				
	$time = $bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE post_id = '$pid' LIMIT 1");			
	// update new last poster and date
	$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t', topic_last_poster= '$uid', topic_last_poster_name='$name', topic_last_post_id = '$pid', topic_time = '$time' WHERE topic_id='$toid'");
						
	// fetch the stats of the source topic			
	$t = $bbdb->get_var("SELECT topic_posts FROM $bbdb->topics WHERE topic_id = '$fid'");
	$t--;			
	$uid = $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE topic_id = '$fid' ORDER BY post_id DESC LIMIT 1");
	$name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = '$uid' LIMIT 1");
	$last_p = $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE topic_id = '$fid' ORDER BY post_id DESC LIMIT 1");
	$time = $bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE post_id = '$last_p' LIMIT 1");
	// downgrade the stats of the source topic			
	$bbdb->query("UPDATE $bbdb->topics SET topic_posts = '$t', topic_last_poster= '$uid', topic_last_poster_name='$name', topic_last_post_id = '$last_p', topic_time = '$time' WHERE topic_id='$fid'");
}

function move_it_topic_merge($fid,$toid) {		
	global $bbdb, $bb_table_prefix;

	// pre-merge updates

	// update post position in source topic's post to last post +1 in destination topic
	$t = $bbdb->get_var("SELECT MAX(post_position) FROM $bbdb->posts WHERE topic_id = '$toid'");
	$bbdb->query("UPDATE $bbdb->posts SET post_position=post_position+$t WHERE topic_id = '$fid'");

	// gather merge data

	$posts = $bbdb->get_var("SELECT count(post_id) FROM $bbdb->posts WHERE topic_id='$fid'");
		
	$last_src_post = $bbdb->get_var("SELECT topic_last_post_id FROM $bbdb->topics WHERE topic_id='$fid'");
		
	$last_dest_post = $bbdb->get_var("SELECT topic_last_post_id FROM $bbdb->topics WHERE topic_id='$toid'");
				
	$first_post_source = $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE topic_id = '$fid' ORDER BY post_id ASC LIMIT 1");
	
	// do merge	
						
	$bbdb->query("UPDATE $bbdb->topics SET topic_posts = topic_posts + $posts WHERE topic_id = '$toid'");
	
	$bbdb->query("UPDATE $bbdb->posts SET topic_id = '$toid' WHERE topic_id = '$fid'"); 

	// post-merge updates
		
	$bbdb->query("DELETE FROM $bbdb->topics WHERE topic_id = '$fid'");
		
	$uid = $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE post_id = '$last_src_post' LIMIT 1");
	$name = $bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID = '$uid' LIMIT 1");
				
	$time = $bbdb->get_var("SELECT post_time FROM $bbdb->posts WHERE post_id = '$last_src_post' LIMIT 1");
			
	// update stats			
			
	$bbdb->query("UPDATE $bbdb->topics SET topic_last_poster= '$uid', topic_last_poster_name='$name', topic_last_post_id = '$last_src_post', topic_time = '$time' WHERE topic_id='$toid'");
}

function move_it_topic_split($pid,$toforumid) {
	// not implimented yet
}


function move_it_rebuild_topics_table() {
global $bbdb;
// currently only rebuilds last post pointers, last poster name, last poster id  
// todo: 	rebuild entire topics table, run the built-in post/topic recount automatically beforehand

echo "\t<br>\n";
	if ( $topics = (array) $bbdb->get_results("SELECT topic_id,post_id,poster_id,post_time FROM `bb_posts` a WHERE  post_position=(SELECT MAX(post_position) FROM `bb_posts` WHERE topic_id=a.topic_id) AND post_status = '0' GROUP BY topic_id ") ) :
		echo "\t\t" . __('Calculating last posters for each topic...') . "<br />\n";		
		foreach ($topics as $t) {
			echo $t->topic_id."... ";
			$user_login=$bbdb->get_var("SELECT user_login FROM $bbdb->users WHERE ID='$t->poster_id'");
			$bbdb->query("UPDATE $bbdb->topics SET topic_time='$t->post_time',topic_last_poster= '$t->poster_id', topic_last_poster_name='$user_login', topic_last_post_id='$t->post_id' WHERE topic_id = '$t->topic_id'");
		}
	endif;
	echo "<br> \t\t" . __('Done calculating last posters.');
}

?>