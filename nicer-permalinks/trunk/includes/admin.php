<?php
/**
 * @package Nicer Permalinks
 */


/**
 * Add plugin actions
 */
add_action( 'bb_admin_menu_generator', 'nicer_permalinks_configuration_page_add' );

if ( isset( $_GET['plugin'] ) && 'nicer_permalinks_configuration_page' == $_GET['plugin'] ) // Add plugin configuration page head if on plugin configuration page
	add_action( 'nicer_permalinks_configuration_page_pre_head', 'nicer_permalinks_configuration_page_process' );


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
function nicer_permalinks_configuration_page_add() {
	bb_admin_add_submenu( NICER_PERMALINKS_NAME, 'use_keys', 'nicer_permalinks_configuration_page' );
}

/**
 * Display plugin configuration page
 *
 * @uses do_action()
 * @uses bb_uri()
 * @uses bb_option_form_element()
 * @uses bb_get_uri()
 * @uses bb_nonce_field()
 *
 * @return void
 */
function nicer_permalinks_configuration_page() {
?>
<h2><?php printf( __( '%s Settings', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'nicer_permalinks_configuration_page' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<p><?php _e( 'Rewrite every bbPress URI removing the words "forum" and "topic" and emphasize forum hierarchy.', NICER_PERMALINKS_ID ); ?></p>
<?php
	bb_option_form_element( 'nicer_permalinks_enabled', array(
		'title' => sprintf( __( '%s status', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME ),
		'type' => 'radio',
		'options' => array(
			0 => __( 'Disabled', NICER_PERMALINKS_ID ),
			1 => __( 'Enabled', NICER_PERMALINKS_ID )
		)
	) );
?>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Additional Information', NICER_PERMALINKS_ID ); ?></legend>
		<div>
			<div class="label"><?php _e( 'Prerequisites', NICER_PERMALINKS_ID ); ?></div>
			<div class="inputs"><ul>
				<li>
					<?php _e( 'PHP version 5 or higher', NICER_PERMALINKS_ID ); ?>
					<p><?php printf( __( 'Your Webserver is running PHP version <strong>%s</strong>.', NICER_PERMALINKS_ID ), PHP_VERSION ); ?></p>
				</li>
				<li>
					<?php printf( __( '<a href="%s">Name based permalinks</a> enabled', NICER_PERMALINKS_ID ), bb_get_uri( 'bb-admin/options-permalinks.php' ) ); ?>
					<p><?php printf( __( 'Do not change permalink settings while %1$s is enabled, or you will mess up your <code>%2$s</code>.', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME, '.htaccess' ); ?></p>
				</li>
				<li>
					<?php printf( __( 'Writable <code>%s</code>', NICER_PERMALINKS_ID ), '.htaccess' ); ?>
					<p><?php printf( __( 'You should restore its orginal permissions (usually read-only) once activated|deactivated %s.', NICER_PERMALINKS_ID ), NICER_PERMALINKS_NAME ); ?></p>
				</li>
				<li>
					<?php printf( __( 'A writable (empty) file named <code>%1$s</code> in <abbr style="border-bottom: 1px dashed; cursor: help;" title="%2$s">bbPress root folder</abbr>, to store <code>%3$s</code> backup', NICER_PERMALINKS_ID ), 'htaccess.bak', BB_PATH, '.htaccess' ); ?>
				</li>
			</ul></div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'options-nicer-permalinks-update' ); ?>
		<input type="hidden" name="action" value="update-nicer-permalinks-settings" />
		<input type="submit" name="submit" class="submit" value="<?php _e( 'Save Changes', NICER_PERMALINKS_ID ); ?>" />
	</fieldset>
</form>
<?php
}

/**
 * Process plugin configuration page
 *
 * @global $bb_admin_body_class
 *
 * @uses bb_check_admin_referer()
 * @uses wp_get_referer()
 * @uses remove_query_arg()
 * @uses add_query_arg()
 * @uses name_based_permalinks_enabled()
 * @uses bb_safe_redirect()
 * @uses backup_htaccess()
 * @uses update_htaccess()
 * @uses bb_update_option()
 * @uses restore_htaccess()
 * @uses bb_delete_option()
 * @uses bb_admin_notice()
 * @uses bb_get_uri()
 *
 * @return void
 */
function nicer_permalinks_configuration_page_process() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-nicer-permalinks-settings' ) {
		bb_check_admin_referer( 'options-nicer-permalinks-update' );

		$goback = remove_query_arg( array( 'nicer-permalinks-updated', 'error-php', 'error-nbp', 'error-htw', 'error-htb', 'unknown-error' ), wp_get_referer() );

		$errors = 0; // Errors counter

		// Check plugin configuration
		if ( 1 == $value = $_POST['nicer_permalinks_enabled'] ) { // Check if plugin can be enabled
			if ( false === (bool) version_compare( '5.0.0', PHP_VERSION, '<' ) ) { // Notice error if PHP version if lower than 5
				$goback = add_query_arg( 'error-php', 'true', $goback );
				$errors++;
			}

			if ( false === (bool) name_based_permalinks_enabled() ) { // Notice error if name based permalinks are not enabled
				$goback = add_query_arg( 'error-nbp', 'true', $goback );
				$errors++;
			}

			if ( !is_writable( BB_PATH . '.htaccess' ) ) { // Notice error if .htaccess is not writable
				$goback = add_query_arg( 'error-htw', 'true', $goback );
				$errors++;
			}

			if ( !file_exists( BB_PATH . 'htaccess.bak' ) ) { // Notice error if there is no htaccess.bak in bbPress root folder
				$goback = add_query_arg( 'error-htb', 'true', $goback );
				$errors++;
			} elseif ( !is_writable( BB_PATH . 'htaccess.bak' ) ) { // Notice error if htaccess.bak is not writable
				$goback = add_query_arg( 'error-htb', 'true', $goback );
				$errors++;
			}
		} elseif ( 0 == $value ) { // Check if plugin can be disabled
			if ( !is_writable( BB_PATH . '.htaccess' ) ) { // Notice error if .htaccess is not writable
				$goback = add_query_arg( 'error-htw', 'true', $goback );
				$errors++;
			}

			if ( !file_exists( BB_PATH . 'htaccess.bak' ) ) { // Notice error if there is no htaccess.bak in bbPress root folder
				$goback = add_query_arg( 'error-htb', 'true', $goback );
				$errors++;
			}
		} else { // Should never happen
			$goback = add_query_arg( 'unknown-error', 'true', $goback );
			bb_safe_redirect( $goback );
			exit;
		}

		if ( 0 < $errors ) { // Report all errors
			bb_safe_redirect( $goback );
			exit;
		}

		// Process request
		if ( 1 == $value ) { // Try to enable the plugin
			if ( false === (bool) backup_htaccess() ) { // Notice error if there is no htaccess.bak in bbPress root folder or it is not writable
				$goback = add_query_arg( 'error-htb', 'true', $goback );
				bb_safe_redirect( $goback );
				exit;
			}

			if ( false === (bool) update_htaccess() ) { // Notice error if .htaccess is not writable
				$goback = add_query_arg( 'error-htw', 'true', $goback );
				bb_safe_redirect( $goback );
				exit;
			}

			bb_update_option( 'nicer_permalinks_enabled', $value );

			$goback = add_query_arg( 'nicer-permalinks-updated', 'true', $goback );
		} elseif ( 0 == $value ) { // Try to disable the plugin
			if ( false === (bool) restore_htaccess() ) { // Notice error if .htaccess is not writable
				$goback = add_query_arg( 'error-htw', 'true', $goback );
				bb_safe_redirect( $goback );
				exit;
			}

			bb_delete_option( 'nicer_permalinks_enabled' );

			$goback = add_query_arg( 'nicer-permalinks-updated', 'true', $goback );
		} else { // Should never happen
			$goback = add_query_arg( 'unknown-error', 'true', $goback );
		}

		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['nicer-permalinks-updated'] ) )
		bb_admin_notice( __( '<strong>Settings saved.</strong>', NICER_PERMALINKS_ID ) );

	if ( !empty( $_GET['error-php'] ) )
		bb_admin_notice(
			sprintf(
				__( 'Your Webserver is running PHP version %1$s, which is <a href="%2$s">no longer receiving security updates</a>. Please contact your host and have them upgrade PHP to its latest stable branch as soon as possible.', NICER_PERMALINKS_ID ),
				PHP_VERSION,
				'http://www.php.net/archive/2007.php#2007-07-13-1'
			),
			'error'
		);

	if ( !empty( $_GET['error-nbp'] ) )
		bb_admin_notice(
			sprintf(
				__( '<a href="%s">Name based permalinks</a> are not enabled.', NICER_PERMALINKS_ID ),
				bb_get_uri( 'bb-admin/options-permalinks.php' )
			),
			'error'
		);

	if ( !empty( $_GET['error-htw'] ) )
		bb_admin_notice(
			sprintf(
				__( '<code>%s</code> is not writable.', NICER_PERMALINKS_ID ),
				'.htaccess'
			),
			'error'
		);

	if ( !empty( $_GET['error-htb'] ) )
		bb_admin_notice(
			sprintf( __( 'There is no <code>%1$s</code> in <abbr style="border-bottom: 1px dashed; cursor: help;" title="%2$s">bbPress root folder</abbr> or it is not writable.', NICER_PERMALINKS_ID ),
				'htaccess.bak',
				BB_PATH
			),
			'error'
		);

	if ( !empty( $_GET['unknown-error'] ) ) // Should never happen
		bb_admin_notice(
			__( 'An unknown processing error occurred.', NICER_PERMALINKS_ID ),
			'error'
		);

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
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

	return ( 'slugs' == $mod_rewrite );
}

