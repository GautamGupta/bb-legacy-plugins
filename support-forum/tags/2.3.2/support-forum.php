<?php
/*
Plugin Name: Support forum
Plugin URI: http://bbpress.org/plugins/topic/16
Description: Changes the forum to a support forum and adds functionality to mark topics resolved, not resolved or not a support question
Author: Aditya Naik, Sam Bauers
Author URI: http://www.adityanaik.com/
Version: 2.3.2

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
2.0.3	: Make PHP4 compatible (Sam Bauers)
2.1		: Compatibility with new bb_register_views/BB_Query methods introduced in build 876 for "unresolved" view (Sam Bauers)
		  Selection of individual forums as support forums rather than the whole site (Sam Bauers)
2.1.1	: Add missing gettext calls to admin page to allow full internationalisation (Sam Bauers)
2.2		: Added the option to add a view for each individual status (Sam Bauers)
2.3		: Added the option for the topic creator to set the status on creation (Sam Bauers)
		  Added the option to allow the topic creator to change the status of the topic (Sam Bauers)
		  Tightened up the permissions so that only those who can change others topics can generally change the status (Sam Bauers)
		  Slightly better formatting of the admin page (Sam Bauers)
2.3.1	: Fixed a problem where a warning was produced if there were no settings in the database (Sam Bauers)
2.3.2	: Pass $support_forum object by reference for latest WP add_filter() and add_action() methods (Michael D. Adams)
*/


/**
 * Support forum for bbPress version 2.3.2
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
 * PHP version 4 and 5
 * 
 * ----------------------------------------------------------------------------------
 * 
 * @author    Aditya Naik <so1oonnet@gmail.com>
 * @author    Sam Bauers <sam@viveka.net.au>
 * @copyright 2007 Aditya Naik
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   2.3.2
 **/


/**
 * Wrapper class for the Support forum plugin
 *
 * @author  Sam Bauers
 * @version 2.3.2
 **/
