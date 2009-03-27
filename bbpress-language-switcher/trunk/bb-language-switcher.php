<?php
/*
Plugin Name: bbPress Language Switcher
Plugin URI: http://bbpress.org/plugins/topic/bbpress-language-switcher
Description: Allows any user (guest or member) to select a different bbPress language for templates.
Version: 0.0.1
Author: _ck_
Author URI:  http://bbshowcase.org
Donate: http://bbshowcase.org/donate/
*/ 
/*
1. You MUST change in your bb-config.php :  define('BBLANG', ' ');  
note the space betweent the quotes - if you do not already have an alternate language set.

2. Put .mo language files into  bb-includes/languages/   
or define your own path with:  define('BB_LANG_DIR', '/your-custom-path/');  

3. Place dropdown anywhere you'd like via:  `<?php do_action('bb_language_switcher',''); ?>`

4. To rebuild the list of languages in the dropdown you must deactivate/reactivate the plugin

5. Users must have cookies enabled for language switch to work
*/

/*  stop editing here  */

bb_language_switcher_set_cookie();
add_filter('locale', 'bb_language_switcher_filter');
add_action('bb_language_switcher','bb_language_switcher');
bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'bb_language_switcher_update');
if (!empty($_GET['bb_language_switcher_update'])) {bb_language_switcher_update();}

function bb_language_switcher_filter($locale='') {if (!empty($_COOKIE['bblang_'.BB_HASH])) {$locale=bb_language_switcher_get_cookie();} return $locale;}

function bb_language_switcher_get_cookie() {return trim(substr(strip_tags(stripslashes($_COOKIE['bblang_'.BB_HASH])),0,20));}

function bb_language_switcher_set_cookie($timeout=999999999) { 		// makes the url request into a cookie
	if (isset($_GET['bblang'])) {				  
		$bblang=trim(substr(strip_tags(stripslashes($_GET['bblang'])),0,20));
		$expire = time() + intval($timeout);
		if ( bb_get_option( 'cookiedomain' ) ) {setcookie( "bblang_".BB_HASH, $bblang, $expire, bb_get_option( 'cookiepath' ), bb_get_option( 'cookiedomain' ) );}
		else {setcookie( "bblang_".BB_HASH, $bblang, $expire, bb_get_option( 'cookiepath' ) );}				
		header("Location: ".remove_query_arg('bblang'));		
		exit;		
	}
}

function bb_language_switcher($ignore='') {				// builds and displays the language dropdown UI
	global $bb_language_switcher; $output=""; $current="";
	if (empty($bb_language_switcher)) {$bb_language_switcher=bb_get_option('bb_language_switcher');}
	$bb_language_switcher['']="English";					
	$current=bb_language_switcher_filter();
	if (empty($current) && defined('BBLANG')) {$bblang=trim(BBLANG); if (!empty($bblang)) {$current=$bblang;}}
	$output.='<span id="bb_language_switcher">'."\n";		
	$output.= '<select style="width:150px;" name="bb_language_switcher" onchange="location.href=\''.add_query_arg('bblang','',remove_query_arg('bblang')).'=\' + this.options[this.selectedIndex].value;">'."\n"	;		
	foreach ($bb_language_switcher as $value=>$description) {
		if ($value==$current) {$selected='" selected="selected" ';} else {$selected='';}
		$bk="";  // todo: background colors
		$output.= '	<option style="padding:2px;'.$bk.'" value="'.$value.'"'.$selected.'>&nbsp;'.$description.'</option>'."\n";
	}
	$output.="</select></span>\n";
	echo $output;
}

function bb_language_switcher_update() {		// reads the .mo files and looks for the formal language name automagically
	if (!bb_current_user_can('administrate')) {return;}
	$languages=bb_glob(BB_LANG_DIR.'*.mo');
	foreach ($languages as $language) {		
		unset($match); $content="";
		$handle = fopen($language, "rb");
		while (!feof($handle)) {
			$content.=fread($handle, 8192);
			if (preg_match("/X\-Poedit\-Language\:(.+?)\n/i",$content,$match)) {continue;}
			if (strlen($content)>81920) {$content=substr($content,-8192);}
		}		
		unset($content);
		fclose($handle);
		if (!empty($match[1])) {
			preg_match("/.*[\/](.+?)\.mo$/i",$language,$lang);
			$list[trim($lang[1])]=trim($match[1]);
		}
	}	
if (!empty($list)) {bb_update_option('bb_language_switcher',$list);}	
}

?>