<?php
/*
Plugin Name:  Post Count Plus - Dynamic.Titles & More!
Plugin URI:  http://bbpress.org/plugins/topic/
Description: An enhanced "user post count" with "custom titles" for topics and profiles, based on posts and days of membership, with cached results for faster pages.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 1.0

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Instructions:   install, activate, tinker with settings in admin menu
*/

global $post_count_plus;

function post_count_plus($user_id=0, $posts_only=0, $titlelink='') {
global $post_count_plus;
if ($posts_only || (bb_get_location()=="profile-page" && $posts_only==0)) {echo __("Posts: ").bb_number_format_i18n(get_user_post_count($user_id));}
else {
	// if (!$titlelink || $post_count_plus['custom_title']) { // calculate custom titles here
	if (!$user_id) {
		$location=bb_get_location(); 
		if ($location=="topic-page") {$user_id=get_post_author_id();} 
		elseif ($location=="profile-page") {global $user; $user_id=$user->ID;}
		else {$user_id=bb_get_current_user_info( 'id' );}
	} 
	$user = bb_get_user( $user_id );
	if ($user) {			 		
		if ($post_count_plus['custom_title']) { 
			if ($user->title) {$title=$user->title;}
			else {$title=post_count_plus_custom_title($user_id);}
		} else {$title=get_post_author_title();}  // $title=$user->title; // old title
		$url=""; 
		switch ($post_count_plus['title_link']) :
			case "Profile" : $url=get_user_profile_link($user_id); break;
			case "Author URL" : $url=$user->user_url; break; 
			case "Nothing" : $url=""; break;
		endswitch;
		if ($url) {$titlelink='<a href="' . attribute_escape($url) . '">' . $title . '</a>';} else {$titlelink=$title;}
	}

echo "<p class='post_count_plus'>";
	echo "<b>$titlelink</b><br /><small>";	
	if ($post_count_plus['join_date']) {post_count_plus_join_date($user_id);  echo "<br />";}
	if ($post_count_plus['post_count']) {echo __("Posts: ").bb_number_format_i18n(get_user_post_count($user_id));}
echo "</small></p>";
}
}

function get_user_post_count($user_id=0) {
if (!$user_id) {
	$location=bb_get_location(); 
	if ($location=="topic-page") {$user_id=get_post_author_id();} 
	elseif ($location=="profile-page") {global $user; $user_id=$user->ID;}
}
if ($user_id) {
	$posts=bb_get_usermeta( $user_id, 'post_count');	// even this should be bypassed at some point with a simple cache check & mysql query - sometimes causes 2 queries
	if (!$posts) {
		global $bbdb; $posts=$bbdb->get_var("SELECT count(*) FROM $bbdb->posts WHERE poster_id = $user_id AND post_status = 0");
		// bb_update_usermeta( $user_id, 'post_count', $posts);  // uses too many queries, we'll do it directly
		$bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ('".$user_id."', 'post_count', '".$posts."') ");
		
	}
}	
if ($posts) {return  $posts;} else {return 0;}
}

