<?php
/*
Plugin Name: BBPress Private Messaging
Plugin URI: http://faq.rayd.org/bbpress_private_message/
Description: Integrates Private Messages into BBPress
Author: Joshua Hutchins
Change Log: .80 - Added template functionality
				- Fixed WPMU support
Author URI: http://ardentfrost.rayd.org/
Version: 0.80
*/

function pm_install_check() {
	global $bbdb, $bb_table_prefix;
	
	$bbdb->hide_errors();
	$installed = $bbdb->get_results("show tables like ".$bb_table_prefix."privatemessages");
	
	if ( !$installed ) :
		$bbdb->query(" CREATE TABLE IF NOT EXISTS `".$bb_table_prefix."privatemessages` (
`pm_id` BIGINT ( 20 ) NOT NULL AUTO_INCREMENT,
`id_sender` INT( 11 ) NOT NULL ,
`id_receiver` INT( 11 ) NOT NULL ,
`seen` INT( 2 ) NOT NULL ,
`pmtitle` VARCHAR( 64 ) NOT NULL ,
`message` VARCHAR( 2048 ) NOT NULL ,
`created_on` DATETIME NOT NULL ,
PRIMARY KEY ( `pm_id` )
)");
	endif;
	$bbdb->show_errors();
}

function pm_fp_link() {
	global $bbdb, $bb_table_prefix, $bb_current_user, $bb;
	
	pm_install_check();
	
	$num = $bbdb->get_var("SELECT COUNT(*) FROM ".$bb_table_prefix."privatemessages WHERE seen = 0 AND id_receiver = ".$bb_current_user->ID."");
	
	echo "<a href=\"";
	echo apply_filters('pm_fp_link', get_pm_fp_link() );
	echo "\">You have ".$num." new messages</a>";
}

function pm_mess_link() {
	echo apply_filters('pm_mess_link', get_pm_fp_link() );
}

function pm_user_link( $userid ) {
	if ( bb_current_user_can('write_post') ) {
		echo '<a href="';
		echo apply_filters('pm_user_link', bb_get_pm_link( '?user='.$userid ) );
		echo '">PM This User</a>';
	} else
		echo "Login to Send PM";
}

function get_pm_fp_link( $tag = '' ) {
	global $bb;
	if ( $bb->mod_rewrite )
		$r = bb_get_option('uri') . "pm" . ( '' != $tag ? "$tag" : '' );
	else
		$r = bb_get_option('uri') . "pm.php" . ( '' != $tag ? "$tag" : '' );
	return apply_filters( 'get_pm_fp_link', $r );
}

function new_pm( $text = false ) {
	if (!$text )
		$text = __('New PM &raquo;');
		
	if ( !bb_is_user_logged_in() )
		$url = add_query_arg( 're', urlencode($url), bb_get_option( 'uri' ) . 'bb-login.php' );
	elseif ( !bb_current_user_can( 'write_topics' ) )
			return;
	else
		$url = get_pm_fp_link('?new=1');

	if ( $url )
		echo "<a href='$url' class='new-pm'>$text</a>\n";
} 

function pm_seen( $mid ) {
	global $bbdb, $bb_table_prefix;
	
	$bbdb->query("UPDATE ".$bb_table_prefix."privatemessages SET seen = 1 WHERE pm_id = $mid");
}

function pm_delete() {
	global $bbdb, $bb_table_prefix;
	
	$todel = $_POST["todel"];
	
	if( isset($todel) ) {
		foreach( $todel as $d ) :
			$bbdb->query("DELETE FROM ".$bb_table_prefix."privatemessages WHERE pm_id = $d");
		endforeach; }
}