class Support_Forum
{
	/**
	 * An array of forum ids that are support forums
	 *
	 * @var array
	 **/
	var $enabled;
	
	
	/**
	 * The default support status of new topics
	 *
	 * @var string
	 **/
	var $status;
	
	
	/**
	 * The list of available resolutions to choose from
	 *
	 * @var array
	 **/
	var $resolutions;
	
	
	/**
	 * The settings for views
	 *
	 * @var array
	 **/
	var $views;
	
	
	/**
	 * Just helps pass a variable between a couple of functions
	 *
	 * @var string
	 **/
	var $statusForGetView;
	
	
	/**
	 * The settings for icons
	 *
	 * @var array
	 **/
	var $icons;
	
	
	/**
	 * The path past the base url where the icons reside
	 *
	 * @var string
	 **/
	var $iconPath;
	
	
	/**
	 * Whether or not the topic poster can set the status
	 *
	 * @var boolean
	 **/
	var $posterSetable;
	
	
	/**
	 * Whether or not the topic poster can change the status
	 *
	 * @var boolean
	 **/
	var $posterChangeable;
	
	
	/**
	 * Retrieves settings for the plugin
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function Support_Forum()
	{
		$this->enabled = bb_get_option('support_forum_enabled');
		
		$this->correctEnabled();
		
		$this->status = bb_get_option('support_forum_default_status');
		
		$this->resolutions = array(
			'yes' => __('resolved'),
			'no'  => __('not resolved'),
			'mu'  => __('not a support question')
		);
		
		$this->getViewStatus();
		
		$this->getIconStatus();
		
		$this->iconPath = str_replace(BBPATH, '', dirname(__FILE__)) . '/';
		
		$this->posterSetable = bb_get_option('support_forum_poster_setable');
	}
	
	
	/**
	 * Returns the current active state of the plugin
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function isActive()
	{
		$this->enabled = bb_get_option('support_forum_enabled');
		
		$this->correctEnabled();
		
		if (!$this->enabled) {
			$this->enabled = array();
		}
		
		if (isset($this->enabled) && is_array($this->enabled)) {
			if (count($this->enabled) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Correct pre 2.1 support_forum_enabled option
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function correctEnabled()
	{
		if (!is_array($this->enabled)) {
			bb_delete_option('support_forum_enabled');
			$this->enabled = false;
		}
	}
	
	
	/**
	 * Returns the current default status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function getStatus()
	{
		$this->status = bb_get_option('support_forum_default_status');
		
		if (isset($this->status)) {
			return $this->status;
		} else {
			return 'mu';
		}
	}
	
	
	/**
	 * Update the current view settings
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function getViewStatus()
	{
		$this->views = bb_get_option('support_forum_views');
		
		if (!isset($this->views)) {
			$this->views = array('no' => true);
		}
	}
	
	
	/**
	 * Update the current icon settings
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function getIconStatus()
	{
		$this->icons = array(
			'status' => bb_get_option('support_forum_icons_status'),
			'closed' => bb_get_option('support_forum_icons_closed')
		);
	}
	
	
	/**
	 * Determines if the current user can change the status of a topic
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function getChangeableStatus($topic_id = 0)
	{
		if (isset($this->posterChangeable)) {
			return $this->posterChangeable;
		}
		
		if (is_topic()) {
			if (bb_current_user_can('edit_others_topics', $topic->topic_id)) {
				$this->posterChangeable = TRUE;
				return TRUE;
			}
			
			if (bb_get_option('support_forum_poster_changeable')) {
				if (!$topic_id) {
					global $topic;
				} else {
					$topic = get_topic($topic_id);
				}
				
				if ($topic->topic_poster == bb_get_current_user_info('id')) {
					$this->posterChangeable = TRUE;
					return TRUE;
				}
			}
		}
		
		$this->posterChangeable = FALSE;
		return FALSE;
	}
	
	
	/**
	 * Adds a view to bbPress
	 *
	 * @return array
	 * @author Sam Bauers
	 **/
	function addViews($views)
	{
		foreach ($this->views as $status => $enabled) {
			if ($enabled) {
				$views['support-forum-' . $status] = sprintf(__('Support topics that are %s'), $this->resolutions[$status]);
			}
		}
		return $views;
	}
	
	
	/**
	 * Adds a filter to show the unresolved topics view
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function processViews($view, $page)
	{
		global $topics;
		global $view_count;
		global $bbdb;
		
		foreach ($this->views as $status => $enabled) {
			if ($enabled) {
				if ($view == 'support-forum-' . $status)  {
					$this->statusForGetView = $status;
					add_filter('get_latest_topics_where', array(&$this, 'getView'));
					
					$topics = get_latest_topics(0, $page);
					$view_count = bb_count_last_query();
				}
			}
		}
	}
	
	
	/**
	 * Returns the "where" part of the SQL query for the unresolved topics view
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function getView($where)
	{
		global $bbdb;
		
		$query = 'SELECT ' . $bbdb->topics . '.topic_id';
		$query .= ' FROM ' . $bbdb->topics;
		$query .= ' LEFT JOIN ' . $bbdb->topicmeta;
		$query .= ' ON ' . $bbdb->topics . '.topic_id = ' . $bbdb->topicmeta . '.topic_id';
		$query .= " AND meta_key = 'topic_resolved'";
		if ($this->getStatus() == $this->statusForGetView) {
			$query .= " WHERE (meta_value = '" . $this->statusForGetView . "'";
			$query .= ' OR meta_value IS NULL)';
		} else {
			$query .= " WHERE meta_value = '" . $this->statusForGetView . "'";
		}
		$query .= ' AND ' . $bbdb->topics . '.forum_id IN (' . join(',', $this->enabled) . ')';
		
		$topic_ids = $bbdb->get_col($query);
		if ($topic_ids) {
			$topics_in = join(',', $topic_ids);
			$where = 'WHERE topic_status = 0 AND topic_open = 1 AND topic_id IN (' . $topics_in . ')';
		} else {
			$where = 'WHERE 0';
		}
		
		return $where;
	}
	
	
	/**
	 * Registers a view to bbPress
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function registerViews()
	{
		if (is_callable('bb_register_view')) { // Build 876+
			foreach ($this->views as $status => $enabled) {
				if ($enabled) {
					if ($this->getStatus() == $status) {
						$query = array(
							'sticky' => 'all',
							'meta_key' => 'topic_resolved',
							'meta_value'=> $status . ',NULL',
							'forum_id' => join(',', $this->enabled)
						);
					} else {
						$query = array(
							'sticky' => 'all',
							'meta_key' => 'topic_resolved',
							'meta_value'=> $status,
							'forum_id' => join(',', $this->enabled)
						);
					}
					
					bb_register_view('support-forum-' . $status, sprintf(__('Support topics that are %s'), $this->resolutions[$status]), $query);
				}
			}
		} else { // Build 214-875
			add_filter('bb_views', array(&$this, 'addViews'));
			add_action('bb_custom_view', array(&$this, 'processViews'),10,2);
		}
	}
	
	
	/**
	 * Runs either the status chooser or the status statement function depending on permissions
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function getStatusDisplay()
	{
		global $topic;
		if ($this->getChangeableStatus()) {
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
	function statusChooser()
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
	function statusStatement()
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
	function topicMeta()
	{
		global $topic;
		
		if (in_array($topic->forum_id, $this->enabled)) {
			echo '<li id="resolution-flipper">' . __('This topic is') . ' ';
			$this->getStatusDisplay();
			echo '</li>' . "\n";
		}
		
		if ($this->icons['closed']) {
			if ('0' === $topic->topic_open) {
				echo '<li>' . __('This topic is') . ' <img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0;" /> ' . __('closed') . '</li>' . "\n";
			}
		}
	}
	
	
	/**
	 * Returns the current support status of a given topic
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function getTopicStatus()
	{
		global $topic;
		
		if (in_array($topic->forum_id, $this->enabled)) {
			if ($topic->topic_resolved) {
				return $topic->topic_resolved;
			} else {
				return $this->getStatus();
			}
		} else {
			return false;
		}
	}
	
	
	/**
	 * Sets the status of the given topic
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function setTopicStatus($topic_id, $resolution = 'yes')
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
	 * Prints a string that is a javascript call to allow for AJAX editing of the topic support status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function AJAX_setTopicStatus()
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
	function AJAX_setTopicStatusProcess()
	{
		global $topic;
		$topic_id = (integer) @$_POST['topic_id'];
		$resolved = @$_POST['resolved'];
		
		if ($this->getChangeableStatus($topic_id)) {
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
	function addTopicClass($class)
	{
		global $topic;
		
		if ('yes' == $topic->topic_resolved && in_array($topic->forum_id, $this->enabled)) {
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
	function modifyTopicTitleStatus($title)
	{
		if (is_forum() || is_front() || is_view()) {
			$topicStatus = $this->getTopicStatus();
			
			if ($topicStatus) {
				if ($this->icons['status']) {
					$status_image = '<img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-' . $topicStatus . '.png" alt="[' . $this->resolutions[$topicStatus] . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />';
				} elseif ($topicStatus != 'mu') {
					$status_image = '[' . $this->resolutions[$topicStatus] . '] ';
				}
				$title = $status_image . $title;
			}
		}
		
		return $title;
	}
	
	
	/**
	 * Appends data to the topic title that indicates the topic closed status
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function modifyTopicTitleClosed($title)
	{
		if (is_forum() || is_front() || is_view()) {
			global $topic;
			if ('0' === $topic->topic_open) {
				return sprintf(__('<img src="' . bb_get_option('uri') . $this->iconPath . 'support-forum-closed.png" alt="[' . __('closed') . ']" style="vertical-align:top; margin-right:0.3em; width:14px; height:14px; border-width:0;" />%s'), $title);
			}
		}
		
		return $title;
	}
	
	
	/**
	 * Adds javascript that determines if the status dropdown in the topic post form is active
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function addStatusSelectModifier()
	{
		if ($this->posterSetable && (is_front() || is_tag())) {
			$j = '<script type="text/javascript">' . "\n";
			$j .= '	window.onload = function()' . "\n";
			$j .= '	{' . "\n";
			$j .= '		var watched = document.getElementById(\'forum-id\');' . "\n";
			$j .= '		var affected = document.getElementById(\'topic-support-status\');' . "\n";
			$j .= '		var forums = \'|' . join('|', $this->enabled) . '|\';' . "\n";
			$j .= '		if (forums.indexOf(\'|\' + watched.value + \'|\') === -1) {' . "\n";
			$j .= '			affected.disabled = \'disabled\';' . "\n";
			$j .= '		}' . "\n";
			$j .= '		watched.onchange = function()' . "\n";
			$j .= '		{' . "\n";
			$j .= '			var current = (watched.options[watched.selectedIndex].value);' . "\n";
			$j .= '			if (forums.indexOf(\'|\' + current + \'|\') === -1) {' . "\n";
			$j .= '				affected.disabled = \'disabled\';' . "\n";
			$j .= '			} else {' . "\n";
			$j .= '				affected.disabled = false;' . "\n";
			$j .= '			}' . "\n";
			$j .= '		}' . "\n";
			$j .= '	}' . "\n";
			$j .= '</script>' . "\n";
			
			echo $j;
		}
	}
	
	
	/**
	 * Adds a dropdown to the topic post form so that a status can be set on topic creation
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function addStatusSelectToPostForm()
	{
		if ($this->posterSetable && !is_topic()) {
			global $forum;
		
			if (!$forum || in_array($forum->forum_id, $this->enabled)) {
				$r = '<p>' . "\n";
				$r .= '<label for="topic-support-status">' . "\n";
				$r .= __('This topic is') . ':' . "\n";
				$r .= '<select name="topic_support_status" id="topic-support-status">' . "\n";
			
				foreach ($this->resolutions as $resolution => $display) {
					$selected = ($resolution == $this->status) ? ' selected="selected"' : '';
					$r .= '<option value="' . $resolution . '"' . $selected . '>' . $display . '</option>' . "\n";
				}
			
				$r .= '</select>' . "\n";
				$r .= '</label>' . "\n";
				$r .= '</p>' . "\n";
			
				echo $r;
			}
		}
	}
	
	
	/**
	 * Sets the status of the given topic on creation
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function setTopicStatusOnCreation($topic_id)
	{
		if ($this->posterSetable && isset($_POST['topic_support_status'])) {
			$this->setTopicStatus($topic_id, $_POST['topic_support_status']);
		}
	}
} // END class Support_Forum


// Initialise the class
$support_forum = new Support_Forum();


if ($support_forum->isActive()) {
	$support_forum->registerViews();
	
	add_action('topicmeta', array(&$support_forum, 'topicMeta'));
	add_action('bb_head', array(&$support_forum, 'AJAX_setTopicStatus'));
	add_action('bb_ajax_update-resolution', array(&$support_forum, 'AJAX_setTopicStatusProcess'));
	add_filter('topic_class', array(&$support_forum, 'addTopicClass'));
	add_filter('topic_title', array(&$support_forum, 'modifyTopicTitleStatus'), 40);
	
	add_action('bb_head', array(&$support_forum, 'addStatusSelectModifier'));
	add_action('post_form_pre_post', array(&$support_forum, 'addStatusSelectToPostForm'));
	add_action('bb_new_topic', array(&$support_forum, 'setTopicStatusOnCreation'));
	
	if ($support_forum->icons['closed']) {
		remove_filter('topic_title', 'closed_title', 30); // Build 371-791
		remove_filter('topic_title', 'bb_closed_title', 30); // Build 792+
		add_filter('topic_title', array(&$support_forum, 'modifyTopicTitleClosed'), 30);
	}
}


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'support_forum_admin_page_add');
add_action('bb_admin_head', 'support_forum_admin_page_head');
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
 * Adds some cute javascript to the admin head that controls the enabling and disabling of one of the checkboxes
 *
 * @return string
 * @author Sam Bauers
 **/
function support_forum_admin_page_head()
{
	$j = '<script type="text/javascript">' . "\n";
	$j .= '	window.onload = function()' . "\n";
	$j .= '	{' . "\n";
	$j .= '		var watched = document.getElementById(\'support-forum-poster-setable\');' . "\n";
	$j .= '		var affected = document.getElementById(\'support-forum-poster-changeable\');' . "\n";
	$j .= '		watched.onchange = function()' . "\n";
	$j .= '		{' . "\n";
	$j .= '			if (watched.checked) {' . "\n";
	$j .= '				affected.disabled = false;' . "\n";
	$j .= '			} else {' . "\n";
	$j .= '				affected.disabled = \'disabled\';' . "\n";
	$j .= '			}' . "\n";
	$j .= '		}' . "\n";
	$j .= '	}' . "\n";
	$j .= '</script>' . "\n";
	
	echo $j;
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
	
	// Called here to get the enabled forums after an update
	$support_forum->isActive();
	
	$support_forum_default_status = array(
		'yes' => null,
		'no'  => null,
		'mu'  => null
	);
	$support_forum_default_status[$support_forum->getStatus()] = ' selected="selected"';
	
	if (bb_get_option('support_forum_poster_setable')) {
		$support_forum_poster_setable_checked = "checked=\"checked\" ";
		$support_forum_poster_changeable_disabled = NULL;
	} else {
		$support_forum_poster_setable_checked = NULL;
		$support_forum_poster_changeable_disabled = "disabled=\"disabled\" ";
	}
	
	$support_forum_poster_changeable_checked = (bb_get_option('support_forum_poster_changeable')) ? "checked=\"checked\" " : "";
	
	$support_forum->getViewStatus();
	$support_forum_views_checked = array(
		'yes' => ($support_forum->views['yes']) ? "checked=\"checked\" " : "",
		'no'  => ($support_forum->views['no']) ? "checked=\"checked\" " : "",
		'mu'  => ($support_forum->views['mu']) ? "checked=\"checked\" " : ""
	);
	
	$support_forum->getIconStatus();
	$support_forum_icons_status_checked = ($support_forum->icons['status']) ? "checked=\"checked\" " : "";
	$support_forum_icons_closed_checked = ($support_forum->icons['closed']) ? "checked=\"checked\" " : "";
	?>
	<h2><?php _e('Support forum'); ?></h2>
	<form method="post">
<?php
	if (bb_forums('type=list&walker=BB_Walker_ForumAdminlistitems')) {
?>
		<ul id="the-list" class="list-block holder" style="margin-bottom:40px;">
			<li class="thead list-block"><div class="list-block"><?php _e('Name'); ?> &#8212; <?php _e('Description'); ?></div></li>
<?php
		while (bb_forum()) {
			$forum = $GLOBALS['forum'];
			
			$support_forum_enabled_checked[$forum->forum_id] = (in_array($forum->forum_id, $support_forum->enabled)) ? "checked=\"checked\" " : "";
			
			if ($close) {
?>
			<li <?php alt_class('forum', 'forum clear list-block'); ?>>
<?php
			}
?>
				<div class="list-block posrel">
					<div class="alignright">
						<?php _e('Enable support forum'); ?>
						<input type="checkbox" name="support_forum_enabled[]" value="<?php echo($forum->forum_id); ?>" <?php echo($support_forum_enabled_checked[$forum->forum_id]); ?>/>
					</div>
					<?php forum_name(); forum_description(array('before' => ' &#8212; ')); ?>
				</div>
<?php
			if ($close) {
?>
			</li>
<?php
			}
		}
?>
		</ul>
<?php
	}
?>
		<hr />
		<p>
			<?php _e('Set the default status for topics:'); ?>
			<select name="support_forum_default_status" >
				<option value="yes"<?php echo($support_forum_default_status['yes']); ?>><?php _e('resolved'); ?></option>
				<option value="no"<?php echo($support_forum_default_status['no']); ?>><?php _e('not resolved'); ?></option>
				<option value="mu"<?php echo($support_forum_default_status['mu']); ?>><?php _e('not a support question'); ?></option>
			</select>
		</p>
		<hr />
		<p>
			<input type="checkbox" name="support_forum_poster_setable" id="support-forum-poster-setable" value="1" <?php echo $support_forum_poster_setable_checked;?>/> <?php _e('Allow the poster of the topic to set the status on topic creation'); ?>
			<span style="display:block; line-height:18px; margin:6px 40px 13px 40px;">
				<input type="checkbox" name="support_forum_poster_changeable" id="support-forum-poster-changeable" value="1" <?php echo $support_forum_poster_changeable_checked;?><?php echo $support_forum_poster_changeable_disabled;?>/> <?php _e('Allow the poster of the topic to set the status at any time'); ?>
			</span>
		</p>
		<hr />
		<p>
			<?php _e('Choose which statuses will have a view:'); ?>
			<span style="display:block; line-height:18px; margin:6px 40px 13px 40px;">
				<input type="checkbox" name="support_forum_views[yes]" value="1"<?php echo($support_forum_views_checked['yes']); ?>>
				<?php _e('resolved'); ?><br />
				<input type="checkbox" name="support_forum_views[no]" value="1"<?php echo($support_forum_views_checked['no']); ?>>
				<?php _e('not resolved'); ?><br />
				<input type="checkbox" name="support_forum_views[mu]" value="1"<?php echo($support_forum_views_checked['mu']); ?>>
				<?php _e('not a support question'); ?>
			</span>
		</p>
		<hr />
		<p>
			<input type="checkbox" name="support_forum_icons_status" value="1" <?php echo $support_forum_icons_status_checked;?>/> <?php _e('Use resolution status icons on topics'); ?>
			<span style="display:block; line-height:18px; margin:6px 40px 13px 40px;">
<?php
	foreach ($support_forum->resolutions as $resolution => $display) {
?>
				<img src="<?php bb_option('uri'); ?><?php echo($support_forum->iconPath); ?>support-forum-<?php echo($resolution); ?>.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0; padding-top:2px;" />
				- <?php echo($display); ?><br />
<?php
	}
?>
			</span>
		</p>
		<p>
			<input type="checkbox" name="support_forum_icons_closed" value="1" <?php echo $support_forum_icons_closed_checked;?>/> <?php _e('Use lock icon on closed topics (applies to all forums)'); ?>
			<span style="display:block; line-height:18px; margin:6px 40px 13px 40px;">
				<img src="<?php bb_option('uri'); ?><?php echo($support_forum->iconPath); ?>support-forum-closed.png" alt="" style="vertical-align:top; width:14px; height:14px; border-width:0; padding-top:2px;" />
				- <?php _e('closed'); ?>
			</span>
		</p>
		<input name="action" type="hidden" value="support_forum_post"/>
		<p class="submit"><input type="submit" name="submit" value="Save support forum settings" /></p>
	</form>
	<hr />
	<form method="post">
		<p>
			<?php echo($upgrade_alert); ?>
		</p>
		<p>
			<?php _e('If you used support forum plugin version 1.0, you will need to update existing topics to work with version 2.x'); ?>
		</p>
		<input name="action" type="hidden" value="support_forum_post_upgrade"/>
		<p class="submit"><input type="submit" name="submit_upgrade" value="<?php _e('Update topics to version 2.x'); ?>" /></p>
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
			if (count($_POST['support_forum_enabled']) > 0) {
				bb_update_option('support_forum_enabled', $_POST['support_forum_enabled']);
			} else {
				bb_update_option('support_forum_enabled', 0);
			}
			
			if ($_POST['support_forum_default_status']) {
				bb_update_option('support_forum_default_status', $_POST['support_forum_default_status']);
			}
			
			if ($_POST['support_forum_poster_setable']) {
				bb_update_option('support_forum_poster_setable', 1);
			} else {
				bb_delete_option('support_forum_poster_setable');
			}
			
			if ($_POST['support_forum_poster_changeable']) {
				bb_update_option('support_forum_poster_changeable', 1);
			} else {
				bb_delete_option('support_forum_poster_changeable');
			}
			
			if ($_POST['support_forum_views']) {
				bb_update_option('support_forum_views', $_POST['support_forum_views']);
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
			
			bb_admin_notice(__('Settings saved'));
		}
	} elseif (isset($_POST['submit_upgrade'])) {
		if ($_POST['action'] == 'support_forum_post_upgrade') {
			 support_forum_upgrade_1_1();
		}
	}
}
?>