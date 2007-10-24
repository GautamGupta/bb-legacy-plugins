<?php
/*
Plugin Name: Admin Drop Menus
Plugin URI: http://www.yellowswordfish.com/index.php?pagename=admin-drop-menus-wordpress-plugin
Description: Create Drop Down Menus for WordPress Admin Panels
Version: 2.0.5
Author: Andy Staines
Author URI: http://www.yellowswordfish.com
BBPress Version: 2.0
*/

/*  Copyright 2005/2006 Andy Staines

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Set the stylesheet to replace wp-admin.css
// ==========================================


function aws_header() 
{
	global $is_winIE;
	global $plugin_uri;

	$dir = basename(dirname(__FILE__));
	$plugin_uri= bb_get_option('uri') . 'my-plugins/' . $dir;
	
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . '/bb-admin.css?version=2.0.2" />' . "\n";
	
	if ($is_winIE)
	{
		echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . '/bb-admin-ie6.css?version=2.0.2" />';
	}
}

// Main function : creates the new set of <ul> and <li> for the admin menus
// ========================================================================

function aws_adminmenu() 
{
	global $is_winIE;
	global $plugin_uri;
	global $ipath;

	$ipath = $plugin_uri . '/images';

	$menu = aws_adminmenu_build();
	$menu = aws_check_orphans($menu);

	$adaut_menu = '';
	$printsub = 1;
	$iecode='';
	
	foreach ($menu as $k=>$v) 
	{
		$url 	= $v['url'];
		$anchor = str_replace('"', '\"', $v['name']);
		$class	= $v['class'];

		$iecode = '';
		$adaut_menu .= '<li><a' . $iecode . " href='$url'$class>$anchor";
		
		if ($is_winIE)
		{
			$adaut_menu .= '<table><tr><td>';
		} else {
			$adaut_menu .= '</a><table><tr><td>';
		}

		if (is_array($v['sub'])) 
		{
			$ulclass='';
			if ($class) $ulclass = " class='ulcurrent'";
			$adaut_menu .= "<ul$ulclass>";

			foreach ($v['sub'] as $subk=>$subv) 
			{
				$suburl = $subv['url'];
				$subanchor = str_replace('"', '\"', $subv['icon'] . '' . $subv['name']);
				$subclass='';
				if (array_key_exists('class',$subv)) $subclass=$subv['class'];
				$adaut_menu .= "<li><a href='$suburl'$subclass>$subanchor</a></li>";
			}
			$adaut_menu .= "</ul>";
		} else {
			$adaut_menu .= "<ul><li></li></ul>";
			if ($class) $printsub = 0;
		}
		
		if ($is_winIE)
		{
			$adaut_menu .= "</td></tr></table></a></li>";
		}
		
		$adaut_menu .="</td></tr></table></li> ";			
	}
	$adaut_menu .= '<li><a href=\'' . bb_get_option('uri') . '\'>Site</a></li>';
	
	aws_adminmenu_printjs($adaut_menu, $printsub);
}

/* Core stuff : builds an array populated with all the infos needed for menu and submenu */
function aws_adminmenu_build () 
{
	global $bb_menu, $bb_submenu, $bb_current_menu, $bb_current_submenu;
	$menu = $bb_menu;
	$submenu = $bb_submenu;
	
	global $plugin_page, $pagenow;
	global $ipath;

	$self = preg_replace('|^.*/bb-admin/|i', '', $_SERVER['PHP_SELF']);
	$self = preg_replace('|^.*/my-plugins/|i', '', $self);

//	get_admin_page_parent();
	$altmenu = array();

	/* Step 1 : populate first level menu as per user rights */
	foreach ($menu as $item) 
 	{
		// 0 = name, 1 = capability, 2 = file
		if ( bb_current_user_can($item[1]) ) 
		{
			$sys_menu_file = $item[2];

			if ( file_exists(ABSPATH . "my-plugins/{$item[2]}") )
			{
				$altmenu[$sys_menu_file]['url'] = bb_get_option('uri') . "bb-admin/admin-base.php?plugin={$item[2]}";
			} else {
				$altmenu[$sys_menu_file]['url'] = bb_get_option('uri') . "bb-admin/{$item[2]}";
			}
			if (( strcmp($self, $item[2]) == 0 && empty($parent_file)) || ($parent_file && ($item[2] == $parent_file)))
			$altmenu[$sys_menu_file]['class'] = " class='current'";
			$altmenu[$sys_menu_file]['name'] = $item[0];
		}
	}

	/* Step 2 : populate second level menu */

	foreach ($submenu as $k=>$v)
	{
		foreach ($v as $item) 
		{
			if (array_key_exists($k,$altmenu) and bb_current_user_can($item[1])) 
			{
				// What's the link ?

				$menu_hook = bb_get_admin_tab_link($item);
				$icon = aws_add_icons($item[0]);

					$mtype = "<img src='$ipath/$icon' height='16' width='16' alt=''/>&nbsp;"; 
					$link = bb_get_option('uri') . "bb-admin/$menu_hook";

				$altmenu[$k]['sub'][$item[2]]['url'] = $link;
				
				// Is it current page ?
				$class = '';
				if ( (isset($plugin_page) && $plugin_page == $item[2] && $pagenow == $k) || (!isset($plugin_page) && $self == $item[2] ) ) $class=" class='current'";
				if ($class) 
				{
					$altmenu[$k]['sub'][$item[2]]['class'] = $class;
					$altmenu[$k]['class'] = $class;
				}
				// What's its name again ?
				$altmenu[$k]['sub'][$item[2]]['name'] = $item[0];
				$altmenu[$k]['sub'][$item[2]]['icon'] = $mtype;	
			}
		}
	}
	return ($altmenu);
}

