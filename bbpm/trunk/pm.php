<?php
/**
 * @package bbPM
 * @version 0.1-beta1
 * @author Nightgunner5
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License, Version 3 or higher
 */

/**
 * Prevent {@link http://bbpress.org/plugins/topic/subscribe-to-topic/ Subscribe to Topic}
 * from removing "unsubscribe" from the query string.
 *
 * @since 0.1-alpha6b
 */
define( 'BBPM_STT_FIX', true );

/**
 * Load the bbPress core
 */
require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

bb_auth( 'logged_in' ); // Is the user logged in?

global $bbpm, $bb_current_user;
// Don't throttle searches.
if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && !empty( $_POST['search'] ) ) {
	header( 'Content-Type: application/json' );

	if ( !bb_verify_nonce( $_POST['_wpnonce'], 'bbpm-user-search' ) )
		exit( '[]' );

	$name = $_POST['search'];
	if ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() )
		$name = stripslashes( $name );
	$name = str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $name, 0, $_POST['pos'] ) ) . '%' . str_replace( array( '%', '?' ), array( '\\%', '\\?' ), substr( $name, $_POST['pos'] ) );

	$not = array( bb_get_current_user_info( 'ID' ) );

	if ( !empty( $_POST['thread'] ) && $bbpm->can_read_thread( $_POST['thread'] ) )
		$not = $bbpm->get_thread_members( (int)$_POST['thread'] );

	global $bbdb;
	$results = $bbdb->get_col( $bbdb->prepare( 'SELECT `user_nicename` FROM `' . $bbdb->users . '` WHERE ( `user_nicename` LIKE %s OR `user_login` LIKE %s OR `display_name` LIKE %s OR `ID` = %d ) AND `ID` NOT IN (' . implode( ', ', $not ) . ') ORDER BY LENGTH(`user_nicename`) ASC LIMIT 15', $name, $name, $name, $_POST['text'] ) );

	if ( !$results )
		exit( '[]' );

	exit( '["' . implode( '","', array_map( 'addslashes', $results ) ) . '"]' );
}

if ( $throttle_time = bb_get_option( 'throttle_time' ) )
	if ( isset( $bb_current_user->data->last_posted ) && time() < $bb_current_user->data->last_posted + $throttle_time && !bb_current_user_can( 'throttle' ) )
		bb_die( __( 'Slow down; you move too fast.' ) );

if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && !empty( $_POST['pm_thread'] ) ) {
	bb_check_admin_referer( 'bbpm-add-member-' . $_POST['pm_thread'] );

	if ( $bbpm->can_read_thread( $_POST['pm_thread'] ) ) {
		if ( !$to = bb_get_user( trim( $_POST['tag'] ) ) )
			if ( !$to = bb_get_user_by_nicename( trim( $_POST['tag'] ) ) )
				if ( !$to = bb_get_user( trim( $_POST['tag'] ), array( 'by' => 'login' ) ) )
					bb_die( __( 'You need to choose a valid person to send the message to.', 'bbpm' ) );

		$bbpm->add_member( $_POST['pm_thread'], $to->ID );
	}

	bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'last_posted', time() );

	wp_redirect( bb_get_option( 'mod_rewrite' ) ? bb_get_uri( 'pm/' . $_POST['pm_thread'] ) : BB_PLUGIN_URL . basename( dirname( __FILE__ ) ) . '/?' . $_POST['pm_thread'] );
	exit;
} elseif ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && empty( $_POST['reply_to'] ) ) {
	bb_check_admin_referer( 'bbpm-new' );

	if ( !bb_current_user_can( 'write_posts' ) || ( function_exists( 'bb_current_user_is_bozo' ) && bb_current_user_is_bozo() ) ) 
		bb_die( __( 'You are not allowed to write private messages.  Are you logged in?', 'bbpm' ) );
	if ( !trim( $_POST['message'] ) )
		bb_die( __( 'You need to actually submit some content!' ) );
	if ( !trim( $_POST['title'] ) )
		bb_die( __( 'Please enter a private message title.', 'bbpm' ) );

	if ( !$to = bb_get_user( trim( $_POST['to'] ) ) )
		if ( !$to = bb_get_user_by_nicename( trim( $_POST['to'] ) ) )
			if ( !$to = bb_get_user( trim( $_POST['to'] ), array( 'by' => 'login' ) ) )
				bb_die( __( 'You need to choose a valid person to send the message to.', 'bbpm' ) );

	if ( !$to = $to->ID )
		bb_die( __( 'You need to choose a valid person to send the message to.', 'bbpm' ) );

	$redirect_to = $bbpm->send_message( $to, trim( stripslashes( $_POST['title'] ) ), stripslashes( $_POST['message'] ) );

	bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'last_posted', time() );

	if ( !$redirect_to )
		bb_die( __( 'Either your outbox or the recipient\'s inbox is full.', 'bbpm' ) );
	else
		wp_redirect( $redirect_to );
	exit;
} elseif ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' && !empty( $_POST['reply_to'] ) ) {
	$reply_to  = (int)$_POST['reply_to'];
	
	bb_check_admin_referer( 'bbpm-reply-' . $reply_to );

	if ( !bb_current_user_can( 'write_posts' ) || ( function_exists( 'bb_current_user_is_bozo' ) && bb_current_user_is_bozo() ) ) 
		bb_die( __( 'You are not allowed to write private messages.  Are you logged in?', 'bbpm' ) );
	if ( !trim( $_POST['message'] ) )
		bb_die( __( 'You need to actually submit some content!' ) );

	$_reply_to = new bbPM_Message( $reply_to );

	if ( !$_reply_to->exists )
		bb_die( __( 'There was an error sending your message.', 'bbpm' ) );

	$redirect_to = $bbpm->send_reply( $reply_to, stripslashes( $_POST['message'] ) );

	bb_update_usermeta( bb_get_current_user_info( 'ID' ), 'last_posted', time() );

	if ( !$redirect_to )
		bb_die( __( 'Either your outbox or the recipient\'s inbox is full.', 'bbpm' ) );
	else
		wp_redirect( $redirect_to );
	exit;
}

if ( isset( $_GET['unsubscribe'] ) && bb_verify_nonce( $_GET['_wpnonce'], 'bbpm-unsubscribe-' . $_GET['unsubscribe'] ) ) {
	$bbpm->unsubscribe( $_GET['unsubscribe'] );

	wp_redirect( $bbpm->get_link() );
}

?>