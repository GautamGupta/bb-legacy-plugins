<?php
// part of xili-bb-npn plugin - since 0.9.4

require_once('./bb-load.php');

bb_repermalink(); // The magic happens here.
//bb_send_headers();

// Check that the current user can do this, if not kick them to the front page
if ( !bb_current_user_can( 'edit_user', $user_id ) ) {
	$sendto = bb_get_uri( null, null, BB_URI_CONTEXT_HEADER );
	wp_redirect( $sendto );
	exit;
}


bb_get_header();

$topics = get_user_favorites( $user_id, true );
?>
<div class="bbcrumb"><a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <a href="<?php user_profile_link( $user_id ); ?>"><?php echo get_user_display_name( $user_id ); ?></a> &raquo; <?php _e('Email Notification','xnpn'); ?></div>

<h2 id="userlogin" role="main"><?php echo get_user_display_name( $user->ID ); ?> <small>(<?php echo get_user_name( $user->ID ); ?>)</small> <?php _e( 'Email Notifications','xnpn' ); ?></h2>

<?php if ( $topics ) : ?>

<p><?php echo get_user_name( $user_id ); ?> <?php _e('currently has favorites.');  ?> <?php _e('(future version of plugin will allow selection one by one)');  ?> </p>

<?php if ( bb_current_user_can( 'edit_favorites_of', $user_id ) ) : ?>
	<?php 
	$xnpn_users_configuration = (array) bb_get_option('xnpn_users_configuration') ;
	if ( $_POST['Submit'] && $_GET['tab'] == 'xnpn' ) { 
		
		bb_check_admin_referer( 'xnpn-profile_' . $user_id ); // if hack display ??
		$selectall =  ( $_POST['xnpn_check'] ) ? 1 : 0 ;
			
			$xnpn_users_configuration[$user_id] = array($selectall); // first = all (future list topics id)
			
			bb_update_option( 'xnpn_users_configuration', $xnpn_users_configuration );
		
		    ?>
		    <p style="color:green;"><?php _e('Profile updated','xnpn'); ?> </p>
		    <?php
	
	} else {
		if ( $xnpn_users_configuration == array() || !isset($xnpn_users_configuration[$user_id]) ) {
			$selectall = 0;
		} elseif ( isset($xnpn_users_configuration[$user_id]) ) {
			$selectall = $xnpn_users_configuration[$user_id][0];
		}
	}
	?>
	<form method="post" action="<?php profile_tab_link( $user->ID, 'xnpn', BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_USER_FORMS ); ?>">
	<fieldset>
	<legend><?php _e('Post notification','xnpn'); ?></legend>
	<label><?php _e('Do you need to receive email for all ?','xnpn'); ?>&nbsp;&nbsp;<input id="xnpn_check" name="xnpn_check" type="checkbox" value="xnpn" <?php if($selectall == 1) echo 'checked="checked"' ?> /></label>
	</fieldset>
	<p class="submit right">
	<?php bb_nonce_field( 'xnpn-profile_' . $user->ID ); ?>
	  <input type="submit" name="Submit" value="<?php echo esc_attr__( 'Update Profile &raquo;' ); ?>" />
	</p>
	<p><br /><small>© 2010 - dev.xiligroup.com - <?php echo XNPN_VER; ?></small></p>
	</form>	
<?php endif; ?>
</p>

<table id="favorites">
<tr>
	<th><?php _e('Topic'); ?></th>
	<th><?php _e('Posts'); ?></th>
	<!-- <th><?php _e('Voices'); ?></th> -->
	<th><?php _e('Last Poster'); ?></th>
	<th><?php _e('Freshness'); ?></th>

</tr>

<?php foreach ( $topics as $topic ) : ?>
<tr<?php topic_class(); ?>>
	<td><?php bb_topic_labels(); ?> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></td>
	<td class="num"><?php topic_posts(); ?></td>
	<!-- <td class="num"><?php bb_topic_voices(); ?></td> -->
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><a href="<?php topic_last_post_link(); ?>"><?php topic_time(); ?></a></td>

</tr>
<?php endforeach; ?>
</table>

<?php else : ?>

<p><?php echo get_user_name( $user_id ); ?> <?php _e('currently has no favorites.'); ?></p>


<?php
endif;
bb_get_footer();
?>