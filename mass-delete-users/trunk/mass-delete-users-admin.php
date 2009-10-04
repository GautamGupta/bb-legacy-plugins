<?php

bb_register_activation_hook(str_replace(array(str_replace("/","\\",BB_PLUGIN_DIR),str_replace("/","\\",BB_CORE_PLUGIN_DIR)),array("user#","core#"),__FILE__), 'mdu_install');
add_action( 'bb_admin_head', 'mass_delete_users_add_css'); 


function mass_delete_users() {
	if ( !bb_current_user_can('administrate') ) {die(__("Now how'd you get here?  And what did you think you'd be doing?"));}
	add_action( 'bb_get_option_page_topics', 'mass_delete_users_topic_limit',250);
	global $bbdb, $bb, $bb_user_cache, $page, $mass_delete_users_options;
		
	$columns="checkbox, id, user, email, registered, role, posts, comments, last";
	
	if (isset($_GET['mass_delete_users_reset'])) {
		bb_delete_option('mass_delete_users_options');			
		wp_redirect(remove_query_arg(array('mass_delete_users_options','mass_delete_users_reset')));
	}
	if ( !empty( $_POST['mass_delete_users_save_options'] ) ) {				
		$mass_delete_users_options['mass_delete_users_columns']=implode(",",array_unique(array_map('trim',explode(",",strtolower(stripslashes($_POST['mass_delete_users_columns'].", $columns" ))))));
		$mass_delete_users_options['mass_delete_users_css']=stripslashes($_POST['mass_delete_users_css']);
		bb_update_option('mass_delete_users_options',$mass_delete_users_options);
		wp_redirect(remove_query_arg(array('mass_delete_users_options','mass_delete_users_reset')));	// may not work since headers are already sent
	}
	
	echo '<div style="text-align:right;margin-bottom:-1.5em;">';
	if (isset($_GET['mass_delete_users_options'])) { 	
	echo '[ <a href="'.bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users").'&mass_delete_users_reset=1">Reset To Defaults</a> ]';
	} else { echo '[ <a href="'.bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users").'&mass_delete_users_options=1">Settings</a> ]';}
	echo '</div>';
	
	echo "<h2><a style='color:black;border:0;text-decoration:none;' href='".bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users")."'>".__('Mass Delete Users')."</a></h2>";
		
	if(!isset($mass_delete_users_options)) {$mass_delete_users_options = bb_get_option('mass_delete_users_options');	}
	if (!isset($mass_delete_users_options['mass_delete_users_columns']) || is_array($mass_delete_users_options['mass_delete_users_columns'])) {
		$mass_delete_users_options['mass_delete_users_columns']=$columns;
		bb_update_option('mass_delete_users_options',$mass_delete_users_options);
	}

	$mass_delete_users_columns=explode(",",strtolower($mass_delete_users_options['mass_delete_users_columns']));
	array_walk($mass_delete_users_columns ,create_function('&$arr','$arr=trim($arr);')); 		

	if (isset($_GET['mass_delete_users_options'])) { ?>
	<form action="<?php echo bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users"); ?>" method="post" id="mass-delete-users-options">
	
	<fieldset><legend><strong>Mass Delete Column Order</strong> - default: <?php echo $columns; ?></legend>
	<input name="mass_delete_users_columns" id="mass_delete_users_columns" type="text" size="80" value="<?php echo $mass_delete_users_options['mass_delete_users_columns']; ?>" />
	<span style="padding-left:1em;" class=submit><input class=submit type="submit" name="mass_delete_users_save_options" value="<?php _e('Save Options') ?> &raquo;"  /></span>
	</fieldset>
		
	<fieldset><legend><b>Mass Delete Users CSS</b></legend>
	<textarea name="mass_delete_users_css" id="mass_delete_users_css" cols="100" rows="10"><?php echo $mass_delete_users_options['mass_delete_users_css']; ?></textarea>
	</fieldset>
	</form>
	<br clear=both />
	<hr />
	<?php 	}

