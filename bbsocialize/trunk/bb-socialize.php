<?php
/*
Plugin Name: bbSocialize
Description: Allows you to set and display your social media websites link in your profile.
Plugin URI: http://astateofmind.eu/freebies/bbsocialize
Author: F.Thion
Author URI: http://astateofmind.eu
Version: 0.0.3

license: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
donate: http://astateofmind.eu/about/support/

TO DO:
   - Add support for
   	- Technorati
   	- MyBlogLog
   	- MySpace

History for 0.0.3:
	- If profiles are empty and are not set by user, then they are not displayed;
	- Added support for technorati;
	- Changed images names, from x.gif to bb_x.gif;
	- Minor modifications to look of management page;
	- Added support to display profile links in single posts;
	- Added "Reset Button" - you can now reset all settings to default (minor error, changes won't appear without page refresh)
*/

function bb_socialize_initialize() {
	global $bb, $bb_current_user, $bb_socialize, $bb_signatures_type;
	if ( !isset( $bb_socialize ) ) 
	{
		$bb_socialize = bb_get_option('bb_socialize');
		if ( !$bb_socialize )
		{
			$bb_socialize['minimum_user_level'] = "participate";
			$bb_socialize['images_url'] = bb_get_option('uri');
			$bb_socialize['links_rel'] = "nofollow";
			$bb_socialize['twitter'] = "true";
			$bb_socialize['pownce'] = "true";
			$bb_socialize['digg'] = "true";
			$bb_socialize['delicious'] = "true";
			$bb_socialize['flickr'] = "true";
			$bb_socialize['technorati'] = "true";
			/* $bb_socialize['mybloglog'] = "true"; */
			/* $bb_socialize['facebook'] = "true"; */
		}
	}
}	
add_action( 'bb_init', 'bb_socialize_initialize');

// Let's get some profiles to display
function get_twitter($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_twitter = $user->social_twitter;
	if ( $bb_twitter ) { return $bb_twitter; }  else  { return ""; }
}

function get_flickr($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_flickr = $user->social_flickr;
	if ( $bb_flickr ) { return $bb_flickr; }  else  { return ""; }
}

function get_digg($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_digg = $user->social_digg;
	if ( $bb_digg ) { return $bb_digg; }  else  { return ""; }
}

function get_delicious($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_delicious = $user->social_delicious;
	if ( $bb_delicious ) { return $bb_delicious; }  else  { return ""; }
}

function get_pownce($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_pownce = $user->social_pownce;
	if ( $bb_pownce ) { return $bb_pownce; }  else  { return ""; }
}

function get_technorati($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_technorati = $user->social_technorati;
	if ( $bb_technorati ) { return $bb_technorati; }  else  { return ""; }
}

/* function get_mybloglog($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_mybloglog = $user->social_mybloglog;
	if ( $bb_mybloglog ) { return $bb_mybloglog; }  else  { return ""; }
} */

/* function get_facebook($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_facebook = $user->social_facebook;
	if ( $bb_facebook ) { return $bb_facebook; }  else  { return ""; }
} */

/* function get_myspace($user_id) {
	$user = bb_get_user( $user_id );  
	$bb_myspace = $user->social_myspace;
	if ( $bb_myspace ) { return $bb_myspace; }  else  { return ""; }
} */