function post_count_plus_update() {
$user_id = bb_get_current_user_info( 'id' );  $posts=intval(bb_get_usermeta( $user_id, 'post_count'));
if ($user_id && $posts) {global $bbdb; $bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".($posts+1)."' WHERE user_id = '".$user_id."' AND meta_key = 'post_count' LIMIT 1");}
} 
add_action('bb_new_post', 'post_count_plus_update',200);

function post_count_plus_join_date($user_id=0,$date_format='') {
global $post_count_plus; 
if (!$date_format) {$date_format=$post_count_plus['join_date_format'];}
	if (!$user_id) {
		$location=bb_get_location(); 
		if ($location=="topic-page") {$user_id=get_post_author_id();} 
		elseif ($location=="profile-page") {global $user; $user_id=$user->ID;}
		else {$user_id=bb_get_current_user_info( 'id' );}
	}
	$user = bb_get_user( $user_id );
	if ($user) {	 
		echo  __('Joined: ').bb_gmdate_i18n(__($date_format), bb_gmtstrtotime( $user->user_registered )); 
	}	
}

function post_count_plus_find_title($user_id=0,$posts=0,$days=0,$role='') {	
global $post_count_plus,$post_count_plus_cache;
if ($user_id) {
	if (isset($post_count_plus[$user_id])) {return $post_count_plus[$user_id];}
	if (!$posts) {$posts=get_user_post_count($user_id); $user = bb_get_user( $user_id );}
	if (!$days) {$days=intval(((bb_current_time('timestamp') - date('Z')) - mysql2date('U', bb_gmtstrtotime( $user->user_registered )))/86400);}
	if (!$role) {$role=array_pop(array_keys($user->bb_capabilities,array('blocked','inactive','member','moderator','administrator','keymaster')));}
}
$found=0; $width=5; $rows=floor(count($post_count_plus['custom_titles'])/$width);
for ($i=1; $i<$rows; $i++) {
if ($post_count_plus['custom_titles'][$i*$width]) {
	$posts0=$post_count_plus['custom_titles'][$i*$width+1];
	$days0=$post_count_plus['custom_titles'][$i*$width+2];
	$role0=$post_count_plus['custom_titles'][$i*$width+3];		
	if ((!$posts0 || $posts>=$posts0) && (!$days0 || $days>=$days0) && (!$role0 || $role==$role0)) {$found=$i*$width;}
}	
} // we don't break out of the loop because we need to see if there is a higher value more appropriate - array needs to be in sorted order though

$post_count_plus_cache[$user_id]=$found;	// cache for same page queries
return $found;
}

function post_count_plus_custom_title($user_id=0,$posts=0,$days=0,$role='') {
global $post_count_plus;
$title=""; 
$found=post_count_plus_find_title($user_id,$posts,$days,$role);
if ($found) { 
	$title=$post_count_plus['custom_titles'][$found];
	$color=$post_count_plus['custom_titles'][$found+4];
	if ($color) {$title='<font color="'.$color.'">'.$title.'</font>';}
}
return $title;
}

function post_count_plus_user_color($user_name, $user_id) {
global $post_count_plus;
if (bb_get_location()=="topic-page") {
	if ($post_count_plus['user_color']) {		
		$found=post_count_plus_find_title($user_id);		
		$color=$post_count_plus['custom_titles'][$found+4];
		if ($color) {$user_name='<font color="'.$color.'">'.$user_name.'</font>';}
	}
}
return $user_name;
}

function post_count_plus_user_link( $url, $user_id) {	// forces links to profile instead of author's url on topic pages
global $post_count_plus;
if (bb_get_location()=="topic-page") {
	switch ($post_count_plus['user_link']) :
		case "Profile" : return attribute_escape( get_user_profile_link( $user_id)); break;
		case "Author URL" : return $url; break;
		case "Nothing" : return ""; break;
	endswitch;
}
return $url;
}

function post_count_plus_profile_key($keys) {	// inserts post_count into profile without hacking
global $post_count_plus; 
if (!isset($_GET['tab']) && bb_get_location()=="profile-page") {
	$keys=array_merge(array_slice($keys, 0 , 1), array('post_count' => array(0, __('Posts'))), array_slice($keys,  1));
}
return $keys;
 }

function post_count_plus_add_css() { global $post_count_plus;  echo '<style type="text/css">'.$post_count_plus['style'].'</style>';} // inject css

function post_count_plus_filter($titlelink) {post_count_plus(0,0,$titlelink); return '';}	// only if automatic inserts are selected

function post_count_plus_initialize() {
	global $bb, $bb_current_user, $post_count_plus, $post_count_plus_type, $post_count_plus_label;
	if(!isset($post_count_plus)) {$post_count_plus = bb_get_option('post_count_plus');
		if (!$post_count_plus) {
		$post_count_plus['activate']=true;
		$post_count_plus['post_count']=true;
		$post_count_plus['join_date']=true;		
		$post_count_plus['custom_title']=true;
		$post_count_plus['profile_insert']=true;
		$post_count_plus['user_color']=true;
		$post_count_plus['user_link']="Profile";
		$post_count_plus['title_link']="Profile";
		$post_count_plus['join_date_format']="M 'y";	
		$post_count_plus['style']=".post_count_plus {color:SlateGray; text-align:center;}\n.post_count_plus a {color:DarkCyan;}";		
		$post_count_plus['custom_titles']=array(
		"New Title",	"Minimum Posts", "Minimum Days", "Minimum Role", "Color",
		"new member",	"0",		"0",			"",	"SlateBlue",
		"junior member","5","14","","Navy",
		"member","10","30","","",
		"senior member","50","180","","#0000FF",
		"preferred member","100","365","","SkyBlue",
		"mod","0","0","moderator","Red",
		"admin","0","0","keymaster","DarkRed");
		}		
		$post_count_plus_label['activate']=__("Use features without template editing ?");
		$post_count_plus_label['post_count']=__("Show post counts for users in topic pages ?");
		$post_count_plus_label['join_date']=__("Show joined date for users in topic pages ?");
		$post_count_plus_label['custom_title']=__("Show custom titles based on posts & membership ?");
		$post_count_plus_label['profile_insert']=__("Insert post count for users into profile?");
		$post_count_plus_label['user_color']=__("Match USERNAME color to TITLE color?");
		$post_count_plus_label['user_link']=__("Where should their USERNAME link to?");		
		$post_count_plus_label['title_link']=__("Where should their TITLE link to?");
		$post_count_plus_label['join_date_format']=__("Custom <a target=_blank href='http://php.net/date#function.date'>format</a> for user joined date:");
		$post_count_plus_label['style']=__("Custom style for post author info:");
		$post_count_plus_label['custom_titles']=__("<h2>Custom Titles</h2>Enter any special titles given based upon number of posts, days of membership, and/or role.<br>Each field is optional, but at least one minimum is required.<br />");

		$post_count_plus_type['activate']="binary";		
		$post_count_plus_type['post_count']="binary";
		$post_count_plus_type['join_date']="binary";				
		$post_count_plus_type['custom_title']="binary";						
		$post_count_plus_type['profile_insert']="binary";
		$post_count_plus_type['user_color']="binary";
		$post_count_plus_type['user_link']="Profile,Author URL,Nothing";
		$post_count_plus_type['title_link']="Profile,Author URL,Nothing";
		$post_count_plus_type['join_date_format']="input";
		$post_count_plus_type['style']="textarea";
		$post_count_plus_type['custom_titles']="array,5,10";
	}
if ($post_count_plus['profile_insert']) {add_filter( 'get_profile_info_keys','post_count_plus_profile_key',200);}
if ($post_count_plus['activate']) {add_filter( 'post_author_title', 'post_count_plus_filter');}
if ($post_count_plus['style']) {add_action('bb_head', 'post_count_plus_add_css');}
add_filter( 'get_user_link','post_count_plus_user_link',200,2);
if ($post_count_plus['user_color']) {add_filter( 'get_post_author','post_count_plus_user_color',200,2);}
}	
add_action( 'bb_init', 'post_count_plus_initialize');
add_action( 'init', 'post_count_plus_initialize');

function post_count_plus_add_admin_page() {bb_admin_add_submenu(__('Post Count Plus'), 'administrate', 'post_count_plus_admin_page');}
add_action( 'bb_admin_menu_generator', 'post_count_plus_add_admin_page' );

function post_count_plus_admin_page() {
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
			bb_admin_notice('<b>Post Count Plus: '.__('All Settings Reset To Defaults.')); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('post_count_plus_reset','post_count_plus_recount')));	// bug workaround, page doesn't show reset settings
		}
		elseif (isset($_REQUEST['post_count_plus_recount'])) {post_count_plus_recount();}
		elseif (isset($_POST['submit']) && isset($_POST['post_count_plus'])) {
			foreach(array_keys( $post_count_plus) as $key) {
				if (isset($_POST[$key])) {$post_count_plus[$key]=$_POST[$key];}
			}
			bb_update_option('post_count_plus',$post_count_plus);
			bb_admin_notice('<b>Post Count Plus: '.__('All Settings Saved.'));
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