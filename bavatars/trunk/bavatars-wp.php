<?php
/*
Plugin Name: Bavatars for WordPress
Plugin Description: Gravatar - Globally recognized + bbPress = Bavatar, now for WordPress as well!
Version: 0.5
Plugin URI: http://nightgunner5.wordpress.com/tag/bavatars/
Author: Ben L. (Nightgunner5)
Author URI: http://llamaslayers.net/daily-llama/
*/

define( 'BAVATARS_BBPRESS_URI', 'http://forums.example.com/' ); // The full address of the front page of your forum.
define( 'BAVATARS_BBPRESS_PATH', '/path/to/bbpress/' ); // The full path to your bbPress installation.
// If your bbPress forum is in a subdirectory of your WordPress blog, you can use define( 'BAVATARS_BBPRESS_PATH', ABSPATH . 'forum/' ); or the like.

/*
 _____   _____   ______   _____       _____   _____    _   _____   _   ______   ______  
|  ___| |_   _| |  __  | |  _  |     |  ___| |  __ \  | | |_   _| | | |  __  | |  ____| 
| |___    | |   | |  | | | |_| |     | |__   | |  \ \ | |   | |   | | | |  | | | |  __  
|___  |   | |   | |  | | |  ___|     |  __|  | |  | | | |   | |   | | | |  | | | | |_ | 
 ___| |   | |   | |__| | | |         | |___  | |__/ / | |   | |   | | | |  | | | |__| | 
|_____|   |_|   |______| |_|         |_____| |_____/  |_|   |_|   |_| |_|  |_| |______| 
                                   o r   e l s e
*/

function bavatars_filter( $avatar, $id_or_email, $size, $default, $alt ) {
	if ( is_object( $id_or_email ) ) {
		$id = $id_or_email->user_id;
	} elseif ( ( function_exists( 'is_email' ) && is_email( $id_or_email ) ) || ( !function_exists( 'is_email' ) && !is_numeric( $id_or_email ) ) ) {
		$id = get_user_by_email( $id_or_email, array( 'by' => 'email' ) )->ID;
	} else {
		$id = (int)$id_or_email;
	}

	if ( !$id )
		return $avatar;

	$id = md5( $id );

	$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '.png';

	if ( !file_exists( trailingslashit( BAVATARS_BBPRESS_PATH ) . $location ) )
		return $avatar;

	if ( $size != 512 ) {
		$_location = $location;
		$location = 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/' . $id . '_' . $size . '.png';
	}

	if ( !file_exists( trailingslashit( BAVATARS_BBPRESS_PATH ) . $location ) ) {
		$src = imagecreatefrompng( trailingslashit( BAVATARS_BBPRESS_PATH ) . $_location );
		imagesavealpha( $src, true );
		imagealphablending( $src, false );

		$temp = imagecreatetruecolor( $size, $size );
		imagesavealpha( $temp, true );
		imagealphablending( $temp, false );

		imagecopyresampled( $temp, $src, 0, 0, 0, 0, $size, $size, 512, 512 );

		imagepng( $temp, trailingslashit( BAVATARS_BBPRESS_PATH ) . $location, 9 );

		imagedestroy( $temp );
		imagedestroy( $src );
	}

	return '<img alt="' . $alt . '" src="' . trailingslashit( BAVATARS_BBPRESS_URI ) . $location . ' class="avatar avatar-' . $size . ' avatar-bavatar" style="height:' . $size . 'px; width:' . $size . 'px;" />';
}
add_filter( 'get_avatar', 'bavatars_filter', 10, 5 );
