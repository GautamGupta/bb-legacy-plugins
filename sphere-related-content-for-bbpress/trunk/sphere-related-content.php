<?php
/*
Plugin Name: Sphere Related Content for bbPress
Plugin URI: http://bbpress.org/plugins/topic/58
Description: Automatically show related blog posts and news articles from Sphere.
Author: Sam Bauers
Author URI: 
Version: 0.1

Version History:
0.1		: Ported from original WordPress Plugin by Watershed Studio, LLC
*/


/**
 * Sphere Related Content for bbPress version 0.1
 * 
 * ----------------------------------------------------------------------------------
 * 
 * Copyright (C) 2007 Sphere (plugins@sphere.com)
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
 * @copyright 2007 Sphere
 * @copyright 2007 Sam Bauers
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v2
 * @version   0.1
 **/



function bb_sphere_header()
{
	$r = '<style type="text/css" media="screen">' . "\r\n";
	$r .= '	li.iconsphere a {' . "\r\n";
	$r .= '		background: url(http://www.sphere.com/images/sphereicon.gif) center left no-repeat;' . "\r\n";
	$r .= '		white-space: nowrap;' . "\r\n";
	$r .= '		padding: 1px 0 1px 20px;' . "\r\n";
	$r .= '	}' . "\r\n";
	$r .= '</style>' . "\r\n";
	$r .= '<script type="text/javascript" src="http://www.sphere.com/widgets/sphereit/js?t=' . bb_get_sphere_rc_wtype() . '&amp;p=wordpressorg"></script>' . "\r\n";
	
	echo $r;
}

function bb_sphere_get_sphereit_link()
{
	$link = get_topic_link();
	
	$r = '<li class="iconsphere">'; 
	$r .= '<a title="Sphere: Related Content" onclick="return Sphere.Widget.search(\'';
	$r .= $link;
	$r .= '\')" href="http://www.sphere.com/search?q=sphereit:';
	$r .= $link;
	$r .= '">Sphere: Related Content</a>'; 
	$r .= "</li>";
	
	echo $r;
}

function bb_sphere_post($content)
{
	$scope = bb_get_sphere_scope();
	
	$sphereit = false;
	
	if ($scope == 'first') {
		global $bb_post;
		if ($bb_post->post_position == 1) {
			$sphereit = true;
		}
	} else {
		$sphereit = true;
	}
	
	if ($sphereit) {
		$r = '<!-- sphereit start -->' . "\r\n";
		$r .= $content . "\r\n"; 
		$r .= '<!-- sphereit end -->';
	} else {
		$r = $content;
	}
	
	return $r;
}

function bb_get_sphere_rc_wtype()
{
	$wtype = bb_get_option('bb_sphere_rc_wtype');
	
	if (!$wtype) {
		$wtype = "wordpressorg";
	}
	
	return $wtype;
}

function bb_get_sphere_scope()
{
	$scope = bb_get_option('bb_sphere_scope');
	
	if (!$scope) {
		$scope = 'all';
	}
	
	return $scope;
}


add_action('bb_head', 'bb_sphere_header');
add_action('topicmeta','bb_sphere_get_sphereit_link');
add_action('post_text','bb_sphere_post');


/**
 * The admin pages below are handled outside of the class due to constraints
 * in the architecture of the admin menu generation routine in bbPress
 */


// Add filters for the admin area
add_action('bb_admin_menu_generator', 'bb_sphere_admin_page_add');
add_action('bb_admin-header.php', 'bb_sphere_admin_page_process');


/**
 * Adds in an item to the $bb_admin_submenu array
 *
 * @return void
 * @author Sam Bauers
 **/
function bb_sphere_admin_page_add()
{
	if (function_exists('bb_admin_add_submenu')) { // Build 794+
		bb_admin_add_submenu(__('Sphere related content'), 'use_keys', 'bb_sphere_admin_page');
	} else {
		global $bb_submenu;
		$submenu = array(__('Sphere related content'), 'use_keys', 'bb_sphere_admin_page');
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
function bb_sphere_admin_page()
{
	$inputs['wordpressorg'] = false;
	$inputs['political_dem'] = false;
	$inputs['political_rep'] = false;
	$inputs['political_gen'] = false;
	
	$wtype = bb_get_sphere_rc_wtype();
	
	$inputs[$wtype] = ' checked="checked"';
	
	
	$inputs['first'] = false;
	
	$scope = bb_get_sphere_scope();
	
	$inputs[$scope] = ' checked="checked"';
?>
<h2><?php _e('Sphere related content'); ?></h2>
<p>Select the Sphere content plug-in you'd like to use on your forum.</p>
<p>
	If your forum is about technology, news, gossip, sports or any other topics, stick with the
	CLASSIC Sphere plug-in. If your forum is about POLITICS, you may want to use a POLITICS plug-ins.
</p>
<form method="post">
	<table class="widefat">
		<thead>
			<tr>
				<th></th>
				<th>Sphere plug-in</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<tr<?php alt_class('plugin'); ?>>
				<td>
					<input name="wtype" type="radio" value="wordpressorg"<?php echo($inputs['wordpressorg']); ?> />
				</td>
				<td style="white-space:nowrap;">Classic</td>
				<td>
					Shows related blog posts and news from a wide variety of sources, not category specific.  If in doubt, stick with this one.
				</td>
			</tr>
			<tr<?php alt_class('plugin'); ?>>
				<td>
					<input name="wtype" type="radio" value="politics_dem"<?php echo($inputs['politics_dem']); ?> />
				</td>
				<td style="white-space:nowrap;">Politics (Democrats)</td>
				<td>
					Shows related blog posts from Democratic and other left-leaning blogs, as well as from a variety of news sources.
				</td>
			</tr>
			<tr<?php alt_class('plugin'); ?>>
				<td>
					<input name="wtype" type="radio" value="politics_rep"<?php echo($inputs['politics_rep']); ?> />
				</td>
				<td style="white-space:nowrap;">Politics (Republican)</td>
				<td>
					Shows related blog posts from Republican and other right-leaning blogs, as well as from a variety of news sources.
				</td>
			</tr>
			<tr<?php alt_class('plugin'); ?>>
				<td>
					<input name="wtype" type="radio" value="politics_gen"<?php echo($inputs['politics_gen']); ?> />
				</td>
				<td style="white-space:nowrap;">Politics (Balanced)</td>
				<td>
					Shows related blog posts from both sides of the political divide, as well as from a variety of news sources.
				</td>
			</tr>
		</tbody>
	</table>
	<p>
		<input name="scope" type="hidden" value="all" />
		<input name="scope" type="checkbox" value="first"<?php echo($inputs['first']); ?> /> Only search Sphere for content related to the first post of each topic.
	</p>
	<p class="submit alignleft">
		<input name="action" type="hidden" value="bb_sphere_save" />
		<input type="submit" value="Save settings" />
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
function bb_sphere_admin_page_process()
{
	if ($_POST['action'] == 'bb_sphere_save') {
		bb_update_option('bb_sphere_rc_wtype', $_POST['wtype']);
		bb_update_option('bb_sphere_scope', $_POST['scope']);
	}
}
?>