if ( !empty( $_POST['mass_delete_users_delete_users'] ) ) :
	bb_check_admin_referer('mass-delete-users-bulk-users');

	$i = 0;
	
	$bbdb->hide_errors();	// bbPress still has some db function issues with topic delete/undelete	
	
	foreach ($_POST['mass_delete_users_delete_users'] as $bb_user_id) : // Check the permissions on each
		$bb_user_id = (int) $bb_user_id;
		// $bb_user_id = $bbdb->get_var("SELECT post_id FROM $bbdb->posts WHERE post_id = $bb_post");
		// $authordata = bb_get_usermeta( $bbdb->get_var("SELECT poster_id FROM $bbdb->posts WHERE ID = $bb_user_id") );
		if ( bb_current_user_can('edit_users') ) {  // , $bb_user_id) ) {  
			if ( !empty( $_POST['mass_delete_users_delete_button'] ) ) {bb_delete_user( $bb_user_id, 0 );}			
			++$i;
		}
	endforeach;
	
	$bbdb->show_errors();	// bbPress still has some db function issues with topic delete/undelete
	// $bbdb->flush();	
	// global $bb_cache,$bb_post_cache, $bb_topic_cache;  unset($bb_cache); unset($bb_post_cache); unset($bb_topic_cache);

	
	echo '<div  id="message" class="updated fade" style="clear:both;"><p>';	
 	if ( !empty( $_POST['mass_delete_users_delete_button'] ) ) {printf(__('%s users deleted.'), $i);}
	if ( !empty( $_POST['mass_delete_users_undelete_button'] ) ) {printf(__('%s users undeleted.'), $i);}		
	echo '</p></div>';
endif;

if (isset($_GET['user_text']))       {$user_text = $bbdb->escape(substr($_GET['user_text'],0,50));} else {$user_text="";}
if (isset($_GET['user_status']))  {$user_status = $bbdb->escape(substr($_GET['user_status'],0,3));} else {$user_status="0";}
if (isset($_GET['user_role']))      {$user_role = $bbdb->escape(substr($_GET['user_role'],0,30));} else {$user_role="";}
if (isset($_GET['user_order']))   {$user_order = ($_GET['user_order']=="ASC") ? "ASC" : "DESC";} else {$user_order="DESC";}
if (isset($_GET['no_posts']))       {$no_posts = intval($_GET['no_posts']);} else {$no_posts = 0;}
if (isset($_GET['exact_match'])) {$exact_match = intval($_GET['exact_match']);} else {$exact_match = 0;}
if (isset($_GET['per_page']))      {$per_page = intval($_GET['per_page']);} else {$per_page="20";} 
$offset = (intval($page) -1) *  $per_page;  		// if (isset($_GET['page']))  {} else {$offset = 0;}

$query="";

if ($user_role) {
	if ($user_role=="inactive") {$norole=" OR meta_value IS NULL ";} else {$norole="";}
	$query.="LEFT JOIN $bbdb->usermeta as meta ON ID=meta.user_id AND meta_key='$bbdb->prefix"."capabilities' WHERE (meta_value LIKE '%:\"$user_role\";%' $norole)";
}

if ($user_text) {	
	if (empty($query)) {$query.=" WHERE ";} else {$query.=" AND ";}
	$query.="(";
	$query.=($exact_match) ? " (user_login REGEXP '[[:<:]]".$user_text."[[:>:]]') " : " (user_login LIKE '%$user_text%') ";
	$query.=" OR ";
	$query.=($exact_match) ? " (display_name REGEXP '[[:<:]]".$user_text."[[:>:]]') " : " (display_name LIKE '%$user_text%') ";
	$query.=" OR ";
	$query.=($exact_match) ? " (user_email REGEXP '[[:<:]]".$user_text."[[:>:]]') " : " (user_email LIKE '%$user_text%') ";
	$query.=")";
}

