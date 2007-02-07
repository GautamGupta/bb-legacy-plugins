<?php
require('./bb-load.php');

if ( !isset($_POST['post_content']) )
	bb_die(__('You need to actually make a post!'));
elseif ( !isset($_POST['title']) )
	bb_die(__('You need to have a message title!'));
elseif ( !isset($_POST['userid']) )
	bb_die(__('You need to pick someone to send the message to!'));

if ( !bb_current_user_can('write_posts') )
	bb_die(__('You are not allowed to post.  Are you logged in?'));

$pmtitle = trim( $_POST['title'] );
$id_receiver = trim( $_POST['userid'] );
$id_sender = $bb_current_user->ID;
$message = $_POST['post_content'];

$mess_id = pm_new_message( $id_receiver, $id_sender, $pmtitle, $message );

$link = bb_get_pm_link( '?id='.$mess_id );

if ( $mess_id )
	wp_redirect( $link );
else
	wp_redirect( get_pm_fp_link() );
exit;

?>