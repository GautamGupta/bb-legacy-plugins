<?php
/*
Plugin Name: zaerl Visibility
Plugin URI: http://www.zaerl.com
Description: topic/forum/profile visibility
Version: 0.2.1
Author: Francesco Bigiarini
Author URI: http://www.zaerl.com

zaerl Visibility: forum/topic/profile hide&lock for bbPress
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

This software is based on the work of `_ck_'. You can download the
original code here: http://bbpress.org/plugins/topic/105

*/

require_once(BB_PATH . BB_INC . 'functions.bb-statistics.php');

define('ZA_VISIBILITY_VERSION', '0.2.1');
define('ZA_VI_ID', 'za-visibility');
define('ZA_VI_NAME', 'zaerl Visibility');

// Public function
function za_page_is_hidden($role)
{
	global $za_vi_settings;
	global $wp_roles;
	
	if(!$wp_roles->is_role($role)) return false;
	
	if(bb_is_forum())
	{
		global $forum;

		$id = $forum->forum_id;

		if(!empty($za_vi_settings['fhr'][$id]) &&
			in_array($role, $za_vi_settings['fhr'][$id]))
			return true;
		
		$parent = get_forum_parent($id);
			
		return ($parent && !empty($za_vi_settings['fhr'][$parent])) ?
			in_array($role, $za_vi_settings['fhr'][$parent]) : false;
	} elseif(bb_is_topic())
	{
		global $topic;

		$id = $topic->topic_id;
		$forum_id = $topic->forum_id;

		if(!empty($za_vi_settings['fhr'][$forum_id]) &&
			in_array($role, $za_vi_settings['fhr'][$forum_id]))
			return true;
			
		return !empty($za_vi_settings['thr'][$id]) ?
			in_array($role, $za_vi_settings['thr'][$id]) : false;
	} elseif(bb_is_profile())
	{
		global $user;
		$id = $user->ID;
		
		if(!empty($za_vi_settings['phr'][$id]))
			return in_array($role, $za_vi_settings['phr'][$id]);
	} else return false;
		
	

	return false;
}

// Public function
function za_forum_is_hidden($id = 0, $role = '')
{
	$id = get_forum_id($id);

	if($id == 0) return null;

	return za_vi_forum_has_visibility($id);
}

// Public function
function za_topic_is_hidden($id = 0, $role = '')
{
	$id = get_topic_id($id);

	if($id == 0) return null;

	return za_vi_topic_has_visibility($id);
}

// Public function
function za_profile_is_hidden($id = 0, $role = '')
{
	$id = bb_get_user_id($id);

	if($id == 0) return null;

	return za_vi_profile_has_visibility($id);
}

// Public function
function za_forum_is_locked($id = 0)
{
	$id = get_forum_id($id);

	return za_entity_check_visibility($id, 'f', 'l');
}

// Public function
function za_topic_is_locked($id = 0)
{
	$id = get_topic_id($id);

	return za_entity_check_visibility($id, 't', 'l');
}

// type = f, t, p
// rule = h, l
function za_entity_check_visibility($id, $entity_type, $rule_type = 'h')
{
	global $za_vi_settings, $za_vi_current_user_role, $bb_current_user;
	$uid = (!empty($bb_current_user)) ? intval($bb_current_user->ID) : 0;

	if($entity_type != 'p') if($id == 0) return null;

	$rl = $za_vi_settings[$entity_type . $rule_type . 'r'][$id];
	$ul = $za_vi_settings[$entity_type . $rule_type . 'u'][$id];
	$uel = $za_vi_settings[$entity_type . 'e' . $rule_type . 'u'][$id];

	if($uid != 0)
	{
		if(isset($uel) && in_array($uid, $uel)) return false;
		if(isset($ul) && in_array($uid, $ul)) return true;
	}
	
	if(isset($rl) && in_array($za_vi_current_user_role, $rl))
		return true;
	
	return false;
}

function za_vi_profile_hidden_error()
{
	global $za_vi_settings;

	bb_die(empty($za_vi_settings['phm']) ? __('User not found.') : $za_vi_settings['phm'], '', 404);
	exit;
}

function za_vi_affected_topics_count()
{
	global $za_vi_settings;
	$ret = 0;
	
	if(!empty($za_vi_settings['thr'])) $ret += count($za_vi_settings['thr']);
	if(!empty($za_vi_settings['thu'])) $ret += count($za_vi_settings['thu']);
	if(!empty($za_vi_settings['tehu'])) $ret += count($za_vi_settings['tehu']);	

	if(!empty($za_vi_settings['tlr'])) $ret += count($za_vi_settings['tlr']);	
	if(!empty($za_vi_settings['tlu'])) $ret += count($za_vi_settings['tlu']);
	if(!empty($za_vi_settings['telu'])) $ret += count($za_vi_settings['telu']);

	return $ret;
}

function za_vi_forum_count($count, $forum_id)
{
	global $za_vi_hidden_topics_count;

	$id = get_forum_id($forum_id);

	if(isset($za_vi_hidden_topics_count[$id]))
		return $count - $za_vi_hidden_topics_count[$id];
	else return $count;
}

function za_vi_forum_has_visibility($id, $only_hide = false)
{
	global $za_vi_settings;

	$ret = !empty($za_vi_settings['fhr'][$id]) ||
		!empty($za_vi_settings['fhu'][$id]) ||
		!empty($za_vi_settings['fehu'][$id]);
	
	if($only_hide) return $ret;
	else return $ret || !empty($za_vi_settings['flr'][$id]) ||
		!empty($za_vi_settings['flu'][$id]) ||
		!empty($za_vi_settings['felu'][$id]);
}

function za_vi_topic_has_visibility($id, $only_hide = false)
{
	global $za_vi_settings;

	$ret = !empty($za_vi_settings['thr'][$id]) ||
		!empty($za_vi_settings['thu'][$id]) ||
		!empty($za_vi_settings['tehu'][$id]);
	
	if($only_hide) return $ret;
	else return $ret || !empty($za_vi_settings['tlr'][$id]) ||
		!empty($za_vi_settings['tlu'][$id]) ||
		!empty($za_vi_settings['telu'][$id]);
}

function za_vi_profile_has_visibility($id)
{
	global $za_vi_settings;

	return !empty($za_vi_settings['phr'][$id]) ||
		!empty($za_vi_settings['phu'][$id]) ||
		!empty($za_vi_settings['pehu'][$id]);
}

function za_vi_reset_forum($id)
{
	global $za_vi_settings;

	unset($za_vi_settings['fhr'][$id]);
	unset($za_vi_settings['fhu'][$id]);
	unset($za_vi_settings['fehu'][$id]);

	unset($za_vi_settings['flr'][$id]);
	unset($za_vi_settings['flu'][$id]);
	unset($za_vi_settings['felu'][$id]);
	
	za_vi_reset_subforums($id);
}

function za_vi_reset_subforums($id)
{
	global $za_vi_settings;
	$subforums = bb_get_forums($id);

	if($subforums !== FALSE)
	{
		foreach($subforums as $f)
		{
			if(isset($za_vi_settings['fhr'][$id]))
				$za_vi_settings['fhr'][$f->forum_id] = $za_vi_settings['fhr'][$id];
			else unset($za_vi_settings['fhr'][$f->forum_id]);

			if(isset($za_vi_settings['fhu'][$id]))
				$za_vi_settings['fhu'][$f->forum_id] = $za_vi_settings['fhu'][$id];
			else unset($za_vi_settings['fhu'][$f->forum_id]);

			if(isset($za_vi_settings['fehu'][$id]))
				$za_vi_settings['fehu'][$f->forum_id] = $za_vi_settings['fehu'][$id];
			else unset($za_vi_settings['fehu'][$f->forum_id]);
		}
	}
}

function za_vi_reset_topic($id)
{
	global $za_vi_settings;

	unset($za_vi_settings['thr'][$id]);
	unset($za_vi_settings['thu'][$id]);
	unset($za_vi_settings['tehu'][$id]);
	unset($za_vi_settings['tlr'][$id]);
	unset($za_vi_settings['tlu'][$id]);
	unset($za_vi_settings['telu'][$id]);
}

function za_vi_reset_profile($id)
{
	global $za_vi_settings;

	unset($za_vi_settings['phr'][$id]);
	unset($za_vi_settings['phu'][$id]);
	unset($za_vi_settings['pehu'][$id]);
}

