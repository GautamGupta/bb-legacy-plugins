<?php
/*
Plugin Name: AdSense for bbPress
Plugin URI: http://www.seanbluestone.com/adsense-for-bbpress-plugin
Description: Inserts your AdSense in your forum as posts, vastly increasing your CTR
Author: Seans0n
Version: 1.0.1
Author URI: http://www.seanbluestone.com
*/

add_action('bb_admin_menu_generator', 'bbad_configuration_page_add');
add_action('bb_admin-header.php', 'bbad_configuration_page_process');

$bbAds=array();
global $bbAds,$bb_alt;

function bbad_install(){
	$bbadOptions['author_name']='Ad Bot';
	$bbadOptions['author_avatar']=dirname(__FILE__).'/adbot.jpg';
	$bbadOptions['ad_0']='<script type="text/javascript"><!--
google_ad_client = "pub-9789978739555670";
/* 468x60, created 1/1/09 */
google_ad_slot = "9633437142";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>';
	$bbadOptions['opt_0']='R';

	bb_update_option('bbad_options',$bbadOptions);
}

function bbad_ad_block($text){
	global $bbAds,$bbadOptions,$bbad_counter,$bb_alt;

	$bbad_counter++;
	$bbadOptions=bb_get_option('bbad_options');

	if($bbad_counter<2){
		for($x=0;$x<10;$x++){
			if('' != $bbadOptions['ad_'.$x] && '' != $bbadOptions['opt_'.$x]){
				$temp=$bbadOptions['opt_'.$x];
				if($temp=='r' || $temp=='R'){ // Random post, chose a post number that exists
					$GFP=get_forum_posts();
					$PPP=bb_get_option('page_topics'); // PPP = Posts displayed Per Page
					$LastPage=ceil($GFP/$PPP);

					if($PPP > $GFP){ // Single page, random between 0 and Total Posts in Topic (GFP)
						$temp=rand(0,$GFP);
					}else{
						if($_GET['page']==$LastPage){ // Random between 0 and ( Total Posts - Posts Per Page )
							$temp=rand(0,($GFP-$PPP));
						}else{ // Random between 0 and full page of posts
							$temp=rand(0,$PPP);
						}
					}
				}
				$bbAds[$temp]=$bbadOptions['ad_'.$x];
			}
		}
	}

	if(array_key_exists($bbad_counter,$bbAds)){
		echo '<div class="threadauthor">
			'.($bbadOptions['author_avatar'] ? '<img width="48" height="48" src="'.stripslashes($bbadOptions['author_avatar']).'">' : '').'
			<p>
				<strong>'.$bbadOptions['author_name'].'</strong><br />
				<small><?php post_author_title(); ?></small>
			</p>
		</div>
		<div class="threadpost">
			<div class="post">'.stripslashes($bbAds[$bbad_counter]).'</div>
			<div class="poststuff">'.sprintf( 'Posted %s ago', bb_get_post_time() ).'</div>
		</div>
	</li>
	<li'.(is_int($bb_alt['post']/2) ? ' class="alt"' : '' ).'>';
		$BBADWAT=TRUE; $bb_alt['post']+=1;
	}
}

/*
function bbad_ad_block($text){
	// No longer used, this function would filter through posts.
	global $bbAds,$bbadOptions,$bbad_counter,$bb_alt;

	$bbad_counter++;
	$bbadOptions=bb_get_option('bbad_options');

	if($bbad_counter<2){
		for($x=0;$x<10;$x++){
			if('' != $bbadOptions['ad_'.$x] && '' != $bbadOptions['opt_'.$x]){
				$temp=$bbadOptions['opt_'.$x];
				if($temp=='r' || $temp=='R'){ // Random post, chose a post number that exists
					$GFP=get_forum_posts();
					$PPP=bb_get_option('page_topics'); // PPP = Posts displayed Per Page
					$LastPage=ceil($GFP/$PPP);

					if($PPP > $GFP){ // Single page, random between 0 and Total Posts in Topic (GFP)
						$temp=rand(0,$GFP);
					}else{
						if($_GET['page']==$LastPage){ // Random between 0 and ( Total Posts - Posts Per Page )
							$temp=rand(0,($GFP-$PPP));
						}else{ // Random between 0 and full page of posts
							$temp=rand(0,$PPP);
						}
					}
				}
				$bbAds[$temp]=$bbadOptions['ad_'.$x];
			}
		}
	}

	if(array_key_exists($bbad_counter,$bbAds)){
		$text.="</div>	</div>	</li>\n\n	<li id=\"post-".get_post_id().'"'.(is_int(get_post_id()/2) ? ' class="alt"' : '').'>'."\n".'	<div class="threadauthor">
			'.($bbadOptions['author_avatar'] ? '<img width="48" height="48" src="'.stripslashes($bbadOptions['author_avatar']).'">' : '').'
			<p>
				<strong>'.$bbadOptions['author_name'].'</strong><br />
			</p>
		</div>
		<div class="threadpost">
			<div class="post">'.stripslashes($bbAds[$bbad_counter]).'</div>
			<div class="poststuff">'.sprintf( 'Posted %s ago', bb_get_post_time() );
		$bb_alt['post']++;
	}
	return $text;
}
*/

