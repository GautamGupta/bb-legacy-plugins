<?php
/*
Plugin Name: Reputation (Karma) for bbPress 
Description:  Allows the forum and members to award Reputation or "Karma" points to other members for their posts. Can optionally be used as a pseudo currency (ie. "forum dollars").
Plugin URI:  http://bbpress.org/plugins/topic/97
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.6

license: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

donate: http://bbshowcase.org/donate/

to do:
* ajax-ish behaviours
* dhtml prompts instead of javascript popups
*/

if (strpos($self,"bb-reputation.php")===false) :	

function bb_reputation_initialize() { global $bb_reputation;

// edit the following options as desired until an admin menu is created

$bb_reputation['automatic']=true;			// add reputation info automatically into topic posts
$bb_reputation['fix_kakumei']=false;		// hack CSS to fix for Kakumei (and Kakumei based) themes BEFORE version 0.9

$bb_reputation['link']='Reputation';		// the tab name, this needs to be bbpress url acceptable, mostly english only
$bb_reputation['label']=__('Reputation');		// what to call them, ie. Reputation, Karma, Points, BB$'s, etc. 
$bb_reputation['unit']=__('Points');			// measuring unit, ie. Points, Dollars, Pounds, etc.
$bb_reputation['action']=__('Give Reputation Points');	// description of the action
$bb_reputation['label_history']=__('Latest Reputation Received');	// title for history

$bb_reputation['instructions_points']=__('Award how many points?'); 				 // description of the action
$bb_reputation['instructions_reason']=__('Please describe why you are rewarding these points?');  // description of the action
$bb_reputation['cancelled']=__('Points Cancelled');  // description of the cancel action

$bb_reputation['show_bar']=true;			// should a graphical bar be show below the count?
$bb_reputation['bar_width']=95;			// maximum bar width 

$bb_reputation['points_per_post']=1;		// should members get points for each post they make? (0,1,2,etc.)
$bb_reputation['points_per_topic']=2;		// should members get points for each topic they start? (0,1,2,etc.)

$bb_reputation['members_can_award']=true;	// can members award each other points?
$bb_reputation['member_award_role']='participate';	// members can award points at what role level (participate/moderate/administrate)
$bb_reputation['points_from_balance']=true;	// when awarding points, should they come from their own balance?
$bb_reputation['variable_points']=true;		// can members chose how many points to award or use fixed numbers above

$bb_reputation['deduct_role']='administrate';	// members can deduct points at what role level (participate/moderate/administrate)

$bb_reputation['reason_length']=80;		// how long can their description for the award reason be?
$bb_reputation['history']=100;			// track how many transactions per user - on large forums you might want to reduce this

$bb_reputation['profile_insert']=true;		// show total reputation in general profile?
$bb_reputation['history_profile']=true;		// show the reputation history in their profile as a tab
$bb_reputation['history_public']=true;		// reputation history is viewable by all?

$bb_reputation['use_ajax']=false;			// not implimented yet

$bb_reputation['style']="
.bb_reputation {margin:5px 0; line-height:1.1em; font-size:11px;  white-space: nowrap; letter-spacing:-1px;} 
.bb_reputation a {color:green;}
.bb_reputation .bb_number {color:black; padding:0 2px;} 
.bb_reputation a.bb_reputation_link {font-size:150%; font-weight:bold; color:red;}
.bb_reputation_bar {margin-top:2px; height:5px; line-height:5px;font-size:5px; background:lightgreen; border:1px solid #ccc;}
#thread .post li {clear:none;}
";

/*	 stop editing here	*/

$bb_reputation['points_per_post']=intval($bb_reputation['points_per_post']);	// correct config errors
$bb_reputation['points_per_topic']=intval($bb_reputation['points_per_topic']);	// correct config errors

bb_reputation_process_request();

if (is_topic()) {
	if ($bb_reputation['automatic']) {add_filter( 'post_author_title', 'bb_reputation_filter',250); add_filter( 'post_author_title_link', 'bb_reputation_filter',250);}
	add_action('bb_head', 'bb_reputation_add_css');
	add_action('bb_foot', 'bb_reputation_form');
} else {
	if ($bb_reputation['history_profile']) {add_action( 'bb_profile_menu', 'bb_reputation_add_profile_tab');}
	if ($bb_reputation['profile_insert']) {add_filter( 'get_profile_info_keys','bb_reputation_profile_key',250);}
}
if ($bb_reputation['points_per_post'] || $bb_reputation['points_per_topic']) {add_action('bb_new_post', 'bb_reputation_points_for_post');}
} add_action( 'bb_init', 'bb_reputation_initialize');

function bb_reputation($post_id=0) { 
global $bb_reputation;
$post_id= intval(get_post_id( $post_id )); 	
if ($post_id) {$user_id=get_post_author_id($post_id);} else {global $user; $user_id=$user->ID;}
if (empty($user_id)) {return;}
$user = bb_get_user( $user_id ); 
$reputation=intval($user->bb_reputation);		
if ($user_id && !isset($user->bb_reputation) && ($bb_reputation['points_per_post'] || $bb_reputation['points_per_topic'])) {
	global $bbdb; 
	$topics = $bb_reputation['points_per_topic']*intval($bbdb->get_var("SELECT count(*) FROM $bbdb->topics WHERE topic_poster = $user_id  AND topic_status = 0"));
	$posts = $bb_reputation['points_per_post']*intval($bbdb->get_var("SELECT count(*) FROM $bbdb->posts WHERE poster_id = $user_id AND post_position != 1 AND post_status = 0"));		
	$reputation = $topics + $posts;
	bb_update_meta( $user_id, 'bb_reputation', $reputation, 'user' );
}
$link=''; if ($bb_reputation['members_can_award'] && bb_current_user_can($bb_reputation['member_award_role']) && $user_id != bb_get_current_user_info( 'id' )) {$link='<a class="bb_reputation_link" title="'.$bb_reputation['action'].'" href="#post-'.$post_id.'" onClick="bb_reputation('.$post_id.');return false;">+</a>';}
$output='<div class="bb_reputation"><a href="'.get_profile_tab_link( $user_id, $bb_reputation['link'] ).'">'.$bb_reputation['label'].':<span class="bb_number">'.bb_number_format_i18n($reputation).'</span></a>'.$link;		
if ($bb_reputation['show_bar']) {$width=round(($bb_reputation['bar_width']*$reputation/(100*ceil(($reputation+1)/100)))/5)*5;$output.='<div class="bb_reputation_bar" style="width:'.$width.'px;">&nbsp;</div>';}
echo $output.'</div>';
}

function bb_reputation_add_points($post_id=0,$points=0,$reason='') {
global $bb_reputation;
if (!$bb_reputation['members_can_award'] || !bb_current_user_can($bb_reputation['member_award_role'])) {return false;}	// security checks
$post_id= intval(get_post_id( $post_id )); 	
if ($post_id) {$user_id=get_post_author_id($post_id);} else {global $user; $user_id=$user->ID;}	
$reason=strip_tags(str_replace('|','-',stripslashes(substr(trim($reason),0,$bb_reputation['reason_length']))));
$from_id=bb_get_current_user_info( 'id' );
if (!$from_id || !$user_id || !$reason || $user_id == $from_id) {return false;}		// can't do anything without both or if giving to themselves
$user = bb_get_user( $user_id ); 
$points=intval($points);
if (!$bb_reputation['variable_points']) {
	if ($post_id) {
	global $bbdb; 
	$position=intval($bbdb->get_var("SELECT post_position FROM $bbdb->posts WHERE post_id = $post_id AND poster_id = $user_id  AND post_status = 0"));		
	if ($position==1) {$points=1;} elseif ($position>1) {$points=2;}
	} else { $points=1;}	// this shouldn't really happen
}
if ($bb_reputation['points_from_balance'] && $points>0) {
	$from = bb_get_user( $from_id ); 
	$deduct=intval($from->bb_reputation)-$points;
	if ($deduct<0) {return false;}	// can't deduct so don't give points
	bb_update_meta( $from_id, 'bb_reputation', $deduct, 'user' );
}
if ($points>0 || ($points && bb_current_user_can($bb_reputation['deduct_role']))) {
	$add=intval($user->bb_reputation)+$points;			
	bb_update_meta( $user_id, 'bb_reputation', $add, 'user' );
	$history=(string) $user->bb_reputation_history.(time()."|$from_id|$post_id|$points|$reason|");
	bb_update_meta( $user_id, 'bb_reputation_history', $history, 'user' );
}
return $points;
}

function bb_reputation_points_for_post($post_id) {
global $bb_reputation;
$user_id=bb_get_current_user_info( 'id' );
if ($post_id && $user_id) {
	$user=bb_get_user($user_id); 
	$reputation=intval($user->bb_reputation);
	$post=bb_get_post($post_id);
	if ($post->post_position==1) {$points=$bb_reputation['points_per_topic'];}
	elseif ($post->post_position>1) {$points=$bb_reputation['points_per_post'];}	
	bb_update_meta( $user_id, 'bb_reputation', $reputation+$points, 'user' );
} 
}

function bb_reputation_form() {
global $bb_reputation;
if (bb_current_user_can('participate')) :
if (is_topic()) {
echo '<form method="POST" name="bb_reputation_form" id="bb_reputation_form" style="display:none;visibility:hidden">
<input type=hidden name="bb_reputation_id"><input type=hidden name="bb_reputation_reason"><input type=hidden name="bb_reputation_points"></form>';
echo '<scr'.'ipt type="text/javascript">
	function bb_reputation(post_id) {';	
	if ($bb_reputation['variable_points']) {
echo	'var bb_reputation_points = prompt("'.$bb_reputation['instructions_points'].'", "1");
	if (!parseFloat(bb_reputation_points)) {alert("'.$bb_reputation['cancelled'].'"); return; }
	document.bb_reputation_form.bb_reputation_points.value=bb_reputation_points;';
	}	
echo	'var bb_reputation_reason = prompt("'.$bb_reputation['instructions_reason'].'", "");
		if (bb_reputation_reason && bb_reputation_reason.length>1) {
			document.bb_reputation_form.bb_reputation_id.value=post_id;
			document.bb_reputation_form.action="#post-"+post_id;
			document.bb_reputation_form.bb_reputation_reason.value=bb_reputation_reason;
			document.bb_reputation_form.submit();
		} else {alert("'.$bb_reputation['cancelled'].'"); }
	}
	</scr'.'ipt>';
}
endif;
}

function bb_reputation_filter($titlelink) {echo $titlelink; bb_reputation(); return '';}	// only if automatic post inserts are selected

function bb_reputation_process_request() { 
	if (isset($_POST) && isset($_POST['bb_reputation_id']) && isset($_POST['bb_reputation_reason'])) { 
		$points=(isset($_POST['bb_reputation_points'])) ? $_POST['bb_reputation_points'] : 0;
		 bb_reputation_add_points($_POST['bb_reputation_id'],$points,$_POST['bb_reputation_reason']);				
		// header("HTTP/1.1 307 Temporary redirect");
		wp_redirect($_SERVER["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
		// exit();  // not sure why but this makes it fail?
	}	
} 

function bb_reputation_add_css() { 	// inject css
global $bb_reputation;  
$fix=""; if ($bb_reputation['fix_kakumei']) {$fix=".threadauthor  {position:relative; float:left; margin:0 -110px 0 0; right:110px; } .poststuff {clear:both;}";}
if (!empty($bb_reputation['style']) || !empty($fix)) {echo '<style type="text/css">'.$bb_reputation['style'].$fix.'</style>';}
} 

function bb_reputation_profile_key($keys) {	// inserts reputation into profile without hacking
global $bb_reputation, $self;
if (empty($self)==true && isset($_GET['tab'])==false && bb_get_location()=="profile-page") {	
	(array) $keys=array_merge(array_slice((array) $keys, 0 , 1), array('bb_reputation' => array(0, $bb_reputation['label'])), array_slice((array) $keys,  1));    
}
return (array) $keys;
 }

function bb_reputation_add_profile_tab() {
global $bb_reputation, $self;
if (!$self) {	// I have no idea exactly why this is but apparently bb_profile_menu action is called twice? bug?
	if ($bb_reputation['history_profile']) {
		if ($bb_reputation['history_public']) {
			$role="";  // $role="read";
		} else {$role="edit_user";}
	add_profile_tab($bb_reputation['link'], $role, $role, __FILE__ );	
	}
}		
}

elseif ($bb_reputation['history_profile'])  :		// we're in the profile tab, is it enabled?

bb_send_headers();
bb_get_header();
?>
<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <a href="<?php echo get_user_profile_link($user->ID ).'">'.__("Profile"); ?></a> &raquo; <?php echo $bb_reputation['label']; ?></h3>

<?php if (($bb_reputation['history_public']) || bb_current_user_can('edit_user', $user->ID)) {   ?>
<div class="indent">
<?php
	echo (!empty($error_message)) ? '<div class="infobox"><strong>'.__($error_message).'</strong></div>' : '';
	echo (!empty($success_message)) ? '<div class="notice">'.__($success_message).'</div>' : '';
?>

<h2 id="userlogin"><?php echo $bb_reputation['label'].__(' for ').get_user_name( $user->ID ); ?></h2>

<div id="user-threads" class="user-recent">
<h4><?php echo $bb_reputation['label_history']; ?> <small>(<?php printf(__('%s total ').$bb_reputation['unit'], bb_number_format_i18n($user->bb_reputation)); ?>)</small></h4>

<?php $matches=explode("|",(string) $user->bb_reputation_history); if ($matches && is_array($matches)) : ?>
<ol>
<?php $rows=floor(count($matches)/5); 		// 5 items per row
for ($offset=$rows-1; $offset>=0; $offset--) { ?>
<li<?php alt_class('threads'); ?>>
	<?php 	
	// time()."|$from_id|$post_id|$points|$reason
	$y=$offset*5; 				// 5 items per row
	printf(__('%s ago'),bb_since($matches[$y])); 
	echo ', <a href="' . get_user_profile_link($matches[$y+1]) . '">' . get_user_name( $matches[$y+1] ) . '</a> ';
	if (intval($matches[$y+2])>0) {
	$bb_post = bb_get_post( $matches[$y+2] ); 
	echo __('on').' <a href="' . get_post_link($matches[$y+2] ) . '">&ldquo;' . get_topic_title($bb_post->topic_id) . '&rdquo;</a> ';
	}
	echo "<span style='white-space:nowrap'>".__('gave')." ".$matches[$y+3]." ".$bb_reputation['unit']." "; 
	echo __('for')." ".strip_tags($matches[$y+4])."</span>"; 
	?>
</li>
<?php } ?>
</ol>
<?php else : if ( $page ) : ?>
<p><?php _e('No more Reputation.') ?></p>
<?php else : ?>
<p><?php _e('No Reputation yet.') ?></p>
<?php endif; endif;?>
</div><br style="clear: both;" />

</div>
<?php 
} // security check
bb_get_footer();
endif;	// profile tab check
?>