<?php

add_action( 'bb_admin-header.php','bb_signatures_process_post');

function bb_signatures_process_post() {
global $bb_signatures;
	if (bb_current_user_can('administrate')) {
		if (isset($_REQUEST['bb_signatures_reset'])) {
			unset($bb_signatures); 		
			bb_delete_option('bb_signatures');
			bb_signatures_initialize();			
			bb_update_option('bb_signatures',$bb_signatures);
			bb_admin_notice('<b>bbPress Signatures: '.__('All Settings Reset To Defaults.').'</b>'); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('bb_signatures_reset')));	// bug workaround, page doesn't show reset settings
		}	
	 	elseif (isset($_POST['submit']) && isset($_POST['bb_signatures'])) {
			foreach(array_keys( $bb_signatures) as $key) {
				if (isset($_POST[$key])) {$bb_signatures[$key]=$_POST[$key];}
			}	
		bb_update_option('bb_signatures',$bb_signatures);
		// unset($GLOBALS['bb_signatures']); $bb_signatures = bb_get_option('bb_signatures');
		}
	}
}

function bb_signatures_admin() {
	global $bb_signatures,$bb_signatures_type,$bb_signatures_extra;		
	?>
		<div style="text-align:right;margin-bottom:-1.5em;">
		[ <a href="<?php echo add_query_arg('bb_signatures_reset','1'); ?>">Reset All Settings To Defaults</a> ] 			
		</div>

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
								?><input <?php echo ($test=$bb_signatures_extra[$key]) ? $test : ""; ?> type=radio name="<?php echo $key;  ?>" value="1" <?php echo ($bb_signatures[$key]==true ? 'checked="checked"' : '');?> >YES &nbsp; &nbsp;
								     <input <?php echo ($test=$bb_signatures_extra[$key]) ? $test : ""; ?> type=radio name="<?php echo $key;  ?>" value="0" <?php echo ($bb_signatures[$key]==false ? 'checked="checked"' : '');?> >NO <?php
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
			<p class="submit"><input type="submit" name="submit" value="Save bbPress Signatures Settings"></p>
		
		</form>
		<?php
}

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

?>