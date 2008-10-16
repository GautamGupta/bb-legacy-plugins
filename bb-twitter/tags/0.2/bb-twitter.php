<?php
/*
Plugin Name: bbPress Tweets
Plugin URI:  http://shuttlex.blogdns.net
Description:  Show users latest twitter on profile page
Version: 0.2
Author: RuneG
Author URI: http://shuttlex.blogdns.net

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://www.amazon.com/gp/registry/wishlist/1K51U8VX047NY/ref=wl_web

Instructions:   install, activate, tinker with settings in admin menu


Version History:
0.1 	: First public release
0.2		: Fixed problem avatars did not show. This happend if bb-avatars where not in use.
*/

function fetch_user_twitter($user_id) {
	$user = bb_get_user( $user_id );  
	$twitter=$user->twitter;
	if ($twitter) {return $twitter;}  else  {return "";}
	
	
}

function add_twitter_to_profile_edit() {
global $user_id, $bb_current_user,$bb_twitter;		
if (bb_current_user_can( 'edit_profile', $user->ID )  &&  bb_is_user_logged_in() ) :
	$twitter = fetch_user_twitter($user_id);
	$user = bb_get_user( $user_id );
	$tweets_on = $user->twitter_on;
	
?><fieldset>
<legend><?php  _e('Twitter')?></legend>
<table border=0>
<tr>
<td>Twitter username : </td><td><input type="text" name="twitter" value="<?php echo $twitter;?> " size="25"/></td>
</tr>
<tr>
<td>Show your latest <em>tweet</em> in your profile?</td><td>
<?php
if ($tweets_on == "yes"){
?>
<input name="show_tweets" value="tweets_on" type="checkbox" checked="checked"/></td>
<?php } else { ?>
<input name="show_tweets" value="tweets_on" type="checkbox"/></td>
<?php } ?>
</table>
</fieldset>
<?php 
	endif;
}
add_action('extra_profile_info', 'add_twitter_to_profile_edit');

function update_user_twitter() {
	global $user_id, $bb_twitter;
	$twitter = $_POST['twitter'];
	if ($_POST['show_tweets']){
	$tweets_on = "yes";
	} else {
	$tweets_on = "no";
	}
	bb_update_usermeta($user_id, "twitter",$twitter);
	bb_update_usermeta($user_id, "twitter_on",$tweets_on);
	
	
	
}
add_action('profile_edited', 'update_user_twitter');


function bb_show_tweets() {
global $user_id, $bb_twitter,$bb_current_user;

$userid = bb_get_user_id( $user_id ) ;
$twitter = bb_get_usermeta($user_id,twitter);
$tweets_on = bb_get_usermeta($user_id,twitter_on);
if ( !is_bb_profile() ){
 echo "";
 } else {
 if ($tweets_on == "yes"){
_e('<h4>Siste Tweet : </h4><code>
	<ul id="twitter_update_list"></ul>
	<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script>
	<script type="text/javascript" src="http://twitter.com/statuses/user_timeline/'.$twitter.'.json?callback=twitterCallback2&count=1"></script></code><br/>');
} else {
 end;
 }}
 
}

function tweet_start(){
global $user_id, $bb_twitter,$bb_current_user;
if ( ! bb_get_option('avatars_show') )
		return false;
$author_id = get_post_author_id( $post_id );
$userid = bb_get_user_id( $user_id ) ;
$twitter = bb_get_usermeta($user_id,twitter);
$tweets_on = bb_get_usermeta($user_id,twitter_on);
if ( !is_bb_profile() ){
 $tweet_print = "";
 } else {
 if ($tweets_on == "yes"){
$tweet_print = ('<h4>Siste Tweet : </h4><code>
	<ul id="twitter_update_list"></ul>
	<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script>
	<script type="text/javascript" src="http://twitter.com/statuses/user_timeline/'.$twitter.'.json?callback=twitterCallback2&count=1"></script></code><br/>');
} else {
 end;
 }}
 $email = bb_get_user_email($author_id);
	if ($email == '' || $email == ' '){
		$author_id = bb_get_user_id( $post_id );
		$email = bb_get_user_email($author_id);
		}
	if ($avatars['default'] == 'Your Own Gravatar'){
		$default = $avatars['standard'];
	} else {
		$default = $avatars['default'];
	}
	$size = $avatars['size'];
		$src = 'http://www.gravatar.com/avatar/';
		$src .= md5( strtolower( $email ) );
		$src .= '?s=' . $size;
		$src .= '&amp;d=' . urlencode( $default );

		$rating = $avatars['rating'];
		if ( !empty( $rating ) )
			$src .= '&amp;r=' . $rating;

		$class = 'avatar avatar-' . $size;
	
		$class = 'avatar avatar-' . $size . ' avatar-default';
	

	$avatar = '<img alt="" src="' . $src . '" class="' . $class . '" style="height:' . $size . 'px; width:' . $size . 'px;" />';
	
	
	
	
	if ( $link = get_user_link( $author_id ) ) {
		if (!function_exists('bb_avatars_show')) {
		echo '<a href="' . attribute_escape( $link ) . '">' . $avatar . '</a>';
		}
		echo $tweet_print;
	} else {
	if (!function_exists('bb_avatars_show')) {
		echo $avatar;
	}
		echo $tweet_print;
		
	
}
 
}	
	




if (!function_exists('bb_avatars_show')) {
add_action( 'bb_get_avatar','tweet_start',10);
}else{
add_action( 'bb_get_avatar','bb_show_tweets',1);
}


?>