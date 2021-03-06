<?php
/*
Plugin Name: BBcode Buttons Toolbar
Plugin URI: http://bbpress.org/plugins/topic/114
Description: Automatically adds an easy access button toolbar above the post textarea to allow quick tags in BBcode. This is an enhanced replacement for the Comment Quicktags plugin. No template editing required.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.9

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

add_action('post_form','bbcode_buttons',11);
add_action('edit_form','bbcode_buttons',11);
add_action('bbcode_buttons','bbcode_buttons');

function bbcode_buttons() { 
$tags = bb_allowed_tags();
echo  "<scr"."ipt type='text/javascript' defer='defer'>
	function BBcodeButtons_init() {
	BBcodeButtons.push(new BBcodeButton('ed_bold','B','[b]','[/b]','b','font-weight:bold;','bold'));
	BBcodeButtons.push(new BBcodeButton('ed_italic','I','[i]','[/i]','i','padding-right:7px;font-style:italic;','italics'));
	BBcodeButtons.push(new BBcodeButton('ed_under','U','[u]','[/u]','u','text-decoration:underline;','underline'));
	BBcodeButtons.push(new BBcodeButton('ed_strike','S','[s]','[/s]','s','text-decoration:line-through;','strike through'));
	BBcodeButtons.push(new BBcodeButton('ed_link','URL','','[/url]','a','text-decoration:underline;','make a link')); 
	BBcodeButtons.push(new BBcodeButton('ed_block','&#147;quote&#148;','[quote]','[/quote]','q','padding:0 1px 1px 1px;','quote'));";
if (isset($tags['img'])) {echo "BBcodeButtons.push(new BBcodeButton('ed_img','IMG','[img]','[/img]','m',-1));";}
echo  "BBcodeButtons.push(new BBcodeButton('ed_ul','UL','[list]','[/list]','u','','unordered list'));
	BBcodeButtons.push(new BBcodeButton('ed_ol','OL','[list=1]','[/list]','o','','ordered list'));
	BBcodeButtons.push(new BBcodeButton('ed_li','LI','[*]','[/*]','l','','list item'));";	
if (isset($tags['center'])) {echo "BBcodeButtons.push(new BBcodeButton('ed_center','center','[center]','[/center]','c','','center'));";}	
echo  "BBcodeButtons.push(new BBcodeButton('ed_code','CODE','[code]','[/code]','p','line-height:160%;font-size:80%;letter-spacing:1px;font-family:anadale,serif;','unformatted / code'));
	BBcodeButtons.push(new BBcodeButton('ed_close','close','','','c',' ','auto-close any tags you left open'));
	}</scr"."ipt>
	<scr"."ipt src='" .bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),".php","\\"),array("",".js","/"),__FILE__),"/\\")."?0.0.9' type='text/javascript' defer='defer'></scr"."ipt>";
} 
?>