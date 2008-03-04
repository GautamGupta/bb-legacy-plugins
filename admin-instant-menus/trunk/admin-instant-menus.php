<?php
/*
Plugin Name: Admin Instant Menus
Plugin URI: http://bbpress.org/plugins/topic/95
Description: Makes the third row of options in the admin menu instantly available without extra clicks. Conversion with code and CSS tweaks from WordPress based on <a href="http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css/">Ozh</a>'s original. 
Version: 1.01
Author: _ck_
Author http://bbShowcase.org

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

To Do: 
* once 0.8.4 has been in wide release for awhile, wrap everything with BB_IS_ADMIN
* option for larger fonts
* option for dropdown instead of slider-style
*/

add_action('bb_admin_footer', 'admin_instant_menus');

/* Main function : creates the new set of intricated <ul> and <li> */
function admin_instant_menus() {	

	$bb_menu = admin_instant_menus_build();

	$ozh_menu = '';
	$ie_code = '';
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')  && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') )
		$ie_code = " onmouseover='this.className=\\\"msieFix\\\"' onmouseout='this.className=\\\"\\\"'";

	foreach ($bb_menu as $k=>$v) {
		$url 	= $v['url'];
		$name 	= $k;
		$anchor = $v['name'];
		$class	= $v['class'];
		
		$ozh_menu .= '<li'.$ie_code."><a href='$url'$class>$anchor</a>";
		if (is_array($v['sub'])) {

			$ulclass='';
			if ($class) $ulclass = " class='ulcurrent'";
			$ozh_menu .= "<ul$ulclass>";

			foreach ($v['sub'] as $subk=>$subv) {
				$suburl = $subv['url'];
				$subanchor = $subv['name'];
				$subclass='';
				if (array_key_exists('class',$subv)) $subclass=$subv['class'];
				$ozh_menu .= "<li><a href='$suburl'$subclass>$subanchor</a></li>";
			}
			$ozh_menu .= "</ul>";
		} else {
			$ozh_menu .= "<ul><li class='altmenu_empty' title='This menu has no sub menu'><small>&#8230;</small></li><li style='padding:0 300px'>&nbsp;</li></ul>";
		}
		$ozh_menu .="</li> ";	

	}
	admin_instant_menus_css();
	admin_instant_menus_old_printjs($ozh_menu);
}

/* Core stuff : builds an array populated with all the infos needed for menu and bb-admin-submenu */
function admin_instant_menus_build () {	
	global $bb_menu, $bb_submenu, $bb_current_menu, $bb_current_submenu, $plugin_page, $pagenow;

	/* Most of the following garbage are bits from admin-header.php,
	 * modified to populate an array of all links to display in the menu
	 */

	$self = preg_replace('|^.*/bb-admin/|i', '', $_SERVER['PHP_SELF']);
	$self = preg_replace('|^.*/plugins/|i', '', $self);

	// bb_get_admin_page_parent();
	$altmenu = array();

	/* Step 1 : populate first level menu as per user rights */

	foreach ($bb_menu as $item) {
		// 0 = name, 1 = capability, 2 = file
		if ( bb_current_user_can($item[1]) ) {
			if ( file_exists(ABSPATH . "my-plugins/{$item[2]}") )
				$altmenu[$item[2]]['url'] = bb_get_option('uri') . "bb-admin/admin-base.php?page={$item[2]}";
			else
				$altmenu[$item[2]]['url'] = bb_get_option('uri') . "bb-admin/{$item[2]}";

			if ($item[2]==$bb_current_menu[2] )
				$altmenu[$item[2]]['class'] = " class='current'";

			$altmenu[$item[2]]['name'] = $item[0];

			/* Windows installs may have backslashes instead of slashes in some paths, fix this */
			$altmenu[$item[2]]['name'] = str_replace(chr(92),chr(92).chr(92),$altmenu[$item[2]]['name']);
		}
	}

	/* Step 2 : populate second level menu */
	foreach ($bb_submenu as $k=>$v) {
		foreach ($v as $item) {
			if (array_key_exists($k,$altmenu) and bb_current_user_can($item[1])) {

				// What's the link ?
				$bb_menu_hook = bb_get_admin_tab_link($item);
				/*
				if (file_exists(ABSPATH . "my-plugins/{$item[2]}") || ! empty($bb_menu_hook)) {
					if ( 'admin.php' == $pagenow )
						$link = bb_get_option('uri') . "bb-admin/admin-base.php?page={$item[2]}";
					else
						$link = bb_get_option('uri') . "bb-admin/{$k}?page={$item[2]}";
				} else {
					$link = bb_get_option('uri') . "bb-admin/{$item[2]}";
				}
				*/
				$link = bb_get_option('uri') . "bb-admin/$bb_menu_hook";

				/* Windows installs may have backslashes instead of slashes in some paths, fix this */
				$link = str_replace(chr(92),chr(92).chr(92),$link);

				$altmenu[$k]['sub'][$item[2]]['url'] = $link;

				// Is it current page ?
				
				if ($item[2]==$bb_current_submenu[2] ) {								
					$altmenu[$k]['sub'][$item[2]]['class'] = " class='current'";
					$altmenu[$k]['class'] = " class='current'";
				}

				// What's its name again ?
				$altmenu[$k]['sub'][$item[2]]['name'] = $item[0];
			}
		}
	}

	return ($altmenu);
}

