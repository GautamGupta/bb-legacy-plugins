<?php
/*
Plugin Name: Support Forums
Plugin URI: http://www.adityanaik.com/projects/
Description: Changes the forum to a support and adds functionality to mark topics resolved, not resolved or not a support question
Author: Aditya Naik
Version: 1.0
Author URI: http://www.adityanaik.com/

Description: The plugin gives option to convert a forum installation into a support forum where the users can mark
the topics as resolved or not resolved. The administrator can also set the default status of the topics.

Please Note: the plugin will needed after ticket #496 is implemented and released.

Install Instructions:
- If you don't have a /my-plugins/ directory in your bbpress installaltion, create it on the same level as config.php.

Version History:
1.0  	- Initial Release
1.1  	- Use topic_resolved meta key
		- by default the support forums are switched on
*/

function support_forum_add_admin_page() {
	global $bb_submenu;
	$bb_submenu['site.php'][] = array(__('Support Forum'), 'use_keys', 'support_forum_admin_page');
}

function support_forum_get_default_status() {
	if(bb_get_option('support_forum_default_resolved_status')) 
		return bb_get_option('support_forum_default_resolved_status'); 
	else 
		return 'mu';
}

function support_forum_admin_page() {
	if ('support_forum_post' == $_POST['action']) {
		$support_forum_check = (isset($_POST['support_forum'])) ? "Y" : "N";
		bb_update_option('support_forum_check',$support_forum_check);

		$support_forum_default_resolved_status = $_POST['default_resolved_status'];
		bb_update_option('support_forum_default_resolved_status',$support_forum_default_resolved_status);
	}
	$support_forum_default_resolved_status = support_forum_get_default_status();
	$support_forum_check = (support_forum_check()) ? "checked " : "";
	?>
	<h2>Support Forum Option</h2>
	<form method="post">
		<p><input type="checkbox" name="support_forum" value="Y" <?php echo $support_forum_check;?>/> Make the forum a Support Forum, Baby!</p>
		<p>
			<select name="default_resolved_status" >
				<option value="yes" <?php if ('yes' == $support_forum_default_resolved_status) echo 'selected';?>>resolved</option>
				<option value="no" <?php if ('no' == $support_forum_default_resolved_status) echo 'selected';?>>not resolved</option>
				<option value="mu" <?php if ('mu' == $support_forum_default_resolved_status) echo 'selected';?>>not a support question</option>
			</select>
		</p>
	
		<input name="action" type="hidden" value="support_forum_post"/>
		<p class="submit"><input type="submit" name="submit" value="Submit" /></p>		
	</form>
	<?php
}

function support_forum_check() {
	return ("N" == bb_get_option('support_forum_check')) ? false : true;
}

add_action( 'bb_admin_menu_generator', 'support_forum_add_admin_page' );
add_action( 'bb_plugins_loaded', 'upgrade_1_1' );