// users with no posts or comments
if ($no_posts) {
if (empty($query)) {$query.=" WHERE ";} else {$query.=" AND ";}
$query.=" poster_id is NULL ";
$query=" LEFT JOIN $bbdb->posts ON ID=poster_id ".$query;
if (!empty($bb->wp_table_prefix)) {
		$query="LEFT JOIN $bb->wp_table_prefix"."comments as comments ON ID=comments.user_id ".$query." AND comments.user_id is NULL ";		
}
}

$query="FROM $bbdb->users ".$query;
	
	$sort=""; if (isset($_GET['user_order'])) {$user_order=$_GET['user_order'];}
	if (empty($user_order) || $user_order=="oldest")  {$sort=" ORDER BY user_registered ASC ";}
	elseif ($user_order=="newest") {$sort=" ORDER BY user_registered DESC ";}	
	elseif ($user_order=="username")  {$sort=" ORDER BY user_login ASC ";}
	elseif ($user_order=="email")  {$sort=" ORDER BY user_email ASC ";}
	
	$restrict=" LIMIT $offset,$per_page";  // print $restrict;
	
	// echo $query;	// diagnostic
	$total = $bbdb->get_var("SELECT COUNT(ID) ".$query);  	// intval(bb_count_last_query($query));
	if ($total) {$user_ids=$bbdb->get_col("SELECT ID ".$query.$sort.$restrict);} else {unset($user_ids);}
	
	// print $query.$sort.$restrict;
	
	if (!empty($user_ids)) {	
		foreach ($user_ids as $user_id) {$users[$user_id]->ID=$ids[$user_id]=$user_id;}  
		bb_cache_users($ids); $ids=join(',', $ids);

		$query="SELECT poster_id,count(post_id) as posts FROM $bbdb->posts WHERE poster_id IN ($ids) GROUP BY poster_id";
		$posts=$bbdb->get_results($query);
		if (!empty($posts)) {foreach ($posts as $post) {$users[$post->poster_id]->posts=$post->posts;}}
		unset($posts);		

		$query="SELECT poster_id, max(post_time) as last FROM $bbdb->posts WHERE poster_id IN ($ids) GROUP BY poster_id";
		$posts=$bbdb->get_results($query);
		if (!empty($posts)) {foreach ($posts as $post) {$users[$post->poster_id]->last=$post->last;}}
		unset($posts);		

		if (!empty($bb->wp_table_prefix)) {
		$query="SELECT user_id,count(comment_ID) as comments FROM $bb->wp_table_prefix"."comments WHERE user_id IN ($ids) GROUP BY user_id";
		$comments=$bbdb->get_results($query);
		if (!empty($comments)) {foreach ($comments as $comment) {$users[$comment->user_id]->comments=$comment->comments;}}
		unset($comments);
		}				
	}

// if ($user_status!="all") {$query.=(($post_text || $users) ? " AND " : "")." user_status = '$user_status' ";}
// $restrict=" ORDER BY post_time $user_order LIMIT $offset,$per_page";

?>

<form action="<?php echo bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users"); ?>" method="get" id="post-search-form" class="search-form">
	<fieldset><legend><?php _e('Show Logins, Names or Email &hellip;') ?></legend> 
	<input name="user_text" id="user-text" class="text-input" type="text" value="<?php echo wp_specialchars($user_text); ?>" size="25" /> 	
	</fieldset>

