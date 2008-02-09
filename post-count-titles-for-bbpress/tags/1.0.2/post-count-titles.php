<?php
/*
Plugin Name: Post count titles
Plugin URI: http://bbpress.org/plugins/topic/50
Description: Adds customisable titles for users based on their post count
Author: Sam Bauers
Version: 1.0.2
Author URI: 

Version History:
1.0 	: Initial Release
1.0.1	: Made PHP4 compatible
1.0.2	: Check that the titles are actually there before transforming them
*/


/**
 * Post count titles for bbPress version 1.0.2
 * 
 * ----------------------------------------------------------------------------------
 * 
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
 * @author    Sam Bauers <sam@viveka.net.au>
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   1.0.2
 **/


/**
 * Container class for Post count titles
 *
 * @author  Sam Bauers
 * @version 1.0.2
 **/
class Post_Count_Titles
{
	/**
	 * The current version of the plugin
	 *
	 * @var string
	 **/
	var $version = '1.0.2';
	
	
	/**
	 * Whether the plugin is enabled
	 *
	 * @var boolean
	 **/
	var $enabled;
	
	
	/**
	 * Whether the plugin has enough settings to work
	 *
	 * @var boolean
	 **/
	var $active = false;
	
	
	/**
	 * The post count titles in an array
	 *
	 * @var array
	 **/
	var $titles;
	
	
	/**
	 * The post count titles in an array, but reversed
	 *
	 * @var array
	 **/
	var $titlesReverse;
	
	
	/**
	 * The format of the post count string
	 *
	 * @var string
	 **/
	var $format;
	
	
	/**
	 * A cache of users titles
	 *
	 * @var array
	 **/
	var $cache = array();
	
	
	/**
	 * Pulls out database settings
	 *
	 * @return void
	 * @author Sam Bauers
	 **/
	function Post_Count_Titles()
	{
		// An integer set to 1 for enabled or 0 for disabled
		$this->enabled = bb_get_option('post_count_titles_enabled');
		
		$this->titles = bb_get_option('post_count_titles_titles');
		
		if (is_array($this->titles)) {
			$this->titlesReverse = array_reverse($this->titles, true);
			$this->active = true;
		}
		
		$this->format = bb_get_option('post_count_titles_format');
		
		if (!$this->format) {
			$this->format = '%ROLE-LINK%<br />%TITLE%<br />%POSTS% posts';
		}
	}
	
	
	/**
	 * Returns whether the plugin is active or not
	 *
	 * @return boolean
	 * @author Sam Bauers
	 **/
	function isActive()
	{
		if ($this->enabled && $this->active) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Adds a string representing the users post count to the given string
	 *
	 * @return string
	 * @author Sam Bauers
	 **/
	function addPostTitleToUserTitle($input)
	{
		global $bb_post;
		global $bbdb;
		global $bb_table_prefix;
		
		// Get the user object
		$user = bb_get_user(get_post_author_id());
		
		// Return the cached version to speed things up a bit on repeats
		if (isset($this->cache[$user->ID])) {
			return $this->cache[$user->ID];
		}
		
		// Get the live current posts
		$currentPosts = $bbdb->get_var("SELECT COUNT(post_id) FROM `" . $bbdb->posts . "` WHERE `post_status` = '0' AND `poster_id` = '" . $user->ID . "';");
		
		// Work out the title for this user
		foreach ($this->titlesReverse as $posts => $title) {
			if ($currentPosts > $posts) {
				$currentTitle = $title;
				break;
			}
		}
		
		// Get the profile link from the input
		if (preg_match('@^(<a[^>]+>)([^<]+)@', $input, $matches)) {
			$profileLink = $matches[1];
			$role = $matches[2];
		} else {
			$role = $input;
		}
		
		// Transform the output based on the format
		$output = str_replace(
			array(
				'%ROLE%',
				'%ROLE-LINK%',
				'%TITLE%',
				'%TITLE-LINK%',
				'%POSTS%'
			),
			array(
				$role,
				$input,
				$currentTitle,
				$profileLink . $currentTitle . '</a>',
				$currentPosts
			),
			$this->format
		);
		
		// Cache the result
		$this->cache[$user->ID] = $output;
		
		return $output;
	}
} // END class Post_Count_Titles


// Initialise the class
$post_count_titles = new Post_Count_Titles();


// If active, then add filters via API
if ($post_count_titles->isActive()) {
	add_filter('post_author_title', array($post_count_titles, 'addPostTitleToUserTitle'));
}


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'post_count_titles_admin_page_add');
add_action('bb_admin-header.php','post_count_titles_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function post_count_titles_admin_page_add() {
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Post count titles'), 'use_keys', 'post_count_titles_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Post count titles'), 'use_keys', 'post_count_titles_admin_page');
		if (isset($bb_submenu['plugins.php'])) { // Build 740-793
			$bb_submenu['plugins.php'][] = $submenu;
		} else { // Build 277-739
			$bb_submenu['site.php'][] = $submenu;
		}
	}
}


