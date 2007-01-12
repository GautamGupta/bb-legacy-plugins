<?php
/*
Plugin Name: Avatar
Plugin URI: http://faq.rayd.org/bbpress_avatar/
Description: Allows users to specify an external address to an avatar that will be shown when they post
Change Log: .73a - Adjusts implementation so that core files no longer have to be changed.
Author: Joshua Hutchins
Author URI: http://ardentfrost.rayd.org/
Version: .73a
*/

function post_avatar() { // function to use in page php to get the avatar
	if ( get_avatar_loc( get_post_author_id() ) )
		echo '<img src="' . get_avatar_loc( get_post_author_id() ) . '">';

} 


function get_avatar_loc ( $id ) { //function to return avatar loc from database
	global $bbdb, $bb_current_user;
	
	$user = bb_get_user( $id );
	$profile_info_keys = get_profile_info_keys();	
	
	if ( $id && false !== $user )
		if ( is_array( $profile_info_keys ) )
			foreach ( $profile_info_keys as $key => $label ) {
				if ( 'avatar_loc' == $key )
					return $user->$key;
			}
}
// This list can be changed to add or remove information that is added into the Profile, but the last one (Avatar Location) MUST remain for this plugin to work
function get_profile_info_keys_plus_avatar() {
	return array('user_email' => array(1, __('Email')), 'user_url' => array(0, __('Website')), 'from' => array(0, __('Location')), 'occ' => array(0, __('Occupation')), 'interest' => array(0, __('Interests')), 'avatar_loc' => array(0,__('Avatar URL')));
}

add_filter('get_profile_info_keys',	'get_profile_info_keys_plus_avatar');


?>