function bbad_configuration_page_add() {
	bb_admin_add_submenu('AdSense for bbPress', 'use_keys', 'bbad_configuration_page');
}

function bbad_configuration_page_process() {
	if(isset($_POST['submit'])) {
		$bbad_options['author_name']=$_POST['author_name'];
		$bbad_options['author_avatar']=$_POST['author_avatar'];

		for($x=0;$x<10;$x++){
			if (isset($_POST['ad_'.$x])) {$bbad_options['ad_'.$x]=$_POST['ad_'.$x];}
			if (isset($_POST['opt_'.$x])) {$bbad_options['opt_'.$x]=$_POST['opt_'.$x];}
		}

		bb_update_option('bbad_options',$bbad_options);
		bb_admin_notice(__('Settings Saved'));
	}
}

function bbad_configuration_page() {
	if('' == bb_get_option('bbad_options')){
		bbad_install();
	}

	$bbad_options=bb_get_option('bbad_options');
?>

<h2>AdSense for bbPress Configuration</h2>
<form method="post" name="bbad_configuration">
<table class="form-table">
	<tr valign="top"><td width="335"><b>Author Name:</b></td><td><input name="author_name" id="author_name" value="<?php echo stripslashes($bbad_options['author_name']); ?>" /></td></tr>
	<tr valign="top"><td><b>Author Avatar:</b></td><td><input name="author_avatar" id="author_avatar" value="<?php echo stripslashes($bbad_options['author_avatar']); ?>" /></td><td>Enter the URL or path to an image file or leave blank for no avatar.</td></tr>
</table>
<fieldset>
	<?php bb_nonce_field( 'bbad-configuration' ); ?>
	<input type="hidden" name="action" id="action" value="update-bbad-configuration" />
	<div class="spacer">
		<input type="submit" name="submit" id="submit" value="Save Changes" />
	</div>
</fieldset><br /><br />

Enter 'R' in any Post Number box to have that Ad displayed at a randomly chosen post number.<br />
Note that depending on the theme odd numbers typically have a white background, even numbers usually have a grey background. If you chose random placement the background of your ad color may look slightly out of place.<br />
Leave the Post Number box blank to not display, but store the Ad code.<br /><br />

<table class="form-table">
<?php for($x=0;$x<10;$x++){ ?>
	<tr valign="top"><td><b>Display Ad Set <?php echo $x; ?></b></td><td><b>At Post:</b></td></tr>
	<tr valign="top"><td><textarea rows="5" cols="45" name="ad_<?php echo $x; ?>" id="ad_<?php echo $x; ?>" /><?php echo stripslashes($bbad_options['ad_'.$x]); ?></textarea></td>
	<td><input type="text" name="opt_<?php echo $x; ?>" id="opt_<?php echo $x; ?>" value="<?php echo stripslashes($bbad_options['opt_'.$x]); ?>" />
	</td>
	</tr>
<?php } ?>
</table>
	<fieldset>
		<input type="hidden" name="action" id="action" value="update-bbad-configuration" />
		<div class="spacer">
			<input type="submit" name="submit" id="submit" value="Save Changes" />
		</div>
	</fieldset>
</form>
<?php
}

?>