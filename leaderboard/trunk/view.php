<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; Leaderboard</h3>

<div class="indent">

<h2>Leaderboard</h2>

<form class="days" method="get" action="<?php echo preg_replace( '|\/page\/[0-9]+?|', '/', $_SERVER["REQUEST_URI"]); ?>">

	<ul class="leaderboard">
	<li>View most active contributors: </li><?php $days=isset($_REQUEST['days']) ? intval($_REQUEST['days']) : 0; ?>	
	<?php // echo "<li>in forum ".leaderboard_forum_list()."<br /></li>"; ?>
	<li><input id="leadersall" name="days" value="0" onclick="this.form.submit()" type="radio" <?php if (!$days) {echo 'checked="checked"';} ?>> <label for="leadersall">Of All-Time</label></li>
	<li><input id="leaders30" name="days" value="30" onclick="this.form.submit()" type="radio" <?php if ($days==30) {echo 'checked="checked"';} ?>> <label for="leaders30">From The Past Month</label></li>
	<li><input id="leaders7" name="days" value="7" onclick="this.form.submit()" type="radio" <?php if ($days==7) {echo 'checked="checked"';} ?>> <label for="leaders7">From The Past Week</label></li>
	<li><input id="leaders1"name="days" value="1" onclick="this.form.submit()" type="radio" <?php if ($days==1) {echo 'checked="checked"';} ?>> <label for="leaders1">From Today</label></li>
	</ul>	
</form>

<?php	
	global $leaders,$page,$total; 
	if (empty($leaders)) {
		$output="	
			<br />
			<p>Sorry, there are no users for this selection, please try another.</p>
			<br />
		";
	
	} else { 
		$output="
			<table id='latest' class='leaderboard'>
			<tr>		
			<th width='1'>".__('Rank')."</th>
			<th>". __('User')."</th>		
			<th>". __('Total')."</th>
			<th>". __('Posts')."</th>
			<th>". __('Topics')."</th>
			<th>". __('Comments')."</th>
			<th>". __('Member Since')."</th>
			</tr>
		";

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
		$output.=$r;
	 } 

	$output.="</table>";
} // end empty $leaders

$output.='<div class="nav">'.get_page_number_links( $page, $total ).'</div>';

// $output.=" <small>updated ".bb_since($filemtime)." ago</small>";
	
echo $output;
leaderboard_cache($template,$days,$forums,$output,$page);	
?>

</div>

<?php
function leaderboard_forum_list( $args = '' ) {
global $forum_id, $forum;
	$old_global = $forum;			
	$defaults = array(
		'size'=>1, 
		'callback' => false, 
		'callback_args' => false, 
		name=> 'forums', 
		'id' => 'leaderboard_forums', 
		'tab' => 5, 
		'hierarchical' => 1, 
		'depth' => 0, 
		'child_of' => 0, 
		'disable_categories' => 1 ,
		'onchange'=>'this.form.submit();'
	);
	if ( $args && is_string($args) && false === strpos($args, '=') ) {$args = array( 'callback' => $args );}
	if ( 1 < func_num_args() ) {$args['callback_args'] = func_get_arg(1);}
	$args = wp_parse_args( $args, $defaults ); extract($args, EXTR_SKIP);  if ( !bb_forums( $args ) ) {return;}	
		
	$forums=isset($_REQUEST['forums']) ? $_REQUEST['forums'] : 0; 
	if (is_array($forums)) {foreach ($forums as $key=>$value) {$forums[$key]=intval($value);}} else {$forums=intval($forums);}
	$selected=array_flip((array) $forums);  
	$option_selected = isset($selected[0]) ? ' selected="selected"' : '';
	$r = '<select name="' . $name . (intval($size)>1 ? '[]' : '').'" id="' . $id . '" tabindex="' . intval($tab). '" size="'.$size.'" '.(intval($size)>1 ? 'multiple="multiple" ' : ' ').' onchange="'.$onchange.'">' . "\n";	
	$r .= "\n" . '<option value="0" '.$option_selected.'>&nbsp;' . __('-  All  -').'&nbsp;</option>' . "\n"; 
	$options = array();
	while ( $depth = bb_forum() ) :
		global $forum;   // Globals + References = Pain
		if ( $disable_categories && isset($forum->forum_is_category) && $forum->forum_is_category ) {
			$options[] = array(
				'value' => 0,
				'display' => str_repeat( '&nbsp;&nbsp;&nbsp;', $depth - 1 ) . $forum->forum_name,
				'disabled' => true,
				'selected' => false
			);
			continue;
		}
		$_selected = false;
		if (isset($selected[$forum->forum_id])) {
			$_selected = true;			
		}
		$options[] = array(
			'value' => $forum->forum_id,
			'display' => str_repeat( '&nbsp;&nbsp;&nbsp;', $depth - 1 ) . $forum->forum_name,
			'disabled' => false,
			'selected' => $_selected
		);
	endwhile;
	
	foreach ($options as $option_index => $option_value) {		
		$option_disabled = $option_value['disabled'] ? ' disabled="disabled"' : '';
		$option_selected = $option_value['selected'] ? ' selected="selected"' : '';
		$r .= "\n" . '<option value="' . $option_value['value'] . '"' . $option_disabled . $option_selected . '>&nbsp;' . $option_value['display'] . '</option>' . "\n";
	}
	
	$forum = $old_global;
	$r .= '</select>' . "\n";
	return $r;
}
?>