function za_vi_admin_get_request($variables, $request)
{
	$variables['plugin'] = 'za_vi_configuration_page';

	$temp = bb_get_uri('bb-admin/admin-base.php', $variables,
		BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN);

	return esc_url(bb_nonce_url($temp, $request));
}

function za_vi_explode_user_list($list)
{
	$ret = array();

	if(isset($list) && is_string($list))
	{
		$temp = explode(',', $list);

		foreach($temp as $i => $v)
		{
			$v = trim($v);
			$user = bb_get_user_by_nicename($v);

			if($user) $ret[] = $user->ID;
		}
	}

	return $ret;
}

function za_vi_var($list)
{
	global $za_vi_settings;

	foreach($list as $i)
		if(!isset($za_vi_settings[$i])) $za_vi_settings[$i] = array();
}

function za_vi_initialize()
{
	bb_load_plugin_textdomain(ZA_VI_ID, dirname(__FILE__) . '/languages');
	
	global $za_vi_settings, $za_vi_hidden_forums_list, $za_vi_hidden_topics_list,
		$za_vi_hidden_forums, $za_vi_hidden_topics, $za_vi_locked_forums_count,
		$za_vi_hidden_topics_count, $za_vi_current_user_role;
		
	global $bb_views, $bb_current_user, $forum_id, $page;

	$za_vi_settings = bb_get_option('za_visibility');

	if(empty($za_vi_settings)) $za_vi_settings = array();

	// super users and roles
	za_vi_var(array('su', 'sr',
		'fhr', 'fhu', 'fehu', 'flr', 'flu', 'felu', // forums
		'thr', 'thu', 'tehu', 'tlr', 'tlu', 'telu', // topics
		'phr', 'phu', 'pehu' // profiles
		));
	
	if(!isset($za_vi_settings['fp'])) $za_vi_settings['fp'] = '';
	if(!isset($za_vi_settings['tp'])) $za_vi_settings['tp'] = '';

	if(!isset($za_vi_settings['flm'])) $za_vi_settings['flm'] = '';
	if(!isset($za_vi_settings['tlm'])) $za_vi_settings['tlm'] = '';
	//if(!isset($za_vi_settings['glm'])) $za_vi_settings['glm'] = '';
	
	if(!isset($za_vi_settings['phm'])) $za_vi_settings['phm'] = '';

	$id = (!empty($bb_current_user)) ? intval($bb_current_user->ID) : 0;

	$za_vi_hidden_forums_list = array();
	$za_vi_hidden_topics_list = array();

	$za_vi_hidden_forums = array();

	$za_vi_hidden_topics = array();
	$za_vi_hidden_topics_count = array();

	// check if user is blocked
	if($id == 0) $za_vi_current_user_role = 'inactive';
	else
	{ 		
		if(!isset($bb_current_user->capabilities) ||
			!is_array($bb_current_user->capabilities) ||
			empty($bb_current_user->capabilities))
		{
			$za_vi_current_user_role = 'inactive';
		} else
		{
			$caps = array_keys($bb_current_user->capabilities);
			$za_vi_current_user_role = $caps[0];
		}
	}

	// Do not filter for super users
	if(!in_array($id, $za_vi_settings['su']))
	{	
		// Do not filter for super roles
		if(!in_array($za_vi_current_user_role, $za_vi_settings['sr']))
		{
			// forum hide role
			foreach($za_vi_settings['fhr'] as $key => $value)
				if(in_array($za_vi_current_user_role, $value)) $za_vi_hidden_forums_list[] = $key;
			
			// forum hide user
			if($id != 0 && !empty($za_vi_settings['fhu']))
			{
				foreach($za_vi_settings['fhu'] as $key => $value)
					if(in_array($id, $value)) $za_vi_hidden_forums_list[] = $key;
			
				$za_vi_hidden_forums_list = array_unique($za_vi_hidden_forums_list);
			}
			
			$ehu_count = count($za_vi_settings['fehu']);
			$i = 0;

			// forum hide user exception
			if($id != 0 && $ehu_count > 0 && !empty($za_vi_hidden_forums_list))
			{
				foreach($za_vi_hidden_forums_list as $key => $value)
				{
					if(isset($za_vi_settings['fehu'][$value]) &&
						in_array($id, $za_vi_settings['fehu'][$value]))
					{
						unset($za_vi_hidden_forums_list[$key]);

						if(++$i == $ehu_count) break;
					}
				}
			}

			// topic hide role
			foreach($za_vi_settings['thr'] as $key => $value)
				if(in_array($za_vi_current_user_role, $value)) $za_vi_hidden_topics_list[] = $key;

			// topic hide user
			if($id != 0 && !empty($za_vi_settings['thu']))
			{
				foreach($za_vi_settings['thu'] as $key => $value)
					if(in_array($id, $value)) $za_vi_hidden_topics_list[] = $key;
			
				$za_vi_hidden_topics_list = array_unique($za_vi_hidden_topics_list);
			}

			$ehu_count = count($za_vi_settings['tehu']);
			$i = 0;
			
			// topic hide user exception
			if($id != 0 && $ehu_count > 0 && !empty($za_vi_hidden_topics_list))
			{
				foreach($za_vi_hidden_topics_list as $key => $value)
				{
					if(isset($za_vi_settings['tehu'][$value]) &&
						in_array($id, $za_vi_settings['tehu'][$value]))
					{
						unset($za_vi_hidden_topics_list[$key]);

						if(++$i == $ehu_count) break;
					}
				}
			}

			if(bb_is_profile()) add_action('bb_profile.php', 'za_vi_profile_hook');

			$temp = array();

			// count hidden topics (per forum)
			if(!empty($za_vi_hidden_topics_list))
			{
				foreach($za_vi_hidden_topics_list as $v)
				{
					$topic = get_topic($v);
					
					if($topic)
					{
						$forum = $topic->forum_id;
						
						if(!isset($za_vi_hidden_topics_count[$forum]))
							$za_vi_hidden_topics_count[$forum] = 1;
						else ++$za_vi_hidden_topics_count[$forum];
					}
				}
				
				add_filter('forum_topics', 'za_vi_forum_count', 10, 2);
				add_filter('forum_posts', 'za_vi_forum_count', 10, 2);
			}
			
			// count locked forums
			foreach($za_vi_settings['flr'] as $key => $value)
				if(in_array($za_vi_current_user_role, $value)) $temp[] = $key;
			
			if($id != 0 && !empty($za_vi_settings['flu']))
			{
				foreach($za_vi_settings['fhu'] as $key => $value)
					if(in_array($id, $value)) $temp[] = $key;
			
				$za_vi_locked_forums_count = count(array_unique($temp));
			}

			if(!empty($za_vi_settings['flr']) ||
				!empty($za_vi_settings['flu']) ||
				!empty($za_vi_settings['felu']) ||

				!empty($za_vi_settings['tlr']) ||
				!empty($za_vi_settings['tlu']) ||
				!empty($za_vi_settings['telu']))
			{
				add_action('bb_forum.php', 'za_vi_forum_template_hook');
				add_action('bb_topic.php', 'za_vi_topic_template_hook');
				add_action('bb_front-page.php', 'za_vi_template_hook');
			}
		}
	}

	if(!empty($za_vi_hidden_forums_list) || !empty($za_vi_hidden_topics_list))
	{
		if(bb_is_forum())
		{
			$page = bb_get_uri_page();
			bb_repermalink();
		
			if(!empty($forum_id) && isset($za_vi_hidden_forums_list[$forum_id]))
			{
				nocache_headers();
				bb_safe_redirect(bb_get_option('uri'));
				exit;
			}
		}
		
		//$za_vi_hidden_forums_count = count($za_vi_hidden_forums_list);
		//$za_vi_hidden_topics_count = count($za_vi_hidden_topics_list);

		$za_vi_hidden_forums = $za_vi_hidden_forums_list;
		$za_vi_hidden_topics = $za_vi_hidden_topics_list;
		$za_vi_hidden_forums_list = implode(',', $za_vi_hidden_forums_list);
		$za_vi_hidden_topics_list = implode(',', $za_vi_hidden_topics_list);
 
		$filters = array('get_thread','get_thread_post_ids',
			'get_latest_posts','get_latest_topics','get_latest_forum_posts',	
			'get_recent_user_replies','get_recent_user_threads','get_user_favorites',
			'get_sticky_topics','get_tagged_topics','get_tagged_topic_posts',	
			'bb_recent_search','bb_relevant_search','bb_get_first_post','bb_is_first'	
		);
	
		// bb_includes/functions.bb-forums.php:143
		// erroneous filter
		if(!is_topic())
		{
			add_filter('get_forum_where','za_vi_simple_filter_fixed', 20);
			add_filter('get_forums_where','za_vi_simple_filter', 20);
		} else add_filter('get_topic_where','za_vi_filter', 20);
		
		foreach($filters as $filter)
			add_filter($filter . '_where','za_vi_filter', 20);

		foreach($bb_views as $key=>$value)
			add_action('bb_view_' . $key . '_where', 'za_vi_filter');
			
		add_filter('sort_tag_heat_map', 'za_vi_filter_tags', 1);
	}
	
	if($bb_current_user && $bb_current_user->has_cap('administrate'))
	{
		if(!empty($za_vi_settings['fp']))
			add_filter('get_forum_name', 'za_vi_label_forum', 11, 2);

		if(!empty($za_vi_settings['tp']))
			add_filter('topic_title', 'za_vi_label_topic', 11, 2);

		//add_action('pre_edit_form', 'za_vi_label_topic_stop');
		add_filter('bb_topic_admin', 'za_vi_topic_admin', 10, 1);
		add_action('bb_admin_menu_generator', 'za_vi_configuration_page_add');
		add_action('za_vi_configuration_page_pre_head', 'za_vi_configuration_page_process');

		add_action('bb_profile.php', 'za_vi_admin_profile_hook');
	}
}

