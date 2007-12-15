<?php
/*
Plugin Name: Gravatar
Plugin URI: http://code.google.com/p/llbbsc/wiki/GravatarPlugin
Description: A simple Gravatar plugin for bbPress
Author: Yu-Jie Lin
Author URI: http://www.livibetter.com/
Version: 0.2.2
Creation Date: 2007-10-18 12:13:25 UTC+8
*/
/*
 * Copyright 2007 Yu-Jie Lin
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or (at your
 * option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('GRAVATAR_DOMAIN', 'Gravatar');
load_plugin_textdomain(GRAVATAR_DOMAIN, dirname(__FILE__) . '/locale');

/* Template stuff
======================================== */

// Find a proper default image
function GAGetUserDefaultImageURI($id=0) {
	$bbUser = new BB_User($id);
	$options = bb_get_option('GAOptions');
	
	$defaultImageURI = $options['defaultRoleImages'][$bbUser->roles[0]];
	// Find Role Default Image
	if (empty($defaultImageURI))
		// No Role Default Image available for this user, use default, instead.
		$defaultImageURI = $options['defaultImage'];
	return $defaultImageURI;
	}

// retrun src for img tag or false
function GAGetImageURI($id=0, $size=0) {
	if ($id==0 || $id===null)
		if (is_topic())
		   $id = get_post_author_id();

	$options = bb_get_option('GAOptions');

	if ($size<1)
		$size = $options['size'];
	if (!empty($size))
		$size = "&amp;size=$size";

	if (!empty($options['rating']))
		$rating = "&amp;rating=$options[rating]";

	if (!$user = bb_get_user(bb_get_user_id($id)))
		// Not a member
		return $options['defaultImage'];

	$defaultImageURI = GAGetUserDefaultImageURI($user->ID);
	if (!GAVerified($user->ID))
		// No Gravatar
		return $defaultImageURI;

	$gravatarEmail = GAGetUserGravatarEmail($user->ID);
	if (isset($user->gravatar['md5']) && !empty($gravatarEmail)) {
		if (!empty($defaultImageURI))
			$defaultImageURI = '&amp;default=' . urlencode($defaultImageURI);
		return "http://www.gravatar.com/avatar.php?gravatar_id=" . $user->gravatar['md5'] . "$defaultImageURI$rating$size";
		}
	return $options['defaultImage'];
	}

// echo version of GAGetImageURI
function GAImageURI($id=0, $size=0) {
	echo GAGetImageURI($id, $size);
	}

// return preset complete img tag
function GAGetImage($id=0, $size=0, $style='border: 1px solid black', $class='', $link=true) {
	global $GA_DEFAULT_IMAGE;
	if ($id==0 || $id===null)
		if (is_topic())
		 $id = get_post_author_id();
	
	$options = bb_get_option('GAOptions');

	if ($size<1)
		$size = $options['size'];
	if (!empty($size))
		$style = "width: {$size}px; height: {$size}px;" . $style;

	// Check style and class
	if ($style!='')
		$style = " style=\"$style\"";
	if ($class!='')
		$class = " class=\"$class\"";

	if (!$user = bb_get_user(bb_get_user_id($id)))
		if (!empty($options['defaultImage']))
			return "<img$style$class src=\"" . urlencode($options['defaultImage']) . "?size=$size\" alt=\"\"/>";
		else
			return "";

	$GravatarURI = GAGetImageURI($user->ID, $size);
	if (empty($GravatarURI))
		return "";

	$img = "<img$style$class src=\"" . $GravatarURI . '" alt="' .
		(($user->display_name) ? $user->display_name : $user->user_login) . '"/>';
	if ($link)
		return '<a href="' . attribute_escape(get_user_profile_link($user->ID)) . "\">$img</a>";
	return $img;
	}

// echo version of GAGetImage without link
function GAImage($id=0, $size=0, $style='border: 1px solid black', $class='') {
	echo GAGetImage($id, $size, $style, $class, false);
	}

// echo version of GAGetImage with link
function GAImageLink($id=0, $size=0, $style='border: 1px solid black', $class='') {
	echo GAGetImage($id, $size, $style, $class);
	}

/* Internal stuff used only when useRegisteredEmail is true
======================================== */

function GAGetUserGravatarEmail($userID) {
	$options = bb_get_option('GAOptions');
	if ($options['useRegisteredEmail']) {
		$user = bb_get_user($userID);
		$gravatarEmail = $user->user_email;
		}
	else
		$gravatarEmail = bb_get_usermeta($userID, 'gravatar_email');
	return $gravatarEmail;
	}

