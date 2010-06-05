<?php

/**
 * @package Facebook Like
 * @subpackage Admin Section
 * @author Gautam Gupta (www.cyberfundu.com)
 * @link http://gaut.am/bbpress/plugins/facebook-like/
 */

/**
 * Check for Updates and if available, then notify
 *
 * @uses WP_Http
 * @uses bb_admin_notice To generate a notice if new version is available
 * @uses $plugin_browser If available, then generates an auto-upgrade link
 *
 * @return string|bool Returns version if update is available, else false
 */
function fblike_update_check() {
	$latest_ver = trim( wp_remote_retrieve_body( wp_remote_get( 'http://gaut.am/uploads/plugins/updater.php?pid=10&chk=ver&soft=bb&current=' . FBLIKE_VER, array( 'user-agent' => 'FBLike/bbPress v' . FBLIKE_VER ) ) ) );
	if ( !$latest_ver || version_compare( $latest_ver, FBLIKE_VER, '<=' ) ) /* If call fails or plugin is upto date, then return */
		return false;
	
	global $plugin_browser;
	if ( class_exists( 'Plugin_Browser' ) && $plugin_browser && method_exists( $plugin_browser, 'nonceUrl' ) ) { /* Can be automatically upgraded */
		$uhref = $plugin_browser->nonceUrl( 'upgrade-plugin_facebook-like', array( 'plugin' => 'plugin_browser_admin_page', 'pb_action' => 'upgrade', 'pb_plugin_id' => urlencode( 'facebook-like' ) ) );
		$message = sprintf( __( 'New version (%1$s) of Facebook Like Plugin is available! Please download the latest version from <a href="%2$s">here</a> or <a href="%3$s">upgrade automatically</a>.', 'facebook-like' ), $latest_ver, 'http://bbpress.org/plugins/topic/facebook-like/', $uhref );
	} else { /* Else just output the normal message with download link */
		$message = sprintf( __( 'New version (%1$s) of Facebook Like Plugin is available! Please download the latest version from <a href="%2$s">here</a>.', 'facebook-like' ), $latest_ver, 'http://bbpress.org/plugins/topic/facebook-like/' );
	}
	
	bb_admin_notice( $message, 'error' );

	return $latest_ver;
}

