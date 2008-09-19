<?php
/*
Plugin Name: Gaming Codes
Plugin URI: http://alumnos.dcc.uchile.cl/~egraells
Description: A plugin that allows your users to have Gamer/Friend codes of gaming consoles in your bbPress profiles.
Author: Eduardo Graells
Version: 1.0
Author URI: http://alumnos.dcc.uchile.cl/~egraells
License: GPLv3.
*/

/// initialize this plugin

add_action('bb_init', 'gaming_codes_initialize', 100);

function gaming_codes_initialize() {
	global $gaming_fields;
	
	$gaming_fields = array(
		'nds' => array('id' => 'gaming_code-nds', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo DS Friend Code", "gaming-codes")),
		'wii' => array('id' => 'gaming_code-wii', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo Wii Friend Code", "gaming-codes")),
		'x360' => array('id' => 'gaming_code-x360', 'type' => __('console', 'gaming-codes'), 'description' => __("XBox-Live Gamer Tag", "gaming-codes")),
		'ps3' => array('id' => 'gaming_code-ps3', 'type' => __('console', 'gaming-codes'), 'description' => __("PlayStation Network ID", "gaming-codes")),
		'ggpo' => array('id' => 'gaming_code-ggpo', 'type' => __('emulation', 'gaming-codes'), 'description' => __("GGPO.net Username", "gaming-codes")),
		'ddf' => array('id' => 'gaming_code-2df', 'type' => __('emulation', 'gaming-codes'), 'description' => __("2D Fighter Username", "gaming-codes"))
	);	
}

/// print the profile form

function gaming_codes_form($user_id) {
	if (!bb_is_user_logged_in()) 
		return;
	global $gaming_fields;
	$user_id = (int) $user_id;
	if ($user_id <= 0) 
		bb_die(__('What are you looking at?', 'gaming-codes'));

	echo '<fieldset><legend>' . __('Gaming Codes', 'gaming-codes') . '</legend>';
	echo '<table class="form-table"><tbody>';

	foreach ($gaming_fields as $field) {
		echo '<tr>';
		echo '<th><label for="' . $field['id'] . '">' . $field['description'] . '</label></th>';
		echo '<td>';
		echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . bb_get_usermeta($user_id, $field['id']) . '" />';
		echo '</td></tr>';
	}
	echo '</tbody></table></fieldset>';
}

/// process the profile form

add_action('profile_edited', 'gaming_codes_process_form');

function gaming_codes_process_form($user_id) {
	global $gaming_fields;
	foreach ($gaming_fields as $slug => $field) {
		if (isset($_POST[$field['id']]) && !empty($_POST[$field['id']]))
			bb_update_usermeta($user_id, $field['id'], attribute_escape($_POST[$field['id']]));
		else
			bb_delete_usermeta($user_id, $field['id']);
	}
}

function the_user_profile_gaming_codes($before = '', $after = '', $id = 0) {
	if ( !$u = bb_get_user( bb_get_user_id( $id ) ) )
		return;

	global $gaming_fields;
	
	if (!$gaming_fields) 
		return;

	if ($u) {
		$codes = array();
		
		foreach ($gaming_fields as $slug => $field) {
			if ($u->{$field['id']}) 
				$codes[] = '<dt class="' . $slug . '">' . $field['description'] . '</dt><dd>' . $u->{$field['id']} . '</dd>';
			else {
				$value = bb_get_usermeta($u->ID, $field['id']);
				if ($value != '') 
					$codes[] = '<dt class="' . $slug . '">' . $field['description'] . '</dt><dd>' . $value . '</dd>';
			}
		}
		
		if (!empty($codes)) {
			echo $before;
			echo '<dl id="gaming-codes">';
			echo implode(" ", $codes);
			echo '</dl>';
			
			$tag = '';
			if ($u->gaming_code-x360) 
				$tag = $u->gaming_code-x360;
			else 
				$tag = bb_get_usermeta($u->ID, 'gaming_code-x360');
			
			if (!empty($tag))
				print_user_gamercard($tag);
			
			echo $after;
		}
	}
}

function print_user_gamercard($tag) {
	if (!empty($tag))
			echo '<iframe src="http://gamercard.xbox.com/' . urlencode($tag) . '.card" frameborder="0" height="141" scrolling="no" width="204"></iframe>';
}

?>