<?php /*  selection by forum and tag not included in initial versions
<fieldset><legend>Forum &hellip;</legend>
<select name="forum_id" id="forum-id" tabindex="5">
<option value="0">Any</option>
<option value="1"> bbPress chat</option>
</select>
</fieldset>
<fieldset><legend>Tag&hellip;</legend>
<input name="tag" id="topic-tag" class="text-input" value="" type="text" />	</fieldset>

<fieldset><legend>Post Author&hellip;</legend>
<input name="post_author" id="post-author" class="text-input" type="text" value="<?php if (isset($_GET['post_author'])) echo wp_specialchars($_GET['post_author'], 1); ?>" />	
</fieldset>
*/ ?>

	<!-- fieldset><legend>User Status &hellip;</legend>
		<select name="user_status" id="user-status">			
			<option value="active" <?php echo ($user_status=="active") ? 'selected="selected"' : ''; ?>>Active</option>
			<option value="deleted" <?php echo ($user_status=="deleted") ? 'selected="selected"' : ''; ?>>Deleted</option>			
			<option value="all" <?php echo ($user_status=="all") ? 'selected="selected"' : ''; ?>>All</option>
		</select>
	</fieldset -->

	<fieldset><legend>User Role &hellip;</legend>
		<select name="user_role" id="user-role">			
			<option value="" <?php echo ($user_role=="any") ? 'selected="selected"' : ''; ?>>Any</option>
			
			<?php  global $bb_roles;  $roles=array_keys($bb_roles->role_names);
			foreach ($roles as $role) {					
			echo '<option value="'.$role.'"'.(($user_role==$role) ? 'selected="selected"' : '').'>'.$role.'</option>';
			}
			?>
		</select>
	</fieldset>
	
	<fieldset><legend>Sort Order &hellip;</legend>
		<select name="user_order" id="user-order">
			<option value="oldest" <?php echo ($user_order=="oldest") ? 'selected="selected"' : ''; ?>>Oldest</option>
			<option value="newest" <?php echo ($user_order=="newest") ? 'selected="selected"' : ''; ?>>Newest</option>			
			<option value="username" <?php echo ($user_order=="username") ? 'selected="selected"' : ''; ?>>Username</option>
			<option value="email" <?php echo ($user_order=="email") ? 'selected="selected"' : ''; ?>>Email</option>			
		</select>
	</fieldset>
	
	<fieldset><legend>Per Page</legend>
		<select name="per_page" id="per-page">
			<option value="20" <?php echo ($per_page==20) ? 'selected="selected"' : ''; ?>>20</option>
			<option value="50" <?php echo ($per_page==50) ? 'selected="selected"' : ''; ?>>50</option>			
			<option value="100" <?php echo ($per_page==100) ? 'selected="selected"' : ''; ?>>100</option>
			<option value="250" <?php echo ($per_page==250) ? 'selected="selected"' : ''; ?>>250</option>
			<option value="500" <?php echo ($per_page==500) ? 'selected="selected"' : ''; ?>>500</option>
		</select>
	</fieldset>

	<fieldset><legend>No Posts</legend>
	<span style="padding-left:1em;"><input style="margin:0.4em 0 0 1em; height:1.4em;width:1.4em;" name="no_posts" id="no-posts" class="checkbox" type="checkbox" value="1" <?php echo ($no_posts) ? 'checked="checked"' : ''; ?> /></span>
	</fieldset>

	<fieldset><legend>Exact Match</legend>	
	<span style="padding-left:1em;"><input style="margin:0.4em 0 0 1em; height:1.4em;width:1.4em;" name="exact_match" id="exact-match" class="checkbox" type="checkbox" value="1" <?php echo ($exact_match) ? 'checked="checked"' : ''; ?> /></span>
    	</fieldset>
    	
    	<fieldset><legend> </legend>	
    	<span class=submit><input class=submit type="submit" name="submit" value="<?php _e('Search') ?> &raquo;"  /></span>
    	<span style="padding-left:1em;" class=submit><input onclick="window.location='<?php echo bb_get_option('uri') . 'bb-admin/' . bb_get_admin_tab_link("mass_delete_users"); ?>'" class=submit type="reset" name="reset" value="<?php _e('Clear') ?>"  /></span>
    	<input type="hidden" name="plugin" value="mass_delete_users"  />
    	</fieldset>
    
 </form>
