<?php
/**
 * Plugin Name: xili New Post Notification (xnpn)
 * Plugin Description: Sends a notification to a selected email when a new post is created.
 * Author: michelwppi
 * Author URI: http://dev.xiligroup.com
 * Plugin URI: http://forum2.dev.xiligroup.com/forum.php?id=4
 * Version: 0.9.5
 */


/*
Changes :
- 0.9.5 - 101024 - po, 1011211 - fixes issues with some servers when 7bits Contents-Transfer-Encoding: now 8bits by default
- 0.9.4 - 101023 - if checked in user profile - send a copy to users (is set in bb_favorites) - new options
- 0.9.3 - 101020 - Source cleaned for very next roadmap
- 0.9.2 - 100509 - Add option to include content of post in email - thanks to Marcus -
*/
define('XNPN_VER','0.9.5'); /* used in admin UI*/

/**
 * Add filters for the admin area
 */
if ( bb_is_admin() ) {
	add_action('bb_admin_menu_generator', 'bb_xnpn_configuration_page_add');
	add_action('bb_admin-header.php', 'bb_xnpn_configuration_page_process');
}


function bb_xnpn_configuration_page_add() {
	bb_admin_add_submenu(__('New Post Notification','xnpn'), 'keymaster', 'bb_xnpn_configuration_page', 'options-general.php');
}

