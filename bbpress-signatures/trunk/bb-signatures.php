<?php
/*
Plugin Name: bbPress signatures
Description:  allows users to add signatures to their forum posts, including an optional per-post toggle
Plugin URI:  http://bbpress.org/plugins/topic/63
Author: _ck_
Author URI: http://CKon.wordpress.com
Version: 0.14
*/
/*
License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions:   install, activate, tinker with settings in admin menu

If you would like the optional toggle on new/edit posts to disable signatures  you must edit  the 
edit-form.php  & post-form.php   templates and place at or near the bottom:  
<?  bb_signatures_checkbox(); ?>
(you can wrap it in a DIV and float it to the left, right style anyway you'd like)

Version History:
0.05 	: slashes & autop fixed, replaced input with textarea, max_lines now supported in post-processing,  max_length checked in realtime (as well as post processing)
0.06 	: internal testing/bugfix
0.07 	: per-post signature toggle
0.08 	: toggle for allow html and allow images should now work
0.10 	: basic functioning admin menu
0.11	: more intelligent admin menu
0.12	: attempted fix at in_array error for disabling posts 
0.13	: warnings cleanup for better code
0.14	: signatures removed from rss feeds

*/

function bb_signatures_initialize() {
	global $bb,$bb_current_user,$bb_signatures,$bb_signatures_type;
	if(!isset($bb_signatures)) {$bb_signatures = bb_get_option('bb_signatures');
		if (!$bb_signatures) {
		$bb_signatures['max_length']=100;     // sanity 
		$bb_signatures['max_lines']=3;     // sanity 
		$bb_signatures['minimum_user_level']="moderate";   // participate, moderate, administrate  (watchout for typos)
		$bb_signatures['allow_html']=true ;  // not implimented yet, obeys post text rules
		$bb_signatures['allow_smilies']=true ;  // not implimented yet, obeys post text rules
		$bb_signatures['allow_images']=true ;  // not implimented yet, obeys post text rules
		$bb_signatures['one_per_user_per_page']=true;    // only one signature shown for a user even if they have 2+ posts on a page
		$bb_signatures['allow_per_post_signature_toggle']=true;    // let's user decide which posts should have signatures
		$bb_signatures['signature_question']="Show your signature on this post?";
		$bb_signatures['signature_instructions']="You may enter a short signature which will be shown below your posts.";
		$bb_signatures['style']=".signature {clear:both;border-top:1px solid #222; font-size:85%; color:#777;padding:1em;}";			
		}
		$bb_signatures_type['max_length']="numeric";     // sanity 
		$bb_signatures_type['max_lines']="numeric";     // sanity 
		$bb_signatures_type['minimum_user_level']="participate,moderate,administrate";   // participate, moderate, administrate  (watchout for typos)
		$bb_signatures_type['allow_html']="binary";  // not implimented yet, obeys post text rules
		$bb_signatures_type['allow_smilies']="binary";  // not implimented yet, obeys post text rules
		$bb_signatures_type['allow_images']="binary";  // not implimented yet, obeys post text rules
		$bb_signatures_type['one_per_user_per_page']="binary";    // only one signature shown for a user even if they have 2+ posts on a page
		$bb_signatures_type['allow_per_post_signature_toggle']="binary";    // let's user decide which posts should have signatures
		$bb_signatures_type['signature_question']="input";
		$bb_signatures_type['signature_instructions']="input";		
		$bb_signatures_type['style']="textarea";
	}
}	
add_action( 'bb_init', 'bb_signatures_initialize');
add_action( 'init', 'bb_signatures_initialize');
	
function bb_signatures_add_css() { global $bb_signatures;  echo '<style type="text/css">'.$bb_signatures['style'].'</style>'; 
} add_action('bb_head', 'bb_signatures_add_css');

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
add_filter('post_text','add_signature_to_post',9);

function add_signature_to_profile_edit() {
global $user_id, $bb_current_user, $bb_signatures;		
if (bb_current_user_can($bb_signatures['minimum_user_level'])  &&  bb_is_user_logged_in() ) :
	$signature = fetch_user_signature($user_id);				
echo '<fieldset>
<legend>'. __('Signature') .'</legend>
<p>' .$bb_signatures['signature_instructions'].'</p>
<table border=0>
<tr class="extra-caps-row">
<td><textarea style="width:90%;" name="signature" id="signature" type="text"  rows="2" cols="80" wrap="off"
 onkeyup="if (this.value.length>'.$bb_signatures['max_length'].') {this.value=this.value.substring(0,'.$bb_signatures['max_length'].')}">
'.$signature.'</textarea></td>
</tr>
</table>
</fieldset>';
	endif;
}
add_action('extra_profile_info', 'add_signature_to_profile_edit');

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
add_action('profile_edited', 'update_user_signature');

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
add_action( 'bb_post.php', 'bb_signatures_exclude_posts_update');
add_action('bb_update_post', 'bb_signatures_exclude_posts_update');