<br clear="both" />

<?php

if ($total) {echo $pagelinks="<p style='clear:left'>[ ".(($total>$per_page) ? "showing ".(($page-1)*$per_page+1)." - ".(($total<$page*$per_page) ? $total : $page*$per_page)." of " : "")."$total users found ] ".'<span style="padding-left:1em">'.get_page_number_links( $page, $total )."</span></p>";}

if (!empty($users)) {

if (empty($bb->wp_table_prefix)) {unset($mass_delete_users_columns[array_search('comments',$mass_delete_users_columns)]);}

// lazy cache loading to radically reduce query count
/*
foreach ($bb_posts as $bb_post) {$users[$bb_post->poster_id]=$bb_post->poster_id; $topics[$bb_post->topic_id]=$bb_post->topic_id;}  
$users=join(',', $users); $topics=join(',', $topics);
$users=$bbdb->get_results("SELECT ID,user_login,user_registered FROM $bbdb->users WHERE ID IN ($users)");
$users = bb_append_meta( $users, 'user' );
unset($users); 
$topics=$bbdb->get_results("SELECT topic_id,topic_title,topic_slug FROM $bbdb->topics WHERE topic_id IN ($topics)");
$topics = bb_append_meta( $topics, 'topic' );
unset($topics);
*/

echo '<form name="deleteusers" id="deleteusers" action="" method="post"> ';
bb_nonce_field('mass-delete-users-bulk-users');

echo '<table class="widefat">
<thead>
<tr>';
foreach ($mass_delete_users_columns as $position) :	
switch ($position) :
case "checkbox" :
    echo '<th scope="col"><input type="checkbox" onclick="checkAll(this,document.getElementById(\'deleteusers\'));" /></th>';
break;
// checkbox , id, name, email, role, posts, comments, last, registered, actions
//  checkbox,id,login,name,email,url,registered,status,role,posts,comments,last
case "id" :   
    echo '<th scope="col">' . __('ID') . '</th>';
break;   
case "user" :   
   echo '<th scope="col">' .  __('User') . '</th>';
break;
case "email" :
   echo '<th scope="col">' . __('Email') . '</th>';
break;
case "url" :
   echo '<th scope="col">' . __('URL') . '</th>';
break;
case "registered" :
   echo '<th scope="col">' . __('Registered') . '</th>';
break;
case "status" :
   echo '<th scope="col">' . __('Status') . '</th>';
break;
case "role" :   
    echo '<th scope="col">' . __('Role') . '</th>';
break;   
case "posts" :   
   echo '<th scope="col">' .  __('Posts') . '</th>';
break;
case "comments" :
   echo '<th scope="col" style="letter-spacing:-1px">' . __('Comments') . '</th>';
break;
case "last" :   
    echo '<th scope="col">' . __('Last Post') . '</th>';
break;   
case 'actions' :   
    echo '<th scope="col">' .  __('Actions') . '</th>';
break;
endswitch;
endforeach;
echo '</tr></thead>';

	foreach ($users as $id=>$data) {
	
	$user=bb_get_user($id);	
		
	/*
	$bb_post_cache[$bb_post->post_id]=$bb_post;		// yes this is naughty but lazy workaround for using internal functions without extra mysql queries

	switch ( $bb_post->user_status ) :
	case 0 : $del_class=''; break;
	case 1 : $del_class= 'deleted'; break;
	case 2 : $del_class= 'spam'; break;
	default: $del_class=apply_filters( 'post_del_class', $bb_post->user_status, $bb_post->post_id );
	endswitch;
	*/
	$del_class='';
?>
<tr id="user-<?php echo $id; ?>" <?php alt_class('user', $del_class); ?>>   
<?php  
foreach ($mass_delete_users_columns as $position) :	
echo "<td>"; 
switch ($position) :
case "checkbox" : 
	if ( bb_current_user_can('edit_users') ) {echo "<input type='checkbox' name='mass_delete_users_delete_users[]' value='$id' />";}
  	break;    
case "id" :         echo $id;   break;   
case "user" :   echo '<a href="' . attribute_escape( get_profile_tab_link( $id,'edit') ) . '">' . mdu_overflow($user->user_login,20) . '</a>';
// if ($user->display_name && $user->user_login!=$user->display_name) {echo " [".mdu_overflow($user->display_name,25)."] ";}
break;   
case "email" :  echo mdu_overflow($user->user_email,25);   break;   
case "url" :       echo $user->user_url;   break;   
case "registered" : 
echo "<span title='".bb_since($user->user_registered,true)." ago'>".date( 'Y-m-d', bb_offset_time( bb_gmtstrtotime( $user->user_registered ) ) )."</span>";   
break;   
case "status" : echo $user->user_status;   break;   
case "role" :   
	$capabilities=(isset($user->bb_capabilities)) ? $user->bb_capabilities : $user->capabilities;  // makes compatibile for 0.8.x - 1.0a	
	if (is_array($capabilities)) {echo reset(array_keys($capabilities));} else {echo "(none)";}
break;
case "posts" :   echo empty($data->posts) ? 0 : $data->posts; break;
case "comments" :  echo empty($data->comments) ? 0 : $data->comments; break;
case "last" :    
if (!empty($data->last)) {
echo "<span title='".bb_since($data->last,true)." ago'>".date( 'Y-m-d', bb_offset_time( bb_gmtstrtotime( $data->last ) ) )."</span>";   
} else {echo "(none)";}
break;   
case 'actions' :   echo "?"; break;
endswitch;
 
  echo "</td>";
  endforeach;
  echo '</tr>';	
	} // end foreach
	unset($bb_posts);
	?></table>

<?php if ($total && count($users)>5) {echo $pagelinks;} ?>

<p class="submit" align="right">
<input type="submit" class="deleted" name="mass_delete_users_delete_button" value="<?php _e('Delete Checked Users &raquo;') ?>" onclick="var numchecked = getNumChecked(document.getElementById('deleteusers')); if(numchecked < 1) { alert('<?php _e("Please select some users to delete"); ?>'); return false } return confirm('<?php printf(__("You are about to delete %s users  \\n  \'Cancel\' to stop, \'OK\' to delete."), "' + numchecked + '"); ?>')" />
<?php /*
<input type="submit" class="normal" name="mass_delete_users_undelete_button" value="<?php _e('Undelete Checked Users &raquo;') ?>" onclick="var numchecked = getNumChecked(document.getElementById('deleteusers')); if(numchecked < 1) { alert('<?php _e("Please select some users to undelete"); ?>'); return false } return confirm('<?php printf(__("You are about to undelete %s users  \\n  \'Cancel\' to stop, \'OK\' to undelete."), "' + numchecked + '"); ?>')" />
*/  ?>
</form>

