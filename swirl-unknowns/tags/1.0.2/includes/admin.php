<?php
/**
 * @package Swirl Unknowns
 */

/**
 * Add plugin actions
 */
add_action( 'bb_admin_menu_generator', 'swirl_unknowns_add_config_page' );

if ( 'swirl_unknowns_config_page' == $_GET['plugin'] ) { // Add plugin configuration page headers if on plugin configuration page
	add_action( 'bb_admin_head',       'swirl_unknowns_add_config_page_css' );
	add_action( 'bb_admin-header.php', 'swirl_unknowns_process_config_page' );
}

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
function swirl_unknowns_add_config_page() {
	bb_admin_add_submenu( SWIRL_UNKNOWNS_NAME, 'use_keys', 'swirl_unknowns_config_page' );
}

/**
 * Add plugin configuration page CSS
 *
 * @uses bb_get_plugin_uri()
 * @uses bb_plugin_basename()
 *
 * @return void
 */
function swirl_unknowns_add_config_page_css() {
?>
<link rel="stylesheet" href="<?php echo bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) . SWIRL_UNKNOWNS_ID; ?>.css" type="text/css" />
<?php
}

/**
 * Display plugin configuration page
 *
 * @uses $notices
 * @uses $immunes
 * @uses $tags
 * @uses $default_swirl_page
 * @uses bb_admin_notice()
 * @uses bb_get_option()
 *
 * @return void
 */
function swirl_unknowns_config_page() {
	global $notices;
	global $immunes;
	global $tags;
	global $default_swirl_page;

	// Retrieve saved option from db
	$swirl_page = (string) bb_get_option( 'swirl_page' );

	if ( count( $notices ) ) // Display notices, if any
		foreach ( $notices as $notice )
			bb_admin_notice( $notice[0], $notice[1] );
?>
<div id="<?php echo SWIRL_UNKNOWNS_ID; ?>-container">
<h2><?php echo SWIRL_UNKNOWNS_NAME; ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form method="post" id="<?php echo SWIRL_UNKNOWNS_ID; ?>-form" class="settings">
<fieldset>
	<div id="option-swirl-page">
		<label for="swirl-page"><?php _e( 'Swirl page', SWIRL_UNKNOWNS_ID ); ?></label>
		<div class="inputs">
			<input type="text" name="swirl-page" id="swirl-page" class="text" value="<?php echo $swirl_page; ?>" />
<?php
	printf( __( '<p>If no address is entered, <abbr title="%s">default swirl page</abbr> will be used.</p>', SWIRL_UNKNOWNS_ID ), $default_swirl_page );
	_e( '<p>There are some <strong>percent-substitution tags</strong> available to you for use.</p>', SWIRL_UNKNOWNS_ID );
?>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend><?php _e( 'Additional Information', SWIRL_UNKNOWNS_ID ); ?></legend>
	<div>
		<div class="label">
			<?php _e( 'Percent-substitution tags', SWIRL_UNKNOWNS_ID ); ?>
		</div>
		<div class="inputs">
<?php
	// List percent-substitution tags
	foreach ( $tags as $tag )
		printf( '<label class="checkboxs"><span class="tag-code">%1$s</span> : <code>%2$s</code></label>', $tag['code'], $tag['value'] );
?>
		</div>
	</div>
	<div>
		<div class="label">
			<?php _e( 'Swirl-immune pages', SWIRL_UNKNOWNS_ID ); ?>
		</div>
		<div class="inputs">
<?php
	// List swirl-immune pages
	foreach ( $immunes as $immune )
		printf( '<label class="checkboxs"><code>%s</code></label>', $immune );
?>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<input type="hidden" name="referrer" id="referrer" value="<?php echo SWIRL_UNKNOWNS_ID; ?>" />
	<input type="submit" name="submit" id="submit" class="submit" value="<?php _e( 'Save Changes', SWIRL_UNKNOWNS_ID ); ?>" />
</fieldset>
</form>
</div>
<?php
}

/**
 * Process plugin configuration page data
 *
 * @uses $notices
 * @uses $default_swirl_page
 * @uses bb_update_option()
 *
 * @return void
 */
function swirl_unknowns_process_config_page() {
	if ( SWIRL_UNKNOWNS_ID == $_POST['referrer'] ) {
		global $notices;

		if ( $_POST['swirl-page'] ) { // Process submitted data, if any
			bb_update_option( 'swirl_page', $_POST['swirl-page'] );
		} else { // Use default swirl page if no data is submitted
			global $default_swirl_page;

			bb_update_option( 'swirl_page', $default_swirl_page );
		}

		// Generate notice
		$notices[] = array( sprintf( __( '<strong>%s settings updated.</strong>', SWIRL_UNKNOWNS_ID ), SWIRL_UNKNOWNS_NAME ), '' );
	}
}

/**
 * Remove plugin traces
 *
 * @uses bb_delete_option()
 *
 * @return void
 */
function swirl_unknowns_deactivate() {
	// Remove saved options from the db
	bb_delete_option( 'swirl_page' );
}
?>