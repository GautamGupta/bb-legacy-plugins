<?php
/*
Plugin Name: bbSocialize
Description: Allows you to set and display your social media websites link in your profile.
Plugin URI: http://astateofmind.eu/freebies/bbsocialize
Author: F.Thion
Author URI: http://astateofmind.eu
Version: 0.0.2

license: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

donate: http://astateofmind.eu/about/support/
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
		}
	}
}	
add_action( 'bb_init', 'bb_socialize_initialize');

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

function add_socialize_to_profile_edit() {
	global $user_id, $bb_current_user, $bb_socialize;	
	
if (bb_current_user_can($bb_socialize['minimum_user_level'])  &&  bb_is_user_logged_in() ) :
$bb_twitter = get_twitter($user_id);
$bb_flickr = get_flickr($user_id);
$bb_digg = get_digg($user_id);
$bb_delicious = get_delicious($user_id);
$bb_pownce = get_pownce($user_id);

echo '<div class="socialize_edit">
'. __('Social Media Profiles') .'

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
echo '</table>

</div>';
	endif;
}
add_action('extra_profile_info', 'add_socialize_to_profile_edit');

function get_socialize() {
	global $user_id, $bb_current_user, $bb_socialize;

	$user_id = bb_get_user( $user_id );
	
	$bb_twitter = $user_id->social_twitter;
	$bb_pownce = $user_id->social_pownce;
	$bb_digg = $user_id->social_digg;
	$bb_delicious = $user_id->social_delicious;
	$bb_flickr = $user_id->social_flickr;
		echo '<div class="socialize_wrap">';
	if ( $bb_socialize['twitter'] == true ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/twitter.gif" width="16" height="16" border="0" /><a href="http://twitter.com/'.$bb_twitter.'" title="User Twitter account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_twitter.'</span></a></dd>'; }
	if ( $bb_socialize['digg'] == true ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/digg.gif" width="16" height="16" border="0" /><a href="http://digg.com/users/'.$bb_digg.'" title="User Digg account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_digg.'</span></a></dd>'; }
	if ( $bb_socialize['pownce'] == true ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/pownce.gif" width="16" height="16" border="0" /><a href="http://pownce.com/'.$bb_pownce.'" title="User Pownce account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_pownce.'</span></a></dd>'; }
	if ( $bb_socialize['delicious'] == true ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/delicious.gif" width="16" height="16" border="0" /><a href="http://delicious.com/'.$bb_delicious.'" title="User Delicious account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_delicious.'</span></a></dd>'; }
	if ( $bb_socialize['flickr'] == true ) { echo '<dd><img src="'.$bb_socialize['images_url'].'/flickr.gif" width="16" height="16" border="0" /><a href="http://flickr.com/photos/'.$bb_flickr.'" title="User Flickr account" rel="'.$bb_socialize['links_rel'].'"><span>'.$bb_flickr.'</span></a></dd>'; }
	echo '</div>';
}

function update_user_socialize() {
	global $user_id, $bb_socialize;
	
	$bb_twitter = $_POST['bb_twitter'];
	$bb_flickr = $_POST['bb_flickr'];
	$bb_digg = $_POST['bb_digg'];
	$bb_delicious = $_POST['bb_delicious'];
	$bb_pownce = $_POST['bb_pownce'];
	
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
				
				<tr>
					<td><label for="twitter"><b>Get Twitter</b></label></td>
					
					<td>
						<input name="twitter" value="1" type="radio" <?php if ( $bb_socialize['twitter'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="twitter" value="0" type="radio" <?php if ( $bb_socialize['twitter'] == false ) { echo 'checked="checked"'; } ?>>NO
					</td>
				</tr>
				
				<tr>
					<td><label for="pownce"><b>Get Pownce</b></label></td>
					
					<td>
						<input name="pownce" value="1" type="radio" <?php if ( $bb_socialize['pownce'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="pownce" value="0" type="radio" <?php if ( $bb_socialize['pownce'] == false ) { echo 'checked="checked"'; } ?>>NO
					</td>
				</tr>
				
				<tr>
					<td><label for="digg"><b>Get Digg</b></label></td>
					
					<td>
						<input name="digg" value="1" type="radio" <?php if ( $bb_socialize['digg'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="digg" value="0" type="radio" <?php if ( $bb_socialize['digg'] == false ) { echo 'checked="checked"'; } ?>>NO
					</td>
				</tr>
				
				<tr>
					<td><label for="delicious"><b>Get Delicious</b></label></td>
					
					<td>
						<input name="delicious" value="1" type="radio" <?php if ( $bb_socialize['delicious'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="delicious" value="0" type="radio" <?php if ( $bb_socialize['delicious'] == false ) { echo 'checked="checked"'; } ?>>NO
					</td>
				</tr>
				
				<tr>
					<td><label for="flickr"><b>Get Flickr</b></label></td>
					
					<td>
						<input name="flickr" value="1" type="radio" <?php if ( $bb_socialize['flickr'] == true ) { echo 'checked="checked"'; } ?>> Yes &nbsp; &nbsp;
						<input name="flickr" value="0" type="radio" <?php if ( $bb_socialize['flickr'] == false ) { echo 'checked="checked"'; } ?>>NO
					</td>
				</tr>
				
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" value="Save bbSocialize settings"></p>
	</fieldset>
	</form>

	<h3>Please support the developer</h3>
	
	<img src="http://astateofmind.eu/uploads/donation.gif" style="margin-right:10px" border="0" align="left" />
	
	Do you like this plugin? Do you find it useful? If so, please donate few dollars so I could keep develop this plugin and others further and further. Even the smallest help is greatly appreciated for a student in Poland ;). <br /><br />
	
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
	
	<p>Want to know what I'm developing right now? <a href="http://twitter.com/t_thion/">Follow me on Twitter</a>, ignore 90% of stuff and learn a lot you will ;). And thank you for using my plugin!</p>
	

	<?php
}

function bb_socialize_process_post() {
	if ( isset( $_POST['submit'] ) && isset( $_POST['bb_socialize'] ) ) 
	{
		global $bb_socialize;
		
		foreach( array_keys($bb_socialize) as $key) {
			if ( isset( $_POST[$key] ) ) { $bb_socialize[$key] = $_POST[$key]; }
		}
		
		bb_update_option('bb_socialize',$bb_socialize);
	}
}
add_action( 'bb_admin-header.php','bb_socialize_process_post');

function bb_socialize_add_admin_page() { 
	bb_admin_add_submenu(__('Social Profiles'), 'administrate', 'bb_socialize_admin_page' ); 
}
add_action( 'bb_admin_menu_generator', 'bb_socialize_add_admin_page' );

?>