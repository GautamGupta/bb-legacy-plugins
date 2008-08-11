<?php
/*
Plugin Name: bbPress Smilies
Description:  Adds clickable smilies (emoticons) to bbPress.  No template edits required. Streamlined for low overhead. Uses swappable icon sets.
Plugin URI:  http://bbpress.org/plugins/topic/121
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.3
*/

$bb_smilies['icon_set']="default";  // change this to the exact directory name (case sensitive) if you want to switch icon package sets
$bb_smilies['popup'] = true;	  	// true = popup panel of smilies /  false = visible above text area always

$bb_smilies['css'] = ".bb_smilies {border:0; vertical-align: top; padding-top:3px;}
.bb_smilies {cursor: pointer; cursor: hand;}
#bb_smilies_clicker {position: absolute; float: right; visibility: hidden; background: buttonface; width: 150px; border:2px inset buttonface; font: 1.2em times, serif;}
#bb_smilies_clicker img {padding:5px;}
#bb_smilies_toggle {float:right; padding: 0px 6px 1px 6px; margin: 1px 7px 2px 0; font: 1.2em times, serif; word-spacing: -1px; height: 16px; vertical-align:middle; line-height:16px;');}
";

/* stop editing here */

$bb_smilies['icon_path']=rtrim(dirname(__FILE__),' /\\').'/'.$bb_smilies['icon_set'].'/'; 
$bb_smilies['icon_url']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/'.$bb_smilies['icon_set'].'/'; 

add_filter('post_text', 'bb_smilies_convert');
add_action('bb_head','bb_smilies_css');
add_action('bb_foot', 'bb_smilies_clicker');

function bb_smilies_clicker() {
global $bb_smilies, $bb_current_user;
if ($bb_current_user->ID && (isset($_GET['new']) || in_array(bb_get_location(),array('topic-page','tag-page','forum-page')))) {
@include($bb_smilies['icon_path']."package-config.php");
echo  "<scr"."ipt type='text/javascript' defer='defer'>

function bb_smilies(myValue) {
	myValue=' '+myValue;	
	if (document.selection) {bb_smilies_textarea.focus();sel = document.selection.createRange();sel.text = myValue;}
	else if (bb_smilies_textarea.selectionStart || bb_smilies_textarea.selectionStart == '0') {var startPos = bb_smilies_textarea.selectionStart; var endPos = bb_smilies_textarea.selectionEnd;
		bb_smilies_textarea.value = bb_smilies_textarea.value.substring(0, startPos)+ myValue+ bb_smilies_textarea.value.substring(endPos, bb_smilies_textarea.value.length);
	} else {bb_smilies_textarea.value += myValue; bb_smilies_textarea.focus();}
}	

function bb_smilies_init() {
bb_smilies_textarea = document.getElementsByTagName('textarea')[0];
if (bb_smilies_textarea) { 
	bb_smilies_html='";
	echo '<img  onclick="bb_smilies_panel()" src="'. $bb_smilies['icon_url'] . $wp_smilies[":)"] .'" title="'.__('Insert Smilies').'" class="bb_smilies" /> ';
echo "';
	bb_smilies_panel_html='";
	foreach(array_unique($wp_smilies) as $smiley => $img) {
	echo '<img onclick=bb_smilies("'.addslashes(trim($smiley)).'")  src="'. $bb_smilies['icon_url'] . $img .'" title="'. htmlspecialchars(trim($smiley), ENT_QUOTES) .'" class="bb_smilies" /> ';
	}
echo "';
	bb_smilies_textarea.setAttribute('style', 'clear:both;'); 	
	bb_smilies_toggle= document.createElement('div');	
	bb_smilies_toggle.setAttribute('id', 'bb_smilies_toggle'); 	

"; 	if ($bb_smilies['popup']) { 
echo "
	bb_smilies_toggle.innerHTML=bb_smilies_html;
	bb_smilies_textarea.parentNode.insertBefore(bb_smilies_toggle,bb_smilies_textarea);
	
	bb_smilies_clicker= document.createElement('div');
	bb_smilies_clicker.setAttribute('id', 'bb_smilies_clicker'); 		
	bb_smilies_clicker.innerHTML=bb_smilies_panel_html;
	bb_smilies_textarea.parentNode.insertBefore(bb_smilies_clicker,bb_smilies_textarea);
";	
	} else {
echo "
	bb_smilies_toggle.innerHTML=bb_smilies_panel_html;
	bb_smilies_textarea.parentNode.insertBefore(bb_smilies_toggle,bb_smilies_textarea);
";	
	}
echo "
} // if bb_smilies_textarea
} // bb_smilies_init

function bb_smilies_panel() {
	if (bb_smilies_clicker.style.visibility!='visible') {	
	// var obj = bb_smilies_textarea; var pos = {x: obj.offsetLeft||0, y: obj.offsetTop||0};	
	// while(obj = obj.offsetParent) { pos.x += obj.offsetLeft||0; pos.y += obj.offsetTop||0; }		
	// bb_smilies_clicker.style.left = pos.x + 'px';  
	// bb_smilies_clicker.style.top = pos.y + 'px';	
	bb_smilies_clicker.style.left =  (bb_smilies_textarea.offsetLeft + bb_smilies_textarea.offsetWidth) - (3 + bb_smilies_clicker.offsetWidth + bb_smilies_toggle.offsetWidth) + 'px'; 
	bb_smilies_clicker.style.visibility='visible';		
	} else {bb_smilies_clicker.style.visibility='hidden';}  
}

if (window.attachEvent) {window.attachEvent('onload', bb_smilies_init);} 
else if (window.addEventListener) {window.addEventListener('load', bb_smilies_init, false);} 
else {document.addEventListener('load', bb_smilies_init, false);}

</scr"."ipt>";
//	<scr"."ipt src='" .bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),".php","\\"),array("",".js","/"),__FILE__),"/\\")."?0.0.4' type='text/javascript' defer='defer'></scr"."ipt>";
}	
} 

function bb_smilies_convert($text) {
global $bb_smilies;
@include($bb_smilies['icon_path']."package-config.php");

$counter=0;  // filter out all backtick code first
if (preg_match_all("|\<code\>(.*?)\<\/code\>|sim", $text, $backticks)) {foreach ($backticks[0] as $backtick) {++$counter; $text=str_replace($backtick,"_bb_smilies_".$counter."_",$text);}}

foreach($wp_smilies as $smiley => $img) { 
	$bb_smilies_search[] = $smiley;
	$bb_smilies_replace[] = ' <img src="'. $bb_smilies['icon_url'] . $img .'" title="'. htmlspecialchars(trim($smiley), ENT_QUOTES) .'" class="bb_smilies" /> ';
}

$prep_search = array_map('bb_smilies_prep', $bb_smilies_search);	
$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); 
$stop = count($textarr); 
$output = "";
for ($i = 0; $i < $stop; $i++) { 
	$content = $textarr[$i];  
	if ((strlen($content) > 0) && ('<' != $content{0})) {$content = preg_replace($prep_search, $bb_smilies_replace, $content);}
	$output .= $content;
}

// undo backticks
if ($counter) {$counter=0; foreach ($backticks[0] as $backtick)  {++$counter; $output=str_replace("_bb_smilies_".$counter."_",$backtick,$output);}}	

return $output;
}

function bb_smilies_prep($string) {return "/(\s|^)".preg_quote(trim($string),'/')."(\s|$)/";}

function bb_smilies_css() {global $bb_smilies; echo '<style type="text/css">'.$bb_smilies['css'].'</style>';} // inject css
 
?>