// Now, let's add input fields to our profile-edit page
function add_socialize_to_profile_edit() {
	global $user_id, $bb_current_user, $bb_socialize;	
	
if (bb_current_user_can($bb_socialize['minimum_user_level'])  &&  bb_is_user_logged_in() ) :
$bb_twitter = get_twitter($user_id);
$bb_flickr = get_flickr($user_id);
$bb_digg = get_digg($user_id);
$bb_delicious = get_delicious($user_id);
$bb_pownce = get_pownce($user_id);
$bb_technorati = get_technorati($user_id);
/* $bb_mybloglog = get_mybloglog($user_id); */
/* $bb_facebook = get_facebook($user_id); */

echo '<div class="socialize_edit">
<fieldset>
<legend>'. __('Social Media Profiles') .'</legend>

<p>Set your Social Media Profiles below.</p>

<table>';
if ( $bb_socialize['twitter'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Twitter</th>
	<td>
		<input type="text" name="bb_twitter" value="'. $bb_twitter .'" />
	</td>
</tr>';
}

if ( $bb_socialize['flickr'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Flickr</th>
	<td>
		<input type="text" name="bb_flickr" value="'. $bb_flickr .'" />
	</td>
</tr>';
}

if ( $bb_socialize['digg'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Digg</th>
	<td>
		<input type="text" name="bb_digg" value="'. $bb_digg .'" />
	</td>
</tr>';
}

if ( $bb_socialize['pownce'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Pownce</th>
	<td>
		<input type="text" name="bb_pownce" value="'. $bb_pownce .'" />
	</td>
</tr>';
}

if ( $bb_socialize['delicious'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Delicious</th>
	<td>
		<input type="text" name="bb_delicious" value="'. $bb_delicious .'" />
	</td>
</tr>';
}

if ( $bb_socialize['technorati'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Technorati</th>
	<td>
		<input type="text" name="bb_technorati" value="'. $bb_technorati .'" />
	</td>
</tr>';
}

/* if ( $bb_socialize['mybloglog'] == true ) {
echo '<tr class="form-field">
	<th scope="row">MyBlogLog</th>
	<td>
		<input type="text" name="bb_mybloglog" value="'. $bb_mybloglog .'" />
	</td>
</tr>';
} */

/* if ( $bb_socialize['facebook'] == true ) {
echo '<tr class="form-field">
	<th scope="row">Facebook</th>
	<td>
		<input type="text" name="bb_facebook" value="'. $bb_facebook .'" />
	</td>
</tr>';
} */

/* if ( $bb_socialize['myspace'] == true ) {
echo '<tr class="form-field">
	<th scope="row">MySpace</th>
	<td>
		<input type="text" name="bb_myspace" value="'. $bb_myspace .'" />
	</td>
</tr>';
} */

echo '</table></fieldset>

</div>';
	endif;
}
add_action('extra_profile_info', 'add_socialize_to_profile_edit');

// The following function can be used to display profiles on user-profile-page
function get_socialize() {
	global $user_id, $bb_current_user, $bb_socialize;

	$user_id = bb_get_user( $user_id );
	
	$bb_twitter = $user_id->social_twitter;
	$bb_pownce = $user_id->social_pownce;
	$bb_digg = $user_id->social_digg;
	$bb_delicious = $user_id->social_delicious;
	$bb_flickr = $user_id->social_flickr;
	$bb_technorati = $user_id->social_technorati;
	/* $bb_mybloglog = $user_id->social_mybloglog; */
	/* $bb_facebook = $user_id->social_facebook; */
	/* $bb_myspace = $user_id->social_myspace; */
		echo '<div class="socialize_wrap">';
	if ( $bb_socialize['twitter'] == true ) { if ( !empty( $bb_twitter ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_twitter.png" width="16" height="16" border="0" /><a href="http://twitter.com/'.$bb_twitter.'" title="User Twitter account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_twitter.'</span></a></dd>'; } }
	if ( $bb_socialize['digg'] == true ) { if ( !empty( $bb_digg ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_digg.png" width="16" height="16" border="0" /><a href="http://digg.com/users/'.$bb_digg.'" title="User Digg account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_digg.'</span></a></dd>'; } }
	if ( $bb_socialize['pownce'] == true ) { if ( !empty( $bb_pownce ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_pownce.png" width="16" height="16" border="0" /><a href="http://pownce.com/'.$bb_pownce.'" title="User Pownce account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_pownce.'</span></a></dd>'; } }
	if ( $bb_socialize['delicious'] == true ) { if ( !empty( $bb_delicious ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_delicious.png" width="16" height="16" border="0" /><a href="http://delicious.com/'.$bb_delicious.'" title="User Delicious account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_delicious.'</span></a></dd>'; } }
	if ( $bb_socialize['flickr'] == true ) { if ( !empty( $bb_flickr ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_flickr.png" width="16" height="16" border="0" /><a href="http://flickr.com/photos/'.$bb_flickr.'" title="User Flickr account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_flickr.'</span></a></dd>'; } }
	if ( $bb_socialize['technorati'] == true ) { if ( !empty( $bb_technorati) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_technorati.png" width="16" height="16" border="0" /><a href="http://technorati.com/people/technorati/'.$bb_technorati.'" title="User Technorati account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_technorati.'</span></a></dd>'; } }
	/* if ( $bb_socialize['mybloglog'] == true ) { if ( !empty( $bb_mybloglog ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_mybloglog.png" width="16" height="16" border="0" /><a href="http://technorati.com/people/technorati/'.$bb_mybloglog.'" title="User MyBlogLog account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_mybloglog.'</span></a></dd>'; } } */
	/* if ( $bb_socialize['facebook'] == true ) { if ( !empty( $bb_facebook ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_facebook.png" width="16" height="16" border="0" /><a href="http://technorati.com/people/technorati/'.$bb_mybloglog.'" title="User Facebook account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_facebook.'</span></a></dd>'; } } */
	/* if ( $bb_socialize['myspace'] == true ) { if ( !empty( $bb_myspace ) ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/bb_myspace.png" width="16" height="16" border="0" /><a href="http://myspace.com/'.$bb_myspace.'" title="User MySpace page" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_myspace.'</span></a></dd>'; } } */
	echo '</div>';
}

// The following function can be used in posts; just put get_socialize_post(get_post_author_id()); somewhere :)
function get_socialize_post( $user_id ) {
	global $bb, $bb_socialize;
	
	$user_id = bb_get_user( $user_id );
	
	$bb_twitter = $user_id->social_twitter;
	$bb_pownce = $user_id->social_pownce;
	$bb_digg = $user_id->social_digg;
	$bb_delicious = $user_id->social_delicious;
	$bb_flickr = $user_id->social_flickr;
	$bb_technorati = $user_id->social_technorati;
	/* $bb_mybloglog = $user_id->social_mybloglog; */
	/* $bb_facebook = $user_id->social_facebook; */
	/* $bb_myspace = $user_id->social_myspace; */
	
	echo '<div class="post_socialize_wrap">';
	if ( $bb_socialize['twitter'] == true ) { if ( !empty( $bb_twitter ) ) { echo '<span><a href="http://twitter.com/'.$bb_twitter.'" title="User Twitter account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_twitter.png" width="16" height="16" border="0" /></a></span>'; } }
	if ( $bb_socialize['digg'] == true ) { if ( !empty( $bb_digg ) ) { echo '<span><a href="http://digg.com/users/'.$bb_digg.'" title="User Digg account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_digg.png" width="16" height="16" border="0" /></a></span>'; } }
	if ( $bb_socialize['pownce'] == true ) { if ( !empty( $bb_pownce ) ) { echo '<span><a href="http://pownce.com/'.$bb_pownce.'" title="User Pownce account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_pownce.png" width="16" height="16" border="0" /></a></span>'; } }
	if ( $bb_socialize['delicious'] == true ) { if ( !empty( $bb_delicious ) ) { echo '<span><a href="http://delicious.com/'.$bb_delicious.'" title="User Delicious account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_delicious.png" width="16" height="16" border="0" /></a></span>'; } }
	if ( $bb_socialize['flickr'] == true ) { if ( !empty( $bb_flickr ) ) { echo '<span><a href="http://flickr.com/photos/'.$bb_flickr.'" title="User Flickr account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_flickr.png" width="16" height="16" border="0" /></a></span>'; } }
	if ( $bb_socialize['technorati'] == true ) { if ( !empty( $bb_technorati) ) { echo '<span><a href="http://technorati.com/people/technorati/'.$bb_technorati.'" title="User Technorati account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_technorati.png" width="16" height="16" border="0" /></a></span>'; } }
	/* if ( $bb_socialize['mybloglog'] == true ) { if ( !empty( $bb_mybloglog ) ) { echo '<span><a href="http://technorati.com/people/technorati/'.$bb_mybloglog.'" title="User MyBlogLog account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_mybloglog.png" width="16" height="16" border="0" /></a></span>'; } } */
	/* if ( $bb_socialize['facebook'] == true ) { if ( !empty( $bb_facebook ) ) { echo '<span><a href="http://technorati.com/people/technorati/'.$bb_mybloglog.'" title="User Facebook account" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_facebook.png" width="16" height="16" border="0" /></a></span>'; } } */
	/* if ( $bb_socialize['myspace'] == true ) { if ( !empty( $bb_myspace ) ) { echo '<span><a href="http://myspace.com/'.$bb_myspace.'" title="User MySpace page" rel="'.$bb_socialize['links_rel'].'"><img src="'.$bb_socialize['images_url'].'/bb_myspace.png" width="16" height="16" border="0" /></a></span>'; } } */
	echo '</div>';
}

function update_user_socialize() {
	global $user_id, $bb_socialize;
	
	$bb_twitter = $_POST['bb_twitter'];
	$bb_flickr = $_POST['bb_flickr'];
	$bb_digg = $_POST['bb_digg'];
	$bb_delicious = $_POST['bb_delicious'];
	$bb_pownce = $_POST['bb_pownce'];
	$bb_technorati = $_POST['bb_technorati'];
	/* $bb_mybloglog = $_POST['bb_mybloglog']; */
	/* $bb_facebook = $_POST['bb_facebook']; */ 
	/* $bb_myspace = $_POST['bb_myspace']; */
	
	if ( $bb_twitter ) 
	{
		$bb_twitter = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_twitter) ), true) ) );
		bb_update_usermeta($user_id, "social_twitter",$bb_twitter);
	}
	else {bb_delete_usermeta($user_id, "social_twitter");}
	
	if ( $bb_flickr ) 
	{
		$bb_flickr = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_flickr) ), true) ) );
		bb_update_usermeta($user_id, "social_flickr",$bb_flickr);
	}
	else {bb_delete_usermeta($user_id, "social_flickr");}
	
	if ( $bb_digg ) 
	{
		$bb_digg = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_digg) ), true) ) );
		bb_update_usermeta($user_id, "social_digg",$bb_digg);
	}
	else {bb_delete_usermeta($user_id, "social_digg");}
	
	if ( $bb_delicious ) 
	{
		$bb_delicious = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_delicious) ), true) ) );
		bb_update_usermeta($user_id, "social_delicious",$bb_delicious);
	}
	else {bb_delete_usermeta($user_id, "social_delicious");}
	
	if ( $bb_pownce ) 
	{
		$bb_pownce = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_pownce) ), true) ) );
		bb_update_usermeta($user_id, "social_pownce",$bb_pownce);
	}
	else {bb_delete_usermeta($user_id, "social_pownce");}
	
	if ( $bb_technorati ) 
	{
		$bb_technorati = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_technorati) ), true) ) );
		bb_update_usermeta($user_id, "social_technorati",$bb_technorati);
	}
	else {bb_delete_usermeta($user_id, "social_technorati");}
	
	/* if ( $bb_mybloglog ) 
	{
		$bb_mybloglog = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_mybloglog) ), true) ) );
		bb_update_usermeta($user_id, "social_mybloglog",$bb_mybloglog);
	}
	else {bb_delete_usermeta($user_id, "social_mybloglog");} */

	/* if ( $bb_facebook ) 
	{
		$bb_facebook = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_facebook) ), true) ) );
		bb_update_usermeta($user_id, "social_facebook",$bb_facebook);
	}
	else {bb_delete_usermeta($user_id, "social_facebook");} */
	
	/* if ( $bb_myspace ) 
	{
		$bb_myspace = bb_filter_kses( stripslashes( balanceTags( bb_code_trick( bb_encode_bad($bb_myspace) ), true) ) );
		bb_update_usermeta($user_id, "social_myspace",$bb_myspace);
	}
	else {bb_delete_usermeta($user_id, "social_myspace");} */
}
add_action('profile_edited', 'update_user_socialize');

