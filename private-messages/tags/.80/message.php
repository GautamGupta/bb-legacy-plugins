<?php
require_once('./bb-load.php');

if ( isset($_GET['id']) ) :
	$messageid = $_GET['id'];
else :
	$messageid = 0;
endif;

if ( $messageid > 0 ){
	$pmmessage = pm_get_message( $messageid );
	if ($bb_current_user->ID == $pmmessage->id_receiver) 
		pm_seen( $messageid );
} elseif ( isset($_GET['user']) ) {
	$toid = $_GET['user'];
	if ($bb->wp_table_prefix)
		$touser = $bbdb->get_row("SELECT * FROM ".$bb->wp_table_prefix."users WHERE ID = $toid");
	else
		$touser = $bbdb->get_row("SELECT * FROM ".$bb_table_prefix."users WHERE ID = $toid");
	pm_user_form( $touser, '' );
	exit;
} else
	echo "Error: No Message Specified";

bb_load_template( 'postmsg.php', array( 'messageid', 'pmmessage', 'touser', 'toid' ) );
?>