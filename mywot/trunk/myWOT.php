<?php
/*
Plugin Name: My WOT
Plugin URI: http://forum.aperto-nota.fr/topic.php?id=5
Description: Add WOT meta authentication. For more information on WOT, see http://www.mywot.com
Author: Thierry HUET
Version: 0.1
Author URI: http://www.aperto-nota.fr
*/


add_action('bb_head', 'ap_add_wot');
add_action( 'bb_admin_menu_generator', 'ap_wot_configuration_page_add' );
add_action( 'ap_wot_configuration_page_pre_head', 'ap_wot_configuration_page_process' );

// Bail here if no key is set
if ( !bb_get_option( 'mywot_key' ) ) {
	return;
}

function ap_wot_configuration_page_add()
{
	// In order to add a menu to the Admin Setting -----------------------------
	bb_admin_add_submenu( __( 'My WOT' ), 'moderate', 'ap_wot_configuration_page', 'options-general.php' );
}

function ap_wot_configuration_page()
{
	?>
	<h2><?php _e( 'My WOT Settings' ); ?></h2>
	<?php
	do_action( 'bb_admin_notices' ); ?>
	The best way, to be checked with good reputation is to be identified by WOT. 
	<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'ap_wot_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
		<fieldset>
<?php
	$after = '';
	if ( false !== $key = bb_get_option( 'mywot_key' ) ) {
		if ( ap_verify_key( $key ) ) {
			$after = __( 'This key is valid' );
		} else {
			bb_delete_option( 'mywot_key' );
		}
	}

	bb_option_form_element( 'mywot_key', array(
		'title' => __( 'my WOT meta tag' ),
		'attributes' => array( 'maxlength' => 20 ),
		'after' => $after,
		'note' => sprintf( __( 'If you don\'t have a meta tag, please go to <a href="%s">myWot.com</a>' ), 'http://www.mywot.com/' )
	) );

?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'options-mywot-update' ); ?>
			<input type="hidden" name="action" value="update-mywot-settings" />
			<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
	</fieldset>
	</form> 
	<?php
}


function ap_wot_configuration_page_process()
{
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-mywot-settings') {
		bb_check_admin_referer( 'options-mywot-update' );

		$goback = remove_query_arg( array( 'invalid-mywot', 'updated-mywot' ), wp_get_referer() );


		if ( $_POST['mywot_key'] ) {
			$value = stripslashes_deep( trim( $_POST['mywot_key'] ) );
			if ( $value ) {
				if ( ap_wot_verify_key( $value ) ) {
					bb_update_option( 'mywot_key', $value );
				} else {
					$goback = add_query_arg( 'invalid-mywot', 'true', $goback );
					bb_safe_redirect( $goback );
					exit;
				}
			} else {
				bb_delete_option( 'mywot_key' );
			}
		} else {
			bb_delete_option( 'mywot_key' );
		}

		$goback = add_query_arg( 'updated-mywot', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['updated-mywot'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	if ( !empty( $_GET['invalid-mywot'] ) ) {
		bb_admin_notice( __( '<strong>The key you attempted to enter is invalid. Reverting to previous setting.</strong>' ), 'error' );
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';	
}	

function ap_wot_verify_key($key)
{
	// Not yet defiend ---------------------------------
	return 1 ;
}

function ap_add_wot()
{
	// -- Code Analytics --------------------
	$mycode  = "<!-- Implantation du code Google Analytics --->\n" ;
	$mycode .= "\t<meta name='wot-verification' content='".bb_get_option('mywot_key')."'>\n" ;
	$mycode .= "<!-- Fin de l'implantation ------------------->";
	 
	// -- Fin code --------------------------
		echo $mycode ;
}


?>