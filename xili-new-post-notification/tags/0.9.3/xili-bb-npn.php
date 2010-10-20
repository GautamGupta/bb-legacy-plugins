<?php
/**
 * Plugin Name: xili New Post Notification (xnpn)
 * Plugin Description: Sends a notification to a selected email when a new post is created.
 * Author: michelwppi
 * Author URI: http://dev.xiligroup.com
 * Plugin URI: http://forum2.dev.xiligroup.com/forum.php?id=4
 * Version: 0.9.3
 */


/*
Changes :
- 0.9.3 - 101020 - Source cleaned for very next roadmap
- 0.9.2 - 100509 - Add option to include content of post in email - thanks to Marcus -
*/
define('XNPN_VER','0.9.3'); /* used in admin UI*/

/**
 * Add filters for the admin area
 */
add_action('bb_admin_menu_generator', 'bb_xnpn_configuration_page_add');
add_action('bb_admin-header.php', 'bb_xnpn_configuration_page_process');

function bb_xnpn_configuration_page_add() {
	bb_admin_add_submenu(__('New Post Notification','xnpn'), 'keymaster', 'bb_xnpn_configuration_page', 'options-general.php');
}

function bb_xnpn_configuration_page() {
?>
<div style="width:600px;">
	<h2><?php _e('New Post Notification Settings','xnpn'); ?></h2>
	<?php do_action( 'bb_admin_notices' ); 
	$xnpnemail = ('' == bb_get_option('xnpn_email')) ? bb_get_option('from_email') : bb_get_option('xnpn_email') ; ?>
	<form class="options" method="post" action="">
		<fieldset style="margin:2px; padding:12px 6px; border:1px solid #ccc; "><legend><?php _e("Email receiving notifications",'xnpn'); ?></legend>
			<label for="xnpn_email">
				<?php _e('Email address:','xnpn'); ?>
			</label>
			
				<input class="text" name="xnpn_email" id="xnpn_email" value="<?php echo $xnpnemail;  ?>" />
			
		
		<p>&nbsp;</p>
			<label for="xnpn_email_content">
				<?php _e('Add content in email:','xnpn'); 
				if('' != bb_get_option('xnpn_email_content')) $check = 'checked="checked"'; ?>
				
				<input id="xnpn_email_content" name="xnpn_email_content" type="checkbox" value="addcontent" <?php echo $check; ?>  />
			</label>
			<p>&nbsp;</p>
		<p><?php 
		if ('' == bb_get_option('xnpn_email')) {
			_e('This email <b>(admin default to change)</b> will receive ID of added posts','xnpn');
			$subbbt = __('Save settings &raquo;','xnpn');
		} else {
			_e('This email (update it if needed) will receive ID of added posts','xnpn');
			$subbbt = __('Update settings &raquo;','xnpn');
		}
		?></p>
		<p>&nbsp;</p>
		
			<?php bb_nonce_field( 'xnpn-configuration' ); ?>
			<input type="hidden" name="action" id="action" value="update-xnpn-configuration" />
			<div class="spacer">
				<input type="submit" name="submit" id="submit" value="<?php echo $subbbt;  ?>" />
			</div>
		</fieldset>
		
	</form>	
	<p>&nbsp;</p>
	<small>** <?php _e('xili New Post Notification by dev.xiligroup.com © 2009-10','xnpn'); echo ' v '.XNPN_VER; ?> **</small>
	</div>
<?php
}

function bb_xnpn_configuration_page_process() {
	if ($_POST['action'] == 'update-xnpn-configuration') {
		
		bb_check_admin_referer( 'xnpn-configuration' );
		
		if ($_POST['xnpn_email']) {
			$value = stripslashes_deep( trim( $_POST['xnpn_email'] ) );
			if ($value) {
				bb_update_option( 'xnpn_email', $value );
			} else {
				bb_delete_option( 'xnpn_email' );
			}
		} else {
			bb_delete_option( 'xnpn_email' );
		}
		
		if ($_POST['xnpn_email_content']) {
			bb_update_option( 'xnpn_email_content', $_POST['xnpn_email_content'] );
		} else {
			bb_delete_option( 'xnpn_email_content' );
		}
		// next version - all settings in array !
		$goback = add_query_arg('xnpn-updated', 'true', wp_get_referer());
		bb_safe_redirect($goback);
		exit;
	}
	
	if ($_GET['xnpn-updated']) {
		bb_admin_notice( __( '<strong>Notification email settings saved.</strong>','xnpn') );
	}
}

/**
 *
 * send email to choosen email
 *
 * road map : send a copy if user check the current topic
 *
 */
function admin_notification_new_post($post_id = 0) { // thanks Markus of comment suggest.
	global $bbdb, $topic_id, $bb_current_user ;
	
	$topic = get_topic($topic_id);
	
	$header = 'From: '.bb_get_option('from_email')."\n"; 
	$header .= 'MIME-Version: 1.0'."\n";
	$header .= 'Content-Type: text/plain; charset="'.BBDB_CHARSET.'"'."\n";
	$header .= 'Content-Transfer-Encoding: 7bit'."\n";
	
	$subject = __('There is a new post on: ','xnpn').$topic->topic_title;
	$msg = __('Hello,','xnpn')."\n".get_user_name($bb_current_user->ID).__(' has posted here:','xnpn')."\n\n".get_topic_link($topic_id);
	if ('' != bb_get_option('xnpn_email_content'))
	 $msg .= "\n\n"."Content:"."\n\n".strip_tags(get_post_text($post_id)); // thanks Markus of comment suggest.
	
	if ('' == bb_get_option('xnpn_email')) {
		$msg = __('Hi Keymaster, don\'t forget to visit New Post Notification settings in forum admin UI and save a right email.','xnpn').$msg;
		bb_mail(bb_get_option('from_email'), $subject, $msg, $header);
	} else {
		bb_mail(bb_get_option('xnpn_email'), $subject, $msg, $header);
	}
}

add_action('bb_new_post', 'admin_notification_new_post');
//add_action('bb_insert_post', 'admin_notification_new_post');


?>