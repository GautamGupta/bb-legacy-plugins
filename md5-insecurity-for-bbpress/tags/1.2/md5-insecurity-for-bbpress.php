<?php
/*
Plugin Name: MD5 insecurity for bbPress
Plugin URI: http://bbpress.org/plugins/topic/72
Description: Changes the password hashing in bbPress to use old-skool MD5
Author: Sam Bauers
Author URI: 
Version: 1.2

Version History:
1.0		: Initial Release
1.1		: Update for compatibility with new auth cookies
1.2		: Port directly from Ryan Boren's "MD5 Password Hashes"
*/

if ( !function_exists('wp_check_password') ) {
	function wp_check_password($password, $hash, $user_id = '') {
		// If the hash was updated to the new hash before this plugin
		// was installed, rehash as md5.
		if ( strlen($hash) > 32 ) {
			global $wp_hasher;
			if ( empty($wp_hasher) ) {
				require_once( BB_PATH . BB_INC . 'class-phpass.php');
				$wp_hasher = new PasswordHash(8, TRUE);
			}
			$check = $wp_hasher->CheckPassword($password, $hash);
			if ( $check && $user_id ) {
				// Rehash using new hash.
				wp_set_password($password, $user_id);
				$user = bb_get_user($user_id);
				$hash = $user->user_pass;
			}
			
			return apply_filters('check_password', $check, $password, $hash, $user_id);
		}
		
		$check = ( $hash == md5($password) );
		
		return apply_filters('check_password', $check, $password, $hash, $user_id);
	}
}

if ( !function_exists( 'wp_hash_password' ) ) {
	function wp_hash_password($password) {
		return md5($password);
	}
}
?>