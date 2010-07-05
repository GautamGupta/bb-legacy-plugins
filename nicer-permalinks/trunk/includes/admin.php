<?php
/**
 * @package Nicer Permalinks
 */

/**
 * Add plugin actions
 */
add_action( 'bb_admin_menu_generator', 'nicer_permalinks_add_config_page' );

if ( 'nicer_permalinks_config_page' == $_GET['plugin'] ) { // Add plugin configuration page headers if on plugin configuration page
	add_action( 'bb_admin_head',       'nicer_permalinks_add_config_page_css' );
	add_action( 'bb_admin-header.php', 'nicer_permalinks_process_config_page' );
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
function nicer_permalinks_add_config_page() {
	bb_admin_add_submenu( NICER_PERMALINKS_NAME, 'use_keys', 'nicer_permalinks_config_page' );
}

/**
 * Add plugin configuration page CSS
 *
 * @uses bb_get_plugin_uri()
 * @uses bb_plugin_basename()
 *
 * @return void
 */
function nicer_permalinks_add_config_page_css() {
?>
<link rel="stylesheet" href="<?php echo bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) . NICER_PERMALINKS_ID; ?>.css" type="text/css" />
<?php
}

/**
 * Display plugin configuration page
 *
 * @uses $notices
 * @uses bb_admin_notice()
 * @uses nicer_permalinks_enabled()
 * @uses bb_get_uri()
 *
 * @return void
 */
function nicer_permalinks_config_page() {
	global $notices;

	if ( count( $notices ) ) // Display notices, if any
		foreach ( $notices as $notice )
			bb_admin_notice( $notice[0], $notice[1] );

	// Retrieve plugin status
	$status = nicer_permalinks_enabled();
?>
<div id="<?php echo NICER_PERMALINKS_ID; ?>-container">
<h2><?php echo NICER_PERMALINKS_NAME; ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form method="post" id="<?php echo NICER_PERMALINKS_ID; ?>-form" class="settings">
<fieldset>
	<div>
		<div class="label">
			<?php echo NICER_PERMALINKS_NAME; ?>
		</div>
		<div class="inputs">
			<label class="checkboxs">
				<input type="checkbox" name="enable" id="enable" class="checkbox"<?php echo ( $status ) ? ' checked="checked"' : ''; ?> />
				<?php _e( 'Remove the words "forum" and "topic" from every bbPress URI and emphasize hierarchy.', NICER_PERMALINKS_ID ); ?>
			</label>
			<p><?php printf( __( 'Nicer forum URI: <code>%s</code>', NICER_PERMALINKS_ID ), bb_get_uri( 'first-forum/' ) ); ?></p>
			<p><?php printf( __( 'Nicer topic URI: <code>%s</code>', NICER_PERMALINKS_ID ), bb_get_uri( 'first-forum/first-topic' ) ); ?></p>
		</div>
	</div>
</fieldset>
<fieldset>
	<legend><?php _e( 'Additional Information', NICER_PERMALINKS_ID ); ?></legend>
	<div>
		<div class="label">
			<?php _e( 'Prerequisites', NICER_PERMALINKS_ID ); ?>
		</div>
		<div class="inputs">
			<label class="checkboxs"><?php _e( 'PHP version 5 or higher.', NICER_PERMALINKS_ID ); ?></label>
			<p><?php printf( __( 'Your Webserver is running PHP version <strong>%s</strong>.', NICER_PERMALINKS_ID ), PHP_VERSION ); ?></p>
			<label class="checkboxs"><?php printf( __( '<a href="%s">Name based permalinks</a>.', NICER_PERMALINKS_ID ), bb_get_uri( 'bb-admin/options-permalinks.php' ) ); ?></label>
			<p><?php printf( __( 'Do not change permalink settings while %1$s are enabled, or you will mess up your <code>%2$s</code>.', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME, '.htaccess' ); ?></p>
			<label class="checkboxs"><?php printf( __( 'Writable <code>%s</code>.', NICER_PERMALINKS_ID ), '.htaccess' ); ?></label>
			<p><?php _e( 'You should restore its orginal permissions (usually read-only) after plugin enabling/disabling.', NICER_PERMALINKS_ID ); ?></p>
			<label class="checkboxs"><?php printf( __( 'A writable (empty) file named <code>%1$s</code> in <abbr title="%2$s">bbPress root folder</abbr>, to store <code>%3$s</code> backup.', NICER_PERMALINKS_ID ), 'htaccess.bak', BB_PATH, '.htaccess' ); ?></label>
		</div>
	</div>
</fieldset>
<fieldset class="submit">
	<input type="hidden" name="referrer" id="referrer" value="<?php echo NICER_PERMALINKS_ID; ?>" />
	<input type="hidden" name="status" id="status" value="<?php echo $status; ?>" />
	<input type="submit" name="submit" id="submit" class="submit" value="<?php _e( 'Save Changes', NICER_PERMALINKS_ID ); ?>" />
</fieldset>
</form>
</div>
<?php
}

/**
 * Process plugin configuration page data
 *
 * @uses $notices
 * @uses nicer_permalinks_config_errors()
 * @uses restore_htaccess()
 * @uses backup_htaccess()
 * @uses bb_delete_option()
 * @uses bb_update_option()
 * @uses get_nicer_htaccess()
 *
 * @return void
 */
function nicer_permalinks_process_config_page() {
	if ( NICER_PERMALINKS_ID == $_POST['referrer'] ) {
		global $notices;

		$status = $_POST['status'];
		$requested_status = ( isset( $_POST['enable'] ) ) ? 1 : 0;

		if ( $requested_status != $status ) { // Process request if requested status does not match plugin current status
			if ( false !== $errors = nicer_permalinks_config_errors( $status ) ) { // Generate error notice and report plugin config errors, if any
				foreach ( $errors as $error )
					$notices[] = array( $error, 'error' );

				return;
			}

			if ( $requested_status ) { // Try to enable the plugin
				if ( false === backup_htaccess() ) { // Generate error notice and return if errors occurred
					$notices[] = array( sprintf( __( '<strong><code>%1$s</code> backup could not be generated: probably there is no <code>%2$s</code> in bbPress root folder or it is not writable.</strong>', NICER_PERMALINKS_ID ), '.htaccess', 'htaccess.bak' ), 'error' );

					return;
				}

				if ( false === file_put_contents( BB_PATH . '.htaccess', get_nicer_htaccess() ) ) { // Generate error notice and return if errors occurred
					$notices[] = array( sprintf( __( '<strong><code>%s</code> could not be updated: probably it is not writable.</strong>', NICER_PERMALINKS_ID ), '.htaccess' ), 'error' );

					return;
				}

				// Save plugin status in db
				bb_update_option( 'nicer_permalinks_enabled', 1 );
			} else { // Try to disable the plugin
				if ( false === restore_htaccess() ) { // Generate error notice and return if errors occurred
					$notices[] = array( sprintf( __( '<strong><code>%s</code> could not be restored: probably it is not writable.</strong>', NICER_PERMALINKS_ID ), '.htaccess' ), 'error' );

					return;
				}

				// Save plugin status in db
				bb_update_option( 'nicer_permalinks_enabled', 0 );
			}
		} else ; // No need to process request if requested status matches plugin current status

		// Generate notice
		$notices[] = array( sprintf( __( '<strong>%s settings updated.</strong>', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME ), '' );
	}
}

/**
 * Returns plugin configuration errors or false if no error is found
 *
 * @param int $status Plugin status
 *
 * @uses nicer_permalinks_enabled()
 * @uses php_version_check()
 * @uses name_based_permalinks_enabled()
 * @uses bb_get_uri()
 *
 * @return array|boolean Errors|false
 */
function nicer_permalinks_config_errors( $status ) {
	if ( is_null( $status ) ) // Retrieve plugin status if no param is passed
		$status = nicer_permalinks_enabled();

	$errors = array();

	if ( $status ) { // Check if plugin can be disabled
		if ( !is_writable( BB_PATH . '.htaccess' ) ) // Generate error if .htaccess is not writable
			$errors[] = sprintf( __( '<code>%s</code> is not writable.', NICER_PERMALINKS_ID ), '.htaccess' );
	} else { // Check if plugin can be enabled
		if ( version_compare( '5.0.0', PHP_VERSION, '>' ) ) // Generate error if PHP version if lower than 5
			$errors[] = sprintf( __( 'Your Webserver is running PHP version %1$s, which is <a href="%2$s">no longer receiving security updates</a>. Please contact your host and have them upgrade PHP to its latest stable branch as soon as possible.', NICER_PERMALINKS_ID ), PHP_VERSION, 'http://www.php.net/archive/2007.php#2007-07-13-1' );
		if ( !name_based_permalinks_enabled() ) // Generate error if name based permalinks are disabled
			$errors[] = sprintf( __( '<a href="%s">Name based permalinks</a> are not enabled.', NICER_PERMALINKS_ID ), bb_get_uri( 'bb-admin/options-permalinks.php' ) );

		if ( !is_writable( BB_PATH . '.htaccess' ) ) // Generate error if .htaccess is not writable
			$errors[] = sprintf( __( '<code>%s</code> is not writable.', NICER_PERMALINKS_ID ), '.htaccess' );

		if ( !file_exists( BB_PATH . 'htaccess.bak' ) ) // Generate error if there is no htaccess.bak in bbPress root folder
			$errors[] = sprintf( __( 'There is no <code>%s</code> in bbPress root folder.', NICER_PERMALINKS_ID ), 'htaccess.bak' );
		else
			if ( !is_writable( BB_PATH . 'htaccess.bak' ) ) // Generate error if htaccess.bak is not writable
				$errors[] = sprintf( __( '<code>%s</code> is not writable.', NICER_PERMALINKS_ID ), 'htaccess.bak' );
	}

	// Return array of errors or false if no error occurred
	return ( count( $errors ) ) ? $errors : false;
}

/**
 * Check if name based permalinks are enabled
 *
 * @uses bb_get_option()
 * 
 * @return boolean
 */
function name_based_permalinks_enabled() {
	$mod_rewrite = (string) bb_get_option( 'mod_rewrite' );

	return ( 'slugs' == $mod_rewrite ) ? true : false;
}

/**
 * Generate nicer .htaccess
 *
 * @uses bb_get_option()
 *
 * @return string
 */
function get_nicer_htaccess() {
	$nicer_htaccess = <<<EOF

# BEGIN bbPress

Options -MultiViews

<IfModule mod_rewrite.c>
	RewriteEngine On

	# %PATH% and bb_get_option( 'path' ) must match
	RewriteBase %PATH%

	Options +FollowSymlinks

	# admin stuff, not processed
	RewriteRule ^bb-admin/.*$ - [L,QSA]

	RewriteRule ^page/([0-9]+)/?$ ?page=$1 [L,QSA]

	RewriteRule ^profile/([^/]+)/([^/]+)/page/([0-9]+)/?$ profile.php?id=$1&tab=$2&page=$3 [L,QSA]
	RewriteRule ^profile/([^/]+)/page/([0-9]+)/?$ profile.php?id=$1&page=$2 [L,QSA]
	RewriteRule ^profile/([^/]+)/([^/]+)$ profile.php?id=$1&tab=$2 [L,QSA]
	RewriteRule ^profile/([^/]+)$ profile.php?id=$1 [L,QSA]
	RewriteRule ^profile/$ profile.php [L,QSA]

	RewriteRule ^rss%PATH%([^/]+)/topics/?$ rss.php?forum=$1&topics=1 [L,QSA]
	RewriteRule ^rss%PATH%([^/]+)/?$ rss.php?forum=$1 [L,QSA]
	RewriteRule ^rss/profile/([^/]+)$ rss.php?profile=$1 [L,QSA]
	RewriteRule ^rss/tags/([^/]+)/topics/?$ rss.php?tag=$1&topics=1 [L,QSA]
	RewriteRule ^rss/tags/([^/]+)$ rss.php?tag=$1 [L,QSA]
	RewriteRule ^rss/topic/([^/]+)$ rss.php?topic=$1 [L,QSA]
	RewriteRule ^rss/view/([^/]+)$ rss.php?view=$1 [L,QSA]
	RewriteRule ^rss/topics/?$ rss.php?topics=1 [L,QSA]
	RewriteRule ^rss/?$ rss.php [L,QSA]

	RewriteRule ^tags/([^/]+)/page/([0-9]+)/?$ tags.php?tag=$1&page=$2 [L,QSA]
	RewriteRule ^tags/([^/]+)$ tags.php?tag=$1 [L,QSA]
	RewriteRule ^tags/?$ tags.php [L,QSA]

	RewriteRule ^view/([^/]+)/page/([0-9]+)/?$ view.php?view=$1&page=$2 [L,QSA]
	RewriteRule ^view/([^/]+)$ view.php?view=$1 [L,QSA]

	# forums
	RewriteRule ^([^/]+)/page/([0-9]+)/?$ forum.php?id=$1&page=$2 [L,QSA]
	RewriteRule ^([^/]+)/$ forum.php?id=$1 [L,QSA] # tailed '/' is mandatory for forum URIs! Props: Mohta

	# topics
	RewriteRule ^([^/]+)/([^/]+)/page/([0-9]+)/?$ topic.php?id=$2&page=$3 [L,QSA]
	RewriteRule ^([^/]+)/([^/]+)$ topic.php?id=$2 [L,QSA]

	# other pages
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^.*$ index.php [L]
</IfModule>

# END bbPress
EOF;

	// Process percent-substitution tag before returning nicer .htaccess
	return str_replace( '%PATH%', bb_get_option( 'path' ), $nicer_htaccess );
}

/**
 * Backup .htaccess
 *
 * @return int|boolean Written bytes|false (error)
 */
function backup_htaccess() {
	// Files paths
	$htaccess_path = BB_PATH . '.htaccess';
	$htaccess_bak_path = BB_PATH . 'htaccess.bak';

	// Load .htaccess content and save it in htaccess.bak
	$htaccess_content = file_get_contents( $htaccess_path );

	return file_put_contents( $htaccess_bak_path, $htaccess_content );
}

/**
 * Restore .htaccess
 *
 * @return int|boolean Written bytes|false (error)
 */
function restore_htaccess() {
	// Files paths
	$htaccess_path = BB_PATH . '.htaccess';
	$htaccess_bak_path = BB_PATH . 'htaccess.bak';

	// Load htaccess.bak content and save it in .htaccess
	$htaccess_bak_content = file_get_contents( $htaccess_bak_path );

	return file_put_contents( $htaccess_path, $htaccess_bak_content );
}

/**
 * Remove plugin traces
 *
 * Note: this function is automatically skipped if plugin is off.
 *
 * @uses nicer_permalinks_enabled()
 * @uses nicer_permalinks_config_errors()
 * @uses bb_die()
 * @uses bb_get_uri()
 * @uses restore_htaccess()
 * @uses bb_delete_option()
 *
 * @return void
 */
function nicer_permalinks_deactivate() {
	if ( !$status = nicer_permalinks_enabled() ) // Return if plugin is not enabled
		return;

	if ( false !== nicer_permalinks_config_errors( $status ) ) { // Die if plugin config is not correct
		bb_die( sprintf(
			__( 'Cannot deactivate "%1$s". See <a href="%2$s">"%1$s" configuration page</a> for more info.', NICER_PERMALINKS_ID ),
			NICER_PERMALINKS_NAME,
			bb_get_uri( 'bb-admin/admin-base.php?plugin=nicer_permalinks_config_page' )
		) );
		exit();
	}

	// Try to restore .htaccess
	if ( false === restore_htaccess() ) { // Die if errors occurred
		bb_die( sprintf(
			__( 'Cannot deactivate "%1$s". See <a href="%2$s">"%1$s" configuration page</a> for more info.', NICER_PERMALINKS_ID ),
			NICER_PERMALINKS_NAME,
			bb_get_uri( 'bb-admin/admin-base.php?plugin=nicer_permalinks_config_page' )
		) );
		exit();
	}

	// Remove plugin status from db
	bb_delete_option( 'nicer_permalinks_enabled' );
}
?>