function GAVerified($userID) {
	$gravatarEmail = GAGetUserGravatarEmail($userID);
	$gravatarVCode = bb_get_usermeta($userID, 'gravatar_vcode');
	// No such usermetas?
	if (!$gravatarEmail || !$gravatarVCode) return false;
	$gravatar = bb_get_usermeta($userID, 'gravatar');
	return ($gravatarEmail == $gravatar['email'] &&
			$gravatarVCode == $gravatar['vcode']);
	}

// Make profile editing page adds a field Gravatar Email, and block other users.
function GAHook_get_profile_info_keys($keys) {
	global $user_id;
	$options = bb_get_option('GAOptions');
	if (is_bb_profile()) {
		$currentUserID = bb_get_current_user_info( 'id' );
		// A powerful user or user him/herself
		if (bb_current_user_can('edit_user', $user_id)) {
			if (!$options['useRegisteredEmail'])
				$keys['gravatar_email'] = array(0, __('Gravatar Email', GRAVATAR_DOMAIN));
			$keys['gravatar_vcode'] = array(0, __('Gravatar Verification Code', GRAVATAR_DOMAIN));
			}
		}
	elseif (bb_get_location() == 'register-page' && !$options['useRegisteredEmail'])
		$keys['gravatar_email'] = array(0, __('Gravatar Email', GRAVATAR_DOMAIN));

	return $keys;
	}

// Send the Verification Code
function GASendVCode($userID) {
	$gravatar = bb_get_usermeta($userID, 'gravatar');
	$message = __("New Gravatar Email is %1\$s.\nNew Verification Code is %2\$s\n\nIt is an 8-letter string. Note that before you successfully verify your new Gravatar Email, your Gravatar will not work.\n\n%3\$s\n%4\$s", GRAVATAR_DOMAIN);
	if (false === bb_mail(
		bb_get_user_email($userID),
		bb_get_option('name') . ': ' . __('Your Gravatar usage verification code', GRAVATAR_DOMAIN),
		sprintf($message, $gravatar['new_email'],$gravatar['new_vcode'], bb_get_option('name'), bb_get_option('uri'))))
		error_log("bPress GA: Failed to send notification mail (user ID $userID)");
	}

// Generate the Verification Code
function GAGenerateVCode($userID) {
	$gravatar = bb_get_usermeta($userID, 'gravatar');
	$gravatar['new_vcode'] = bb_random_pass(8); // from registration-functions.php
	bb_update_usermeta($userID, 'gravatar', $gravatar);
	}

// Check gravatar_email usermeta after profile edited or new user registered
function GAHook_profile_edited($userID) {
	$options = bb_get_option('GAOptions');
	$gravatar = bb_get_usermeta($userID, 'gravatar');

	$gravatarEmail = GAGetUserGravatarEmail($userID);
	$gravatarVCode = bb_get_usermeta($userID, 'gravatar_vcode');
	$gravatar = bb_get_usermeta($userID, 'gravatar');

	// No email inputed
	if ($gravatarEmail === null || $gravatarEmail == '') {
		if ($options['useRegisteredEmail'])
			bb_delete_usermeta($userID, 'gravatar_email');
		bb_delete_usermeta($userID, 'gravatar');
		return;
		}

	// New email?
	if (!GAVerified($userID))
		if (isset($gravatar['new_email']) &&
			isset($gravatar['new_vcode'])) {
			if ($gravatarEmail == $gravatar['new_email'] &&
				$gravatarVCode == $gravatar['new_vcode']) {
				// New email verified, update with news
				$gravatar['email'] = $gravatar['new_email'];
				$gravatar['vcode'] = $gravatar['new_vcode'];
				$gravatar['md5'] = md5($gravatar['email']);
				unset($gravatar['new_email'], $gravatar['new_vcode']);
				bb_update_usermeta($userID, 'gravatar', $gravatar);
				}
			elseif ($gravatarEmail != $gravatar['new_email']) {
				// Save newer email
				$gravatar['new_email'] = $gravatarEmail;
				bb_update_usermeta($userID, 'gravatar', $gravatar);
				bb_update_usermeta($userID, 'gravatar_vcode', 'Newer code should be in you mail box.'); #__('Newer code should be in you mail box.', GRAVATAR_DOMAIN));
				 // New email has been changed again, regenerate the vcode
				GAGenerateVCode($userID);
				GASendVCode($userID);
				}
			else {
				// New email has not been changed, send vcode again
				bb_update_usermeta($userID, 'gravatar_vcode', 'Code has been sent again.'); #__('Code has been sent again.', GRAVATAR_DOMAIN));
				GASendVCode($userID);
				}
			}
		else {
			// Verify the email format
			if (bb_verify_email($gravatarEmail) === false) {
				bb_update_usermeta($userID, 'gravatar_vcode', 'Not a Valid Email!'); #__('Not a Valid Email!', GRAVATAR_DOMAIN));
				bb_delete_usermeta($userID, 'gravatar');
				return;
				}
			// Generate new verification code
			$gravatar['new_email'] = $gravatarEmail;
			bb_update_usermeta($userID, 'gravatar_vcode', 'Check Your Mailbox.'); #__('Check Your Mailbox.', GRAVATAR_DOMAIN));
			bb_update_usermeta($userID, 'gravatar', $gravatar);
			GAGenerateVCode($userID);
			GASendVCode($userID);
			}
	}

