<?php
/*
Plugin Name: bbPress Smilies
Description:  Adds clickable smilies (emoticons) to bbPress.  No template edits required. Streamlined for low overhead. Uses swappable icon sets.
Plugin URI:  http://bbpress.org/plugins/topic/121
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.8
*/

$bb_smilies['icon_set']="default";  // change this to the exact directory name (case sensitive) if you want to switch icon package sets
$bb_smilies['popup'] = true;	  	// true = popup panel of smilies /  false = visible above text area always

$bb_smilies['css'] = ".bb_smilies {border:0; vertical-align: middle; padding-bottom:1px;}
.bb_smilies {cursor: pointer; cursor: hand;}
#bbClicker {position: absolute; float: right; visibility: hidden; background: buttonface; width: 150px; border:2px inset buttonface; font: 1.2em times, serif;}
#bbClicker img {padding:5px;}
#bb_smilies_toggle {float:right; padding: 0px 6px 1px 6px; margin: 1px 7px 2px 0; font: 1.2em times, serif; word-spacing: -1px; height: 16px; vertical-align:middle; line-height:16px;');}
";

/*  stop editing here  */

$bb_smilies['icon_path']=rtrim(dirname(__FILE__),' /\\').'/'.$bb_smilies['icon_set'].'/'; 
$bb_smilies['icon_url']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/'.$bb_smilies['icon_set'].'/'; 

add_filter('post_text', 'bb_smilies_convert');
add_action('bb_head','bb_smilies_css');
add_action('post_form','bbClicker',($bb_smilies['popup'] ? 20 : 9));
add_action('edit_form','bbClicker',($bb_smilies['popup'] ? 20 : 9));

add_filter('pm_text', 'bb_smilies_convert');  // support private messages plugin
if (bb_find_filename($_SERVER['REQUEST_URI'])=='pm.php') {add_action('bb_foot','bbClicker',($bb_smilies['popup'] ? 20 : 9));}

function bbClicker() {
global $wp_smilies, $bb_smilies, $bb_current_user;
if (empty($wp_smilies)) {@include($bb_smilies['icon_path']."package-config.php");}
echo  "<scr"."ipt type='text/javascript' defer='defer'>

if (window.attachEvent) {window.attachEvent('onload', bb_smilies_init);} 
else if (window.addEventListener) {window.addEventListener('load', bb_smilies_init, false);} 
else {document.addEventListener('load', bb_smilies_init, false);}

function bb_smilies(bbValue) {
	bbValue=' '+bbValue;	
	if (document.selection) {bbField.focus();sel = document.selection.createRange();sel.text = bbValue;}
	else if (bbField.selectionStart || bbField.selectionStart == '0') {var startPos = bbField.selectionStart; var endPos = bbField.selectionEnd;
		bbField.value = bbField.value.substring(0, startPos)+ bbValue+ bbField.value.substring(endPos, bbField.value.length);
	} else {bbField.value += bbValue; bbField.focus();}
bbClicker.style.visibility='hidden';
}	

function bb_smilies_init() {
if (typeof bbField == 'undefined') {bbField = document.getElementsByTagName('textarea')[0];}
if (bbField) { 
	bb_smilies_html='";
	echo '<img  onclick="bb_smilies_panel()" src="'. $bb_smilies['icon_url'] . $wp_smilies[":)"] .'" title="'.__('Insert Smilies').'" class="bb_smilies" /> ';
echo "';
	bb_smilies_panel_html='"; 
	$unique=array_unique($wp_smilies);
	foreach($unique as $smiley => $img) {
	echo '<img onclick=bb_smilies("'.addslashes(trim($smiley)).'")  src="'. $bb_smilies['icon_url'] . $img .'" title="'. htmlspecialchars(trim($smiley), ENT_QUOTES) .'" class="bb_smilies" /> ';
	}
echo "';
	bbField.setAttribute('style', 'clear:both;'); 	
	bb_smilies_toggle= document.createElement('div');	
	bb_smilies_toggle.setAttribute('id', 'bb_smilies_toggle'); 	

"; 	if ($bb_smilies['popup']) { 
echo "
	bb_smilies_toggle.innerHTML=bb_smilies_html;
	bbField.parentNode.insertBefore(bb_smilies_toggle,bbField);
	
	bbClicker= document.createElement('div');
	bbClicker.setAttribute('id', 'bbClicker'); 		
	bbClicker.innerHTML=bb_smilies_panel_html;
	bbField.parentNode.insertBefore(bbClicker,bbField);
";	
	} else {
echo "
	bb_smilies_toggle.innerHTML=bb_smilies_panel_html;
	bbField.parentNode.insertBefore(bb_smilies_toggle,bbField);
";	
	}
echo "
} // bbField
} // bb_smilies_init

