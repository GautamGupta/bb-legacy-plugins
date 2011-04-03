<?php
/**
 * @package Swirl Unknowns
 */


/**
 * Add plugin actions
 */
add_action( 'bb_admin_menu_generator', 'swirl_unknowns_configuration_page_add' );

if ( !empty( $_GET['plugin'] ) && 'swirl_unknowns_configuration_page' == $_GET['plugin'] ) // Add plugin configuration page head if on plugin configuration page
	add_action( 'swirl_unknowns_configuration_page_pre_head', 'swirl_unknowns_configuration_page_process' );


/**
 * Functions
 */

/**
 * Add plugin submenu
 *
 * @uses bb_admin_add_submenu()
 *
 * @return void
 */
function swirl_unknowns_configuration_page_add() {
	bb_admin_add_submenu( SWIRL_UNKNOWNS_NAME, 'use_keys', 'swirl_unknowns_configuration_page' );
}

/**
 * Display plugin configuration page
 *
 * @global $swirl_unknowns_settings
 *
 * @uses do_action()
 * @uses bb_uri()
 * @uses bb_option_form_element()
 * @uses bb_nonce_field()
 *
 * @return void
 */
function swirl_unknowns_configuration_page() {
	global $swirl_unknowns_settings;
?>
<h2><?php printf( __( '%s Settings', SWIRL_UNKNOWNS_ID ), SWIRL_UNKNOWNS_NAME ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'swirl_unknowns_configuration_page' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<p><?php _e( 'Redirects non-logged-in users to a page of your choice.', SWIRL_UNKNOWNS_ID ); ?></p>
<?php
	bb_option_form_element( 'swirl_page', array(
		'title' => __( 'Swirl page', SWIRL_UNKNOWNS_ID ),
		'note' => array(
			sprintf(
				__( 'If no address is entered, <abbr style="border-bottom: 1px dashed; cursor: help;" title="%s">default swirl page</abbr> will be used.', SWIRL_UNKNOWNS_ID ),
				$swirl_unknowns_settings->default_swirl_page
			),
			__( 'There are some <strong>percent-substitution tags</strong> available to you for use.', SWIRL_UNKNOWNS_ID )
		)
	) );
?>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Additional Information', SWIRL_UNKNOWNS_ID ); ?></legend>
		<div>
			<div class="label"><?php _e( 'Percent-substitution tags', SWIRL_UNKNOWNS_ID ); ?></div>
			<div class="inputs"><ul>
<?php
	foreach ( $swirl_unknowns_settings->tags as $tag => $value )
		printf(
			'<li><strong>%1$s</strong> : <code>%2$s</code></li>',
			$tag,
			$value
		);
?>
			</ul></div>
		</div>
		<div>
			<div class="label"><?php _e( 'Swirl-immune pages', SWIRL_UNKNOWNS_ID ); ?></div>
			<div class="inputs"><ul>
<?php
	foreach ( $swirl_unknowns_settings->immunes as $immune )
		printf(
			'<li><code>%s</code></li>',
			$immune
		);
?>
			</ul></div>
		</div>
	</fieldset>
	<fieldset>
		<div id="option-swirl-unknowns-uninstall">
			<div class="label"><?php printf( __( 'Uninstall %s', SWIRL_UNKNOWNS_ID ), SWIRL_UNKNOWNS_NAME ); ?></div>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="swirl_unknowns_uninstall" id="swirl-unknowns-uninstall-0" value="1" /> <?php printf( __( 'Remove %s data from database.', SWIRL_UNKNOWNS_ID ), SWIRL_UNKNOWNS_NAME ); ?></label>
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'options-swirl-unknowns-update' ); ?>
		<input type="hidden" name="action" value="update-swirl-unknowns-settings" />
		<input type="submit" name="submit" class="submit" value="<?php _e( 'Save Changes', SWIRL_UNKNOWNS_ID ); ?>" />
	</fieldset>
</form>
<?php
}

/**
 * Process plugin configuration page
 *
 * @global $swirl_unknowns_settings
 * @global $bb_admin_body_class
 *
 * @uses bb_check_admin_referer()
 * @uses wp_get_referer()
 * @uses remove_query_arg()
 * @uses bb_delete_option()
 * @uses add_query_arg()
 * @uses bb_safe_redirect()
 * @uses bb_update_option()
 * @uses bb_admin_notice()
 *
 * @return void
 */
function swirl_unknowns_configuration_page_process() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && !empty( $_POST['action'] ) && 'update-swirl-unknowns-settings' == $_POST['action'] ) {
		bb_check_admin_referer( 'options-swirl-unknowns-update' );

		$goback = remove_query_arg( array( 'swirl-unknowns-updated', 'swirl-unknowns-uninstalled' ), wp_get_referer() );

		if ( empty( $_POST['swirl_unknowns_uninstall'] ) )
			$_POST['swirl_unknowns_uninstall'] = false;

		if ( (bool) $_POST['swirl_unknowns_uninstall'] ) { // Remove plugin data from database
			bb_delete_option( 'swirl_page' );

			$goback = add_query_arg( 'swirl-unknowns-uninstalled', 'true', $goback );
			bb_safe_redirect( $goback );
			exit;
		}

		$swirl_page = trim( $_POST['swirl_page'] );

		if ( !empty( $swirl_page ) ) {
			bb_update_option( 'swirl_page', $swirl_page );
		} else {
			global $swirl_unknowns_settings;

			bb_update_option( 'swirl_page', $swirl_unknowns_settings->default_swirl_page );
		}

		$goback = add_query_arg( 'swirl-unknowns-updated', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['swirl-unknowns-updated'] ) )
		bb_admin_notice( __( '<strong>Settings saved.</strong>', SWIRL_UNKNOWNS_ID ) );

	if ( !empty( $_GET['swirl-unknowns-uninstalled'] ) )
		bb_admin_notice( sprintf( __( '<strong>%s data removed.</strong>', SWIRL_UNKNOWNS_ID ), SWIRL_UNKNOWNS_NAME ) );

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}
?>