add_action('bb_init', 'za_vi_initialize');

function za_vi_admin_profile_hook($keys)
{
	add_filter('get_profile_info_keys', za_vi_profile_info_key_filter);
}

function za_vi_profile_hook($keys)
{
	global $user;

	if(za_entity_check_visibility(za_vi_profile_has_visibility($user->ID) ? $user->ID : 0, 'p'))
		za_vi_profile_hidden_error();

	return $keys;
}

function za_vi_profile_info_key_filter($keys)
{
	$id = bb_get_user_id();

	echo '<p><a href="', za_vi_admin_get_request(array('pid' => $id), 'za-vi-update'),
		'">', __('Modify Profile Hide Status', ZA_VI_ID), '</a></p>';

	return $keys;
}

function za_vi_filter($where = '')
{
	global $za_vi_hidden_forums_list, $za_vi_hidden_topics_list; 
	$prefix = '';
	
	if($za_vi_hidden_forums_list == '' && $za_vi_hidden_topics_list == '')
		return $where;

	if(strpos($where,' t.')) $prefix = 't.';
	elseif(strpos($where, ' p.')) $prefix = 'p.';

	$ret = $where . (empty($where) ? ' WHERE ' : ' AND ');
	
	if($za_vi_hidden_forums_list != '')
		$ret .= "{$prefix}forum_id NOT IN ($za_vi_hidden_forums_list) ";

	if($za_vi_hidden_topics_list != '')
	{
		if($za_vi_hidden_forums_list != '')
			$ret .= 'AND ';
		
		$ret .= "{$prefix}topic_id NOT IN ($za_vi_hidden_topics_list) ";
	}
	
	return $ret;
}

function za_vi_simple_filter($where='')
{
	global $za_vi_hidden_forums_list; 
	$prefix = '';
	
	// CHECK
	if($za_vi_hidden_forums_list == '') return $where;

	if(strpos($where,' t.')) $prefix = 't.';
	elseif(strpos($where," p.")) $prefix = 'p.';

	return $where . (empty($where) ? ' WHERE ' : ' AND ') .
		"{$prefix}forum_id NOT IN ($za_vi_hidden_forums_list)";
}

function za_vi_simple_filter_fixed($where='')
{
	global $za_vi_hidden_forums_list; 

	//if(strpos($where,' t.')) $prefix = 't.';
	//elseif(strpos($where," p.")) $prefix = 'p.';

	if($za_vi_hidden_forums_list == '') return $where;

	//return "$where AND {$prefix}forum_id NOT IN ($za_vi_hidden_forums_list)";// AND {$prefix}topic_id NOT IN ($za_vi_hidden_topics_list) ";
	return "AND forum_id NOT IN ($za_vi_hidden_forums_list)";
}

function za_vi_get_label($label, $forum_id, $topic_id)
{
	global $za_vi_settings;

	if($label == '' || ($forum_id == '' && $topic_id == '')) return '';
	
	if(stripos($label, '%status%') !== FALSE)
	{
		$replace = '';
		
		if($forum_id != '')
		{	
			if(!empty($za_vi_settings['fhr'][$forum_id])) $replace .= '+HR';
			if(!empty($za_vi_settings['fhu'][$forum_id])) $replace .= '+HU';
			if(!empty($za_vi_settings['fehu'][$forum_id])) $replace .= '-HU';

			if(!empty($za_vi_settings['flr'][$forum_id])) $replace .= '+LR';
			if(!empty($za_vi_settings['flu'][$forum_id])) $replace .= '+LU';
			if(!empty($za_vi_settings['felu'][$forum_id])) $replace .= '-LU';
		}

		if($topic_id != '')
		{
			if(!empty($za_vi_settings['thr'][$topic_id])) $replace .= '+gr';
			if(!empty($za_vi_settings['thu'][$topic_id])) $replace .= '+hu';
			if(!empty($za_vi_settings['tehu'][$topic_id])) $replace .= '-hu';

			if(!empty($za_vi_settings['tlr'][$topic_id])) $replace .= '+lr';
			if(!empty($za_vi_settings['tlu'][$topic_id])) $replace .= '+lu';
			if(!empty($za_vi_settings['telu'][$topic_id])) $replace .= '-lu';
		}
		
		if($replace != '') $label = str_ireplace('%status%', $replace, $label);
	}

	if(!empty($label)) $label .= ' ';

	return $label;
}

function za_vi_label_forum($title, $id)
{
	global $za_vi_settings;
	
	if(!isset($_GET['action']) && (za_vi_forum_has_visibility($id)))
		return za_vi_get_label($za_vi_settings['fp'], $id, '') . $title;
	else return $title;
}

function za_vi_label_topic($title, $id)
{
	global $topic, $za_vi_settings;

	if($id == $topic->topic_id) $forum_id = $topic->forum_id;
	else
	{
		$get_topic = get_topic($id);
		$forum_id = $get_topic->forum_id;
	}
	
	if(za_vi_topic_has_visibility($id))
		return za_vi_get_label($za_vi_settings['tp'], $forum_id, $id) . $title;
	else return $title;
}	

function za_vi_label_topic_stop()
{
	remove_filter('topic_title', 'za_vi_label_topic', 11);
}

function za_vi_hide_link($args = '')
{
	global $za_vi_settings;

	$defaults = array('id' => 0, 'before' => '[', 'after' => ']');
	extract(wp_parse_args($args, $defaults), EXTR_SKIP);
	$id = get_topic_id((int)$id);

	$topic = get_topic($id);

	if(!$topic || !bb_current_user_can('stick_topic', $topic->topic_id)) return;

	$uri = za_vi_admin_get_request(array('id' => $topic->topic_id), 'za-vi-update');

	/* Translators: `Status' of a forum topic */
	$ret = za_vi_topic_has_visibility($id) ? __('Modify Hide/Lock Status', ZA_VI_ID) :
		__('Add Hide/Lock Rule', ZA_VI_ID);
	
	return "$before<a href=\"$uri\">$ret</a>$after";
}

function za_vi_topic_admin($parts)
{
	$parts['hide'] = za_vi_hide_link();
	return $parts;
}