/**
 * Backup .htaccess contents in htaccess.bak
 *
 * @return int|boolean Written bytes|false (error)
 */
function backup_htaccess() {
	// Load .htaccess contents and save them in htaccess.bak
	$htaccess_contents = file_get_contents( BB_PATH . '.htaccess' );

	return file_put_contents( BB_PATH . 'htaccess.bak', $htaccess_contents );
}

/**
 * Update .htaccess contents with newly generated rules
 *
 * @uses get_nicer_htaccess_contents()
 *
 * @return int|boolean Written bytes|false (error)
 */
function update_htaccess() {
	// Get file contents and store them in an array for further processing
	$htaccess_rules = explode( "\n", implode( '', file(  BB_PATH . '.htaccess' ) ) );

	// Replace existing bbPress rules with newly generated rules
	$keep_rule = true;
	$kept_rules = array();

	foreach ( $htaccess_rules as $rule ) {
		if ( false !== strpos( $rule, '# BEGIN bbPress' ) ) {
			$keep_rule = false;
			continue;
		} elseif ( false !== strpos( $rule, '# END bbPress' ) ) {
			$keep_rule = true;
			continue;
		}

		if ( $keep_rule )
			$kept_rules[] = $rule;
	}

	// Newly generated rules are prepended to kept rules
	$nicer_contents = get_nicer_htaccess_contents() . "\n" . join( "\n", $kept_rules );

	return file_put_contents( BB_PATH . '.htaccess', $nicer_contents );
}