if (support_forum_check()) {


	add_filter('bb_views','support_forum_add_view');

	function support_forum_add_view($views) {
		$views['unresolved'] = 'Unresolved topics';
		return $views;
	}

	add_action('topicmeta','support_forum_show_support_dropdown');

	function support_forum_show_support_dropdown() {
		?>
		<li id="resolution-flipper"><?php _e('This topic is') ?> <?php support_forum_topic_resolved(); ?></li>
		<?php
	}

	function support_forum_topic_resolved( $yes = '', $no = '', $mu = '', $id = 0 ) {
		global $bb_current_user, $topic;
		if ( empty($yes) )
			$yes = __('resolved');
		if ( empty($no) )
			$no = __('not resolved');
		if ( empty($mu) )
			$mu = __('not a support question');
		if ( bb_current_user_can( 'edit_topic', $topic->topic_id ) ) :
			$resolved_form  = '<form id="resolved" method="post" ><div>' . "\n";
			$resolved_form .= '<input type="hidden" name="action" value="support_forum_post_process" />' . "\n";
			$resolved_form .= '<input type="hidden" name="id" value="' . $topic->topic_id . "\" />\n";
			$resolved_form .= '<select name="resolved" id="resolvedformsel" tabindex="2">' . "\n";

			$cases = array( 'yes', 'no', 'mu' );
			$resolved = support_forum_get_topic_resolved( $id );

			foreach ( $cases as $case ) {
				$selected = ( $case == $resolved ) ? ' selected="selected"' : '';
				$resolved_form .= "<option value=\"$case\"$selected>${$case}</option>\n";
			}

			$resolved_form .= "</select>\n";
			$resolved_form .= '<input type="submit" name="submit" id="resolvedformsub" value="'. __('Change') .'" />' . "\n</div>";
			echo $resolved_form;
			bb_nonce_field( 'support-forum-resolve-topic_' . $topic->topic_id );
			echo "\n</form>";
		else:
			switch ( support_forum_get_topic_resolved( $id ) ) {
				case 'yes' : echo $yes; break;
				case 'no'  : echo $no;  break;
				case 'mu'  : echo $mu;  break;
			}
		endif;
	}

	function support_forum_get_topic_resolved( $id = 0 ) {
		global $topic;
		if ( $id )
			$topic = get_topic( $id );
		return ($topic->topic_resolved) ? $topic->topic_resolved : support_forum_get_default_status();
	}

	function support_forum_resolve_topic( $topic_id, $resolved = 'yes' ) {
		global $bbdb, $bb_cache;
		$topic_id = (int) $topic_id;
		apply_filters( 'topic_resolution', $resolved, $topic_id );
		if ( ! in_array($resolved, array('yes', 'no', 'mu')) )
			return false;

		$bb_cache->flush_one( 'topic', $topic_id );

		bb_update_topicmeta( $topic_id, 'topic_resolved', $resolved );

		return true;
	}

	add_action('bb_custom_view','support_forum_view_process',10,2);

	function support_forum_view_process($view, $page) {
		if ('unresolved' == $view)  {
			global $topics, $view_count, $bbdb;

			add_filter('get_latest_topics_where','support_forums_get_latest_topics_where_unresolved');

			$topics = get_latest_topics( 0, $page);
			$view_count = bb_count_last_query();
		}
	}

	function support_forums_get_latest_topics_where_unresolved($where){
		global $bbdb;

		$topicids = $bbdb->get_col("SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key = 'topic_resolved' AND meta_value = 'no'");
		if ($topicids) {
			$topics_in = join(',',$topicids);
			return $where . " AND topic_id IN ($topics_in)";
		} else {
			return "WHERE 0";
		}

		return $where;
	}

	add_action('bb_head','support_forum_jsfunction');
	function support_forum_jsfunction() {
		?>
		<script type="text/javascript">
		addLoadEvent( function() { // TopicMeta

			var resolvedSub = $('resolvedformsub');
			if ( !resolvedSub )
				return;
			resFunc = function(e) { return theTopicMeta.ajaxUpdater( 'resolution', 'resolved' ); }
			resolvedSub.onclick = resFunc;
			theTopicMeta.addComplete = function(what, where, update) {
				if ( update && 'resolved' == where )
					$('resolvedformsub').onclick = resFunc;
			}
		} );
		</script>
		<?php
	}

	add_action( 'bb_ajax_update-resolution','support_forum_ajax_post_process');
	
	function support_forum_ajax_post_process() {
		global $topic;
		$topic_id = (int) @$_POST['topic_id'];
		$resolved = @$_POST['resolved'];

		if ( !bb_current_user_can( 'edit_topic', $topic_id ) )
			die('-1');

		$topic = get_topic( $topic_id );
		if ( !$topic )
			die('0');

		if ( support_forum_resolve_topic( $topic_id, $resolved ) ) {
			$topic->topic_resolved = $resolved;
			ob_start();
				echo '<li id="resolution-flipper">' . __('This topic is') . ' ';
				support_forum_topic_resolved();
				echo '</li>';
			$data = ob_get_contents();
			ob_end_clean();
			$x = new WP_Ajax_Response( array(
				'what' => 'resolution',
				'id' => 'flipper',
				'data' => $data
			) );
			$x->send();
		}
	}

	add_filter( 'topic_class','support_forum_topic_class');

	function support_forum_topic_class($class) {

		global $topic;

		if ( 'yes' == $topic->topic_resolved )
			$class[] = 'resolved';

		return $class;

	}

}

function upgrade_1_1() {
	global $bbdb;

	$rows = $bbdb->get_results("SELECT * FROM $bbdb->topicmeta WHERE meta_key = 'support_forum_resolved'");
	if ($rows) :
		foreach($rows as $row) :
			bb_update_topicmeta($row->topic_id, 'topic_resolved', $row->meta_value);
			bb_delete_topicmeta($row->topic_id, 'support_forum_resolved');
		endforeach;
	endif;

}

?>