/* The javascript bits that replace the existing menu by our new one */
function admin_instant_menus_old_printjs ($admin = '') {
	print "<script type=\"text/javascript\">";		
	print "document.getElementById('bb-admin-menu').innerHTML=\"$admin\";";
	print "if (!document.getElementById('bb-admin-submenu')) {newUL = document.createElement(\"UL\"); newUL.setAttribute(\"id\", \"bb-admin-submenu\"); document.getElementById('bb-admin-menu').parentNode.insertBefore(newUL, document.getElementById('bb-admin-menu').nextSibling);	document.getElementById('bb-admin-submenu').innerHTML=\"<li class='altmenu_empty' title='This menu has no sub menu'><small>&#8230;</small></li><li style='padding:0 300px'>&nbsp;</li>\"; }";
	// print "else {document.getElementById('bb-admin-submenu').innerHTML=\"<li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li>\";}";
	print "</script>";
}

/* Print the CSS stuff. Modify with care if you want to customize colors ! */
function admin_instant_menus_css() {
	$id = '#bb-admin-menu';

	print <<<CSS
	<style>	
	$id {
		height: 1.8em;		
	}
	$id .current {
		background: #ddf4ea;
		color: #333;
		font-weight: bold;
	}			
	#bb-admin-submenu {
		height: 1.8em;
		line-height:170%;		
	}		
	$id li ul li {
		float: left;
		padding:0 5px 0 0;
		margin: 0;		
	}
	$id li ul li a, $id li ul li a:link, $id li ul li a:visited {		
		font-size: 12px;
		border-bottom: none;		
		padding: .3em .4em .33em;
		color:#fff;
	}
	$id li ul li a:hover {
		background: #ddf4ea;
		color: #393939;
	}
	/* Nested ULs */
	$id li ul {
		position:absolute;
		left: -3000px;
		background: #0D4F32;				
		margin:0;
	}
	$id li:hover ul,$id li.msieFix ul {
		left:0px;
		z-index:90;
		right:0px;
	}
	$id li .ulcurrent {
		left:0px;
		right:0px;
		z-index:89;
		width:auto;
	}
	$id li ul li a.current, $id li ul li a.current:link, $id li ul li a.current:visited {	
		background: #ddf4ea;
		border-top: 1px solid #049052;
		border-right: 2px solid #049052;
		color: #000000;
		font-weight:bold;
		}
		
	.altmenu_empty {
		color: #fff;
		font-weight:bold;	
	}
CSS;
if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ) { // && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') 
print <<<CSS
	$id li .ulcurrent {
		width:200%; 	
	}
	$id li a {
		margin-right:0.3em;
	}	
	$id li.msieFix ul, {
		margin:2em 0;
		width:300%;
		left:0;
	}
	$id li ul {
		margin:2em 0;
		padding-left: 3em;
	}	
CSS;
}
print "	</style>";

}

?>