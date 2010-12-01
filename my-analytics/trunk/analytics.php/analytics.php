<?php
/*
Plugin Name: My Analytics
Plugin URI: http://forum.aperto-nota.fr/topic.php?id=3
Description: Add Google Analytics to BBPress. It is based on Askimet code and can be used as demonstration.
Author: Thierry HUET
Version: 0.0
Author URI: http://www.aperto-nota.fr
*/


add_action('bb_head', 'ap_add_analytics');
add_action( 'bb_admin_menu_generator', 'ap_configuration_page_add' );
add_action( 'ap_configuration_page_pre_head', 'ap_configuration_page_process' );

function ap_configuration_page_add()
{
	// In order to add a menu to the Admin Setting -----------------------------
	bb_admin_add_submenu( __( 'My Analytics' ), 'moderate', 'ap_configuration_page', 'options-general.php' );
}

function ap_configuration_page()
{
	?>
	<h2><?php _e( 'My Analytics Settings' ); ?></h2>
	<?php
	do_action( 'bb_admin_notices' ); ?>
	To be active, you must have an account on Google Analytics. 
	<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'ap_configuration_page'), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
		<fieldset>
<?php
	$after = '';
	if ( false !== $key = bb_get_option( 'gganalytics_key' ) ) {
		if ( ap_verify_key( $key ) ) {
			$after = __( 'This key is valid' );
		} else {
			bb_delete_option( 'gganalytics_key' );
		}
	}

	bb_option_form_element( 'gganalytics_key', array(
		'title' => __( 'Google Analytics Account' ),
		'attributes' => array( 'maxlength' => 12 ),
		'after' => $after,
		'note' => sprintf( __( 'If you don\'t have a Google Analytics Account, you can get one at <a href="%s">google.com</a>' ), 'http://www.google.com/analytics/' )
	) );

?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'options-myanalytics-update' ); ?>
			<input type="hidden" name="action" value="update-myanalytics-settings" />
			<input class="submit" type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
	</fieldset>
	</form> 
	<?php
}


function ap_configuration_page_process()
{
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-myanalytics-settings') {
		bb_check_admin_referer( 'options-myanalytics-update' );

		$goback = remove_query_arg( array( 'invalid-myanalytics', 'updated-myanalytics' ), wp_get_referer() );


		if ( $_POST['gganalytics_key'] ) {
			$value = stripslashes_deep( trim( $_POST['gganalytics_key'] ) );
			if ( $value ) {
				if ( ap_verify_key( $value ) ) {
					bb_update_option( 'gganalytics_key', $value );
				} else {
					$goback = add_query_arg( 'invalid-myanalytics', 'true', $goback );
					bb_safe_redirect( $goback );
					exit;
				}
			} else {
				bb_delete_option( 'gganalytics_key' );
			}
		} else {
			bb_delete_option( 'gganalytics_key' );
		}

		$goback = add_query_arg( 'updated-myanalytics', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['updated-myanalytics'] ) ) {
		bb_admin_notice( __( '<strong>Settings saved.</strong>' ) );
	}

	if ( !empty( $_GET['invalid-myanalytics'] ) ) {
		bb_admin_notice( __( '<strong>The key you attempted to enter is invalid. Reverting to previous setting.</strong>' ), 'error' );
	}

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';	
}	

function ap_verify_key($key)
{
	// Not yet defiend ---------------------------------
	return 1 ;
}

function ap_add_analytics()
{
	// -- Code Analytics --------------------
	$mycode  = "<!-- Implantation du code Google Analytics --->\n" ;
	$mycode .= "<script type='text/javascript'>\n";
	$mycode .= "\tvar _gaq = _gaq || [];\n" ;
	$mycode .= "\t_gaq.push(['_setAccount', '".bb_get_option('gganalytics_key')."']);\n" ;
	$mycode .= "\t_gaq.push(['_trackPageview']);\n" ;
	$mycode .= "\t(function() {\n" ;
	$mycode .= "\t\tvar ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
   $mycode .= "\t\tga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
   $mycode .= "\t\tvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
	$mycode .= "\t})();\n";
	$mycode .= "</script>\n" ;
	$mycode .= "<!-- Fin de l'implantation ------------------->";
	 
	// -- Fin code --------------------------
		echo $mycode ;
}


?>