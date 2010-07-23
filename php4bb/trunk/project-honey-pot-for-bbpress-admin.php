<?php

function php4bb_validate_httpbl_key( $key ) {
	if ( strlen( $key ) != 12 )
		return false;
	if ( preg_match( '/[^a-z]/', $key ) )
		return false;
	if ( gethostbyname( $key . '.0.1.1.127.dnsbl.httpbl.org' ) != '127.1.1.0' )
		return false;
	return true;
}

function php4bb_admin_parse() {
	bb_check_admin_referer( 'php4bb-admin-save' );

	$_settings = $settings = bb_get_option( 'php4bb' );

	$success = $errors = array();

	if ( $_POST['httpbl-key'] != $settings['httpbl-key'] ) {
		if ( php4bb_validate_httpbl_key( $_POST['httpbl-key'] ) ) {
			$settings['httpbl-key'] = $_POST['httpbl-key'];
			$success[] = __( 'http:BL key', 'php4bb' );
		} else {
			$errors[] = __( 'Your http:BL key was invalid.', 'php4bb' );
		}
	}

	if ( $_POST['httpbl-days'] != $settings['httpbl-days'] ) {
		if ( preg_match( '/^[1-9][0-9]*$/', $_POST['httpbl-days'] ) ) {
			$settings['httpbl-days'] = $_POST['httpbl-days'];
			$success[] = __( 'Maximum days', 'php4bb' );
		} else {
			$errors[] = __( '"Maximum days" must be a positive whole number.', 'php4bb' );
		}
	}

	if ( $_POST['httpbl-threat'] != $settings['httpbl-threat'] ) {
		if ( preg_match( '/^[1-9][0-9]*$/', $_POST['httpbl-threat'] ) ) {
			$settings['httpbl-threat'] = $_POST['httpbl-threat'];
			$success[] = __( 'Minimum threat', 'php4bb' );
		} else {
			$errors[] = __( '"Minimum threat" must be a positive whole number.', 'php4bb' );
		}
	}

	if ( $_POST['httpbl-confirmedonly'] != ( $settings['httpbl-confirmedonly'] ? 'only' : 'all' ) ) {
		if ( in_array( $_POST['httpbl-confirmedonly'], array( 'only', 'all' ) ) ) {
			$settings['httpbl-confirmedonly'] = $_POST['httpbl-confirmedonly'] == 'only';
			$success[] = __( 'Confirmed spammers only', 'php4bb' );
		} else {
			$errors[] = __( 'Your "confirmed spammers only" setting was invalid.', 'php4bb' );
		}
	}

	if ( $_POST['httpbl-redirect'] != $settings['httpbl-redirect'] ) {
		if ( esc_url_raw( $_POST['httpbl-redirect'], array( 'http', 'https' ) ) == $_POST['httpbl-redirect'] ) {
			$settings['httpbl-redirect'] = $_POST['httpbl-redirect'];
			$success[] = __( 'Redirect spammers', 'php4bb' );
		} else {
			$errors[] = __( '"Redirect spammers" must be blank or an http or https URL.', 'php4bb' );
		}
	}

	if ( $_POST['noemail'] != $settings['noemail'] ) {
		if ( esc_url_raw( $_POST['noemail'], array( 'http', 'https' ) ) == $_POST['noemail'] ) {
			$settings['noemail'] = $_POST['noemail'];
			$success[] = __( 'Email collection policy', 'php4bb' );
		} else {
			$errors[] = __( '"Email collection policy" must be blank or an http or https URL.', 'php4bb' );
		}
	}

	if ( $_POST['hpot-url'] != $settings['hpot-url'] ) {
		if ( esc_url_raw( $_POST['hpot-url'], array( 'http', 'https' ) ) == $_POST['hpot-url'] ) {
			$settings['hpot-url'] = $_POST['hpot-url'];
			$success[] = __( 'Honey pot URL', 'php4bb' );
		} else {
			$errors[] = __( '"Honey pot URL" must be blank or an http or https URL.', 'php4bb' );
		}
	}

	bb_update_option( 'php4bb', $settings );

	if ( $success ) {
		bb_admin_notice( __( 'The following updates were successful:', 'php4bb' ) . '</p><p>' . implode( '<br/>', $success ) );
	}
	if ( $errors ) {
		bb_admin_notice( __( 'The following errors occurred:', 'php4bb' ) . '</p><p>' . implode( '<br/>', $errors ) );
	}
}
if ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' )
	add_action( 'php4bb_admin_page_pre_head', 'php4bb_admin_parse' );