<div id="ajax-response"></div>
<?php
	} else {
?>
<p style="clear:both;">
<?php if ($exact_match)  {
echo " <strong>".__('No results found for exact match.')." ";
echo ' <a href="'.attribute_escape( remove_query_arg( 'exact_match' ) ).'">'.__("Try non-exact?").'</a></strong> ';
}
else {echo "<strong>".__('No results found.')."</strong>";} ?>
</p>
<?php
	} // end if ($bb_posts)
?>

</div>

<?php 
}

function mass_delete_users_topic_limit($per_page) {
if (isset($_GET['per_page'])) {$per_page = intval(substr($_GET['per_page'],0,3));} else {$per_page="20";} 
return $per_page;
}

function mass_delete_users_list_posts() {
	global $bb_posts, $bb_post;	
	if ( $bb_posts ) : foreach ( $bb_posts as $bb_post ) : ?>
	<li<?php alt_class('post'); ?>>
		<div class="threadauthor">
			<p><strong><?php poster_id_link(); ?></strong><br />
				<small><?php poster_id_type(); ?></small></p>
		</div>
		<div class="threadpost">
			<div class="post"><?php post_text(); ?></div>
			<div class="poststuff">
				<?php printf(__('Posted: %1$s in <a href="%2$s">%3$s</a>'), bb_get_post_time(), get_topic_link( $bb_post->topic_id ), get_topic_title( $bb_post->topic_id ));?> IP: <?php post_ip_link(); ?> <?php post_edit_link(); ?> <?php post_delete_link();?></div>
			</div>
	</li><?php endforeach; endif;
}