// CHECK: return real caps
function za_vi_user_has_caps($allcaps, $caps, $args)
{
	global $za_vi_settings, $za_vi_current_user_role, $za_vi_locked_forums_count;
	$message = '';

	if($caps[1] == 'write_posts' && $args[0] == 'write_post')
	{
		$denied = 'write_posts';
		$rl = $za_vi_settings['tlr'][$args[2]];
		$ul = $za_vi_settings['tlu'][$args[2]];
		$uel = $za_vi_settings['telu'][$args[2]];

		if(!empty($za_vi_settings['tlm']))
			$message = $za_vi_settings['tlm'];
	} elseif($caps[1] == 'write_topics' && $args[0] == 'write_topic')
	{
		$denied = 'write_topics';
		
		if($args[2] == 0)
		{
			if($za_vi_locked_forums_count == get_total_forums())
				$no_forums = true;
		} else
		{
			$rl = $za_vi_settings['flr'][$args[2]];
			$ul = $za_vi_settings['flu'][$args[2]];
			$uel = $za_vi_settings['felu'][$args[2]];
			
			if(!empty($za_vi_settings['flm']) &&
				!(bb_is_tag() || bb_is_front()))
				$message = $za_vi_settings['flm'];
		}
	} elseif($caps[1] == 'edit_tags' && $args[0] == 'edit_tag_by_on')
	{
		$denied = 'edit_tags';
		$rl = $za_vi_settings['tlr'][$args[3]];
		$ul = $za_vi_settings['tlu'][$args[3]];
		$uel = $za_vi_settings['telu'][$args[2]];

		//if(!empty($za_vi_settings['glm']))
		//	$message = $za_vi_settings['glm'];
	}

	if(isset($denied) && (isset($no_forums) ||
		(isset($rl) && in_array($za_vi_current_user_role, $rl)) ||
		(isset($uel) && !in_array($args[1], $uel)) ||
		(isset($ul) && in_array($args[1], $ul))))
	{
		if($message != '') echo $message;
		$allcaps[$denied] = 0;
	}

	return $allcaps;
}

function za_vi_forum_template_hook($args)
{
	za_vi_template_hook($args);
}

function za_vi_topic_template_hook($args)
{
	za_vi_template_hook($args);
}

function za_vi_template_hook($args)
{
	add_filter('user_has_cap', 'za_vi_user_has_caps', 10, 3);
}

