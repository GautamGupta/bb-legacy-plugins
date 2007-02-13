<?php
/*
Plugin Name: Support Forums
Plugin URI: http://www.adityanaik.com/projects/
Description: Changes the forum to a support forum and adds functionality to mark topics resolved, not resolved or not a support question
Author: Aditya Naik, Sam Bauers
Author URI: http://www.adityanaik.com/
Version: 1.2

Version History:
1.0  	- Initial Release (Aditya Naik)
1.1  	- Use topic_resolved meta key (Aditya Naik)
		- by default the support forums are switched on (Aditya Naik)
1.2		- Integrated visual-support-forum plugin features as options in admin (Sam Bauers)
		- Added admin action to upgrade database instead of running on plugin load (Sam Bauers)
		- When default status is "unresolved" topics with no status set now show in the "unresolved" view (Sam Bauers)
		- Sticky topics that are unresolved now show in the "unresolved" view (Sam Bauers)
*/

$icon_path = str_replace(BBPATH, '', BBPLUGINDIR);

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
	global $icon_path;
	if ('support_forum_post' == $_POST['action']) {
		$support_forum_check = (isset($_POST['support_forum'])) ? "Y" : "N";
		bb_update_option('support_forum_check',$support_forum_check);
		
		$support_forum_default_resolved_status = $_POST['default_resolved_status'];
		bb_update_option('support_forum_default_resolved_status',$support_forum_default_resolved_status);
		
		$support_forum_status_icons_check = (isset($_POST['support_forum_status_icons'])) ? "Y" : "N";
		bb_update_option('support_forum_status_icons_check',$support_forum_status_icons_check);
		
		$support_forum_closed_icon_check = (isset($_POST['support_forum_closed_icon'])) ? "Y" : "N";
		bb_update_option('support_forum_closed_icon_check',$support_forum_closed_icon_check);
	} elseif ('support_forum_post_upgrade' == $_POST['action']) {
		$upgrade_alert = support_forum_upgrade_1_1();
	}
	$support_forum_default_resolved_status = support_forum_get_default_status();
	$support_forum_checked = (support_forum_check()) ? "checked=\"checked\" " : "";
	$support_forum_status_icons_checked = (support_forum_status_icons_check()) ? "checked=\"checked\" " : "";
	$support_forum_closed_icon_checked = (support_forum_closed_icon_check()) ? "checked=\"checked\" " : "";
	?>
	<h2>Support Forum Option</h2>
	<form method="post">
		<p><input type="checkbox" name="support_forum" value="Y" <?php echo $support_forum_checked;?>/> Make the forum a Support Forum, Baby!</p>
		<p>
			<select name="default_resolved_status" >
				<option value="yes" <?php if ('yes' == $support_forum_default_resolved_status) echo 'selected';?>>resolved</option>
				<option value="no" <?php if ('no' == $support_forum_default_resolved_status) echo 'selected';?>>not resolved</option>
				<option value="mu" <?php if ('mu' == $support_forum_default_resolved_status) echo 'selected';?>>not a support question</option>
			</select>
			Set the default status for topics, Cool!
		</p>
		<p>
			<input type="checkbox" name="support_forum_status_icons" value="Y" <?php echo $support_forum_status_icons_checked;?>/> Use pretty status icons on topics, Dude!
			<blockquote>
				<img src="<?php bb_option('uri'); ?><?php echo($icon_path); ?>support-forum-yes.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
				- <?php _e('resolved'); ?><br />
				<img src="<?php bb_option('uri'); ?><?php echo($icon_path); ?>support-forum-no.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
				- <?php _e('not resolved'); ?><br />
				<img src="<?php bb_option('uri'); ?><?php echo($icon_path); ?>support-forum-mu.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
				- <?php _e('not a support question'); ?>
			</blockquote>
		</p>
		<p>
			<input type="checkbox" name="support_forum_closed_icon" value="Y" <?php echo $support_forum_closed_icon_checked;?>/> Use clichéd lock icon on closed topics, Right on!
			<blockquote>
				<img src="<?php bb_option('uri'); ?><?php echo($icon_path); ?>support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
				- <?php _e('closed'); ?>
			</blockquote>
		</p>
	
		<input name="action" type="hidden" value="support_forum_post"/>
		<p class="submit"><input type="submit" name="submit" value="Submit" /></p>		
	</form>
	<hr />
	<form method="post">
		<p>
			<?php echo($upgrade_alert); ?>
		</p>
		<p>
			If you used support forum plugin version 1.0, you will need to update existing topics to work with 1.1, um... Cowabunga!
		</p>
		<input name="action" type="hidden" value="support_forum_post_upgrade"/>
		<p class="submit"><input type="submit" name="submit_upgrade" value="Update topics to version 1.1" /></p>
	</form>
	<?php
}

function support_forum_check() {
	return ("N" == bb_get_option('support_forum_check')) ? false : true;
}

