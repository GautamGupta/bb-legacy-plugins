<?php
/*
Plugin Name: zaerl Post Permalink
Plugin Description: Post Permalink
Version: 0.1
Plugin URI: http://www.zaerl.com
Author: zaerl
Author URI: http://www.zaerl.com

zaerl Post Permalink: post permalink for bbPress
Copyright (C) 2010  Francesco Bigiarini

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
	
define('ZA_PP_VERSION', '0.1');
define('ZA_PP_ID', 'za-post-permalink');
define('ZA_PP_NAME', 'zaerl Post Permalink');

// Public function
function za_post_permalink($id = 0)
{
	$id = get_post_id($id);
	
	if($id === false) return false;

	if(bb_get_option('mod_rewrite')) return bb_get_uri("post/$id");
	else return bb_get_uri('', array('post' => $id));
}

function za_pp_check_post_id($id)
{
	if(!is_numeric($id))
	{
		nocache_headers();
		bb_safe_redirect(bb_get_option('uri'));
		exit;
	}

	return true;
}

function za_pp_get_post()
{
	if(bb_get_option('mod_rewrite'))
	{
		$s = $_SERVER['REQUEST_URI'];
		$pos = strpos($s, 'post/');
		
		if($pos !== false)
		{
			$num = substr($s, $pos + 5);

			if($num !== false && za_pp_check_post_id($num)) return (int)$num;
		}
	}

	if(isset($_GET['post']) && za_pp_check_post_id($_GET['post']))
		return (int)$_GET['post'];
	
	return false;
}

function za_pp_initialize()
{
	global $bb_current_user;
	
	bb_load_plugin_textdomain(ZA_PP_ID, dirname(__FILE__) . '/languages');
	
	$id = za_pp_get_post();
	
	if($id !== false)
	{
		$post = bb_get_post(get_post_id($id));

		if(!$post) bb_die(__("The post you have specified doesn't exists", ZA_PP_ID));
		elseif($post->post_status == 1) bb_die(__("The post you have specified has been canceled", ZA_PP_ID));
		else
		{
			$page = bb_get_page_number($bb_post->post_position);
			$link = apply_filters('get_post_link',
				get_topic_link($post->topic_id, $page) . "#post-$post->post_id",
				$post->post_id);

			wp_redirect($link);
			exit;
		}
	}
}

add_action('bb_init', 'za_pp_initialize');

?>