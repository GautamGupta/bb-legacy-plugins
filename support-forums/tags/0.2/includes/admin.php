<?php
/**
 * @package Support Forums
 */


/**
 * Add plugin actions
 */
add_action( 'bb_admin_menu_generator', 'support_forums_configuration_page_add' );

if ( isset( $_GET['plugin'] ) && 'support_forums_configuration_page' == $_GET['plugin'] ) { // Add plugin configuration page head if on plugin configuration page
	add_action( 'support_forums_configuration_page_pre_head', 'support_forums_configuration_page_process' );
	add_action( 'bb_admin-header.php',                        'support_forums_configuration_page_head' );
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
function support_forums_configuration_page_add() {
	bb_admin_add_submenu( SUPPORT_FORUMS_NAME, 'use_keys', 'support_forums_configuration_page' );
}

/**
 * Add plugin configuration page head
 *
 * @uses bb_plugin_basename()
 * @uses bb_get_plugin_uri()
 * @uses wp_enqueue_script()
 *
 * @return void
 */
function support_forums_configuration_page_head() {
	wp_enqueue_script( SUPPORT_FORUMS_ID . '-configuration-page', bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) ) . SUPPORT_FORUMS_ID . '-configuration-page.js', NULL, NULL );
}

/**
 * Display plugin configuration page
 *
 * @global $support_forums_settings
 * @uses do_action()
 * @uses bb_uri()
 * @uses bb_get_forums()
 * @uses get_forum_link()
 * @uses bb_option_form_element()
 * @uses bb_nonce_field()
 *
 * @return void
 */
