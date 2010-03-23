<?php
/**
 * @package After the Deadline
 * @subpackage Public Section
 * @category Ignore Always (AJAX)
 * @author Gautam Gupta (www.gaut.am)
 * @link http://gaut.am/bbpress/plugins/after-the-deadline/
 */

/* 
 *  Called when Ignore Always is clicked (setup as an action through admin-ajax.php)
 */
function atd_ignore_call() {
	$user = bb_get_current_user();
	if ( !$user || $user->ID == 0 )
		return;
	
	/* Get, process, save */
	$uo = bb_get_usermeta( $user->ID, ATD_USER_OPTIONS );
	$uo['ignorealways']	= explode( ',', $uo['ignorealways'] );
	$uo['ignorealways'][]	= esc_attr( $_GET['phrase'] );
	$uo['ignorealways']	= implode( ',', array_unique( $uo['ignorealways'] ) );
	bb_update_usermeta( $user->ID, ATD_USER_OPTIONS, $uo );

	header( 'Content-Type: text/xml' );
	echo '<success></success>';
	die();
}
add_action( 'bb_ajax_atd_ignore', 'atd_ignore_call' );

?>