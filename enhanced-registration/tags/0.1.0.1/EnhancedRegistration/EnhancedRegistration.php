<?php
/*
Plugin Name: Enhanced Registration
Description: Enhancing bbPress Registration
Author: Yu-Jie Lin
Author URI: http://www.livibetter.com/
Version: 0.1.0.1
Creation Date: 2007-11-25T12:41:56+0800
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

define('ER_DOMAIN', 'EnhancedRegistration');
load_plugin_textdomain(ER_DOMAIN, dirname(__FILE__) . '/locale');

// Remove login_before usermeta
add_action('bb_user_login', 'ERHook_bb_user_login');
function ERHook_bb_user_login($userID) {
	$v = bb_get_usermeta($userID, 'login_before');
	if (!empty($v))
		bb_delete_usermeta($userID, 'login_before');
	}

add_action('register_user', 'ERHook_register_user');
function ERHook_register_user($userID) {
	$options = bb_get_option('EROptions');
	if (($over = $options['autoDeleteUnactivatedOver']) > 0)
		bb_update_usermeta($userID, 'login_before', time() + $over * 3600);
	}

/* Admin
======================================== */

add_action('bb_admin_menu_generator', 'ERAdminMenu');
function ERAdminMenu() {
    global $bb_submenu;
    $bb_submenu['plugins.php'][] = array(__('Enhanced Registration', ER_DOMAIN), 'manage_options', 'EROptions');
    }

include_once('OptionsPage.php');

function ERSendReport() {
	$options = bb_get_option('EROptions');
	// TODO BUG May not send report if the forum has really few visitors
	$doSend = false;	
	if ($options['sendReport'] == 'hourly')
		$doSend = gmdate('G', time()) != gmdate('G', $options['lastSent']);
	if ($options['sendReport'] == 'daily')
		$doSend = gmdate('j', time()) != gmdate('j', $options['lastSent']);
	
	if (!$doSend)
		return;
	// Check any report is available to be sent
	$report = '';
	if ($options['deletedUnactivatedIDs']) {
		$report .= "Deleted users:\n";
		foreach ($options['deletedUnactivatedIDs'] as $t => $mappedIDLogin)
			$report .= sprintf("%1\$s:\n  %2\$s\n\n", gmdate('r', $t), implode("\n  ", array_map(create_function('$id, $userLogin',
				'return "$id: $userLogin";'), array_keys($mappedIDLogin), array_values($mappedIDLogin))));
		}
	if (empty($report))
		return;
	// Send the report
    $message = __("The report was generated at %1\$s. All times are in UTC.\n\n%2\$s", ER_DOMAIN);
    $result = bb_mail(
        bb_get_option('admin_email'),
        bb_get_option('name') . ": Your {$options[sendReport]} report",
        sprintf($message, gmdate('r', time()), $report));

	if ($result) {
		// Seems send successfully, then Clean up
		$options['lastSent'] = time();
		unset($options['deletedUnactivatedIDs']);
		bb_update_option('EROptions', $options);
		}
	else
        error_log('bbPress ER: Failed to send report!');
	return $result;
	}

/* Options
======================================== */

function ERGetDefaultOptions() {
	return array(
		'autoDeleteUnactivatedOver' => 0,
		'sendReport' => 'daily'
		);
	}

function ERUpgradeOptions() {
	global $bbdb;
	$options = bb_get_option('EROptions');
	if (empty($options)) {
		$options = ERGetDefaultOptions();
		$options['version'] = '0.1';
		}
	else {
		if (version_compare($options['version'], '0.0.0.2', '<=')) {
			// Get list of whom has usermeta act_code
			$users = $bbdb->get_results("SELECT $bbdb->users.ID, $bbdb->users.user_registered FROM $bbdb->users, $bbdb->usermeta WHERE $bbdb->users.ID = $bbdb->usermeta.user_id AND $bbdb->usermeta.meta_key = 'act_code'");
			if ($users) {
				if (($over = $options['autoDeleteUnactivatedOver']) > 0)
					foreach ($users as $user) {
						bb_update_usermeta($user->ID, 'login_before', bb_gmtstrtotime($user->user_registered) + $over * 3600);
						}
				$bbdb->query("DELETE FROM $bbdb->usermeta WHERE meta_key = 'act_code'");
				}
			$options['version'] = '0.1';
			}
		}
	bb_update_option('EROptions', $options);
	return $options;
	}

/* Functions
======================================== */

function ERGetUnactivatedUserCount() {
	global $bbdb;
	return $bbdb->query("SELECT $bbdb->users.ID FROM $bbdb->users, $bbdb->usermeta WHERE $bbdb->users.ID = $bbdb->usermeta.user_id AND $bbdb->usermeta.meta_key = 'login_before'");
	}

function ERDeleteUnactivated() {
	global $bbdb;
	$IDs = $bbdb->get_col("SELECT $bbdb->users.ID, $bbdb->users.user_login FROM $bbdb->users, $bbdb->usermeta WHERE $bbdb->users.ID = $bbdb->usermeta.user_id AND $bbdb->usermeta.meta_key = 'login_before' AND DATE_ADD('1970-01-01', INTERVAL UNIX_TIMESTAMP() SECOND) >= DATE_ADD('1970-01-01', INTERVAL $bbdb->usermeta.meta_value SECOND)");
	
	if ($IDs) {
		if (!function_exists('array_combine')) {
			function array_combine($arr1,$arr2) {
				$out = array();
				foreach($arr1 as $key1 => $value1)
					$out[$value1] = $arr2[$key1];
				return $out;
				}
			}
		$mapped = array_combine($IDs, $bbdb->get_col(null, 1));
		foreach ($IDs as $ID)
			bb_delete_user($ID);
		// Put these IDs into log
		$options = bb_get_option('EROptions');
		$options['deletedUnactivatedCount'] += sizeof($IDs);
		if (in_array($options['sendReport'], array('hourly', 'daily'))) {
			if (!is_array($options['deletedUnactivatedIDs']))
				$options['deletedUnactivatedIDs'] = array();
			$options['deletedUnactivatedIDs'] = array_merge($options['deletedUnactivatedIDs'], array(time() . '.0' => $mapped));
			}
		else
			unset($options['deletedUnactivatedIDs']);
		bb_update_option('EROptions', $options);
		}
	return $mapped;
	}

// Initializes ER and auto-deletion
add_action('bb_init', 'ERHook_bb_init');
function ERHook_bb_init() {
	$options = ERUpgradeOptions();
	// Process auto tasks
	if (time() >= $options['lastRun'] + 3600) {
		$options['lastRun'] = time();
		bb_update_option('EROptions', $options);

		ERDeleteUnactivated();
		}
	ERSendReport();
	}

add_action('bb_deactivate_plugin_' . bb_plugin_basename(__FILE__), 'ERDeactivate');
function ERDeactivate() {
    if ($_GET['by'] == 'plugin') {
        bb_delete_option('EROptions');
        // Remove usermeta
        global $bbdb;
        $bbdb->query("DELETE FROM $bbdb->usermeta WHERE meta_key = 'login_before'");
        }
    }
?>