// Detects Registration page
function GAHook_get_location($prevResult, $src) {
	if (bb_find_filename($src) == 'register.php')
		return 'register-page';
	return $prevResult;
	}

/* Hooks for Internal Stuff
======================================== */

add_filter('get_profile_info_keys', 'GAHook_get_profile_info_keys');
add_filter('bb_get_location', 'GAHook_get_location', 10, 2);
add_action('profile_edited', 'GAHook_profile_edited');
add_action('register_user', 'GAHook_profile_edited');

/* Options
======================================== */

function GAGetAllDefaultOptions() {
	$options = array();
	$options = array_merge(GAGetDefaultGeneralOptions()   , $options);
	$options = array_merge(GAGetDefaultImageOptions()	, $options);
	return $options;
	}

function GAGetDefaultGeneralOptions() {
	$options = array();
	$options['useRegisteredEmail'] = false;
	$options['rating'] = 'G'; # [ G | PG | R | X ]
	$options['size']   = 64;  # 1..80
	return $options;
	}

function GAGetDefaultImageOptions() {
	$options = array();
	$options['defaultImage'] = '';
	// Find one in plugin directory or theme directory
	if	 (file_exists(dirname(__FILE__) . '/gravatar-default.jpg'))
		$options['defaultImage'] = bb_path_to_url(dirname(__FILE__) . '/gravatar-default.jpg');
	elseif (file_exists(dirname(__FILE__) . '/gravatar-default.gif'))
		$options['defaultImage'] = bb_path_to_url(dirname(__FILE__) . '/gravatar-default.gif');
	elseif (file_exists(dirname(__FILE__) . '/gravatar-default.png'))
		$options['defaultImage'] = bb_path_to_url(dirname(__FILE__) . '/gravatar-default.png');
	elseif (file_exists(bb_get_active_theme_folder() . '/gravatar-default.jpg'))
		$options['defaultImage'] = bb_path_to_url(bb_get_active_theme_folder() . '/gravatar-default.jpg');
	elseif (file_exists(bb_get_active_theme_folder() . '/gravatar-default.gif'))
		$options['defaultImage'] = bb_path_to_url(bb_get_active_theme_folder() . '/gravatar-default.gif');
	elseif (file_exists(bb_get_active_theme_folder() . '/gravatar-default.png'))
		$options['defaultImage'] = bb_path_to_url(bb_get_active_theme_folder() . '/gravatar-default.png');
	return $options;
	}

/* Admin
======================================== */

function GAAdminMenu() {
	global $bb_submenu;
	$bb_submenu['plugins.php'][] = array(__('Gravatar', GRAVATAR_DOMAIN), 'manage_options', 'GAOptions');

	$options = bb_get_option('GAOptions');
	// Check options for 0.2 or first install
	if (empty($options)) {
		$options = GAGetAllDefaultOptions();
		bb_update_option('GAOptions', $options);
		}
	}

function GADeactivate() {
	if ($_GET['by'] == 'plugin') {
		bb_delete_option('GAOptions');
		// Remove usermeta
		global $bbdb;
		$bbdb->query("DELETE FROM $bbdb->usermeta WHERE meta_key like 'gravatar%'");
		}
	}

include_once('OptionsPage.php');
add_action('bb_admin_menu_generator', 'GAAdminMenu');
//bb_register_deactivation_hook(__FILE__, 'GADeactivate'); // Bug in bbPress 0.8.3, do same thing using next line
add_action('bb_deactivate_plugin_' . bb_plugin_basename(__FILE__), 'GADeactivate');
?>