function bb_socialize_admin_page() {
	global $bb_socialize;
	?>
	
	<h2>bbPress Social Profiles Management</h2>
	
	<form method="post" name="bb_socialize_form" id="bb_socialize_form">
	<fieldset>
	<input type="hidden" name="bb_socialize" value="1">
	
		<table class="widefat">
			<thead>
				<tr><th width="170">Option</th><th>Setting</th></tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="images_url"><b>URL to images</b></label></td>
					
					<td>
						<input type="text" name="images_url" class="text long" value="<?php echo $bb_socialize['images_url']; ?>"> Please provide full url, but without final slash.
					</td>
				</tr>
				
				<tr>
					<td><label for="links_rel"><b>Links REL</b></label></td>
					
					<td>
						<input type="text" name="links_rel" class="text long" value="<?php echo $bb_socialize['links_rel']; ?>"> e.g. follow
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3>Set social sites</h3>
		
		<p>Use the settings below to decide, which social media websites will be used on your forum. Set "yes" if you want to allow users to set their account for this specific website, or set "no" to hide the specific website. You can change these settings anytime you want.</p>
		
		<table class="widefat">
			<thead>
				<tr><th width="170">Social Media Site</th><th>Display</th></tr>
			</thead>
			<tbody>
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_twitter.png" width="16" height="16" border="0" /> <label for="twitter"><b>Twitter</b></label></td>
					
					<td>
						<input name="twitter" value="1" type="radio" <?php if ( $bb_socialize['twitter'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="twitter" value="0" type="radio" <?php if ( $bb_socialize['twitter'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>
				
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_pownce.png" width="16" height="16" border="0" /> <label for="pownce"><b>Pownce</b></label></td>
					
					<td>
						<input name="pownce" value="1" type="radio" <?php if ( $bb_socialize['pownce'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="pownce" value="0" type="radio" <?php if ( $bb_socialize['pownce'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>
				
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_digg.png" width="16" height="16" border="0" /> <label for="digg"><b>Digg</b></label></td>
					
					<td>
						<input name="digg" value="1" type="radio" <?php if ( $bb_socialize['digg'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="digg" value="0" type="radio" <?php if ( $bb_socialize['digg'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>
				
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_delicious.png" width="16" height="16" border="0" /> <label for="delicious"><b>Delicious</b></label></td>
					
					<td>
						<input name="delicious" value="1" type="radio" <?php if ( $bb_socialize['delicious'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="delicious" value="0" type="radio" <?php if ( $bb_socialize['delicious'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>
				
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_flickr.png" width="16" height="16" border="0" /> <label for="flickr"><b>Flickr</b></label></td>
					
					<td>
						<input name="flickr" value="1" type="radio" <?php if ( $bb_socialize['flickr'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="flickr" value="0" type="radio" <?php if ( $bb_socialize['flickr'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>
				
				<tr>
					<td><img src="<?php echo $bb_socialize['images_url']; ?>/bb_technorati.png" width="16" height="16" border="0" /> <label for="technorati"><b>Technorati</b></label></td>
					
					<td>
						<input name="technorati" value="1" type="radio" <?php if ( $bb_socialize['technorati'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="technorati" value="0" type="radio" <?php if ( $bb_socialize['technorati'] == false ) { echo 'checked="checked"'; } ?>>No
					</td>
				</tr>

			</tbody>
		</table>
		<p class="submit"><input type="submit" name="bb_socialize_submit" value="Save bbSocialize settings"> <input type="submit" name="bb_socialize_reset" value="Reset settings"></p>
	</fieldset>
	</form>

	<hr />
	
	<p>If you like this plugin, please donate few bucks so I could keep developing it. Or at least <a href="http://twitter.com/t_thion/">follow me on Twitter</a> :).
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	
	<input type="hidden" name="cmd" value="_donations">
	<input type="hidden" name="business" value="wojciech.usarzewicz@gmail.com">
	<input type="hidden" name="item_name" value="bbPages Donation">
	<input type="hidden" name="item_number" value="bbPages Donation">
	<input type="hidden" name="no_shipping" value="0">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD" />
	
	Type donation amount: $ <input type="text" name="amount" value="1" />
	
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	
	<input type="submit" name="submit" value="Donate with PayPal!" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
	</form>
	</p>
	
	<?php
}

function bb_socialize_process_post() {
	if ( isset( $_POST['bb_socialize_submit'] ) && isset( $_POST['bb_socialize'] ) ) 
	{
		global $bb_socialize;
		
		foreach( array_keys($bb_socialize) as $key) {
			if ( isset( $_POST[$key] ) ) { $bb_socialize[$key] = $_POST[$key]; }
		}
		
		bb_update_option('bb_socialize',$bb_socialize);
	} elseif ( isset( $_POST['bb_socialize_reset'] ) ) {
		global $bb_socialize;
		
		foreach( array_keys($bb_socialize) as $key) {
			if ( isset( $_POST[$key] ) ) { $bb_socialize[$key] = $_POST[$key]; }
		}
		
		bb_delete_option('bb_socialize');
	}
}
add_action( 'bb_admin-header.php','bb_socialize_process_post');

function bb_socialize_add_admin_page() { 
	bb_admin_add_submenu(__('Social Profiles'), 'administrate', 'bb_socialize_admin_page' ); 
}
add_action( 'bb_admin_menu_generator', 'bb_socialize_add_admin_page' );

?>