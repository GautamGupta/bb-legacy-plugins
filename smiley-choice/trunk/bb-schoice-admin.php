<?php

add_action( 'bb_admin-header.php','bb_schoice_process_post');

function bb_schoice_process_post() {
global $bb_schoice;
	if (bb_current_user_can('administrate')) {
		if (isset($_REQUEST['bb_schoice_reset'])) {
			unset($bb_schoice); 		
			bb_delete_option('bb_schoice');
			bb_schoice_initialize();			
			bb_update_option('bb_schoice',$bb_schoice);
			bb_admin_notice('<b>bbPress Smiley Choice: '.__('All Settings Reset To Defaults.').'</b>'); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('bb_schoice_reset')));	// bug workaround, page doesn't show reset settings
		}	
	 	elseif (isset($_POST['submit']) && isset($_POST['bb_schoice'])) {
			foreach(array_keys( $bb_schoice) as $key) {
				if (isset($_POST[$key])) {$bb_schoice[$key]=$_POST[$key];}
			}	
		bb_update_option('bb_schoice',$bb_schoice);
		// unset($GLOBALS['bb_schoice']); $bb_schoice = bb_get_option('bb_schoice');
		}
	}
}

function bb_schoice_admin() {
	global $bb_schoice,$bb_schoice_type,$bb_schoice_extra;		
	?>
		<div style="text-align:right;margin-bottom:-1.5em;">
		[ <a href="<?php echo add_query_arg('bb_schoice_reset','1'); ?>">Reset All Settings To Defaults</a> ] 			
		</div>

		<h2>bbPress Smiley Choice</h2><br />
        <?php echo "Leave input-fields blank if not needed.";	?>
        <?php echo "<br />Directory location is relative to bb-smilies Plugin by _ck_";	?>
        <?php echo "<br />So you can add up to 10 Smiley directories which users can choose from.";	?>
		<form method="post" name="bb_schoice_form" id="bb_schoice_form">
		<input type=hidden name="bb_schoice" value="1">
			<table class="widefat">
				<thead>
					<tr> <th width="170">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
					<?php
					foreach(array_keys( $bb_schoice_type) as $key) {
						?>
						<tr>
							<td><label for="bb_schoice_<?php echo $key; ?>"><b><?php echo ucwords(str_replace("_"," ",$key)); ?></b></label></td>
							<td>
							<?php
							switch ( $bb_schoice_type[$key]) :
							case 'binary' :
								?><input <?php echo ($test=$bb_schoice_extra[$key]) ? $test : ""; ?> type=radio name="<?php echo $key;  ?>" value="1" <?php echo ($bb_schoice[$key]==true ? 'checked="checked"' : '');?> >YES &nbsp; &nbsp;
								     <input <?php echo ($test=$bb_schoice_extra[$key]) ? $test : ""; ?> type=radio name="<?php echo $key;  ?>" value="0" <?php echo ($bb_schoice[$key]==false ? 'checked="checked"' : '');?> >NO <?php
							break;
							case 'numeric' :
								?><input type=text maxlength=20 name="<?php echo $key;  ?>" value="<?php echo $bb_schoice[$key]; ?>"> <?php 
							break;
							case 'textarea' :								
								?><textarea style="width:98%" name="<?php echo $key;  ?>"><?php echo $bb_schoice[$key]; ?></textarea><?php 							
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$bb_schoice_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($bb_schoice[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type=text style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $bb_schoice[$key]; ?>"> <?php 
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
			<p class="submit"><input type="submit" name="submit" value="Save bbPress schoice Settings"></p>
		
		</form>
		<?php
}

function bb_schoice_display_role_dropdown($name, $index, $role) {
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

?>