function bb_smilies_panel() {
	if (bbClicker.style.visibility!='visible') {	
	// var obj = bbField; var pos = {x: obj.offsetLeft||0, y: obj.offsetTop||0};	
	// while(obj = obj.offsetParent) { pos.x += obj.offsetLeft||0; pos.y += obj.offsetTop||0; }		
	// bbClicker.style.left = pos.x + 'px';  
	// bbClicker.style.top = pos.y + 'px';	
	bbClicker.style.left =  (bbField.offsetLeft + bbField.offsetWidth) - (3 + bbClicker.offsetWidth + bb_smilies_toggle.offsetWidth) + 'px'; 
	bbClicker.style.visibility='visible';		
	} else {bbClicker.style.visibility='hidden';}  
}

</scr"."ipt>";
//	<scr"."ipt src='" .bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),".php","\\"),array("",".js","/"),__FILE__),"/\\")."?0.0.4' type='text/javascript' defer='defer'></scr"."ipt>";
} 

function bb_smilies_convert($text) {
global $bb_smilies, $bb_smilies_search, $bb_smilies_replace, $bb_smilies_prep;

if (empty($bb_smilies_prep)) {bb_smilies_init();}

$counter=0;  // filter out all backtick code first
if (preg_match_all("|\<code\>(.*?)\<\/code\>|sim", $text, $backticks)) {foreach ($backticks[0] as $backtick) {++$counter; $text=str_replace($backtick,"_bb_smilies_".$counter."_",$text);}}

$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); 
$stop = count($textarr); 
$output = "";
for ($i = 0; $i < $stop; $i++) { 
	$content = $textarr[$i];  
	if ((strlen($content) > 0) && ('<' != $content{0})) {$content = preg_replace($bb_smilies_prep, $bb_smilies_replace, $content);}
	$output .= $content;
}

// undo backticks
if ($counter) {$counter=0; foreach ($backticks[0] as $backtick)  {++$counter; $output=str_replace("_bb_smilies_".$counter."_",$backtick,$output);}}	

return $output;
}

function bb_smilies_prep($string) {return "/(\s|^|\&\#60\;p\&\#62\;)".preg_quote(trim($string),'/')."(\s|$|\&\#60\;br \/\&\#62\;)/";}

function bb_smilies_css() {global $bb_smilies; echo '<style type="text/css">'.$bb_smilies['css'].'</style>';} // inject css
 
function bb_smilies_init() {
global $wp_smilies, $bb_smilies, $bb_smilies_search, $bb_smilies_replace, $bb_smilies_prep;
if (empty($wp_smilies)) {@include($bb_smilies['icon_path']."package-config.php");}
$is_bb_feed=is_bb_feed();

foreach($wp_smilies as $smiley => $img) { 	
	$replace='$1 <img src="'. $bb_smilies['icon_url'] . $img .'" title="'. htmlspecialchars(trim($smiley), ENT_QUOTES) .'" class="bb_smilies" /> $2';
	if (is_bb_feed()) {$replace=wp_specialchars($replace);}
	$bb_smilies_replace[] = $replace;
	$bb_smilies_search[] = $smiley;
}
$bb_smilies_prep = array_map('bb_smilies_prep', $bb_smilies_search);	
 }
 
?>