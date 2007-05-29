<?php
/*
Plugin Name: Page links
Plugin URI: http://bbpress.org/plugins/topic/43
Description: Adds page links to topic lists
Author: Sam Bauers
Version: 1.0.2
Author URI: 

Version History:
1.0 	: Initial Release
1.0.1	: Fixed a bug relating to earlier versions, suppressed a PHP warning
1.0.2	: Added compatibility with "Front Page Topics" plugin
*/

/*
Page links for bbPress version 1.0.2
Copyright (C) 2007 Sam Bauers (sam@viveka.net.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

if (!function_exists('is_tags')) {
	function is_tags()
	{
		return is_tag();
	}
}

if (is_front() || is_forum() || is_tags()) {
	add_filter('topic_title', 'page_links_add_links', 100);
	add_action('bb_head', 'page_links_add_css');
}

function page_links_add_links($title)
{
	global $topic;
	$uri = get_topic_link();
	$posts = $topic->topic_posts;
	$posts += topic_pages_add($topic->topic_id);
	
	if (bb_get_option('mod_rewrite')) {
		if (false === $pos = strpos($uri, '?')) {
			$uri = $uri . '%_%';
		} else {
			$uri = substr_replace($uri, '%_%', $pos, 0);
		}
	} else {
		$uri = add_query_arg('page', '%_%', $uri);
	}
	
	if ($perPage = bb_get_option('front_page_topics')) {
		$perPage = $perPage['topic-page'];
	} else {
		$perPage = bb_get_option('page_topics');
	}
	
	$links = paginate_links(
		array(
			'base' => $uri,
			'format' => bb_get_option('mod_rewrite') ? '/page/%#%' : '%#%',
			'total' => ceil($posts/$perPage),
			'current' => 0,
			'show_all' => true,
			'type' => 'array'
		)
	);
	
	if ($links) {
		unset($links[0]);
	}
	
	if ($links) {
		$links = join('', $links);
		$links = substr($links, 0, -4);
		$title .= '</a> -' . $links;
	}
	
	return $title;
}

function page_links_add_css()
{
	$plugin_uri = bb_get_option('uri') . str_replace(BBPATH, '', BBPLUGINDIR);
	$css_uri = $plugin_uri . 'page-links.css';
	echo '<link rel="stylesheet" href="' . $css_uri . '" type="text/css" />' . "\n";
}
?>