function support_forum_status_icons_check() {
	return ("Y" == bb_get_option('support_forum_status_icons_check')) ? true : false;
}

function support_forum_closed_icon_check() {
	return ("Y" == bb_get_option('support_forum_closed_icon_check')) ? true : false;
}

add_action( 'bb_admin_menu_generator', 'support_forum_add_admin_page' );

if (support_forum_check()) {
	add_filter('bb_views','support_forum_add_view');

	function support_forum_add_view($views) {
		$views['unresolved'] = 'Unresolved topics';
		return $views;
	}

	add_action('topicmeta','support_forum_show_support_dropdown');

	function support_forum_show_support_dropdown() {
		global $icon_path;
?>
		<li id="resolution-flipper"><?php _e('This topic is') ?> <?php support_forum_topic_resolved(); ?></li>
<?php
		if (support_forum_closed_icon_check()) {
			global $topic;
			if ( '0' === $topic->topic_open ) {
?>
		<li><?php _e('This topic is') ?> <img src="<?php bb_option('uri') ?><?php echo($icon_path); ?>support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> <?php _e('closed'); ?></li>
<?php
			}
		}
	}

	function support_forum_topic_resolved( $yes = '', $no = '', $mu = '', $id = 0 ) {
		global $icon_path;
		global $bb_current_user, $topic;
		if ( empty($yes) )
			$yes = __('resolved');
		if ( empty($no) )
			$no = __('not resolved');
		if ( empty($mu) )
			$mu = __('not a support question');
		if ( bb_current_user_can( 'edit_topic', $topic->topic_id ) ) :
			$resolved_form  = '<form id="resolved" method="post" style="display:inline;"><div style="display:inline;">' . "\n";
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
			$status = support_forum_get_topic_resolved( $id );
			if (support_forum_status_icons_check()) {
				echo '<img src="' . bb_get_option('uri') . $icon_path . 'support-forum-' . $status . '.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> ';
			}
			echo $$status;
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
		
		if (support_forum_get_default_status() == 'no') {
			$query = "SELECT $bbdb->topics.topic_id FROM $bbdb->topics LEFT JOIN $bbdb->topicmeta ON $bbdb->topics.topic_id = $bbdb->topicmeta.topic_id AND meta_key = 'topic_resolved' WHERE meta_value = 'no' OR meta_value IS NULL";
		} else {
			$query = "SELECT topic_id FROM $bbdb->topicmeta WHERE meta_key = 'topic_resolved' AND meta_value = 'no'";
		}
		
		$topicids = $bbdb->get_col($query);
		if ($topicids) {
			$topics_in = join(',',$topicids);
			return "WHERE topic_status = 0 AND topic_open = 1 AND topic_id IN ($topics_in)";
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
	
	// Just in case you dont have the categories patch installed - that's most people BTW
	if (!function_exists('bb_is_category')) {
		function bb_is_category() {
			return false;
		}
	}
	
	if (support_forum_status_icons_check()) {
		add_filter( 'topic_title', 'support_forum_topic_title', 40);
	}
	
	function support_forum_topic_title($title) {
		global $icon_path;
		if ( is_forum() || bb_is_category() || is_front() || is_view() ) :
			if ( empty($yes) )
				$yes = __('resolved');
			if ( empty($no) )
				$no = __('not resolved');
			if ( empty($mu) )
				$mu = __('not a support question');
			// Gets the status of the topic, or the defualt if none
			$status = support_forum_get_topic_resolved( $id );
			// If status is not one of the three above or the default, then make it the default
			if (!$$status) {
				$status = support_forum_get_default_status();
			}
			$status_image = '<img src="' . bb_get_option('uri') . $icon_path . 'support-forum-' . $status . '.png" alt="[' . $$status . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />';
			$title = $status_image . $title;
		endif;
	
		return $title;
	}
	
	if (support_forum_closed_icon_check()) {
		remove_filter('topic_title', 'closed_title', 30);
		add_filter('topic_title', 'support_forum_closed_title', 30);
	}
	
	function support_forum_closed_title( $title ) {
		global $icon_path;
		if ( is_forum() || bb_is_category() || is_front() || is_view() ) {
			global $topic;
			if ( '0' === $topic->topic_open ) {
				return sprintf(__('<img src="' . bb_get_option('uri') . $icon_path . 'support-forum-closed.png" alt="[' . __('closed') . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />%s'), $title);
			}
		}
		
		return $title;
	}

}

function support_forum_upgrade_1_1() {
	global $bbdb;
	$rows = $bbdb->get_results("SELECT * FROM $bbdb->topicmeta WHERE meta_key = 'support_forum_resolved'");
	if ($rows) {
		foreach($rows as $row) :
			bb_update_topicmeta($row->topic_id, 'topic_resolved', $row->meta_value);
			bb_delete_topicmeta($row->topic_id, 'support_forum_resolved');
		endforeach;
		return __('Update performed');
	} else {
		return __('No update required');
	}
}

?>