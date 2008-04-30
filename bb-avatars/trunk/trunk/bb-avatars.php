<?php
/*
Plugin Name: bb-avatars
Plugin URI:  http://bbpress.org/plugins/topic/bb-avatars
Description:  Allows admin to choose default Gravatar image and set other options
Version: 0.1
Author: RuneG
Author URI: http://shuttlex.blogdns.net

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://www.amazon.com/gp/registry/wishlist/1K51U8VX047NY/ref=wl_web

Instructions:   install, activate, tinker with settings in admin menu


Version History:
0.1 	: First public release

*/

function bb_avatars_initialize() {
	global $bb,$bb_current_user,$bb_avatars,$bb_avatars_type;
	if(!isset($bb_avatars)) {$bb_avatars = bb_get_option('bb_avatars');
		if (!$bb_avatars) {
		$bb_avatars['size']='48';     // sanity 
		$bb_avatars['rating']="G";     // sanity 
		$bb_avatars['default']="Your Own Gravatar";   // YourOwnGravatar, wavatar, identicon or monsterid  (watchout for typos)
		$bb_avatars['standard']="http://use.perl.org/images/pix.gif";    // Default default gravatar
		}}
		$bb_avatars_type['size']="numeric";     // sanity 
		$bb_avatars_type['rating']="G,PG,R,X";     // sanity 
		$bb_avatars_type['default']="Your Own Gravatar,wavatar,identicon,monsterid";   // YourOwnGravatar, wavatar, identicon or monsterid  (watchout for typos)
		$bb_avatars_type['standard']="textarea";    
		
}
add_action( 'bb_init', 'bb_avatars_initialize');
	
function bb_avatars_add_admin_page() {bb_admin_add_submenu(__('Avatars'), 'administrate', 'bb_avatars_admin_page');}
add_action( 'bb_admin_menu_generator', 'bb_avatars_add_admin_page' );

function bb_avatars_admin_page() {
	global $bb,$bb_current_user,$bb_avatars,$bb_avatars_type;		
	?>
		<h2>bbPress Avatars</h2>
		<form method="post" name="bb_avatars_form" id="bb_avatars_form">
		<input type=hidden name="bb_avatars" value="1">
			<table class="widefat">
				<thead>
					<tr> <th width="170">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
<?php
					foreach(array_keys( $bb_avatars_type) as $key) {
						?>
						<tr>
							<td><label for="bb_avatars_<?php echo $key; ?>"><b><?php echo ucwords(str_replace("_"," ",$key)); ?></b></label></td>
							<td>
							<?php
							switch ( $bb_avatars_type[$key]) :
							case 'numeric' :
								?><input type=text maxlength=3 name="<?php echo $key;  ?>" value="<?php echo $bb_avatars[$key]; ?>"> <?php 
							break;
							case 'textarea' :								
								?><textarea style="width:98%" name="<?php echo $key;  ?>"><?php echo $bb_avatars[$key]; ?></textarea><?php 							
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$bb_avatars_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($bb_avatars[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type=text style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $bb_avatars[$key]; ?>"> <?php 
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
			<p class="submit"><input type="submit" name="submit" value="Save bbPress Avatars Settings"></p>
		
		</form>
		<?php
}
function bb_avatars_process_post() {
	if(isset($_POST['submit']) && isset($_POST['bb_avatars'])) {
		global $bb_avatars,$bb_avatars_type;
		foreach(array_keys( $bb_avatars) as $key) {
			if (isset($_POST[$key])) {$bb_avatars[$key]=$_POST[$key];}
		}
		bb_update_option('bb_avatars',$bb_avatars);
		
	}
}

function bb_avatars_show() {
	global $bb_avatars, $bb_avatars_type;
	
	if ( ! bb_get_option('avatars_show') )
		return false;
	$avatars = bb_get_option('bb_avatars');
	$author_id = get_post_author_id( $post_id );
	$email = bb_get_user_email($author_id);
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
		echo '<a href="' . attribute_escape( $link ) . '">' . $avatar . '</a>';
		
	} else {
		echo $avatar;
		
	}
}
add_action( 'bb_admin-header.php','bb_avatars_process_post');
add_action( 'bb_get_avatar','bb_avatars_show',1);
?>