function fblike_options() {
	global $fblike_plugopts;
	
	$fblike_layouts	= array( 'standard' => __( 'Standard', 'facebook-like' ), 'button_count' => __( 'Button Count', 'facebook-like' ) );
	$fblike_verbs	= array( 'like' => __( 'Like', 'facebook-like' ), 'recommend' => __( 'Recommend', 'facebook-like' ) );
	$fblike_aligns	= array( 'left' => __( 'Left', 'facebook-like' ), 'right' => __( 'Right', 'facebook-like' ) );
	$fblike_fonts	= array( 'arial' => __( 'Arial', 'facebook-like' ), 'lucida grande' => __( 'Lucida Grande', 'facebook-like' ), 'segoe ui' => __( 'Segoe UI', 'facebook-like' ), 'tahoma' => __( 'Tahoma', 'facebook-like' ), 'trebuchet ms' => __( 'Trebuchet MS', 'facebook-like' ), 'verdana' => __( 'Verdana', 'facebook-like' ) );
	$fblike_colorschemes = array( 'light' => __( 'Light', 'facebook-like' ), 'dark' => __( 'Dark', 'facebook-like' ) );
	$fblike_types	= array(
		'activities'	=> '-- ' . __( 'Activities', 'facebook-like' )		. ' --', 'activity' => __( 'Activity', 'facebook-like' ), 'sport' => __( 'Sport', 'facebook-like' ), 
		'businesses'	=> '-- ' . __( 'Businesses', 'facebook-like' )		. ' --', 'bar' => __( 'Bar', 'facebook-like' ), 'company' => __( 'Company', 'facebook-like' ), 'cafe' => __( 'Cafe', 'facebook-like' ), 'hotel' => __( 'Hotel', 'facebook-like' ), 'restaurant' => __( 'Restaurant', 'facebook-like' ), 
		'groups'	=> '-- ' . __( 'Groups', 'facebook-like' )		. ' --', 'cause' => __( 'Cause', 'facebook-like' ), 'sports_league' => __( 'Sports League', 'facebook-like' ), 'sports_team' => __( 'Sports Team', 'facebook-like' ),
		'organizations'	=> '-- ' . __( 'Organizations', 'facebook-like' )	. ' --', 'band' => __( 'Band', 'facebook-like' ), 'government' => __( 'Government', 'facebook-like' ), 'non_profit' => __( 'Non Profit', 'facebook-like' ), 'school' => __( 'School', 'facebook-like' ), 'university' => __( 'University', 'facebook-like' ),
		'people'	=> '-- ' . __( 'People', 'facebook-like' )		. ' --', 'actor' => __( 'Actor', 'facebook-like' ), 'athlete' => __( 'Athlete', 'facebook-like' ), 'author' => __( 'Author', 'facebook-like' ), 'director' => __( 'Director', 'facebook-like' ), 'musician' => __( 'Musician', 'facebook-like' ), 'politician' => __( 'Politician', 'facebook-like' ), 'public_figure' => __( 'Public Figure', 'facebook-like' ),
		'places'	=> '-- ' . __( 'Places', 'facebook-like' )		. ' --', 'city' => __( 'City', 'facebook-like' ), 'country' => __( 'Country', 'facebook-like' ), 'landmark' => __( 'Landmark', 'facebook-like' ), 'state_province' => __( 'State Provice', 'facebook-like' ),
		'websites'	=> '-- ' . __( 'Websites', 'facebook-like' )		. ' --', 'article' => __( 'Article', 'facebook-like' ), 'blog' => __( 'Blog', 'facebook-like' ), 'website' => __( 'Website', 'facebook-like' ),
		'products and entertainment' => '-- ' . __( 'Products and Entertainment', 'facebook-like' ) . ' --', 'album' => __( 'Album', 'facebook-like' ), 'book' => __( 'Book', 'facebook-like' ), 'drink' => __( 'Drink', 'facebook-like' ), 'food' => __( 'Food', 'facebook-like' ), 'game' => __( 'Game', 'facebook-like' ), 'movie' => __( 'Movie', 'facebook-like' ), 'product' => __( 'Product', 'facebook-like' ), 'song' => __( 'Song', 'facebook-like' ), 'tv_show' => __( 'TV Show', 'facebook-like' )
	);
	
	if ( $_POST['fblike_opts_submit'] == 1 ) { /* Settings have been received, now save them! */
		
		bb_check_admin_referer( 'fblike-save-chk' ); /* Security Check */
		
		/* Sanity Checks */
		
		foreach ( array( 'facebook_image', 'longitude', 'latitude', 'street_address', 'locality', 'region', 'postal_code', 'country', 'email', 'phone_number', 'fax_number' ) as $opt ) /* Texts */
			$fblike_plugopts[$opt]	= (string) esc_attr( trim( $_POST[$opt] ) );
		foreach ( array( 'width', 'height', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'facebook_id', 'facebook_app_id', 'facebook_image_id', 'facebook_page_id' ) as $opt ) /* Numbers */
			$fblike_plugopts[$opt]	= intval( $_POST[$opt] );
		foreach ( array( 'showfaces', 'xfbml', 'xfbml_async' ) as $opt ) /* Checkboxes */
			$fblike_plugopts[$opt]	= (bool) $_POST[$opt] == true ? true : false;
		
		/* Arrays */
		$fblike_plugopts['layout']	= in_array( $_POST['layout'],		array_keys( $fblike_layouts		) ) ? $_POST['layout']		: 'standard';
		$fblike_plugopts['verb']	= in_array( $_POST['verb'],		array_keys( $fblike_verbs		) ) ? $_POST['verb']		: 'like';
		$fblike_plugopts['font']	= in_array( $_POST['font'],		array_keys( $fblike_fonts		) ) ? $_POST['font']		: 'arial';
		$fblike_plugopts['colorscheme']	= in_array( $_POST['colorscheme'],	array_keys( $fblike_colorschemes	) ) ? $_POST['colorscheme']	: 'light';
		$fblike_plugopts['align']	= in_array( $_POST['align'],		array_keys( $fblike_aligns		) ) ? $_POST['align']		: 'left';
		$fblike_plugopts['type']	= in_array( $_POST['type'],		array_keys( $fblike_types		) ) ? $_POST['type']		: 'website';
		
		/* Save the options and notify user */
		bb_update_option( FBLIKE_OPTIONS, $fblike_plugopts );
		bb_admin_notice( sprintf( __( 'The options have been successfully saved! Maybe you would consider <a href="%s">donating</a>.', 'facebook-like' ), 'http://gaut.am/donate/bb/fbl/' ) );
	}
	
	/* Check for updates and if available, then notify */
	fblike_update_check();
	
	/* Options in an array to be printed */
	$fblike_options = array(
		'appearance' => array(
			'type'		=> 'message',
			'title'		=> '<h2 style="padding:0;font-size:1.6em">' . __( 'Appearance', 'facebook-like' ) . '</h2>'
		),
		'width' => array(
			'title'		=> __( 'Width', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['width'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'height' => array(
			'title'		=> __( 'Height', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['height'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'layout' => array(
			'title'		=> __( 'Layout', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['layout'],
			'type'		=> 'select',
			'options'	=> $fblike_layouts
		),
		'verb' => array(
			'title'		=> __( 'Verb to display', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['verb'],
			'type'		=> 'select',
			'options'	=> $fblike_verbs
		),
		'font' => array(
			'title'		=> __( 'Font', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['font'],
			'type'		=> 'select',
			'options'	=> $fblike_fonts
		),
		'colorscheme' => array(
			'title'		=> __( 'Color Scheme', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['colorscheme'],
			'type'		=> 'select',
			'options'	=> $fblike_colorschemes
		),
		'showfaces' => array(
			'title'		=> __( 'Show Faces', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['showfaces'],
			'type'		=> 'checkbox',
			'note'		=> __( 'Don\'t forget to increase the height accordingly.', 'facebook-like' ),
			'options'	=> array(
				'1'	=> __( 'Yes', 'facebook-like' )
			)
		),
		'position' => array(
			'type'		=> 'message',
			'title'		=> '<h2 style="padding:0;font-size:1.6em">' . __( 'Position', 'facebook-like' ) . '</h2>'
		),
		'align' => array(
			'title'		=> __( 'Align', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['align'],
			'type'		=> 'select',
			'options'	=> $fblike_aligns,
			'note'		=> __( 'Don\'t forget to adjust the width accordingly if you choose to align right.', 'facebook-like' )
		),
		'margin_top' => array(
			'title'		=> __( 'Top Margin', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['margin_top'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'margin_bottom' => array(
			'title'		=> __( 'Bottom Margin', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['margin_bottom'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'margin_left' => array(
			'title'		=> __( 'Left Margin', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['margin_left'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'margin_right' => array(
			'title'		=> __( 'Right Margin', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['margin_right'],
			'class'		=> array( 'short' ),
			'after'		=> 'px'
		),
		'fb-admin-page' => array(
			'type'		=> 'message',
			'title'		=> '<h2 style="padding:0;font-size:1.6em">' . __( 'Facebook Admin Page', 'facebook-like' ) . '</h2>',
			'message'	=> '<br /><br /><small>' . sprintf( __( 'For advanced users only, be sure to read the <a href="%s">FAQ</a> in case of problem', 'facebook-like' ), 'http://gaut.am/bbpress/plugins/facebook-like/faq/' ) . '</small>'
		),
		'facebook_id' => array(
			'title'		=> __( 'Numeric Facebook ID', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['facebook_id'],
			'note'		=> __( 'Your Facebook ID to manage your fans and send them updates. If you have several, separate them with commas. Required if using XFBML. Eg. 68310606562 and not markzuckerberg.', 'facebook-like' )
		),
		'facebook_image' => array(
			'title'		=> __( 'Facebook Image URL', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['facebook_image']
		),
		'xfbml' => array(
			'title'		=> __( 'Use XFBML', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['xfbml'],
			'type'		=> 'checkbox',
			'options'	=> array(
				'1'	=> __( 'Yes', 'facebook-like' )
			)
		),
		'xfbml_async' => array(
			'title'		=> __( 'Load XFBML Asynchronously', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['xfbml_async'],
			'type'		=> 'checkbox',
			'options'	=> array(
				'1'	=> __( 'Yes', 'facebook-like' )
			)
		),
		'facebook_app_id' => array(
			'title'		=> __( 'Facebook App ID', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['facebook_app_id'],
			'note'		=> sprintf( __( 'To get an App ID: <a href="%s">Create an App</a>. If you have several, separate them with commas. Required if using XFBML.', 'facebook-like' ), 'http://developers.facebook.com/setup/' )
		),
		'facebook_page_id' => array(
			'title'		=> __( 'Facebook Page ID', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['facebook_page_id']
		),
		'open-graph' => array(
			'type'		=> 'message',
			'title'		=> '<h2 style="padding:0;font-size:1.6em">' . __( 'Open Graph Options', 'facebook-like' ) . '</h2>',
			'message'	=> '<br /><a href="http://www.opengraphprotocol.org/">' . __( 'More Info', 'facebook-like' ) . '</a>'
		),
		'latitude' => array(
			'title'		=> __( 'Latitude', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['latitude']
		),
		'longitude' => array(
			'title'		=> __( 'Longitude', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['longitude']
		),
		'street_address' => array(
			'title'		=> __( 'Street Address', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['street_address']
		),
		'locality' => array(
			'title'		=> __( 'Locality', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['locality'],
			'class'		=> array( 'short' )
		),
		'region' => array(
			'title'		=> __( 'Region', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['region'],
			'class'		=> array( 'short' )
		),
		'postal_code' => array(
			'title'		=> __( 'Postal Code', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['postal_code'],
			'class'		=> array( 'short' )
		),
		'country' => array(
			'title'		=> __( 'Country', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['country'],
			'class'		=> array( 'short' )
		),
		'postal_code' => array(
			'title'		=> __( 'Postal Code', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['postal_code'],
			'class'		=> array( 'short' )
		),
		'email' => array(
			'title'		=> __( 'Email', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['email']
		),
		'phone_number' => array(
			'title'		=> __( 'Phone', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['phone_number']
		),
		'fax_number' => array(
			'title'		=> __( 'Fax', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['fax_number']
		),
		'type' => array(
			'title'		=> __( 'Type', 'facebook-like' ),
			'value' 	=> $fblike_plugopts['type'],
			'type'		=> 'select',
			'options'	=> $fblike_types
		)
	);

	?>
	
	<h2><?php _e( 'Facebook Like Options', 'facebook-like' ); ?></h2>
	<?php do_action( 'bb_admin_notices' ); ?>
	<form method="post" class="settings options">
		<fieldset>
			<?php
			foreach ( $fblike_options as $option => $args )
				bb_option_form_element( $option, $args );
			?>
		</fieldset>
		<fieldset class="submit">
			<?php bb_nonce_field( 'fblike-save-chk' ); ?>
			<input type="hidden" name="fblike_opts_submit" value="1"></input>
			<p><?php printf( __( 'Happy with the plugin? Why not <a href="%1$s">buy the author a cup of coffee or two</a> or <a href="%2$s">follow him on twitter</a> (or even visit his <a href="%3$s">website</a>).', 'facebook-like' ), 'http://gaut.am/donate/bb/fbl/', 'http://twitter.com/Gaut_am', 'http://www.cyberfundu.com/' ); ?></p>
			<input class="submit" type="submit" name="submit" value="<?php _e( 'Save Changes', 'ajaxed-chat' ); ?>" />
		</fieldset>
	</form>
	
	<?php
}
    
/**
 * Adds a menu link to the setting's page in the Settings section
 *
 * @uses bb_admin_add_submenu()
 */
function fblike_menu_link() {
	bb_admin_add_submenu( __( 'Facebook Like', 'facebook-like' ), 'administrate', 'fblike_options', 'options-general.php' );
}

add_action( 'bb_admin_menu_generator', 'fblike_menu_link', 3 ); /* Adds a menu link to setting's page */
