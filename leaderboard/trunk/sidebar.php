<?php	
	if (empty($leaders)) {
	
		$output="
			<br />
			<p>Sorry, there are no users yet.</p>
			<br />
		";
	
	} else { 
	
		$output="
			<table class='leaderboard'>
			<tr>		
				<th width='1'>".__('Rank')."</th>
				<th>".__('User')."</th>				
				<th>".__('Posts')."</th>		
				<th>".__('Comments')."</th>
			</tr>";
			
	foreach ($leaders as $rank=>$leader) {
	$user = bb_get_user( $leader->ID );
		
		$output .= "
			<tr " . get_alt_class("leaderboard") . ">
	 			<td class='num'>".($rank+1)."</td>
				<td><a href='" . get_user_profile_link( $user->ID ) . "'>" . " " . get_user_name( $user->ID ) . "</a></td>
	 			<td class='num'>".($leader->post_count+$leader->topic_count)."</td>
	 			<td class='num'>$leader->comment_count</td>
			</tr>";		
	
	} 
		$output.="</table>";	
	}
	// $output.=" <small>updated ".bb_since($filemtime)." ago</small>";
	
	echo $output;
	leaderboard_cache($template,$days,$forums,$output);	
?>