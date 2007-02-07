<?php
require_once('./bb-load.php');

$go = false;

pm_install_check();

pm_delete();

if ( isset($_GET['new']) && '1' == $_GET['new'] )
	$pms = false; 
elseif ( !bb_is_user_logged_in() ) {
} else {
	$pms = get_pms();
	$sentpms = get_sent_pms();
	if( !$pms )
		$go = true; }

bb_load_template( 'privatemessages.php', array( 'go', 'pms', 'sentpms' ) );
?>