function pm_new_message( $id_receiver, $id_sender, $pmtitle, $message ){
	global $bbdb, $bb_table_prefix;
	
	$created_on = bb_current_time('mysql');
	
	$bbdb->query("INSERT INTO ".$bb_table_prefix."privatemessages
		(id_sender, id_receiver, pmtitle, message, created_on)
		VALUES
		('$id_sender', '$id_receiver', '$pmtitle',  '$message','$created_on')");
}

function get_pms() {
	global $bb_current_user, $bbdb, $bb_table_prefix;

	return $bbdb->get_results("SELECT * FROM ".$bb_table_prefix."privatemessages WHERE id_receiver = $bb_current_user->ID ORDER BY created_on DESC");
}

function get_sent_pms() {
	global $bb_current_user, $bbdb, $bb_table_prefix;

	return $bbdb->get_results("SELECT * FROM ".$bb_table_prefix."privatemessages WHERE id_sender = $bb_current_user->ID ORDER BY created_on DESC");
}

function pm_get_message( $mid ) {
	global $bbdb, $bb_table_prefix;
	
	return $bbdb->get_row("SELECT * FROM ".$bb_table_prefix."privatemessages WHERE pm_id = $mid");
}

function bb_pm_link() {
	echo apply_filters('bb_pm_link', bb_get_pm_link() );
}

function bb_get_pm_link( $tag = '' ) {
	global $bb;
	if ( $bb->mod_rewrite )
		$r = bb_get_option('uri') . "message" . ( '' != $tag ? "$tag" : '' );
	else
		$r = bb_get_option('uri') . "message.php" . ( '' != $tag ? "$tag" : '' );
	return apply_filters( 'get_pm_link', $r );
}

function get_pm_time( $timevar ) {
	global $bb;
	return strftime("%m/%d/%y at %r",strtotime($timevar)+($bb->gmt_offset * 60 * 60));
}

function pm_post_form( $h2 = '' ) {
	global $bb_current_user, $bb;

	if ( empty($h2) )
		$h2 = __('Send Private Message');

	if ( !empty($h2) )
		echo "<h2 class='post-form'>$h2</h2>\n";

	if ( bb_current_user_can( 'write_post' ) ) {
		echo "<form class='postform' name='postform' id='postform' method='post' action='" . bb_get_option('uri') . "pm-post.php'>\n";
		if ( file_exists( bb_get_template( 'pm-form.php' ) ) ) {
			include( bb_get_template( 'pm-form.php' ) );
		} else {
			die("You do not have pm-form.php in your template folder");
		}
		echo "\n</form>";
	} elseif ( !bb_is_user_logged_in() ) {
		echo '<p>';
		_e(sprintf('You must <a href="%s">login</a> to post.', bb_get_option('uri') . 'bb-login.php'));
		echo '</p>';
	}
}

function pm_reply_form( $h2 = '', $pmmessage ) {
	global $bb_current_user, $bb;

	if ( empty($h2) )
		$h2 = __('Send Private Message');

	if ( !empty($h2) )
		echo "<h2 class='post-form'>$h2</h2>\n";

	if ( !bb_is_user_logged_in() ) {
		echo '<p>';
		_e(sprintf('You must <a href="%s">login</a> to post.', bb_get_option('uri') . 'bb-login.php'));
		echo '</p>';
	} elseif ( bb_current_user_can( 'write_post' ) ) {
		echo "<form class='messform' name='messform' id='messform' method='post' action='" . bb_get_option('uri') . "pm-post.php'>\n";
		if ( file_exists( bb_get_template( 'message-form.php' ) ) ) {
			include( bb_get_template( 'message-form.php' ) );
		} else {
			die("You do not have message-form.php in your template folder");
		}
		echo "\n</form>"; 
	}
}

function pm_user_form( $pmuser, $h2 = '') {
	global $bb_current_user, $bb;
	bb_get_header();
	echo '<h3 class="bbcrumb"><a href="';
	echo option('uri');
	echo '">';
	echo option('name');
	echo '</a> &raquo; Private Messages</h3>';

	if ( empty($h2) )
		$h2 = __('Send Private Message');

	if ( !empty($h2) )
		echo "<h2 class='post-form'>$h2</h2>\n";

	if ( bb_current_user_can( 'write_post' ) ) {
		echo "<form class='postform' name='postform' id='postform' method='post' action='" . bb_get_option('uri') . "pm-post.php'>\n";
		if ( file_exists( bb_get_template( 'pm-user-form.php' ) ) ) {
			include( bb_get_template( 'pm-user-form.php' ) );
		} else {
			die("You do not have pm-user-form.php in your template folder");
		}
		echo "\n</form>";
	} elseif ( !bb_is_user_logged_in() ) {
		echo '<p>';
		_e(sprintf('You must <a href="%s">login</a> to post.', bb_get_option('uri') . 'bb-login.php'));
		echo '</p>';
	}
	bb_get_footer();
}


function is_deletable( $pm ) {
	global $bb;
	if ( $pm->seen == 0 )
		return true;
	else
		return false;
}

function pm_text( $pm ) {
	echo apply_filters( 'pm_text', get_pm_text( $pm ) );
}

function get_pm_text( $pm ) {
	return $pm->message;
}

function pm_user_dropdown() {
	global $bbdb, $bb_current_user, $bb;
	if ( $bb->wp_table_prefix )
		$users = $bbdb->get_results("SELECT * FROM ".$bb->wp_table_prefix."users WHERE user_status = 0 ORDER BY user_login");
	else
		$users = $bbdb->get_results("SELECT * FROM $bbdb->users WHERE user_status = 0 ORDER BY user_login");
	echo '<select name="userid" id="userid">';
	
	foreach ( $users as $user ) :
		if ( $user->ID != $bb_current_user->ID )
			echo "<option value='$user->ID'>$user->user_login</option>";
	endforeach;
	echo '</select>';
}

function get_pm_bb_location(){
	if ( bb_find_filename($_SERVER['PHP_SELF']) == 'pm.php' )
		return 'pm-page';
}

function is_pm() {
	if ('pm-page' == get_bb_location() )
		return true;
	else
		return false;
}

function get_mess_bb_location(){
	if ( bb_find_filename($_SERVER['PHP_SELF']) == 'message.php' )
		return 'message-page';
}

function is_message() {
	if ('message-page' == get_bb_location() )
		return true;
	else
		return false;
}

bb_add_filter('get_bb_location', 'get_mess_bb_location');
bb_add_filter('get_bb_location', 'get_pm_bb_location');

?>
