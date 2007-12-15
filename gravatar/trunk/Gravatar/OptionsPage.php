<?php
/*
 * Copyright 2007 Yu-Jie Lin
 * 
 * This file is part of Cite this.
 * 
 * Cite this is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 * 
 * Cite this is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * Author: Yu-Jie Lin
 * Creation Date: 2007-10-29T07:43:42+0800
 */

function GAOptions() {
	$options = bb_get_option('GAOptions');
 
	if (isset($_POST['manage'])) {
		switch($_POST['manage']) {
		case __('Reset All Options', GRAVATAR_DOMAIN):
			$options = GAGetAllDefaultOptions();
			bb_update_option('GAOptions', $options);
			echo '<div class="updated"><p>' . __('All options are reseted!', GRAVATAR_DOMAIN) . '</p></div>';
			break;
		case __('Deactivate Plugin', GRAVATAR_DOMAIN):
			$plugin_file = dirname(bb_plugin_basename(__FILE__)) . '/Gravatar.php';
			wp_redirect(str_replace('&#038;', '&', bb_nonce_url("plugins.php?action=deactivate&plugin=$plugin_file", "deactivate-plugin_$plugin_file")) . '&by=plugin');
			break;
			}
		}
	elseif (isset($_POST['updateGeneralOptions'])) {
		switch($_POST['updateGeneralOptions']) {
		case __('Save', GRAVATAR_DOMAIN):
			$newOptions = array();
			$newOptions['useRegisteredEmail'] = ($_POST['useRegisteredEmail'] == 'true') ? true : false;
			$newOptions['rating'] = $_POST['rating'];
			$newOptions['size']   = $_POST['size'];
			$options = array_merge($options, $newOptions);
			bb_update_option('GAOptions', $options);
			echo '<div class="updated"><p>' . __('General options saved!', GRAVATAR_DOMAIN) . '</p></div>';
			break;
		case __('Reset', GRAVATAR_DOMAIN):
			$options = array_merge($options, GAGetDefaultGeneralOptions());
			bb_update_option('GAOptions', $options);
			echo '<div class="updated"><p>' . __('General options reseted!', GRAVATAR_DOMAIN) . '</p></div>';
			break;
			}
		}
	elseif (isset($_POST['updateImageOptions'])) {
		switch($_POST['updateImageOptions']) {
		case __('Save', GRAVATAR_DOMAIN):
			$newOptions = array();
			$newOptions['defaultImage'] = $_POST['defaultImage'];
			// Find default image URIs for roles
			foreach ($_POST as $name => $value) {
				if (preg_match('/defaultImage\-(.+)/', $name, $match)) {
					if (empty($_POST[$name]))
						unset($options['defaultRoleImages'][$match[1]]);
					else
						$newOptions['defaultRoleImages'][$match[1]] = $value;
					}
				}
			$options = array_merge($options, $newOptions);
			bb_update_option('GAOptions', $options);
			echo '<div class="updated"><p>' . __('Image options saved!', GRAVATAR_DOMAIN) . '</p></div>';
			break;
		case __('Reset', GRAVATAR_DOMAIN):
			$options = array_merge($options, GAGetDefaultImageOptions());
			unset($options['defaultRoleImages']);
			bb_update_option('GAOptions', $options);
			echo '<div class="updated"><p>' . __('Image options reseted!', GRAVATAR_DOMAIN) . '</p></div>';
			break;
			}
		}
	// Render options page
?>
	<h2><?php _e('Gravatar Options', GRAVATAR_DOMAIN); ?></h2>
		<h3><?php _e('About this plugin', GRAVATAR_DOMAIN); ?></h3>
		<div>
		<ul>
			<li><a href="http://code.google.com/p/llbbsc/wiki/GravatarPlugin"><?php _e('Plugin\'s Website', GRAVATAR_DOMAIN); ?></a> - <?php _e('Documentations', GRAVATAR_DOMAIN); ?></li>
			<li><a href="http://groups.google.com/group/llbbsc"><?php _e('Get Support', GRAVATAR_DOMAIN); ?></a> - <?php _e('Ask question, submit feedbacks', GRAVATAR_DOMAIN); ?></li>
			<li><a href="http://www.livibetter.com/"><?php _e('Author\'s Website', GRAVATAR_DOMAIN); ?></a></li>
		</ul>
		</div>

		<h3><?php _e('Management', GRAVATAR_DOMAIN); ?></h3>
		<div>
			<form method="post" action="">
				<p>
					<input type="submit" name="manage" value="<?php _e('Reset All Options', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
					<small><?php _e('Reverts all options to defaults.', GRAVATAR_DOMAIN); ?></small>
				</p>
				<p>
					<input type="submit" name="manage" value="<?php _e('Deactivate Plugin', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
					<small><?php _e('Be careful! This will remove all your settings for this plugin! If you don\'t want to lose settings, please use Plugins page to deactivate this plugin.', GRAVATAR_DOMAIN); ?></small>
				</p>
			</form>
		</div>

		<h3><?php _e('General Options', GRAVATAR_DOMAIN); ?></h3>
		<div>
			<form method="post" action="">
			<table><tbody>
				<tr>
					<td><label for="useRegisteredEmail"><?php _e('Use Registered Email?', GRAVATAR_DOMAIN); ?></labal></td>
					<td>
						<select name="useRegisteredEmail" id="useRegisteredEmail">
						<option <?php if($options['useRegisteredEmail']) echo 'selected'; ?> value="false"><?php _e('No', GRAVATAR_DOMAIN); ?></option>
						<option <?php if($options['useRegisteredEmail']) echo 'selected'; ?> value="true"><?php _e('Yes', GRAVATAR_DOMAIN); ?></option>
						</select>
					<td>
				</tr>
				<tr>
					<td><label for="rating"><?php _e('Rating:', GRAVATAR_DOMAIN); ?></label></td>
					<td>
						<select name="rating" id="rating">
						<option <?php if(empty($options['rating']) ) echo 'selected'; ?>   value=""></option>
						<option <?php if($options['rating'] ==  'G') echo 'selected'; ?>  value="G">G</option>
						<option <?php if($options['rating'] == 'PG') echo 'selected'; ?> value="PG">PG</option>
						<option <?php if($options['rating'] ==  'R') echo 'selected'; ?>  value="R">R</option>
						<option <?php if($options['rating'] ==  'X') echo 'selected'; ?>  value="X">X</option>
						</select>
					</td> 
				</tr>
				<tr>
					<td><label for="size"><?php _e('Avatar Size:', GRAVATAR_DOMAIN); ?></label></td>
					<td>
						<input name="size" type="text" id="size" value="<?php echo $options['size']; ?>" size="3"/>
						<em><small><?php _e('1 to 80 (pixels).', GRAVATAR_DOMAIN); ?></small></em>
					</td>
				</tr>
			</tbody></table>
			<div class="submit">
				<input type="submit" name="updateGeneralOptions" value="<?php _e('Save', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
				<input type="submit" name="updateGeneralOptions" value="<?php _e('Reset', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
			</div>
			</form>
		</div>

		<h3 ><?php _e('Default Image URIs', GRAVATAR_DOMAIN); ?></h3>
		<div>
			<form method="post" action="">
			<table><tbody>
				<tr>
					<th><?php _e('Avatar', GRAVATAR_DOMAIN); ?></th>
					<th><?php _e('Role Name / Default Image URI', GRAVATAR_DOMAIN); ?></th>
				</tr>
				<tr>
					<td style="text-align: center">
<?php
$imageURI = $options['defaultImage'];
if (!empty($imageURI))
	echo '<img style="border: 1px solid black; width: 64px; height: 64px;" src="' . attribute_escape($imageURI) . '" alt="' . __('Default Image', GRAVATAR_DOMAIN) . '"/>';
?>
					</td>
					<td>
						<label for="defaultImage"><?php _e('Default Image - This will apply to all no avatars users with no default avatar role.', GRAVATAR_DOMAIN); ?></label><br/>
						<input name="defaultImage" type="text" id="defaultImage" value="<?php echo htmlspecialchars(stripslashes($options['defaultImage'])); ?>" size="50" />
					</td>
				</tr>
				<tr>
					<td colspan="2"><?php _e('The following role default avatars override the Default Image above. Applies only when users don\'t have an avatar.', GRAVATAR_DOMAIN); ?></td>
				</tr>
<?php
// List Roles
global $bb_roles;
foreach ($bb_roles->roles as $role => $data) {
	$fieldID = attribute_escape($role);
?>
				<tr>
					<td style="text-align: center">
<?php
$imageURI = $options['defaultRoleImages'][$role];
if (!empty($imageURI))
	echo '<img style="border: 1px solid black; width: 64px; height: 64px;" src="' . attribute_escape($imageURI) . '" alt="' . $bb_roles->role_names[$role] . '"/>';
?>
					</td>
					<td>
						<label for="defaultImage-<?php echo $fieldID; ?>"><?php echo $bb_roles->role_names[$role]; ?></label><br/>
						<input type="text" id="defaultImage-<?php echo $fieldID; ?>" name="defaultImage-<?php echo $fieldID; ?>" value="<?php echo htmlspecialchars(stripslashes($options['defaultRoleImages'][$role])); ?>" size="50">
					</td>
				</tr>
<?php
	}
?>
			</tbody></table>
			<div class="submit">
				<input type="submit" name="updateImageOptions" value="<?php _e('Save', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
				<input type="submit" name="updateImageOptions" value="<?php _e('Reset', GRAVATAR_DOMAIN); ?>" style="font-weight:bold;"/>
			</div>
			</form>
		</div>
<?php
	}
?>
