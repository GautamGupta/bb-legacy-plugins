<?php

$message = false;

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	if ( !empty( $_FILES['bavatar'] ) ) {
		if ( $_FILEs['bavatar']['size'] > BAVATARS_MAX_SIZE )
			bb_die( __( 'Your avatar\'s filesize is too large. Please upload a smaller file.', 'bavatars' ) );

		$src = @imagecreatefromstring( file_get_contents( $_FILES['bavatar']['tmp_name'] ) );
		if ( !$src )
			bb_die( __( 'The file you uploaded was not a valid image.', 'bavatars' ) );
		imagesavealpha( $src, true );
		imagealphablending( $src, false );

		$temp = imagecreatefromstring( gzinflate( base64_decode( '6wzwc+flkuJiYGDg9fRwCWJgYGIAYQ42IPWl4sovIMVYHOTuxLDunMxLIIctydvdheE/CC7Yu3wyUISzwCOymIGBWxiEGRlmzZEACrKXePq6st9kZRTi1nCI4qwFarzl6eIYUnHr7TVDRgYGjsMGB/Y/T2zqd3FaXzaJiaFOkpPhAMh4BgYDBoYGoAoeBoYEoAAzAwPQPPxSJCpHkiJROUKKLHeCpMhzJw9IbDSERkNoNIRGQ2g0hAZVCD04x7TkB9tfn9e/HIFiDJ6ufi7rnBKaAA==' ) ) );
		imagesavealpha( $temp, true );
		imagealphablending( $temp, false );

		$x = imagesx( $src );
		$y = imagesy( $src );
		$m = max( $x, $y );
		$w = $x * 512 / $m;
		$h = $y * 512 / $m;
		$l = ceil( ( 512 - $w ) / 2 );
		$t = ceil( ( 512 - $h ) / 2 );

		imagecopyresampled( $temp, $src, $l, $t, 0, 0, $w, $h, $x, $y );

		imagedestroy( $src );

		$id = md5( $user_id );

		$folder = BB_PATH . 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/';

		@unlink( $folder . $id . '.png' );

		foreach ( bb_glob( $folder . $id . '_*.png' ) as $avatarsize ) {
			@unlink( $avatarsize );
		}

		imagepng( $temp, $folder . $id . '.png', 9 );

		imagedestroy( $temp );

		$message = __( 'Avatar uploaded successfully!', 'bavatars' );
	} elseif ( $_POST['delete'] ) {
		bb_check_admin_referer( 'bavatar_delete-' . $user_id );

		$id = md5( $user_id );

		$folder = BB_PATH . 'avatars/' . substr( $id, 0, 1 ) . '/' . substr( $id, 0, 2 ) . '/' . substr( $id, 0, 3 ) . '/';

		@unlink( $folder . $id . '.png' );

		foreach ( bb_glob( $folder . $id . '_*.png' ) as $avatarsize ) {
			@unlink( $avatarsize );
		}

		$message = __( 'Avatar deleted successfully!', 'bavatars' );
	}
}

bb_get_header();

if ( $message )
	echo '<div class="notice"><p>' . $message . '</p></div>';

echo bb_get_avatar( $user_id, 256 );
?>
<form method="post" action="<?php profile_tab_link( $user_id, 'avatar' ); ?>" enctype="multipart/form-data">
	<input type="file" name="bavatar" id="bavatar" />
	<input type="submit" value="Upload new avatar &raquo;" />
</form>
<form method="post" action="<?php profile_tab_link( $user_id, 'avatar' ); ?>">
	<?php bb_nonce_field( 'bavatar_delete-' . $user_id ); ?>
	<input type="submit" id="delete" class="delete" name="delete" value="Delete" />
</form>

<?php bb_get_footer();