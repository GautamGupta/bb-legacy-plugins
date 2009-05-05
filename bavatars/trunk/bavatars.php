<?php
/*
Plugin Name: Bavatars
Plugin Description: Gravatar - Globally recognized + bbPress = Bavatar
Version: 0.4
Plugin URI: http://llamaslayers.net/daily-llama/tag/bavatars
Author: Nightgunner5
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
	@mkdir( BB_PATH . '/avatars', 0777 );

	for ( $a = 0; $a < 16; $a++ ) {
		@mkdir( BB_PATH . '/avatars/' . dechex( $a ), 0777 );
		for ( $b = 0; $b < 16; $b++ ) {
			@mkdir( BB_PATH . '/avatars/' . dechex( $a ) . '/' . dechex( $a ) . dechex( $b ), 0777 );
			for ( $c = 0; $c < 16; $c++ ) {
				@mkdir( BB_PATH . '/avatars/' . dechex( $a ) . '/' . dechex( $a ) . dechex( $b ) . '/' . dechex( $a ) . dechex( $b ) . dechex( $c ), 0777 );
			}
		}
	}
}
bb_register_plugin_activation_hook( __FILE__, 'bavatars_install' );

function bavatars_add_profile_tab() {
	add_profile_tab( __( 'Avatar', 'bavatars' ), 'edit_profile', 'administrate', dirname( __FILE__ ) . '/bavatars-profile.php', 'avatar' );
}
add_action( 'bb_profile_menu', 'bavatars_add_profile_tab' );

function bavatars_filter( $avatar, $id_or_email, $size, $default ) {
	if ( is_object( $id_or_email ) ) {
		$id = $id_or_email->user_id;
	} elseif ( ( function_exists( 'is_email' ) && is_email( $id_or_email ) ) || ( !function_exists( 'is_email' ) && !is_numeric( $id_or_email ) ) ) {
		$id = bb_get_user( $id_or_email, array( 'by' => 'email' ) )->ID;
	} else {
		$id = (int)$id_or_email;
	}

	if ( !$id )
		return $avatar;

	$id = md5( $id );

	$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '.png';

	if ( !file_exists( BB_PATH . '/' . $location ) )
		return $avatar;

	if ( $size != 512 ) {
		$_location = $location;
		$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '_' . $size . '.png';
	}

	if ( !file_exists( BB_PATH . '/' . $location ) ) {
		$src = imagecreatefrompng( BB_PATH . $_location );
		imagesavealpha( $src, true );
		imagealphablending( $src, false );

		$temp = imagecreatetruecolor( $size, $size );
		imagesavealpha( $temp, true );
		imagealphablending( $temp, false );

		imagecopyresampled( $temp, $src, 0, 0, 0, 0, $size, $size, 512, 512 );

		imagepng( $temp, BB_PATH . '/' . $location, 9 );

		imagedestroy( $temp );
		imagedestroy( $src );
	}

	return '<img alt="" src="' . bb_get_option( 'uri' ) . $location . '" class="avatar avatar-' . $size . ' avatar-bavatar" style="height:' . $size . 'px; width:' . $size . 'px;" />';
}
add_filter( 'bb_get_avatar', 'bavatars_filter', 10, 4 );
add_filter( 'get_avatar', 'bavatars_filter', 10, 4 );

?>