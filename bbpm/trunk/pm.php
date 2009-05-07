<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/bb-load.php';

bb_auth( 'logged_in' ); // Is the user logged in?

global $bbpm;
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty( $_POST['reply_to'] ) ) {
	bb_check_admin_referer( 'bbpm-new' );

	if ( !bb_current_user_can( 'write_posts' ) || ( function_exists( 'bb_current_user_is_bozo' ) && bb_current_user_is_bozo() ) ) 
		bb_die( __( 'You are not allowed to write private messages.  Are you logged in?', 'bbpm' ) );
	if ( !trim( $_POST['message'] ) )
		bb_die( __( 'You need to actually submit some content!' ) );
	if ( !trim( $_POST['title'] ) )
		bb_die( __( 'Please enter a private message title.', 'bbpm' ) );
	$to = new BP_User( trim( $_POST['to'] ) );
	if ( !$to = $to->ID )
		bb_die( __( 'You need to choose a valid person to send the message to.', 'bbpm' ) );
	$redirect_to = $bbpm->send_message( $to, trim( stripslashes( $_POST['title'] ) ), $_POST['message'] );
	if ( !$redirect_to )
		bb_die( __( 'Either your outbox or the recipient\'s inbox is full.', 'bbpm' ) );
	else
		wp_redirect( $redirect_to );
	exit;
} elseif ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['reply_to'] ) ) {
	$reply_to  = (int)$_POST['reply_to'];
	
	bb_check_admin_referer( 'bbpm-reply-' . $reply_to );

	if ( !bb_current_user_can( 'write_posts' ) || ( function_exists( 'bb_current_user_is_bozo' ) && bb_current_user_is_bozo() ) ) 
		bb_die( __( 'You are not allowed to write private messages.  Are you logged in?', 'bbpm' ) );
	if ( !trim( $_POST['message'] ) )
		bb_die( __( 'You need to actually submit some content!' ) );

	$_reply_to = new bbPM_Message( $reply_to );

	if ( !$_reply_to->exists )
		bb_die( __( 'There was an error sending your message.', 'bbpm' ) );

	$redirect_to = $bbpm->send_message( $_reply_to->from->ID == bb_get_current_user_info( 'ID' ) ? $_reply_to->to->ID : $_reply_to->from->ID, ( $_reply_to->reply ? '' : __( 'Re: ', 'bbpm' ) ) . $_reply_to->title, $_POST['message'], $reply_to );
	if ( !$redirect_to )
		bb_die( __( 'Either your outbox or the recipient\'s inbox is full.', 'bbpm' ) );
	else
		wp_redirect( $redirect_to );
	exit;
}

if ( isset( $_GET['delete'] ) && bb_verify_nonce( $_GET['_wpnonce'], 'bbpm-delete-' . $_GET['delete'] ) ) {
	$bbpm->delete_message( $_GET['delete'] );
	wp_redirect( $bbpm->get_link() );
}

?>