/**
 * Restore .htaccess contents from its backup at htaccess.bak
 *
 * @return int|boolean Written bytes|false (error)
 */
function restore_htaccess() {
	// Load htaccess.bak contents and save them in .htaccess
	$htaccess_bak_contents = file_get_contents( BB_PATH . 'htaccess.bak' );

	return file_put_contents( BB_PATH . '.htaccess', $htaccess_bak_contents );
}

/**
 * Generate nicer .htaccess contents
 *
 * @uses bb_get_option()
 *
 * @return string Nicer .htaccess contents
 */
function get_nicer_htaccess_contents() {
	$nicer_htaccess_contents = <<<EOF

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

	RewriteRule ^rss/forum/([^/]+)/topics/?$ rss.php?forum=$1&topics=1 [L,QSA]
	RewriteRule ^rss/forum/([^/]+)/?$ rss.php?forum=$1 [L,QSA]
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

	// Process percent-substitution tag and return nicer .htaccess contents
	return str_replace( '%PATH%', bb_get_option( 'path' ), $nicer_htaccess_contents );
}

/**
 * Restore .htaccess contents from its backup at htaccess.bak and remove plugin data from database
 *
 * Note: this function is automatically skipped if plugin is already disabled.
 *
 * @uses nicer_permalinks_enabled()
 * @uses bb_get_uri()
 * @uses bb_die()
 * @uses restore_htaccess()
 * @uses bb_delete_option()
 *
 * @return void
 */
function nicer_permalinks_uninstall() {
	if ( false === (bool) nicer_permalinks_enabled() ) // Plugin is already disabled
		return;

	// Check plugin configuration
	if ( !is_writable( BB_PATH . '.htaccess' ) || !file_exists( BB_PATH . 'htaccess.bak' ) ) { // Die if .htaccess is not writable or there is no htaccess.bak in bbPress root folder
		bb_die( sprintf(
			__( 'Cannot deactivate "%1$s". See <a href="%2$s">"%1$s" configuration page</a> for information on how to proceed.', NICER_PERMALINKS_ID ),
			NICER_PERMALINKS_NAME,
			bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'nicer_permalinks_configuration_page' ), BB_URI_CONTEXT_BB_ADMIN )
		) );
		exit;
	}

	// Try to restore .htaccess
	if ( false === (bool) restore_htaccess() ) { // Die if .htaccess is not writable or there is no htaccess.bak in bbPress root folder
		bb_die( sprintf(
			__( 'Could not deactivate "%1$s". See <a href="%2$s">"%1$s" configuration page</a> for error details.', NICER_PERMALINKS_ID ),
			NICER_PERMALINKS_NAME,
			bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'nicer_permalinks_configuration_page' ), BB_URI_CONTEXT_BB_ADMIN )
		) );
		exit;
	}

	// Remove plugin data from database
	bb_delete_option( 'nicer_permalinks_enabled' );
}
?>