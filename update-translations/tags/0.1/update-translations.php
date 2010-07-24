<?php
/*
Plugin Name: Update Translations
Plugin URI: http://nightgunner5.wordpress.com/tag/update-translations/
Version: 0.1
Description: Download up-to-date plugin translations from Nightgunner5's Translation Station with the push of a button.
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
Text Domain: update-translations
Domain Path: /translations/
*/

function update_translations() {
	$plugin_names = array();
	require_once BB_PATH . 'bb-admin/includes/functions.bb-plugin.php';
	$plugins = bb_get_plugins( 'user' );

	$plugin_slugs = array();
	foreach ( $plugins as $plugin => $details ) {
		$slug = preg_replace( '/^user#|[\\\\\\/].*$/', '', $plugin );

		if ( !$details['text_domain'] )
			continue;

		$plugin_slugs[] = $slug;
		$plugin_names[$slug] = $details['name'];
	}
	if ( BB_LANG ) {
		require_once dirname( __FILE__ ) . '/locales.php';

		$lang_slug = array_search( BB_LANG, upd8_i18n_get_locales() );
		if ( !$lang_slug ) {
			bb_admin_notice( __( 'You are using an unsupported language. See <code>locales.php</code> in this plugin\'s source code for a list of languages supported by this plugin.', 'update-translations' ) );
		}
	} else {
		bb_admin_notice( __( 'You have not defined a language in your <code>bb-config.php</code> file.', 'update-translations' ) );
	} ?>
<h2><?php _e( 'Update Translations', 'update-translations' ); ?></h2>
<?php do_action( 'bb_admin_notices' ); ?>

<?php if ( !$error ) { ?>
<p><?php _e( 'With your permission, Update Translations will attempt to download fresh translations for the following plugins:', 'update-translations' ); ?></p>
<ul style="margin-top: .5em">
<?php foreach ( $plugin_names as $slug => $plugin ) { ?>
	<li><a style="color: #060;" href="http://nightgunner5.is-a-geek.net:1337/glotpress/projects/<?php echo $slug; if ( $lang_slug ) { ?>/<?php echo $lang_slug; ?>/default<?php } ?>" target="_blank"><?php echo $plugin; ?></a></li>
<?php } ?>
</ul>
<form class="settings" action="" method="post"><fieldset class="submit">
	<input type="checkbox" name="download-all" id="download-all"/>
	<label for="download-all"><?php _e( '<strong>For plugin developers:</strong> Download all languages and POT.', 'update-translations' ); ?></label>
	<br/>
	<input type="hidden" name="action" value="update-translations"/>
	<?php bb_nonce_field( 'update-translations' ); ?>
	<input type="submit" class="submit" value="<?php _e( 'Update now', 'update-translations' ); ?>"/>
</fieldset></form>
<?php }
}

function upd8_i18n_admin_add() {
	bb_admin_add_submenu( __( 'Update Translations', 'update-translations' ), 'manage_plugins', 'update_translations', 'tools-recount.php' );
}
add_action( 'bb_admin_menu_generator', 'upd8_i18n_admin_add' );

function upd8_i18n_pre_head() {
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'update-translations' ) {
		bb_check_admin_referer( 'update-translations' );
		set_time_limit( 0 );

		if ( BB_LANG || !empty( $_POST['download-all'] ) ) {
			require_once dirname( __FILE__ ) . '/locales.php';

			$locales = upd8_i18n_get_locales();
			if ( empty( $_POST['download-all'] ) ) {
				$lang_slugs = array( BB_LANG => array_search( BB_LANG, $locales ) . '/default' );
			}
			if ( $lang_slugs || !empty( $_POST['download-all'] ) ) {
				require_once BB_PATH . 'bb-admin/includes/functions.bb-plugin.php';
				$plugins = bb_get_plugins( 'user' );

				$messages = array();
				$errors = array();
				foreach ( $plugins as $plugin => $details ) {
					if ( !$details['text_domain'] )
						continue;

					$slug = preg_replace( '/^user#|[\\\\\\/].*$/', '', $plugin );

					@mkdir( BB_PLUGIN_DIR . $slug . '/' . $details['domain_path'] );

					$url_project = 'http://nightgunner5.is-a-geek.net:1337/glotpress/api/projects/' . $slug;
					if ( !empty( $_POST['download-all'] ) ) {
						$project = wp_remote_get( $url_project );

						if ( is_wp_error( $project ) ) {
							$errors[] = sprintf( __( '%s was not found on the translation site.', 'update-translations' ), $details['name'] );
							continue;
						}

						$project_data = json_decode( wp_remote_retrieve_body( $project ), true );

						$lang_slugs = array();
						foreach ( $project_data['translation_sets'] as $translation_set ) {
							$lang_slugs[isset( $locales[$translation_set['locale']] ) ? $locales[$translation_set['locale']] : $translation_set['locale']] = $translation_set['locale'] . '/' . $translation_set['slug'];
						}

						$url_pot = 'http://nightgunner5.is-a-geek.net:1337/glotpress/pot/' . $slug . '.pot';
						$pot = wp_remote_get( $url_pot );

						file_put_contents( BB_PLUGIN_DIR . $slug . '/' . $details['domain_path'] . '/' . $details['text_domain'] . '.pot', wp_remote_retrieve_body( $pot ) );
					}

					foreach ( $lang_slugs as $lang => $lang_slug ) {
						$url_po = 'http://nightgunner5.is-a-geek.net:1337/glotpress/projects/' . $slug . '/' . $lang_slug . '/export-translations';
						$url_mo = 'http://nightgunner5.is-a-geek.net:1337/glotpress/projects/' . $slug . '/' . $lang_slug . '/export-translations?format=mo';

						$po = wp_remote_get( $url_po );
						if ( is_wp_error( $po ) ) {
							$project = wp_remote_get( $url_project );
							if ( is_wp_error( $project ) ) {
								$errors[] = sprintf( __( '%s was not found on the translation site.', 'update-translations' ), $details['name'] );
								continue;
							} else {
								$errors[] = sprintf( __( '%s was not found in your language on the translation site.', 'update-translations' ), $details['name'] );
								continue;
							}
						}
						file_put_contents( BB_PLUGIN_DIR . $slug . '/' . $details['domain_path'] . '/' . $details['text_domain'] . '-' . $lang . '.po', wp_remote_retrieve_body( $po ) );

						$mo = wp_remote_get( $url_mo );
						if ( is_wp_error( $mo ) ) {
							$errors[] = sprintf( __( '%s could not be updated.', 'update-translations' ), $details['name'] );
							continue;
						}
						file_put_contents( BB_PLUGIN_DIR . $slug . '/' . $details['domain_path'] . '/' . $details['text_domain'] . '-' . $lang . '.mo', wp_remote_retrieve_body( $mo ) );
						$messages[] = sprintf( __( '%s was updated successfully.', 'update-translations' ), $details['name'] );
					}
				}
				if ( $messages )
					bb_admin_notice( implode( '<br/>', array_unique( $messages ) ) );
				if ( $errors )
					bb_admin_notice( implode( '<br/>', array_unique( $errors ) ), 'error' );
			}
		}
	}
}
add_action( 'update_translations_pre_head', 'upd8_i18n_pre_head' );

load_plugin_textdomain( 'update-translations', dirname( __FILE__ ) . '/translations' );