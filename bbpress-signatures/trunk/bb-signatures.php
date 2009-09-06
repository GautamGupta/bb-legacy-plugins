<?php
/*
Plugin Name: bbPress signatures
Plugin URI:  http://bbpress.org/plugins/topic/63
Description:  allows users to add signatures to their forum posts, including an optional per-post toggle
Version: 0.2.0
Author: _ck_
Author URI: http://bbshowcase.org

If you would like the optional toggle on new/edit posts to disable signatures  you must edit  the 
edit-form.php  & post-form.php   templates and place at or near the bottom:  
<?php  bb_signatures_checkbox(); ?>
(you can wrap it in a DIV and float it to the left, right style anyway you'd like)
*/

add_action('bb_init', 'bb_signatures_initialize');
add_action('bb_head', 'bb_signatures_add_css');
add_action('extra_profile_info', 'add_signature_to_profile_edit');
add_action('profile_edited', 'update_user_signature');
add_action('bb_update_post', 'bb_signatures_exclude_posts_update');
add_filter('post_text','add_signature_to_post',5);
if (defined('BACKPRESS_PATH')) {add_action('bb-post.php', 'bb_signatures_exclude_posts_update');}
else {add_action('bb_post.php', 'bb_signatures_exclude_posts_update');}


if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 
	if (isset($_GET['plugin']) && ($_GET['plugin']=="bb_signatures_admin" || strpos($_GET['plugin'],"bb-signatures.php"))) {require_once("bb-signatures-admin.php");} 
	add_action( 'bb_admin_menu_generator', 'bb_signatures_add_admin_page' );
	bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'bb_signatures_install');
	function bb_signatures_add_admin_page() {bb_admin_add_submenu(__('Signatures'), 'administrate', 'bb_signatures_admin');}
	function bb_signatures_install() {global $bb_signatures; bb_signatures_initialize(); bb_update_option('bb_signatures',$bb_signatures);}
}

function bb_signatures_initialize() {
	global $bb,$bb_current_user,$bb_signatures,$bb_signatures_type, $bb_signatures_extra;
	if (!isset($bb_signatures)) {$bb_signatures = bb_get_option('bb_signatures');
		if (empty($bb_signatures)) {
		$bb_signatures['max_length']=300;     // sanity 
		$bb_signatures['max_lines']=3;     // sanity 
		$bb_signatures['minimum_user_level']="participate";   // participate, moderate, administrate  (watchout for typos)
		$bb_signatures['one_per_user_per_page']=true;    // only one signature shown for a user even if they have 2+ posts on a page
		$bb_signatures['allow_per_post_signature_toggle']=true;    // allows user decide which posts should have signatures
		$bb_signatures['allow_html']=true ;  // not implemented yet, obeys post text rules
		$bb_signatures['allow_smilies']=true ;  // not implemented yet, obeys post text rules
		$bb_signatures['allow_images']=true ;  // not implemented yet, obeys post text rules
		$bb_signatures['signature_question']="Show your signature on this post?";
		$bb_signatures['signature_instructions']="You may enter a short signature which will be shown below your posts.";
		$bb_signatures['style']=".signature {padding:1em; border-top:1px solid #ccc; font-size:0.87em; color:#444;}";   // add clear:both; for very bottom		
		}}
	// if (BB_IS_ADMIN) {		// doesn't exist until 1040 :-(
		$bb_signatures_type['max_length']="numeric";     // sanity 
		$bb_signatures_type['max_lines']="numeric";     // sanity 
		$bb_signatures_type['minimum_user_level']="participate,moderate,administrate";   // participate, moderate, administrate  (watchout for typos)
		$bb_signatures_type['one_per_user_per_page']="binary";    // only one signature shown for a user even if they have 2+ posts on a page
		$bb_signatures_type['allow_per_post_signature_toggle']="binary";    // let's user decide which posts should have signatures
		$bb_signatures_type['allow_html']="binary";  // not implemented yet, obeys post text rules
		$bb_signatures_type['allow_smilies']="binary";  // not implemented yet, obeys post text rules
		$bb_signatures_type['allow_images']="binary";  // not implemented yet, obeys post text rules
		$bb_signatures_type['signature_question']="input";
		$bb_signatures_type['signature_instructions']="input";		
		$bb_signatures_type['style']="textarea";
		
		$bb_signatures_extra['allow_html']="disabled";  // not implemented yet, obeys post text rules
		$bb_signatures_extra['allow_smilies']="disabled" ;  // not implemented yet, obeys post text rules
		$bb_signatures_extra['allow_images']="disabled" ;  // not implemented yet, obeys post text rules
	// }
}	
	
function bb_signatures_add_css() { global $bb_signatures;  echo '<style type="text/css">'.$bb_signatures['style'].'</style>'; } 

function add_signature_to_post($text) {
if (!is_bb_feed()) {
global $bb_post,$bb_signatures,$bb_signatures_on_page;
// if (bb_current_user_can($bb_signatures['minimum_user_level']) ) :      // only enabled for testing on my own rig
	$user_id=$bb_post->poster_id;
	if (($bb_signatures['one_per_user_per_page'] && $bb_signatures_on_page[$user_id]) || bb_signatures_exclude_posts_check($bb_post->post_id)) {return $text;}
	if ($signature=fetch_user_signature($user_id))  :	
		$text.='<div class="signature">'.nl2br($signature).'</div>';
		$bb_signatures_on_page[$user_id]=true;
	endif;
// endif;
}
return $text;
}