function za_vi_filter_tags(&$tags)
{
	global $bbdb, $za_vi_hidden_forums_list, $za_vi_hidden_topics_list;

	$min = min($tags) - 1;

	$query = "SELECT name,count FROM $bbdb->terms AS t 
		INNER JOIN $bbdb->term_taxonomy AS tt ON t.term_id = tt.term_id 
		INNER JOIN $bbdb->term_relationships AS tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN $bbdb->topics as tp ON object_id=topic_id
		WHERE tt.taxonomy='bb_topic_tag' AND tt.count >= $min AND (";
	
	$have_t = $za_vi_hidden_topics_list != '';
		
	if($za_vi_hidden_forums_list != '')
	{
		$query .= "forum_id IN($za_vi_hidden_forums_list)";
		
		if($have_t) $query .= ' OR ';
	}

	if($have_t) $query .= " topic_id IN($za_vi_hidden_topics_list)";
	
	$query .= ')';

	$results = $bbdb->get_results($query);
	
	if(empty($results) || !is_array($results)) return;

	foreach($results as $result)
	{
		if(isset($tags[$result->name]))
		{
			$tags[$result->name] -= $result->count; 

			if($tags[$result->name] < 1) unset($tags[$result->name]);
		}
	}

	unset($results);
}

function za_vi_replacement_message_field($title, $flm, $tlm/*, $glm*/)
{ ?>
	<fieldset>
		<legend><?php echo $title ?></legend>
		<?php /* Translators: a `role' is the role used by a user on a BBS. "Administrator" and "moderator" are examples of tipical roles. A `trailing space' is a space character appended after a string of text */
		//echo "\t\t<p>", __('You can specify three replacement messages that will be shown in place of the "new topic", "new post" and "add tag" forms.', ZA_VI_ID),
		echo "\t\t<p>", __('You can specify three replacement messages that will be shown in place of the "new topic" and "new post" forms.', ZA_VI_ID),
			"</p>\n";

		bb_option_form_element('za_vi_forum_message', array(
			'title' => __('Topic', ZA_VI_ID),
			'value' => $flm,
			'note' => __('Replacement message for "New Topic" form', ZA_VI_ID)));

		bb_option_form_element('za_vi_topic_message', array(
			'title' => __('Post', ZA_VI_ID),
			'value' => $tlm,
			'note' => __('Replacement message for "New Post" form', ZA_VI_ID)));

		/*bb_option_form_element('za_vi_tag_message', array(
			'title' => __('Tag', ZA_VI_ID),
			'value' => $glm,
			'note' => __('Replacement message for "Add Tag" form', ZA_VI_ID)));*/

		echo "\t</fieldset>";
}

function za_vi_configuration_page()
{
	global $za_vi_settings, $wp_roles;
	$roles = $wp_roles->get_names();
	$hidden_suffix = __('[hidden]', ZA_VI_ID);
?>
<h2><?php /* Translators: %s is replaced by the program name */ printf(__('%s Settings', ZA_VI_ID), ZA_VI_NAME); ?></h2>
<?php do_action('bb_admin_notices'); ?>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php',
	array('plugin' => 'za_vi_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
<?php

	$action_value = '';
	$id;

	if(isset($_GET['pid'])) // Profile special page
	{
		$is_all = $_GET['pid'] == 0;
		$id = $_GET['pid'];

		if(!$is_all) $user = bb_get_user($_GET['pid']);
?>
	<fieldset>
		<legend><?php $is_all ? _e('Modify Global Profile Hide Status', ZA_VI_ID) : _e('Modify Profile Hide Status', ZA_VI_ID) ?></legend>
		<p><?php
		
		if($is_all) _e('Modify the visibility of all profile pages.', ZA_VI_ID);
		else
		{
			printf(__('Modify the visibility of %s (%s) profile page.', ZA_VI_ID),
				'<a href="' . get_user_profile_link($id) . "\">{$user->display_name}</a>",
				$user->user_login);
		}

		echo "</p>\n";
?>
		<div>
			<div class="label"><?php _e('Roles Hide Rules', ZA_VI_ID) ?></div>
			<div class="inputs">
<?php

		/*if($is_forum)
		{
			$fh = $za_vi_settings['fhr'][$id];
		} else
		{
			$fh = $za_vi_settings['thr'][$id];
			$hidden_suffix = __('[hidden]', ZA_VI_ID);
		}*/
		
		$ph = $za_vi_settings['phr'][$id];
		$pgh = $za_vi_settings['phr'][0];

		foreach($roles as $i => $v)
		{ ?>					
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="<?php
					echo "za_vi_hide_$i\"";

					if(isset($ph) && in_array($i, $ph)) echo ' checked="checked"';

					echo "/> $v";
					
					if($i == 'inactive') echo ' ', _e('(guest users)', ZA_VI_ID);
				
					if($id != 0 && isset($pgh) && in_array($i, $pgh)) echo ' <em>', $hidden_suffix, '</em>';

					echo "</label>\n";	
		} ?>
				<p><?php $is_all ? _e('Specify the user roles for whom all profiles are hidden.', ZA_VI_ID) : _e('Specify the user roles for whom this profile is hidden.', ZA_VI_ID); ?></p>
			</div>
		</div><?php

		$hu = isset($za_vi_settings['phu'][$id]) ? $za_vi_settings['phu'][$id] : null;
		$hidden_users_names = array();
		
		if(isset($hu))
		{
			foreach($hu as $v)
			{
				$user = bb_get_user($v);

				if($user) $hidden_users_names[] = $user->user_login;
			}
		}

		bb_option_form_element('za_vi_hidden_users', array(
			'title' => __('Users Hide Rules', ZA_VI_ID),
			'value' => empty($hidden_users_names) ? '' : implode(', ', $hidden_users_names),
			'note' => $is_all ? __('Specify the users for whom all profiles are hidden. You must input a comma separated list of usernames.', ZA_VI_ID) : __('Specify the users for whom this profile is hidden. You must input a comma separated list of usernames.', ZA_VI_ID)));

		$hu = isset($za_vi_settings['pehu'][$id]) ? $za_vi_settings['pehu'][$id] : null;
		$hidden_users_names = array();
		
		if(isset($hu))
		{
			foreach($hu as $v)
			{
				$user = bb_get_user($v);

				if($user) $hidden_users_names[] = $user->user_login;
			}
		}

		bb_option_form_element('za_vi_ex_hidden_users', array(
			'title' => __('Users Hide Rules Exceptions', ZA_VI_ID),
			'value' => empty($hidden_users_names) ? '' : implode(', ', $hidden_users_names),
			'note' => __('Specify the users that are not affected by above hide rules. You must input a comma separated list of usernames.', ZA_VI_ID)));
?>
	</fieldset>
<?php
		$action_value = $is_all ? 'update-za-vi-allprofiles-settings' : 'update-za-vi-profile-settings';
	} elseif(isset($_GET['id']) || isset($_GET['fid'])) // Topics/Forums special page
	{
		$is_forum = isset($_GET['fid']);

		$id = $is_forum ? get_forum_id($_GET['fid']): get_topic_id($_GET['id']);

		// check double spec
		if(!$is_forum) $topic = get_topic($id);
		else $forum = get_forum($id);
?>
	<fieldset>
		<legend><?php $is_forum ? _e('Modify Forum Hide/Lock Status', ZA_VI_ID) : _e('Modify Topic Hide/Lock Status', ZA_VI_ID) ?></legend>
		<p><?php
		
		if($is_forum)
		{
			printf(__('Modify the visibility and lock status of the forum %s.', ZA_VI_ID),
				'<a href="' . get_forum_link($id) . '">' . $forum->forum_name . '</a>');

			$subforums = bb_get_forums($id);

			if($subforums !== FALSE)
			{
				$fc = count($subforums);
				$if = '';

				if($fc == 1)
				{
					$el = reset($subforums);
					$if = '<a href="';
					$if .= get_forum_link($el->forum_id);
					$if .= "\">$el->forum_name</a>";
				} else
				{
					$i = 0;

					foreach($subforums as $f)
					{
						$if .= '<a href="';
						$if .= get_forum_link($f->forum_id);
						$if .= "\">$f->forum_name</a>";

						if($i++ != $fc - 1) $if .= ', ';
					}
				}
				
				echo '</p><p>';
				
				$n = _n('This forum has one subforum: %s. Its hide rules will be reset.', 'This forum has %u subforums: %s. Their hide rules will be reset.', $fc);

				if($fc == 1) printf($n, $if);
				else printf($n, $fc, $if);
			}
		} else
		{
			$f = bb_get_forum(get_forum_id($topic->forum_id));

			/* Translators: The second `%s' is replaced by a username */
			printf(__('Modify the visibility and lock status of the topic %s started by %s on forum %s.', ZA_VI_ID),
			'<a href="' . get_topic_link($id) . '">' . get_topic_title($id) . '</a>',
			'<a href="' . get_user_profile_link($topic->topic_poster) . '">' . $topic->topic_poster_name . '</a>',
			'<a href="' . get_forum_link($topic->forum_id) . '">' . $f->forum_name . '</a>');
		}

		echo "</p>\n";

		if(!$is_forum)
		{
			$froles = $za_vi_settings['fhr'][$topic->forum_id];

			if(isset($froles))
			{
				echo "\t\t<p>", __('This topic is hidden by parent forum.', ZA_VI_ID), "</p>\n";
			}
		}
?>
		<div>
			<div class="label"><?php _e('Roles Hide Rules', ZA_VI_ID) ?></div>
			<div class="inputs">
<?php

		$fi = $is_forum ? 'fhr' : 'thr';
		$fh = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;

		foreach($roles as $i => $v)
		{ ?>
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="<?php
					echo "za_vi_hide_$i\"";

					if(isset($fh) && in_array($i, $fh)) echo ' checked="checked"';

					echo "/> $v";
					
					if($i == 'inactive') echo ' ', _e('(guest users)', ZA_VI_ID);
				
					if(!$is_forum)
					{
						if(isset($froles) && in_array($i, $froles))
							echo ' <em>', $hidden_suffix, '</em>';
						//if(isset($broles) && in_array($i, $broles)) echo $locked_suffix;
					}

					echo "</label>\n";
		} ?>
				<p><?php $is_forum ? _e('Specify the user roles for whom this forum is hidden.', ZA_VI_ID) : _e('Specify the user roles for whom this topic is hidden.', ZA_VI_ID); ?></p>
			</div>
		</div><?php

		$fi = $is_forum ? 'fhu' : 'thu';
		$hu = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;

		$hidden_users_names = array();
		
		if(isset($hu))
		{
			foreach($hu as $v)
			{
				$user = bb_get_user($v);

				if($user) $hidden_users_names[] = $user->user_login;
			}
		}

		bb_option_form_element('za_vi_hidden_users', array(
			'title' => __('Users Hide Rules', ZA_VI_ID),
			'value' => empty($hidden_users_names) ? '' : implode(', ', $hidden_users_names),
			'note' => $is_forum ? __('Specify the users for whom this forum is hidden. You must input a comma separated list of usernames.', ZA_VI_ID) : __('Specify the users for whom this topic is hidden. You must input a comma separated list of usernames.', ZA_VI_ID)));

		$fi = $is_forum ? 'fehu' : 'tehu';
		$hu = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;		
		$hidden_users_names = array();
		
		if(isset($hu))
		{
			foreach($hu as $v)
			{
				$user = bb_get_user($v);

				if($user) $hidden_users_names[] = $user->user_login;
			}
		}

		bb_option_form_element('za_vi_ex_hidden_users', array(
			'title' => __('Users Hide Rules Exceptions', ZA_VI_ID),
			'value' => empty($hidden_users_names) ? '' : implode(', ', $hidden_users_names),
			'note' => __('Specify the users that are not affected by above hide rules. You must input a comma separated list of usernames.', ZA_VI_ID)));
?>
		<div>
			<div class="label"><?php /* check */ $is_forum ? _e('Roles Lock Rules', ZA_VI_ID) : _e('Roles Lock Rules', ZA_VI_ID) ?></div>
			<div class="inputs">
<?php

		$fi = $is_forum ? 'flr' : 'tlr';
		$fb = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;

		foreach($roles as $i => $v)
		{ ?>
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="<?php
					echo "za_vi_lock_$i\"";

					if(isset($fb) && in_array($i, $fb)) echo ' checked="checked"';
					
					echo "/> $v";
					
					if($i == 'inactive') echo ' ', _e('(guest users)', ZA_VI_ID);
					
					echo "</label>\n";
		} ?>
				<p><?php $is_forum ? _e('Specify the user roles for whom this forum is locked.', ZA_VI_ID) : _e('Specify the user roles for whom this topic is locked.', ZA_VI_ID); ?></p>
			</div>
		</div><?php
		
		$fi = $is_forum ? 'flu' : 'tlu';
		$bu = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;

		$locked_users_names = array();
		
		if(isset($bu))
		{
			foreach($bu as $v)
			{
				$user = bb_get_user($v);

				if($user) $locked_users_names[] = $user->user_login;
			}
		}

		bb_option_form_element('za_vi_locked_users', array(
			/* Translators: The rules that prevent users to modify locked resources */
			'title' => __('Users Lock Rules', ZA_VI_ID),
			'value' => empty($locked_users_names) ? '' : implode(', ', $locked_users_names),
			'note' => $is_forum ? __('Specify the users for whom this forum is locked. You must input a comma separated list of usernames.', ZA_VI_ID) : __('Specify the users for whom this topic is locked. You must input a comma separated list of usernames.', ZA_VI_ID)));

		$fi = $is_forum ? 'felu' : 'telu';
		$bu = isset($za_vi_settings[$fi][$id]) ? $za_vi_settings[$fi][$id] : null;

		$locked_users_names = array();

		if(isset($bu))
		{
			foreach($bu as $v)
			{
				$user = bb_get_user($v);

				if($user) $locked_users_names[] = $user->user_login;
			}
		}
		
		bb_option_form_element('za_vi_ex_locked_users', array(
			/* Translators: The rules that prevent users to modify locked resources */
			'title' => __('Users Lock Rules Exceptions', ZA_VI_ID),
			'value' => empty($locked_users_names) ? '' : implode(', ', $locked_users_names),
			'note' => __('Specify the users thare are not affected by above lock rules. You must input a comma separated list of usernames.', ZA_VI_ID)));
?>
	</fieldset>
<?php
			$action_value = $is_forum ? 'update-za-vi-forum-settings' : 'update-za-vi-topic-settings';
		} else // Main settings page
		{ ?>
	<fieldset>
		<legend><?php _e('Edit Forums', ZA_VI_ID) ?></legend>
		<p><?php _e('This is the list of all your forums. Please notice that a subforum inherits hide rules from its parent forum.', ZA_VI_ID) ?><p>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Name', ZA_VI_ID) ?></th><th><?php _e('Description', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Name', ZA_VI_ID) ?></th><th><?php _e('Description', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</tfoot>
			<tbody><?php
		$forums = bb_get_forums();

		$vas = __('Visualize the forum', ZA_VI_ID);
		$ms = __('Modify', ZA_VI_ID);
		$mas = __('Modify the hide/lock status of the forum', ZA_VI_ID);
		$rs = __('Reset', ZA_VI_ID);
		$ras = __('Reset all the visibility rules', ZA_VI_ID);
		
		$hforums = array_unique(array_merge(array_keys($za_vi_settings['fhr']),
			array_keys($za_vi_settings['fhu']),
			array_keys($za_vi_settings['fehu']),

			array_keys($za_vi_settings['flr']),
			array_keys($za_vi_settings['flu']),
			array_keys($za_vi_settings['felu'])));
		
		$need_update = false;
			
		if(!empty($hforums))
		{
			$temp_f = array_keys($forums);
			
			foreach($hforums as $i => $v)
			{			
				if(!in_array($v, $temp_f))
				{
					unset($za_vi_settings['fhr'][$v]);
					unset($za_vi_settings['fhu'][$v]);
					unset($za_vi_settings['fehu'][$v]);

					unset($za_vi_settings['flr'][$v]);
					unset($za_vi_settings['flu'][$v]);
					unset($za_vi_settings['felu'][$v]);

					unset($hforums[$i]);
					
					$need_update = true;
				}
			}
		}
		
		if($need_update) bb_update_option('za_visibility', $za_vi_settings);
		
		foreach($forums as $id => $forum)
		{
			$fl = '<a href="' . get_forum_link($forum->forum_id) . "\" title=\"$vas\">$forum->forum_name</a>";
			$fs = '<a href="' . za_vi_admin_get_request(array('fid' => $forum->forum_id), 'za-vi-update') . "\" title=\"$mas\">$ms</a>";
			$tr_class = ' class="alt"';
			$fr = '';
			
			if(in_array($forum->forum_id, $hforums))
			{
				$fr = ' | <a href="' . za_vi_admin_get_request(array('fid' => $forum->forum_id, 'action' => 'reset'), 'za-vi-update') . "\" title=\"$ras\">$rs</a>";
				$tr_class = '';
			}

			echo "<tr$tr_class><td>$forum->forum_id</td><td>$fl</td><td>$forum->forum_desc</td><td>$fs$fr</td></tr>\n";
		}
?>
			</tbody>
		</table>
	</fieldset>
	<fieldset>
		<legend><?php _e('Edit Topics', ZA_VI_ID) ?></legend><?php

		$topics = array_unique(array_merge(array_keys($za_vi_settings['thr']),
			array_keys($za_vi_settings['thu']),
			array_keys($za_vi_settings['tehu']),
			array_keys($za_vi_settings['tlr']),
			array_keys($za_vi_settings['tlu']),
			array_keys($za_vi_settings['telu'])));
			
		$need_update = false;
		
		// We strip away deleted topics from Visibility settings
		foreach($topics as $i => $topic_id)
		{
			$topic = get_topic_id($topic_id);
			
			if($topic == 0)
			{
				unset($za_vi_settings['thr'][$topic_id]);
				unset($za_vi_settings['thu'][$topic_id]);
				unset($za_vi_settings['tehu'][$topic_id]);
				unset($za_vi_settings['tlr'][$topic_id]);
				unset($za_vi_settings['tlu'][$topic_id]);
				unset($za_vi_settings['telu'][$topic_id]);
				
				unset($topics[$i]);
				
				$need_update = true;
			}
		}
		
		if($need_update) bb_update_option('za_visibility', $za_vi_settings);

		if(za_vi_affected_topics_count())
		{
			 echo '<p>', __('This is the list of those topics that are affected by hide/lock own rules. You can handle the visibility of a topic through the link that you can find in administration menu at the bottom of its page.', ZA_VI_ID), '<p>';
?>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Title', ZA_VI_ID) ?></th><th><?php _e('Author', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Name', ZA_VI_ID) ?></th><th><?php _e('Description', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</tfoot>
			<tbody><?php
			$vas = __('Visualize the post', ZA_VI_ID);
			$mas = __('Modify the hide/lock status of the post', ZA_VI_ID);
			
			foreach($topics as $topic_id)
			{
				$topic = get_topic($topic_id);

				$tl = '<a href="' . get_topic_link($topic->topic_id) . "\" title=\"$vas\">$topic->topic_title</a>";
				$ts = '<a href="' . za_vi_admin_get_request(array('id' => $topic->topic_id), 'za-vi-update') . "\" title=\"$mas\">$ms</a>";
				$tr = '<a href="' . za_vi_admin_get_request(array('id' => $topic->topic_id, 'action' => 'reset'), 'za-vi-update') . "\" title=\"$ras\">$rs</a>";				

				echo "<tr class=\"alt\"><td>$topic->topic_id</td><td>$tl</td><td>$topic->topic_poster_name</td><td>$ts | $tr </td></tr>\n	";
			} ?>
			</tbody>
		</table><?php
		} else echo '<p>', __('There are no hidden/locked topics. You can handle the visibility of a topic through the administration menu at the bottom of the topic page.', ZA_VI_ID), '<p>'; ?>
	</fieldset>
	<fieldset>
		<legend><?php _e('Edit Profiles', ZA_VI_ID) ?></legend><?php
			$pl = get_user_profile_link(1);
			$apl = "<a href=\"$pl\">$pl</a>";
			echo '<p>', sprintf(__('This is the list of those profiles that are hidden. <strong>All Profile Pages</strong> is a special entry that can be used to apply hide rules to all profile pages. If a single profile page has its own set of hide rules then only those rules are applied. You can handle the visibility of a profile through the link that you can find in the profile page (e.g. %s.)', ZA_VI_ID), $apl), '<p>';
?>
		<table class="widefat">
			<thead>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Username', ZA_VI_ID) ?></th><th><?php _e('Name', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e('Unique ID', ZA_VI_ID) ?></th><th><?php _e('Username', ZA_VI_ID) ?></th><th><?php _e('Name', ZA_VI_ID) ?></th><th><?php _e('Actions', ZA_VI_ID) ?></th></tr>
			</tfoot>		
			<tbody><?php
			$vas = __('Visualize the profile page', ZA_VI_ID);
			$mas = __('Modify the hide status of the profile', ZA_VI_ID);

			if(za_vi_profile_has_visibility(0))
			{
				$con = ' | <a href="' . za_vi_admin_get_request(array('pid' => 0, 'action' => 'reset'), 'za-vi-update') . "\" title=\"$ras\">$rs</a>";
				$tr_class = '';
			} else
			{
				$con = '';
				$tr_class = ' class="alt"';
			}

			echo "<tr$tr_class><td colspan=\"3\"><strong>", __('All Profile Pages', ZA_VI_ID), '</strong></td><td><a href="',
				za_vi_admin_get_request(array('pid' => 0), 'za-vi-update') . "\" title=\"$mas\">$ms$con</a>";
				
				
			echo "</td></tr>\n";

			// ?
			$profiles = array_unique(array_merge(array_keys($za_vi_settings['phr']),
				array_keys($za_vi_settings['phu']),
				array_keys($za_vi_settings['pehu'])));
			
			foreach($profiles as $profile_id)
			{
				if($profile_id == 0) continue;

				$user = bb_get_user($profile_id);
				$pl = '<a href="' . get_user_profile_link($user->ID) . "\" title=\"$vas\">" . get_user_name($user->ID) . '</a>';
				$un = get_user_display_name($user->ID);
				$ts = '<a href="' . za_vi_admin_get_request(array('pid' => $profile_id), 'za-vi-update') . "\" title=\"$mas\">$ms</a>";
				$tr = '<a href="' . za_vi_admin_get_request(array('pid' => $profile_id, 'action' => 'reset'), 'za-vi-update') . "\" title=\"$ras\">$rs</a>";

				echo "<tr class=\"alt\"><td>$user->ID</td><td>$pl</td><td>$un</td><td>$ts | $tr </td></tr>\n	";
			}
?>
			</tbody>
		</table><?php
		echo "\t\t<p>", __('If a user try to access an hidden profile an error page is shown.', ZA_VI_ID),
			"</p>\n";
		bb_option_form_element('za_vi_profile_message', array(
			'title' => __('Custom Hidden Profile Message', ZA_VI_ID),
			'value' => $za_vi_settings['phm'],
			'note' => sprintf(__('Specify a custom message that will be displayed when a user access a profile that is hidden. If you leave this blank the default message "%s" will be used instead.', ZA_VI_ID), __('User Not Found.'))));
?>
	</fieldset>
	<fieldset>
		<legend><?php _e('Super Users/Roles', ZA_VI_ID) ?></legend>
		<?php /* Translators: a `role' is the role used by a user on a BBS. "Administrator" and "moderator" are examples of tipical roles. */
		echo "\t\t<p>", __('Super users and users that have super roles are not affected by hide/lock rules.', ZA_VI_ID),
			"</p>\n";
		
		$super_users_names = array();
		
		foreach($za_vi_settings['su'] as $v)
		{
			$user = bb_get_user($v);

			if($user) $super_users_names[] = $user->user_login;
		}

		bb_option_form_element('za_vi_super_users', array(
			'title' => __('Super Users', ZA_VI_ID),
			'value' => implode(', ', $super_users_names),
			'note' => __('Specify the super users. You must input a comma separated list of usernames.', ZA_VI_ID))); ?>

		<div>
			<div class="label"><?php _e('Super Roles', ZA_VI_ID) ?></div>
			<div class="inputs"><?php

		$sr = $za_vi_settings['sr'];

		foreach($roles as $i => $v)
		{ ?>
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="<?php
					echo "za_vi_super_role_$i\"";

					if(isset($sr) && in_array($i, $sr)) echo ' checked="checked"';
					
					echo "/> $v";

					if($i == 'inactive') echo ' ', _e('(guest users)', ZA_VI_ID);

					echo "</label>\n";
		} ?>
				<p><?php _e('Specify the super roles.', ZA_VI_ID); ?></p>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php _e('Title Prefixes', ZA_VI_ID) ?></legend>
		<?php /* Translators: a `role' is the role used by a user on a BBS. "Administrator" and "moderator" are examples of tipical roles. A `trailing space' is a space character appended after a string of text. Do not translate the text between the `%' characters */
		echo "\t\t<p>", __('You can specify a prefix that will be appended to hidden/locked forums/topics titles. <code>%status%</code> will be substituted by a brief string that summarizes the presence of visibility rules ("+hr" = hide rules, "+hu" = hide users, "-hu" = hide users exceptions, "+lr" = lock rules, "+lu" = lock users, "-lu" = lock users exceptions) and parent forum rules, if any, (same syntax but with capital letters.)', ZA_VI_ID),
			"</p>\n";

		bb_option_form_element('za_vi_forum_prefix', array(
			'title' => /* Translators: the string used as a prefix of the title of those forums that are hidden/locked */
				__('Forum Name Prefix', ZA_VI_ID),
			'value' => $za_vi_settings['fp']));

		bb_option_form_element('za_vi_topic_prefix', array(
			'title' => /* Translators: the string used as a prefix of the title of those posts that are hidden/locked */
				__('Topic Title Prefix', ZA_VI_ID),
			'value' => $za_vi_settings['tp']));

		$action_value = 'update-za-vi-settings'; ?>
	</fieldset>
	<?php za_vi_replacement_message_field(__('Replacement Messages', ZA_VI_ID),
		$za_vi_settings['flm'], $za_vi_settings['tlm']/*, $za_vi_settings['glm']*/);
	} // id
?>
	<fieldset class="submit">
		<?php bb_nonce_field('options-za-vi-update'); ?>
		<input type="hidden" name="action" value="<?php echo $action_value; ?>" />
		<?php if(isset($id)) echo "<input type=\"hidden\" name=\"entity_id\" value=\"$id\" />"; ?>
		<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes', ZA_VI_ID) ?>" />
	</fieldset>
</form>
<?php
}

function za_vi_configuration_page_add()
{
	bb_admin_add_submenu(ZA_VI_NAME, 'moderate', 'za_vi_configuration_page', 'options-general.php');
}

function za_vi_configuration_page_process()
{
	global $za_vi_settings, $wp_roles;
	$roles = $wp_roles->get_names();
	$changed = FALSE;

	$goback = remove_query_arg(array('za-visibility-updated', 'za-forum-reset',
		'za-topic-reset', 'za-allprofiles-reset', 'za-profile-reset',
		'za-zero-forums', 'id', 'fid', 'pid', 'action'), wp_get_referer());

	if('post' == strtolower($_SERVER['REQUEST_METHOD']))
	{
		bb_check_admin_referer('options-za-vi-update');
		//$goback = remove_query_arg(array('za-visibility-updated', 'za-zero-forums',
		//	'za-forum-reset', 'za-zero-forums'), wp_get_referer());
		
		if($_POST['action'] == 'update-za-vi-settings')
		{
			if(isset($_POST['za_vi_profile_message']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_profile_message']));
				$za_vi_settings['phm'] = $value;
			}

			if(isset($_POST['za_vi_forum_prefix']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_forum_prefix']));
				$za_vi_settings['fp'] = $value;
			}

			if(isset($_POST['za_vi_topic_prefix']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_topic_prefix']));
				$za_vi_settings['tp'] = $value;
			}
			
			if(isset($_POST['za_vi_forum_message']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_forum_message']));
				$za_vi_settings['flm'] = $value;
			}

			if(isset($_POST['za_vi_topic_message']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_topic_message']));
				$za_vi_settings['tlm'] = $value;
			}

			/*if(isset($_POST['za_vi_tag_message']))
			{
				$value = stripslashes_deep(trim($_POST['za_vi_tag_message']));
				$za_vi_settings['glm'] = $value;
			}*/

			$new_super_roles = array();

			foreach($roles as $i => $v)
			{
				if(isset($_POST["za_vi_super_role_$i"]) && $_POST["za_vi_super_role_$i"] == 'on')
					$new_super_roles[] = $i;
			}
			
			$za_vi_settings['sr'] = $new_super_roles;
			$za_vi_settings['su'] = za_vi_explode_user_list($_POST['za_vi_super_users']);

			bb_update_option('za_visibility', $za_vi_settings);

			$goback = add_query_arg('za-visibility-updated', 'true', $goback);
			bb_safe_redirect($goback);
			exit;
		} else
		{
			if($_POST['action'] == 'update-za-vi-forum-settings')
				$entity = 0;
			elseif($_POST['action'] == 'update-za-vi-topic-settings')
				$entity = 1;
			elseif($_POST['action'] == 'update-za-vi-allprofiles-settings')
				$entity = 2;
			else $entity = 3;
			
			if(isset($_POST['entity_id'])) $id = absint($_POST['entity_id']); 

			if(!isset($id) || !is_numeric($id))
			{
				wp_redirect(bb_get_uri(null, null, BB_URI_CONTEXT_HEADER));
				exit;
			}
			
			if($entity == 0)
			{
				$forum = get_forum($id);
				if(!$forum) bb_die(__('There is a problem with that forum, pardner.', ZA_VI_ID));			
			} elseif($entity == 1)
			{
				$topic = get_topic($id);
				if(!$topic) bb_die(__('There is a problem with that topic, pardner.', ZA_VI_ID));
			} elseif($entity == 2)
			{
				if($id != 0)
				{
					$user = bb_get_user($id);
					if(!$user) bb_die(__('There is a problem with that user, pardner.', ZA_VI_ID));
				}
			}
			
			$has_lock = $entity == 0 || $entity == 1;

			$new_hide_roles = array();
			if($has_lock) $new_lock_roles = array();

			foreach($roles as $i => $v)
			{
				if($_POST["za_vi_hide_$i"] == 'on') $new_hide_roles[] = $i;
				if($has_lock && $_POST["za_vi_lock_$i"] == 'on') $new_lock_roles[] = $i;
			}

			if($entity == 0) $p = 'f';
			elseif($entity == 1) $p = 't';
			else $p = 'p';

			// Single profile rules can be empty
			if($user) $za_vi_settings[$p . 'hr'][$id] = $new_hide_roles;
			else
			{
				// Save new hide roles
				if(!empty($new_hide_roles))
					$za_vi_settings[$p . 'hr'][$id] = $new_hide_roles;
				else unset($za_vi_settings[$p . 'hr'][$id]);
			}

			// Save new hide users			
			$list1 = za_vi_explode_user_list($_POST['za_vi_hidden_users']);
			
			if($user) $za_vi_settings[$p . 'hu'][$id] = $list1;
			else
			{
				if(!empty($list1)) $za_vi_settings[$p . 'hu'][$id] = $list1;
				else unset($za_vi_settings[$p . 'hu'][$id]);
			}

			// Save new hide exception users
			$list2 = za_vi_explode_user_list($_POST['za_vi_ex_hidden_users']);
			
			if($user) $za_vi_settings[$p . 'ehu'][$id] = $list2;
			else
			{
				if(!empty($list2)) $za_vi_settings[$p . 'ehu'][$id] = $list2;
				else unset($za_vi_settings[$p . 'ehu'][$id]);
			}
			
			if($entity == 0)
			{
				za_vi_reset_subforums($id);

				//$subforums = bb_get_forums($id);
				
				/*if($subforums !== FALSE)
				{
					foreach($subforums as $f)
					{
						if(isset($za_vi_settings['fhr'][$id]))
							$za_vi_settings['fhr'][$f->forum_id] = $za_vi_settings['fhr'][$id];
						else unset($za_vi_settings['fhr'][$f->forum_id]);

						if(isset($za_vi_settings['fhu'][$id]))
							$za_vi_settings['fhu'][$f->forum_id] = $za_vi_settings['fhu'][$id];
						else unset($za_vi_settings['fhu'][$f->forum_id]);

						if(isset($za_vi_settings['fehu'][$id]))
							$za_vi_settings['fehu'][$f->forum_id] = $za_vi_settings['fehu'][$id];
						else unset($za_vi_settings['fehu'][$f->forum_id]);
					}
				}*/
			}

			// Save new lock roles
			if($has_lock)
			{
				if(!empty($new_lock_roles))
				{
					$za_vi_settings[$p . 'lr'][$id] = $new_lock_roles;
				} else unset($za_vi_settings[$p . 'lr'][$id]);

				// Save new lock users			
				$list = za_vi_explode_user_list($_POST['za_vi_locked_users']);

				if(!empty($list)) $za_vi_settings[$p . 'lu'][$id] = $list;
				else unset($za_vi_settings[$p . 'lu'][$id]);

				// Save new lock exception users
				$list = za_vi_explode_user_list($_POST['za_vi_ex_locked_users']);

				if(!empty($list)) $za_vi_settings[$p . 'elu'][$id] = $list;
				else unset($za_vi_settings[$p . 'elu'][$id]);
			}

			if($entity == 0)
			{
				if(!za_vi_forum_has_visibility($id))
				{
					za_vi_reset_forum($id);
					$goback = add_query_arg('za-forum-reset', 'true', $goback);
				} else
				{
					$num_forum = get_total_forums();
					
					// zero forums aren't allowed
					if($num_forum == 1 && (!empty($za_vi_settings['fhu'][$id]) || !empty($za_vi_settings['fhr'][$id])))
					{
						unset($za_vi_settings['fhu'][$id]);
						unset($za_vi_settings['fhr'][$id]);
						$goback = add_query_arg('za-zero-forums', 'true', $goback);
					} elseif(!empty($za_vi_settings['fhr']) && count($za_vi_settings['fhr']) == $num_forum)
					{
						foreach($za_vi_settings['fhr'] as $i => $v)
						{
							// check
						}
					} else $goback = add_query_arg('za-visibility-updated', 'true', $goback);
				}
			} elseif($entity == 1)
			{
				if(!za_vi_topic_has_visibility($id))
				{
					za_vi_reset_topic($id);
					$goback = add_query_arg('za-topic-reset', 'true', $goback);
				} else $goback = add_query_arg('za-visibility-updated', 'true', $goback);
			} else
			{
				if(!za_vi_profile_has_visibility($id))
				{
					$goback = add_query_arg($id == 0 ? 'za-allprofiles-reset' : 'za-profile-reset', 'true', $goback);
				} else $goback = add_query_arg('za-visibility-updated', 'true', $goback);
			}

			bb_update_option('za_visibility', $za_vi_settings);
			bb_safe_redirect($goback);
			exit;
		}
	}

	if(!empty($_GET['za-visibility-updated']))
		bb_admin_notice('<strong>' . __('Settings saved.', ZA_VI_ID) . '</strong>');
	elseif(!empty($_GET['za-zero-forums']))
		bb_admin_notice(__('You have only one forum and you can\'t hide it. bbPress assumes that there is <strong>at least one forum</strong> and it spawns an error when there are no forums to display. In order to avoid this problem the hide rules that you have specified have not been registered.', ZA_VI_ID) . '</strong>', 'error');
	elseif(!empty($_GET['za-forum-reset']))
		bb_admin_notice('<strong>' . __('Forum hide/lock status erased.', ZA_VI_ID) . '</strong>');
	elseif(!empty($_GET['za-topic-reset']))
		bb_admin_notice('<strong>' . __('Topic hide/lock status erased.', ZA_VI_ID) . '</strong>');
	elseif(!empty($_GET['za-allprofiles-reset']))
		bb_admin_notice('<strong>' . __('All profiles hide status erased.', ZA_VI_ID) . '</strong>');
	elseif(!empty($_GET['za-profile-erase']))
		bb_admin_notice('<strong>' . __('Profile hide status erased.', ZA_VI_ID) . '</strong>');
	elseif(!empty($_GET['za-profile-reset']))
		bb_admin_notice('<strong>' . __('The profile hide status has been reset. Take note that a profile with no hide rules is still a valid entry because single profile rules override global rules. If you want to delete these rules eliminate the profile from the plugin main settings panel.', ZA_VI_ID) . '</strong>');
	elseif(isset($_GET['id']) || isset($_GET['fid']) || isset($_GET['pid']))
	{
		bb_check_admin_referer('za-vi-update');

		global $bb_current_user;
	
		if(isset($_GET['id'])) $id = absint($_GET['id']);
		elseif(isset($_GET['fid'])) $id = absint($_GET['fid']);
		else $id = absint($_GET['pid']);

		if(!isset($id) || !is_numeric($id) ||
			!isset($bb_current_user) || !$bb_current_user->has_cap('administrate'))
		{
			wp_redirect(bb_get_uri(null, null, BB_URI_CONTEXT_HEADER));
			exit;
		}

		//bb_check_admin_referer('manage-topic_' . $id);
		
		$is_reset = (bool)(isset($_GET['action']) && strtolower($_GET['action']) == 'reset');
		
		if(isset($_GET['id']))
		{
			$topic = get_topic($id);
			if(!$topic) bb_die(__('There is a problem with that topic, pardner.', ZA_VI_ID));
			
			if($is_reset)
			{
				za_vi_reset_topic($id);
				bb_update_option('za_visibility', $za_vi_settings);

				$goback = add_query_arg('za-topic-reset', 'true', $goback);
				bb_safe_redirect($goback);
				exit;
			}
		} elseif(isset($_GET['fid']))
		{
			$forum = get_forum($id);
			if(!$forum) bb_die(__('There is a problem with that forum, pardner.', ZA_VI_ID));

			if($is_reset)
			{
				za_vi_reset_forum($id);
				bb_update_option('za_visibility', $za_vi_settings);

				$goback = add_query_arg('za-forum-reset', 'true', $goback);
				bb_safe_redirect($goback);
				exit;
			}
		} elseif(isset($_GET['pid']))
		{
			if($id != 0)
			{
				$user = bb_get_user($id);
				if(!$user) bb_die(__('There is a problem with that user, pardner.', ZA_VI_ID));
			}

			if($is_reset)
			{
				za_vi_reset_profile($id);
				bb_update_option('za_visibility', $za_vi_settings);

				$goback = add_query_arg($id == 0 ? 'za-allprofiles-reset' :
					'za-profile-erase', 'true', $goback);
				bb_safe_redirect($goback);
				exit;
			}
		}
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}

?>