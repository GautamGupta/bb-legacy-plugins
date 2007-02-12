<?php
/*
Plugin Name: Force Login
Description: No one can see your forums unless they are logged in.
Plugin URI: http://bbpress.org/forums/topic/117
Author: Michael D Adams
Author URI: http://blogwaffe.com/
Version: 0.8
*/

function force_login_init() {
	if ( !bb_is_user_logged_in()
		&& 0 !== strpos($_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'bb-login.php')
		&& 0 !== strpos($_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'bb-reset-password.php')
		&& 0 !== strpos($_SERVER['REQUEST_URI'], bb_get_option( 'path' ) . 'register.php')
		
	) {
		bb_load_template( 'login.php' );
		exit;
	}
}

add_action( 'bb_init', 'force_login_init' );

?>