function bb_xnpn_configuration_page() {
?>
<div style="width:600px;">
	<h2><?php _e('New Post Notification Settings','xnpn'); ?></h2>
	<?php do_action( 'bb_admin_notices' ); 
	$xnpn_admin_configuration = bb_get_option('xnpn_admin_configuration') ;
	if ( empty( $xnpn_admin_configuration ) ) { // for update since 0.9.4
		
		$xnpn_admin_configuration = array();
		$xnpn_admin_configuration['xnpn_email'] = ('' == bb_get_option('xnpn_email')) ? bb_get_option('from_email') : bb_get_option('xnpn_email') ;
		$xnpn_admin_configuration['xnpn_email_content'] = ('' == bb_get_option('xnpn_email_content')) ? '' : bb_get_option('xnpn_email_content') ;
		bb_update_option( 'xnpn_admin_configuration', $xnpn_admin_configuration );
		bb_delete_option( 'xnpn_email' ); // unti 0.9.3
		bb_delete_option( 'xnpn_email_content' );
		
	} 
		$xnpn_email = ( '' == $xnpn_admin_configuration['xnpn_email'] ) ? bb_get_option('from_email') : $xnpn_admin_configuration['xnpn_email'] ; // default value
	
	
	 ?>
	<form class="options" method="post" action="">
		<fieldset style="margin:2px; padding:12px 6px; border:1px solid #ccc; "><legend><?php _e("Email receiving notifications",'xnpn'); ?></legend>
			<label for="xnpn_email">
				<?php _e('Email address:','xnpn'); ?>
			</label>
			
				<input class="text" name="xnpn_email" id="xnpn_email" value="<?php echo $xnpn_email;  ?>" />
			
		
		<p>&nbsp;</p>
		<p><?php 
		if ( '' == $xnpn_admin_configuration['xnpn_email'] ) {
			_e('This email <b>(admin default to change)</b> will receive ID of added posts','xnpn');
			$subbbt = __('Save settings &raquo;','xnpn');
		} else {
			_e('This email (update it if needed) will receive ID of added posts','xnpn');
			$subbbt = __('Update settings &raquo;','xnpn');
		}
		?></p>
		<p>&nbsp;</p>
			<label for="xnpn_email_content">
				<?php _e('Add content in email:','xnpn'); 
				if ( 'addcontent' == $xnpn_admin_configuration['xnpn_email_content'] ) $check = 'checked="checked"'; ?>
				
				<input id="xnpn_email_content" name="xnpn_email_content" type="checkbox" value="addcontent" <?php echo $check; ?>  />
			</label>
			<p>&nbsp;</p>
			<label for="xnpn_server_aware">
				<?php _e('Each user can choose to receive email when a topic is his favorite and when this topic is updated. <br />Check here if you are sure that your server (and ISP) is not limited and able to send a series of emails:','xnpn'); 
				if ( 'serveraware' == $xnpn_admin_configuration['xnpn_server_aware'] ) $check2 = 'checked="checked"'; ?>
				
				&nbsp;<input id="xnpn_server_aware" name="xnpn_server_aware" type="checkbox" value="serveraware" <?php echo $check2; ?>  />
			</label>
			<p>&nbsp;</p>
		
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
		$xnpn_admin_configuration = bb_get_option('xnpn_admin_configuration') ;
		
				
			if ($_POST['xnpn_email']) {
				$value = stripslashes_deep( trim( $_POST['xnpn_email'] ) );
				if ($value) {
					$xnpn_admin_configuration['xnpn_email'] = $value ;
				} else {
					$xnpn_admin_configuration['xnpn_email'] = "";
				}
			} else {
				$xnpn_admin_configuration['xnpn_email'] = "";
			}
			
			if ($_POST['xnpn_email_content']) {
				$xnpn_admin_configuration['xnpn_email_content'] =  $_POST['xnpn_email_content'] ;
			} else {
				$xnpn_admin_configuration['xnpn_email_content'] = "" ;
			}
			if ($_POST['xnpn_server_aware']) {
				$xnpn_admin_configuration['xnpn_server_aware'] =  $_POST['xnpn_server_aware'] ;
			} else {
				$xnpn_admin_configuration['xnpn_server_aware'] = "" ;
			}
			
			bb_update_option( 'xnpn_admin_configuration', $xnpn_admin_configuration );
		
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
	
	$xnpn_admin_configuration = bb_get_option('xnpn_admin_configuration') ;
	if ( !empty($xnpn_admin_configuration) )  { // to be sure that settings was done

		$topic = get_topic($topic_id);
		
		$header = 'From: '.bb_get_option('from_email')."\n"; 
		$header .= 'MIME-Version: 1.0'."\n";
		$header .= 'Content-Type: text/plain; charset="'.BBDB_CHARSET.'"'."\n";
		$header .= 'Content-Transfer-Encoding: 8bit'."\n"; // 0.9.5
		
		$subject = __('There is a new post on: ','xnpn').$topic->topic_title;
		$msg = __('Hello,','xnpn')."\n".get_user_name($bb_current_user->ID).__(' has posted here:','xnpn')."\n\n".get_topic_link($topic_id);
		if ('' != $xnpn_admin_configuration['xnpn_email_content'])
		 $msg .= "\n\n"."Content:"."\n\n".strip_tags(get_post_text($post_id)); // thanks Markus of comment suggest.
		$theadminmsg = ( $xnpn_admin_configuration['xnpn_server_aware'] == 'serveraware' ) ? __('Users receive email if topic is favorite.','xnpn') : __('Users do not receive email even if topic is favorite - see plugin settings !','xnpn');
		$msg .= "\n\n".sprintf(__('INFOS : %s','xnpn'),$theadminmsg)."\n\n";
		
		if ( '' == $xnpn_admin_configuration['xnpn_email'] ) {
			$msg = __('Hi Keymaster, don\'t forget to visit New Post Notification settings in forum admin UI and save a right email.','xnpn').$msg;
			bb_mail( bb_get_option('from_email') , $subject, $msg, $header);
		} else {
			bb_mail( $xnpn_admin_configuration['xnpn_email'] , $subject, $msg, $header);
		}
		
		if ( $xnpn_admin_configuration['xnpn_server_aware'] == 'serveraware' )  { // to be sure that settings was done
			// check the list of users
			$xnpn_users_configuration = bb_get_option('xnpn_users_configuration') ; 
			$users = bb_user_search();
			//print_r($users);
			foreach ($users as $curuser) {
				//if ( bb_is_trusted_user($curuser->ID) ) { = only admin , keymaster, moderator
				// check the list of favorites of each user
					$cur_user = bb_get_user($curuser->ID);
				// check if user has set
					$userchecks = false ;
				    if ( array() != $xnpn_users_configuration ) {
						if ( isset($xnpn_users_configuration[$curuser->ID]) ) {
							$userchecks = ( $xnpn_users_configuration[$curuser->ID][0] == 1 ) ? true : false ;
						}
				    }
				// is topic_id inside list
					if ( is_user_favorite($curuser->ID , $topic_id) && $userchecks ){
						// send topic to user email
						$msg = __('Hello,','xnpn')." ".get_user_name($curuser->ID).",\n\n".get_user_name($bb_current_user->ID).__(' has posted here:','xnpn')."\n\n".get_topic_link($topic_id);
						if ( '' != $xnpn_admin_configuration['xnpn_email_content'] )
				 				$msg .= "\n\n"."Content:"."\n\n".strip_tags(get_post_text($post_id));
				 		$msg .= "\n\n".__('You receive this email because you have choosen this topic','xnpn')." (".$topic->topic_title.") ".__('as favorite','xnpn');
						bb_mail($cur_user->user_email, $subject, $msg, $header);
					}
				//}
			}
		} // server aware
	} // if config is set
}

add_action('bb_new_post', 'admin_notification_new_post');
//add_action('bb_insert_post', 'admin_notification_new_post');


if ( bb_is_profile() ) { // && $_GET['tab'] != 'new-post-notification'
	add_action( 'bb_profile_menu', 'bb_check_xnpn_add_profile_tab');
	add_filter( 'get_profile_info_keys','bb_check_xnpn_profile_key',250);
}

function bb_check_xnpn_add_profile_tab() {
	global $self;
	if ( !$self ) {
		$role="read";
		add_profile_tab(__('New Post Notification','xnpn'), $role, $role, dirname( __FILE__ ) .'/xili-bb-npn-ui.php','xnpn' );	
	}
		
}



function bb_check_xnpn_profile_key($keys) {	// inserts xnpn into profile without hacking
	global $self;
	if (empty($self)==true && isset($_GET['tab'])==false && bb_get_location()=="xnpn") {	
		(array) $keys = array_merge(array_slice((array) $keys, 0 , 1), array('xili-new-post-notification' => array(0, "xnpn")), array_slice((array) $keys,  1));    
	}
	return (array) $keys;
}



?>