// Support Routines:
// ===========================================================================
/* The javascript bits that replace the existing menu by our new one */
function aws_adminmenu_printjs ($admin = '', $sub = 1) 
{
	print "<script>
	document.getElementById('bb-admin-menu').innerHTML=\"$admin\";";
	if ($sub) print "document.getElementById('bb-admin-submenu').innerHTML=\"<li>&nbsp;</li>\"";
	print "</script>";
}

function aws_top_menu_plugin($menuname)
{
	if(strpos(' Dashboard Write Manage Links Presentation Plugins Users Options Import', $menuname))
	{
		return true;
	}
	return false;
}

// if any top level menus have no submenu then adds the single menu item as a sub (for IE table fix)
function aws_check_orphans($menu)
{
	global $ipath;
	
	foreach ($menu as $k=>$v)
	{
		if (!is_array($v['sub'])) 
		{
			$menu[$k]['sub'][$k]['url'] = $v['url'];
			$menu[$k]['sub'][$k]['name'] = $v['name'];
			$icon = aws_add_icons($v['name']);
			$menu[$k]['sub'][$k]['icon'] = "<img src='$ipath/$icon' height='16' width='16' alt=''/>&nbsp;";
		}
	}
	return $menu;
}

// add the icons to the sub menu items
function aws_add_icons($menuitem)
{
	switch(substr($menuitem, 0, 21))
	{
		case __('Dashboard'):
			$i = "information.png";
			break;
		case __('Find'):
			$i = "zoom.png";
			break;
		case __('Moderators'):
			$i = "user.png";
			break;			
		case __('Blocked'):
			$i = "plugin.png";
			break;
		case __('Topics'):
			$i = "folder_page.png";
			break;			
		case __('Posts'):
			$i = "comments.png";
			break;
		case __('Forums'):
			$i = "basket_edit.png";
			break;
			break;
		case __('Themes'):
			$i = "layout.png";
			break;
		case __('Plugins'):
			$i = "plugin.png";
			break;
		case __('Recount'):
			$i = "plugin.png";
			break;

/*
		case __('Write Post'):
			$i = "email_edit.png";
			break;
		case __('Write Page'):
			$i = "page_edit.png";
			break;
		case __('Posts'):
			$i = "folder_table.png";
			break;
		case __('Pages'):
			$i = "folder_page.png";
			break;
		case __('Categories'):
			$i = "basket_edit.png";
			break;
		case __('Comments'):
			$i = "comments.png";
			break;
		case substr(__('Awaiting Moderation (%s)'),0,21):
			$i = "comment_add.png";
			break;
		case __('Files'):
			$i = "brick_edit.png";
			break;
		case __('Manage Links'):
			$i = "application_link.png";
			break;
		case __('Add Link'):
			$i = "link_add.png";
			break;
		case __('Link Categories'):
			$i = "calendar_link.png";
			break;
		case __('Import Links'):
			$i = "folder_link.png";
			break;
		case __('Themes'):
			$i = "layout.png";
			break;
		case __('Theme Editor'):
			$i = "layout_edit.png";
			break;
		case __('Plugins'):
			$i = "plugin_add.png";
			break;
		case __('Plugin Editor'):
			$i = "plugin_edit.png";
			break;
		case __('Your Profile'):
			$i = "user.png";
			break;
		case __('Authors &amp; Users'):
			$i = "group_key.png";
			break;
		case __('General'):
			$i = "application_view_list.png";
			break;
		case __('Writing'):
			$i = "pencil.png";
			break;
		case __('Reading'):
			$i = "zoom.png";
			break;
		case __('Discussion'):
			$i = "group_link.png";
			break;
		case __('Permalinks'):
			$i = "book_link.png";
			break;
		case __('Miscellaneous'):
			$i = "bullet_wrench.png";
			break;
		case __('Import'):
			$i = "report_add.png";
			break;
*/
			default: 
			$i = "plugin.png";
			break;
	}
	if (substr($menuitem,0,7) == substr(__('Authors &amp; Users'),0,7))
	{
		$i = "group_key.png";
	}
	return $i;
}

// wp action hooks
// ====================================


/*
add_action('admin_head', 'aws_header');
add_action('admin_footer', 'aws_adminmenu');
*/

add_action('bb_admin_head', 'aws_header');
add_action('bb_admin_footer', 'aws_adminmenu');


?>