function add_signature_to_profile_edit() {
global $user_id, $bb_current_user, $bb_signatures;		
if (bb_current_user_can($bb_signatures['minimum_user_level'])  &&  bb_is_user_logged_in() ) :
	$signature = fetch_user_signature($user_id);				
echo '<fieldset>
<legend>'. __('Signature') .'</legend>
<p>' .$bb_signatures['signature_instructions'].'</p>
<table border=0 cellpadding=0 cellspacing=0 width="95%">
<tr class="extra-caps-row">
<td><textarea style="overflow:auto;height:5em;width:98%;" name="signature" id="signature" type="text"  rows="2" wrap="off"
 onkeyup="if (this.value.length>'.$bb_signatures['max_length'].') {this.value=this.value.substring(0,'.$bb_signatures['max_length'].')}">
'.$signature.'</textarea></td>
</tr>
</table>
</fieldset>';
	endif;
}

function update_user_signature() {
	global $user_id, $bb_signatures;
	$signature=trim(substr($_POST['signature'],0,$bb_signatures['max_length']));
	if ($signature) {
	// remove_filter('pre_post', 'bb_autop', 60);  $signature  = apply_filters('pre_post', $signature);
	$signature=bb_filter_kses(stripslashes(balanceTags(bb_code_trick(bb_encode_bad($signature)),true)));
	if (!$bb_signatures['allow_html']) {
	if ($bb_signatures['allow_images']) {$allowed="<img>";} else {$allowed="";}
		$signature=strip_tags($signature,$allowed);
	}
	$signature= implode("\n",array_slice (explode("\n",$signature), 0, $bb_signatures['max_lines']));	
	bb_update_usermeta($user_id, "signature",$signature);
	}
	else {bb_delete_usermeta($user_id, "signature");}
}

function fetch_user_signature($user_id) {
	$user = bb_get_user( $user_id );  
	$signature=$user->signature;
	if ($signature) {return $signature;}  else  {return "";}
}

function bb_signatures_exclude_posts_update($post_id) {
if ($post_id ) :
if (isset($_POST['signature_disable'])) {
global $bb_post,$bb_signatures,$bb_signatures_on_page;
if (bb_current_user_can($bb_signatures['minimum_user_level']) ) :    
	$disable=$_POST['signature_disable'];
	$user_id=get_post_author_id($post_id);
	$user = bb_get_user( $user_id );  
	if ($user->bb_signatures_exclude_posts) {$bb_signatures_exclude_posts=explode(",",$user->bb_signatures_exclude_posts);}
	if (is_array($bb_signatures_exclude_posts)) {$inarray=in_array($post_id,$bb_signatures_exclude_posts);} else {$inarray=false;}
	if ($disable==0)  {if (!$inarray) {return;} else { unset($bb_signatures_exclude_posts[array_search( $post_id, $bb_signatures_exclude_posts)]); }  }
	if ($disable==1) {if  ($inarray) {return;} else {$bb_signatures_exclude_posts[]=$post_id;} }	
	bb_update_usermeta( $user_id, 'bb_signatures_exclude_posts', implode(",",$bb_signatures_exclude_posts));
endif;
}	
endif;
}

function bb_signatures_exclude_posts_check($post_id) {
$user_id=get_post_author_id($post_id);
$user = bb_get_user( $user_id );  
$bb_signatures_exclude_posts=(isset($user->bb_signatures_exclude_posts) ? explode(",",$user->bb_signatures_exclude_posts) : array() );
return  in_array($post_id,$bb_signatures_exclude_posts);
}

function bb_signatures_checkbox() {
//  calls to this function must unfortunately be manually placed into edit-form.php and post-form.php templates
global $bb_signatures,$bb_signatures_on_page,$posts,$bb_post;
if ($bb_signatures['allow_per_post_signature_toggle']) :
if (bb_current_user_can($bb_signatures['minimum_user_level']) ) :
if (isset($posts) || !isset($bb_post->poster_id)) {$user_id=bb_get_current_user_info( 'id' );} else {$user_id=$bb_post->poster_id;}	// || is_topic()
if (isset($posts) || !isset($bb_post->post_id)) {$post_id=0;} else {$post_id=$bb_post->post_id;}		// || is_topic()
// if (fetch_user_signature($user_id)) {$checked="checked";}
if ($post_id && bb_signatures_exclude_posts_check($post_id)) {$checked=""; $disabled="1";} else {$checked="checked"; $disabled="0";}
echo $bb_signatures['signature_question'].'	
	<input type="checkbox" name="signature_uncheck"  onchange="this.form.signature_disable.value=((this.checked)?0:1)" value="0" '.$checked.'>
	<input type="hidden"      name="signature_disable"     onchange="this.forum.signature_uncheck.checked = ((this.value==1)?false:true)" value="'.$disabled.'">
'; //.$user_id.' - '.$post_id;	
endif;
endif;
}

?>