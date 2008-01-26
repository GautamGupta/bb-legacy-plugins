<?php

/*
Plugin Name: bbPress Theme Switcher
Plugin URI: http://bbpress.org/
Description: Allows your users and guests to switch themes.
Version: 1.08
Author: _ck_
Author URI:  http://bbshowcase.org

Adapted from Ryan Boren WordPress theme switcher which was adapted from Alex King's WordPress style switcher http://www.alexking.org/software/wordpress/

To use, add the following to your sidebar menu:

  <li>Themes:
	<?php bb_theme_switcher(); ?>
  </li>

This will create a list of themes for your readers to select.

If you would like a dropdown box rather than a list, add this:

  <li>Themes:
	<?php bb_theme_switcher('dropdown'); ?>
  </li>

*/ 

add_filter('bb_template','bb_ts_add_dropdown',100,2);    //  disable this line if you don't want the switcher inserted automatically
add_filter('bb_get_active_theme_folder','bb_ts_get_template');
add_filter('bb_get_active_theme_uri', 'bb_ts_get_active_theme_uri');

$bbhash=$bb->wp_siteurl ? md5($bb->wp_siteurl) : md5($bb_table_prefix);   // $bbhash is not available before plugins load in 0.8.2.x :-(
bb_ts_set_theme_cookie();

function bb_ts_add_dropdown($template='',$file='') {
global $bb_ts_add_dropdown; 
if ($file=='' || ($file=="footer.php" && !$bb_ts_add_dropdown)){
$bb_ts_add_dropdown=true;
echo '<div style="position:relative;clear:right;padding-right:1em;white-space:nowrap;text-align:right;">'.__('Theme Switcher').': ';bb_theme_switcher('dropdown'); echo '</div>';
}
return $template;	
} 

function bb_ts_set_theme_cookie() { global $bbhash;
	$expire = time() + 180;  				// 180 set for 3 minute demo timeout - increase  want a longer timeout 
	if (!empty($_GET["bbtheme"])) {		
		if ( bb_get_option( 'cookiedomain' ) ) {
		setcookie( "bb_theme_".$bbhash, stripslashes($_GET["bbtheme"]), $expire, bb_get_option( 'cookiepath' ), bb_get_option( 'cookiedomain' ) );}
		else {setcookie( "bb_theme_".$bbhash, stripslashes($_GET["bbtheme"]), $expire, bb_get_option( 'cookiepath' ) );}
				
		$redirect = bb_get_option( 'uri' );
		if (function_exists('bb_redirect'))
			bb_redirect($redirect);
		else
			header("Location: ". $redirect);

		exit;
	}
}

function bb_ts_get_theme() { global $bbhash;
	if (!empty($_COOKIE["bb_theme_".$bbhash])) {
		return $_COOKIE["bb_theme_".$bbhash];
	}	else {
		return '';
	}
}

function bb_ts_get_active_theme_uri($uri) {
	$theme = bb_ts_get_theme(); 
	if (empty($theme)) {return $uri;}
	$active_uri=bb_get_theme_uri(bb_get_one_theme($theme));	
	if ($active_uri) {return $active_uri;} else {return $uri;}
}

function bb_get_one_theme($theme) {
	$themes = bb_get_themes();
	if (array_key_exists($theme, $themes)) {return $themes[$theme];}	
	return NULL;
}

function bb_ts_get_template($template) {
	$theme = bb_ts_get_theme();

	if (empty($theme)) {
		return $template;
	}

	$theme = bb_get_one_theme($theme);
	
	if (empty($theme)) {
		return $template;
	}

	// Don't let people peek at unpublished themes.
	// if (isset($theme['Status']) && $theme['Status'] != 'publish')
	// 	return $template;		

	return $theme;
}


function bb_theme_switcher($style = "text") { global $bbhash;
	$themes = bb_get_themes();

	$default_theme = array_search(bb_get_option('bb_active_theme'),$themes);  //  get_current_theme();

	if (count($themes) > 1) {
		$theme_names = array_keys($themes);
		natcasesort($theme_names);
		
		if ($style == 'dropdown') {
			$ts = '<span id="themeswitcher">'."\n";		

			$ts .=  // '<li>'."\n"
				 '	<select style="width:150px;" name="themeswitcher" onchange="location.href=\''.bb_get_option( 'uri' ).'?bbtheme=\' + this.options[this.selectedIndex].value;">'."\n"	;

			foreach ($theme_names as $theme_name) {
				// Skip unpublished themes.
				// if (isset($themes[$theme_name]['Status']) && $themes[$theme_name]['Status'] != 'publish')
				//	continue;
					
				if ((!empty($_COOKIE["bb_theme_".$bbhash]) && $_COOKIE["bb_theme_".$bbhash] == $theme_name) 
						|| (empty($_COOKIE["bb_theme_".$bbhash]) && ($theme_name == $default_theme))) 
							{$selected='" selected="selected" ';} else {$selected='';}
				
				$display = explode("/",trim($theme_name," /")); $display = end($display); 	// lazy fix for build >1000 with full path names								
				$display = str_replace(array("Bb ","Bbpress"," For "),array("bb ","bbPress"," for "),htmlspecialchars(ucwords(str_replace("-"," ",$display))));
												
				$ts .= '<option style="text-indent: 1em;padding:2px" value="'.$theme_name.'"'.$selected.'>'. $display.'</option>'."\n";
								
			}
			$ts .= '	</select></span>'."\n";
				// . '</li>'."\n";
		}	else {
			$ts = '<ul id="themeswitcher">'."\n";		
			foreach ($theme_names as $theme_name) {
				// Skip unpublished themes.
				// if (isset($themes[$theme_name]['Status']) && $themes[$theme_name]['Status'] != 'publish')
				//	continue;

				// $display = htmlspecialchars(ucfirst(str_replace("-"," ",$theme_name)));
				
				$display = explode("/",trim($theme_name," /")); $display = end($display); 	// lazy fix for build >1000 with full path names								
				$display = str_replace(array("Bb ","Bbpress"," For "),array("bb ","bbPress"," for "),htmlspecialchars(ucwords(str_replace("-"," ",$display))));
				
				if ((!empty($_COOKIE["bb_theme_".$bbhash]) && $_COOKIE["bb_theme_".$bbhash] == $theme_name)
						|| (empty($_COOKIE["bb_theme_".$bbhash]) && ($theme_name == $default_theme))) {
					$ts .= '	<li>'.$display.'</li>'."\n";
				}	else {
					$ts .= '	<li><a href="'
						.bb_get_option( 'uri' )
						.'?bbtheme='.urlencode($theme_name).'">'
						.$display.'</a></li>'."\n";
				}
			}
			$ts .= '</ul>';
		}
		
	}

	echo $ts;
}


?>