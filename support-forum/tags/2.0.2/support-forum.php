<?php
/*
Plugin Name: Support forum
Plugin URI: http://www.adityanaik.com/projects/
Description: Changes the forum to a support forum and adds functionality to mark topics resolved, not resolved or not a support question
Author: Aditya Naik, Sam Bauers
Author URI: http://www.adityanaik.com/
Version: 2.0.2

Version History:
1.0		: Initial Release (Aditya Naik)
1.1		: Use topic_resolved meta key (Aditya Naik)
		  by default the support forums are switched on (Aditya Naik)
1.2		: Integrated visual-support-forum plugin features as options in admin (Sam Bauers)
		  Added admin action to upgrade database instead of running on plugin load (Sam Bauers)
		  When default status is "unresolved" topics with no status set now show in the "unresolved" view (Sam Bauers)
		  Sticky topics that are unresolved now show in the "unresolved" view (Sam Bauers)
1.2.1	: Added support for new admin menu structure introduced in build 740 (Sam Bauers)
		  Text based labels in topic lists now show again when icons not used (Sam Bauers)
2.0		: Object-orientation (Sam Bauers)
		  Made admin page more serious (Sam Bauers)
		  Added visual feedback when changing a topic's status (Sam Bauers)
		  Limited javascript addLoadEvent call to topic pages only (Sam Bauers)
		  Admin page feedback now uses bb_admin_notice() (Sam Bauers)
		  Added GPLv2 license details (Sam Bauers)
		  Added support for bb_admin_add_submenu() (Sam Bauers)
2.0.1	: Also remove topic_title filter through bb_topic_title function (Sam Bauers)
2.0.2	: Added some whitespace to clean up display of icons in topics (Sam Bauers)
*/


/**
 * Support forum for bbPress version 2.0.2
 * 
 * ----------------------------------------------------------------------------------
 * 
 * Copyright (C) 2007 Aditya Naik (so1oonnet@gmail.com)
 * Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)
 * 
 * ----------------------------------------------------------------------------------
 * 
 * LICENSE:
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * ----------------------------------------------------------------------------------
 * 
 * PHP version 5
 * 
 * ----------------------------------------------------------------------------------
 * 
 * @author    Aditya Naik <so1oonnet@gmail.com>
 * @author    Sam Bauers <sam@viveka.net.au>
 * @copyright 2007 Aditya Naik
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   2.0.2
 **/


/**
 * Wrapper class for the Support forum plugin
 *
 * @author  Sam Bauers
 * @version 1.0.1
 **/
