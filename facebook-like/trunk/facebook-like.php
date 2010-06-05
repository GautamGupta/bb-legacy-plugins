<?php
/**
* Plugin Name: Facebook Like
* Plugin URI: http://gaut.am/bbpress/plugins/facebook-like/
* Description: Let your readers quickly share your content on Facebook with a simple click. The Like button is the new Facebook sharing button released on Apr. 21st 2010
* Version: 0.1
* Author: Gautam
* Author URI: http://www.cyberfundu.com/
*/

/**
 * @license GNU General Public License version 3 (GPLv3): http://www.opensource.org/licenses/gpl-3.0.html
 */

/** Create Text Domain For Translations */
bb_load_plugin_textdomain( 'facebook-like', dirname( __FILE__ ) . '/translations' );

/**
 * Defines
 */
define( 'FBLIKE_VER'		, '0.1'							); /** Version */
define( 'FBLIKE_OPTIONS'	, 'FacebookLike'					); /** Option Name */
//define( 'FBLIKE_PLUGURL'	, bb_get_plugin_uri( bb_plugin_basename( __FILE__ ) )	); /** Plugin URL */

/**
 * Options
 */
$fblike_plugopts = bb_get_option( FBLIKE_OPTIONS );
if ( !is_array( $fblike_plugopts ) ) { /* Set the Options if they are not set */
	$fblike_plugopts = array(
		'width'		=> '450',
		'height'	=> '30',
		'layout'	=> 'standard',
		'verb'		=> 'like',
		'font'		=> 'arial',
		'colorscheme'	=> 'light',
		'align'		=> 'left',
		'showfaces'	=> false,
		'margin_top'	=> '10',
		'margin_bottom'	=> '0',
		'margin_left'	=> '0',
		'margin_right'	=> '0',
		'facebook_id'	=> '',
		'facebook_image'=> '',
		'xfbml'		=> false,
		'xfbml_async'	=> false,
		'latitude'	=> '',
		'longitude'	=> '',
		'street_addess'	=> '',
		'locality'	=> '',
		'region'	=> '',
		'postal_code'	=> '',
		'county_name'	=> '',
		'email'		=> '',
		'county_name'	=> '',
		'email'		=> '',
		'county_name'	=> '',
		'phone_numer'	=> '',
		'fax_numer'	=> '',
		'like_type'	=> 'article',
		'type'		=> 'website',
		'facebook_app_id'	=> '',
		'facebook_page_id'	=> ''
		
	);
	bb_update_option( FBLIKE_OPTIONS, $fblike_plugopts );
}

if ( bb_is_admin() ) /* Load admin.php file if it is the admin area */
	require_once( 'includes/admin.php' );
elseif ( bb_is_topic() ) /* Else load public.php file if it is the topic page */
	require_once( 'includes/public.php' );