/**
 * Writes an admin page for the plugin
 *
 * @return string
 * @author Sam Bauers
 **/
function post_count_titles_admin_page() {
	$enabled = bb_get_option('post_count_titles_enabled');
	$titles = bb_get_option('post_count_titles_titles');
	$format = bb_get_option('post_count_titles_format');
	
	if ($enabled) {
		$enabled_checked = ' checked="checked"';
	}
	
	if (!$titles) {
		$titles = array(
			'0' => 'French Fry',
			'10' => 'Junior Burger',
			'100' => 'Quarter Pounder',
			'1000' => 'Big Mac'
		);
	}
	
	$title_form = array();
	
	foreach ($titles as $posts => $title) {
		$title_form[] = array(
			'posts' => $posts,
			'title' => $title
		);
	}
	
	if (!$format) {
		$format = '%ROLE-LINK%<br />%TITLE%<br />%POSTS% posts';
	}
?>
	<h2>Post count titles</h2>
	<h3>Enable</h3>
	<form method="post">
	<p>
		<input type="checkbox" name="post_count_titles_enabled" value="1" tabindex="1"<?php echo $enabled_checked; ?> /> Enable post count titles<br />
		&nbsp;
	</p>
	<h3>Titles</h3>
	<p>
		"Posts" refers to the number of posts the user must have made to achieve the given "Title"
	</p>
	<table>
		<tr>
			<th>Posts</th>
			<th>Title</th>
		</tr>
<?php
	for ($i = 0; $i < 10; $i++) { 
?>
		<tr>
			<td><input type="text" name="post_count_titles_posts[<?php echo $i; ?>]" tabindex="<?php echo $i+1; ?>0" value="<?php echo $title_form[$i]['posts']; ?>" /></td>
			<td><input type="text" name="post_count_titles_title[<?php echo $i; ?>]" tabindex="<?php echo $i+1; ?>1" value="<?php echo $title_form[$i]['title']; ?>" /></td>
		</tr>
<?php
	}
?>
	</table>
	<h3>Format</h3>
	<p>
		The format of the string used in place of the normal users role on posts.
	</p>
	<ul>
		<li>%ROLE% - the normal users role</li>
		<li>%ROLE-LINK% - the normal users role with a link to their profile</li>
		<li>%TITLE% - the users post count title</li>
		<li>%TITLE-LINK% - the users post count title with a link to their profile</li>
		<li>%POSTS% - the users post count</li>
	</ul>
	<p>
		<input type="text" name="post_count_titles_format" value="<?php echo $format; ?>" tabindex="1000" size="50" />
	</p>
	<p class="submit alignleft">
		<input name="submit" type="submit" value="<?php _e('Update'); ?>" tabindex="1001" />
		<input type="hidden" name="action" value="post_count_titles_update" />
	</p>
	</form>
<?php
}


/**
 * Processes the admin page form
 *
 * @return void
 * @author Sam Bauers
 **/
function post_count_titles_admin_page_process() {
	if (isset($_POST['submit'])) {
		if ('post_count_titles_update' == $_POST['action']) {
			// Enable post count titles
			if ($_POST['post_count_titles_enabled']) {
				bb_update_option('post_count_titles_enabled', $_POST['post_count_titles_enabled']);
			} else {
				bb_delete_option('post_count_titles_enabled');
			}
			
			// Set an empty titles array
			$titles = array();
			
			// Enable email retrieval from LDAP
			if ($_POST['post_count_titles_posts'] && $_POST['post_count_titles_title']) {
				$posts = $_POST['post_count_titles_posts'];
				$title = $_POST['post_count_titles_title'];
				
				for ($i = 0; $i < 10; $i++) {
					if (is_numeric($posts[$i]) && !empty($title[$i])) {
						$titles[$posts[$i]] = $title[$i];
					}
				}
			}
			
			// Save or delete the titles
			if (count($titles)) {
				ksort($titles, SORT_NUMERIC);
				bb_update_option('post_count_titles_titles', $titles);
			} else {
				bb_delete_option('post_count_titles_titles');
			}
			
			// Post count titles format
			if ($_POST['post_count_titles_format']) {
				bb_update_option('post_count_titles_format', $_POST['post_count_titles_format']);
			} else {
				bb_delete_option('post_count_titles_format');
			}
		}
	}
}
?>