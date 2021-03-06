<?php

define( 'BBPM_STT_FIX', true );

require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

bb_auth( 'logged_in' ); // Is the user logged in?

global $bbpm, $bb_current_user;
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

	wp_redirect( wp_get_referer() );
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