function mass_delete_users_add_css() { 
if (!(isset($_GET['plugin']) && $_GET['plugin']=="mass_delete_users")) {return;}

$mass_delete_users_options = bb_get_option('mass_delete_users_options');	
if (!isset($mass_delete_users_options['mass_delete_users_css'])) {
	$mass_delete_users_options['mass_delete_users_css']= 'fieldset {display:inline;}
	.submit input {cursor:pointer;cursor:hand;} 
	table td a {line-height:160%;border:0;text-decoration:none;}
	table.widefat tbody tr:hover { background: #ccddcc; }
	.timetitle {border-bottom: 1px dashed #aaa; cursor: help;}
	.metext,.metext:hover,.metext:visited {line-height:120%;text-decoration:none;border:0;color:black;overflow:hidden;display:block;}
	.bozo { background: #eeee88; }
	.alt.bozo { background: #ffff99; }
	.deleted,INPUT.deleted { background: #ee8888; }
	.alt.deleted { background: #ff9999; }
	.spam,INPUT.spam {background:#FFF380;}
	.alt.spam {background:#FAF8CC;}
	.normal,INPUT.normal {background:#D4E8D4;}';	
bb_update_option('mass_delete_users_options',$mass_delete_users_options);
}
echo '<style type="text/css">'.$mass_delete_users_options['mass_delete_users_css'].'</style>';

?><script type="text/javascript">
<!--
function checkAll(source,form)
{
	for (i = 3, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(source.checked ==false)
				form.elements[i].checked = false;
			else
				form.elements[i].checked = true;
		}
	}
}

function getNumChecked(form)
{
	var num = 0;
	for (i = 3, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].checked == true)
				num++;
		}
	}
	return num;
}
//-->
</script>
<?php
} 

function mdu_overflow($text,$limit=20) {
if (strlen($text)>$limit) {$text=substr($text,0,$limit)."&hellip;";}
return $text;
}

function mdu_install() {
global $bbdb; 
$bbdb->query("CREATE TABLE IF NOT EXISTS `bb_users_deleted` 
		   CHARSET utf8  COLLATE utf8_general_ci
		   SELECT * FROM $bbdb->users WHERE 1=0");
$bbdb->query("CREATE INDEX IF NOT EXISTS `ID` ON `bb_users_deleted` (`ID`)");
$bbdb->query("CREATE TABLE IF NOT EXISTS `bb_usermeta_deleted` (
		`user_id` bigint(20)  UNSIGNED NOT NULL default '0',
		`meta_key` varchar(255) default NULL,		
		`meta_value` longtext default NULL,				
		INDEX (`user_id`),		
		) CHARSET utf8  COLLATE utf8_general_ci");			   
}

/*
// This will show all users older than at least a month who have never posted:
SELECT ID,user_login,user_registered FROM wp_users
LEFT JOIN bb_posts ON ID=poster_id
WHERE user_registered<DATE_SUB(CURDATE(),INTERVAL 30 DAY)
AND poster_id is NULL
// This will show all "orphaned" usermeta:
SELECT user_id,meta_key, umeta_id FROM wp_usermeta LEFT JOIN wp_users ON user_id=ID WHERE ID is NULL
*/
?>