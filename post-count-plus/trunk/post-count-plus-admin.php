<?php

/* Post Count Plus - admin functions */

function post_count_plus_admin() {
	global $post_count_plus, $post_count_plus_type, $post_count_plus_label;			
	?>
		<div style="text-align:right;margin-bottom:-1.5em;">
			[ <a title="recommended occasionally to speed up page loads or re-sync counts" href="<?php echo add_query_arg('post_count_plus_recount','1',remove_query_arg('post_count_plus_reset')); ?>">Rebuild Post Count For All Users</a> ]
			&nbsp;&nbsp;&nbsp;&nbsp;
			[ <a href="<?php echo add_query_arg('post_count_plus_reset','1',remove_query_arg('post_count_plus_recount')); ?>">Reset All Settings To Defaults</a> ] 			
		</div>
		
		<h2>Post Count Plus</h2>
		
		<form method="post" name="post_count_plus_form" id="post_count_plus_form" action="<?php echo remove_query_arg(array('post_count_plus_reset','post_count_plus_recount')); ?>">
		<input type=hidden name="post_count_plus" value="1">
			<table class="widefat">
				<thead>
					<tr> <th width="33%">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
					<?php
					
					$post_count_plus['custom_titles'][0]=__("New Title");	 
					$post_count_plus['custom_titles'][1]=__("Minimum Posts");
					$post_count_plus['custom_titles'][2]=__("Minimum Days");
					$post_count_plus['custom_titles'][3]=__("Minimum Role");
					$post_count_plus['custom_titles'][4]=__("Color");
					
					foreach(array_keys( $post_count_plus_type) as $key) {
					$post_count_plus[$key]=stripslashes_deep($post_count_plus[$key]);					
					$colspan= (substr($post_count_plus_type[$key],0,strpos($post_count_plus_type[$key].",",","))=="array") ? "2" : "1";
						?>
						<tr>
							<td nowrap colspan=<?php echo $colspan; ?>>
							<label for="post_count_plus_<?php echo $key; ?>">
							<b><?php  if ($post_count_plus_label[$key])  {echo $post_count_plus_label[$key];} else {echo ucwords(str_replace("_"," ",$key));} ?></b>
							</label>
							<?php
							if ($colspan<2) {echo "</td><td>";} else {echo "<br />";}
							switch (substr($post_count_plus_type[$key],0,strpos($post_count_plus_type[$key].",",","))) :
							case 'binary' :
								?><input type=radio name="<?php echo $key;  ?>" value="1" <?php echo ($post_count_plus[$key]==true ? 'checked="checked"' : ''); ?> >Yes
								     <input type=radio name="<?php echo $key;  ?>" value="0" <?php echo ($post_count_plus[$key]==false ? 'checked="checked"' : ''); ?> >No <?php
							break;
							case 'numeric' :
								?><input type=text maxlength=3 name="<?php echo $key;  ?>" value="<?php echo $post_count_plus[$key]; ?>"> <?php 
							break;
							case 'textarea' :								
								?><textarea style="width:98%" name="<?php echo $key;  ?>"><?php echo $post_count_plus[$key]; ?></textarea><?php 							
							break;
							case 'array' :
								$elements=explode(",",$post_count_plus_type[$key]);
								echo "<table border=0 align='center' width='80%'>";
								for ($row=0; $row<$elements[2]; $row++) { echo "<tr>";
								for ($column=0; $column<$elements[1]; $column++) {
								if ($row==0) {echo "<th width='".intval(100/$elements[1])."%'>".$post_count_plus[$key][$column]."</th>";}
								else {
								$position=($row*$elements[1])+$column; 
								echo "<td width='".intval(100/$elements[1])."%'>";
								?><input type=text style="width:99%" name="<?php echo $key."[$position]";  ?>" value="<?php echo $post_count_plus[$key][$position]; ?>">
								<?php  echo "</td>";	} 									
									} echo "</tr>";
								} echo "</table>";								
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$post_count_plus_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($post_count_plus[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type=text style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $post_count_plus[$key]; ?>"> <?php 
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
			<p class="submit"><input type="submit" name="submit" value="Save Post Count Plus Settings"></p>
		
		</form>
		<?php
}

function post_count_plus_process_post() {
global $post_count_plus;
	if (bb_current_user_can('administrate')) {
		if (isset($_REQUEST['post_count_plus_reset'])) {
			unset($post_count_plus); 		
			bb_delete_option('post_count_plus');
			post_count_plus_initialize();			
			bb_update_option('post_count_plus',$post_count_plus);
			bb_admin_notice('<b>Post Count Plus: '.__('All Settings Reset To Defaults.').'</b>'); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('post_count_plus_reset','post_count_plus_recount')));	// bug workaround, page doesn't show reset settings
		}
		elseif (isset($_REQUEST['post_count_plus_recount'])) {post_count_plus_recount();}
		elseif (isset($_POST['submit']) && isset($_POST['post_count_plus'])) {
							
			foreach(array_keys( $post_count_plus) as $key) {
				if (isset($_POST[$key])) {$post_count_plus[$key]=$_POST[$key];}
			}
			$found=0; $width=5; $rows=floor(count($post_count_plus['custom_titles'])/$width);
			for ($i=1; $i<$rows; $i++) {	// filter typed in settings here for correctness	
			if ($post_count_plus['custom_titles'][$i*$width+3]) { // strip down roles to lowercase no spaces - could actually try to match real role names?
				$post_count_plus['custom_titles'][$i*$width+3]=str_replace(" ","",strtolower($post_count_plus['custom_titles'][$i*$width+3]));
			}} 
			bb_update_option('post_count_plus',$post_count_plus);
			bb_admin_notice('<b>Post Count Plus: '.__('All Settings Saved.').'</b>');
			// unset($GLOBALS['post_count_plus']); $post_count_plus = bb_get_option('post_count_plus');
		}
	}
}
add_action( 'bb_admin-header.php','post_count_plus_process_post');

function post_count_plus_recount() { 	// count function to re-sync all user post counts and keep extra queries low
	 global $bbdb; 	
	// echo "<html><body><h1>Post Count Plus</h1><h2>counting posts for all users...</h2><pre>";
	$status1=$bbdb->query("DELETE FROM $bbdb->usermeta  WHERE meta_key = 'post_count'");
	$status2=$bbdb->query("INSERT INTO  $bbdb->usermeta  (user_id, meta_key, meta_value) ".
	"SELECT poster_id as user_id,'post_count',count(*) as meta_value FROM $bbdb->posts WHERE post_status = 0 GROUP BY poster_id");
	// echo "<h3>".mysql_affected_rows(). " users counted and inserted.</h3></pre>";
	// echo "<b><a href='".remove_query_arg('post_count_plus_recount')."'>return to forum</a></b>";
	// echo "<scr"."ipt>setTimeout(".'"'."window.location='".remove_query_arg('post_count_plus_recount')."'".'"'.",3000);</scr"."ipt>"; exit();	
	bb_admin_notice('<b>Post Count Plus: '.__('All users and posts recounted.').' '.mysql_affected_rows().' '.__('users counted and inserted.').'</b> ('.__('status: ').$status1.':'.$status2.')'); 	// , 'error' 
} 

function post_count_plus_recount_list(){
global $recount_list;
$recount_list[123] = array('post_count_plus_recount', __('Rebuild Post Count For All Users'));
}	
add_action('bb_recount_list','post_count_plus_recount_list',200);

?>