function bb_signatures_exclude_posts_check($post_id) {
$user_id=get_post_author_id($post_id);
$user = bb_get_user( $user_id );  
$bb_signatures_exclude_posts=(isset($user->bb_signatures_exclude_posts) ? explode(",",$user->bb_signatures_exclude_posts) : array() );
return  in_array($post_id,$bb_signatures_exclude_posts);
}

function bb_signatures_checkbox() {
//  this function call must unfortunately be manually placed into edit-form.php and post-form.php templates
global $bb_post,$bb_signatures,$bb_signatures_on_page;
if ($bb_signatures['allow_per_post_signature_toggle']) :
if (bb_current_user_can($bb_signatures['minimum_user_level']) ) :
if (is_topic() || !$user_id=$bb_post->poster_id) {$user_id=bb_get_current_user_info( 'id' );}
if (is_topic() || !$post_id=$bb_post->post_id) {$post_id=0;}
// if (fetch_user_signature($user_id)) {$checked="checked";}
if ($post_id && bb_signatures_exclude_posts_check($post_id)) {$checked="";} else {$checked="checked";}
echo $bb_signatures['signature_question'].'	
	<input type="checkbox" name="signature_uncheck"  onchange="this.form.signature_disable.value=((this.checked)?0:1)" value="0" '.$checked.'>
	<input type="hidden"      name="signature_disable"     onchange="this.forum.signature_uncheck.checked = ((this.value==1)?false:true)" value="0">
'; //.$user_id.' - '.$post_id;	
endif;
endif;
}


function bb_signatures_add_admin_page() {bb_admin_add_submenu(__('Signatures'), 'administrate', 'bb_signatures_admin_page');}
add_action( 'bb_admin_menu_generator', 'bb_signatures_add_admin_page' );

function bb_signatures_display_role_dropdown($name, $index, $role) {
	?>
	<p>
		<select name="<?php echo $name . '[' . $index . ']'; ?>" id="<?php echo $name . '_' . $index ; ?>">			
			<option value="MEMBER" <?php echo ($role == 'MEMBER') ? 'selected' : '' ; ?>>Registered Members</option>
			<option value="MODERATOR" <?php echo ($role == 'MODERATOR') ? 'selected' : '' ; ?>>Moderators</option>
			<option value="ADMINISTRATOR" <?php echo ($role == 'ADMINISTRATOR') ? 'selected' : '' ; ?>>Administrators</option>
		</select>
	</p>
	<?php
}

function bb_signatures_admin_page() {
	global $bb_signatures,$bb_signatures_type;		
	?>
		<h2>bbPress Signatures</h2>
		<form method="post" name="bb_signatures_form" id="bb_signatures_form">
		<input type=hidden name="bb_signatures" value="1">
			<table class="widefat">
				<thead>
					<tr> <th width="170">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
					<?php
					foreach(array_keys( $bb_signatures_type) as $key) {
						?>
						<tr>
							<td><label for="bb_signatures_<?php echo $key; ?>"><b><?php echo ucwords(str_replace("_"," ",$key)); ?></b></label></td>
							<td>
							<?php
							switch ( $bb_signatures_type[$key]) :
							case 'binary' :
								?><input type=radio name="<?php echo $key;  ?>" value="1" <?php echo ($bb_signatures[$key]==true ? 'checked="checked"' : '');?> >YES
								     <input type=radio name="<?php echo $key;  ?>" value="0" <?php echo ($bb_signatures[$key]==false ? 'checked="checked"' : '');?> >NO <?php
							break;
							case 'numeric' :
								?><input type=text maxlength=3 name="<?php echo $key;  ?>" value="<?php echo $bb_signatures[$key]; ?>"> <?php 
							break;
							case 'textarea' :								
								?><textarea style="width:98%" name="<?php echo $key;  ?>"><?php echo $bb_signatures[$key]; ?></textarea><?php 							
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$bb_signatures_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($bb_signatures[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type=text style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $bb_signatures[$key]; ?>"> <?php 
								}
							endswitch;							
							?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" value="Submit"></p>
		
		</form>
		<?php
}

function bb_signatures_process_post() {
	if(isset($_POST['submit']) && isset($_POST['bb_signatures'])) {
		global $bb_signatures;
		foreach(array_keys( $bb_signatures) as $key) {
			if (isset($_POST[$key])) {$bb_signatures[$key]=$_POST[$key];}
		}
		bb_update_option('bb_signatures',$bb_signatures);
		// unset($GLOBALS['bb_signatures']); $bb_signatures = bb_get_option('bb_signatures');
	}
}
add_action( 'bb_admin-header.php','bb_signatures_process_post');

?>