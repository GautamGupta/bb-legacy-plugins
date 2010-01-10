<?php
/*
Plugin Name: Bavatars
Plugin Description: Gravatar - Globally recognized + bbPress = Bavatar
Version: 0.5
Plugin URI: http://nightgunner5.wordpress.com/tag/bavatars/
Author: Ben L. (Nightgunner5)
Author URI: http://llamaslayers.net/daily-llama/
*/

define( 'BAVATARS_MAX_SIZE', 50 * 1024 ); // 50KB - less than 5% of a megabyte

/*
 _____   _____   ______   _____       _____   _____    _   _____   _   ______   ______  
|  ___| |_   _| |  __  | |  _  |     |  ___| |  __ \  | | |_   _| | | |  __  | |  ____| 
| |___    | |   | |  | | | |_| |     | |__   | |  \ \ | |   | |   | | | |  | | | |  __  
|___  |   | |   | |  | | |  ___|     |  __|  | |  | | | |   | |   | | | |  | | | | |_ | 
 ___| |   | |   | |__| | | |         | |___  | |__/ / | |   | |   | | | |  | | | |__| | 
|_____|   |_|   |______| |_|         |_____| |_____/  |_|   |_|   |_| |_|  |_| |______| 
                                   o r   e l s e
*/

function bavatars_install() {
	mkdir( BB_PATH . 'avatars', 0777 );

	for ( $a = 0; $a < 16; $a++ ) {
		mkdir( BB_PATH . 'avatars/' . dechex( $a ), 0777 );
		for ( $b = 0; $b < 16; $b++ ) {
			mkdir( BB_PATH . 'avatars/' . dechex( $a ) . '/' . dechex( $a ) . dechex( $b ), 0777 );
			for ( $c = 0; $c < 16; $c++ ) {
				mkdir( BB_PATH . 'avatars/' . dechex( $a ) . '/' . dechex( $a ) . dechex( $b ) . '/' . dechex( $a ) . dechex( $b ) . dechex( $c ), 0777 );
			}
		}
	}
}
bb_register_plugin_activation_hook( __FILE__, 'bavatars_install' );

function bavatars_add_profile_tab() {
	add_profile_tab( __( 'Avatar', 'bavatars' ), 'edit_profile', 'administrate', dirname( __FILE__ ) . '/bavatars-profile.php', 'avatar' );
}
add_action( 'bb_profile_menu', 'bavatars_add_profile_tab' );

function bb_bavatars_filter( $avatar, $id_or_email, $size, $default ) {
	if ( is_object( $id_or_email ) ) {
		$id = $id_or_email->user_id;
	} elseif ( ( function_exists( 'is_email' ) && is_email( $id_or_email ) ) || ( !function_exists( 'is_email' ) && !is_numeric( $id_or_email ) ) ) {
		$id = bb_get_user( $id_or_email, array( 'by' => 'email' ) );
		$id = $id->ID;
	} else {
		$id = (int)$id_or_email;
	}

	if ( !$id )
		return $avatar;

	$id = md5( $id );

	$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '.png';

	if ( !file_exists( BB_PATH . $location ) )
		return $avatar;

	if ( $size != 512 ) {
		$_location = $location;
		$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '_' . $size . '.png';
	}

	if ( !file_exists( BB_PATH . $location ) ) {
		$src = imagecreatefrompng( BB_PATH . $_location );
		imagesavealpha( $src, true );
		imagealphablending( $src, false );

		$temp = imagecreatetruecolor( $size, $size );
		imagesavealpha( $temp, true );
		imagealphablending( $temp, false );

		imagecopyresampled( $temp, $src, 0, 0, 0, 0, $size, $size, 512, 512 );

		imagepng( $temp, BB_PATH . $location, 9 );

		imagedestroy( $temp );
		imagedestroy( $src );
	}

	return '<img alt="" src="' . bb_get_option( 'uri' ) . $location . '" class="avatar avatar-' . $size . ' avatar-bavatar" style="height:' . $size . 'px; width:' . $size . 'px;" />';
}
add_filter( 'bb_get_avatar', 'bb_bavatars_filter', 10, 4 );

if ( !function_exists( 'bavatars_filter' ) )
	add_filter( 'get_avatar', 'bb_bavatars_filter', 10, 4 );

if ( bb_is_admin() ) {
	function bavatars_fix_permissions_really() {
		if ( bb_verify_nonce( $_GET['nonce'], 'bavatars-fix-permissions' ) )
			bavatars_install();
		bb_safe_redirect( wp_get_referer() );
		exit;
	}
	add_action( 'bavatars_fix_permissions_pre_head', 'bavatars_fix_permissions_really' );

	function bavatars_fix_permissions() {}

	function bavatars_admin_init() {
		if ( !file_exists( BB_PATH . 'avatars' ) || !is_dir( BB_PATH . 'avatars' ) || !is_writable( BB_PATH . 'avatars' ) ) {
			bb_admin_notice( sprintf( __( 'Bavatars was unable to create the folders needed. Please create a folder called avatars in your forum root and set its permissions to 0777 (drwxrwxrwx). <a href="%s">Click here when you have done this</a>.', 'bavatars' ), bb_get_uri( 'bb-admin/admin-base.php', array( 'plugin' => 'bavatars_fix_permissions', 'nonce' => bb_create_nonce( 'bavatars-fix-permissions' ) ), BB_CONTEXT_BB_ADMIN ) ), 'error' );
		}

		if ( isset( $_GET['plugin'] ) && $_GET['plugin'] == 'bavatars_fix_permissions' )
			bb_admin_add_submenu( '_', 'use_keys', 'bavatars_fix_permissions' );
	}
	add_action( 'bb_admin_menu_generator', 'bavatars_admin_init' );
}