function support_forums_configuration_page() {
	global $support_forums_settings;
?>
<h2><?php printf( __( '%s Settings', SUPPORT_FORUMS_ID ), SUPPORT_FORUMS_NAME ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>
<form class="settings" method="post" action="<?php bb_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'support_forums_configuration_page' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>">
	<fieldset>
		<p><?php _e( 'Turn any number of forums into support forums, where users can mark topics as resolved, not resolved or not a support question.', SUPPORT_FORUMS_ID ); ?></p>
		<table class="widefat" id="topics-list">
		<thead>
		<tr>
			<th scope="col"><?php _e( 'Unique ID', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Name', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Description', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Action', SUPPORT_FORUMS_ID ); ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th scope="col"><?php _e( 'Unique ID', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Name', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Description', SUPPORT_FORUMS_ID ); ?></th>
			<th scope="col"><?php _e( 'Action', SUPPORT_FORUMS_ID ); ?></th>
		</tr>
		</tfoot>
		<tbody>
<?php
	// Get all forums
	$forums = bb_get_forums();
	
	foreach ( $forums as $forum ) :
		$forum_link = get_forum_link( $forum->forum_id );
		$forum_name = sprintf(
			'<a href="%1$s">%2$s</a>',
			$forum_link,
			$forum->forum_name
		);
		$checked = '';
		$class = '';

		if ( true === (bool) $support_forums_settings->isEnabled() && in_array( $forum->forum_id, $support_forums_settings->forums ) ) { // Plugin is enabled and forum is a support forum
			$checked = ' checked="checked"';
			$class = ' class="alt"';
		}

		$checkbox = sprintf(
			'<label for="%1$s"><input type="checkbox" class="checkbox" name="%2$s" id="%1$s" value="%3$s"%4$s /> %5$s</label>',
			'support-forum-' . $forum->forum_id,
			'support_forum_' . $forum->forum_id,
			$forum->forum_id,
			$checked,
			__( 'Turn into support forum', SUPPORT_FORUMS_ID )
		);
?>
		<tr<?php echo $class; ?>>
			<td><?php echo $forum->forum_id; ?></td>
			<td class="topic">
				<span class="row-title"><?php echo $forum_name; ?></span>
				<div style="margin-right: 0; padding: 0;">
				<span class="row-actions">
					<a href="<?php echo $forum_link; ?>"><?php _e( 'View' ); ?></a>
				</span>&nbsp;
				</div>
			</td>
			<td><?php echo $forum->forum_desc; ?></td>
			<td><?php echo $checkbox; ?></td>
		</tr>
<?php endforeach; ?>
		</tbody>
		</table>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Support Topics Settings' , SUPPORT_FORUMS_ID ); ?></legend>
<?php
	bb_option_form_element( 'support_forums_settings[default_status]', array(
		'title' => __( 'Default support status', SUPPORT_FORUMS_ID ),
		'type' => 'radio',
		'options' => $support_forums_settings->statuses
	) );
	bb_option_form_element( 'support_forums_settings[poster_setable]', array(
		'title' => __( 'Poster setable', SUPPORT_FORUMS_ID ),
		'type' => 'checkbox',
		'options' => array(
			1 => __( 'Users can set topic support status on creation.', SUPPORT_FORUMS_ID )
		)
	) );
	bb_option_form_element( 'support_forums_settings[poster_changeable]', array(
		'title' => __( 'Poster changeable', SUPPORT_FORUMS_ID ),
		'type' => 'checkbox',
		'options' => array(
			1 => __( 'Support topic poster can change its status at anytime.', SUPPORT_FORUMS_ID )
		)
	) );
?>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Support Views', SUPPORT_FORUMS_ID ); ?></legend>
		<p><?php _e( 'You may create a view for each of the support statuses.', SUPPORT_FORUMS_ID ); ?></p>
<?php
	$views_enabled = (bool) $support_forums_settings->areViewsEnabled();

	foreach ( $support_forums_settings->statuses as $status => $display ) {
		$checked = ( $views_enabled && in_array( $status, $support_forums_settings->views ) ) ? ' checked="checked"' : '';
	
		printf(
			'<div id="option-support-forums-settings-views-%1$s">%2$s',
			str_replace( '_', '-', $status ),
			"\n"
		);
		printf(
			'<div class="label">%1$s</div>%2$s',
			ucfirst( $display ), // ucfirst() capitalizes string first char
			"\n"
		);
		echo '<div class="inputs">' . "\n";
		echo '<label class="checkboxs">' . "\n";
		printf(
			'<input type="checkbox" value="1" id="support-forums-settings-views-%1$s-0" name="support_forums_settings[views][%2$s]" class="checkbox"%3$s /> %4$s %5$s.',
			str_replace( '_', '-', $status ),
			$status,
			$checked,
			__( 'Support topics that are', SUPPORT_FORUMS_ID ),
			$display
		);
		echo '</label>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}
?>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Support Icons', SUPPORT_FORUMS_ID ); ?></legend>
		<p><?php _e( 'You may add icons to support, closed or sticky topics.', SUPPORT_FORUMS_ID ); ?></p>
		<div id="option-support-forums-settings-icons-dir">
			<label for="support-forums-settings-icons-dir"><?php _e( 'Icons directory', SUPPORT_FORUMS_ID );  ?></label>
			<div class="inputs">
<?php
	$icons_enabled = (bool) $support_forums_settings->areIconsEnabled();

	printf(
		'<input type="text" value="%1$s" id="support-forums-settings-icons-dir" name="support_forums_settings[icons][dir]" class="text" />%2$s',
		( $icons_enabled && array_key_exists( 'dir', $support_forums_settings->icons ) ) ?
			$support_forums_settings->icons['dir'] :
			'',
		"\n"
	);
?>
				<p><?php printf( 'Example: <code>%s</code>.', 'my-support-icons/' ); ?></p>
				<p><?php _e( 'Your icons directory must be a subfolder of your active template <code>images/</code> directory.' ); ?></p>
				<p><?php _e( 'Your icons names must match default icons&#8217;.' ); ?></p>
				<p><?php _e( 'Leave empty to use default icons.' ); ?></p>
			</div>
		</div>
		<div id="option-support-forums-settings-icons-status">
			<div class="label"><?php _e( 'Support topics icons', SUPPORT_FORUMS_ID ); ?></div>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" value="status" id="support-forums-settings-icons-status-0" name="support_forums_settings[icons][status]" class="checkbox"<?php echo ( $icons_enabled && in_array( 'status', $support_forums_settings->icons ) ) ? ' checked="checked"' : ''; ?> /> <?php _e( 'Add icons to support topics.', SUPPORT_FORUMS_ID ); ?></label>
<?php
	$icons_uri = ( $icons_enabled && array_key_exists( 'dir', $support_forums_settings->icons ) ) ? SUPPORT_FORUMS_ACTIVE_TEMPLATE_IMAGES_URI . $support_forums_settings->icons['dir'] : SUPPORT_FORUMS_ICONS_URI;

	foreach ( $support_forums_settings->statuses as $status => $display )
		printf(
			'<p><img src="%1$s-%2$s.png" alt="" title="%3$s" style="vertical-align: middle;" /> - %3$s</p>',
			$icons_uri . SUPPORT_FORUMS_ID,
			str_replace( '_', '-', $status ),
			$display
		);
?>
			</div>
		</div>
<?php
	foreach ( array( 'closed' => __( 'closed', SUPPORT_FORUMS_ID ), 'sticky' => __( 'sticky', SUPPORT_FORUMS_ID ) ) as $status => $display ) :
		printf(
			'<div id="option-support-forums-settings-icons-%1$s">%2$s',
			$status,
			"\n"
		);

		$label = sprintf(
			__( '%s topics icon', SUPPORT_FORUMS_ID ),
			$display
		);

		printf(
			'<div class="label">%1$s</div>%2$s',
			ucfirst( $label ), // ucfirst() capitalizes string first char
			"\n"
		);
		echo '<div class="inputs">' . "\n";
		echo '<label class="checkboxs">';

		$label = sprintf(
			__( 'Add an icon to %s topics.', SUPPORT_FORUMS_ID ),
			$display
		);

		printf(
			'<input type="checkbox" value="1" id="support-forums-settings-icons-%1$s-0" name="support_forums_settings[icons][%1$s]" class="checkbox"%2$s /> %3$s</label>',
			$status,
			( $icons_enabled && in_array( $status, $support_forums_settings->icons ) ) ? ' checked="checked"' : '',
			$label
		);
		printf(
			'<p><img src="%1$s-%2$s.png" alt="%3$s" title="%3$s" style="vertical-align: middle;" /> - %3$s</p>',
			$icons_uri . SUPPORT_FORUMS_ID,
			$status,
			$display
		);
?>
			</div>
		</div>
<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<legend><?php _e( 'Legacy Support', SUPPORT_FORUMS_ID ); ?></legend>
		<div id="option-support-forums-import-settings">
			<div class="label"><?php _e( 'Import settings', SUPPORT_FORUMS_ID ); ?></div>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="support_forums_import_settings" id="support-forums-import-settings-0" value="1" /> <?php _e( 'Import Support Forum settings.', SUPPORT_FORUMS_ID ); ?></label>
			</div>
		</div>
		<div id="option-support-forums-remove-settings">
			<div class="label"><?php _e( 'Remove settings', SUPPORT_FORUMS_ID ); ?></div>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="support_forums_remove_settings" id="support-forums-remove-settings-0" value="1" /> <?php _e( 'Remove Support Forum settings after import.', SUPPORT_FORUMS_ID ); ?></label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<div id="option-support-forums-uninstall">
			<div class="label"><?php printf( __( 'Uninstall %s', SUPPORT_FORUMS_ID ), SUPPORT_FORUMS_NAME ); ?></div>
			<div class="inputs">
				<label class="checkboxs"><input type="checkbox" class="checkbox" name="support_forums_uninstall" id="support-forums-uninstall-0" value="1" /> <?php printf( __( 'Remove %s data from database.', SUPPORT_FORUMS_ID ), SUPPORT_FORUMS_NAME ); ?></label>
			</div>
		</div>
	</fieldset>
	<fieldset class="submit">
		<?php bb_nonce_field( 'options-support-forums-update' ); ?>
		<input type="hidden" name="action" value="update-support-forums-settings" />
		<input type="submit" name="submit" class="submit" value="<?php _e( 'Save Changes', SUPPORT_FORUMS_ID ); ?>" />
	</fieldset>
</form>
<?php
}

/**
 * Process plugin configuration page
 *
 * = = = = = = = = = = = = = = = = = = = = = = = = = = = =
 * Data structure reminder
 * = = = = = = = = = = = = = = = = = = = = = = = = = = = =
 *
 * $option						$value	as $key => $entry
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * support_forum_N				N
 * support_forums_settings		array()
 *
 * $key							$entry	as $_key => $_entry
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * default_status				'not_support'
 * poster_setable				1
 * poster_changeable			1
 * views						array()
 * icons						array()
 *
 * $_key						$_entry
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * not_resolved					1
 * ...
 * dir							'my-support-icons/'
 * sticky						1
 * ...
 *
 * = = = = = = = = = = = = = = = = = = = = = = = = = = = =
 *
 *
 * @global $bb_admin_body_class
 * @global $bbdb
 *
 * @uses bb_check_admin_referer()
 * @uses wp_get_referer()
 * @uses remove_query_arg()
 * @uses bb_delete_option()
 * @uses add_query_arg()
 * @uses bb_safe_redirect()
 * @uses import_support_forum_settings()
 * @uses bb_get_active_theme_directory()
 * @uses bb_update_option()
 * @uses bb_admin_notice()
 *
 * @return void
 */
function support_forums_configuration_page_process() {
	if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && $_POST['action'] == 'update-support-forums-settings' ) {
		bb_check_admin_referer( 'options-support-forums-update' );

		$goback = remove_query_arg( array( 'support-forums-updated', 'icons-directory-invalid', 'support-forums-uninstalled', 'support-forum-settings-imported', 'support-forum-settings-import-error' ), wp_get_referer() );

		if ( !isset( $_POST['support_forums_uninstall'] ) )
			$_POST['support_forums_uninstall'] = false;

		if ( true === (bool) $_POST['support_forums_uninstall'] ) { // Remove plugin data from database
			global $bbdb;

			bb_delete_option( 'support_forums_settings' );

			$bbdb->query( $bbdb->prepare( "DELETE FROM $bbdb->meta WHERE meta_key = %s", 'topic_support_status' ) ); // Remove plugin inserted topic meta (there could be none)

			$goback = add_query_arg( 'support-forums-uninstalled', 'true', $goback );

			bb_safe_redirect( $goback );
			exit;
		}

		if ( !isset( $_POST['support_forums_import_settings'] ) )
			$_POST['support_forums_import_settings'] = false;

		if ( true === (bool) $_POST['support_forums_import_settings'] ) { // Import Support Forum settings
			if ( !isset( $_POST['support_forums_remove_settings'] ) )
				$_POST['support_forums_remove_settings'] = false;

			$goback = ( true === (bool) import_support_forum_settings( (bool) $_POST['support_forums_remove_settings'] ) ) ?
				add_query_arg( 'support-forum-settings-imported', 'true', $goback ) :
				add_query_arg( 'support-forum-settings-import-error', 'true', $goback );

			bb_safe_redirect( $goback );
			exit;
		}

		// Temporary var for options
		$settings = array();

		foreach ( (array) $_POST as $option => $value ) // $option = ( support_forums_settings | support_forum_#id )
			if ( !in_array( $option, array( '_wpnonce', '_wp_http_referer', 'action', 'submit' ) ) ) {
				$option = trim( $option );
				$value = ( is_array( $value ) ) ? $value : trim( $value );

				if ( !isset( $value ) )
					$value = false;

				if ( $value ) {
					if ( is_array( $value ) ) { // $value = ( support_forums_settings )
 						foreach ( $value as $key => &$entry ) { // Prepend '&' to modify var value
							$key = trim( $key );
							$entry = ( is_array( $entry ) ) ? $entry : trim( $entry );

							if ( !isset( $entry ) )
								$entry = false;

							if ( $entry ) {
								if ( is_array( $entry ) ) // $entry = ( views | icons )
									foreach ( $entry as $_key => &$_entry ) { // Prepend '&' to modify var value
										$_key = trim( $_key );
										$_entry = ( is_array( $_entry ) ) ? $_entry : trim( $_entry );
	
										if ( !isset( $_entry ) )
											$_entry = false;
			
										if ( $_entry ) { // $_entry could be empty only if ( dir ) was empty
											if ( 'dir' == $_key ) {
												// First remove ALL shashes
												$_entry = str_replace( '/', '', $_entry );

												// Do the trim again since $_entry might have changed
												$_entry = trim( $_entry );

												// Tail a slash. Mandatory!
												$_entry .= '/';

												if ( !is_dir( bb_get_active_theme_directory() . 'images/' . $_entry ) ) {
													$goback = add_query_arg( 'icons-directory-invalid', 'true', $goback );
													bb_safe_redirect( $goback );
													exit;
												}

												$settings[$key][$_key] = $_entry;
											} else {
												// status, closed and sticky are elements of icons[]. views[] follows the same rule
												$settings[$key][] = $_key;
											}
										}
									}
								else // $entry = ( default_status | poster_setable | poster_changeable )
									$settings[$key] = $entry;
							}
						}
					} else { // $value = ( #id )
						// Forums ids are elements of forums[]
						$settings['forums'][] = $value;
					}
				}
			}

		bb_update_option( 'support_forums_settings', $settings );

		$goback = add_query_arg( 'support-forums-updated', 'true', $goback );
		bb_safe_redirect( $goback );
		exit;
	}

	if ( !empty( $_GET['support-forums-updated'] ) )
		bb_admin_notice( __( '<strong>Settings saved.</strong>', SUPPORT_FORUMS_ID ) );

	if ( !empty( $_GET['support-forums-uninstalled'] ) )
		bb_admin_notice( sprintf( __( '<strong>%s data removed.</strong>', SUPPORT_FORUMS_ID ), SUPPORT_FORUMS_NAME ) );

	if ( !empty( $_GET['icons-directory-invalid'] ) )
		bb_admin_notice(
			__( '<strong>The icons directory you entered is not valid.</strong>', SUPPORT_FORUMS_ID ),
			'error'
		);

	if ( !empty( $_GET['support-forum-settings-imported'] ) )
		bb_admin_notice( __( '<strong>Support Forum settings imported.</strong>', SUPPORT_FORUMS_ID ) );

	if ( !empty( $_GET['support-forum-settings-import-error'] ) )
		bb_admin_notice(
			__( '<strong>No Support Forum settings found.</strong>', SUPPORT_FORUMS_ID ),
			'error'
		);

	global $bb_admin_body_class;
	$bb_admin_body_class = ' bb-admin-settings';
}

/**
 * Whether or not Support Forum settings import was successful
 *
 * @param boolean $remove_sf_settings Whether or not remove Support Forum settings after import
 *
 * @global $bbdb
 *
 * @uses bb_get_option()
 * @uses support_forums_map_status()
 * @uses bb_update_option()
 *
 * @return boolean
 */
function import_support_forum_settings( $remove_sf_settings = false ) {
	$sf_forums = (array) bb_get_option( 'support_forum_enabled' );

	if ( empty( $sf_forums ) ) { // Support Forum is not installed
		return false;
	}

	// Get all other settings
	global $bbdb;

	$sf_default_status =    (string) bb_get_option( 'support_forum_default_status' );
	$sf_poster_setable =    (string) bb_get_option( 'support_forum_poster_setable' );
	$sf_poster_changeable = (string) bb_get_option( 'support_forum_poster_changeable' );
	$sf_views =             (array) bb_get_option( 'support_forum_views' );
	$sf_icons =             array(
		'status' => (string) bb_get_option( 'support_forum_icons_status' ),
		'closed' => (string) bb_get_option( 'support_forum_icons_closed' ),
		'sticky' => (string) bb_get_option( 'support_forum_icons_sticky' )
	);

	// Temporary var for imported settings
	$imported_settings = array();

	// Get support forums
	foreach ( $sf_forums as $sf_forum )
		$imported_settings['forums'][] = $sf_forum;

	// Get support topics
	$sf_topics = $bbdb->get_results( $bbdb->prepare( "SELECT object_id, meta_value FROM $bbdb->meta WHERE meta_key = %s", 'topic_resolved' ) );

	foreach ( $sf_topics as $sf_topic )
		$bbdb->query( $bbdb->prepare(
			"INSERT IGNORE INTO $bbdb->meta ( object_type, object_id, meta_key, meta_value ) VALUES ( %s, %d, %s, %s )", // Prevent duplication of content using IGNORE
			'bb_topic',
			(int) $sf_topic->object_id,
			'topic_support_status',
			support_forums_map_status( $sf_topic->meta_value )
		) );

	// Get default support status
	$imported_settings['default_status'] = ( !empty( $sf_default_status ) ) ?
		support_forums_map_status( $sf_default_status ) :
		'not_support';

	// Get poster setable
	$imported_settings['poster_setable'] = ( !empty( $sf_poster_setable ) ) ?
		$sf_poster_setable :
		'0';

	// Get poster changeable
	$imported_settings['poster_changeable'] = ( !empty( $sf_poster_changeable ) ) ?
		$sf_poster_changeable :
		'0';

	// Get support views
	if ( !empty( $sf_views ) )
		foreach ( array_keys( $sf_views ) as $sf_view )
			$imported_settings['views'][] = support_forums_map_status( $sf_view );

	// Get support, closed and sticky icons
	foreach ( $sf_icons as $option => $value )
		if ( !empty( $value ) )
			$imported_settings['icons'][] = $option;

	bb_update_option( 'support_forums_settings', $imported_settings );

	if ( true === (bool) $remove_sf_settings ) { // Remove Support Forum settings
		bb_delete_option( 'support_forum_enabled' );
		bb_delete_option( 'support_forum_default_status' );
		bb_delete_option( 'support_forum_poster_setable' );
		bb_delete_option( 'support_forum_poster_changeable' );
		bb_delete_option( 'support_forum_views' );
		bb_delete_option( 'support_forum_icons_status' );
		bb_delete_option( 'support_forum_icons_closed' );
		bb_delete_option( 'support_forum_icons_sticky' );
		$bbdb->query( $bbdb->prepare( "DELETE FROM $bbdb->meta WHERE meta_key = %s", 'topic_resolved' ) ); // Remove Support Forum inserted topic meta (there could be none)
	}

	return true;
}

/**
 * Map Support Forums statuses into Support Forums ones
 *
 * @param string $sf_status Support Forum status
 *
 * @return string
 */
function support_forums_map_status( $sf_status ) {
	switch ( $sf_status ) {
		case 'yes':
			$sf_status = 'resolved';
			break;
		case 'no':
			$sf_status = 'not_resolved';
			break;
		case 'mu':
			$sf_status = 'not_support';
			break;
	}

	return $sf_status;
}
?>