function php4bb_admin_page() {
	$settings = bb_get_option( 'php4bb' );
	$options = array(
		'httpbl-key' => array(
			'title' => __( 'http:BL key', 'php4bb' ),
			'class' => array( 'code' ),
			'note' => __( '<a href="http://www.projecthoneypot.org/httpbl_configure.php">Get your http:BL key here</a>.', 'php4bb' ),
			'value' => $settings['httpbl-key'] ? $settings['httpbl-key'] : ''
		),
		'httpbl-days' => array(
			'title' => __( 'Maximum days', 'php4bb' ),
			'class' => array( 'short' ),
			'note' => __( 'Only block if an offence has occurred in the last X days. Higher numbers are more strict.', 'php4bb' ),
			'value' => $settings['httpbl-days'] ? $settings['httpbl-days'] : '30'
		),
		'httpbl-threat' => array(
			'title' => __( 'Minimum threat', 'php4bb' ),
			'class' => array( 'short' ),
			'note' => __( 'Only block if the <a href="http://www.projecthoneypot.org/threat_info.php">threat rating</a> is at least X. Lower numbers are more strict.', 'php4bb' ),
			'value' => $settings['httpbl-threat'] ? $settings['httpbl-threat'] : '15'
		),
		'httpbl-confirmedonly' => array(
			'title' => __( 'Confirmed spammers only', 'php4bb' ),
			'type' => 'radio',
			'options' => array(
				'only' => __( 'Only block confimed spammers', 'php4bb' ),
				'all' => __( 'Also block suspected spammers', 'php4bb' )
			),
			'value' => $settings['httpbl-confirmedonly'] ? 'only' : 'all'
		),
		'httpbl-redirect' => array(
			'title' => __( 'Redirect spammers', 'php4bb' ),
			'class' => array( 'long', 'code' ),
			'note' => __( 'Leave this empty to block spammers without redirecting them.', 'php4bb' ),
			'value' => $settings['httpbl-redirect'] ? $settings['httpbl-redirect'] : ''
		),
		'noemail' => array(
			'title' => __( 'Email collection policy', 'php4bb' ),
			'class' => array( 'long', 'code' ),
			'note' => __( 'Put a link to your email collection policy in this box. If the box is empty, <a href="http://www.unspam.com/noemailcollection">the default</a> will be used. See <a href="http://www.projecthoneypot.org/model_terms_of_use.php"></a> for a model of what it should look like.', 'php4bb' ),
			'value' => $settings['noemail'] ? $settings['noemail'] : 'http://www.unspam.com/noemailcollection'
		),
		'hpot-url' => array(
			'title' => __( 'Honey pot URL', 'php4bb' ),
			'class' => array( 'long', 'code' ),
			'note' => __( 'Enter the address of your <a href="http://www.projecthoneypot.org/manage_honey_pots.php">honey pot</a> or <a href="http://www.projecthoneypot.org/manage_quicklink.php">QuickLink</a> and links will be automatically added to your forum.', 'php4bb' ),
			'value' => $settings['hpot-url'] ? $settings['hpot-url'] : ''
		)
	);
?>
<h2><img src="<?php echo bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ); ?>/target.png" style="vertical-align:top"/> <?php _e( 'Project Honey Pot for bbPress', 'php4bb' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<p><?php _e( 'If you haven\'t already, <a href="http://www.projecthoneypot.org/?rf=59295">join Project Honey Pot</a>.', 'php4bb' ); ?></p>
<form class="settings" method="post" action="<?php bb_uri('bb-admin/admin-base.php', array( 'plugin' => 'php4bb_admin_page' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN); ?>">
	<fieldset>
<?php

foreach ( $options as $option => $args ) {
	bb_option_form_element( $option, $args );
}

?>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'php4bb-admin-save' ); ?>
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Save changes', 'php4bb' ); ?>" />
	</fieldset>
</form>
<?php if ( $blocks = (int)bb_get_option( 'php4bb_blocks' ) ) { ?>
<div style="font-size: .75em; position: absolute; bottom: 50px; right: 5px"><?php printf( _n( '%s spammer blocked by Project Honey Pot for bbPress', '%s spammers blocked by Project Honey Pot for bbPress', $blocks, 'php4bb' ), bb_number_format_i18n( $blocks ) ); ?></div>
<?php } ?>
<?php
}

function php4bb_admin_add() {
	bb_admin_add_submenu( __( 'Project Honey Pot for bbPress', 'php4bb' ), 'use_keys', 'php4bb_admin_page', 'options-general.php' );
}
add_action( 'bb_admin_menu_generator', 'php4bb_admin_add' );