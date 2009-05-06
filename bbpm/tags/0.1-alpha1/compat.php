<?php

/* This is not an individual plugin, but a part of bbPM. */

/**
 * BB_URI_CONTEXT_* - Bitwise definitions for bb_uri() and bb_get_uri() contexts
 *
 * @since 1.0
 */
@define( 'BB_URI_CONTEXT_NONE',                 0 );
@define( 'BB_URI_CONTEXT_HEADER',               1 );
@define( 'BB_URI_CONTEXT_TEXT',                 2 );
@define( 'BB_URI_CONTEXT_A_HREF',               4 );
@define( 'BB_URI_CONTEXT_FORM_ACTION',          8 );
@define( 'BB_URI_CONTEXT_IMG_SRC',              16 );
@define( 'BB_URI_CONTEXT_LINK_STYLESHEET_HREF', 32 );
@define( 'BB_URI_CONTEXT_LINK_ALTERNATE_HREF',  64 );
@define( 'BB_URI_CONTEXT_LINK_OTHER',           128 );
@define( 'BB_URI_CONTEXT_SCRIPT_SRC',           256 );
@define( 'BB_URI_CONTEXT_IFRAME_SRC',           512 );
@define( 'BB_URI_CONTEXT_BB_FEED',              1024 );
@define( 'BB_URI_CONTEXT_BB_USER_FORMS',        2048 );
@define( 'BB_URI_CONTEXT_BB_ADMIN',             4096 );
@define( 'BB_URI_CONTEXT_BB_XMLRPC',            8192 );
@define( 'BB_URI_CONTEXT_WP_HTTP_REQUEST',      16384 );
//@define( 'BB_URI_CONTEXT_*',                    32768 );  // Reserved for future definitions
//@define( 'BB_URI_CONTEXT_*',                    65536 );  // Reserved for future definitions
//@define( 'BB_URI_CONTEXT_*',                    131072 ); // Reserved for future definitions
//@define( 'BB_URI_CONTEXT_*',                    262144 ); // Reserved for future definitions
@define( 'BB_URI_CONTEXT_AKISMET',              524288 );

if ( !function_exists( 'bb_uri' ) ) :
/**
 * Echo a URI based on the URI setting
 *
 * @since 1.0
 *
 * @param $resource string The directory, may include a querystring
 * @param $query mixed The query arguments as a querystring or an associative array
 * @param $context integer The context of the URI, use BB_URI_CONTEXT_*
 * @return void
 */
function bb_uri( $resource = null, $query = null, $context = BB_URI_CONTEXT_A_HREF )
{
	echo apply_filters( 'bb_uri', bb_get_uri( $resource, $query, $context ), $resource, $query, $context );
}
endif;

if ( !function_exists( 'bb_get_uri' ) ) :
/**
 * Return a URI based on the URI setting
 *
 * @since 1.0
 *
 * @param $resource string The directory, may include a querystring
 * @param $query mixed The query arguments as a querystring or an associative array
 * @param $context integer The context of the URI, use BB_URI_CONTEXT_*
 * @return string The complete URI
 */
function bb_get_uri( $resource = null, $query = null, $context = BB_URI_CONTEXT_A_HREF )
{
	// If there is a querystring in the resource then extract it
	if ( $resource && strpos( $resource, '?' ) !== false ) {
		list( $_resource, $_query ) = explode( '?', trim( $resource ), 2 );
		$resource = $_resource;
		$_query = wp_parse_args( $_query );
	} else {
		// Make sure $_query is an array for array_merge()
		$_query = array();
	}

	// $query can be an array as well as a string
	if ( $query ) {
		if ( is_string( $query ) ) {
			$query = ltrim( trim( $query ), '?' );
		}
		$query = wp_parse_args( $query );
	}

	// Make sure $query is an array for array_merge()
	if ( !$query ) {
		$query = array();
	}

	// Merge the queries into a single array
	$query = array_merge( $_query, $query );

	// Make sure context is an integer
	if ( !$context || !is_integer( $context ) ) {
		$context = BB_URI_CONTEXT_A_HREF;
	}

	// Get the base URI
	static $_uri;
	if( !isset( $_uri ) ) {
		$_uri = bb_get_option( 'uri' );
	}
	$uri = $_uri;

	/*// Use https?
	if (
		( ( $context & BB_URI_CONTEXT_BB_USER_FORMS ) && bb_force_ssl_user_forms() ) // Force https when required on user forms
	||
		( ( $context & BB_URI_CONTEXT_BB_ADMIN ) && bb_force_ssl_admin() ) // Force https when required in admin
	) {
		static $_uri_ssl;
		if( !isset( $_uri_ssl ) ) {
			$_uri_ssl = bb_get_option( 'uri_ssl' );
		}
		$uri = $_uri_ssl;
	}*/

	// Add the directory
	$uri .= ltrim( $resource, '/' );

	// Add the query string to the URI
	$uri = add_query_arg( $query, $uri );

	return apply_filters( 'bb_get_uri', $uri, $resource, $context );
}
endif;

if ( !function_exists( 'wp_enqueue_script' ) ) :
function wp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false ) {
	bb_enqueue_script( $handle, $src, $deps, $ver );
}
endif;

if ( !function_exists( 'get_user_display_name' ) ) :
function get_user_display_name( $id = 0 ) {
	$user = bb_get_user( bb_get_user_id( $id ) );
	return apply_filters( 'get_user_display_name', $user->display_name ? $user->display_name : $user->user_login, $user->ID );
}
endif;

if ( !class_exists( 'BP_User' ) ) :
class BP_User extends BB_User {}
endif;

if ( !function_exists( 'wp_cache_get' ) ) :
function wp_cache_get( $id, $flag = '' ) {
	return false;
}
endif;

if ( !function_exists( 'wp_cache_add' ) ) :
function wp_cache_add( $key, $data, $flag = '', $expire = 0 ) {}
endif;

if ( !function_exists( '_n' ) ) :
function _n( $single, $plural, $number, $domain = 'default' ) {
	return __ngettext( $single, $plural, $number, $domain );
}
endif;

?>