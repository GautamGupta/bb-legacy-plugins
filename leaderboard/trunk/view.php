<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; Leaderboard</h3>

<div class="indent">

<h2>Leaderboard</h2>

<form class="days" method="get" action="<?php echo preg_replace( '|\/page\/[0-9]+?|', '/', $_SERVER["REQUEST_URI"]); ?>">

	<ul class="leaderboard">
	<li>View most active contributors: </li><?php $days=isset($_REQUEST['days']) ? intval($_REQUEST['days']) : 0; ?>
	<li><input id="leadersall" name="days" value="0" onclick="this.form.submit()" type="radio" <?php if (!$days) {echo 'checked="checked"';} ?>> <label for="leadersall">Of All-Time</label></li>
	<li><input id="leaders30" name="days" value="30" onclick="this.form.submit()" type="radio" <?php if ($days==30) {echo 'checked="checked"';} ?>> <label for="leaders30">From The Past Month</label></li>
	<li><input id="leaders7" name="days" value="7" onclick="this.form.submit()" type="radio" <?php if ($days==7) {echo 'checked="checked"';} ?>> <label for="leaders7">From The Past Week</label></li>
	<li><input id="leaders1"name="days" value="1" onclick="this.form.submit()" type="radio" <?php if ($days==1) {echo 'checked="checked"';} ?>> <label for="leaders1">From Today</label></li>
	</ul>	
</form>

<?php	
	global $leaders,$page,$total; 
	if (empty($leaders)) {
?>	
	<br />
	<p>Sorry, there are no users for this selection, please try another.</p>
	<br />
	
<?php	} else {  ?>

<table id="latest" class="leaderboard">
	<tr>		
		<th width="1"><?php _e('Rank'); ?></th>
		<th><?php _e('User'); ?></th>		
		<th><?php _e('Total'); ?></th>
		<th><?php _e('Posts'); ?></th>
		<th><?php _e('Topics'); ?></th>
		<th><?php _e('Comments'); ?></th>
		<th><?php _e('Member Since'); ?></th>

	</tr>
<?php 
	foreach ($leaders as $rank=>$leader) {
		$user = bb_get_user( $leader->ID ); $r="";
		$r .= "<tr id='user-$user->ID'" . get_alt_class("leaderboard") . ">";	
		$r .= "<td class='num'>".($rank+1+(($page-1)*$leaderboard['per_page']))."</td>";			
		$r .= "<td><a href='" . get_user_profile_link( $user->ID ) . "'>" .  bb_get_avatar( $user->ID, 32 ) . " " . get_user_name( $user->ID ) . "</a></td>";		
		$r .= "<td class='num'>$leader->total_count</td>";
		$r .= "<td class='num'>$leader->post_count</td>";
		$r .= "<td class='num'>$leader->topic_count</td>";
		$r .= "<td class='num'>$leader->comment_count</td>";
		$r .= "<td>" . bb_since( $user->user_registered ) . "</td>";
		$r .= "</tr>";
		echo $r;
	 } 
?>
</table>
<?php } // end empty $leaders ?>

<div class="nav"><?php echo get_page_number_links( $page, $total ); ?></div>

</div>