class Support_Forum
{
	/**
	 * Whether or not the support forum is enabled
	 *
	 * @var boolean
	 **/
	private $enabled;
	
	
	/**
	 * The default support status of new topics
	 *
	 * @var string
	 **/
	private $status;
	
	
	/**
	 * The list of available resolutions to choose from
	 *
	 * @var array
	 **/
	public $resolutions;
	
	
	/**
	 * The settings for icons
	 *
	 * @var array
	 **/
	public $icons;
	
	
	/**
	 * The path past the base url where the icons reside
	 *
	 * @var string
	 **/
	public $iconPath;
	
	
	/**
	 * Retrieves settings for the plugin
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	public function __construct()
	{
		$this->enabled = bb_get_option('support_forum_enabled');
		
		$this->status = bb_get_option('support_forum_default_status');
		
		$this->resolutions = array(
			'yes' => __('resolved'),
			'no'  => __('not resolved'),
			'mu'  => __('not a support question')
		);
		
		$this->getIconStatus();
		
		$this->iconPath = str_replace(BBPATH, '', dirname(__FILE__)) . '/';
	}
	
	
	/**
	 * Returns the current active state of the plugin
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	public function isActive()
	{
		$this->enabled = bb_get_option('support_forum_enabled');
		
		if (isset($this->enabled)) {
			if ($this->enabled) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	
	/**
	 * Returns the current default status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function getStatus()
	{
		$this->status = bb_get_option('support_forum_default_status');
		
		if (isset($this->status)) {
			return $this->status;
		} else {
			return 'mu';
		}
	}
	
	
	/**
	 * Update the current icon settings
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	public function getIconStatus()
	{
		$this->icons = array(
			'status' => bb_get_option('support_forum_icons_status'),
			'closed' => bb_get_option('support_forum_icons_closed')
		);
	}
	
	
	/**
	 * Adds a view to bbPress
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	public function addView($views)
	{
		$views['unresolved'] = __('Unresolved support topics');
		return $views;
	}
	
	
	/**
	 * Runs either the status chooser or the status statement function depending on permissions
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	private function getStatusDisplay()
	{
		global $topic;
		if (bb_current_user_can('edit_topic', $topic->topic_id)) {
			$this->statusChooser();
		} else {
			$this->statusStatement();
		}
	}
	
	
	/**
	 * Prints a string of HTML which is a topic status chooser
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	private function statusChooser()
	{
		global $topic;
		
		$topicStatus = $this->getTopicStatus();
		
		if ($this->icons['status']) {
			echo ' <img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-' . $topicStatus . '.png" alt="" style="vertical-align:middle; width:14px; height:14px; border-width:0;" /> ';
		}
		
		$r  = '<form id="resolved" method="post" style="display:inline;"><div style="display:inline;">' . "\n";
		$r .= '<input type="hidden" name="action" value="support_forum_post_process" />' . "\n";
		$r .= '<input type="hidden" name="id" value="' . $topic->topic_id . '" />' . "\n";
		$r .= '<select name="resolved" id="resolvedformsel" tabindex="2">' . "\n";
		
		$topicStatus = $this->getTopicStatus();
		
		foreach ($this->resolutions as $resolution => $display) {
			$selected = ($resolution == $topicStatus) ? ' selected="selected"' : '';
			$r .= '<option value="' . $resolution . '"' . $selected . '>' . $display . '</option>' . "\n";
		}
        
		$r .= '</select>' . "\n";
		$r .= '<input type="submit" name="submit" id="resolvedformsub" value="' . __('Change') . '" />' . "\n";
		$r .= '</div>' . "\n";
		echo $r;
		bb_nonce_field('support-forum-resolve-topic_' . $topic->topic_id);
		echo "\n" . '</form>';
	}
	
	
	/**
	 * Prints a string of HTML which is a statement of the current status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	private function statusStatement()
	{
		$topicStatus = $this->getTopicStatus();
		
		if ($this->icons['status']) {
			echo '<img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-' . $topicStatus . '.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> ';
		}
		
		echo $this->resolutions[$topicStatus];
	}
	
	
	/**
	 * Prints a dropdown status selector for the topic meta area of each topic
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function topicMeta()
	{
		echo '<li id="resolution-flipper">' . __('This topic is') . ' ';
		$this->getStatusDisplay();
		echo '</li>' . "\n";
		if ($this->icons['closed']) {
			global $topic;
			if ('0' === $topic->topic_open) {
				echo '<li>' . __('This topic is') . ' <img src="' . bb_option('uri') . $this->iconPath . 'support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> ' . __('closed') . '</li>' . "\n";
			}
		}
	}
	
	
	/**
	 * Returns the current support status of a given topic
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	private function getTopicStatus()
	{
		global $topic;
		
		if ($topic->topic_resolved) {
			return $topic->topic_resolved;
		} else {
			return $this->getStatus();
		}
	}
	
	
	/**
	 * Sets the status of the given topic
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	private function setTopicStatus($topic_id, $resolution = 'yes')
	{
		global $bbdb;
		global $bb_cache;
		
		$topic_id = (integer) $topic_id;
		
		apply_filters('topic_resolution', $status, $topic_id);
		
		if (!in_array($resolution, array_keys($this->resolutions))) {
			return false;
		}
		
		$bb_cache->flush_one('topic', $topic_id);
		
		bb_update_topicmeta($topic_id, 'topic_resolved', $resolution);
		
		return true;
	}
	
	
	/**
	 * Adds a filter to show the unresolved topics view
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	public function processView($view, $page)
	{
		if ('unresolved' == $view)  {
			global $topics;
			global $view_count;
			global $bbdb;
			
			add_filter('get_latest_topics_where', array($this, 'getView'));
			
			$topics = get_latest_topics(0, $page);
			$view_count = bb_count_last_query();
		}
	}
	
	
	/**
	 * Returns the "where" cpart of the SQL query for the unresolved topics view
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function getView($where)
	{
		global $bbdb;
		
		if ($this->getStatus() == 'no') {
			$query = 'SELECT ' . $bbdb->topics . '.topic_id';
			$query .= ' FROM ' . $bbdb->topics;
			$query .= ' LEFT JOIN ' . $bbdb->topicmeta;
			$query .= ' ON ' . $bbdb->topics . '.topic_id = ' . $bbdb->topicmeta . '.topic_id';
			$query .= " AND meta_key = 'topic_resolved'";
			$query .= " WHERE meta_value = 'no'";
			$query .= ' OR meta_value IS NULL';
		} else {
			$query = 'SELECT topic_id';
			$query .= ' FROM ' . $bbdb->topicmeta;
			$query .= " WHERE meta_key = 'topic_resolved'";
			$query .= " AND meta_value = 'no'";
		}
		
		$topicids = $bbdb->get_col($query);
		if ($topicids) {
			$topics_in = join(',', $topicids);
			$where = 'WHERE topic_status = 0 AND topic_open = 1 AND topic_id IN (' . $topics_in . ')';
		} else {
			$where = 'WHERE 0';
		}
		
		return $where;
	}
	
	
	/**
	 * Prints a string that is a javascript call to allow for AJAX editing of the topic support status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function AJAX_setTopicStatus()
	{
		if (is_topic()) {
			$r = '<script type="text/javascript">' . "\n";
			$r .= '	addLoadEvent(' . "\n";
			$r .= '		function() {' . "\n";
			$r .= '			var resolvedSub = $("resolvedformsub");' . "\n";
			$r .= '			if (!resolvedSub) { return; }' . "\n";
			$r .= '			resFunc = function(e) {' . "\n";
			$r .= '				return theTopicMeta.ajaxUpdater("resolution", "resolved");' . "\n";
			$r .= '			}' . "\n";
			$r .= '			resolvedSub.onclick = resFunc;' . "\n";
			$r .= '			theTopicMeta.addComplete = function(what, where, update) {' . "\n";
			$r .= '				if (update && "resolved" == where) {' . "\n";
			$r .= '					$("resolvedformsub").onclick = resFunc;' . "\n";
			$r .= '				}' . "\n";
			$r .= '			}' . "\n";
			$r .= '		}' . "\n";
			$r .= '	);' . "\n";
			$r .= '</script>' . "\n";
			
			echo $r;
		}
	}
	
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	public function AJAX_setTopicStatusProcess()
	{
		global $topic;
		$topic_id = (integer) @$_POST['topic_id'];
		$resolved = @$_POST['resolved'];
		
		if (!bb_current_user_can( 'edit_topic', $topic_id )) {
			die('-1');
		}
		
		$topic = get_topic($topic_id);
		if (!$topic) {
			die('0');
		}
		
		if ($this->setTopicStatus($topic_id, $resolved)) {
			$topic->topic_resolved = $resolved;
			ob_start();
			echo '<li id="resolution-flipper">' . __('This topic is') . ' ';
			$this->statusChooser();
			echo '</li>';
			$data = ob_get_contents();
			ob_end_clean();
			$x = new WP_Ajax_Response(
				array(
					'what' => 'resolution',
					'id' => 'flipper',
					'data' => $data
				)
			);
			$x->send();
		}
	}
	
	
	/**
	 * Adds a class to resolved topics
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	public function addTopicClass($class)
	{
		global $topic;
		
		if ('yes' == $topic->topic_resolved) {
			$class[] = 'resolved';
		}
		
		return $class;
	}
	
	
	/**
	 * Appends data to the topic title that indicates the topic support status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function modifyTopicTitleStatus($title)
	{
		if (is_forum() || is_front() || is_view()) {
			$topicStatus = $this->getTopicStatus();
			
			if ($this->icons['status']) {
				$status_image = '<img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-' . $topicStatus . '.png" alt="[' . $this->resolutions[$topicStatus] . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />';
			} elseif ($topicStatus != 'mu') {
				$status_image = '[' . $this->resolutions[$topicStatus] . '] ';
			}
			$title = $status_image . $title;
		}
		
		return $title;
	}
	
	
	/**
	 * Appends data to the topic title that indicates the topic closed status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	public function modifyTopicTitleClosed($title)
	{
		if (is_forum() || is_front() || is_view()) {
			global $topic;
			if ('0' === $topic->topic_open) {
				return sprintf(__('<img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-closed.png" alt="[' . __('closed') . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />%s'), $title);
			}
		}
		
		return $title;
	}
} // END class Support_Forum


// Initialise the class
$support_forum = new Support_Forum();


if ($support_forum->isActive()) {
	add_filter('bb_views', array($support_forum, 'addView'));
	add_action('topicmeta', array($support_forum, 'topicMeta'));
	add_action('bb_custom_view', array($support_forum, 'processView'),10,2);
	add_action('bb_head', array($support_forum, 'AJAX_setTopicStatus'));
	add_action('bb_ajax_update-resolution', array($support_forum, 'AJAX_setTopicStatusProcess'));
	add_filter('topic_class', array($support_forum, 'addTopicClass'));
	add_filter('topic_title', array($support_forum, 'modifyTopicTitleStatus'), 40);
	
	if ($support_forum->icons['closed']) {
		remove_filter('topic_title', 'closed_title', 30); // Build 371-791
		remove_filter('topic_title', 'bb_closed_title', 30); // Build 792+
		add_filter('topic_title', array($support_forum, 'modifyTopicTitleClosed'), 30);
	}
}


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'support_forum_admin_page_add');
add_action('bb_admin-header.php', 'support_forum_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function support_forum_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Support forum'), 'use_keys', 'support_forum_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Support forum'), 'use_keys', 'support_forum_admin_page');
		if (isset($bb_submenu['plugins.php'])) { // Build 740-793
			$bb_submenu['plugins.php'][] = $submenu;
		} else { // Build 277-739
			$bb_submenu['site.php'][] = $submenu;
		}
	}
}


/**
 * Upgrades the topicmeta table data to be compatible with version 1.1+ of the plugin
 *
 * @return void
 * @author Sam Bauers
 **/
function support_forum_upgrade_1_1() {
	global $bbdb;
	$rows = $bbdb->get_results("SELECT * FROM $bbdb->topicmeta WHERE meta_key = 'support_forum_resolved'");
	if ($rows) {
		foreach($rows as $row) :
			bb_update_topicmeta($row->topic_id, 'topic_resolved', $row->meta_value);
			bb_delete_topicmeta($row->topic_id, 'support_forum_resolved');
		endforeach;
		bb_admin_notice(__('Update performed'));
	} else {
		bb_admin_notice(__('No update required'));
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return string
 * @author Sam Bauers
 **/
function support_forum_admin_page() {
	global $support_forum;
	
	$support_forum_enabled_checked = ($support_forum->isActive()) ? "checked=\"checked\" " : "";
	
	$support_forum_default_status = array(
		'yes' => null,
		'no'  => null,
		'mu'  => null
	);
	$support_forum_default_status[$support_forum->getStatus()] = ' selected="selected"';
	
	$support_forum->getIconStatus();
	$support_forum_icons_status_checked = ($support_forum->icons['status']) ? "checked=\"checked\" " : "";
	$support_forum_icons_closed_checked = ($support_forum->icons['closed']) ? "checked=\"checked\" " : "";
	?>
	<h2>Support forum</h2>
	<form method="post">
		<p><input type="checkbox" name="support_forum_enabled" value="1" <?php echo $support_forum_enabled_checked;?>/> Make the forum a support forum</p>
		<p>
			<select name="support_forum_default_status" >
				<option value="yes"<?php echo($support_forum_default_status['yes']); ?>>resolved</option>
				<option value="no"<?php echo($support_forum_default_status['no']); ?>>not resolved</option>
				<option value="mu"<?php echo($support_forum_default_status['mu']); ?>>not a support question</option>
			</select>
			Set the default status for topics
		</p>
		<p>
			<input type="checkbox" name="support_forum_icons_status" value="1" <?php echo $support_forum_icons_status_checked;?>/> Use resolution status icons on topics
			<blockquote>
<?php
	foreach ($support_forum->resolutions as $resolution => $display) {
?>
				<img src="<?php bb_option('uri'); ?><?php echo($support_forum->iconPath); ?>support-forum-<?php echo($resolution); ?>.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
				- <?php echo($display); ?><br />
<?php
	}
?>
			</blockquote>
		</p>
		<p>
			<input type="checkbox" name="support_forum_icons_closed" value="1" <?php echo $support_forum_icons_closed_checked;?>/> Use lock icon on closed topics
			<blockquote>
				<img src="<?php bb_option('uri'); ?><?php echo($support_forum->iconPath); ?>support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" />
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
			If you used support forum plugin version 1.0, you will need to update existing topics to work with 2.0
		</p>
		<input name="action" type="hidden" value="support_forum_post_upgrade"/>
		<p class="submit"><input type="submit" name="submit_upgrade" value="Update topics to version 1.1" /></p>
	</form>
<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 **/
function support_forum_admin_page_process()
{
	if (isset($_POST['submit'])) {
		if ($_POST['action'] == 'support_forum_post') {
			if ($_POST['support_forum_enabled']) {
				bb_update_option('support_forum_enabled', 1);
			} else {
				bb_update_option('support_forum_enabled', 0);
			}
			
			if ($_POST['support_forum_default_status']) {
				bb_update_option('support_forum_default_status', $_POST['support_forum_default_status']);
			}
			
			if ($_POST['support_forum_icons_status']) {
				bb_update_option('support_forum_icons_status', 1);
			} else {
				bb_delete_option('support_forum_icons_status');
			}
			
			if ($_POST['support_forum_icons_closed']) {
				bb_update_option('support_forum_icons_closed', 1);
			} else {
				bb_delete_option('support_forum_icons_closed');
			}
			
			bb_admin_notice(__('Options updated'));
		}
	} elseif (isset($_POST['submit_upgrade'])) {
		if ($_POST['action'] == 'support_forum_post_upgrade') {
			 support_forum_upgrade_1_1();
		}
	}
}
?>