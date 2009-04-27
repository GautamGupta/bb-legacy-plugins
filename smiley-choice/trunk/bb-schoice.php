<?php
/*
Plugin Name: smiley choice
Plugin URI:  http://bbpress.org/plugins/topic/smiley-choice/
Description:  allows users to to change their shown smiley pack
Version: 0.1
Author: possessed
Author URI: http://besessener.com
*/

add_action('bb_init', 'bb_schoice_initialize');
add_action('extra_profile_info', 'add_schoice_to_profile_edit');
add_action('profile_edited', 'update_user_schoice');

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || !(strpos($_SERVER['REQUEST_URI'],"/bb-admin/")===false)) { // "stub" only load functions if in admin 
	if (isset($_GET['plugin']) && ($_GET['plugin']=="bb_schoice_admin" || strpos($_GET['plugin'],"bb-schoice.php"))) {require_once("bb-schoice-admin.php");} 
	add_action( 'bb_admin_menu_generator', 'bb_schoice_add_admin_page' );
	bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'bb_schoice_install');
	function bb_schoice_add_admin_page() {bb_admin_add_submenu(__('Smiley Choice'), 'administrate', 'bb_schoice_admin');}
	function bb_schoice_install() {global $bb_schoice; bb_schoice_initialize(); bb_update_option('bb_schoice',$bb_schoice);}
}

function bb_schoice_initialize() {
	global $bb,$bb_current_user,$bb_schoice,$bb_schoice_type, $bb_schoice_extra;
	if (!isset($bb_schoice)) {$bb_schoice = bb_get_option('bb_schoice');
		if (empty($bb_schoice)) {
		$bb_schoice['minimum_user_level']="participate";   // participate, moderate, administrate  (watchout for typos)
		$bb_schoice['schoice_instructions']="You may enter a short schoice which will be shown below your posts.";	
		$bb_schoice['smiley_choice_1']="Your 1st Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_2']="Your 2nd Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_3']="Your 3rd Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_4']="Your 4th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_5']="Your 5th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_6']="Your 6th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_7']="Your 7th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_8']="Your 8th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_9']="Your 9th Smiley-Pack-Directory";
		$bb_schoice['smiley_choice_10']="Your 10th Smiley-Pack-Directory";
		}}
	// if (BB_IS_ADMIN) {		// doesn't exist until 1040 :-(
		$bb_schoice_type['minimum_user_level']="participate,moderate,administrate";   // participate, moderate, administrate  (watchout for typos)
		$bb_schoice_type['schoice_instructions']="input";
		$bb_schoice_type['smiley_choice_1']="input";
		$bb_schoice_type['smiley_choice_2']="input";
		$bb_schoice_type['smiley_choice_3']="input";
		$bb_schoice_type['smiley_choice_4']="input";
		$bb_schoice_type['smiley_choice_5']="input";
		$bb_schoice_type['smiley_choice_6']="input";
		$bb_schoice_type['smiley_choice_7']="input";
		$bb_schoice_type['smiley_choice_8']="input";
		$bb_schoice_type['smiley_choice_9']="input";
		$bb_schoice_type['smiley_choice_10']="input";
	// }
}	
	
function add_schoice_to_profile_edit() {
global $user_id, $bb_current_user, $bb_schoice;		
if (bb_current_user_can($bb_schoice['minimum_user_level'])  &&  bb_is_user_logged_in() ) {
	$schoice = fetch_user_schoice($user_id);					
}
echo '<fieldset>
<legend>'. __('Smiley Choice') .'</legend>
<p>' .$bb_schoice['schoice_instructions'].'</p>'; ?>
<table style="text-align:center;">
	<tr>
    <?php
		$counter=0;
    	for($count = 1; $counter<5; $count++){
		$pack='smiley_choice_'.$count;
		if($schoice==$bb_schoice[$pack]){$check='checked="checked"';}
		if($bb_schoice[$pack] != ""){
			$counter += 1;
			echo '<td><img src="http://besessener.com/justicex/forum/bb-plugins/bb-smilies/'.$bb_schoice[$pack].'/preview.gif" alt="'.$bb_schoice[$pack].'"/></td>';
		}
		if ($count==10)$counter=5;
	}?>
    </tr>
    <tr>
    <?php 
	$counter=0;
	for($count = 1; $counter < 5; $count++){
		$pack='smiley_choice_'.$count;
		if($schoice==$bb_schoice[$pack]){$check='checked="checked"';}else $check='';
		if($bb_schoice[$pack] != ""){
		echo '<td><input type="radio" name="smileychoice" id="'.$pack.'" value="'.$bb_schoice[$pack].'" '.$check.' ></td>';
		$counter += 1;
		if($counter==5)echo '</tr><tr>';
		}
	if ($count==10)$counter=5;
	} ?>
    </tr><tr>
        <?php
    	for($count = $counter+1; $count <= 10; $count++){
		$pack='smiley_choice_'.$count;
		if($schoice==$bb_schoice[$pack]){$check='checked="checked"';}
		if($bb_schoice[$pack] != ""){
			echo '<td><img src="http://besessener.com/justicex/forum/bb-plugins/bb-smilies/'.$bb_schoice[$pack].'/preview.gif" alt="'.$bb_schoice[$pack].'"/></td>';
		}
	}?>
    </tr>
    <tr>
    <?php 
	for($count = $counter+1; $count <= 10; $count++){
		$pack='smiley_choice_'.$count;
		if($schoice==$bb_schoice[$pack]){$check='checked="checked"';}else $check='';
		if($bb_schoice[$pack] != ""){
		echo '<td><input type="radio" name="smileychoice" id="'.$pack.'" value="'.$bb_schoice[$pack].'" '.$check.' ></td>';
		}
	} ?>
    </tr>
</table>



<?php 
echo'</fieldset>';
	//endif;
}

function update_user_schoice() {
	global $user_id, $bb_schoice;
	if(isset($_POST["smileychoice"])){
		$schoice=$_POST["smileychoice"];
	}
		
	if ($schoice) {
		bb_update_usermeta($user_id, "schoice",$schoice);
	}
		else {bb_delete_usermeta($user_id, "schoice");}
}

function fetch_user_schoice($user_id) {
	$user = bb_get_user( $user_id );  
	$schoice=$user->schoice;
	if ($schoice) {return $schoice;}  else  {return "";}
}
?>