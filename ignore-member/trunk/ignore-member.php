<?php
/*
Plugin Name: Ignore Member
Description:  Allows members to not see posts by other members that they don't get along with. They cannot block moderators or administrators.
Plugin URI:  http://bbpress.org/plugins/topic/68
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.06

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://amazon.com/paypage/P2FBORKDEFQIVM

Instructions:  install, activate 
put  <?php ignore_member_link(); ?> in your post.php template where you want the "Ignore" link to be seen
optionally put in your theme stylesheet:    a.ignore_member {color:blue;}  
add any mods you wish to make unignorable to the array below, ie. 	array("1","27","55");

TECHICAL NOTE: if you are using rewrite=slugs and mod_rewrite (not multiviews) 
there is a possibility for a user to ignore an entire topic that ends with a member they just ignored.
The one time that happens, bbpress redirects them to /topic/ (ending in blank) which the auto-generated rewrite rules don't support.
So the member will then get  get some weird result. To fix this, add something like
RewriteRule ^topic/$ /forums/ [L,QSA]
Where "forums" is the name of your bbpress root directory.
Again, the multiviews and non-slugs setups should (in theory) not be affected.

history:
2007-08-12	0.01	Ignore Member plugin is born
2007-08-13	0.05	first public beta release
2007-08-20	0.06	admin can now see users blocked in other member's profile, and remove block if desired
*/

$ignore_member['unignorable']=array("1");     // eventually automatic in admin panel:  get_ids_by_role( array('keymaster',administrator','moderator'));
$ignore_member['link_text']=__("Ignore");
$ignore_member['profile_text']=__('Ignored Members');
$ignore_member['remove_text']=__("Remove");

/*	stop editing		*/

function ignore_member_link($post_id=0) {
global $ignore_member;
if (bb_current_user_can('participate') ) :
	$user_id=bb_get_current_user_info( 'id' );
	$post_author_id=get_post_author_id($post_id);
	if (!in_array($post_author_id,$ignore_member['unignorable']) &&  $post_author_id != $user_id) {
		echo '<a class=ignore_member title="block ALL posts by this member" href="'.add_query_arg( 'ignore_member', $post_author_id).'">'.$ignore_member['link_text'].'</a>';
 	}
endif;
}

function ignore_member_update($add_id=0,$remove_id=0) {
global $bb_current_user,$user_id,$ignore_member;
if ($user_id && bb_current_user_can('administrate') ) {$user = bb_get_user( $user_id ); $data_ignore_member=$user->ignore_member;} 
else {$data_ignore_member=$bb_current_user->data->ignore_member;}
if ($data_ignore_member || $add_id || $remove_id) {	// these next two lines are a mess, must cleanup soon
	if ($data_ignore_member && strlen($data_ignore_member)) {$member_list=explode(",",$data_ignore_member);}
	if (!is_array($member_list)) {if (intval($member_list)) {$member_list=array($member_list);} else {$member_list=array();}}
	if ($add_id && !in_array($add_id,$member_list)) {$member_list[]=$add_id;}
	if ($remove_id && in_array($remove_id,$member_list)) {unset($member_list[array_search($remove_id,$member_list)]);}
	$member_list=implode(",",array_diff($member_list,$ignore_member['unignorable']));
	if ($member_list!=$data_ignore_member) {
		if ($user_id && bb_current_user_can('administrate'))  {$temp_id=$user_id;}
		else {$temp_id=$bb_current_user->data->ID; $bb_current_user->data->ignore_member=$member_list; } 
		bb_update_usermeta($temp_id, "ignore_member",$member_list);
	}
}
}
add_filter('bb_init','ignore_member_update');

function ignore_member_add_member() { 
global $bb_current_user,$ignore_member;
	if (isset($_GET['ignore_member'])) {	// process member to ignore
		if (bb_current_user_can('participate') ) :
			$ignore_id=intval($_GET['ignore_member']);
			if ($ignore_id) {ignore_member_update($ignore_id);}			 
		endif;				
	}
	if (isset($_GET['remove_ignore'])) {	// process member to remove ignore
		if (bb_current_user_can('participate') ) :
			$ignore_id=intval($_GET['remove_ignore']);
			if ($ignore_id) {bb_repermalink(); ignore_member_update(0,$ignore_id);}			 
		endif;				
	}
} 
add_action('bb_init', 'ignore_member_add_member');  

function ignore_member_post_filter($where){
global $bb_current_user;
if ($bb_current_user->data->ignore_member) {$where .= " AND poster_id NOT IN (".$bb_current_user->data->ignore_member.") "; }
return $where;
}
function ignore_member_topic_filter($where){
global $bb_current_user;
if ($bb_current_user->data->ignore_member) {$where .= " AND topic_last_poster NOT IN (".$bb_current_user->data->ignore_member.") "; }
return $where;
}
add_filter( 'get_topic_where', 'ignore_member_topic_filter');
add_filter( 'get_thread_where', 'ignore_member_post_filter');
add_filter( 'get_thread_post_ids_where','ignore_member_post_filter');
add_filter( 'get_latest_posts_where', 'ignore_member_post_filter');
add_filter( 'get_recent_user_replies_where', 'ignore_member_post_filter');
add_filter( 'get_latest_topics_where', 'ignore_member_topic_filter');
add_filter( 'get_recent_user_threads_where', 'ignore_member_topic_filter');
add_filter( 'get_recent_user_threads_where', 'ignore_member_topic_filter');
add_filter( 'get_latest_forum_posts_where', 'ignore_member_post_filter');
add_filter( 'get_recent_user_replies_where', 'ignore_member_post_filter');
// add_filter( 'get_sticky_topics_where', 'ignore_member_topic_filter');
// add_filter( 'bb_is_first_where', 'ignore_member_post_filter');

function ignore_member_profile_edit() {
global $bbdb,$user_id,$ignore_member;
$user = bb_get_user( $user_id );
if (bb_is_user_logged_in() && $user->ignore_member) :
echo '<fieldset><legend>'.$ignore_member['profile_text'].'</legend>';
foreach ( (array) $bbdb->get_results("SELECT  ID, user_login FROM $bbdb->users WHERE ID IN (".$user->ignore_member.")") as $ignored ) {
	echo '<span style="margin-left:15px;white-space:nowrap;">[<a title="'.$ignore_member['remove_text'].'" href="'.add_query_arg( 'remove_ignore', $ignored->ID).'"><b>x</b></a>] '.$ignored->user_login.' </span>';
}
echo '</fieldset>';
endif;
}
add_action('extra_profile_info', 'ignore_member_profile_edit',100);

?>